(function (window, $) {
    'use strict';

    var config = window.ReportShareConfig || {};
    var pendingShare = null;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function blobToBase64(blob) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onloadend = function () { resolve(reader.result); };
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    }

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

    function openModal(fileName) {
        var modal = document.getElementById('reportShareModal');
        var fileLabel = document.getElementById('reportShareFileName');
        if (!modal) {
            return;
        }
        if (fileLabel) {
            fileLabel.textContent = 'Attachment: ' + fileName;
        }
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        var modal = document.getElementById('reportShareModal');
        if (!modal) {
            return;
        }
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        pendingShare = null;
    }

    function sharePdfBlob(blob, fileName, source) {
        pendingShare = {
            blob: blob,
            fileName: sanitizeFileName(fileName),
            source: source || 'report',
        };
        openModal(pendingShare.fileName);
    }

    function exportDataTablePdf(dt, buttonConfig) {
        return new Promise(function (resolve, reject) {
            if (!dt || !window.pdfMake) {
                reject(new Error('PDF export is unavailable.'));
                return;
            }

            var config = $.extend(true, {}, $.fn.dataTable.ext.buttons.pdfHtml5, buttonConfig || {});
            var exportData = dt.buttons.exportData(config.exportOptions || { modifier: { page: 'all' } });
            var title = typeof config.title === 'function' ? config.title() : (config.title || 'Report');

            var doc = {
                pageSize: config.pageSize || 'A4',
                pageOrientation: config.orientation || 'portrait',
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
                        layout: config.layout || 'lightHorizontalLines',
                    },
                ],
                defaultStyle: {
                    fontSize: config.fontSize || 8,
                    alignment: 'center',
                },
                styles: {
                    title: { fontSize: 18, bold: true, alignment: 'center' },
                    tableHeader: { bold: true, fontSize: 8, color: 'black', fillColor: '#eeeeee', alignment: 'center' },
                },
            };

            if (typeof config.customize === 'function') {
                config.customize(doc, config, dt);
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

    function sendShareRequest() {
        if (!pendingShare) {
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
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
        }

        blobToBase64(pendingShare.blob).then(function (pdfData) {
            return $.ajax({
                url: config.shareUrl,
                method: 'POST',
                data: {
                    _token: config.csrfToken,
                    pdf_name: pendingShare.fileName,
                    pdf_data: pdfData,
                    recipients: recipients,
                    source: pendingShare.source,
                },
            });
        }).then(function (response) {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Shared',
                text: response.message || 'Report shared successfully.',
            });
        }).catch(function (xhr) {
            var message = 'Unable to share the report.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: message,
            });
        }).finally(function () {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send Email';
            }
        });
    }

    function injectReportShareButtons() {
        $('.reports-dt-btn-wrap').each(function () {
            var $wrap = $(this);
            if ($wrap.find('.buttons-share').length) {
                return;
            }

            var $pdfBtn = $wrap.find('.buttons-pdf').first();
            if (!$pdfBtn.length) {
                return;
            }

            var $shareBtn = $('<button type="button" class="dt-button buttons-share">Share</button>');
            $shareBtn.insertAfter($pdfBtn);
            $shareBtn.on('click', function (event) {
                event.preventDefault();
                var $table = $wrap.closest('.dataTables_wrapper').find('table.dataTable');
                if (!$table.length || !$.fn.DataTable.isDataTable($table)) {
                    return;
                }

                var dt = $table.DataTable();
                var pdfButton = null;
                dt.buttons().every(function () {
                    if ($(this.node()).hasClass('buttons-pdf')) {
                        pdfButton = this;
                    }
                });

                if (!pdfButton) {
                    return;
                }

                $shareBtn.prop('disabled', true).text('Preparing...');
                exportDataTablePdf(dt, pdfButton.conf()).then(function (result) {
                    sharePdfBlob(result.blob, result.fileName, 'report');
                }).catch(function (error) {
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: error.message || 'Unable to prepare the PDF.',
                    });
                }).finally(function () {
                    $shareBtn.prop('disabled', false).text('Share');
                });
            });
        });
    }

    function syncChartShareButton() {
        var downloadBtn = document.getElementById('downloadPdf');
        var shareBtn = document.getElementById('shareReportPdf');
        if (!downloadBtn || !shareBtn) {
            return;
        }
        shareBtn.style.display = downloadBtn.style.display;
        shareBtn.disabled = !!downloadBtn.disabled;
    }

    function bindModalEvents() {
        var modal = document.getElementById('reportShareModal');
        if (!modal) {
            return;
        }

        $('#reportShareModalClose, #reportShareCancelBtn').on('click', closeModal);
        $('#reportShareSendBtn').on('click', sendShareRequest);
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
            if (shareBtn.disabled) {
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
                shareBtn.disabled = false;
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

    $(document).ready(function () {
        bindModalEvents();
        bindAnalyticsShareButton();
        injectReportShareButtons();

        $(document).on('xhr.dt draw.dt', '.reports-module table.dataTable', function () {
            injectReportShareButtons();
            if (typeof window.syncReportExportButtons === 'function') {
                var api = $(this).DataTable();
                window.syncReportExportButtons(api);
            }
        });
    });

    window.ReportShare = {
        sharePdfBlob: sharePdfBlob,
        exportDataTablePdf: exportDataTablePdf,
        buildAnalyticsChartPdf: buildAnalyticsChartPdf,
        injectReportShareButtons: injectReportShareButtons,
        syncChartShareButton: syncChartShareButton,
    };
})(window, jQuery);
