<div class="modal fade text-start profilenew-modal" id="leadContacts" tabindex="-1" aria-labelledby="myModalLabel17"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px !important">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Add New</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="form" role="add-lead-contacts" method="POST"
                    action="{{ route('notes.add-lead-contacts') }}" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-3">
                            <div class="mb-1">
                                <label class="form-label">Contact Name</label>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="mb-1">
                                <label class="form-label">Contact Number</label>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="mb-1">
                                <label class="form-label">Contact Email</label>
                            </div>
                        </div>
                    </div>
                    <div id="render-existing-lead-contacts">
                    </div>
                    <div class="row add-more-row">
                        <div class="col-3">
                            <div class="mb-1">
                                <input type="text" class="form-control" name="data[0][contact_name]"
                                    id="lead_contact_name" />
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="mb-1">
                                <input type="text" class="form-control numberonly-v2" name="data[0][contact_number]"
                                    id="lead_contact_number" />
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="mb-1">
                                <input type="text" class="form-control" name="data[0][contact_email]"
                                    id="lead_contact_email" />
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="mb-1" id="change-to-remove">
                                <a id="add-more-contact"
                                    data-trash-url="{{ asset('/app-assets/images/icons/trash.svg') }}"
                                    onclick="addMoreLeadContacts();">
                                    <span class="text-primary" style="font-size: 20px; font-weight: bold;">+</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-1 mt-1">
                            <button type="button" class="btn btn-primary data-submit" data-request="lead-contacts"
                                data-target="[role=add-lead-contacts]">Submit</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
