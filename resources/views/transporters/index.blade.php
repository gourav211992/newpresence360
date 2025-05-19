@extends('layouts.supplier')
@use('App\Helpers\ConstantHelper')
@section('content')
    <div class="app-content content ">
        <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
                <div class="content-wrapper container-xxl p-0">
                    <div class="content-header row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">{{ $typeName }}</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                            <li class="breadcrumb-item active">All Request</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div> 
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right"> 
                                <button class="btn btn-dark btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>    
                            </div>
                        </div>
                    </div>
                    <div class="content-body dasboardnewbody">
                    <!-- ChartJS section start -->
                        <section id="chartjs-chart">
                            <div class="row">
                                <div class="col-md-12 col-12">
                                    <div class="card  new-cardbox">
                                        <ul class="nav nav-tabs border-bottom" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#Live">Live Bids &nbsp;<span>({{count($bids)}})</span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#Delivery">Past Bids &nbsp;<span>({{ count($past_bids) }})</span></a>
                                            </li> 
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="Live">
                                                @forelse ($bids as $bid)
                                                @php
                                                            $bidStatus = $bid?->bids->first()?->bid_status;
                                                            $bidPrice = $bid?->bids->first()?->bid_price;
                                                            $vehicle_no = $bid?->bids->first()?->vehicle_number;
                                                            $driver_name = $bid?->bids->first()?->driver_name;
                                                            $driver_contact_no = $bid?->bids->first()?->driver_contact_no;
                                                            $remarks = $bid?->bids->first()?->transporter_remarks;
                                                            $driver_details = $vehicle_no."-".$driver_name."-".$driver_contact_no."-".$remarks;
                                                @endphp
                                                        

                
                                                @if (!in_array($bid->document_status, [ConstantHelper::DRAFT,ConstantHelper::COMPLETED]))
                                                <div class="summary-box tripsummarbpx mb-1 mx-1 mt-1">
                                                    
                                                    <div class="row align-items-right bid-row"> 
                                                        
                                                        <div class="col-md-2"> 
                                                        <h6 class="fw-bolder text-dark bid-end"> Trip Id : <span class="bid-id" request-id="{{ $bid->id }}">{{ $bid->document_number }}</span> </h6>
                                                        </div> 
                                                        <div class="col-md-6"></div>
                                                        <div class="col-md-4 mb-1 d-flex justify-content-end">  
                                                            
                                                            @if ($bid?->bids->isNotEmpty()) 
                                                                <h6 class="font-small-1">
                                                                @if ($bidPrice)
                                                                <span class="badge rounded-pill badge-light-primary bid-status badgeborder-radius mx-0.25 fw-bold font-small-2">Bid Price: {{ ucfirst($bidPrice) }} </span>&nbsp;&nbsp;&nbsp; 
                                                                @endif
                                                                <span class="badge rounded-pill badge-light-warning bid-status mx-0.25 badgeborder-radius fw-bold font-small-2">{{ ucfirst($bidStatus)}} </span>
                                                            @endif
                                                            @if(!in_array($bid->document_status,[ConstantHelper::CLOSED,ConstantHelper::COMPLETED]))
                                                            
                                                            @if ($bidStatus == "submitted" && !in_array($bid->document_status,[ConstantHelper::SHORTLISTED,ConstantHelper::CONFIRMED]))
                                                                <button bid-price = {{ $bid->bids->first()->bid_price }} request-id="{{ $bid->id }}" bid-status="{{ $bidStatus }}" data-bs-toggle="modal" data-bs-target="#addaccess" class="btn-sm btn-warning border-0 py-25 font-small-1 submit-bid">
                                                                    Change Bid
                                                                </button>   
                                                            @elseif($bidStatus == 'shortlisted')
                                                                <button bid-status="{{ $bidStatus }}" request-id="{{ $bid->id }}" data-bs-toggle="modal" data-bs-target="#assigndriver" class="btn-sm btn-primary border-0 py-25 font-small-1 submit-bid">
                                                                    Add Vehicle
                                                                </button>   
                                                            @elseif($bidStatus == 'confirmed')
                                                            <button bid-status="{{ $bidStatus }}" request-id="{{ $bid->id }}" id="cancel_trip" data-bs-toggle="modal" data-bs-target="#cancel-trip" class="btn-sm btn-danger border-0 py-25 font-small-1 submit-bid">Cancel Trip</button>  
                                                            <button bid-status="{{ $bidStatus }}" request-id="{{ $bid->id }}" driver-details = {{ json_encode($driver_details."-update") }} data-bs-toggle="modal" data-bs-target="#assigndriver" id="update_driver" class="btn-sm btn-warning border-0 py-25 font-small-1 submit-bid">Update Driver</button>  
                                                            @elseif($bidStatus == 'cancelled')
                                                            @elseif($bid->bids->isEmpty())
                                                                <button bid-status="{{ $bidStatus }}" request-id="{{ $bid->id }}" data-bs-toggle="modal" data-bs-target="#addaccess" class="btn-sm btn-primary border-0 py-25 font-small-1 submit-bid">
                                                                    Submit Bid
                                                                </button>    
                                                            @endif
                                                            @endif
                                                            </h6>
                                                        </div>
                                                    </div> 
                                                    
                                                    <div class="row align-items-center bid-data-row">  

                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Bid End Date</label>
                                                            <h6 class="fw-bolder text-dark bid-end">{{ $bid->bid_end }}</h6>
                                                        </div> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Vehicle Type</label>
                                                            <h6 class="fw-bolder text-dark vehicle-type">{{ $bid->vehicle->vehicle_type }}</h6>
                                                        </div>
                                                        <div class="col-md-2 "> 
                                                            <label class="form-label">Weight</label>
                                                            <h6 class="fw-bolder text-dark bid-weight">{{ $bid->total_weight }} {{ $bid->uom_code }}</h6>
                                                        </div> 
                                                        <div class="col-md-2 "> 
                                                            <label class="form-label">Date &amp; Time of Loading</label>
                                                            <h6 class="fw-bolder text-dark loading-date-time">{{ $bid->loading_date_time }}</h6>
                                                        </div>
                                                        <div class="col-md-2 "> 
                                                            <label class="form-label">Remarks</label>
                                                            <h6 class="fw-bolder text-dark loading-date-time"> {{ $bid->remarks?? " - " }}</h6>
                                                        </div>

                                                    </div>
                                                    <div class="row align-items-center bid-data-row">  
                                                        
                                                        <div class="col-md-6 mb-2">  
                                                        <h6 class="fw-bolder text-dark">&nbsp;<span class="bid-id"></span></h6>
                                                            <h6 class="font-small-2">
                                                                <div class="mb-50 d-flex align-items-center">Pick Up  
                                                                    <div class="d-flex align-items-center ms-25">
                                                                        @if($bid->pickup->isNotEmpty()) 
                                                                        <span class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 pickup-location">
                                                                            {{ $bid->pickup->first()->location_name }}
                                                                        </span>
                                                                        <span class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 pickup-location">
                                                                            Address : {{ $bid?->pickup?->first()?->address?->getDisplayAddressAttribute() }}
                                                                        </span>
                                                                        @if($bid->pickup->count() > 1)
                                                                        <span  data-bs-target="modal" data-target="#location" location-ids="{{ json_encode($bid->pickup->pluck('id')->toArray()) }}" onclick="showLocation(this,'Pickup')" class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 extra-pickup">
                                                                            +{{ $bid->pickup->count() - 1 }}
                                                                        </span>
                                                                        @endif
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </h6>
                                                        </div>
                                                        <div class="col-md-6 mb-2">  
                                                            <h6 class="fw-bolder text-dark">&nbsp;<span class="bid-id"></span></h6>
                                                                <h6 class="font-small-2">
                                                                <div class="mb-50 d-flex align-items-center"><span class="text-dark">Drop Off</span>        
                                                                    <div class="d-flex align-items-center ms-25">
                                                                        @if($bid->dropoff->isNotEmpty()) 
                                                                            <span class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 dropoff-location">
                                                                                {{ $bid->dropoff->first()->location_name }}
                                                                            </span>
                                                                            <span class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 dropoff-location">
                                                                                Address : {{ $bid?->dropoff?->first()?->address?->getDisplayAddressAttribute() }}    
                                                                            </span>
                                                                            @if($bid->dropoff->count() > 1)
                                                                                <span  data-bs-target="modal" data-target="#location" location-ids="{{ json_encode($bid->dropoff->pluck('id')->toArray()) }}" onclick="showLocation(this,'DropOff')" class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 extra-dropoff">
                                                                                    +{{ $bid->dropoff->count() - 1 }}
                                                                                </span>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </div>   
                                                            </h6>
                                                        </div> 
                                                    </div>
                                                </div>
                                                @endif

                                                @empty
                                                <div class="summary-box tripsummarbpx mb-1 mx-1 mt-1">
                                                    <div class="row align-items-right bid-row">   
                                                        <div class="col-md-12"> 
                                                        <h6 class="fw-bolder text-danger justify-text-center text-center"> No Live Bids Present At the moment </h6>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                                @endforelse    
                                            </div>
                                            <div class="tab-pane" id="Delivery">
                                                <div class="table-responsive">
                                                    <table class="table myrequesttablecbox loanapplicationlist">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Trip ID</th>
                                                                <th>Pick up</th>
                                                                <th>Drop Off</th>
                                                                <th>Vehcile Type</th>
                                                                <th>Weight</th>
                                                                <th>Date &amp; Time of Loading</th>
                                                                <th>End Bid Date</th>
                                                                <th>Bid Price</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($past_bids as $index=>$bid)
                                                            <tr>
                                                                <td>{{$index+1}}</td>
                                                                <td class="no-wrap">{{$bid->document_number}}</td>
                                                                <td>
                                                                    <div class="d-flex align-items-center ms-25">
                                                                        @if($bid->pickup->isNotEmpty()) 
                                                                        <span data-bs-target="modal" data-target="#location" location-ids="{{ json_encode($bid->pickup->pluck('id')->toArray()) }}" onclick="showLocation(this,'Pickup')" class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 pickup-location  cursor-pointer">
                                                                            {{ $bid->pickup->first()->location_name }}
                                                                        </span>
                                                                        @if($bid->pickup->count() > 1)
                                                                        <span data-bs-target="modal" data-target="#location" location-ids="{{ json_encode($bid->pickup->pluck('id')->toArray()) }}" onclick="showLocation(this,'Pickup')" class="badge cursor-pointer rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 extra-pickup">
                                                                            +{{ $bid->pickup->count() - 1 }}
                                                                        </span>
                                                                        @endif
                                                                    @else
                                                                    <h6>N/A</h6>
                                                                    @endif
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center ms-25">
                                                                        @if($bid->dropoff->isNotEmpty()) 
                                                                            <span data-bs-target="modal" data-target="#location" location-ids="{{ json_encode($bid->dropoff->pluck('id')->toArray()) }}" onclick="showLocation(this,'Dropoff')" class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 dropoff-location cursor-pointer">
                                                                                {{ $bid->dropoff->first()->location_name}}
                                                                            </span>
                                                                            @if($bid->dropoff->count() > 1)
                                                                                <span data-bs-target="modal" data-target="#location" location-ids="{{ json_encode($bid->dropoff->pluck('id')->toArray()) }}" onclick="showLocation(this,'Dropoff')" class="badge rounded-pill badge-light-secondary badgeborder-radius fw-bold font-small-2 extra-dropoff  cursor-pointer">
                                                                                    +{{ $bid->dropoff->count() - 1 }}
                                                                                </span>
                                                                            @endif
                                                                        @else
                                                                        <h6>N/A</h6>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td>{{ $bid->vehicle->vehicle_type }}</td>
                                                                <td>{{ $bid->total_weight." ".$bid->uom_code }}</td>
                                                                <td>{{ $bid->loading_date_time }}</td>
                                                                <td>{{ $bid->bid_end }}</td>
                                                                <td>{{ $bid?->bid?->bid_price?"Rs.".$bid?->bid?->bid_price:" " }}</td>

                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td>No Past Bids Present At The Moment</td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div> 
                                        </div>  
                                    </div>
                                </div>
                            </div>
                        </section>
                        <div class="modal fade" id="addaccess" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header p-0 bg-transparent">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body px-sm-4 mx-50 pb-2">
                                        <h1 class="text-center mb-1" id="shareProjectTitle">Apply for Bidding</h1>

                                        <!-- Bidding Form -->
                                        <form id="bidForm">
                                            <div class="row mt-2"> 
                                                <div class="col-md-6 mb-1">
                                                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                                    <input type="text" id="bid_vehicle_type" name="vehicle_type" value="" disabled class="form-control" />
                                                </div>
                                                <div class="col-md-6 mb-1">
                                                    <label class="form-label">Weight <span class="text-danger">*</span></label>
                                                    <input type="text" id="bid_weight" name="weight" value="" disabled class="form-control" />
                                                </div>
                                                
                                                <div class="col-md-12 mb-1">
                                                    <label class="form-label">Time of Loading <span class="text-danger">*</span></label>
                                                    <input type="text" id="bid_loading_date_time" name="loading_time" value="30-01-2025 | 11:00 AM" disabled class="form-control" />
                                                </div>
                                                
                                                <div class="col-md-12 mb-1">
                                                    <label class="form-label">Bidding Price <span class="text-danger">*</span></label>
                                                    <input type="number" id="bid_price" name="bid_price" class="form-control" required />
                                                </div>
                                            </div>

                                            <div class="modal-footer justify-content-center">  
                                                <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                        
                                        <!-- Success & Error Messages -->
                                        <div id="responseMessage" class="mt-2 text-center"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="location" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 90%; width: auto;">
                                <div class="modal-content">
                                <div class="modal-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center">
                                    <div class="w-100 text-center">
                                        <h1 id="locationModalTitle"></h1>
                                    </div>
                                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Dynamic content (table) will be inserted here -->
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="assigndriver" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header p-0 bg-transparent">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body px-sm-4 mx-50 pb-2">
                                        <h1 class="text-center mb-1" id="shareProjectTitle">Assign Driver</h1>
                                        <div class="row mt-2">    
                                            <div class="col-md-12 mb-1">
                                                <label class="form-label">Vehicle No.<span class="text-danger">*</span></label>
                                                <input type="text" id='vehicle_no' class="form-control" />
                                            </div>
                                            
                                            <div class="col-md-12 mb-1">
                                                <label class="form-label">Driver Name <span class="text-danger">*</span></label>
                                                <input type="text" id="driver_name" class="form-control" />
                                            </div>
                                            
                                            <div class="col-md-12 mb-1">
                                                <label class="form-label">Driver Contact no.<span class="text-danger">*</span></label>
                                                <input type="number" id="driver_contact" class="form-control" />
                                            </div>   
                                            <div class="col-md-12 mb-1">
                                                <label class="form-label">Remarks<span class="text-danger">*</span></label>
                                                <input type="textarea" id="remarks" class="form-control" />
                                            </div>   
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-center">  
                                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                                        <button type="buttom" id="Driver_submit" class="btn btn-primary">Submit</button>
                                    </div>
                                    <div id="response_Message" class="mt-2 text-center"></div>

                                </div>
                            </div>
                        </div>
                        
                        <div class="modal fade" id="cancel-trip" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <input type="hidden" name="action_type" id="action_type">
                                    <input type="hidden" name="bid_id">
                                    <div class="modal-header">
                                        <div>
                                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
                                        </h4>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body pb-2">
                                        <div class="row mt-1">
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label">Remarks</label>
                                                <textarea id="cancel_reason" name="remarks" class="form-control cannot_disable"></textarea>
                                            </div>
                                            <div class="row">
                                                <div class = "col-md-8">
                                                    <div class="mb-1">
                                                        <label class="form-label">Upload Document</label>
                                                        <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                                                    </div>
                                                </div>
                                                <div class = "col-md-4" style = "margin-top:19px;">
                                                    <div class = "row" id = "approval_files_preview">

                                                    </div>
                                                </div>
                                            </div>
                                            <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                            
                                        </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-center">  
                                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
                                        <button id="confirm_cancel_trip" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>
                        <!-- ChartJS section end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
        <script>
            $(document).on("click", ".submit-bid", function (e) {
                let row = $(e.target).closest(".col-md-4").parent('.bid-row').next('div'); 
                console.log(row);

                let bidStatus = $(this).attr("bid-status");
                let bidPrice = $(this).attr("bid-price");
                let data_target = $(this).attr('data-bs-target'); // Fix: Correctly fetch `data-bs-target`
                let bidId = $(this).attr("request-id");
                console.log("Bid Status:", bidStatus);

                let pickupLocation = row.closest(".summary-box").find(".pickup-location").first().text().trim();
                let dropoffLocation = row.closest(".summary-box").find(".dropoff-location").first().text().trim();
                let bidEndDate = row.find(".bid-end").text().trim();
                let vehicleType = row.find(".vehicle-type").text().trim();
                let bidWeight = row.find(".bid-weight").text().trim();
                let loadingDateTime = row.find(".loading-date-time").text().trim();

                // Populate modal inputs based on bid price
                if (bidPrice) {
                    $('#bid_price').val(bidPrice);
                    $('#shareProjectTitle').text('Change Your Bid');
                } else {
                    $('#shareProjectTitle').text('Apply For Bidding');
                }

                // Fill Modal Inputs with Captured Data
                $("#bid_vehicle_type").val(vehicleType);
                $("#bid_weight").val(bidWeight);
                $("#bid_loading_date_time").val(loadingDateTime);
                console.log(data_target);
                // Ensure `data_target` exists before using it
                if (data_target) {
                    $(data_target).data("bidId", bidId);
                    $(data_target).data("bidStatus", bidStatus);
                    $(data_target).data("pickupLocation", pickupLocation);
                    $(data_target).data("dropoffLocation", dropoffLocation);
                    $(data_target).data("bidEndDate", bidEndDate);
                } else {
                    console.error("data-bs-target is not defined or invalid.");
                }

                // Debugging output
                console.log("Captured Bid Data:", {
                    bidId,
                    pickupLocation,
                    dropoffLocation,
                    bidEndDate,
                    vehicleType,
                    bidWeight,
                    loadingDateTime
                });
            });
            $('#update_driver').on('click',function(){
                let modal = $("#assigndriver");
                $('#shareProjectTitle').text('Update Driver Details');
                let driver_details = $(this).attr('driver-details').split('-');
                console.log(driver_details);
                let driver_name=driver_details[1];
                let vehicle_no=driver_details[0];
                let driver_contact=driver_details[2];
                let remarks=driver_details[3];
                let action_type = 'update';
                $('#driver_name').val(driver_name); 
                $('#vehicle_no').val(vehicle_no);
                $('#driver_contact').val(driver_contact);
                $('#remarks').val(remarks);
                modal.data('action_type',action_type);


            });

            $("#confirm_cancel_trip").on('click',function(){
                let modal = $('#cancel-trip');
                let remarks = $('#cancel_reason').val();
                let bidId = modal.data("bidId");

                $.post('{{route('supplier.transporter.cancel_trip')}}', { bid_id:bidId,remarks:remarks}, function(response) {
                    $("#response_Message").html(`<div class="alert alert-success">${response.message}</div>`);
                    setTimeout(() => {
                        $("#assigndriver").modal("hide"); // Hide modal after success
                    }, 1500);
                    console.log("HHHH")
                    Swal.fire("Success!", response.message, "success");
                    location.reload();
                });
            });
            $('#Driver_submit').on('click',function(){
                let modal = $("#assigndriver");

                let driver_name=$('#driver_name').val();
                let vehicle_no=$('#vehicle_no').val();
                let driver_contact=$('#driver_contact').val();
                let remarks=$('#remarks').val();
                let bidId = modal.data("bidId");
                let action_type = modal.data('action_type');
                console.log(action_type);
                if (!driver_name) {
                    alert("Please enter a Driver's Name!");
                    return;
                }
                if (!vehicle_no) {
                    alert("Please enter a Vehicle's Number!");
                    return;
                }
                if (!driver_contact) {
                    alert("Please enter a Driver's Contact!");
                    return;
                }
                $.post('{{route('supplier.transporter.submit_vehicle')}}', { bid_id:bidId,driver_name:driver_name,vehicle_no:vehicle_no,driver_contact:driver_contact,remarks:remarks,action_type:action_type??'create'}, function(response) {
                    $("#response_Message").html(`<div class="alert alert-success">${response.message}</div>`);
                    setTimeout(() => {
                        $("#assigndriver").modal("hide"); // Hide modal after success
                    }, 1500);
                    console.log("HHHH")
                    Swal.fire("Success!", response.message, "success");
                    location.reload();
                });
            });

            $("#bidForm").on("submit", function(event) {
                event.preventDefault(); // Prevent default form submission
                let modal = $("#addaccess");
                let bidId = modal.data("bidId");
                let bidStatus = modal.data("bidStatus");
                let pickupLocation = modal.data("pickupLocation");
                let dropoffLocation = modal.data("dropoffLocation");
                let bidEndDate = modal.data("bidEndDate");
                let vehicleType = modal.data("vehicleType");
                let bidWeight = modal.data("bidWeight");
                let loadingDateTime = modal.data("loadingDateTime");
                let bidPrice = $("#bid_price").val(); // Get bid price from input

                
                // Ensure bid price is entered
                if (!bidPrice) {
                    alert("Please enter a bid price!");
                    return;
                }

                // Prepare data to send
                let formData = {
                    bid_id: bidId,
                    pickup_location: pickupLocation,
                    dropoff_location: dropoffLocation,
                    bid_end_date: bidEndDate,
                    vehicle_type: vehicleType,
                    bid_weight: bidWeight,
                    loading_date_time: loadingDateTime,
                    bid_price: bidPrice,
                    _token: $('meta[name="csrf-token"]').attr("content") // CSRF token for Laravel
                };
                let sending_coordinates = "{{ route('supplier.transporter.submit_bid') }}";
                if(bidStatus){
                    sending_coordinates = "{{ route('supplier.transporter.change_bid') }}"
                }
                $.ajax({
                    url: sending_coordinates, // Update with your actual route
                    type: "POST",
                    data: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // Include CSRF token if using Laravel
                    },
                    success: function(response) {
                        $("#bidForm")[0].reset(); // Reset form
                        Swal.fire("Success!", response.message, "success");
                        setTimeout(() => {
                            $("#addaccess").modal("hide"); // Hide modal after success
                        }, 1500);

                        location.reload();
                    },
                    error: function(xhr) {
                        $("#responseMessage").html(`<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
                    }
                });
            });



            $(window).on('load', function() {
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            })

            function showLocation(element, type) {
                let locations = $(element).attr('location-ids');

                $.ajax({
                    url: '{{ route('supplier.transporter.get-locations') }}',
                    type: "POST",
                    data: { location_ids: locations },
                    success: function(response) {
                        // Set the modal title

                        // Build the table structure
                        let tableHtml = `
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        // Loop through the response (assuming it's an array of arrays)
                        response.data.forEach(location => {
                            tableHtml += `
                                <tr>
                                    <td class='no-wrap'>${location[0]}</td>
                                    <td class='no-wrap'>${location[1]}</td>
                                </tr>
                            `;
                        });

                        tableHtml += `</tbody></table>`;

                        // Append the table to the modal body
                        $('#locationModalTitle').text(`${type} Location Details`);                            
                        $('.modal-body').html(tableHtml);
                        $('#location').modal('show');

                    },
                    error: function(xhr) {
                        $(".modal-body").html(`<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
                    }
                });
            }


              
            $(function () { 

            var dt_basic_table = $('.datatables-basic'),
                dt_date_table = $('.dt-date'),
                dt_complex_header_table = $('.dt-complex-header'),
                dt_row_grouping_table = $('.dt-row-grouping'),
                dt_multilingual_table = $('.dt-multilingual'),
                assetPath = '../../../app-assets/';

            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            // DataTable with buttons
            // --------------------------------------------------------------------

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                
                order: [[0, 'asc']],
                dom: 
                    '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                displayLength: 7,
                lengthMenu: [7, 10, 25, 50, 75, 100],
                buttons: [
                    {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                    buttons: [
                        {
                        extend: 'print',
                        text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
                        className: 'dropdown-item',
                        exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                        extend: 'csv',
                        text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                        className: 'dropdown-item',
                        exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                        extend: 'excel',
                        text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                        className: 'dropdown-item',
                        exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                        extend: 'pdf',
                        text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                        className: 'dropdown-item',
                        exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                        extend: 'copy',
                        text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                        className: 'dropdown-item',
                        exportOptions: { columns: [3, 4, 5, 6, 7] }
                        }
                    ],
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function () {
                        $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                    },
                    
                ],
                
                language: {
                    paginate: {
                    // remove previous & next text from pagination
                    previous: '&nbsp;',
                    next: '&nbsp;'
                    }
                }
                });
                $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
            }
            
            
            
            // Flat Date picker
            if (dt_date_table.length) {
                dt_date_table.flatpickr({
                    monthSelectorType: 'static',
                    dateFormat: 'm/d/Y'
                });
            }
            
            // Add New record
            // ? Remove/Update this code as per your requirements ?
            var count = 101;
            $('.data-submit').on('click', function () {
                var $new_name = $('.add-new-record .dt-full-name').val(),
                $new_post = $('.add-new-record .dt-post').val(),
                $new_email = $('.add-new-record .dt-email').val(),
                $new_date = $('.add-new-record .dt-date').val(),
                $new_salary = $('.add-new-record .dt-salary').val();
                
                if ($new_name != '') {
                    dt_basic.row
                    .add({
                        responsive_id: null,
                        id: count,
                        full_name: $new_name,
                    post: $new_post,
                    email: $new_email,
                    start_date: $new_date,
                    salary: '$' + $new_salary,
                    status: 5
                })
                .draw();
                count++;
                $('.modal').modal('hide');
            }
        });
        
        // Delete Record
        $('.datatables-basic tbody').on('click', '.delete-record', function () {
            dt_basic.row($(this).parents('tr')).remove().draw();
        });
    });
                  
        </script>
    @endsection