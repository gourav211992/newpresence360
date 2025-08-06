@extends('layouts.app')

@section('content')
<style>
    .middleinputerror {
    padding-bottom: 30px;
    }
    .middleinputerror span.text-danger {
        font-size: 12px;
        position: absolute;
        top: 38px;
    }
    .itemactive { position: absolute; left: 6px; font-size: 11px; top: 6px; color: #fff } 
    .iteminactive {  left: 24px; color: #999 } 
    .customernewsection-form .statusactiinactive .form-check-input { width: 80px; cursor: pointer}
    .customernewsection-form .statusactiinactive .form-check-input:checked + .itemactive { display: inline-block}
    .customernewsection-form .statusactiinactive .form-check-input:checked ~ .iteminactive { display: none }
    
    .customernewsection-form .statusactiinactive .form-check-input:not(:checked) + .itemactive { display: none}
    .customernewsection-form .statusactiinactive .form-check-input:not(:checked) ~ .iteminactive { display: inline-block }
</style>
    <!-- BEGIN: Content-->
  <form class="ajax-input-form" method="POST" action="{{ route('customer.store') }}" data-redirect="{{ url('/customers') }}"  enctype="multipart/form-data">
   @csrf
   <input type="hidden" name="customer_code_type" value="{{ $customerCodeType }}">
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6 col-6 mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Customer</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="#">Home</a>
										</li> 
                                        <li class="breadcrumb-item"><a href="{{route('customer.index')}}">Customer</a>
										</li>  
										<li class="breadcrumb-item active">Add</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('customer.index') }}" class="btn btn-secondary btn-sm">
                              <i data-feather="arrow-left-circle"></i> Back
                            </a>
                            <input type="hidden" name="document_status" id="document_status">
                            <button type="submit"  name="action" class="btn btn-warning btn-sm submit-button" value="draft">
                                <i data-feather="save"></i> Save as Draft
                            </button>
                            <button type="submit"  name="action" class="btn btn-primary btn-sm submit-button" value="submitted">
                                <i data-feather="check-circle"></i> Submit
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="fetchGstDetailsBtn">
                                <i data-feather="search"></i> Get GSTIN Details
                            </button>
                        </div>
                    </div>
				</div>
			</div>
            <div class="content-body">
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">  
							
                            <div class="card">
								 <div class="card-body customernewsection-form"> 
                                   <!--Start customer -->
                                          <div class="row">
                                                <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div> 
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p> 
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center"> 
                                                            <div class="form-check form-check-primary form-switch statusactiinactive">
                                                                <input type="checkbox" checked class="form-check-input" id="customSwitch3" />
                                                                <span class="itemactive">Active</span>
                                                                <span class="itemactive iteminactive">Inactive</span>
                                                            </div>
                                                            <input type="hidden" name="status" id="status_hidden_input" value="inactive">
                                                        </div>
                                                    </div>
                                                  </div>
                                                </div> 

                                                <div class="col-md-9"> 

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">
                                                                <span id="company_name_label">Customer Name</span><span class="text-danger">*</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-9">
                                                            <input type="text" name="company_name" class="form-control customer-name-autocomplete" placeholder="Enter Customer Name" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Customer Type <span class="text-danger">*</span></label>  
                                                        </div> 
                                                        <div class="col-md-4"> 
                                                            <div class="demo-inline-spacing">
                                                                @foreach ($customerTypes as $type)
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input
                                                                            type="radio"
                                                                            id="customer_type_{{ strtolower($type) }}"
                                                                            name="customer_type"
                                                                            value="{{ $type }}"
                                                                            class="form-check-input"
                                                                            {{ $type === 'Regular' ? 'checked' : '' }}
                                                                        >
                                                                        <label class="form-check-label fw-bolder" for="customer_type_{{ strtolower($type) }}">
                                                                            {{ $type }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label">
                                                                <span id="customer_initial_label">Customer Initials</span><span class="text-danger">*</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" name="customer_initial" class="form-control" placeholder="Enter Customer Initials" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Customer Code <span class="text-danger">*</span></label>  
                                                        </div>
                                                        <div class="col-md-4"> 
                                                            <input type="text" name="customer_code" class="form-control"/>
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Organization Type</label>  
                                                        </div> 
                                                        <div class="col-md-4">  
                                                            <select name="organization_type_id" class="form-select select2">
                                                                @foreach ($organizationTypes as $type)
                                                                <option value="{{ $type->id }}" {{ $type->name == 'Private Ltd' ? 'selected' : '' }}>
                                                                    {{ $type->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>  
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Sales Person</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control sales-person-autocomplete" placeholder="Type to search sales-person">
                                                            <input type="hidden" name="sales_person_id" class="sales-person-id">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Group</label>
                                                        </div>
                                                        <div class="col-md-4 pe-sm-0 mb-1 mb-sm-0">
                                                            <input type="text" name="subcategory_name" class="form-control category-autocomplete" placeholder="Type to search group">
                                                            <input type="hidden" name="subcategory_id" class="category-id">
                                                            <input type="hidden" name="category_type" class="category-type" value="Customer">
                                                            <input type="hidden" name="cat_initials" class="cat_initials-id" value="">
                                                        </div>
                                                    </div>
                                                    <p class="mb-0" style="color: red;"><b>Note*:</b> File must be 2MB max | Formats: pdf, jpg, jpeg, png</p>
                                                </div>

                                                 <div class="col-md-3 border-start">
                                                    <!-- <div class="row align-items-center mb-2">
                                                        <div class="col-md-12"> 
                                                            <label class="form-label text-primary"><strong>Status</strong></label>   
                                                            <div class="demo-inline-spacing">
                                                                @foreach ($status as $option)
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input
                                                                            type="radio"
                                                                            id="status_{{ strtolower($option) }}"
                                                                            name="status"
                                                                            value="{{ $option }}"
                                                                            class="form-check-input"
                                                                            {{ $option == 'active' ? 'checked' : '' }} >
                                                                            <label class="form-check-label fw-bolder" for="status_{{ strtolower($option) }}">
                                                                                {{ ucfirst($option) }}
                                                                            </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            @error('status')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                        </div> 
                                                    </div>  -->

                                                    <div class="row align-items-center mb-2">
                                                        <div class="col-md-12"> 
                                                            <label class="form-label text-primary"><strong>Stop Billing</strong></label>   
                                                            <div class="demo-inline-spacing">
                                                                @foreach ($options as $option)
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input
                                                                            type="radio"
                                                                            id="stop_billing_{{ strtolower($option) }}"
                                                                            name="stop_billing"
                                                                            value="{{ $option }}"
                                                                            class="form-check-input"
                                                                            {{ $option == 'No' ? 'checked' : '' }} >
                                                                            <label class="form-check-label fw-bolder" for="stop_billing_{{ strtolower($option) }}">
                                                                                {{ $option }}
                                                                            </label>
                                                                    </div>
                                                                @endforeach
                                                            </div> 
                                                            @error('stop_billing')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                        </div> 
                                                    </div> 
                                                </div> 
                                           </div>
                                            <!--End customer -->
											<div class="mt-1">
												<ul class="nav nav-tabs border-bottom mt-25" role="tablist">
													<li class="nav-item">
														<a class="nav-link active" data-bs-toggle="tab" href="#payment">General Details</a>
													</li>
                                                    <li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#Shipping">Addresses</a>
													</li>
                                                    <li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#Financial">Financial</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#amend">Contact Persons</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#schedule">Compliances</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#send">Bank Info</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#latestrates">Notes</a>
													</li>
                                                    <li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#Items">Items</a>
													</li>

												</ul>

												 <div class="tab-content pb-1 px-1">
                                                     <!--Start customer Details -->
                                                     <div class="tab-pane active" id="payment">
                                                            <div class="row align-items-center mb-1" id="reldCustomerDropdown">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Parent Customer</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" name="reld_customer_name" class="form-control parent-customer-autocomplete" placeholder="Type to search customers">
                                                                     <input type="hidden" name="reld_customer_id" class="reld_customer_id">
                                                                </div>
                                                            </div>
                                                            <!-- Related Party -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Related Party</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="Related" name="related_party">
                                                                        <label class="form-check-label" for="Related">Yes/No</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1" id="contraLedger" style="display: none;">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Contra Ledger</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" name="contra_ledger_name" class="form-control contra-ledger-autocomplete" placeholder="Type to search contra ledger">
                                                                     <input type="hidden" name="contra_ledger_id" class="contra_ledger_id">
                                                                </div>
                                                            </div>

                                                          <!-- Group Organizations -->
                                                            <div class="row align-items-center mb-1" id="groupOrganizationsDropdown" style="display: none;">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Related Organizations<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select select2" name="enter_company_org_id" id="enter_company_org_id">
                                                                       <option value="">Select</option>
                                                                        @foreach ($groupOrganizations as $organization)
                                                                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <!-- Customer Email -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Customer Email</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='mail'></i></span>
                                                                        <input type="email" class="form-control" name="email" placeholder="">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Customer Phone and Mobile -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Customer Phone</label>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='phone'></i></span>
                                                                        <input type="text" class="form-control numberonly" name="phone" placeholder="Work Phone">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='smartphone'></i></span>
                                                                        <input type="text" class="form-control numberonly" id="phone_mobile" name="mobile" placeholder="Mobile">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Customer Whatsapp Number -->
                                                            <div class="row mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Customer Whatsapp Number</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='phone'></i></span>
                                                                        <input type="text" class="form-control numberonly" id="whatsapp_number" name="whatsapp_number">
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" name="whatsapp_same_as_mobile">
                                                                        <label class="form-check-label" for="colorCheck1">Same as Mobile No.</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Notification -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Notification</label>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" id="Email" name="notification[]" value="email">
                                                                            <label class="form-check-label" for="Email">Email</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" id="SMS" name="notification[]" value="sms">
                                                                            <label class="form-check-label" for="SMS">SMS</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input " id="Whatsapp" name="notification[]" value="whatsapp">
                                                                            <label class="form-check-label" for="Whatsapp">Whatsapp</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- PAN Number and Attachment -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">PAN</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="pan_number">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="file" class="form-control" name="pan_attachment"  onchange="simpleFileValidation(this)">
                                                                </div>
                                                            </div>

                                                            <!-- TIN Number and Attachment -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">TIN No.</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="tin_number">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="file" class="form-control" name="tin_attachment"  onchange="simpleFileValidation(this)">
                                                                </div>
                                                            </div>

                                                            <!-- Aadhar Number and Attachment -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Aadhar No.</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="aadhar_number">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="file" class="form-control" name="aadhar_attachment" onchange="simpleFileValidation(this)">
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mb-1" style="margin-top:24px">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Currency<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select select2" id="currencySelect" name="currency_id" style="height: 40px;">
                                                                        @foreach($currencies as $currency)
                                                                        <option value="{{ $currency->id }}" data-short-name="{{ $currency->short_name }}" 
                                                                            @if(isset($organization) && $currency->id == $organization->currency_id) selected @endif>
                                                                            {{ $currency->name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <!-- Org Currency -->
                                                                <div class="col-md-2" id="orgCurrencyRow" style="display: none;position: relative;">
                                                                    <label class="form-label" style="position: absolute;top:-20px">Org Currency</label>
                                                                    <div class="input-group" style="height: 38px;">
                                                                        <span class="input-group-text bg-light" id="orgCurrencySymbol">Code</span>
                                                                        <input type="text" class="form-control" id="orgCurrency" name="org_currency" readonly>
                                                                    </div>
                                                                </div>

                                                                  <!-- Company Currency -->
                                                                  <div class="col-md-2" id="companyCurrencyRow" style="display: none;position: relative;">
                                                                    <label class="form-label" style="position: absolute;top:-20px">Company Currency</label>
                                                                    <div class="input-group" style="height: 38px;">
                                                                        <span class="input-group-text bg-light" id="companyCurrencySymbol">Code</span>
                                                                        <input type="text" class="form-control" id="companyCurrency" name="company_currency" readonly>
                                                                    </div>
                                                                </div>

                                                                <!-- Group Currency -->
                                                                <div class="col-md-2" id="groupCurrencyRow" style="display: none;position: relative;">
                                                                    <label class="form-label" style="position: absolute;top:-20px">Group Currency</label>
                                                                    <div class="input-group" style="height: 38px;">
                                                                        <span class="input-group-text bg-light" id="groupCurrencySymbol">Code</span>
                                                                        <input type="text" class="form-control" id="groupCurrency" name="group_currency" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-1">
                                                                    <a href="{{ route('exchange-rates.index') }}" target="_blank" class="voucehrinvocetxt mt-0">Add Exchange Rate</a>
                                                                </div>
                                                                  <!-- Hidden Transaction Date Input -->
                                                               <input type="hidden" id="transactionDate" name="transaction_date">
                                                            </div>
                                                            <!-- Opening Balance -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Opening Balance</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group">
                                                                        <span class="input-group-text bg-light" id="currencySymbol">INR</span>
                                                                        <input type="text" class="form-control" name="opening_balance">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Payment Terms -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Payment Term<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select select2" name="payment_terms_id">
                                                                        <option value="">Select</option>
                                                                        @foreach($paymentTerms as $paymentTerm)
                                                                            <option value="{{ $paymentTerm->id }}">{{ $paymentTerm->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Upload Documents -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Upload Documents</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="file" class="form-control" name="other_documents[]" multiple  onchange="simpleFileValidation(this)">
                                                                </div>
                                                            </div>
                                                     </div>

                                                        <!--End customer Details -->
                                                        <div class="tab-pane" id="Shipping">
                                                            <div class="table-responsive"> 
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th style="width:150px;">Country<span class="text-danger">*</span></th>
                                                                            <th style="width:150px;">State<span class="text-danger">*</span></th>
                                                                            <th style="width:150px;">City<span class="text-danger">*</span></th>
                                                                            <th>Pin Code<span class="text-danger">*</span></th>
                                                                            <th>Address<span class="text-danger">*</span></th>
                                                                            <th>Type</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="address-table-body">
                                                                        <tr class="address-row" data-index="0">
                                                                            <td class="index">1</td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 country-input" name="addresses[0][country]" placeholder="Search Country">
                                                                                <input type="hidden" name="addresses[0][country_id]" class="country-id">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 state-input" name="addresses[0][state]" placeholder="Search State">
                                                                                <input type="hidden" name="addresses[0][state_id]" class="state-id">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 city-input" name="addresses[0][city]" placeholder="Search City">
                                                                                <input type="hidden" name="addresses[0][city_id]" class="city-id">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control numberonly mw-100" name="addresses[0][pincode]" placeholder="Pincode">
                                                                                <input type="hidden" name="addresses[0][pincode_master_id]" class="pincode-id">
                                                                            </td>

                                                                            <td><input type="text" class="form-control mw-100" name="addresses[0][address]"></td> 
                                                                            <td>
                                                                                <div class="demo-inline-spacing">
                                                                                    <div class="form-check form-check-primary mt-25">
                                                                                        <input type="radio" id="isDefaultPurchase0" name="addresses[0][is_billing]" value="1" class="form-check-input">
                                                                                        <label class="form-check-label fw-bolder" for="isDefaultPurchase0">Billing</label>
                                                                                    </div>
                                                                                    <div class="form-check form-check-primary mt-25">
                                                                                        <input type="radio" id="isDefaultSelling0" name="addresses[0][is_shipping]" value="1" class="form-check-input">
                                                                                        <label class="form-check-label fw-bolder" for="isDefaultSelling0">Shipping</label>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-address"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-address"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                         <!--Start Financial -->
                                                         <div class="tab-pane" id="Financial">
                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2">
                                                                        <label class="form-label">Create Ledger?</label>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="hidden" name="create_ledger" value="0">
                                                                            <input type="checkbox" class="form-check-input" id="create_customer_ledger" name="create_ledger"
                                                                                value="1">
                                                                            <label class="form-check-label" for="create_customer_ledger">Yes</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
    
                                                               <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2">
                                                                        <label for="ledger_name" class="form-label">Ledger</label>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="text" id="ledger_name" name="ledger_name" class="form-control customer-ladger-autocomplete" placeholder="Type to search...">
                                                                        <input type="hidden" id="ledger_id" name="ledger_id" class="ladger-id">
                                                                    </div>
                                                                </div>
                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2">
                                                                        <label for="ledger_group_name" class="form-label">Ledger Group</label>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <select id="ledger_group_name" name="ledger_group_id" class="form-control ledger-group-select">
                                                                        </select>
                                                                    
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2"> 
                                                                        <label for="pricing_type" class="form-label">Pricing Type</label>  
                                                                    </div>  
                                                                    <div class="col-md-3"> 
                                                                        <select id="pricing_type" name="pricing_type" class="form-select select2">
                                                                            <option value="">Select</option>
                                                                            <option value="fixed">Fixed</option>
                                                                            <option value="variable">Variable</option>
                                                                        </select>
                                                                    </div> 
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2"> 
                                                                        <label for="credit_limit" class="form-label">Credit Limit</label>  
                                                                    </div>  
                                                                    <div class="col-md-3"> 
                                                                        <input type="number" id="credit_limit" name="credit_limit" class="form-control decimal-only" placeholder="Enter credit limit"/>
                                                                    </div> 
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2"> 
                                                                        <label for="credit_days" class="form-label">Credit Days</label>  
                                                                    </div>  
                                                                    <div class="col-md-3"> 
                                                                        <input type="number" id="credit_days" name="credit_days" class="form-control numberonly" placeholder="Enter credit days" />
                                                                        <input type="hidden" name="credit_days_editable" value="0">
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" id="credit_days_allowed_checkbox" name="credit_days_editable" value="1">
                                                                            <label class="form-check-label" for="credit_days_allowed_checkbox">Allowed to Change</label>
                                                                        </div>
                                                                    </div> 
                                                                </div>
                                                                <input type="hidden" id="hidden_ledger_customer_name" name="hidden_ledger_customer_name" value="">
                                                                <input type="hidden" id="hidden_ledger_customer_code" name="hidden_ledger_customer_code" value="">
                                                              
                                                                <!-- <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2">
                                                                        <label class="form-label">On Account Required?</label>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" id="OnAccountRequired" name="on_account_required">
                                                                            <label class="form-check-label" for="OnAccountRequired">Yes/No</label>
                                                                        </div>
                                                                    </div>
                                                                </div> -->
                                                         </div>
                                                        <!--End Financial -->

                                                       <!--Start Contact -->
                                                       <div class="tab-pane" id="amend">
                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped" id="contactsTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th>Salutation</th>
                                                                            <th>Name</th>
                                                                            <th>Email</th>
                                                                            <th>Mobile</th>
                                                                            <th>Work Phone</th>
                                                                            <th>Primary</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr id="rowTemplate">
                                                                            <td>1</td>
                                                                            <td class="px-1">
                                                                                <select class="form-select px-1" name="contacts[0][salutation]">
                                                                                    <option value="">Select</option>
                                                                                       @foreach($titles as $title)
                                                                                            <option value="{{ $title }}">{{ $title }}</option>
                                                                                        @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td class="px-1"><input type="text" name="contacts[0][name]" class="form-control "></td>
                                                                            <td class="px-1"><input type="email" name="contacts[0][email]" class="form-control"></td>
                                                                            <td class="px-1"><input type="text" name="contacts[0][mobile]" class="form-control numberonly"></td>
                                                                            <td class="px-1"><input type="text" name="contacts[0][phone]" class="form-control  numberonly"></td>
                                                                            <td>
                                                                                <input type="radio" name="contacts[0][primary]" value="1" class="primary-radio">
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-contact-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-contact-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                       <!--End Contact -->

                                                       <div class="tab-pane" id="schedule">
                                                            <div class="row">
                                                                <!-- TDS Details -->
                                                                <div class="col-md-6">
                                                                    <h5 class="mt-1 mb-2 text-dark"><strong>TDS Details</strong></h5>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TDS Applicable</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" name="compliance[tds_applicable]" id="tdsApplicableIndia" class="form-check-input" checked>
                                                                                <label class="form-check-label" for="tdsApplicableIndia">Yes/No</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Wef Date</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="date" name="compliance[wef_date]" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TDS Certificate No.</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" name="compliance[tds_certificate_no]" class="form-control numberonly">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TDS Tax Percentage</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" name="compliance[tds_tax_percentage]" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TDS Category</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" name="compliance[tds_category]" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TDS Value Cap</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" name="compliance[tds_value_cab]" class="form-control numberonly">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TAN Number</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" name="compliance[tan_number]" class="form-control">
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
                                                                                    <input type="radio" id="gstRegisteredIndia" name="compliance[gst_applicable]" value="1" class="form-check-input" checked>
                                                                                    <label class="form-check-label fw-bolder" for="gstRegisteredIndia">Registered</label>
                                                                                </div>
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="gstNonRegisteredIndia" name="compliance[gst_applicable]" value="0"class="form-check-input">
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
                                                                            <input type="text" name="compliance[gstin_no]"  id="gstinNo" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Legal Name</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" name="compliance[gst_registered_name]" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">GSTIN Reg. Date</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="date" name="compliance[gstin_registration_date]" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Upload Certificate</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="file" name="compliance[gst_certificate][]" multiple class="form-control"  onchange="simpleFileValidation(this)">
                                                                            <div id="gstCertificateLinks"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                       </div>
                                                           <!-- Start Bank Info -->
                                                           <div class="tab-pane" id="send">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th>Bank Name</th>
                                                                            <th>Beneficiary Name</th>
                                                                            <th>Account Number</th>
                                                                            <th>Re-enter Account No.</th>
                                                                            <th>IFSC Code</th>
                                                                            <th>Primary</th>
                                                                            <th>Cancel Cheque</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="bank-info-container">
                                                                        <!-- Initial bank info entry -->
                                                                        <tr class="bank-info-row" data-index="0">
                                                                            <td>#</td>
                                                                            <td><input type="text" class="form-control mw-100 bank-name" name="bank_info[0][bank_name]" /></td>
                                                                            <td><input type="text" class="form-control mw-100" name="bank_info[0][beneficiary_name]" /></td>
                                                                            <td><input type="text" class="form-control mw-100" name="bank_info[0][account_number]" /></td>
                                                                            <td><input type="text" class="form-control mw-100" name="bank_info[0][re_enter_account_number]" /></td>
                                                                            <td><input type="text" class="form-control mw-100 ifsc-code" name="bank_info[0][ifsc_code]" /></td>
                                                                            <td>
                                                                             <input type="radio" name="bank_info[0][primary]" value="1" class="primary-radio">
                                                                            </td>

                                                                            <td>
                                                                                <div><input type="file" class="form-control mw-100" name="bank_info[0][cancel_cheque][]" multiple  onchange="simpleFileValidation(this)" /></div></td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-bank-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-bank-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <!-- End Bank Info -->

                                                      <!--Start Note -->
														<div class="tab-pane" id="latestrates">
                                                            <label class="form-label">Notes (For Internal Use)</label>  
												            <textarea class="form-control" name="notes[remark]" placeholder="Enter Notes...."></textarea>
														</div> 
                                                     <!--End Note -->

                                                     <!-- Item start -->
                                                        <div class="tab-pane" id="Items">
                                                        <div class="table-responsive-md"> 
                                                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="vendorTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>S.NO</th>
                                                                        <th width="300px">Item</th>
                                                                            <th>Customer Item Code</th>
                                                                            <th>Customer Item Name</th>
                                                                            <th>Customer Item Details</th>
                                                                            <th id="sell-price-header">Sell Price</th>
                                                                            <th>Sell Uom</th>
                                                                            <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="vendorTableBody">
                                                                <tr id="row-0">
                                                                            <td>1</td>
                                                                            <td>
                                                                                <input type="text" name="customer_item[0][item_name]" class="form-control mw-100 vendor-autocomplete" data-id="0" placeholder="Search Item">
                                                                                <input type="hidden" id="item-id_0" name="customer_item[0][item_id]" class="item-id" value="">
                                                                            </td>
                                                                            <td><input type="text" name="customer_item[0][item_code]" class="form-control mw-100"></td>
                                                                            <td><input type="text" name="customer_item[0][item_name]" class="form-control mw-100"></td>
                                                                            <td><input type="text" name="customer_item[0][item_details]" class="form-control mw-100"></td>
                                                                            <td><input type="text" name="customer_item[0][sell_price]" id="sell-price_0" class="form-control sell-price-approved-customer mw-100"></td>
                                                                            <td><select name="customer_item[0][uom_id]"  id="uom_0" class="form-select mw-100" disabled></select></td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-item"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-item"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        </div>
                                                      <!-- Item End -->
												</div> 
											 
											</div>
								</div>

                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                     
                </section>
                <div class="modal fade" id="gstinModal" tabindex="-1" aria-labelledby="gstinModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header p-0 bg-transparent">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body px-sm-4 mx-50 pb-2">
                                        <h1 class="text-center mb-1" id="gstinModalLabel">Enter GSTIN Details</h1>
                                        <p class="text-center">Enter the GSTIN number below.</p>
                                        
                                        <div class="form-group mt-2">
                                            <label class="form-label" for="gstinInput">GSTIN No.</label>
                                            <input type="text" class="form-control" id="gstinInput" placeholder="Enter GSTIN Number" maxlength="15">
                                            <small id="gstinHelp" class="form-text text-muted d-block mt-50">GSTIN should be 15 characters long.</small>
                                        </div>
                                        
                                        <div id="gstinDetails" class="mt-2 font-small-3"></div>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="fetchGstDetails">Fetch Details</button>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>
        </div>
    </div>
 </form>
    <!-- END: Content-->
@endsection
@section('scripts')
 <!-- for item -->
 <script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        var selectedItemIds = [];
        function fetchUOMs(itemId, rowId) {
            $.ajax({
                url: "{{ url('/vendors/get-uoms') }}", 
                method: 'POST',
                data: { item_id: itemId },
                success: function(data) {
                    var uomSelect = $('#uom_' + rowId);
                    uomSelect.empty();
                    uomSelect.append('<option value="">Select</option>');
                    data.alternate_uoms.forEach(function(uom) {
                        uomSelect.append('<option value="' + uom.id + '">' + uom.name + '</option>');
                    });

                    uomSelect.prop('disabled', true); 
                },
                error: function(xhr) {
                    console.error('Error fetching UOM data:', xhr.responseText);
                }
            });
        }
        $('#vendorTable').on('input', '.sell-price-approved-customer', function () {
            var rowId = $(this).closest('tr').attr('id').split('-')[1]; 
            var sellPrice = $('#sell-price_' + rowId).val(); 
            if (sellPrice && !isNaN(sellPrice)) {
                $('#uom_' + rowId).prop('disabled', false);
            } else {
                $('#uom_' + rowId).prop('disabled', true);
            }
        });
        function initializeVendorAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ url('/items/search') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            term: request.term 
                        },
                        success: function(data) {
                            var filteredData = data.filter(function(item) {
                                return !selectedItemIds.includes(item.id); 
                            });
                            response($.map(filteredData, function(item) {
                                return {
                                    id: item.id,
                                    label: item.label,
                                    value: item.value,
                                    code: item.code || '', 
                                    item_id: item.id
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching vendor data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label); 
                    var rowId = $(this).data('id');
                    $('#item-id_' + rowId).val(ui.item.id);
                    $('#item-code_' + rowId).val(ui.item.code);
                    
                    if (!selectedItemIds.includes(ui.item.id)) {
                        selectedItemIds.push(ui.item.id);
                    }
                    fetchUOMs(ui.item.id, rowId);
                    return false;
                },
                change: function(event, ui) {
                    var rowId = $(this).data('id');
                    var currentItemId = $('#item-id_' + rowId).val(); 
                    if (!ui.item) {
                        $(this).val("");  
                        $('#item-id_' + rowId).val(''); 
                        if (currentItemId && selectedItemIds.includes(parseInt(currentItemId))) {
                            selectedItemIds.splice(selectedItemIds.indexOf(parseInt(currentItemId)), 1);
                        }
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }
        function updateRowIndices() {
            var $rows = $('#vendorTable tbody tr');
            $('#vendorTable tbody tr').each(function(index) {
                var $row = $(this);
                $row.find('td:first').text(index + 1);
                $row.find('input, select').each(function() {
                    var $this = $(this);
                    var name = $this.attr('name');
                    if (name) {
                        $this.attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                    var id = $this.attr('id');
                    if (id) {
                        $this.attr('id', id.replace(/\d+$/, index));
                    }
                    var dataId = $this.data('id');
                    if (dataId !== undefined) {
                        $this.data('id', index);
                    }
                });
                $row.attr('id', 'row-' + index);
                if ($rows.length === 1) {
                    $(this).find('.delete-item').hide(); 
                    $(this).find('.add-item').show(); 
                } else {
                    $(this).find('.delete-item').show(); 
                    $(this).find('.add-item').toggle(index === 0); 
                }
                var uomSelect = $row.find('select[id^="uom_"]');
                uomSelect.prop('disabled', true);
                var sellPriceInput = $row.find('input[id^="sell-price_"]');
                var sellPrice = sellPriceInput.val();
                if (sellPrice && !isNaN(sellPrice)) {
                    uomSelect.prop('disabled', false); 
                }
            });
            initializeVendorAutocomplete(".vendor-autocomplete");
        }
        $('#vendorTable').on('click', '.add-item', function(e) {
            e.preventDefault();
            
            var newRow = $('#vendorTable tbody tr:first').clone();
            var rowCount = $('#vendorTable tbody tr').length;

            newRow.find('td:first').text(rowCount + 1);
            newRow.attr('id', 'row-' + rowCount);

            newRow.find('input').each(function() {
                $(this).val('');
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, rowCount)); 
                }
                var dataId = $(this).data('id');
                if (dataId !== undefined) {
                    $(this).data('id', rowCount); 
                }
            });
            newRow.find('select').each(function() {
                var selectId = $(this).attr('id');
                if (selectId) {
                    $(this).attr('id', selectId.replace(/\d+$/, rowCount)); 
                }
                $(this).prop('disabled', true);
            });
            
            $('#vendorTable tbody').append(newRow);
            updateRowIndices();
            feather.replace(); 
            applyCapsLock();
        });

        $('#vendorTable').on('click', '.delete-item', function(e) {
            e.preventDefault();
            var rowId = $(this).closest('tr').find('input[data-id]').data('id');
            var itemIdToRemove = $('#item-id_' + rowId).val();
            if (itemIdToRemove && selectedItemIds.includes(parseInt(itemIdToRemove))) {
                selectedItemIds.splice(selectedItemIds.indexOf(parseInt(itemIdToRemove)), 1);
            }
            
            $(this).closest('tr').remove();
            updateRowIndices();
        });

        $('#addVendor').on('click', function(e) {
            e.preventDefault();
            $('#vendorTable').find('.add-item').first().trigger('click'); 
        });
        updateRowIndices();
        initializeVendorAutocomplete(".vendor-autocomplete");
    });
</script>

 <script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        var titles = @json($titles);
        var $contactsTableBody = $('#contactsTable tbody');
        function updateDropdown($select) {
            var options = '<option value="">Select</option>' + titles.map(function(title) {
                return '<option>' + title + '</option>';
            }).join('');
            $select.html(options);
            console.log("Dropdown updated with titles:", titles); 
        }

        function updateIcons() {
            var rows = $contactsTableBody.find('tr');
            var $rows = $('#contactsTable tbody tr');
            rows.each(function(index) {
                var $row = $(this);
                $row.find('td').eq(0).text(index + 1); 

                $row.find('input[name]').each(function() {
                    var name = $(this).attr('name');
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']')); 
                });
                if ($rows.length === 1) {
                    $(this).find('.delete-contact-row').hide(); 
                    $(this).find('.add-contact-row').show(); 
                } else {
                    $(this).find('.delete-contact-row').show(); 
                    $(this).find('.add-contact-row').toggle(index === 0); 
                } 
            });
        }

        function addContactRow() {
            var rowCount = $contactsTableBody.children().length;
            var $currentRow = $contactsTableBody.find('tr:last');
            var $newRow = $currentRow.clone();
            $newRow.find('[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']')); 
                $(this).val(''); 
            });
            $newRow.find('input[type=radio]').prop('checked', false);
            updateDropdown($newRow.find('.form-select'));
            $contactsTableBody.append($newRow);
            feather.replace();
            updateIcons();
            applyCapsLock();
        }
        $contactsTableBody.on('click', '.delete-contact-row', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            $contactsTableBody.children().each(function(index) {
                $(this).find('td:first').text(index + 1); 
                $(this).find('[name]').each(function() {
                    var name = $(this).attr('name');
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']')); 
                });
            });

            updateIcons();
        });

        $contactsTableBody.on('change', 'input[type=radio]', function() {
            var $radioButtons = $contactsTableBody.find('input[type=radio]');
            $radioButtons.prop('checked', false); 
            $(this).prop('checked', true);
            $radioButtons.each(function() {
                var value = $(this).is(':checked') ? 1 : 0;
                $(this).val(value);
            });
        });
        if ($contactsTableBody.children().length === 0) {
            addContactRow(); 
        } else {
            updateIcons(); 
        }
        $(document).on('click', '.add-contact-row', function(e) {
            e.preventDefault();
            addContactRow();
        });
        updateIcons();
    });
