@extends('layouts.app')
@section('styles')
<style type="text/css">

        #map {
             height: 450px; /* or whatever size you prefer */
            width: 100%;
            border: 10px solid #fff;
            box-shadow: 0 0px 20px rgba(0, 0, 0, 0.1);
        }
    #pac-input {
    margin-top: 10px;
    padding: 10px;
    width: 95% !important;
    font-size: 16px;
    position: relative !important;
    left: 0 !important;
    top: 51px !important;
    border: #eee thin solid;
    font-size: 14px;
    border-radius: 6px;
    margin-left: 11px;
}
</style>
@endsection

@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Land</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{url('/land')}}">Home</a>
                                        </li>  
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">   
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if(empty($data->lease))  
                            <button  form="land-form" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button>
                            @endif 
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                 
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">  
                            
                            <div class="card">
                                 <div class="card-body customernewsection-form"> 
                                             
                                            
                                            <div class="border-bottom mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader "> 
                                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                                <p class="card-text">Fill the details</p> 
                                                            </div>
                                                        </div> 

                                                         
                                                    </div>
                                     
                                             </div>  
  
                                            
    <form id="land-form" method="POST" action="{{ route('update.land') }}">
    @csrf
    <input type="hidden" name="id" value="{{$data->id}}">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-5">
            <!-- Series Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Series <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <select class="form-select" name="series" required id="series">
                        <option value="" disabled selected>Select</option>
                        @foreach($series as $key=> $serie)
                         <option value="{{$serie->id}}" @if($serie->id == $data->series) selected @endif>{{$serie->book_name}}</option>
                         @endforeach
                    </select>
                    @error('series')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Document No. <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="documentno" id="documentno" value="{{ $data->documentno }}"  required readonly>
                    @error('document_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Land No. Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Land No. <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="land_no" value="{{ $data->land_no}}" onchange="cleanInput(this)" required>
                    @error('land_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Plot No. Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Plot No. <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="plot_no" value="{{ $data->plot_no }}" onchange="cleanInput(this)" required>
                    @error('plot_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Khasara No. Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Khasara No. <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="khasara_no" value="{{ $data->khasara_no }}" onchange="cleanInput(this)" required>
                    @error('khasara_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Area in Sq ft Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Area in Sq ft <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="area" value="{{ $data->area }}" onchange="cleanInput(this)" required>
                    @error('area')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Dimension Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Dimension <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="dimension" value="{{ $data->dimension }}" onchange="cleanInput(this)" required>
                    @error('dimension')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Address Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Address <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="address" id="address" value="{{ $data->address }}" onchange="cleanInput(this)" required>
                    @error('address')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Pincode Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="pincode" id="pincode" value="{{ $data->pincode }}" onchange="cleanInput(this)" required>
                    @error('pincode')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Latitude Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Latitude <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="latitude" id="latitude" readonly value="{{ $data->latitude }}" required>
                    @error('latitude')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Longitude Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Longitude <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="longitude" id="longitude" readonly value="{{ $data->longitude }}" required>
                    @error('longitude')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Cost Field -->
            <div class="row align-items-center mb-1">
                <div class="col-md-3">
                    <label class="form-label">Cost</label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="cost" value="{{$data->cost }}" onchange="cleanInputNumber(this)" >
                    @error('cost')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- Remarks Field -->
            <div class="row mb-1">
                <div class="col-md-3">
                    <label class="form-label">Remarks</label>
                </div>
                <div class="col-md-8">
                    <textarea class="form-control" name="remarks" rows="4" maxlength="250" placeholder="Enter Remarks here...">{{ $data->remarks }}</textarea>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-5">
            <!-- Status Field -->
            <div class="mb-1">
                <label class="form-label text-primary"><strong>Status</strong></label>
                <div class="demo-inline-spacing">
                    <div class="form-check form-check-primary mt-25">
                        <input type="radio" id="active" name="status" class="form-check-input" value="active" 
                            {{ $data->status == 'active' ? 'checked' : '' }} required>
                        <label class="form-check-label fw-bolder" for="active">Active</label>
                    </div>
                    <div class="form-check form-check-primary mt-25">
                        <input type="radio" id="inactive" name="status" class="form-check-input" value="inactive" 
                            {{ $data->status == 'inactive' ? 'checked' : '' }} required>
                        <label class="form-check-label fw-bolder" for="inactive">Inactive</label>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <!-- Google Maps Iframe -->
           <!--  <iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="450" style="border:10px solid #fff; box-shadow: 0 0px 20px rgba(0,0,0,0.1)" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> -->
            <input id="pac-input" class="controls" type="text" placeholder="Search for a location">
           <div id="map" style=" width: 100%;
            height: 450px;
            border: 10px solid #fff;
            box-shadow: 0 0px 20px rgba(0, 0, 0, 0.1);"></div>

        </div>
    </div>
</form>


 
                                
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                     
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Pending Disbursal</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                     <div class="row">
                         
                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Loan Type</label>
                                <select class="form-select">
                                    <option>Select</option>
                                    <option>Home Loan</option>
                                    <option>Vehicle Loan</option>
                                    <option>Term Loan</option>
                                </select>
                            </div>
                        </div>
                         
                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2">
                                    <option>Select</option> 
                                </select>
                            </div>
                        </div>
                         
                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Application No.</label>
                                <select class="form-select select2">
                                    <option>Select</option> 
                                </select>
                            </div>
                        </div>
                          
                         
                         <div class="col-md-3  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

                         <div class="col-md-12">
 

                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail"> 
                                    <thead>
                                         <tr>
                                            <th>#</th>  
                                            <th>Application No.</th>
                                            <th>Date</th> 
                                            <th>Customer Name</th>
                                            <th>Loan Type</th>
                                            <th>Disbursal Milestone</th>
                                            <th>Disbursal Amt.</th>
                                            <th>Mobile No.</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                             <tr>
                                                 <td>
                                                    <div class="form-check form-check-primary">
                                                        <input type="radio" id="customColorRadio3" name="customColorRadio3" class="form-check-input" checked=""> 
                                                    </div>
                                                 </td>   
                                                <td>HL/2024/001</td>
                                                <td>20-07-2024</td>
                                                <td class="fw-bolder text-dark">Kundan Kumar</td>
                                                <td>Term</td>
                                                <td>1st floor completed</td>
                                                <td>200000</td>
                                                <td>9876787656</td> 
                                            </tr>
                                            
                                            <tr>
                                                 <td> 
                                                    <div class="form-check form-check-primary">
                                                        <input type="radio" id="customColorRadio3" name="customColorRadio3" class="form-check-input" checked=""> 
                                                    </div>    
                                                 </td>   
                                                <td>HL/2024/001</td>
                                                <td>20-07-2024</td>
                                                <td class="fw-bolder text-dark">Kundan Kumar</td>
                                                <td>Term</td>
                                                <td>2nd floor completed</td>
                                                <td>200000</td>
                                                <td>nishu@gmail.com</td>
                                            </tr>
                                            
                                            
                                             
                                             

                                       </tbody>


                                </table>
                            </div>
                        </div>


                     </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>

@section('scripts')
     <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcAUGu5A8te4ZMlIhVtBduoCNWLrQfObY&libraries=places" async defer></script>
    <script>
        $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#documentno');

            request.val(''); // Clear any existing options
            
            if (book_id) {
                $.ajax({
                    url: "{{ url('get-land-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) 
                    {
                        console.log(data);
                            if (data.requestno) {
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });


window.addEventListener('load', function() {
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 8,
        center: { lat: {{$data->latitude}}, lng: {{$data->longitude}} },
    });

    // Both form and map search inputs
    var formInput = document.getElementById('address');
    var mapInput = document.getElementById('pac-input');

    // Initialize SearchBoxes for both inputs
    var formSearchBox = new google.maps.places.SearchBox(formInput);
    var mapSearchBox = new google.maps.places.SearchBox(mapInput);
    
    // Add the map input control to the map (optional if you need it)
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(mapInput);

    var marker = new google.maps.Marker({
        position: { lat: {{$data->latitude}}, lng: {{$data->longitude}} }, // Initial position
        map: map,
        draggable: true // Allow dragging
    });

    var geocoder = new google.maps.Geocoder();

    // Synchronize both inputs when a place is selected in either
    function handlePlacesChange(searchBox) {
        var places = searchBox.getPlaces();
        if (places.length === 0) return;

        if (marker) marker.setMap(null);

        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry || !place.geometry.location) return;

            // Place marker
            marker = new google.maps.Marker({
                map: map,
                position: place.geometry.location,
                draggable: true
            });

            // Update lat/lng in hidden fields
            updateLatLngInputs(place.geometry.location.lat(), place.geometry.location.lng());

            // Reverse geocode to update address
            geocodeLatLng(geocoder, place.geometry.location.lat(), place.geometry.location.lng());

            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);

        // Add marker dragging functionality
        google.maps.event.addListener(marker, 'dragend', function(event) {
            updateLatLngInputs(event.latLng.lat(), event.latLng.lng());
            geocodeLatLng(geocoder, event.latLng.lat(), event.latLng.lng());
        });
    }

    // Listen for place changes in both inputs
    formSearchBox.addListener('places_changed', function() {
        handlePlacesChange(formSearchBox);
        mapInput.value = formInput.value;  // Sync inputs
    });

    mapSearchBox.addListener('places_changed', function() {
        handlePlacesChange(mapSearchBox);
        formInput.value = mapInput.value;  // Sync inputs
    });

    // Handle map click to place marker
    google.maps.event.addListener(map, 'click', function(event) {
        var clickedLocation = event.latLng;
        if (!marker) {
            marker = new google.maps.Marker({
                position: clickedLocation,
                map: map,
                draggable: true
            });
        } else {
            marker.setPosition(clickedLocation);
        }

        // Update lat/lng and reverse geocode to update address
        updateLatLngInputs(clickedLocation.lat(), clickedLocation.lng());
        geocodeLatLng(geocoder, clickedLocation.lat(), clickedLocation.lng());

        // Marker dragging event
        google.maps.event.addListener(marker, 'dragend', function(event) {
            updateLatLngInputs(event.latLng.lat(), event.latLng.lng());
            geocodeLatLng(geocoder, event.latLng.lat(), event.latLng.lng());
        });
    });

    // Function to update lat/lng hidden fields
    function updateLatLngInputs(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    }

    // Reverse geocode to get address and update the form input
    function geocodeLatLng(geocoder, lat, lng) {
        var latlng = { lat: lat, lng: lng };
        geocoder.geocode({ location: latlng }, function(results, status) {
            if (status === 'OK') {
                if (results[0]) {
                    var address = results[0].formatted_address;
                    document.getElementById('address').value = address;
                    document.getElementById('pac-input').value = address; // Sync map input
                } else {
                    console.log("No results found");
                }
            } else {
                console.log("Geocoder failed due to: " + status);
            }
        });
    }
});




    </script>
<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });

        function cleanInput(input) {
        // Remove negative numbers and special characters
        input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, '');
    }

    function cleanInputNumber(input) {
        // Remove negative numbers and special characters
        input.value = input.value.replace(/[^0-9 ]/g, '');
    }
        
    </script>

@endsection
@endsection