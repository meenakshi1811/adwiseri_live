                    <div class="tab-pane fade" id="associatesReport" role="tabpanel" aria-labelledby="associates-report-tab">
                        <div style="display: flex; justify-content: center; align-items: center; text-align: center;" class="row">
                            <div class="col-4 my-3 d-flex align-items-center">
                                <label class="mr-4 w-50 fw-bold" for="">Filter By Attribute</label>
                                <select id="associateReportFilter" class="form-select"
                                    onchange="onChangeAssociatesReport(this.value, this.options[this.selectedIndex].text)">
                                    <option value="" selected>Select Attribute</option>
                                    <option value="byCity">By City</option>
                                    <option value="byCountry">By Country</option>
                                    <option value="byReferrals">By Referrals</option>
                                    <option value="byBusiness">By Business (Amount)</option>
                                    <option value="byHomeCountry">By Client's Home Country</option>
                                    <option value="byOutstanding">By Outstanding Payment (Amount)</option>
                                    <option value="byYear">By Year</option>
                                    <option value="byTimeline">By Timeline (Duration)</option>
                                </select>
                            </div>
                            <div class="col-4 my-3 d-flex align-items-center ">
                                <label class="mr-4 w-50 fw-bold">Select Duration</label>
                                <input type="text" id="custom_date_picker15" name="custom_date_picker"
                                    placeholder="Select Duration" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div id="reportAssociates" style="display:none">
                                    <h3 id="associateReportTitle"></h3>
                                    <table class="fl-table table table-hover table-responsive p-0 m-0"
                                        style="width:100%;" id="associateReportTable">
                                        <thead></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <h3 class="second-table-heading">Associates</h3>
                            <table class="fl-table table table-hover p-0 m-0" id="associatesTable1"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="p-1 text-center">Sr No.</th>
                                        <th class="p-1 text-center">Associate ID</th>
                                        <th class="p-1 text-center">Name</th>
                                        <th class="p-1 text-center">Organization</th>
                                        <th class="p-1 text-center">City</th>
                                        <th class="p-1 text-center">Country</th>
                                        <th class="p-1 text-center">Phone No</th>
                                        <th class="p-1 text-center">Email</th>
                                        <th class="p-1 text-center">Created Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
