(function (window, $) {
    'use strict';

    var config = window.ReportShareConfig || {};
    var pendingShare = null;
    var reportShareClickBound = false;
    var shareRequestInFlight = false;
    var SHARE_REQUEST_TIMEOUT_MS = 180000;

    function sanitizeFileName(name) {
        var value = String(name || 'Report').trim() || 'Report';
        value = value.replace(/[^\w\s\-\(\)\.]/g, '').trim() || 'Report';
        if (!/\.pdf$/i.test(value)) {
            value += '.pdf';
        }
        return value;
    }

    function getSelectedRecipients() {
        var values = [];
        document.querySelectorAll('.report-share-recipient:checked').forEach(function (input) {
            values.push(input.value);
        });
        return values;
    }

    function getShareModalTitle(source) {
        return source === 'analytics' ? 'Share Chart' : 'Share Report';
    }

    function resetSendButton() {
        var sendBtn = document.getElementById('reportShareSendBtn');
        if (!sendBtn) {
            return;
        }

        sendBtn.disabled = sendBtn.hasAttribute('data-share-disabled');
        sendBtn.textContent = 'Send Email';
    }

    function openModal(fileName, source) {
        var modal = document.getElementById('reportShareModal');
        var fileLabel = document.getElementById('reportShareFileName');
        var titleEl = document.getElementById('reportShareModalTitle');
        if (!modal) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Share dialog is unavailable on this page.',
            });
            return false;
        }
        if (titleEl) {
            titleEl.textContent = getShareModalTitle(source || 'report');
        }
        if (fileLabel) {
            fileLabel.textContent = 'Attachment: ' + fileName;
        }
        resetSendButton();
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        return true;
    }

    function closeModal() {
        var modal = document.getElementById('reportShareModal');
        if (!modal) {
            return;
        }
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        pendingShare = null;
        shareRequestInFlight = false;
        resetSendButton();
    }

    function sharePdfBlob(blob, fileName, source) {
        pendingShare = {
            blob: blob,
            fileName: sanitizeFileName(fileName),
            source: source || 'report',
        };
        openModal(pendingShare.fileName, pendingShare.source);
    }

    function exportDataTablePdf(dt, buttonConfig) {
        return new Promise(function (resolve, reject) {
            if (!dt || !window.pdfMake) {
                reject(new Error('PDF export is unavailable.'));
                return;
            }

            var exportConfig = $.extend(true, {}, $.fn.dataTable.ext.buttons.pdfHtml5, buttonConfig || {});
            var exportData = dt.buttons.exportData(exportConfig.exportOptions || { modifier: { page: 'all' } });
            var title = typeof exportConfig.title === 'function' ? exportConfig.title() : (exportConfig.title || 'Report');

            var doc = {
                pageSize: exportConfig.pageSize || 'A4',
                pageOrientation: exportConfig.orientation || 'portrait',
                content: [
                    {
                        text: title,
                        style: 'title',
                        alignment: 'center',
                        margin: [0, 0, 0, 12],
                    },
                    {
                        table: {
                            headerRows: 1,
                            widths: Array(exportData.header.length).fill('*'),
                            body: [exportData.header].concat(exportData.body),
                        },
                        layout: exportConfig.layout || 'lightHorizontalLines',
                    },
                ],
                defaultStyle: {
                    fontSize: exportConfig.fontSize || 8,
                    alignment: 'center',
                },
                styles: {
                    title: { fontSize: 18, bold: true, alignment: 'center' },
                    tableHeader: { bold: true, fontSize: 8, color: 'black', fillColor: '#eeeeee', alignment: 'center' },
                },
            };

            if (typeof exportConfig.customize === 'function') {
                exportConfig.customize(doc, exportConfig, dt);
            }

            pdfMake.createPdf(doc).getBlob(function (blob) {
                resolve({ blob: blob, fileName: sanitizeFileName(title + '.pdf') });
            });
        });
    }

    function buildAnalyticsChartPdf(canvasId) {
        return new Promise(function (resolve, reject) {
            var canvas = document.getElementById(canvasId || 'myChart');
            if (!canvas || !window.html2canvas || !window.jspdf) {
                reject(new Error('Chart export is unavailable.'));
                return;
            }

            var moduleTitle = $('#selectAttribute option:selected').text().trim();
            var filterTitle = $('#filters option:selected').text().trim();
            var selectedDate = $('#custom_date_picker').val() || '';
            var title = [moduleTitle, filterTitle].filter(Boolean).join(' ');
            if (selectedDate && title.indexOf('By Timeline') === -1 && title.indexOf('By Year') === -1) {
                title += ' (' + selectedDate + ')';
            }
            title = title.trim() || 'Analytics Chart';

            html2canvas(canvas).then(function (snapshot) {
                var imgData = snapshot.toDataURL('image/png');
                var jsPDF = window.jspdf.jsPDF;
                var pdf = new jsPDF({ orientation: 'portrait', unit: 'px', format: 'a4' });
                pdf.setFontSize(16);
                pdf.text(title, 20, 30);
                pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                resolve({ blob: pdf.output('blob'), fileName: sanitizeFileName(title + '.pdf') });
            }).catch(reject);
        });
    }

    function setShareButtonDisabled($btn, disabled) {
        if (!$btn || !$btn.length) {
            return;
        }

        $btn.prop('disabled', !!disabled);
        $btn.attr('aria-disabled', disabled ? 'true' : 'false');
        $btn.toggleClass('disabled', !!disabled);
    }

    function resolvePdfExportConfig(dt) {
        if (!dt || typeof dt.button !== 'function') {
            return null;
        }

        try {
            var pdfButton = dt.button('.buttons-pdf');
            if (pdfButton && typeof pdfButton.any === 'function' && pdfButton.any()) {
                return pdfButton.conf();
            }
        } catch (error) {
            // Fall through to the init-config lookup below.
        }

        try {
            var configFromButtons = null;
            dt.buttons().every(function () {
                var node = typeof this.node === 'function' ? this.node() : this;
                if ($(node).hasClass('buttons-pdf')) {
                    configFromButtons = typeof this.conf === 'function' ? this.conf() : null;
                }
            });
            if (configFromButtons) {
                return configFromButtons;
            }
        } catch (error) {
            // Fall through to the init-config lookup below.
        }

        var settings = dt.settings()[0];
        var buttons = settings && settings.oInit && settings.oInit.buttons;
        if (!Array.isArray(buttons)) {
            return null;
        }

        for (var i = 0; i < buttons.length; i++) {
            var button = buttons[i];
            var extend = button && button.extend;
            if (extend === 'pdf' || extend === 'pdfHtml5') {
                return $.extend(true, {}, $.fn.dataTable.ext.buttons.pdfHtml5, button);
            }
        }

        return null;
    }

    function extractShareErrorMessage(error, textStatus) {
        if (textStatus === 'timeout') {
            return 'The request timed out. Please try again with fewer recipients or a smaller report.';
        }

        if (error && error.responseJSON) {
            if (error.responseJSON.message) {
                return error.responseJSON.message;
            }
            if (error.responseJSON.errors) {
                var firstKey = Object.keys(error.responseJSON.errors)[0];
                if (firstKey && error.responseJSON.errors[firstKey][0]) {
                    return error.responseJSON.errors[firstKey][0];
                }
            }
        }

        if (error && error.message) {
            return error.message;
        }

        return 'Unable to share the report.';
    }

    function sendShareRequest() {
        if (!pendingShare || shareRequestInFlight) {
            return;
        }

        if (!config.shareUrl) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Share endpoint is unavailable on this page.',
            });
            return;
        }

        var recipients = getSelectedRecipients();
        if (!recipients.length) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Please select at least one staff recipient.',
            });
            return;
        }

        var sendBtn = document.getElementById('reportShareSendBtn');
        var sharePayload = pendingShare;
        var formData = new FormData();
        formData.append('_token', config.csrfToken);
        formData.append('pdf_name', sharePayload.fileName);
        formData.append('pdf_file', sharePayload.blob, sharePayload.fileName);
        formData.append('source', sharePayload.source || 'report');
        recipients.forEach(function (recipientId) {
            formData.append('recipients[]', recipientId);
        });

        shareRequestInFlight = true;
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
        }

        $.ajax({
            url: config.shareUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: SHARE_REQUEST_TIMEOUT_MS,
        }).done(function (response) {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Shared',
                text: (response && response.message) || (sharePayload.source === 'analytics' ? 'Chart shared successfully.' : 'Report shared successfully.'),
            });
        }).fail(function (xhr, textStatus) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: extractShareErrorMessage(xhr, textStatus),
            });
        }).always(function () {
            shareRequestInFlight = false;
            resetSendButton();
        });
    }

    function createShareButton() {
        return $('<button type="button" class="dt-button buttons-share buttons-html5" disabled aria-disabled="true"><span>Share</span></button>');
    }

    function injectReportShareButtons() {
        $('.reports-dt-btn-wrap').each(function () {
            var $wrap = $(this);
            var $pdfBtn = $wrap.find('.buttons-pdf').first();
            if (!$pdfBtn.length) {
                return;
            }

            var $shareBtn = $wrap.find('.buttons-share').first();
            if (!$shareBtn.length) {
                $shareBtn = createShareButton();
                setShareButtonDisabled($shareBtn, true);
                $shareBtn.insertAfter($pdfBtn);
            } else if (!$shareBtn.find('span').length) {
                $shareBtn.wrapInner('<span></span>');
            }
        });
    }

    function handleReportShareClick(event) {
        event.preventDefault();

        var $shareBtn = $(event.currentTarget);
        if ($shareBtn.prop('disabled') || $shareBtn.hasClass('disabled')) {
            return;
        }

        var $wrapper = $shareBtn.closest('.dataTables_wrapper');
        var $table = $wrapper.find('table.dataTable').first();
        if (!$table.length || !$.fn.DataTable.isDataTable($table)) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Unable to locate the report table for sharing.',
            });
            return;
        }

        var dt = $table.DataTable();
        var pdfConfig = resolvePdfExportConfig(dt);
        if (!pdfConfig) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Unable to prepare the report PDF for sharing.',
            });
            return;
        }

        setShareButtonDisabled($shareBtn, true);
        $shareBtn.find('span').first().text('Preparing...');

        exportDataTablePdf(dt, pdfConfig).then(function (result) {
            sharePdfBlob(result.blob, result.fileName, 'report');
        }).catch(function (error) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: error.message || 'Unable to prepare the PDF.',
            });
        }).finally(function () {
            $shareBtn.find('span').first().text('Share');
            if (typeof window.syncReportExportButtons === 'function') {
                window.syncReportExportButtons(dt);
            } else {
                setShareButtonDisabled($shareBtn, false);
            }
        });
    }

    function bindReportShareClick() {
        if (reportShareClickBound) {
            return;
        }

        $(document).on('click', '.reports-module button.buttons-share', handleReportShareClick);
        reportShareClickBound = true;
    }

    function syncChartShareButton() {
        var downloadBtn = document.getElementById('downloadPdf');
        var shareBtn = document.getElementById('shareReportPdf');
        if (!downloadBtn || !shareBtn) {
            return;
        }

        var $downloadBtn = $('#downloadPdf');
        var $shareBtn = $('#shareReportPdf');
        if ($downloadBtn.is(':visible')) {
            $shareBtn.show();
        } else {
            $shareBtn.hide();
        }

        var isDisabled = $downloadBtn.prop('disabled');
        setShareButtonDisabled($shareBtn, isDisabled);
    }

    function bindModalEvents() {
        var modal = document.getElementById('reportShareModal');
        if (!modal) {
            return;
        }

        $('#reportShareModalClose, #reportShareCancelBtn').on('click', closeModal);
        $('#reportShareSendBtn').on('click', function (event) {
            event.preventDefault();
            sendShareRequest();
        });
        $('#reportShareSelectAll').on('change', function () {
            var checked = this.checked;
            document.querySelectorAll('.report-share-recipient').forEach(function (input) {
                input.checked = checked;
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    function bindAnalyticsShareButton() {
        var shareBtn = document.getElementById('shareReportPdf');
        if (!shareBtn) {
            return;
        }

        shareBtn.addEventListener('click', function (event) {
            event.preventDefault();
            if (shareBtn.disabled || shareBtn.classList.contains('disabled')) {
                return;
            }

            shareBtn.disabled = true;
            shareBtn.textContent = 'Preparing...';

            buildAnalyticsChartPdf('myChart').then(function (result) {
                sharePdfBlob(result.blob, result.fileName, 'analytics');
            }).catch(function (error) {
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: error.message || 'Unable to prepare the chart PDF.',
                });
            }).finally(function () {
                shareBtn.textContent = 'Share';
                syncChartShareButton();
            });
        });

        var downloadBtn = document.getElementById('downloadPdf');
        if (downloadBtn && window.MutationObserver) {
            var observer = new MutationObserver(syncChartShareButton);
            observer.observe(downloadBtn, { attributes: true, attributeFilter: ['style', 'disabled'] });
        }
        syncChartShareButton();
    }

    function refreshReportShareButtons(api) {
        injectReportShareButtons();
        if (typeof window.syncReportExportButtons === 'function') {
            window.syncReportExportButtons(api || null);
        }
    }

    $(document).ready(function () {
        bindModalEvents();
        bindReportShareClick();
        bindAnalyticsShareButton();
        injectReportShareButtons();

        $(document).on('xhr.dt draw.dt init.dt', '.reports-module table.dataTable', function () {
            var api = null;
            if ($.fn.DataTable.isDataTable(this)) {
                api = $(this).DataTable();
            }
            refreshReportShareButtons(api);
        });
    });

    window.ReportShare = {
        sharePdfBlob: sharePdfBlob,
        exportDataTablePdf: exportDataTablePdf,
        buildAnalyticsChartPdf: buildAnalyticsChartPdf,
        injectReportShareButtons: injectReportShareButtons,
        syncChartShareButton: syncChartShareButton,
        setShareButtonDisabled: setShareButtonDisabled,
        refreshReportShareButtons: refreshReportShareButtons,
    };
})(window, jQuery);
