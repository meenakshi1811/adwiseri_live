@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">

            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <form class="form-inline d-flex justify-content-between w-100">
                        <h3 class="text-primary">Add New Payment (AR) Record Entry Form</h3>
                    </form>
                </div>
                <div class="col">
                    <form id="registration_form" class="register-box login-box" method="POST"
                        action="{{ route('payment_received') }}">
                        @csrf
                        {{-- <input type="hidden" name="id" value="{{ old('id }}" /> --}}
                        <input type="hidden" name="local_time" class="localtime" />
                        <div class="row">
                             <!-- Payment Entry Type -->
                            <div class="col-md-4 p-1">
                                <label>Payment Entry Type<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_entry_type" id="new_entry" value="New" onclick="toggleOutstanding(false)">
                                    <label class="form-check-label" for="new_entry">New</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_entry_type" id="existing_entry" value="Existing" checked onclick="toggleOutstanding(true)">
                                    <label class="form-check-label" for="existing_entry">Existing</label>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 inovice-section">
                                <label>Select Invoice<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1 inovice-section">
                            
                                <select name="invoices_list" id="invoices_list"
                                        class="form-control form-select @error('invoices') is-invalid @enderror"  onchange="fetchInvoiceDetails()">
                                    <option value="">Select Invoice</option>
                                    @foreach ($invoices as $invoice)
                                    <option {{ (old('invoices_list') == $invoice['id']) ? 'selected' : '' }}
                                            value="{{ $invoice['id'] }}"
                                            data-client-id="{{ $invoice['client_id'] }}"
                                            data-application-id="{{ $invoice['application_code'] }}"
                                            data-application-display="{{ $invoice['application_display'] }}"
                                            data-amount="{{ $invoice['amount'] }}"
                                            data-outstanding="{{ $invoice['outstanding_amount'] }}"
                                            data-paid="{{ $invoice['paid_amount'] }}"
                                            data-service="{{ $invoice['service_description'] }}">
                                            {{ $invoice['display_label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('subscriber')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Outstanding Amount (Hidden by default) -->
                            <!-- Client Name -->
                            <div class="col-md-4 p-1">
                                <label>Client Name<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select id="client_id" required disabled
                                    class="form-control form-select @error('client') is-invalid @enderror" id="exampleInputEmail1"
                                    aria-describedby="emailHelp">
                                    <option value="">Select Client</option>
                                    @foreach ($clients as $clint)
                                        <option {{ (old('client') == $clint->id) ? 'selected' : '' }} value="{{ $clint->id }}">{{ $clint->name . '(' . $clint->id . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subscriber')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <input type="hidden" name="client_id" id="client_id_hidden" value="{{ old('client_id') }}">
                            <input type="hidden" name="application_id" id="application_id" value="{{ old('application_id') }}">

                            <div class="col-md-4 p-1">
                                <label>Application (ID)</label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input type="text" class="form-control" id="application_display" readonly
                                    placeholder="Application (ID)" value="{{ old('application_display') }}">
                            </div>

                            <div class="col-md-4 p-1">
                                <label>Service Offered</label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="service_description" type="text" minlength="3" maxlength="200"
                                    class="form-control @error('service_description') is-invalid @enderror" id="service_description"
                                    aria-describedby="ageHelp" value="{{ old('service_description') }}" required
                                    placeholder="Service Description" autocomplete="detail" readonly>
                                @error('service_description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Total Amount To Pay<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="amount" 
                                    required 
                                    type="number" 
                                    min="0.01"
                                    step="0.01"
                                    class="form-control @error('amount') is-invalid @enderror" 
                                    id="amount"
                                    aria-describedby="emailHelp" 
                                    value="{{ old('amount') }}" 
                                    placeholder="Total Amount To Pay"
                                    autocomplete="amount" readonly>
                                @error('amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1 existing-only" style="display:none;">
                                <label>Amount Paid</label>
                            </div>
                            <div class="col-md-8 p-1 existing-only" style="display:none;">
                                <input type="number" class="form-control" id="amount_paid_existing" readonly>
                            </div>
                            <div class="col-md-4 p-1 existing-only outstanding-section" style="display:none;">
                                <label>Outstanding</label>
                            </div>
                            <div class="col-md-8 p-1 existing-only outstanding-section" style="display:none;">
                                <input name="outstanding_amount" min="0" step="0.01" type="number" class="form-control" value="{{ old('outstanding_amount') }}" id="outstanding_amount" placeholder="Outstanding Amount" readonly>
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Amount Paying<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="paid_amount" required type="number" min="0.01" step="0.01"
                                    class="form-control @error('amount') is-invalid @enderror" id="paid_amount"
                                    aria-describedby="emailHelp" value="{{ old('paid_amount') }}" placeholder="Amount Paying"
                                    autocomplete="amount">
                                @error('paid_amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Mode Of Payment (MOP)
                                </label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="payment_mode" class="form-control form-select @error('payment_mode') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ old('payment_mode') }}" required>
                                    <option value="">Select Payment Mode</option>
                                    <option {{(old('payment_mode') == "Cash") ? 'selected':''}} value="Cash">Cash</option>
                                    <option {{(old('payment_mode') == "Cheque") ? 'selected':''}} value="Cheque">Cheque</option>
                                    <option {{(old('payment_mode') == "DD") ? 'selected':''}} value="DD">DD</option>
                                    <option {{(old('payment_mode') == "Wire") ? 'selected':''}} value="Wire">Wire</option>
                                    <option {{(old('payment_mode') == "UPI") ? 'selected':''}} value="UPI">UPI</option>
                                    <option {{(old('payment_mode') == "Vouchers") ? 'selected':''}} value="Vouchers">Vouchers</option>
                                    <option {{(old('payment_mode') == "Notes") ? 'selected':''}} value="Notes">Notes</option>
                                </select>
                                @error('payment_mode')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Payment Date
                                </label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="payment_date" type="text"
                                class="form-control date @error('payment_date') is-invalid @enderror"
                                id="payment_date"
                                aria-describedby="emailHelp"
                                value="{{ old('payment_date', date('d-m-Y')) }}"
                                placeholder="{{ date('d-m-Y') }}"
                                autocomplete="off"
                                max="{{ date('d-m-Y') }}"
                                required
                                />
                                @error('payment_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                          

                            <div class="col-md-4 p-1"></div>
                            <div class="col-md-8 p-1 text-start">
                                <button type="submit" class="form-control btn btn-primary"
                                    style="width: fit-content;">Submit</button>
                            </div>
                        </div>
                    </form>


                </div>


            </div>

    </div>
    </div>

    </div>
    <script>
         // Function to fetch selected invoice details
         function fetchInvoiceDetails() {
            const invoiceSelect = document.getElementById("invoices_list");
            const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
            const selectedInvoiceId = invoiceSelect.value;
            if (!selectedInvoiceId) return;

            // Prefill from option data while waiting for / validating via API
            if (selectedOption) {
                document.getElementById("client_id").value = selectedOption.dataset.clientId || "";
                document.getElementById("client_id_hidden").value = selectedOption.dataset.clientId || "";
                document.getElementById("application_id").value = selectedOption.dataset.applicationId || "";
                document.getElementById("application_display").value = selectedOption.dataset.applicationDisplay || "";
                document.getElementById("service_description").value = selectedOption.dataset.service || "";
                document.getElementById("amount").value = selectedOption.dataset.amount || "";
                document.getElementById("amount_paid_existing").value = parseFloat(selectedOption.dataset.paid || '0').toFixed(2);
                document.getElementById("outstanding_amount").value = parseFloat(selectedOption.dataset.outstanding || '0').toFixed(2);
                document.getElementById("paid_amount").value = "";
            }

            fetch(`/invoices/${selectedInvoiceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        return;
                    }
                    // Set selected client in dropdown
                    const clientDropdown = document.getElementById("client_id");
                    if (data.client) {
                        clientDropdown.value = data.client || "";
                        document.getElementById("client_id_hidden").value = data.client || "";
                    }

                    // Keep option-prefilled application when API cannot resolve one
                    if (data.applicationID || data.applicationDisplay || data.applicationName) {
                        document.getElementById("application_id").value = data.applicationID || "";
                        document.getElementById("application_display").value = data.applicationDisplay || data.applicationName || "";
                    }
                    if (data.service) {
                        document.getElementById("service_description").value = data.service || "";
                    }
                    if (data.amount !== undefined && data.amount !== null && data.amount !== "") {
                        document.getElementById("amount").value = data.amount;
                    }
                    document.getElementById("amount_paid_existing").value = Number(data.paidAmmount || 0).toFixed(2);
                    document.getElementById("paid_amount").value = "";
                    document.getElementById("outstanding_amount").value = Number(data.outstandingAmount || 0).toFixed(2);
                })
                .catch(error => console.error("Error fetching invoice details:", error));
        }
        toggleOutstanding(true);
        function toggleOutstanding(show) {
            let invoiceSections = document.querySelectorAll('.inovice-section');
            invoiceSections.forEach(section => {
                section.style.display = 'block';
            });
            document.querySelectorAll('.existing-only').forEach(section => {
                section.style.display = show ? 'block' : 'none';
            });
            const invoiceSelect = document.getElementById("invoices_list");
            Array.from(invoiceSelect.options).forEach((opt, idx) => {
                if (idx === 0) return;
                const paidAmount = parseFloat(opt.dataset.paid || '0');
                const shouldShow = show ? paidAmount > 0 : paidAmount <= 0;
                opt.hidden = !shouldShow;
            });
            invoiceSelect.value = '';

            document.getElementById("client_id").value = '';
            document.getElementById("client_id_hidden").value = '';
            document.getElementById("application_id").value = '';
            document.getElementById("application_display").value = '';
            document.getElementById("service_description").value = '';
            document.getElementById("amount").value = '';
            document.getElementById("paid_amount").value = '';
            document.getElementById("amount_paid_existing").value = '';
            document.getElementById("outstanding_amount").value = '';

            document.getElementById("service_description").setAttribute("readonly", "readonly");
            document.getElementById("amount").setAttribute("readonly", "readonly");
        }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        $(document).ready(() => {
            
            $("#subscriber").change(function() {
                var subscriber = $(this).val();
                $.ajax({
                    url: 'check_client_limit',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        subscriber: subscriber,
                    },
                    cache: false,
                    success: function(data) {
                        //   console.log(data);
                        if (data.limit == 'full') {
                            Swal.fire({
                                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                                title: 'Oops!',
                                text: 'Client limit reached for this subscriber.'
                            });
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    }
                });
            });
            $("#country").change(function() {
                var country = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: 'get_states',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        country: country,
                    },
                    cache: false,
                    success: function(data) {
                        console.log(data);
                        $("#state").html(data);
                    }
                });
            });
            $("#subscriber").change(function() {
                var id = $(this).val();
                var name = 'subscriber';
                // console.log(counrty);
                $.ajax({
                    url: 'get_job_role',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id,
                        name: name,
                    },
                    cache: false,
                    success: function(data) {
                        console.log(data);
                        $("#job_role").html(data);
                    }
                });
            });

            function filterInvoicesByClient() {
                const selectedClient = $("#client_id").val();
                const invoiceSelect = document.getElementById("invoices_list");
                Array.from(invoiceSelect.options).forEach((opt, idx) => {
                    if (idx === 0) return;
                    opt.hidden = selectedClient && opt.dataset.clientId !== selectedClient;
                });
            }
            $("#client_id").change(function(){
                document.getElementById("client_id_hidden").value = this.value || "";
                filterInvoicesByClient();
            });
    const amountInput = document.getElementById('amount');
    const outstandingInput = document.getElementById('outstanding_amount');
    const paidAmountInput = document.getElementById('paid_amount');
    const registrationForm = document.getElementById('registration_form');

    // Ensure both fields exist before adding event listeners
    if (amountInput && paidAmountInput && registrationForm) {
        // Function to validate the paid amount
        function getAllowedAmount() {
            const outstanding = parseFloat(outstandingInput?.value);
            if (!isNaN(outstanding) && outstanding > 0) {
                return outstanding;
            }
            return parseFloat(amountInput.value) || 0;
        }

        function validatePaidAmount(showAlert = false) {
            const amount = parseFloat(amountInput.value) || 0;
            const paidAmount = parseFloat(paidAmountInput.value) || 0;
            const allowedAmount = getAllowedAmount();

            // Check if Paid Amount is greater than Outstanding/Amount to Pay
            if (paidAmount > allowedAmount) {
                const message = `Amount Paying should not exceed ${allowedAmount.toFixed(2)} (Outstanding).`;
                paidAmountInput.setCustomValidity(message);
                paidAmountInput.classList.add('is-invalid'); // Add invalid class for styling
                if (showAlert) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: message
                        });
                    } else {
                        alert(message);
                    }
                }
                return false;
            } else {
                paidAmountInput.setCustomValidity(''); // Clear custom validity
                paidAmountInput.classList.remove('is-invalid'); // Remove invalid class if valid
                return true;
            }
        }

        // Attach validation on input events
        amountInput.addEventListener('input', validatePaidAmount);
        paidAmountInput.addEventListener('input', validatePaidAmount);
        outstandingInput?.addEventListener('input', validatePaidAmount);
        registrationForm.addEventListener('submit', function (event) {
            if (!validatePaidAmount(true)) {
                event.preventDefault();
            }
        });

        // Initialize validation in case there is pre-filled data
        validatePaidAmount();
    }

    // Optionally add console logs to check if things are working correctly
    console.log("Validation Script Loaded");
        });
    </script>
    <script>
        function deleteuser(id) {
            var conf = confirm('Are you sure you want to delete this payment?');
            if (conf == true) {
                window.location.href = "delete_user/" + id + "";
            }
        }
    </script>

    @if (session()->has('deleted'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Payment deleted successfully.'
            })
        </script>
    @endif

@endsection()
