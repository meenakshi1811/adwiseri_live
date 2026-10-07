<script>
    function onClickInvoicesAP() {

    // This arrangement can be altered based on how we want the date's format to appear.
    var result = getStartAndEndDate('InvoiceAP');
    let currentDate = `${result.startDate} - ${result.endDate}`;

    deativeTabs();
    invoiceAPTable1 = true;

    var invoiceTable = $('#invoicesAPTable1').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        "lengthMenu": [
            [10, 20, 50, -1],
            [10, 20, 50, "All"]
        ],
        "pageLength": 10,
        dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip', // Custom layout
        "buttons": [{
                extend: 'csv',
                title: 'Report : Invoices  (' + currentDate + ')', // Custom title for CS
                exportOptions: {
                    modifier: {
                        page: 'all' // Export all data, not just the current page
                    }
                }
            },
            {
                extend: 'excel',
                title: 'Report : Invoices  (' + currentDate + ')', // Custom title for Exce
                exportOptions: {
                    modifier: {
                        page: 'all' // Export all data, not just the current page
                    }
                }
            },
            {
                extend: 'pdf',
                title: 'Report : Invoices  (' + currentDate + ')', // Custom title for Exce
                orientation: 'landscape', // Makes table fit better
                pageSize: 'A4',
                customize: function(doc) {
                    let table = doc.content[1].table;
                    let columnCount = table.body[0].length;

                    table.widths = [
                        '5%',
                        '*',
                        '10%',
                        '*', // Column 1 (3% width)
                        '7%',
                        '7%', // Column 3 (2% width)
                        '7%', // Column 5 (2% width)
                        '7%', // Column 7 (2% width)
                        '8%',
                        '9%', // Column 7 (2% width)
                        '10%' // Column 8 (3% width)
                    ];

                    doc.defaultStyle.fontSize = 10; // Font size for table data
                    doc.styles.tableHeader.fontSize = 12; // Larger header font
                    doc.styles.title.fontSize = 18; // Title font
                    doc.pageMargins = [10, 10, 10, 10]; // Reduce margins
                    doc.styles.title.bold = true;

                    // Force full-page width usage
                    doc.content[1].layout = {
                        hLineWidth: function(i, node) {
                            return 0.5; // Line thickness
                        },
                        vLineWidth: function(i, node) {
                            return 0.5;
                        },
                        paddingLeft: function(i, node) {
                            return 4; // Adjust left padding
                        },
                        paddingRight: function(i, node) {
                            return 4;
                        }
                    };

                    // Center-align all table content
                    doc.content[1].table.body.forEach(function(row, rowIndex) {
                        row.forEach(function(cell, cellIndex) {
                            if (typeof cell === 'object') {
                                cell.alignment = 'center'; // Center-align text in each cell
                            }
                        });
                    });

                    // Center-align the title
                    doc.content[0].alignment = 'center';
                },
                exportOptions: {
                    columns: ':visible',
                    modifier: {
                        page: 'all' // Export all data, not just the current page
                    }
                }
            }
        ],
        ajax: {
            url: "{{ route('manage_reports_invoices_ap') }}",
            data: function(d) {
                // Add additional data here
                d.startDate = result.startDate;
                d.endDate = result.endDate;

            },
            dataSrc: function(json) {
                if (json.data.length === 0) {
                    // Swal.fire({
                    //     icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                    //     title: 'Oops!',
                    //     text: 'No Data found for Chart :Invoices  (' + currentDate + ')',
                    //     confirmButtonText: 'OK'
                    // });
                }
                return json.data;
            }
        },
        language: {
            emptyTable: 'No Data found for Chart : Invoices  (' + currentDate + ')',
        },
        initComplete: function(settings, json) {
            syncReportExportButtons(this.api());
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'to_name',
                name: 'to_name'
            },
            {
                data: 'to_phone',
                name: 'to_phone'
            },
            {
                data: 'to_email',
                name: 'to_email'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'discount',
                name: 'discount'
            },
            {
                data: 'tax',
                name: 'tax'
            },
            {
                data: 'total',
                name: 'total'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'due_date',
                name: 'due_date'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ],
        order: [1, 'asc']

    });
    }

    function onchangeInvoicesReport(type, text) {
        let selectedText = text;
        let columns = [];

        if (type === '') {
            // If empty, hide the table and the report title
            $('#reportInvoices').hide(); // Hide the table

            return; // Exit the function
        }
        $('#reportInvoices').show();
        // Ensure DataTable is properly destroyed and HTML table is cleared
        if ($.fn.DataTable.isDataTable('#reportInvoiceTable')) {
            $('#reportInvoiceTable').DataTable().clear().destroy();
            $('#reportInvoiceTable').empty(); // Remove existing columns and rows
        }
        var result = getStartAndEndDate('InvoiceAP');
        let currentDate = `${result.startDate} - ${result.endDate}`;
        $('#reportInvoices').show();
        let dataTableSettings = {
            processing: true,
            serverSide: true,
            searching: true, // Disable the search box
            // Disable the "Showing x to y of z entries" message
            "lengthMenu": [
                [10, 20, 50, -1],
                [10, 20, 50, "All"]
            ],
            "pageLength": 10,
            destroy: true,
            dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip', // Custom layout
            buttons: [{
                    extend: 'csv',
                    title: 'Report : Invoices  ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for CSV
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'excel',
                    title: 'Report : Invoices  ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for Excel
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'pdf',
                    title: 'Report : Invoices  ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for Excel
                    orientation: 'landscape', // Makes table fit better
                    pageSize: 'A4',
                    customize: function(doc) {
                        let table = doc.content[1].table;
                        let columnCount = table.body[0].length;

                        // Dynamically set column widths to distribute space evenly
                        // Dynamically set column widths to distribute space evenly
                        table.widths = ['8%', ...Array(columnCount - 1).fill('*')];

                        // Adjust styles
                        doc.defaultStyle.fontSize = 10; // Font size for table data
                        doc.styles.tableHeader.fontSize = 12; // Larger header font
                        doc.styles.title.fontSize = 18; // Title font
                        doc.pageMargins = [10, 10, 10, 10]; // Reduce margins
                        doc.styles.title.bold = true;
                        // Force full-page width usage
                        doc.content[1].layout = {
                            hLineWidth: function(i, node) {
                                return 0.5; // Line thickness
                            },
                            vLineWidth: function(i, node) {
                                return 0.5;
                            },
                            paddingLeft: function(i, node) {
                                return 4; // Adjust left padding
                            },
                            paddingRight: function(i, node) {
                                return 4;
                            }
                        };

                        // Center-align all table content
                        doc.content[1].table.body.forEach(function(row, rowIndex) {
                            row.forEach(function(cell, cellIndex) {
                                if (typeof cell === 'object') {
                                    cell.alignment = 'center'; // Center-align text in each cell
                                }
                            });
                        });

                        // Center-align the title
                        doc.content[0].alignment = 'center';
                    },
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                }
            ],
            ajax: {
                url: "{{ route('invoicesReport') }}",
                data: function(d) {
                    d.type = type;
                    d.startDate = result.startDate;
                    d.endDate = result.endDate
                },
                dataSrc: function(json) {
                    if (json.data.length === 0) {
                        // Swal.fire({
                        //     icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        //     title: 'Oops!',
                        //     text: 'No Data found for Chart : Invoices  ' + selectedText + ' (' + currentDate + ')',
                        //     confirmButtonText: 'OK'
                        // });
                    }
                    return json.data;
                }
            },
            language: {
                emptyTable: 'No Data found for Chart : Invoices  ' + selectedText + ' (' + currentDate + ')',
            },
            initComplete: function(settings, json) {
                syncReportExportButtons(this.api());
            },



        };
        // Configure DataTable based on report type
        if (type == 'byAmount') {
            $('#clientReportTitle2').html('Invoices (AR) By Amount');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Amount Range",
                    data: 'amount_range',
                    name: 'amount_range'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
            dataTableSettings.order = [
                [1, 'asc']
            ]
        } else if (type == 'byType') {
            $('#clientReportTitle2').html('Invoices (AR) By Type');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Invoice Type",
                    data: 'status',
                    name: 'status'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byClient') {
            $('#clientReportTitle2').html(`Invoices (AR) By Client's Home Country`);
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Client Country",
                    data: 'country',
                    name: 'country'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byVisaCountry') {
            $('#clientReportTitle2').html("Invoices (AR) By Visa Country's Count");
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Visa Country",
                    data: 'to_country',
                    name: 'to_country'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byTimeLine') {
            $('#clientReportTitle2').html(`Invoices (AR) By Timeline (Duration)`);
            // reportTitle = 'Invoices By Timeline';
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Timeline (Duration)",
                    data: 'year',
                    name: 'year',

                },
                {
                    title: "No of Invoices",
                    data: 'count',
                    name: 'count',

                }
            ];
            dataTableSettings.order = [
                // []
            ]
        }  else if (type == 'yearly') {
            $('#clientReportTitle2').html('Invoices (AR) By Year');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Year",
                    data: 'year',
                    name: 'year'
                },
                {
                    title: "Count",
                    data: 'year_count',
                    name: 'year_count'
                }
            ];
        }

        $('#custom_date_picker6').prop('disabled', type == 'byTimeLine' || type == 'yearly');
        // Initialize the DataTable with the new settings
        $('#reportInvoiceTable').DataTable(dataTableSettings);
    }

    function onchangeInvoicesAPReport(type, text) {
        let selectedText = text;
        let columns = [];

        if (type === '') {
            // If empty, hide the table and the report title
            $('#reportInvoicesAP').hide(); // Hide the table

            return; // Exit the function
        }
        $('#reportInvoicesAP').show();
        // Ensure DataTable is properly destroyed and HTML table is cleared
        if ($.fn.DataTable.isDataTable('#reportInvoiceAPTable')) {
            $('#reportInvoiceAPTable').DataTable().clear().destroy();
            $('#reportInvoiceAPTable').empty(); // Remove existing columns and rows
        }
        var result = getStartAndEndDate('InvoiceAP');
        let currentDate = `${result.startDate} - ${result.endDate}`;
        $('#reportInvoicesAP').show();
        let dataTableSettings = {
            processing: true,
            serverSide: true,
            searching: true, // Disable the search box
            // Disable the "Showing x to y of z entries" message
            "lengthMenu": [
                [10, 20, 50, -1],
                [10, 20, 50, "All"]
            ],
            "pageLength": 10,
            destroy: true,
            dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip', // Custom layout
            buttons: [{
                    extend: 'csv',
                    title: 'Report : Invoices  ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for CSV
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'excel',
                    title: 'Report : Invoices  ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for Excel
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'pdf',
                    title: 'Report : Invoices  ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for Excel
                    orientation: 'landscape', // Makes table fit better
                    pageSize: 'A4',
                    customize: function(doc) {
                        let table = doc.content[1].table;
                        let columnCount = table.body[0].length;

                        // Dynamically set column widths to distribute space evenly
                        // Dynamically set column widths to distribute space evenly
                        table.widths = ['8%', ...Array(columnCount - 1).fill('*')];

                        // Adjust styles
                        doc.defaultStyle.fontSize = 10; // Font size for table data
                        doc.styles.tableHeader.fontSize = 12; // Larger header font
                        doc.styles.title.fontSize = 18; // Title font
                        doc.pageMargins = [10, 10, 10, 10]; // Reduce margins
                        doc.styles.title.bold = true;
                        // Force full-page width usage
                        doc.content[1].layout = {
                            hLineWidth: function(i, node) {
                                return 0.5; // Line thickness
                            },
                            vLineWidth: function(i, node) {
                                return 0.5;
                            },
                            paddingLeft: function(i, node) {
                                return 4; // Adjust left padding
                            },
                            paddingRight: function(i, node) {
                                return 4;
                            }
                        };

                        // Center-align all table content
                        doc.content[1].table.body.forEach(function(row, rowIndex) {
                            row.forEach(function(cell, cellIndex) {
                                if (typeof cell === 'object') {
                                    cell.alignment = 'center'; // Center-align text in each cell
                                }
                            });
                        });

                        // Center-align the title
                        doc.content[0].alignment = 'center';
                    },
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                }
            ],
            ajax: {
                url: "{{ route('invoicesReport_ap') }}",
                data: function(d) {
                    d.type = type;
                    d.startDate = result.startDate;
                    d.endDate = result.endDate
                },
                dataSrc: function(json) {
                    if (json.data.length === 0) {
                        // Swal.fire({
                        //     icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        //     title: 'Oops!',
                        //     text: 'No Data found for Chart : Invoices  ' + selectedText + ' (' + currentDate + ')',
                        //     confirmButtonText: 'OK'
                        // });
                    }
                    return json.data;
                }
            },
            language: {
                emptyTable: 'No Data found for Chart : Invoices  ' + selectedText + ' (' + currentDate + ')',
            },
            initComplete: function(settings, json) {
                syncReportExportButtons(this.api());
            },



        };
        // Configure DataTable based on report type
        if (type == 'byAmount') {
            $('#clientReportAPTitle2').html('Invoices (AP) By Amount');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Amount Range",
                    data: 'amount_range',
                    name: 'amount_range'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
            dataTableSettings.order = [
                [1, 'asc']
            ]
        } else if (type == 'byType') {
            $('#clientReportAPTitle2').html('Invoices (AP) By Type');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Invoice Type",
                    data: 'status',
                    name: 'status'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byClient') {
            $('#clientReportAPTitle2').html(`Invoices (AP) By Client's Home Country`);
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Client Country",
                    data: 'country',
                    name: 'country'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byVisaCountry') {
            $('#clientReportAPTitle2').html("Invoices (AP) By Visa Country's Count");
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Visa Country",
                    data: 'to_country',
                    name: 'to_country'
                },
                {
                    title: "No. of Invoices",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byTimeLine') {
            $('#clientReportAPTitle2').html(`Invoices (AP) By Timeline (Duration)`);
            // reportTitle = 'Invoices By Timeline';
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Timeline (Duration)",
                    data: 'year',
                    name: 'year',

                },
                {
                    title: "No of Invoices",
                    data: 'count',
                    name: 'count',

                }
            ];
            dataTableSettings.order = [
                // []
            ]
        }  else if (type == 'yearly') {
            $('#clientReportAPTitle2').html('Invoices (AP) By Year');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Year",
                    data: 'year',
                    name: 'year'
                },
                {
                    title: "Count",
                    data: 'year_count',
                    name: 'year_count'
                }
            ];
        }

        $('#custom_date_picker66').prop('disabled', type == 'byTimeLine' || type == 'yearly');
        // Initialize the DataTable with the new settings
        $('#reportInvoiceAPTable').DataTable(dataTableSettings);
    }

    function onClickPaymentsAP() {

        var result = getStartAndEndDate('PaymentAP');
        // This arrangement can be altered based on how we want the date's format to appear.
        let currentDate = `${result.startDate} - ${result.endDate}`;

        deativeTabs();
        paymentsAPTable1 = true;

        var paymentAPTable1 = $('#paymentsAPTable1').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            "lengthMenu": [
                [10, 20, 50, -1],
                [10, 20, 50, "All"]
            ],
            "pageLength": 10,
            order: [
                [0, 'desc']
            ],
            dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip', // Custom layout
            "buttons": [{
                    extend: 'csv',
                    title: 'Report : Payments (AP)   (' + currentDate + ')', // Custom title for CS
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'excel',
                    title: 'Report : Payments (AP)   (' + currentDate + ')', // Custom title for Exce
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'pdf',
                    title: 'Report : Payments (AP)   (' + currentDate + ')', // Custom title for Exce
                    orientation: 'landscape', // Makes table fit better
                    pageSize: 'A4',
                    customize: function(doc) {
                        let table = doc.content[1].table;
                        let columnCount = table.body[0].length;

                        // Dynamically set column widths to distribute space evenly
                        table.widths = [
                            '5%',
                            '*',
                            '*',
                            '5%', // Column 1 (3% width)
                            '10%',
                            '10%', // Column 3 (2% width)
                            '7%', // Column 5 (2% width)
                            '7%', // Column 7 (2% width)
                            // '7%',
                            // '7%', // Column 7 (2% width)
                            // '7%',
                            // '8%',
                            '8%' // Column 8 (3% width)
                        ];

                        doc.defaultStyle.fontSize = 10; // Font size for table data
                        doc.styles.tableHeader.fontSize = 12; // Larger header font
                        doc.styles.title.fontSize = 18; // Title font
                        doc.pageMargins = [10, 10, 10, 10]; // Reduce margins
                        doc.styles.title.bold = true;

                        // Force full-page width usage
                        doc.content[1].layout = {
                            hLineWidth: function(i, node) {
                                return 0.5; // Line thickness
                            },
                            vLineWidth: function(i, node) {
                                return 0.5;
                            },
                            paddingLeft: function(i, node) {
                                return 4; // Adjust left padding
                            },
                            paddingRight: function(i, node) {
                                return 4;
                            }
                        };

                        // Center-align all table content
                        doc.content[1].table.body.forEach(function(row, rowIndex) {
                            row.forEach(function(cell, cellIndex) {
                                if (typeof cell === 'object') {
                                    cell.alignment = 'center'; // Center-align text in each cell
                                }
                            });
                        });

                        // Center-align the title
                        doc.content[0].alignment = 'center';
                    },
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                }
            ],
            ajax: {
                url: "{{ route('manage_report_payments') }}",
                data: function(d) {
                    // Add additional data here
                    d.startDate = result.startDate;
                    d.endDate = result.endDate;
                    d.type = 'ap'

                },
                dataSrc: function(json) {
                    if (json.data.length === 0) {
                        // Swal.fire({
                        //     icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        //     title: 'Oops!',
                        //     text: 'No Data found for Chart : Payments  (' + currentDate + ')',
                        //     confirmButtonText: 'OK'
                        // });
                    }
                    return json.data;
                }
            },
            language: {
                emptyTable: 'No Data found for Chart : Payments (AP)   (' + currentDate + ')',
            },
            initComplete: function(settings, json) {
                syncReportExportButtons(this.api());
            },
            columns: [
                // { data: 'DT_RowIndex', name: 'DT_RowIndex', title: '#', orderable: false, searchable: false },
                {
                    data: 'DT_RowIndex', // Index column
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,

                },
                // {
                //     data: 'client',
                //     name: 'client'
                // },
                {
                    data: 'service_provider',
                    name: 'service_provider'
                },
                // {
                //     data: 'type',
                //     name: 'type'
                // },
                // {
                //     data: 'service_provider',
                //     name: 'service_provider'
                // },
                {
                    data: 'service_taken',
                    name: 'service_taken'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'paid_amount',
                    name: 'paid_amount'
                },
                {
                    data: 'amount_to_pay',
                    name: 'amount_to_pay'
                },
                {
                    data: 'payment_date',
                    name: 'payment_date'
                },
                // {
                //     data: 'invoice_no',
                //     name: 'invoice_no'
                // },
                {
                    data: 'created_at',
                    name: 'created_at'
                }

                //{ data: 'action', name: 'action' },

            ],


        });
    }

    function onchangePaymentsAPReport(type, text) {
        let selectedText = text;

        if (type === '') {
            // If empty, hide the table and the report title
            $('#reportAPPayments').hide(); // Hide the table

            return; // Exit the function
        }
        // Ensure DataTable is properly destroyed and HTML table is cleared
        if ($.fn.DataTable.isDataTable('#reportPaymentAPTable')) {
            $('#reportPaymentAPTable').DataTable().clear().destroy();
            $('#reportPaymentAPTable').empty(); // Remove existing columns and rows
        }
        var result = getStartAndEndDate('PaymentAP');
        // This arrangement can be altered based on how we want the date's format to appear.
        let currentDate = `${result.startDate} - ${result.endDate}`;
        let columns = [];
        $('#reportAPPayments').show();
        let dataTableSettings = {
            processing: true,
            serverSide: true,
            searching: true, // Disable the search box
            // Disable the "Showing x to y of z entries" message
            "lengthMenu": [
                [10, 20, 50, -1],
                [10, 20, 50, "All"]
            ],
            "pageLength": 10,
            destroy: true,
            dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip', // Custom layout
            buttons: [{
                    extend: 'csv',
                    title: 'Report : Payments (AP) ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for CSV
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'excel',
                    title: 'Report : Payments (AP) ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for Excel
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                },
                {
                    extend: 'pdf',
                    title: 'Report : Payments (AP) ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''), // Custom title for Excel
                    orientation: 'landscape', // Makes table fit better
                    pageSize: 'A4',
                    customize: function(doc) {
                        let table = doc.content[1].table;
                        let columnCount = table.body[0].length;

                        // Dynamically set column widths to distribute space evenly
                        table.widths = ['8%', ...Array(columnCount - 1).fill('*')];

                        // Adjust styles
                        doc.defaultStyle.fontSize = 10; // Font size for table data
                        doc.styles.tableHeader.fontSize = 12; // Larger header font
                        doc.styles.title.fontSize = 18; // Title font
                        doc.pageMargins = [10, 10, 10, 10]; // Reduce margins
                        doc.styles.title.bold = true;

                        // Force full-page width usage
                        doc.content[1].layout = {
                            hLineWidth: function(i, node) {
                                return 0.5; // Line thickness
                            },
                            vLineWidth: function(i, node) {
                                return 0.5;
                            },
                            paddingLeft: function(i, node) {
                                return 4; // Adjust left padding
                            },
                            paddingRight: function(i, node) {
                                return 4;
                            }
                        };

                        // Center-align all table content
                        doc.content[1].table.body.forEach(function(row, rowIndex) {
                            row.forEach(function(cell, cellIndex) {
                                if (typeof cell === 'object') {
                                    cell.alignment = 'center'; // Center-align text in each cell
                                }
                            });
                        });

                        // Center-align the title
                        doc.content[0].alignment = 'center';
                    },
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            page: 'all' // Export all data, not just the current page
                        }
                    }
                }
            ],
            ajax: {
                url: "{{ route('paymentReport') }}",
                data: function(d) {
                    d.type = type;
                    d.payment_type = 'ap'
                    d.startDate = result.startDate;
                    d.endDate = result.endDate
                },
                dataSrc: function(json) {
                    if (json.data.length === 0) {
                        // Swal.fire({
                        //     icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        //     title: 'Oops!',
                        //     text: 'No Data found for Chart : Payments  ' + selectedText + ' (' + currentDate + ')',
                        //     confirmButtonText: 'OK'
                        // });
                    }
                    return json.data;
                }
            },
            language: {
                emptyTable: 'No Data found for Chart : Payments (AP)  ' + selectedText + ' (' + currentDate + ')',
            },
            initComplete: function(settings, json) {
                syncReportExportButtons(this.api());
            },
            columns: columns,
            order: [
                [1, 'desc']
            ]
        };
        // Configure DataTable based on report type
        if (type == 'byPaymentMode') {
            $('#clientReportAPTitle5').html('Payments (AP) By Mode');
            dataTableSettings.columns = [{
                    title: "Sr.No",
                    data: 'DT_RowIndex', // Index column
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,

                }, {
                    title: "Payment Mode",
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    title: "No. of Payments",
                    data: 'number_of_payment',
                    name: 'number_of_payment'
                }
            ];
        } else if (type == 'byPaymentAmount') {
            $('#clientReportAPTitle5').html('Payments (AP) By Amount (Range)');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                },
                {
                    title: "Amount (Range)",
                    data: 'amount_range',
                    name: 'amount_range',

                },
                {
                    title: "No. of Payments",
                    data: "number_of_invoices",
                    name: "number_of_invoices",

                }

            ];
            dataTableSettings.order = [
                [1, 'asc']
            ]
        } else if (type == 'byOutstandingAmount') {
            $('#clientReportAPTitle5').html('Payments (AP) By Outstanding Amount');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Client Name (ID)",
                    data: 'client_name',
                    name: 'client_name'
                },
                {
                    title: "Name of Application/Service",
                    data: 'application_name',
                    name: 'application_name',

                },
                {
                    title: "Amount To Pay",
                    data: "amount",
                    name: "amount",

                },
                {
                    title: "Paid Amount",
                    data: "paid_amount",
                    name: "paid_amount",

                },
                {
                    title: "Outstanding Amount",
                    data: "amount_to_pay",
                    name: "amount_to_pay",

                },

                {
                    title: "Payment Due Date",
                    data: "due_date",
                    name: "due_date",

                }
            ];
            dataTableSettings.order = [
                [1, 'asc']
            ]
        } else if (type == 'byInvoiceType') {
            $('#clientReportAPTitle5').html('Payments (AP) By Payment Type');
            dataTableSettings.columns = [{
                    title: "Sr.No",
                    data: 'DT_RowIndex', // Index column
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,

                }, {
                    title: "Payment Type",
                    data: 'type',
                    name: 'type'
                },
                {
                    title: "No. of Payments",
                    data: 'number_of_invoices',
                    name: 'number_of_invoices'
                }
            ];
        } else if (type == 'byClientCountry') {
            $('#clientReportAPTitle5').html("Payments (AP) By Client's Home Country");
            dataTableSettings.columns = [{
                    title: "Sr.No",
                    data: 'DT_RowIndex', // Index column
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,

                }, {
                    title: "Home Country",
                    data: 'country',
                    name: 'country'
                },
                {
                    title: "No. of Payments",
                    data: 'number_of_payment',
                    name: 'number_of_payment'
                }
            ];
        } else if (type == 'byVisaCountry') {
            $('#clientReportAPTitle5').html("Payments (AP) By Visa Country");
            dataTableSettings.columns = [{
                    title: "Sr.No",
                    data: 'DT_RowIndex', // Index column
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,

                }, {
                    title: "Visa Country",
                    data: 'to_country',
                    name: 'to_country'
                },
                {
                    title: "No. of Payments",
                    data: 'number_of_payment',
                    name: 'number_of_payment'
                }
            ];
        } else if (type == 'byApplicationType') {
            $('#clientReportAPTitle5').html('Payments (AP) By Application Type');
            dataTableSettings.columns = [{
                    title: "Sr.No",
                    data: 'DT_RowIndex', // Index column
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,

                }, {
                    title: "Application Type",
                    data: 'application_name',
                    name: 'application_name'
                },
                {
                    title: "No. of Payments",
                    data: 'number_of_payment',
                    name: 'number_of_payment'
                }
            ];
        } else if (type == 'byTimeLine') {
            $('#clientReportAPTitle5').html('Payments (AP) By Timeline');
            dataTableSettings.columns = [{
                    title: 'Sr.No',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Timeline (Duration)",
                    data: 'type',
                    name: 'type',

                },
                {
                    title: "No of Payments",
                    data: 'count',
                    name: 'count',

                }
            ];
            dataTableSettings.order = [
                // []
            ]
        } else if (type == 'byYear') {
            $('#clientReportAPTitle5').html('Payments (AP) By Year');
            dataTableSettings.columns = [{
                    title: "Sr.No",
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    title: "Year",
                    data: 'year',
                    name: 'year'
                },
                {
                    title: "No. of Payments",
                    data: 'count',
                    name: 'count'
                }
            ];
            dataTableSettings.order = [
                // []
            ]
        }

        $('#custom_date_picker71').prop('disabled', type == 'byTimeLine' || type == 'byYear');
        // Initialize the DataTable with the new settings
        $('#reportPaymentAPTable').DataTable(dataTableSettings);
    }

    function onClickAssociates() {
        var result = getStartAndEndDate('Associate');
        let currentDate = `${result.startDate} - ${result.endDate}`;
        deativeTabs();
        associatesTable1 = true;

        $('#associatesTable1').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            lengthMenu: [
                [10, 20, 50, -1],
                [10, 20, 50, "All"]
            ],
            pageLength: 10,
            dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip',
            buttons: [{
                    extend: 'csv',
                    title: 'Report : Associates  (' + currentDate + ')',
                    exportOptions: { modifier: { page: 'all' } }
                },
                {
                    extend: 'excel',
                    title: 'Report : Associates  (' + currentDate + ')',
                    exportOptions: { modifier: { page: 'all' } }
                },
                {
                    extend: 'pdf',
                    title: 'Report : Associates  (' + currentDate + ')',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { modifier: { page: 'all' } }
                }
            ],
            ajax: {
                url: "{{ route('manage_associates_report') }}",
                data: function(d) {
                    d.startDate = result.startDate;
                    d.endDate = result.endDate;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'associate_code', name: 'associate_code' },
                { data: 'name', name: 'name' },
                { data: 'organization', name: 'organization' },
                { data: 'city', name: 'city' },
                { data: 'country', name: 'country' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                { data: 'created_at', name: 'created_at' }
            ]
        });
    }

    function onChangeAssociatesReport(type, text) {
        let selectedText = text;

        if (!type || type === '') {
            $('#reportAssociates').hide();
            return;
        }

        var result = getStartAndEndDate('Associate');
        let currentDate = `${result.startDate} - ${result.endDate}`;
        $('#reportAssociates').show();

        let reportTitle = '';
        let columns = [];
        const totalAssociatesTitle = 'Total (No. of Associates)';

        switch (type) {
            case 'byCity':
                reportTitle = 'Associates By City';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'City', data: 'city', name: 'city' },
                    { title: totalAssociatesTitle, data: 'total', name: 'total' }
                ];
                break;
            case 'byCountry':
                reportTitle = 'Associates By Country';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'Country', data: 'country', name: 'country' },
                    { title: totalAssociatesTitle, data: 'total', name: 'total' }
                ];
                break;
            case 'byReferrals':
                reportTitle = 'Associates By Referrals';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'Associate ID', data: 'associate_code', name: 'associate_code' },
                    { title: 'Associate Name', data: 'name', name: 'name' },
                    { title: 'Total (No. of Referrals)', data: 'total', name: 'total' }
                ];
                break;
            case 'byBusiness':
                reportTitle = 'Associates By Business (Amount)';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'Associate ID', data: 'associate_code', name: 'associate_code' },
                    { title: 'Associate Name', data: 'name', name: 'name' },
                    { title: 'Total Business Amount', data: 'total', name: 'total' }
                ];
                break;
            case 'byHomeCountry':
                reportTitle = "Associates By Client's Home Country";
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: "Client's Home Country", data: 'home_country', name: 'home_country' },
                    { title: totalAssociatesTitle, data: 'total', name: 'total' }
                ];
                break;
            case 'byOutstanding':
                reportTitle = 'Associates By Outstanding Payment (Amount)';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'Associate ID', data: 'associate_code', name: 'associate_code' },
                    { title: 'Associate Name', data: 'name', name: 'name' },
                    { title: 'Total Outstanding', data: 'total', name: 'total' }
                ];
                break;
            case 'byYear':
                reportTitle = 'Associates By Year';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'Year', data: 'year', name: 'year' },
                    { title: totalAssociatesTitle, data: 'total', name: 'total' }
                ];
                break;
            case 'byTimeline':
                reportTitle = 'Associates By Timeline (Duration)';
                columns = [
                    { title: 'Sr No.', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { title: 'Period', data: 'period', name: 'period' },
                    { title: totalAssociatesTitle, data: 'total', name: 'total' }
                ];
                break;
            default:
                return;
        }

        $('#custom_date_picker15').prop('disabled', type == 'byYear');
        $('#associateReportTitle').html(reportTitle);

        if ($.fn.DataTable.isDataTable('#associateReportTable')) {
            $('#associateReportTable').DataTable().clear().destroy();
            $('#associateReportTable').empty();
        }

        $('#associateReportTable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            lengthMenu: [
                [10, 20, 50, -1],
                [10, 20, 50, "All"]
            ],
            pageLength: 10,
            destroy: true,
            dom: '<"reports-dt-toolbar d-flex align-items-center"l<"reports-dt-btn-wrap"B>f>rtip',
            buttons: [{
                    extend: 'csv',
                    title: 'Report : Associates ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''),
                    exportOptions: { modifier: { page: 'all' } }
                },
                {
                    extend: 'excel',
                    title: 'Report : Associates ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''),
                    exportOptions: { modifier: { page: 'all' } }
                },
                {
                    extend: 'pdf',
                    title: 'Report : Associates ' + selectedText + (!text.includes('By Timeline (Duration)') && !text.includes('By Year') ? ' (' + currentDate + ')' : ''),
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { modifier: { page: 'all' } }
                }
            ],
            ajax: {
                url: "{{ route('associatesReport') }}",
                data: function(d) {
                    d.type = type;
                    d.startDate = result.startDate;
                    d.endDate = result.endDate;
                }
            },
            columns: columns
        });
    }
</script>
