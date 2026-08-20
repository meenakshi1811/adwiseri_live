/*
 * Adwiseri chart types.
 *
 * Chart.js draws four of the eight types we offer natively. The other four are
 * expressed in terms of those: Area is a filled line, Scatter and Bubble are
 * point charts fed {x, y[, r]} instead of plain numbers, and Gauge is a
 * half-circle doughnut.
 *
 * Rather than edit ~190 `new Chart(ctx, {...})` call sites across the Analytics
 * views, this file wraps the Chart constructor and rewrites the config on the
 * way through. `adaptConfig` is a pure function so it can be unit tested.
 *
 * Load AFTER chart.js (and after chartjs-plugin-datalabels).
 */
(function (global) {
    'use strict';

    /* Virtual chart type -> the Chart.js type that actually draws it. */
    var BASE_TYPE = {
        line: 'line',
        bar: 'bar',
        pie: 'pie',
        doughnut: 'doughnut',
        area: 'line',
        scatter: 'scatter',
        bubble: 'bubble',
        gauge: 'doughnut'
    };

    /* Types drawn as a circle: no cartesian axes, legend carries the categories. */
    var RADIAL = ['pie', 'doughnut', 'gauge'];

    var BUBBLE_MIN_RADIUS = 4;
    var BUBBLE_MAX_RADIUS = 22;
    var FALLBACK_COLOR = '#695EEE';
    var TOOLTIP_BOX_SIZE = 11;
    var TOOLTIP_BOX_PADDING = 2;
    var TOOLTIP_BG = 'rgba(15, 23, 42, 0.94)';
    var TOOLTIP_TEXT = '#ffffff';
    var TOOLTIP_ELEMENT_ID = 'adwiseri-chart-tooltip';

    function tooltipSpacingOptions(overrides) {
        return Object.assign({
            enabled: false,
            external: renderHtmlTooltip,
            backgroundColor: TOOLTIP_BG,
            titleColor: TOOLTIP_TEXT,
            bodyColor: TOOLTIP_TEXT,
            footerColor: TOOLTIP_TEXT,
            borderColor: 'rgba(255, 255, 255, 0.18)',
            borderWidth: 1,
            bodyAlign: 'left',
            footerAlign: 'left',
            boxWidth: TOOLTIP_BOX_SIZE,
            boxHeight: TOOLTIP_BOX_SIZE,
            boxPadding: TOOLTIP_BOX_PADDING
        }, overrides || {});
    }

    function completeTooltipCallbacks(callbacks) {
        var merged = Object.assign({}, callbacks || {});

        if (typeof merged.labelTextColor !== 'function') {
            merged.labelTextColor = function () {
                return TOOLTIP_TEXT;
            };
        }
        if (typeof merged.labelPointStyle !== 'function') {
            merged.labelPointStyle = function () {
                return { pointStyle: 'circle', rotation: 0 };
            };
        }

        ['beforeTitle', 'afterTitle', 'beforeBody', 'beforeLabel', 'beforeFooter', 'afterFooter'].forEach(function (name) {
            if (typeof merged[name] !== 'function') {
                merged[name] = function () {
                    return null;
                };
            }
        });

        return merged;
    }

    function tooltipHasText(tooltip) {
        if (!tooltip) {
            return false;
        }

        var i;
        var body = tooltip.body || [];
        for (i = 0; i < body.length; i += 1) {
            if (body[i] && body[i].before && body[i].before.join('').trim()) {
                return true;
            }
            if (body[i] && body[i].lines && body[i].lines.join('').trim()) {
                return true;
            }
            if (body[i] && body[i].after && body[i].after.join('').trim()) {
                return true;
            }
        }

        if ((tooltip.title || []).join('').trim()) {
            return true;
        }

        return (tooltip.footer || []).some(function (line) {
            var text = String(line || '').replace(/[-_\s]/g, '');
            return text.length > 0;
        });
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function hideHtmlTooltip() {
        if (typeof document === 'undefined') {
            return;
        }
        var el = document.getElementById(TOOLTIP_ELEMENT_ID);
        if (!el) {
            return;
        }
        el.style.opacity = '0';
        el.style.left = '-9999px';
    }

    function bindTooltipDismiss() {
        if (typeof window === 'undefined' || window.__ADWISERI_TOOLTIP_HIDE_BOUND__) {
            return;
        }
        window.__ADWISERI_TOOLTIP_HIDE_BOUND__ = true;
        window.addEventListener('scroll', hideHtmlTooltip, true);
        window.addEventListener('resize', hideHtmlTooltip);
        document.addEventListener('mouseleave', hideHtmlTooltip);
    }

    function ensureTooltipElement() {
        var el = document.getElementById(TOOLTIP_ELEMENT_ID);
        if (el) {
            return el;
        }

        el = document.createElement('div');
        el.id = TOOLTIP_ELEMENT_ID;
        el.className = 'adwiseri-chart-tooltip';
        el.setAttribute('role', 'tooltip');
        document.body.appendChild(el);
        return el;
    }

    function renderHtmlTooltip(context) {
        if (typeof document === 'undefined' || !context || !context.tooltip) {
            return;
        }

        var tooltip = context.tooltip;
        var chart = context.chart;
        var el = ensureTooltipElement();

        if (!tooltip.opacity || !tooltipHasText(tooltip)) {
            el.style.opacity = '0';
            el.style.left = '-9999px';
            return;
        }

        var html = [];
        var colors = tooltip.labelColors || [];
        var body = tooltip.body || [];
        var i;
        var color;
        var lines;

        (tooltip.title || []).forEach(function (line) {
            if (String(line || '').trim()) {
                html.push('<div class="adwiseri-chart-tooltip-title">' + escapeHtml(line) + '</div>');
            }
        });

        for (i = 0; i < body.length; i += 1) {
            color = colors[i] || {};
            lines = [].concat(
                (body[i] && body[i].before) || [],
                (body[i] && body[i].lines) || [],
                (body[i] && body[i].after) || []
            ).filter(function (line) {
                return String(line || '').trim();
            });

            if (!lines.length) {
                continue;
            }

            html.push('<div class="adwiseri-chart-tooltip-row">');
            html.push('<span class="adwiseri-chart-tooltip-swatch" style="background:' +
                escapeHtml(color.backgroundColor || FALLBACK_COLOR) +
                ';border-color:' + escapeHtml(color.borderColor || color.backgroundColor || FALLBACK_COLOR) +
                ';"></span>');
            html.push('<span>' + escapeHtml(lines.join(' ')) + '</span>');
            html.push('</div>');
        }

        var footerLines = (tooltip.footer || []).filter(function (line) {
            return String(line || '').replace(/[-_\s]/g, '').length > 0;
        });
        if (footerLines.length) {
            html.push('<div class="adwiseri-chart-tooltip-footer">');
            footerLines.forEach(function (line) {
                html.push('<div>' + escapeHtml(line) + '</div>');
            });
            html.push('</div>');
        }

        if (!html.length) {
            el.style.opacity = '0';
            el.style.left = '-9999px';
            return;
        }

        el.innerHTML = html.join('');

        var canvas = chart && chart.canvas;
        var rect = canvas ? canvas.getBoundingClientRect() : { left: 0, top: 0 };
        var caretX = Number(tooltip.caretX) || 0;
        var caretY = Number(tooltip.caretY) || 0;
        var left = rect.left + caretX;
        var top = rect.top + caretY;

        el.style.opacity = '1';
        el.style.left = left + 'px';
        el.style.top = top + 'px';

        var tipRect = el.getBoundingClientRect();
        var margin = 12;
        if (left + tipRect.width + margin > window.innerWidth) {
            left = window.innerWidth - tipRect.width - margin;
        }
        if (left < margin) {
            left = margin;
        }
        if (top + tipRect.height + margin > window.innerHeight) {
            top = top - tipRect.height - 16;
        }
        if (top < margin) {
            top = margin;
        }

        el.style.left = Math.round(left) + 'px';
        el.style.top = Math.round(top) + 'px';
    }

    function applyReadableTooltip(config) {
        if (!config) {
            return config;
        }

        var options = config.options || (config.options = {});
        var plugins = options.plugins || (options.plugins = {});
        var existing = plugins.tooltip || {};
        var callbacks = completeTooltipCallbacks(existing.callbacks);

        plugins.tooltip = Object.assign({}, existing, tooltipSpacingOptions({
            callbacks: callbacks
        }));
        plugins.tooltip.enabled = false;
        plugins.tooltip.external = renderHtmlTooltip;
        plugins.tooltip.callbacks = callbacks;

        return config;
    }

    function applyHorizontalCountLabels(config) {
        if (!config) {
            return config;
        }

        var options = config.options || (config.options = {});
        var plugins = options.plugins || (options.plugins = {});
        if (!plugins.datalabels) {
            return config;
        }

        var style = (config.adwiseriStyle || config.type || '').toLowerCase();
        var radial = isRadial(style);
        plugins.datalabels.rotation = 0;
        plugins.datalabels.clamp = true;
        plugins.datalabels.clip = false;
        plugins.datalabels.color = plugins.datalabels.color || '#000000';
        if (!radial) {
            plugins.datalabels.anchor = plugins.datalabels.anchor || 'end';
            plugins.datalabels.align = 'top';
            plugins.datalabels.offset = plugins.datalabels.offset == null ? 4 : plugins.datalabels.offset;
        }

        return config;
    }

    function patchTooltipIndicatorGap(ChartRef) {
        var plugin = ChartRef.registry && ChartRef.registry.plugins && ChartRef.registry.plugins.get
            ? ChartRef.registry.plugins.get('tooltip')
            : null;
        var Tooltip = plugin && plugin._element;
        if (!Tooltip || !Tooltip.prototype || Tooltip.prototype._adwiseriTooltipGapPatched) {
            return;
        }

        var bodyMethods = ['_drawBody', 'drawBody'].filter(function (name) {
            return typeof Tooltip.prototype[name] === 'function';
        });
        if (!bodyMethods.length) {
            return;
        }

        Tooltip.prototype._adwiseriTooltipGapPatched = true;
        bodyMethods.forEach(function (methodName) {
            var original = Tooltip.prototype[methodName];
            Tooltip.prototype[methodName] = function () {
                var ctx = this.ctx;
                var fontSize = 12;
                try {
                    var bodyFont = this.options && this.options.bodyFont;
                    if (ChartRef.helpers && typeof ChartRef.helpers.toFont === 'function') {
                        fontSize = ChartRef.helpers.toFont(bodyFont).size || fontSize;
                    }
                } catch (error) {
                    /* keep default */
                }

                var tighten = fontSize / 4;
                var originalFillText = ctx.fillText;
                ctx.fillText = function (text, x, y, maxWidth) {
                    return originalFillText.call(this, text, x - tighten, y, maxWidth);
                };

                try {
                    return original.apply(this, arguments);
                } finally {
                    ctx.fillText = originalFillText;
                }
            };
        });
    }

    function isSupported(style) {
        return typeof style === 'string' && Object.prototype.hasOwnProperty.call(BASE_TYPE, style);
    }

    function baseType(style) {
        return isSupported(style) ? BASE_TYPE[style] : style;
    }

    function isRadial(style) {
        return RADIAL.indexOf(style) !== -1;
    }

    function types() {
        return Object.keys(BASE_TYPE);
    }

    function eachDataset(config, fn) {
        var datasets = config && config.data && config.data.datasets;
        if (Array.isArray(datasets)) {
            datasets.forEach(fn);
        }
    }

    function firstColor(color) {
        return Array.isArray(color) ? color[0] : color;
    }

    function isUsableIndicatorColor(color) {
        if (color == null || color === '') {
            return false;
        }
        if (typeof color !== 'string') {
            return true;
        }
        var normalized = color.trim().toLowerCase();
        return normalized !== 'transparent'
            && normalized !== 'rgba(0,0,0,0)'
            && normalized !== '#000'
            && normalized !== '#000000'
            && normalized !== 'black';
    }

    function resolveTooltipColor(context, labels) {
        var dataset = context.dataset || {};
        var index = context.dataIndex;
        if (context.parsed && context.parsed.x !== undefined && !isNaN(context.parsed.x)) {
            index = Math.round(Number(context.parsed.x));
        }
        if (Array.isArray(dataset._indicatorColors) && dataset._indicatorColors[index]) {
            return dataset._indicatorColors[index];
        }
        if (Array.isArray(dataset.pointBackgroundColor) && dataset.pointBackgroundColor[index]) {
            return dataset.pointBackgroundColor[index];
        }
        if (Array.isArray(dataset.backgroundColor) && dataset.backgroundColor[index]) {
            return dataset.backgroundColor[index];
        }
        if (isUsableIndicatorColor(dataset.backgroundColor)) {
            return dataset.backgroundColor;
        }
        if (isUsableIndicatorColor(dataset.borderColor) && dataset.borderColor !== '#555555') {
            return dataset.borderColor;
        }
        return FALLBACK_COLOR;
    }

    /* Area needs a see-through fill under a solid line. */
    function toTranslucent(color, alpha) {
        if (typeof color !== 'string') {
            return 'rgba(105, 94, 238, ' + alpha + ')';
        }

        var hex = color.trim().match(/^#([0-9a-f]{6})$/i);
        if (hex) {
            var n = parseInt(hex[1], 16);
            return 'rgba(' + ((n >> 16) & 255) + ', ' + ((n >> 8) & 255) + ', ' + (n & 255) + ', ' + alpha + ')';
        }

        var hsl = color.trim().match(/^hsl\(([^)]+)\)$/i);
        if (hsl) {
            return 'hsla(' + hsl[1] + ', ' + alpha + ')';
        }

        var rgb = color.trim().match(/^rgb\(([^)]+)\)$/i);
        if (rgb) {
            return 'rgba(' + rgb[1] + ', ' + alpha + ')';
        }

        return color;
    }

    function sumOfValues(config) {
        var total = 0;
        eachDataset(config, function (ds) {
            (ds.data || []).forEach(function (point) {
                if (point && typeof point === 'object') {
                    total += Number(point.y) || 0;
                } else {
                    total += Number(point) || 0;
                }
            });
        });
        return total;
    }

    function applyArea(config) {
        eachDataset(config, function (ds) {
            /* Prefer an explicit vivid stroke; never collapse to black/transparent. */
            var AREA_STROKE = '#4363d8';
            var raw = firstColor(ds.borderColor) || firstColor(ds.backgroundColor) || AREA_STROKE;
            var line = raw;
            var normalized = typeof raw === 'string' ? raw.trim().toLowerCase() : '';
            if (!normalized
                || normalized === '#000'
                || normalized === '#000000'
                || normalized === 'black'
                || normalized === '#555555'
                || normalized.indexOf('rgba(0,0,0') === 0
                || normalized === 'rgba(0,0,0,0)') {
                line = AREA_STROKE;
            }

            var pointColors = Array.isArray(ds.pointBackgroundColor)
                ? ds.pointBackgroundColor
                : (Array.isArray(ds._indicatorColors) ? ds._indicatorColors : null);

            ds.fill = 'origin';
            if (ds.tension === undefined) {
                ds.tension = 0.35;
            }
            if (ds.pointRadius === undefined) {
                ds.pointRadius = 6;
            }
            if (ds.pointHoverRadius === undefined) {
                ds.pointHoverRadius = 8;
            }

            ds.borderColor = line;
            ds.backgroundColor = toTranslucent(line, 0.38);
            ds.borderWidth = ds.borderWidth || 2;
            ds.pointBackgroundColor = pointColors || line;
            ds.pointBorderColor = pointColors || line;
            if (pointColors) {
                ds._indicatorColors = pointColors;
            }
        });
    }

    function applyPoints(config, style) {
        var labels = (config.data && config.data.labels) || [];

        eachDataset(config, function (ds) {
            if (!Array.isArray(ds.data) || ds.data.length === 0) {
                return;
            }
            if (ds.data[0] !== null && typeof ds.data[0] === 'object') {
                return; // already {x, y} — leave it alone
            }

            var values = ds.data.map(function (v) {
                return Number(v) || 0;
            });

            var max = 0;
            values.forEach(function (v) {
                if (v > max) {
                    max = v;
                }
            });
            if (max <= 0) {
                max = 1;
            }

            ds.data = values.map(function (v, i) {
                var point = { x: i, y: v };
                if (style === 'bubble') {
                    point.r = BUBBLE_MIN_RADIUS + (v / max) * (BUBBLE_MAX_RADIUS - BUBBLE_MIN_RADIUS);
                }
                return point;
            });

            /* Match line-chart indicator size for scatter points. */
            if (style === 'scatter') {
                ds.pointRadius = 6;
                ds.pointHoverRadius = 8;
                ds.radius = 6;
                ds.hoverRadius = 8;
            }

            if (ds.borderColor === undefined) {
                ds.borderColor = firstColor(ds.backgroundColor) || FALLBACK_COLOR;
            }
        });

        /* x is now a numeric index, so map it back to the category name. */
        var options = config.options || (config.options = {});
        var scales = options.scales || (options.scales = {});
        var x = scales.x || (scales.x = {});
        var y = scales.y || (scales.y = {});

        x.type = 'linear';
        x.offset = true;

        var xTicks = x.ticks || (x.ticks = {});
        var pointCount = labels.length || 0;
        var overlap = global.AdwiseriAnalyticsOverlap || {};
        var maxLabelLength = typeof overlap.axisLabelMaxLength === 'function'
            ? overlap.axisLabelMaxLength(pointCount)
            : 18;
        var truncateLabel = typeof overlap.truncateLabel === 'function'
            ? overlap.truncateLabel
            : function (text) { return text; };

        xTicks.stepSize = 1;
        xTicks.autoSkip = pointCount > 18;
        xTicks.maxTicksLimit = pointCount > 24 ? 14 : (pointCount > 18 ? 16 : undefined);
        xTicks.maxRotation = pointCount > 14 ? 90 : (pointCount > 8 ? 75 : 45);
        xTicks.minRotation = pointCount > 14 ? 70 : (pointCount > 8 ? 50 : 0);
        xTicks.color = '#000000';
        xTicks.font = Object.assign({}, xTicks.font || {}, {
            size: pointCount > 16 ? 8 : (pointCount > 10 ? 9 : 10),
            weight: '600'
        });
        xTicks.callback = function (value) {
            var label = labels[value] !== undefined ? labels[value] : value;
            return truncateLabel(String(label), maxLabelLength);
        };

        var yTicks = y.ticks || (y.ticks = {});
        yTicks.color = '#000000';

        x.border = Object.assign({}, x.border || {}, {
            display: true,
            color: '#000000',
            width: 1
        });
        y.border = Object.assign({}, y.border || {}, {
            display: true,
            color: '#000000',
            width: 1
        });

        /* The stock Analytics tooltip and datalabels formatter assume plain numbers
           and would render "[object Object]" against point data, so point charts
           get their own equivalents (same Value / Percent information). */
        var plugins = options.plugins || (options.plugins = {});
        var total = sumOfValues(config);

        plugins.tooltip = Object.assign({}, plugins.tooltip, tooltipSpacingOptions({
            callbacks: completeTooltipCallbacks({
                title: function () {
                    return null;
                },
                label: function (item) {
                    var index = item.parsed && item.parsed.x !== undefined ? item.parsed.x : item.dataIndex;
                    if (labels[index] !== undefined) {
                        return String(labels[index]);
                    }
                    if (item.label) {
                        return String(item.label);
                    }
                    return item.formattedValue != null ? String(item.formattedValue) : '';
                },
                afterLabel: function () {
                    return null;
                },
                afterBody: function () {
                    return [];
                },
                labelColor: function (context) {
                    var color = resolveTooltipColor(context, labels);
                    return { borderColor: color, backgroundColor: color };
                },
                labelTextColor: function () {
                    return TOOLTIP_TEXT;
                },
                footer: function (tooltipItems) {
                    if (!tooltipItems || !tooltipItems.length) {
                        return [];
                    }
                    var value = tooltipItems[0].parsed.y;
                    var percent = total > 0 ? Math.round((value / total) * 100) : 0;
                    return [
                        'Value : ' + value,
                        'Percentage : ' + percent + ' %'
                    ];
                }
            })
        }));

        if (plugins.datalabels) {
            var pointCount = labels.length || 0;
            plugins.datalabels = Object.assign({}, plugins.datalabels, {
                display: true,
                color: '#000000',
                anchor: 'end',
                align: 'top',
                offset: 4,
                rotation: 0,
                clip: false,
                font: {
                    size: pointCount > 10 ? 9 : 11,
                    weight: '700'
                },
                formatter: function (value) {
                    return (value && typeof value === 'object') ? value.y : value;
                }
            });
        } else if (style === 'scatter' || style === 'bubble') {
            plugins.datalabels = {
                display: true,
                color: '#000000',
                anchor: 'end',
                align: 'top',
                offset: 4,
                rotation: 0,
                clip: false,
                font: { size: 10, weight: '700' },
                formatter: function (value) {
                    return (value && typeof value === 'object') ? value.y : value;
                }
            };
        }
    }

    function applyGauge(config) {
        var options = config.options || (config.options = {});

        /* Half circle, opening upward, with a wide hole — reads as a gauge. */
        options.circumference = 180;
        options.rotation = 270;
        options.cutout = '70%';

        eachDataset(config, function (ds) {
            ds.circumference = 180;
            ds.rotation = 270;
        });
    }

    /*
     * Keep chart indicators clean and borderless. Line and area datasets retain
     * their connecting stroke; only their point indicators lose the outline.
     */
    function removeIndicatorBorders(config, style) {
        eachDataset(config, function (ds) {
            ds.pointBorderWidth = 0;
            ds.pointHoverBorderWidth = 0;

            if (style !== 'line' && style !== 'area') {
                ds.borderWidth = 0;
                ds.hoverBorderWidth = 0;
            }
        });
    }

    /* Line/area series must keep a visible connecting stroke and plot room. */
    function applyLineStroke(config, style) {
        style = style || (config && (config.adwiseriStyle || config.type)) || '';
        if (style !== 'line' && style !== 'area') {
            return config;
        }

        var LINE_STROKE = '#4363d8';

        eachDataset(config, function (ds) {
            var stroke = firstColor(ds.borderColor);
            var normalized = typeof stroke === 'string' ? stroke.trim().toLowerCase() : '';
            if (!isUsableIndicatorColor(stroke)
                || normalized === '#555555'
                || normalized === '#555'
                || (typeof stroke === 'string' && stroke.indexOf('rgba(0,0,0') === 0)) {
                stroke = LINE_STROKE;
            }

            ds.showLine = true;
            ds.spanGaps = true;
            ds.clip = false;
            ds.borderColor = stroke;
            ds.borderWidth = Math.max(Number(ds.borderWidth) || 0, style === 'area' ? 2 : 3);
            ds.hoverBorderWidth = Math.max(Number(ds.hoverBorderWidth) || 0, ds.borderWidth);
            ds.pointRadius = ds.pointRadius == null ? 5 : ds.pointRadius;
            ds.pointHoverRadius = ds.pointHoverRadius == null ? 7 : ds.pointHoverRadius;
            ds.pointHitRadius = Math.max(Number(ds.pointHitRadius) || 0, 12);

            if (style === 'line') {
                ds.fill = false;
            }
        });

        var options = config.options || (config.options = {});
        options.clip = false;

        return config;
    }

    function injectTooltipStyles() {
        if (typeof document === 'undefined') {
            return;
        }
        var styleId = 'adwiseri-chart-tooltip-styles';
        if (document.getElementById(styleId)) {
            return;
        }
        var style = document.createElement('style');
        style.id = styleId;
        style.textContent = [
            '.chartjs-tooltip-footer,',
            '.chartjs-tooltip-footer tr,',
            '.chartjs-tooltip-footer td {',
            '  text-align: left !important;',
            '}',
            '.adwiseri-chart-tooltip {',
            '  position: fixed;',
            '  z-index: 99999;',
            '  pointer-events: none;',
            '  min-width: 140px;',
            '  max-width: 280px;',
            '  padding: 8px 10px;',
            '  border-radius: 8px;',
            '  background: rgba(15, 23, 42, 0.94);',
            '  color: #ffffff !important;',
            '  font: 12px/1.4 Lato, Arial, sans-serif;',
            '  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.28);',
            '  box-sizing: border-box;',
            '  white-space: normal;',
            '  word-break: break-word;',
            '  opacity: 0;',
            '  transform: translate(-8px, 12px);',
            '  transition: opacity 80ms ease;',
            '}',
            '.adwiseri-chart-tooltip-title {',
            '  font-weight: 700;',
            '  margin-bottom: 4px;',
            '}',
            '.adwiseri-chart-tooltip-row {',
            '  display: flex;',
            '  align-items: center;',
            '  gap: 8px;',
            '  color: #ffffff;',
            '}',
            '.adwiseri-chart-tooltip-swatch {',
            '  width: 11px;',
            '  height: 11px;',
            '  border-radius: 50%;',
            '  border: 1px solid rgba(255,255,255,0.7);',
            '  flex: 0 0 11px;',
            '}',
            '.adwiseri-chart-tooltip-footer {',
            '  margin-top: 6px;',
            '  padding-top: 6px;',
            '  border-top: 1px solid rgba(255,255,255,0.22);',
            '  color: #ffffff;',
            '  text-align: left;',
            '}'
        ].join('\n');
        document.head.appendChild(style);
    }

    /*
     * Rewrites a Chart.js config for one of our virtual types. Pure: the only
     * thing it touches is the config object handed to it. Unknown types pass
     * through untouched.
     */
    function adaptConfig(config) {
        if (!config) {
            return config;
        }

        if (typeof config.type === 'string') {
            var style = config.adwiseriStyle || config.type;
            if (isSupported(style)) {
                config.adwiseriStyle = style;
                config.type = baseType(style);

                if (style === 'area') {
                    applyArea(config);
                } else if (style === 'scatter' || style === 'bubble') {
                    applyPoints(config, style);
                } else if (style === 'gauge') {
                    applyGauge(config);
                }

                removeIndicatorBorders(config, style);
                applyLineStroke(config, style);
            }
        }

        applyReadableTooltip(config);
        applyHorizontalCountLabels(config);

        return config;
    }

    /*
     * Wraps window.Chart so every existing `new Chart(ctx, cfg)` gets adaptConfig
     * for free. Statics (getChart, register, defaults, ...) resolve through the
     * prototype chain to the real constructor.
     */
    function install() {
        var Real = global.Chart;

        if (typeof Real !== 'function') {
            if (global.console && global.console.warn) {
                global.console.warn('[AdwiseriCharts] Chart.js not loaded yet — Area/Scatter/Bubble/Gauge unavailable.');
            }
            return false;
        }

        if (Real.adwiseriChartTypesInstalled) {
            return true;
        }

        if (Real.defaults && Real.defaults.plugins && Real.defaults.plugins.tooltip) {
            Object.assign(Real.defaults.plugins.tooltip, tooltipSpacingOptions());
            Real.defaults.plugins.tooltip.enabled = false;
            Real.defaults.plugins.tooltip.external = renderHtmlTooltip;
            Real.defaults.plugins.tooltip.callbacks = completeTooltipCallbacks(
                Real.defaults.plugins.tooltip.callbacks
            );
        }
        if (Real.defaults && Real.defaults.plugins && Real.defaults.plugins.datalabels) {
            Real.defaults.plugins.datalabels.rotation = 0;
            Real.defaults.plugins.datalabels.clamp = true;
            Real.defaults.plugins.datalabels.clip = false;
        }
        if (Real.defaults && Real.defaults.elements && Real.defaults.elements.line) {
            Real.defaults.elements.line.borderWidth = 3;
            Real.defaults.elements.line.borderColor = '#4363d8';
            Real.defaults.elements.line.tension = 0.35;
        }
        if (Real.defaults && Real.defaults.datasets && Real.defaults.datasets.line) {
            Real.defaults.datasets.line.showLine = true;
            Real.defaults.datasets.line.borderWidth = 3;
        }

        patchTooltipIndicatorGap(Real);

        injectTooltipStyles();
        bindTooltipDismiss();

        function AdwiseriChart(item, config) {
            return new Real(item, adaptConfig(config));
        }

        AdwiseriChart.prototype = Real.prototype;
        Object.setPrototypeOf(AdwiseriChart, Real);
        AdwiseriChart.adwiseriChartTypesInstalled = true;

        global.Chart = AdwiseriChart;

        return true;
    }

    global.AdwiseriCharts = {
        adaptConfig: adaptConfig,
        baseType: baseType,
        isRadial: isRadial,
        isSupported: isSupported,
        types: types,
        install: install,
        tooltipSpacingOptions: tooltipSpacingOptions,
        tooltipAppearanceOptions: tooltipSpacingOptions,
        patchTooltipIndicatorGap: patchTooltipIndicatorGap,
        renderHtmlTooltip: renderHtmlTooltip,
        applyReadableTooltip: applyReadableTooltip,
        applyHorizontalCountLabels: applyHorizontalCountLabels,
        applyLineStroke: applyLineStroke,
        completeTooltipCallbacks: completeTooltipCallbacks
    };

    install();
})(typeof window !== 'undefined' ? window : this);

if (typeof module !== 'undefined' && module.exports) {
    module.exports = (typeof window !== 'undefined' ? window : this).AdwiseriCharts;
}
