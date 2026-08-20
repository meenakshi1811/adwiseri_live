(function (window) {
    'use strict';

    var OPTIONAL_CLIENT_VISA_FILTERS = {
        web: [
            { key: 'by_university', text: 'By University', value: 'ByClientUniversity', apiType: 'byClientUniversity', labelField: 'institution' },
            { key: 'by_course', text: 'By Course', value: 'ByClientCourse', apiType: 'byClientCourse', labelField: 'course_name' },
            { key: 'by_intake', text: 'By Intake', value: 'ByClientIntake', apiType: 'byClientIntake', labelField: 'intake' },
            { key: 'by_employer', text: 'By Employer', value: 'ByClientEmployer', apiType: 'byClientEmployer', labelField: 'employer_name' },
            { key: 'by_job_role', text: 'By Job Role', value: 'ByClientJobRole', apiType: 'byClientJobRole', labelField: 'employment_role' },
        ],
        admin: [
            { key: 'by_university', text: 'By University', value: 'By Client University', apiType: 'byClientUniversity', labelField: 'institution' },
            { key: 'by_course', text: 'By Course', value: 'By Client Course', apiType: 'byClientCourse', labelField: 'course_name' },
            { key: 'by_intake', text: 'By Intake', value: 'By Client Intake', apiType: 'byClientIntake', labelField: 'intake' },
            { key: 'by_employer', text: 'By Employer', value: 'By Client Employer', apiType: 'byClientEmployer', labelField: 'employer_name' },
            { key: 'by_job_role', text: 'By Job Role', value: 'By Client Job Role', apiType: 'byClientJobRole', labelField: 'employment_role' },
        ],
    };

    window.clientVisaChartFilters = window.clientVisaChartFilters || {
        by_university: false,
        by_course: false,
        by_intake: false,
        by_employer: false,
        by_job_role: false,
    };

    window.clientVisaChartFilterDefinitions = OPTIONAL_CLIENT_VISA_FILTERS;

    window.appendAvailableClientVisaFilters = function (langArray, mode) {
        var definitions = OPTIONAL_CLIENT_VISA_FILTERS[mode] || OPTIONAL_CLIENT_VISA_FILTERS.web;
        var availability = window.clientVisaChartFilters || {};

        definitions.forEach(function (definition) {
            if (availability[definition.key]) {
                langArray.push({
                    text: definition.text,
                    value: definition.value,
                });
            }
        });
    };

    window.fetchClientVisaChartFilters = function (subId, reportUrl) {
        if (!subId) {
            window.clientVisaChartFilters = {
                by_university: false,
                by_course: false,
                by_intake: false,
                by_employer: false,
                by_job_role: false,
            };
            return $.Deferred().resolve(window.clientVisaChartFilters).promise();
        }

        return $.ajax({
            type: 'GET',
            url: reportUrl,
            data: {
                type: 'clientVisaChartFilterAvailability',
                subid: subId,
            },
        }).then(function (response) {
            window.clientVisaChartFilters = response.data || {};
            return window.clientVisaChartFilters;
        }).catch(function () {
            window.clientVisaChartFilters = {
                by_university: false,
                by_course: false,
                by_intake: false,
                by_employer: false,
                by_job_role: false,
            };
            return window.clientVisaChartFilters;
        });
    };

    window.findClientVisaChartDefinition = function (selectedFilter, mode) {
        var definitions = OPTIONAL_CLIENT_VISA_FILTERS[mode] || OPTIONAL_CLIENT_VISA_FILTERS.web;
        return definitions.find(function (definition) {
            return definition.value === selectedFilter;
        }) || null;
    };

    window.renderClientVisaDetailChart = function (options) {
        var chartStatus = Chart.getChart('myChart');
        if (chartStatus !== undefined) {
            chartStatus.destroy();
        }

        $.ajax({
            type: 'GET',
            url: options.reportUrl,
            data: {
                type: options.apiType,
                subid: options.subId,
                country: options.country,
                startDate: options.startDate,
                endDate: options.endDate,
            },
            success: function (data) {
                if (options.checkIfDataIsEmpty(data, options.title)) {
                    return;
                }

                $('#downloadPdf').prop('disabled', false).show();

                var result = options.sortChartResult(data.data);
                var labels = [];
                var numbers = [];

                result.forEach(function (row) {
                    var label = String(row[options.labelField] || '').trim();
                    var count = Number(row.total_clients || 0);
                    if (label && count > 0) {
                        labels.push(label);
                        numbers.push(count);
                    }
                });

                var ctx = document.getElementById('myChart');
                var dynamicColors = options.generateDistinctColors(labels.length);

                new Chart(ctx, {
                    type: options.chartType,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '',
                            data: numbers,
                            borderWidth: 2,
                            borderColor: 'black',
                            backgroundColor: dynamicColors,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                },
                            },
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: options.title,
                                font: {
                                    size: 20,
                                    weight: 800,
                                },
                                padding: {
                                    bottom: 30,
                                },
                                color: 'black',
                                align: 'center',
                            },
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    padding: 12,
                                },
                            },
                            tooltip: options.buildTooltipOptions ? options.buildTooltipOptions() : {},
                        },
                    },
                });

                if (typeof options.onComplete === 'function') {
                    options.onComplete();
                }
            },
        });
    };
})(window);
