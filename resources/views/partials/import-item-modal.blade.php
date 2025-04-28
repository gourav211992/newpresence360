<div class="modal fade" id="importItemModal" tabindex="-1" aria-labelledby="importItem" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 1000px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="headerDisc">
                    <b>Import Items</b>
                </h1>
                <div class="text-end"></div>
                <div class="row">
                    <div class="col-md-6">
                        <!-- <div class="mb-1">
                            <label class="form-label">
                                Module
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="module" name="module">
                                <option value="mrn">MRN</option>
                            </select>
                        </div> -->
                    </div>
                    <div class="col-md-6 d-flex align-items-center justify-content-end">
                        <a download href="{{asset('templates/Transaction_item_sample_template.xlsx')}}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Download Sample
                        </a>
                    </div>
                </div>
                <section id="basic-datatable">
                    <div class="row justify-content-center">
                        <div class="col-9">
                            <form class="importForm" method="POST" action="{{ route('material-receipt.items.import') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="upload-item-masstrerdata">
                                    <!-- File Upload Section -->
                                    <div class="drapdroparea upload-btn-wrapper text-center default-dragdrop-area-unique">
                                        <i class="uploadiconsvg" data-feather='upload'></i>
                                        <p>Upload the template file with updated data</p>
                                        <button type="button" class="btn btn-primary">DRAG AND DROP HERE OR CHOOSE FILE</button>  
                                        <input type="file" name="file" accept=".xlsx, .xls, .csv" class="form-control" id="fileUpload"/>
                                    </div>
                    
                                    <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="fileNameDisplay" style="display: none;">
                                        <div class="badge rounded-pill badge-light-warning fw-bold mb-1 badgeborder-radius d-flex align-items-center"> 
                                            <span id="selectedFileName"></span> 
                                            <i data-feather='x-circle' id="cancelBtn" class="ms-75"></i>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Proceed to Upload</button>
                                    </div>

                                    <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="uploadProgress" style="display: none;">
                                        <span class="badge rounded-pill badge-light-warning fw-bold mb-1 badgeborder-radius d-flex align-items-center" id="progressFileName">
                                            <span id="selectedFileName"></span>
                                        </span>
                                        <button class="btn btn-primary" disabled>Proceed to Upload</button>
                                        <div class="w-75 mt-3">
                                            <div class="progress" style="height: 15px">
                                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped bg-success progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">0%</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Error Section -->
                                    <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="uploadError" style="display: none;">
                                        <i class="alertdropdatamaster" data-feather='alert-triangle'></i><br>
                                        <div class="alert alert-danger" id="upload-error" style="display: none;"></div>
                                        <div class="mt-2 downloadtemplate"> 
                                            <button class="editbtnNew">
                                                <i data-feather='upload'></i> Upload Again
                                            </button> 									
                                        </div> 	
                                    </div>

                                    <!-- Success Section -->
                                    <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="uploadSuccess" style="display: none;">
                                        <i class="itemdatasuccesssmaster" data-feather='check-circle'></i>
                                        <p>All records have been uploaded successfully.<br>
                                        Please proceed to process sales.</p>
                                        <div class="d-flex">
                                            <span class="badge rounded-pill badge-light-success fw-bold me-1 font-small-2 badgeborder-radius" id="success-count-badge"></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-11 mt-3 col-12 hide-this-section" style="display:none">
                           <div class="card  new-cardbox"> 
                                <ul class="nav nav-tabs border-bottom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#Succeded">Records Succeeded &nbsp;<span id="success-count">(0)</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#Failed">Records Failed &nbsp;<span id="failed-count">(0)</span></a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="Succeded">
                                        <div class="text-end my-1">
                                            <!-- <button type="button" class="btn btn-info btn-sm mb-50 mb-sm-0 me-50 exportBtn">
                                                <i data-feather="download"></i>Download
                                            </button> -->
                                            <button type="button" class="btn btn-success btn-sm processImportedBtn">
                                                <i data-feather="arrow-right"></i> Process Items
                                            </button>
                                        </div>
                                        <div class="table-responsive candidates-tables" style="max-height: 500px; overflow-y: auto;">
                                            <table class="datatables-basic datatables-success table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Item Code</th>
                                                        <th>Item Name</th>
                                                        <th>UOM</th>
                                                        <th>HSN</th>
                                                        <th>Store</th>
                                                        <th>Qty.</th>
                                                        <th>Rate</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="success-table-body">
                                                </tbody>
                                                <!-- <tfoot>
                                                    <tr>
                                                        <td colspan="9" class="text-end">
                                                            <button type="button" class="btn btn-success btn-sm processImportedBtn">
                                                                <i data-feather="arrow-right"></i> Process Items
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tfoot> -->
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="Failed">
                                        <div class="text-end my-1">
                                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 me-50 editbtnNew">
                                                <i data-feather='upload'></i>Upload Again
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 me-50 exportBtn">
                                                <i data-feather="download"></i>Download
                                            </button>
                                        </div>
                                        <div class="table-responsive candidates-tables" style="max-height: 500px; overflow-y: auto;">
                                            <table class="datatables-basic datatables-failed table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Item Code</th>
                                                        <th>Item Name</th>
                                                        <th>UOM</th>
                                                        <th>HSN</th>
                                                        <th>Store</th>
                                                        <th>Qty.</th>
                                                        <th>Rate</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="failed-table-body">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>