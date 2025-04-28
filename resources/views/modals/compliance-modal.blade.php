<div class="modal fade" id="pricingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-simple modal-pricing">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Additional Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" >
                    <input type="hidden" id="formAction" name="formAction" value="add">
                    <!-- TDS Details -->
                    <div class="row country-content" id="modalForm" >
                        <div class="col-md-6">
                            <h5 class="mt-1 mb-2 text-dark"><strong>TDS Details</strong></h5>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">TDS Applicable</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                        <input type="checkbox" name="compliance[tds_applicable]" id="tdsApplicableIndia">
                                        <label class="form-check-label" for="tdsApplicableIndia">Yes/No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">Wef Date</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="compliance[wef_date]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">TDS Certificate No.</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[tds_certificate_no]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">TDS Tax Percentage</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[tds_tax_percentage]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">TDS Category</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[tds_category]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">TDS Value Cab</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[tds_value_cab]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">TAN Number</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[tan_number]" class="form-control" />
                                </div>
                            </div>
                        </div>

                        <!-- GST Info -->
                        <div class="col-md-6">
                            <h5 class="mt-1 mb-2 text-dark"><strong>GST Info</strong></h5>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">GST Applicable</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="demo-inline-spacing">
                                        <div class="form-check form-check-primary mt-25">
                                            <input type="radio" id="gstRegisteredIndia" name="compliance[gst_applicable]" value="1"  class="form-check-input">
                                            <label class="form-check-label fw-bolder" for="gstRegisteredIndia">Registered</label>
                                        </div>
                                        <div class="form-check form-check-primary mt-25">
                                            <input type="radio" id="gstNonRegisteredIndia" name="compliance[gst_applicable]" value="0" checked class="form-check-input">
                                            <label class="form-check-label fw-bolder" for="gstNonRegisteredIndia">Non-Registered</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">GSTIN No.</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[gstin_no]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">GST Registered Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[gst_registered_name]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">GSTIN Reg. Date</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="compliance[gstin_registration_date]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">Upload Certificate</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="compliance[gst_certificate][]" multiple class="form-control" />
                                    <div id="gstCertificateLinks"></div>
                                </div>
                            </div>
                        </div>

                        <!-- MSME Details -->
                        <div class="col-md-6">
                            <h5 class="mt-1 mb-2 text-dark"><strong>MSME Details</strong></h5>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">MSME Registered?</label>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                    <input type="checkbox" class="form-check-input" name="compliance[msme_registered]" id="msmeRegisteredIndia">
                                        <label class="form-check-label" for="msmeRegisteredIndia">This vendor is MSME registered</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">MSME No.</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="compliance[msme_no]" class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">MSME Type</label>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select"  name="compliance[msme_type]">
                                        <option value="">Select</option>
                                        <option>Micro</option>
                                        <option>Small</option>
                                        <option>Medium</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-4">
                                    <label class="form-label">Upload Certificate</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="compliance[msme_certificate][]" multiple class="form-control" />
                                    <a id="msmeCertificateLinks"></a>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>


