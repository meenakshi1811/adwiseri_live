                    <div class="tab-pane fade" id="paymentsap" role="tabpanel" aria-labelledby="paymentsap-tab">
                        <div style="display: flex; justify-content: center; align-items: center; text-align: center;" class="row">

                            <div class="col-4 my-3 d-flex align-items-center">
                                <label class="mr-4 w-50 fw-bold" for="">Filter By Attribute</label>
                                <select id="paymentAPFilter" class="form-select" name=""
                                    onchange="onchangePaymentsAPReport(this.value,this.options[this.selectedIndex].text)">
                                    <option value="" selected>Select Attribute</option>
                                    <option value="byPaymentAmount">By Amount (Range)</option>
                                    <option value="byPaymentMode">By Payment Mode</option>
                                    <option value="byOutstandingAmount">By Outstanding Amount</option>
                                    <option value="byInvoiceType">By Payment Type </option>
                                    <option value="byVisaCountry">By Visa Country</option> 
                                    <option value="byClientCountry">By Client's Home Country</option>
                                    <!-- <option value="byClientCountry">By Client's Home Country</option>
                                    <option value="byVisaCountry">By Visa Country</option> -->
                                    <!-- <option value="byApplicationType">By Application Type</option> -->
                                    
                                    <option value="byYear">By Year</option>
                                    <option value="byTimeLine">By Timeline (Duration)</option>
                                </select>
                            </div>
                            <div class="col-4 my-3 d-flex align-items-center ">
                                <label class="mr-4 w-50 fw-bold">Select Duration</label>
                                <input type="text" id="custom_date_picker71" name="custom_date_picker"
                                    placeholder="Select Duration" class="form-control">

                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="reportAPPayments" style="display:none">
                                    <h3 id="clientReportAPTitle5"></h3>
                                    <table class="fl-table table table-hover table-responsive p-0 m-0"
                                        style="width:100%;" id="reportPaymentAPTable">
                                        <thead>

                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <h3 class="second-table-heading">Payments (AP)</h3>
                            <table class="fl-table table table-hover p-0 m-0" id="paymentsAPTable1"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <!-- <th>Client Name (ID)</th> -->
                                        <!-- <th>Service Offered</th>
                                        <th>Payment Type</th> -->
                                        <th class="col-4">Service Provider</th>
                                        <th class="col-4"> Service Taken</th>
                                        <th>Payment Mode</th>
                                        <th>Amount To Pay</th>
                                        <th>Paid Amount</th>
                                        <th>Outstanding</th>
                                        <th>Payment Date</th>
                                        <!-- <th>InvoiceID</th> -->
                                        <th class="squeeze-column">EntryDate</th>
                                    </tr>

                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
