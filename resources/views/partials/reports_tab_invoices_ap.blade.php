                    <div class="tab-pane fade" id="invocies_ap" role="tabpanel" aria-labelledby="invocies-ap-tab">
                        <div style="display: flex; justify-content: center; align-items: center; text-align: center;" class="row">
                            <div class="col-4 my-3 d-flex align-items-center">
                                <label class="mr-4 w-50 fw-bold" for="">Filter By Attribute</label>
                                <select id="invoiceAPFilter" class="form-select" name=""
                                    onchange="onchangeInvoicesAPReport(this.value,this.options[this.selectedIndex].text)">
                                    <option value="" selected>Select Attribute</option>
                                    <option value="byAmount">By Amount</option>
                                    <option value="byType">By Invoice Type</option>
                                    {{-- <option value="Gender">By Gender</option> --}}
                                    <option value="byClient">By Client's Home Country </option>
                                    <option value="byVisaCountry">By Visa Country Count</option>
                                    <option value="byTimeLine">By Timeline (Duration)</option>
                                    <option value="yearly">By Year</option>
                                </select>
                            </div>
                            <div class="col-4 my-3 d-flex align-items-center ">
                                <label class="mr-4 w-50 fw-bold">Select Duration</label>
                                <input type="text" id="custom_date_picker66" name="custom_date_picker"
                                    placeholder="Select Duration" class="form-control">

                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="reportInvoicesAP" style="display:none">
                                    <h3 id="clientReportAPTitle2"></h3>
                                    <table class="fl-table table table-hover table-responsive p-0 m-0"
                                        style="width:100%;" id="reportInvoiceAPTable">
                                        <thead>

                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="table-wrapper">
                            <h3 class="second-table-heading">Invoices (AP)</h3>
                            <table class="fl-table table table-hover p-0 m-0" id="invoicesAPTable1"
                                style="width: 100%">
                                <thead>

                                    <tr>
                                        <th>Sr No.</th>
                                        <th>Client Name (ID)</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th class="squeeze-column">CreatedDate</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
