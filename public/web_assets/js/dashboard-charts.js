/*
 * Dashboard chart renderer.
 * Requires Chart.js 3.x (UMD) + adwiseri-chart-types.js + chartjs-plugin-datalabels.
 */
(function (global) {
  'use strict';

  var VIRTUAL_TYPES = {
    area: 'line',
    gauge: 'doughnut',
    scatter: 'scatter',
    bubble: 'bubble'
  };

  var dashChartPalette = [
    '#e6194B', '#3cb44b', '#4363d8', '#f58231', '#911eb4',
    '#42d4f4', '#f032e6', '#bfef45', '#469990', '#e6beff',
    '#9A6324', '#800000', '#aaffc3', '#808000', '#000075',
    '#695EEE', '#26A69A', '#EF5350', '#FFA726', '#42A5F5'
  ];
  var dashLineStroke = '#4363d8';
  var dashChartInstances = [];
  var dashCharts = [];
  var rerenderTimer = null;
  var rerenderAttempts = 0;
  var resizeTimer = null;
  var postRenderResizeTimers = [];
  var layoutStableTimer = null;
  var booted = false;

  function paletteFor(count) {
    var colors = [];
    for (var i = 0; i < count; i++) {
      colors.push(dashChartPalette[i % dashChartPalette.length]);
    }
    return colors;
  }

  function chartValue(raw) {
    if (raw && typeof raw === 'object') {
      return Number(raw.y != null ? raw.y : raw.r) || 0;
    }
    return Number(raw) || 0;
  }

  function chartStyleOf(configOrType) {
    if (configOrType && typeof configOrType === 'object') {
      return (configOrType.adwiseriStyle || configOrType.type) || '';
    }
    return configOrType || '';
  }

  function isRadialDashChart(style) {
    if (global.AdwiseriCharts && typeof global.AdwiseriCharts.isRadial === 'function') {
      return global.AdwiseriCharts.isRadial(style);
    }
    return style === 'pie' || style === 'doughnut' || style === 'gauge';
  }

  function resolvePointColor(dataset, index) {
    if (Array.isArray(dataset._indicatorColors) && dataset._indicatorColors[index]) {
      return dataset._indicatorColors[index];
    }
    if (Array.isArray(dataset.pointBackgroundColor) && dataset.pointBackgroundColor[index]) {
      return dataset.pointBackgroundColor[index];
    }
    if (Array.isArray(dataset.backgroundColor) && dataset.backgroundColor[index]) {
      return dataset.backgroundColor[index];
    }
    return dashChartPalette[index % dashChartPalette.length];
  }

  function buildDataset(cfg, colors) {
    var style = chartStyleOf(cfg);
    var dataset = {
      label: '',
      data: (cfg.values || []).map(chartValue),
      borderWidth: style === 'line' || style === 'area' ? 3 : 1,
      _indicatorColors: colors
    };

    if (style === 'line' || style === 'area') {
      dataset.borderColor = style === 'area' ? (colors[0] || dashLineStroke) : dashLineStroke;
      dataset.backgroundColor = style === 'area' ? (colors[0] || dashChartPalette[0]) : 'rgba(67, 99, 216, 0.08)';
      dataset.pointBackgroundColor = colors;
      dataset.pointBorderColor = colors;
      dataset.pointRadius = style === 'area' ? 4 : 5;
      dataset.pointHoverRadius = style === 'area' ? 6 : 7;
      dataset.pointHitRadius = 12;
      dataset.fill = style === 'area' ? 'origin' : false;
      dataset.showLine = true;
      dataset.spanGaps = true;
      dataset.clip = false;
      dataset.tension = 0.35;
      dataset.cubicInterpolationMode = 'monotone';
    } else if (style === 'scatter' || style === 'bubble') {
      dataset.backgroundColor = colors;
      dataset.borderColor = colors;
      dataset.pointBackgroundColor = colors;
      dataset.pointBorderColor = colors;
      dataset.pointRadius = 6;
      dataset.pointHoverRadius = 8;
    } else {
      dataset.backgroundColor = colors;
      dataset.borderColor = colors;
      dataset.hoverBackgroundColor = colors;
    }

    return dataset;
  }

  function buildTooltipOptions(cfg, colors) {
    var total = (cfg.values || []).reduce(function (sum, value) {
      return sum + chartValue(value);
    }, 0);

    var callbacks = {
      title: function () {
        return null;
      },
      label: function (context) {
        var labels = cfg.labels || [];
        var index = context.dataIndex;
        if (context.parsed && context.parsed.x !== undefined && !isNaN(context.parsed.x)) {
          index = Math.round(Number(context.parsed.x));
        }
        if (labels[index] !== undefined && String(labels[index]).trim()) {
          return String(labels[index]);
        }
        if (context.label) {
          return String(context.label);
        }
        return context.formattedValue != null ? String(context.formattedValue) : String(chartValue(context.raw));
      },
      afterLabel: function () {
        return null;
      },
      afterBody: function () {
        return [];
      },
      footer: function (tooltipItems) {
        if (!tooltipItems || !tooltipItems.length) {
          return [];
        }
        var value = chartValue(tooltipItems[0].raw);
        var percent = total > 0 ? ((value / total) * 100).toFixed(0) : '0';
        return [
          'Value : ' + value,
          'Percentage : ' + percent + ' %'
        ];
      },
      labelColor: function (context) {
        var index = context.dataIndex;
        if (context.parsed && context.parsed.x !== undefined && !isNaN(context.parsed.x)) {
          index = Math.round(Number(context.parsed.x));
        }
        var color = resolvePointColor(context.dataset || {}, index);
        return { borderColor: color, backgroundColor: color };
      },
      labelTextColor: function () {
        return '#ffffff';
      }
    };

    if (global.AdwiseriCharts && typeof global.AdwiseriCharts.completeTooltipCallbacks === 'function') {
      callbacks = global.AdwiseriCharts.completeTooltipCallbacks(callbacks);
    }

    var tooltip = {
      enabled: false,
      mode: 'nearest',
      intersect: true,
      position: 'nearest',
      backgroundColor: 'rgba(15, 23, 42, 0.94)',
      titleColor: '#ffffff',
      bodyColor: '#ffffff',
      footerColor: '#ffffff',
      bodyAlign: 'left',
      footerAlign: 'left',
      boxWidth: 11,
      boxHeight: 11,
      boxPadding: 2,
      callbacks: callbacks
    };

    if (global.AdwiseriCharts && typeof global.AdwiseriCharts.renderHtmlTooltip === 'function') {
      tooltip.external = global.AdwiseriCharts.renderHtmlTooltip;
    }

    return tooltip;
  }

  function buildDataLabelsOptions(cfg, style) {
    var count = (cfg.labels || []).length;
    var radial = isRadialDashChart(style);

    return {
      display: count > 0 && (radial ? count <= 10 : true),
      color: '#000000',
      anchor: radial ? 'center' : 'end',
      align: radial ? 'center' : 'top',
      offset: radial ? 0 : 4,
      rotation: 0,
      clip: false,
      clamp: true,
      font: {
        size: radial ? 10 : (count > 10 ? 9 : 11),
        weight: '700'
      },
      formatter: function (value) {
        if (value && typeof value === 'object') {
          return value.y != null ? value.y : '';
        }
        return value;
      }
    };
  }

  function buildDashboardLegendItems(chartInstance) {
    var labels = chartInstance.data.labels || [];
    var ds = (chartInstance.data.datasets && chartInstance.data.datasets[0]) || {};

    return labels.reduce(function (items, label, index) {
      var text = String(label == null ? '' : label).trim();
      if (!text || text.toLowerCase() === 'null' || text.toLowerCase() === 'undefined' || text === '-' || text.toLowerCase() === 'n/a') {
        return items;
      }

      var color = resolvePointColor(ds, index);
      items.push({
        text: ' ' + text,
        fillStyle: color,
        strokeStyle: color,
        fontColor: '#000000',
        color: '#000000',
        lineWidth: 1,
        pointStyle: 'circle',
        hidden: typeof chartInstance.getDataVisibility === 'function'
          ? !chartInstance.getDataVisibility(index)
          : false,
        index: index,
        datasetIndex: 0
      });
      return items;
    }, []);
  }

  function applyGaugeFallback(config) {
    config.options = config.options || {};
    config.options.circumference = 180;
    config.options.rotation = 270;
    config.options.cutout = '70%';

    if (config.data && Array.isArray(config.data.datasets)) {
      config.data.datasets.forEach(function (ds) {
        ds.circumference = 180;
        ds.rotation = 270;
      });
    }
  }

  function applyAreaFallback(config) {
    if (chartStyleOf(config) !== 'area' || !config.data || !Array.isArray(config.data.datasets)) {
      return;
    }

    config.data.datasets.forEach(function (ds) {
      var stroke = Array.isArray(ds.borderColor) ? ds.borderColor[0] : (ds.borderColor || '#4363d8');
      if (typeof stroke === 'string') {
        var normalized = stroke.trim().toLowerCase();
        if (!normalized || normalized === '#000' || normalized === '#000000' || normalized === 'black' || normalized === '#555555') {
          stroke = '#4363d8';
        }
      }

      ds.fill = 'origin';
      ds.tension = ds.tension == null ? 0.35 : ds.tension;
      ds.borderColor = stroke;
      ds.borderWidth = ds.borderWidth || 2;
      ds.pointRadius = ds.pointRadius || 4;
      ds.pointHoverRadius = ds.pointHoverRadius || 6;

      if (typeof stroke === 'string' && stroke.indexOf('rgba(') === 0) {
        ds.backgroundColor = stroke;
      } else if (typeof stroke === 'string' && stroke.charAt(0) === '#') {
        var hex = stroke.replace('#', '');
        if (hex.length === 6) {
          var n = parseInt(hex, 16);
          ds.backgroundColor = 'rgba(' + ((n >> 16) & 255) + ', ' + ((n >> 8) & 255) + ', ' + (n & 255) + ', 0.38)';
        }
      } else {
        ds.backgroundColor = 'rgba(67, 99, 216, 0.38)';
      }
    });
  }

  function adaptDashChartConfig(config) {
    var originalType = (config && config.type) || '';

    if (global.AdwiseriCharts && typeof global.AdwiseriCharts.adaptConfig === 'function') {
      global.AdwiseriCharts.adaptConfig(config);
    } else if (VIRTUAL_TYPES[originalType]) {
      config.adwiseriStyle = originalType;
      config.type = VIRTUAL_TYPES[originalType];
      if (originalType === 'gauge') {
        applyGaugeFallback(config);
      }
    }

    if (VIRTUAL_TYPES[config.type]) {
      config.adwiseriStyle = config.adwiseriStyle || config.type;
      config.type = VIRTUAL_TYPES[config.type];
      if (config.adwiseriStyle === 'gauge') {
        applyGaugeFallback(config);
      }
    }

    applyAreaFallback(config);
  }

  function sanitizeDashboardChartConfig(config, style) {
    config.options = config.options || {};

    if (isRadialDashChart(style)) {
      config.options.scales = {
        x: { display: false, grid: { display: false }, ticks: { display: false }, border: { display: false } },
        y: { display: false, grid: { display: false }, ticks: { display: false }, border: { display: false } }
      };
      config.options.layout = Object.assign({}, config.options.layout || {}, {
        padding: { top: 10, bottom: 8, left: 8, right: 8 }
      });
      return;
    }

    config.options.scales = config.options.scales || {};
    config.options.scales.x = Object.assign({}, config.options.scales.x || {}, {
      offset: true
    });
    config.options.clip = false;
    config.options.layout = Object.assign({}, config.options.layout || {}, {
      padding: { top: 22, right: 12, bottom: 6, left: 4 }
    });
    config.options.scales.y = Object.assign({}, config.options.scales.y || {}, {
      type: 'linear',
      beginAtZero: true,
      grace: '10%'
    });
  }

  function destroyDashCharts() {
    dashChartInstances.forEach(function (chart) {
      try {
        if (chart && chart.canvas) {
          resetCanvasSize(chart.canvas);
        }
        if (chart && typeof chart.destroy === 'function') {
          chart.destroy();
        }
      } catch (e) {}
    });
    dashChartInstances = [];

    document.querySelectorAll('.dash-chart-canvas canvas').forEach(function (canvas) {
      resetCanvasSize(canvas);
    });
  }

  function resetCanvasSize(canvas) {
    if (!canvas) {
      return;
    }

    canvas.removeAttribute('width');
    canvas.removeAttribute('height');
    canvas.style.width = '';
    canvas.style.height = '';
    canvas.style.maxWidth = '';
    canvas.style.maxHeight = '';
  }

  function chartHostReady(canvas) {
    if (!canvas || !canvas.parentElement) {
      return false;
    }

    var host = canvas.parentElement;
    if (host.clientWidth < 40 || host.clientHeight < 40) {
      return false;
    }

    var panel = host.closest('.dash-chart-panel');
    if (panel && panel.clientWidth > 0) {
      var panelRatio = host.clientWidth / panel.clientWidth;
      if (panelRatio < 0.85) {
        return false;
      }
    }

    var row = host.closest('.dash-charts-row');
    if (row && row.clientWidth > 0) {
      var minExpectedWidth = row.clientWidth * 0.35;
      if (host.clientWidth < minExpectedWidth) {
        return false;
      }
    }

    return true;
  }

  function allChartHostsReady() {
    if (!dashCharts.length) {
      return true;
    }

    return dashCharts.every(function (cfg) {
      if (cfg.empty) {
        return true;
      }

      var canvas = document.getElementById(cfg.id);
      return chartHostReady(canvas);
    });
  }

  function clearPostRenderResizeTimers() {
    postRenderResizeTimers.forEach(function (timerId) {
      clearTimeout(timerId);
    });
    postRenderResizeTimers = [];
  }

  function schedulePostRenderResize() {
    clearPostRenderResizeTimers();
    [0, 120, 300, 600].forEach(function (delay) {
      postRenderResizeTimers.push(setTimeout(function () {
        if (!dashChartInstances.length && dashCharts.length) {
          scheduleRerender();
          return;
        }
        resizeDashCharts();
        equalizeDashboardColumns();
      }, delay));
    });
  }

  function whenLayoutStable(callback) {
    clearTimeout(layoutStableTimer);

    var attempts = 0;
    var lastWidths = null;

    function probe() {
      attempts += 1;

      if (allChartHostsReady()) {
        var widths = dashCharts.map(function (cfg) {
          if (cfg.empty) {
            return 0;
          }
          var canvas = document.getElementById(cfg.id);
          return canvas && canvas.parentElement ? canvas.parentElement.clientWidth : 0;
        });

        if (lastWidths && widths.join('|') === lastWidths.join('|')) {
          callback();
          return;
        }

        lastWidths = widths;
      }

      if (attempts >= 30) {
        callback();
        return;
      }

      layoutStableTimer = setTimeout(function () {
        requestAnimationFrame(probe);
      }, 50);
    }

    if (document.readyState === 'complete') {
      requestAnimationFrame(probe);
      return;
    }

    window.addEventListener('load', function onLoad() {
      window.removeEventListener('load', onLoad);
      requestAnimationFrame(probe);
    }, { once: true });

    requestAnimationFrame(probe);
  }

  function renderChart(cfg) {
    if (cfg.empty) {
      return;
    }

    var canvas = document.getElementById(cfg.id);
    if (!canvas || typeof global.Chart === 'undefined') {
      return;
    }
    if (!chartHostReady(canvas)) {
      return false;
    }

    if (typeof global.Chart.getChart === 'function') {
      var existing = global.Chart.getChart(canvas);
      if (existing) {
        existing.destroy();
      }
    }

    resetCanvasSize(canvas);

    var style = chartStyleOf(cfg);
    var radial = isRadialDashChart(style);
    var colors = paletteFor(cfg.labels.length);
    var dataset = buildDataset(cfg, colors);

    var config = {
      type: style,
      data: {
        labels: cfg.labels,
        datasets: [dataset]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        resizeDelay: 0,
        animation: false,
        layout: {
          padding: radial
            ? { top: 10, right: 8, bottom: 8, left: 8 }
            : { top: 22, right: 12, bottom: 6, left: 4 }
        },
        interaction: { mode: 'nearest', intersect: true, axis: 'xy' },
        hover: { mode: 'nearest', intersect: true },
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            align: 'center',
            labels: {
              boxWidth: 11,
              boxHeight: 11,
              padding: radial ? 10 : 4,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { size: 10, weight: '500' },
              color: '#000000',
              generateLabels: function (chartInstance) {
                return buildDashboardLegendItems(chartInstance);
              }
            }
          },
          colors: { enabled: false, forceOverride: false },
          tooltip: buildTooltipOptions(cfg, colors),
          datalabels: buildDataLabelsOptions(cfg, style)
        },
        scales: radial ? {} : {
          y: {
            type: 'linear',
            beginAtZero: true,
            grace: '10%',
            ticks: {
              precision: 0,
              color: '#000000',
              font: { size: 10, weight: '600' }
            },
            border: {
              display: true,
              color: '#000000',
              width: 1
            }
          },
          x: {
            offset: true,
            ticks: {
              autoSkip: false,
              maxRotation: 55,
              minRotation: 35,
              color: '#000000',
              font: { size: 9, weight: '600' }
            },
            border: {
              display: true,
              color: '#000000',
              width: 1
            }
          }
        }
      }
    };

    adaptDashChartConfig(config);
    sanitizeDashboardChartConfig(config, style);

    if (global.AdwiseriAnalyticsOverlap && typeof global.AdwiseriAnalyticsOverlap.enhance === 'function') {
      global.AdwiseriAnalyticsOverlap.enhance(config);
    }

    var chart = new global.Chart(canvas, config);
    dashChartInstances.push(chart);
    return true;
  }

  function equalizeDashboardColumns() {
    var row = document.querySelector('.client-row.dashboard-equal-cols');
    if (!row) {
      return;
    }

    var left = row.querySelector(':scope > .column-dashbox') || row.querySelector('.column-dashbox');
    var right = row.querySelector(':scope > .activity-box') || row.querySelector('.activity-box');
    var mainCol = row.querySelector(':scope > .dash-main-col') || row.querySelector('.dash-main-col');
    if (!left || !right) {
      return;
    }

    left.style.minHeight = '';
    left.style.height = '';
    right.style.minHeight = '';
    right.style.height = '';
    right.style.maxHeight = '';
    if (mainCol) {
      mainCol.style.minHeight = '';
      mainCol.style.height = '';
    }

    if (global.matchMedia('(max-width: 991px)').matches) {
      return;
    }

    var leftHeight = left.offsetHeight;
    if (leftHeight < 1) {
      return;
    }

    right.style.height = leftHeight + 'px';
    right.style.maxHeight = leftHeight + 'px';
    right.style.minHeight = leftHeight + 'px';

    if (mainCol) {
      var chartsRow = mainCol.querySelector('.dash-charts-row');
      var needsAutoHeight = chartsRow && /dash-charts-row--count-(4|5|6)/.test(chartsRow.className);
      mainCol.style.minHeight = needsAutoHeight ? '' : (leftHeight + 'px');
      mainCol.style.height = needsAutoHeight ? 'auto' : (leftHeight + 'px');
    }

    requestAnimationFrame(resizeDashCharts);
  }

  function resizeDashCharts() {
    if (!dashChartInstances.length) {
      if (dashCharts.length) {
        scheduleRerender();
      }
      return;
    }

    var allReady = dashChartInstances.every(function (chart) {
      return chartHostReady(chart && chart.canvas);
    });

    if (!allReady) {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var readyNow = dashChartInstances.every(function (chart) {
          return chartHostReady(chart && chart.canvas);
        });
        if (!readyNow) {
          scheduleRerender();
          return;
        }
        dashChartInstances.forEach(function (chart) {
          try {
            chart.resize();
            if (typeof chart.update === 'function') {
              chart.update('none');
            }
          } catch (e) {
            scheduleRerender();
          }
        });
      }, 120);
      return;
    }

    dashChartInstances.forEach(function (chart) {
      try {
        chart.resize();
        if (typeof chart.update === 'function') {
          chart.update('none');
        }
      } catch (e) {
        scheduleRerender();
      }
    });
  }

  function renderAllDashCharts() {
    destroyDashCharts();

    var pending = false;
    dashCharts.forEach(function (cfg) {
      if (cfg.empty) {
        return;
      }
      if (renderChart(cfg) === false) {
        pending = true;
      }
    });

    requestAnimationFrame(function () {
      dashChartInstances.forEach(function (chart) {
        try {
          if (chartHostReady(chart.canvas)) {
            chart.resize();
          }
        } catch (e) {}
      });
      equalizeDashboardColumns();
    });

    if (pending && rerenderAttempts < 30) {
      rerenderAttempts += 1;
      clearTimeout(rerenderTimer);
      rerenderTimer = setTimeout(renderAllDashCharts, 100);
    } else {
      rerenderAttempts = 0;
      schedulePostRenderResize();
      setTimeout(equalizeDashboardColumns, 50);
    }
  }

  function scheduleRerender() {
    if (!dashCharts || !dashCharts.length) {
      equalizeDashboardColumns();
      return;
    }
    clearTimeout(rerenderTimer);
    rerenderTimer = setTimeout(renderAllDashCharts, 50);
  }

  function bindChartObservers() {
    if (typeof ResizeObserver === 'undefined') {
      return;
    }

    document.querySelectorAll('.dash-chart-canvas').forEach(function (host) {
      if (host._dashChartObserved) {
        return;
      }
      host._dashChartObserved = true;

      var observer = new ResizeObserver(function () {
        if (document.visibilityState === 'hidden') {
          return;
        }
        if (host.clientWidth < 40 || host.clientHeight < 40) {
          return;
        }
        resizeDashCharts();
      });
      observer.observe(host);
    });
  }

  function applyChartDefaults() {
    if (typeof global.Chart === 'undefined' || !global.Chart.defaults) {
      return;
    }

    global.Chart.defaults.color = '#000000';
    if (global.Chart.defaults.scale) {
      global.Chart.defaults.scale.ticks = global.Chart.defaults.scale.ticks || {};
      global.Chart.defaults.scale.ticks.color = '#000000';
      global.Chart.defaults.scale.border = Object.assign({}, global.Chart.defaults.scale.border || {}, {
        display: true,
        color: '#000000',
        width: 1
      });
    }
    ['category', 'linear', 'logarithmic', 'time', 'timeseries', 'radialLinear'].forEach(function (scaleId) {
      if (!global.Chart.defaults.scales || !global.Chart.defaults.scales[scaleId]) {
        return;
      }
      if (global.Chart.defaults.scales[scaleId].ticks) {
        global.Chart.defaults.scales[scaleId].ticks.color = '#000000';
      }
      global.Chart.defaults.scales[scaleId].border = Object.assign({}, global.Chart.defaults.scales[scaleId].border || {}, {
        display: true,
        color: '#000000',
        width: 1
      });
    });
    if (global.Chart.defaults.scales && global.Chart.defaults.scales.category) {
      global.Chart.defaults.scales.category.offset = true;
    }
    if (global.Chart.defaults.plugins && global.Chart.defaults.plugins.colors) {
      global.Chart.defaults.plugins.colors.enabled = false;
      global.Chart.defaults.plugins.colors.forceOverride = false;
    }
    if (global.Chart.defaults.plugins && global.Chart.defaults.plugins.tooltip) {
      global.Chart.defaults.plugins.tooltip.enabled = false;
      global.Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.94)';
      global.Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
      global.Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
      global.Chart.defaults.plugins.tooltip.footerColor = '#ffffff';
      global.Chart.defaults.plugins.tooltip.bodyAlign = 'left';
      global.Chart.defaults.plugins.tooltip.footerAlign = 'left';
      global.Chart.defaults.plugins.tooltip.boxWidth = 11;
      global.Chart.defaults.plugins.tooltip.boxHeight = 11;
      global.Chart.defaults.plugins.tooltip.boxPadding = 2;
      if (global.AdwiseriCharts && typeof global.AdwiseriCharts.renderHtmlTooltip === 'function') {
        global.Chart.defaults.plugins.tooltip.external = global.AdwiseriCharts.renderHtmlTooltip;
      }
    }
    if (global.Chart.defaults.plugins && global.Chart.defaults.plugins.datalabels) {
      global.Chart.defaults.plugins.datalabels.clip = false;
      global.Chart.defaults.plugins.datalabels.rotation = 0;
      global.Chart.defaults.plugins.datalabels.clamp = true;
    }
    if (global.Chart.defaults.plugins && global.Chart.defaults.plugins.legend && global.Chart.defaults.plugins.legend.labels) {
      global.Chart.defaults.plugins.legend.labels.color = '#000000';
    }

    patchLegendSwatchGap();
  }

  function patchLegendSwatchGap() {
    if (typeof global.Chart === 'undefined' || !global.Chart.registry || !global.Chart.registry.plugins) {
      return;
    }

    var plugin = global.Chart.registry.plugins.get('legend');
    var Legend = plugin && plugin._element;
    if (!Legend || !Legend.prototype || !Legend.prototype._draw || Legend.prototype._adwiseriGapPatched) {
      return;
    }

    Legend.prototype._adwiseriGapPatched = true;
    var origDraw = Legend.prototype._draw;
    Legend.prototype._draw = function () {
      var ctx = this.ctx;
      var fontOpt = this.options.labels && this.options.labels.font;
      var fontSize = (fontOpt && fontOpt.size) || 11;
      if (global.Chart.helpers && typeof global.Chart.helpers.toFont === 'function') {
        fontSize = global.Chart.helpers.toFont(fontOpt).size || fontSize;
      }
      var tighten = fontSize / 4;
      var origFillText = ctx.fillText;
      var titleOn = this.options.title && this.options.title.display;
      var fills = 0;
      ctx.fillText = function (text, x, y, maxWidth) {
        if (titleOn && fills++ === 0) {
          return origFillText.call(this, text, x, y, maxWidth);
        }
        fills++;
        return origFillText.call(this, text, x - tighten, y, maxWidth);
      };
      try {
        return origDraw.apply(this, arguments);
      } finally {
        ctx.fillText = origFillText;
      }
    };
  }

  function registerDataLabelsPlugin() {
    if (typeof global.Chart === 'undefined' || typeof global.Chart.register !== 'function') {
      return;
    }
    if (global.__DASHBOARD_DATALABELS_REGISTERED__) {
      return;
    }
    var plugin = global.ChartDataLabels;
    if (plugin) {
      global.Chart.register(plugin);
      global.__DASHBOARD_DATALABELS_REGISTERED__ = true;
    }
  }

  function init(charts) {
    dashCharts = Array.isArray(charts) ? charts : [];
    registerDataLabelsPlugin();
    applyChartDefaults();
    bindChartObservers();
    scheduleRerender();
  }

  function boot() {
    if (booted) {
      return;
    }
    booted = true;

    whenLayoutStable(function () {
      init(global.__DASHBOARD_CHARTS__ || []);
    });
  }

  global.AdwiseriDashboardCharts = {
    init: init,
    renderAll: renderAllDashCharts,
    resize: resizeDashCharts
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
      booted = false;
      destroyDashCharts();

      var mainCol = document.querySelector('.dash-main-col');
      if (mainCol) {
        mainCol.style.minHeight = '';
        mainCol.style.height = '';
      }

      whenLayoutStable(function () {
        booted = true;
        scheduleRerender();
      });
    }
    equalizeDashboardColumns();
  });

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState !== 'visible') {
      return;
    }
    resizeDashCharts();
    equalizeDashboardColumns();
  });

  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      resizeDashCharts();
      equalizeDashboardColumns();
    }, 150);
  });

  window.addEventListener('orientationchange', function () {
    setTimeout(function () {
      scheduleRerender();
      equalizeDashboardColumns();
    }, 200);
  });
})(window);
