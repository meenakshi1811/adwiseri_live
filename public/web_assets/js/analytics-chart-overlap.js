/*
 * Analytics chart overlap fixes for long category labels and large numbers.
 * Loaded after Chart.js and adwiseri-chart-types.js; enhanced from analytics blades.
 */
(function (global) {
    'use strict';

    var RADIAL_TYPES = ['pie', 'doughnut', 'gauge'];

    function chartStyle(config) {
        return String((config && (config.adwiseriStyle || config.type)) || '').toLowerCase();
    }

    function isRadial(config) {
        return RADIAL_TYPES.indexOf(chartStyle(config)) !== -1;
    }

    function pointCount(config) {
        var labels = (config.data && config.data.labels) ? config.data.labels : [];
        var datasets = (config.data && config.data.datasets) ? config.data.datasets : [];
        var dataLen = 0;

        if (datasets[0] && Array.isArray(datasets[0].data)) {
            dataLen = datasets[0].data.length;
        }

        return Math.max(labels.length, dataLen, 0);
    }

    function rawValue(value) {
        if (value && typeof value === 'object') {
            if (value.y != null) {
                return value.y;
            }
            if (value.x != null) {
                return value.x;
            }
        }
        return value;
    }

    function truncateLabel(text, maxLength) {
        var value = String(text == null ? '' : text).trim();
        if (!value || !maxLength || value.length <= maxLength) {
            return value;
        }

        if (maxLength <= 3) {
            return value.slice(0, maxLength);
        }

        return value.slice(0, maxLength - 1) + '…';
    }

    function axisLabelMaxLength(count) {
        if (count <= 4) {
            return 28;
        }
        if (count <= 6) {
            return 22;
        }
        if (count <= 10) {
            return 18;
        }
        if (count <= 14) {
            return 14;
        }
        if (count <= 20) {
            return 11;
        }
        return 9;
    }

    function formatCompactNumber(value, decimals) {
        var number = Number(value);
        if (!isFinite(number)) {
            return String(value == null ? '' : value);
        }

        var abs = Math.abs(number);
        var places = typeof decimals === 'number' ? decimals : 1;

        if (abs >= 1000000000) {
            return trimTrailingZero((number / 1000000000).toFixed(places)) + 'B';
        }
        if (abs >= 1000000) {
            return trimTrailingZero((number / 1000000).toFixed(places)) + 'M';
        }
        if (abs >= 10000) {
            return trimTrailingZero((number / 1000).toFixed(places)) + 'K';
        }
        if (abs >= 1000) {
            return trimTrailingZero((number / 1000).toFixed(Math.min(places, 2))) + 'K';
        }

        if (Number.isInteger(number)) {
            return String(number);
        }

        return trimTrailingZero(number.toFixed(Math.min(places, 2)));
    }

    function trimTrailingZero(text) {
        return String(text).replace(/\.0+$/, '');
    }

    function shouldRotateDataLabels(config, count) {
        /* Keep value counts horizontal for readability across dashboard and analytics. */
        return false;
    }

    function enhanceAxes(config, count) {
        if (isRadial(config) || !config.options || !config.options.scales) {
            return;
        }

        var rotateVertical = shouldRotateDataLabels(config, count);
        var scales = config.options.scales;
        var labels = (config.data && config.data.labels) ? config.data.labels : [];
        var maxLabelLength = axisLabelMaxLength(count);

        Object.keys(scales).forEach(function (scaleId) {
            var scale = scales[scaleId];
            if (!scale || scale.display === false) {
                return;
            }

            if (scaleId === 'x' || scale.axis === 'x') {
                var xTicks = scale.ticks || (scale.ticks = {});
                var isCategory = !scale.type || scale.type === 'category';

                xTicks.maxRotation = count > 14 ? 90 : (count > 8 ? 75 : (count > 4 ? 60 : 45));
                xTicks.minRotation = count > 14 ? 70 : (count > 8 ? 50 : (count > 4 ? 35 : 0));
                xTicks.autoSkip = count > 18;
                xTicks.maxTicksLimit = count > 24 ? 14 : (count > 18 ? 16 : undefined);
                xTicks.font = Object.assign({}, xTicks.font || {}, {
                    size: count > 16 ? 8 : (count > 10 ? 9 : 10),
                    weight: '600'
                });

                if (isCategory) {
                    xTicks.callback = function (value, index) {
                        var label = labels[index];
                        if (label === undefined) {
                            label = value;
                        }
                        return truncateLabel(label, maxLabelLength);
                    };
                } else if (typeof xTicks.callback === 'function') {
                    var originalXCallback = xTicks.callback;
                    xTicks.callback = function (value, index, ticks) {
                        var label = originalXCallback.call(this, value, index, ticks);
                        return truncateLabel(label, maxLabelLength);
                    };
                }

                config.options.layout = config.options.layout || {};
                config.options.layout.padding = Object.assign(
                    {},
                config.options.layout.padding || {},
                {
                    top: rotateVertical ? 28 : 20,
                    right: rotateVertical ? 24 : 16,
                    bottom: count > 14 ? 10 : (count > 8 ? 8 : 6)
                }
            );
            }

            if (scaleId === 'y' || scale.axis === 'y') {
                var yTicks = scale.ticks || (scale.ticks = {});
                if (!config._formatByteValues) {
                    var originalYCallback = yTicks.callback;
                    yTicks.callback = function (value, index, ticks) {
                        var formatted = typeof originalYCallback === 'function'
                            ? originalYCallback.call(this, value, index, ticks)
                            : value;
                        if (Math.abs(Number(formatted)) >= 1000 || Math.abs(Number(value)) >= 1000) {
                            return formatCompactNumber(Number(formatted) || Number(value) || 0, 1);
                        }
                        return formatted;
                    };
                }
                yTicks.font = Object.assign({}, yTicks.font || {}, { size: 10, weight: '600' });
            }
        });
    }

    function enhanceDataLabels(config, count) {
        var plugins = config.options.plugins || (config.options.plugins = {});
        var datalabels = plugins.datalabels || (plugins.datalabels = {});
        var style = chartStyle(config);
        var isPointChart = style === 'line' || style === 'scatter' || style === 'area' || style === 'bubble';
        var rotateVertical = shouldRotateDataLabels(config, count);
        var showLabels = datalabels.display !== false;

        if (isRadial(config)) {
            showLabels = count > 0 && count <= 10;
            rotateVertical = false;
        } else if (isPointChart || style === 'bar') {
            showLabels = count > 0;
        } else {
            showLabels = count > 0;
        }

        var originalFormatter = datalabels.formatter;
        datalabels.display = showLabels;
        datalabels.clamp = true;
        datalabels.clip = false;
        datalabels.anchor = isRadial(config) ? 'center' : 'end';
        datalabels.align = isRadial(config) ? 'center' : 'top';
        datalabels.offset = rotateVertical ? 8 : 4;
        datalabels.rotation = 0;
        datalabels.font = Object.assign({}, datalabels.font || {}, {
            size: count > 10 ? 9 : 11,
            weight: '700'
        });
        datalabels.formatter = function (value, context) {
            var formatted = value;
            if (typeof originalFormatter === 'function') {
                formatted = originalFormatter(value, context);
            } else if (value && typeof value === 'object') {
                formatted = value.y != null ? value.y : value;
            }

            if (config._formatByteValues) {
                return formatted;
            }

            var numeric = Number(formatted);
            if (isFinite(numeric) && Math.abs(numeric) >= 1000) {
                return formatCompactNumber(numeric, 1);
            }

            return formatted;
        };

        if (showLabels) {
            config.options.layout = config.options.layout || {};
            config.options.layout.padding = Object.assign(
                {},
                config.options.layout.padding || {},
                {
                    top: rotateVertical ? 36 : 28,
                    right: rotateVertical ? 28 : 18
                }
            );
        }
    }

    function enhanceLegend(config, count) {
        var legend = config.options.plugins && config.options.plugins.legend;
        if (!legend || !legend.labels) {
            return;
        }

        var labels = legend.labels;
        var maxLegendLength = count > 14 ? 34 : (count > 8 ? 42 : 52);
        var originalGenerate = labels.generateLabels;

        labels.padding = count > 12 ? 4 : 6;
        labels.font = Object.assign({}, labels.font || {}, {
            size: count > 12 ? 9 : 10,
            weight: '500'
        });

        if (typeof originalGenerate === 'function') {
            labels.generateLabels = function (chart) {
                return originalGenerate(chart).map(function (item) {
                    return Object.assign({}, item, {
                        text: ' ' + truncateLabel(String(item.text || '').trim(), maxLegendLength),
                        pointStyle: item.pointStyle || 'circle',
                        fillStyle: item.fillStyle,
                        strokeStyle: item.strokeStyle,
                        fontColor: item.fontColor || '#000000',
                        color: item.color || '#000000',
                        lineWidth: item.lineWidth || 1
                    });
                });
            };
        }
    }

    function enhanceTooltip(config) {
        var plugins = config.options.plugins || (config.options.plugins = {});
        var tooltip = plugins.tooltip || (plugins.tooltip = {});
        var appearance = (global.AdwiseriCharts && typeof global.AdwiseriCharts.tooltipAppearanceOptions === 'function')
            ? global.AdwiseriCharts.tooltipAppearanceOptions()
            : {
                enabled: false,
                backgroundColor: 'rgba(15, 23, 42, 0.94)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                footerColor: '#ffffff',
                bodyAlign: 'left',
                footerAlign: 'left'
            };

        Object.assign(tooltip, appearance);
        tooltip.enabled = false;
        if (global.AdwiseriCharts && typeof global.AdwiseriCharts.renderHtmlTooltip === 'function') {
            tooltip.external = global.AdwiseriCharts.renderHtmlTooltip;
        }
        if (global.AdwiseriCharts && typeof global.AdwiseriCharts.completeTooltipCallbacks === 'function') {
            tooltip.callbacks = global.AdwiseriCharts.completeTooltipCallbacks(tooltip.callbacks);
        }
    }

    function enhance(config) {
        if (!config || !config.options) {
            return config;
        }

        var count = pointCount(config);
        enhanceAxes(config, count);
        enhanceDataLabels(config, count);
        enhanceLegend(config, count);
        enhanceTooltip(config);

        if (global.AdwiseriCharts && typeof global.AdwiseriCharts.applyHorizontalCountLabels === 'function') {
            global.AdwiseriCharts.applyHorizontalCountLabels(config);
        }
        if (global.AdwiseriCharts && typeof global.AdwiseriCharts.applyLineStroke === 'function') {
            global.AdwiseriCharts.applyLineStroke(config, chartStyle(config));
        }

        return config;
    }

    global.AdwiseriAnalyticsOverlap = {
        enhance: enhance,
        truncateLabel: truncateLabel,
        formatCompactNumber: formatCompactNumber,
        axisLabelMaxLength: axisLabelMaxLength
    };
})(typeof window !== 'undefined' ? window : this);