</script>
>
<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }

        function initializeAutocomplete($row) {
            // Country Autocomplete
            $row.find('.country-input').autocomplete({
                source: function(request, response) {
                    $.get('/countries', { term: request.term }, function(data) {
                        response(data.data.countries.map(country => ({
                            label: country.label, 
                            value: country.value,
                            id: country.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('tr').find('.country-id').val(ui.item.id);  
                    const $stateInput = $(this).closest('tr').find('.state-input');
                    $stateInput.val('').removeAttr('data-state-id');
                    const $cityInput = $(this).closest('tr').find('.city-input');
                    $cityInput.val('').removeAttr('data-city-id');
                    const $pincodeInput = $(this).closest('tr').find('input[name*="[pincode]"]');
                    $pincodeInput.val('');
                    const $pincodeIdInput = $(this).closest('tr').find('input[name*="[pincode_master_id]"]');
                    $pincodeIdInput.val('');
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            // State Autocomplete
            $row.find('.state-input').autocomplete({
                source: function(request, response) {
                    const countryId = $(this.element).closest('tr').find('.country-id').val();
                    if (!countryId) {
                        response([]);
                        return;
                    }
                    $.get(`/states/${countryId}`, { term: request.term }, function(data) {
                        response(data.data.states.map(state => ({
                            label: state.label,
                            value: state.value,
                            id: state.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('tr').find('.state-id').val(ui.item.id);  
                    const $cityInput = $(this).closest('tr').find('.city-input');
                    $cityInput.val('').removeAttr('data-city-id');
                    const $pincodeInput = $(this).closest('tr').find('input[name*="[pincode]"]');
                    $pincodeInput.val('');
                    const $pincodeIdInput = $(this).closest('tr').find('input[name*="[pincode_master_id]"]');
                    $pincodeIdInput.val('');
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            // City Autocomplete
            $row.find('.city-input').autocomplete({
                source: function(request, response) {
                    const stateId = $(this.element).closest('tr').find('.state-id').val();
                    if (!stateId) {
                        response([]);
                        return;
                    }
                    $.get(`/cities/${stateId}`, { term: request.term }, function(data) {
                        response(data.data.cities.map(city => ({
                            label: city.label,
                            value: city.value,
                            id: city.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('tr').find('.city-id').val(ui.item.id); 
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            // Pincode Autocomplete
            $row.find('input[name*="[pincode]"]').autocomplete({
                source: function(request, response) {
                    const stateId = $(this.element).closest('tr').find('.state-id').val();
                    if (!stateId) {
                        response([]);
                        return;
                    }
                    $.get(`/pincodes/${stateId}`, { term: request.term }, function(data) {
                        response(data.data.pincodes.map(pincode => ({
                            label: pincode.label,
                            value: pincode.value,
                            id: pincode.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);  
                    $(this).closest('tr').find('input[name*="[pincode_master_id]"]').val(ui.item.id);  
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });
        }

        $('#address-table-body .address-row').each(function() {
            initializeAutocomplete($(this));
        });

        $(document).on('click', '.add-address', function(e) {
            e.preventDefault();
            const $lastRow = $('#address-table-body .address-row').last();
            const index = $lastRow.data('index') + 1;
            const $newRow = $lastRow.clone().attr('data-index', index);
            $newRow.find('input').val('');
            $newRow.find('input').removeAttr('data-country-id data-state-id data-city-id data-gst-state-id data-gst-country-id data-gst-state data-gst-country');
            $newRow.find('input[type="radio"]').prop('checked', false);
            $('#address-table-body').append($newRow);

            const $gstRows = $('#address-table-body .address-row').filter(function() {
                return $(this).find('input[name*="[state]"]').attr('data-gst-state-id') &&
                    $(this).find('input[name*="[state]"]').attr('data-gst-state') &&
                    $(this).find('input[name*="[country]"]').attr('data-gst-country-id') &&
                    $(this).find('input[name*="[country]"]').attr('data-gst-country');
            });

            const $gstRow = $gstRows.last();
            if ($gstRow.length) {
                const gstCountryId = $gstRow.find('input[name*="[country]"]').attr('data-gst-country-id');
                const gstCountryName = $gstRow.find('input[name*="[country]"]').val();
                const gstStateId = $gstRow.find('input[name*="[state]"]').attr('data-gst-state-id');
                const gstStateName = $gstRow.find('input[name*="[state]"]').val();
                $newRow.find('input[name*="[country_id]"]').val(gstCountryId);
                $newRow.find('input[name*="[country]"]').val(gstCountryName).prop('disabled', true);
                $newRow.find('input[name*="[state_id]"]').val(gstStateId);
                $newRow.find('input[name*="[state]"]').val(gstStateName).prop('disabled', true);
            }
            initializeAutocomplete($newRow);
            updateRowIndexes();
            handleRadioSelection();
            applyCapsLock();
        });

        $(document).on('click', '.delete-address', function(e) {
            e.preventDefault();
            if ($('#address-table-body .address-row').length > 1) {
                $(this).closest('.address-row').remove();
                updateRowIndexes();
            }
        });

        function updateRowIndexes() {
            var $rows = $('#address-table-body tr'); 
            $('#address-table-body .address-row').each(function(index) {
                $(this).find('.index').text(index + 1);
                $(this).find('input, select').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, `[${index}]`));
                });
                if ($rows.length === 1) {
                    $(this).find('.delete-address').hide(); 
                    $(this).find('.add-address').show(); 
                } else {
                    $(this).find('.delete-address').show(); 
                    $(this).find('.add-address').toggle(index === 0); 
                }  
            });
        }

        function handleRadioSelection() {
            $('#address-table-body').on('change', 'input[type="radio"][name*="[is_billing]"]', function() {
                $('#address-table-body input[type="radio"][name*="[is_billing]"]').not(this).prop('checked', false);
                $(this).val('1');
            });

            $('#address-table-body').on('change', 'input[type="radio"][name*="[is_shipping]"]', function() {
                $('#address-table-body input[type="radio"][name*="[is_shipping]"]').not(this).prop('checked', false);
                $(this).val('1');
            });
        }

        updateRowIndexes();
        handleRadioSelection();
        applyCapsLock();
    });
</script>

<script>
$(document).ready(function() {
    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();  
            $(this).val(value); 
        });
    }
    var $bankTableBody = $('#bank-info-container');
    var index = $bankTableBody.children('.bank-info-row').length;

    function updateRowIndices() {
        var $rows = $('#bank-info-container tr'); 
        $rows.each(function (index) {
            $(this).find('td:first').text(index + 1);
            $(this).find('input, select').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, index));
                }
            });
            $(this).attr('id', `row-${index}`);
            if ($rows.length === 1) {
                $(this).find('.delete-bank-row').hide(); 
                $(this).find('.add-bank-row').show(); 
            } else {
                $(this).find('.delete-bank-row').show(); 
                $(this).find('.add-bank-row').toggle(index === 0); 
            }
        });
   }
   
     function addNewRow() {
        let $template = $bankTableBody.find('.bank-info-row:first').clone();
        $template.attr('data-index', index++);

        $template.find('input').each(function() {
            let name = $(this).attr('name');
            if ($(this).attr('type') !== 'file') {
            $(this).val('');
            } else {
                $(this).val('');
                $(this).attr('onchange', 'simpleFileValidation(this)'); 
            }
            $(this).attr('name', name.replace(/\d+/, index - 1)); 
            $(this).removeClass('is-invalid');
        });
        $template.find('input[type=radio]').prop('checked', false).val('0');
        $template.find('input[type=file]').val('');
        $template.find('.file-link').parent().hide();
        $template.find('.ajax-validation-error-span').remove();
        $bankTableBody.append($template);
        updateRowIndices();
        feather.replace();
        applyCapsLock();
    }
    function fetchIfscDetails(ifscCode, $row) {
        if (!ifscCode) return;
        $.ajax({
            url: '/banks/ifsc/' + ifscCode,
            method: 'GET',
            success: function(data) {
                if (data.status) {
                    $row.find('.bank-name').val(data.data.BANK); // only bank name
                } else {
                    alert('Invalid IFSC code. Please try again.');
                    $row.find('.bank-name').val('');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching IFSC details:', textStatus, errorThrown);
                alert('An error occurred while fetching IFSC details. Please try again.');
                $row.find('.bank-name').val('');
            }
        });
    }
    // listener for IFSC input
    $bankTableBody.on('keyup', '.ifsc-code', function () {
        var ifscCode = $(this).val().trim();
        var $row = $(this).closest('tr');
        clearTimeout($.data(this, 'timer'));
        var wait = setTimeout(function () {
            fetchIfscDetails(ifscCode, $row);
        }, 300); // debounce
        $(this).data('timer', wait);
    });
    $('#bank-info-container').on('change', 'input[type=radio]', function() {
        $('#bank-info-container input[type=radio]').each(function() {
            $(this).prop('checked', false).val('0');
        });
        $(this).prop('checked', true).val('1');
    });

    $bankTableBody.on('click', '.delete-bank-row', function(e) {
        e.preventDefault();
        $(this).closest('.bank-info-row').remove();
        updateRowIndices();
    });

    $bankTableBody.on('click', '.add-bank-row', function(e) {
        e.preventDefault();
        addNewRow();
    });
    if ($bankTableBody.children('.bank-info-row').length === 0) {
        addNewRow(); 
    }
    updateRowIndices(); 
});
</script>

<script>
$(document).ready(function() {
    var today = new Date().toISOString().split('T')[0];
    $('#transactionDate').val(today);
    function fetchExchangeRates() {
        var transactionDate = $('#transactionDate').val();
        var currencyId = $('#currencySelect').val();
        $('#orgCurrencyRow, #groupCurrencyRow, #companyCurrencyRow').hide();
        if (currencyId && transactionDate) {
            $.ajax({
                url: '/exchange-rates/get-currency-exchange-rate',
                type: 'POST',
                data: {
                    currency: currencyId,
                    date: transactionDate,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        $('#orgCurrencyRow, #groupCurrencyRow, #companyCurrencyRow').show();
                        $('#orgCurrencySymbol').text(response.data.org_currency_code);
                        $('#groupCurrencySymbol').text(response.data.group_currency_code);
                        $('#companyCurrencySymbol').text(response.data.comp_currency_code);
                        $('#orgCurrency').val(response.data.org_currency_exg_rate);
                        $('#groupCurrency').val(response.data.group_currency_exg_rate);
                        $('#companyCurrency').val(response.data.comp_currency_exg_rate);
                        $('#submit-button').prop('disabled', false);
                        $('#save-draft-button').prop('disabled', false); 
                    } else {
                        alert(response.message);
                        $('#submit-button').prop('disabled', true);
                        $('#save-draft-button').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', error);
                    alert('An error occurred while fetching exchange rates.');
                    $('#submit-button').prop('disabled', true);
                    $('#save-draft-button').prop('disabled', true);
                }
            });
        } else {
            alert('Please select a currency and ensure the date is set.');
            $('#submit-button').prop('disabled', true);
            $('#save-draft-button').prop('disabled', true);
        }
    }
    $('#currencySelect').on('change', function() {
        fetchExchangeRates();
    });
    if ($('#currencySelect').val()) {
        fetchExchangeRates();
    }
});
</script>
<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        applyCapsLock();
    });
 </script>
 <script>
    $(document).ready(function() {
        const customerCodeType = '{{ $customerCodeType }}'; 
        const companyNameInput = $('input[name="company_name"]'); 
        const customerTypeInput = $('input[name="customer_type"]'); 
        const catInitialsInput = $('input[name="cat_initials"]');
        const subCatInitialsInput = $('input[name="sub_cat_initials"]');
        const customerInitialInput = $('input[name="customer_initial"]'); 
        const customerCodeInput = $('input[name="customer_code"]');
        if (customerCodeType === 'Manual') {
            customerCodeInput.prop('readonly', false); 
        } else {
            customerCodeInput.prop('readonly', true); 
        }
        function getCustomerInitials(companyName) {
            const cleanedCompanyName = companyName.replace(/[^a-zA-Z0-9\s]/g, '');
            const words = cleanedCompanyName.split(/\s+/).filter(word => word.length > 0); 
            let initials = '';
            if (words.length === 1) {
                initials = words[0].substring(0, 3).toUpperCase();
            } else if (words.length === 2) {
                initials = words[0].substring(0, 2).toUpperCase() + words[1][0].toUpperCase();
            } else if (words.length >= 3) {
                initials = words[0][0].toUpperCase() + words[1][0].toUpperCase() + words[2][0].toUpperCase();
            }
            return initials.substring(0, 3); 
        }

        function generateCustomerCode() {
            if (customerCodeType === 'Manual') {
                return; 
            }
            const companyName = companyNameInput.val().trim();
            const customerInitials = customerInitialInput.val().trim() || getCustomerInitials(companyName); 
            customerInitialInput.val(customerInitials); 
            const categoryInitials = (catInitialsInput.val() || '').trim();
            const subCategoryInitials = (subCatInitialsInput.val() || '').trim();
            const selectedCustomerType = customerTypeInput.filter(':checked').val();  
            let customerTypeCode = '';
            if (selectedCustomerType === 'Regular') {
                customerTypeCode = 'R'; 
            } else if (selectedCustomerType === 'Cash') {
                customerTypeCode = 'CA';   
            }
            
            $.ajax({
                url: '{{ route('generate-customer-code') }}',  
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', 
                    company_name: companyName,
                    customer_type: customerTypeCode,
                    customer_initials: customerInitials 
                },
                success: function(response) {
                    customerCodeInput.val((response.customer_code || '')); 
                },
                error: function() {
                    customerCodeInput.val(''); 
                }
            });
        }

        if (customerCodeType === 'Auto') {
            generateCustomerCode(); 
        }

        companyNameInput.on('input change', function() {
            const companyName = $(this).val().trim();  
            customerInitialInput.val(getCustomerInitials(companyName)); 
            if (customerCodeType === 'Auto') {
                generateCustomerCode(); 
            }
        });

        customerCodeInput.on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
        customerTypeInput.on('change', generateCustomerCode);
    });
</script>
<script>
    //related-checkbox-start
    $(document).ready(function() {
        $('#Related').change(function() {
            if ($(this).is(':checked')) {
                $('#groupOrganizationsDropdown').show();
                $('#contraLedger').show();
            } else {
                $('#groupOrganizationsDropdown').hide();
                $('#contraLedger').hide();
            }
        });
    });
     //related-checkbox-end
    //file-validation-start
    const ALLOWED_EXTENSIONS_SIMPLE = ['pdf', 'jpg', 'jpeg', 'png'];
    const ALLOWED_MIME_TYPES_SIMPLE = ['application/pdf', 'image/jpeg', 'image/png'];
    const MAX_FILE_SIZE_SIMPLE = 2048; 

    function simpleFileValidation(element) {
        const input = element;
        const files = Array.from(input.files);
        const dt = new DataTransfer();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileSize = (file.size / 1024).toFixed(2); 

            if (!ALLOWED_EXTENSIONS_SIMPLE.includes(fileExtension) || !ALLOWED_MIME_TYPES_SIMPLE.includes(file.type)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Only PDF, JPG, JPEG, PNG files are allowed.',
                    icon: 'error',
                });
                input.value = '';
                return;
            }

            if (fileSize > MAX_FILE_SIZE_SIMPLE) {
                Swal.fire({
                    title: 'Error!',
                    text: 'File size must not exceed 2MB.',
                    icon: 'error',
                });
                input.value = '';
                return;
            }

            dt.items.add(file);
        }

        input.files = dt.files;
    }
    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('customSwitch3');
        const hiddenInput = document.getElementById('status_hidden_input');
        hiddenInput.value = switchInput.checked ? 'active' : 'inactive';
        switchInput.addEventListener('change', function () {
            hiddenInput.value = switchInput.checked ? 'active' : 'inactive';
        });
    });
    //file-validation-end
    function initializeAutocomplete(selector, options) {
            const $input = $(selector);
            const hiddenFieldSelector = options.hiddenFieldSelector();
            const $ledgerGroupSelect = $(".ledger-group-select");
            const $ledgerGroupId = $(".ledger-group-id");
            const $createLedger = $('#create_customer_ledger');
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: options.url,
                        method: 'GET',
                        data: {
                            q: request.term,
                            type: options.type
                        },
                    success: function(data) {
                            let items = [];
                            if (data.status === false || !Array.isArray(data.data) || data.data.length === 0) {
                                items = [{
                                    label: data.message || 'No record found.',
                                    value: '',
                                    id: null,
                                    disabled: true
                                }];
                            } else {
                                items = data.data.map(item => ({
                                    label: item[options.labelField],
                                    value: item[options.labelField],
                                    id: item.id,
                                    ...item
                                }));
                            }

                            response(items);
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: options.minLength || 0,
                select: function(event, ui) {
                    if (ui.item && ui.item.id) {
                        $(hiddenFieldSelector).val(ui.item.id);
                        if (typeof options.onSelect === 'function') {
                            options.onSelect(ui.item);
                        }
                    }
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(hiddenFieldSelector).val('');
                        $ledgerGroupSelect.empty();
                        $ledgerGroupId.val('');
                        $createLedger.prop('disabled', false);
                    }
                },
                search: function(event, ui) {
                    if (event.originalEvent && event.originalEvent.type === 'focus') {
                        event.preventDefault();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            }).on('input', function() {
                if ($(this).val().trim() === '') {
                    $(hiddenFieldSelector).val('');
                    $ledgerGroupSelect.empty();
                    $ledgerGroupId.val('');
                    $createLedger.prop('checked', false);
                }
            });

            if (typeof options.onInitialize === 'function') {
                options.onInitialize();
            }
        }
        function updateLedgerGroupDropdownWithType(ledgerId, type) {
            const $ledgerGroupSelect = $(".ledger-group-select");
            const $ledgerGroupId = $(".ledger-group-id");
            $ledgerGroupSelect.empty();

            let url = '';
            if (ledgerId) {
                url = `/vendors/ledger/${ledgerId}/groups?type=${encodeURIComponent(type)}`;
            } else {
                url = `/vendors/ledgers/group-by-type?type=${encodeURIComponent(type)}`;
            }

            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    if (Array.isArray(data) && data.length) {
                        data.forEach(function(group) {
                            const option = new Option(group.name, group.id);
                            $ledgerGroupSelect.append(option);
                        });

                        const preselectedGroupId = $ledgerGroupId.val();
                        if (preselectedGroupId) {
                            $ledgerGroupSelect.val(preselectedGroupId).trigger('change');
                        }
                    } else {
                        console.warn('No groups found for type:', type);
                        $ledgerGroupId.val(''); 
                    }
                },
                error: function() {
                    $ledgerGroupId.val(''); 
                }
            });
        }

        $(document).ready(function () {
            const $createLedger = $('#create_customer_ledger');
            const $ledgerId = $('#ledger_id');
            const $ledgerName = $('#ledger_name');
            const $ledgerGroup = $('.ledger-group-select');
            const $ledgerGroupId = $(".ledger-group-id");
            const $hiddenName = $('#hidden_ledger_customer_name');
            const $hiddenCode = $('#hidden_ledger_customer_code');

            function toggleLedgerOption() {
                const hasLedger = $('.ladger-id').val()?.trim() || $ledgerId.val()?.trim();
                $createLedger.prop('disabled', !!hasLedger);
                if (hasLedger) {
                    $createLedger.prop('checked', false);
                }
            }
            
            toggleLedgerOption();
            const ledgerCheckInterval = setInterval(toggleLedgerOption, 500);

            $createLedger.change(function () {
                if ($(this).is(':checked')) {
                    $hiddenName.val($('input[name="company_name"]').val());
                    $hiddenCode.val($('input[name="customer_code"]').val());
                    $ledgerName.prop('disabled', true).val('');
                    updateLedgerGroupDropdownWithType(null, 'customer');
                } else {
                    $hiddenName.val('');
                    $hiddenCode.val('');
                    $ledgerName.prop('disabled', false);
                    $ledgerGroup.empty(); 
                    $ledgerGroupId.val('');
                }
            });

            $ledgerGroup.on('change', function() {
                $ledgerGroupId.val($(this).val());
            });

            initializeAutocomplete(".customer-ladger-autocomplete", {
                    url: '/search',
                    type: 'customerLadger',
                    labelField: 'name',
                    hiddenFieldSelector: function() { return '.ladger-id'; },
                    minLength: 0,
                    additionalFields: ['description'],
                    onSelect: function(selectedItem) {
                        if (selectedItem && selectedItem.id) {
                            updateLedgerGroupDropdownWithType(selectedItem.id, 'customer');
                            toggleLedgerOption(); 
                        }
                    },
                    onInitialize: function() {
                        const ledgerId = $('.ladger-id').val();
                        if (ledgerId) {
                            updateLedgerGroupDropdownWithType(ledgerId, 'customer');
                        }
                        toggleLedgerOption(); 
                    }
                });
        });
</script>
@endsection
