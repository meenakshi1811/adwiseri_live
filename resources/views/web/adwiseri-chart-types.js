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
        xTicks.stepSize = 1;
        xTicks.autoSkip = false;
        xTicks.color = '#000000';
        xTicks.callback = function (value) {
            return labels[value] !== undefined ? labels[value] : '';
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

        plugins.tooltip = Object.assign({}, plugins.tooltip, {
            callbacks: {
                title: function () {
                    return '';
                },
                label: function (item) {
                    var index = item.parsed && item.parsed.x !== undefined ? item.parsed.x : item.dataIndex;
                    return labels[index] !== undefined ? String(labels[index]) : '';
                },
                afterLabel: function (item) {
                    var value = item.parsed.y;
                    var percent = total > 0 ? Math.round((value / total) * 100) : 0;
                    return ['Value : ' + value, 'Percentage : ' + percent + ' %'];
                }
            }
        });

        if (plugins.datalabels) {
            var pointCount = labels.length || 0;
            var rotateVertical = false;
            if (pointCount > 18) {
                rotateVertical = true;
            } else if (pointCount > 14) {
                var longValues = 0;
                eachDataset(config, function (ds) {
                    if (!Array.isArray(ds.data)) {
                        return;
                    }
                    ds.data.forEach(function (point) {
                        var value = (point && typeof point === 'object') ? point.y : point;
                        if (String(value == null ? '' : value).length >= 4) {
                            longValues++;
                        }
                    });
                });
                rotateVertical = longValues >= Math.ceil(pointCount / 2);
            }
            plugins.datalabels = Object.assign({}, plugins.datalabels, {
                display: true,
                color: '#000000',
                anchor: 'end',
                align: rotateVertical ? 'right' : 'top',
                offset: rotateVertical ? 8 : 6,
                rotation: rotateVertical ? -90 : 0,
                font: {
                    size: rotateVertical ? 9 : (pointCount > 10 ? 10 : 12),
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
                offset: 6,
                rotation: 0,
                font: { size: 12, weight: '700' },
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

    /*
     * Rewrites a Chart.js config for one of our virtual types. Pure: the only
     * thing it touches is the config object handed to it. Unknown types pass
     * through untouched.
     */
    function adaptConfig(config) {
        if (!config || typeof config.type !== 'string' || !isSupported(config.type)) {
            return config;
        }

        var style = config.type;
        config.type = baseType(style);
        config.adwiseriStyle = style;

        if (style === 'area') {
            applyArea(config);
        } else if (style === 'scatter' || style === 'bubble') {
            applyPoints(config, style);
        } else if (style === 'gauge') {
            applyGauge(config);
        }

        removeIndicatorBorders(config, style);

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
        install: install
    };

    install();
})(typeof window !== 'undefined' ? window : this);

if (typeof module !== 'undefined' && module.exports) {
    module.exports = (typeof window !== 'undefined' ? window : this).AdwiseriCharts;
}
