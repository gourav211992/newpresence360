@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
             
            <div class="content-body">
				<div class="card border">
					
					<div class="row">
						 <div class="col-md-12"> 
							 
							 		<div class="row align-items-center po-reportfileterBox">
										 <div class="col-md-12">
											<div class="card-header d-block p-1">
											 	<div class="row  align-items-center">
													<div class="col-md-4">
														<h3>Close Current F.Y.</h3>
														<p>Apply the Basic Filter</p>
													</div>
													<div class="col-md-8 text-sm-end">
														<button class="btn mt-25 btn-primary btn-sm" type="submit"><i data-feather="x-circle"></i> Close F.Y</button>
														<button class="btn mt-25 btn-danger btn-sm" type="submit"><i data-feather="lock"></i> Lock F.Y</button>
														<button class="btn mt-25 btn-success btn-sm" type="submit"><i data-feather="unlock"></i> UnLock F.Y</button>
													</div>
												</div>
												
											 </div>

											<div class="customernewsection-form poreportlistview">
												<div class="bg-light border-bottom mb-1  p-1">
													<div class="row">

														<div class="col-md-3">
															<div class="mb-1 mb-sm-0"> 
																<label class="form-label">Select Organization</label>
                                                                <select id="organization_id" class="form-select select2" multiple>
                                                                    <option value="" disabled>Select</option>
                                                                    @foreach ($companies as $organization)
                                                                    <option value="{{ $organization->organization->id }}"
                                                                        {{ $organization->organization->id == $organizationId ? 'selected' : '' }}>
                                                                        {{ $organization->organization->name }}
                                                                    </option>
                                                                @endforeach 
                                                                </select>
															 </div>
														</div>

														<div class="col-md-3">
															<div class="mb-1 mb-sm-0"> 
																<label class="form-label">Select F.Y</label>
																<select id="fyear_id" class="form-select select2">
																	<option>Select</option>
                                                                    @foreach ($fyears as $fyear)
                                                                    <option value="{{ $fyear['id'] }}" {{ $fyear['id'] == $fyearId ? 'selected' : '' }}>
                                                                        {{ $fyear['range'] }}
                                                                    </option>
                                                                    @endforeach
																</select>
															 </div>
														</div> 


														<div class="col-md-6">
															<div class="mt-sm-2 mb-sm-0">
																<label class="mb-1">&nbsp;</label>
																<button class="btn mt-25 btn-warning btn-sm" type="submit"><i data-feather="filter"></i> Run Report</button> 
															</div>
														</div> 

													</div>

												</div> 

											</div> 
											
										</div> 
										<div class="row align-items-center">
											<div class="col-md-8">
												{{-- <div class="newheader">
												   <div>
													   <h4 class="card-title text-theme text-dark" id="company_name"></h4>
													   <p class="card-text"><span id="startDate"></span> to <span id="endDate"></span></p>
												   </div>
											   </div> --}}
											</div>
											<div class="col-md-4 text-sm-end">
											   <a href="#" class="trail-exp-allbtnact" id="expand-all">
												   <i data-feather='plus-circle'></i> Expand All
											   </a>
											   <a href="#" class="trail-col-allbtnact" id="collapse-all">
												   <i data-feather='minus-circle'></i> Collapse All
											   </a>
											</div>
										</div>
									 </div>
							  

									 <div class="px-2 mt-1">
										<div class="step-custhomapp bg-light">
											<ul class="nav nav-tabs my-25 custapploannav" role="tablist"> 
												<li class="nav-item">
													<a class="nav-link active" data-bs-toggle="tab" href="#Transfer">Transfer Ledgers</a>
												</li>
												<li class="nav-item">
													<a class="nav-link" data-bs-toggle="tab" href="#Access">Access</a>
												</li>  
											</ul> 
										</div>
										 
										 <div class="tab-content ">
                                             <div class="tab-pane active" id="Transfer">
												 <div class="earn-dedtable trail-balancefinance trailbalnewdesfinance">
													<div class="table-responsive">
														<table class="table border">
															<thead>
																<tr>
																	<th id="company_name"></th>
																	<th width="300px">F.Y 2024-25 Closing Balance</th>
																</tr>
															</thead>
															{{-- <tbody>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#" class="trail-open-new-listplus-btn"><i data-feather='plus-circle'></i></a> <a href="#"  class="trail-open-new-listminus-btn"><i data-feather='minus-circle'></i></a> Capital Account</td>
																	<td class="fullbaltrailborder-bottom">40,000.00 Cr</td>
																</tr>
																<tr class="trail-sub-list-open" style="display: none">
																	<td><a href="#" class="trail-open-new-listplus-sub-btn text-dark"><i data-feather='plus-circle'></i></a> <a href="#"  class="trail-open-new-listminus-sub-btn text-dark"><i data-feather='minus-circle'></i></a> Proprietor Capital A/c</td>
																	<td>40,000.00 Cr</td>
																</tr>
																<tr class="trail-subsub-list-open" style="display: none">
																	<td style="padding-left: 35px"><a href="#" class="trail-open-new-listplus-subsub-btn text-dark"><i data-feather='plus-circle'></i></a> <a href="#"  class="trail-open-new-listminus-subsub-btn text-dark"><i data-feather='minus-circle'></i></a> Capital A/c</td>
																	<td>40,000.00 Cr</td>
																</tr>
																<tr class="trail-subsubsub-list-open" style="display: none">
																	<td style="padding-left: 45px"><a href="#"><i data-feather='arrow-right'></i> Sundry Creditors</a></td>
																	<td>12,000.00 Cr</td>
																</tr>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#"><i data-feather='plus-circle'></i></a> Current Liabilities</td>
																	<td>12,000.00 Cr</td>
																</tr>
																<tr>
																	<td>Sundry Creditors</td>
																	<td>12,000.00 Cr</td>
																</tr>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#"><i data-feather='plus-circle'></i></a> Fixed Assets</td>
																	<td>20,000.00 Dr</td>
																</tr>
																<tr>
																	<td>Furniture</td>
																	<td>20,000.00 Dr</td>
																</tr>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#"><i data-feather='plus-circle'></i></a> Current Assets</td>
																	<td>32,000.00 Dr</td>
																</tr>
																<tr>
																	<td>Sundary Debtors</td>
																	<td>12,000.00 Dr</td>
																</tr>
																<tr>
																	<td>Cash-In-Hand</td>
																	<td>-</td>
																</tr>
																<tr>
																	<td>Bank Accounts</td>
																	<td>20,000.00 Dr</td>
																</tr>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#"><i data-feather='plus-circle'></i></a> Sales Accounts</td>
																	<td>-</td>
																</tr>
																<tr>
																	<td>Sales</td>
																	<td>-</td>
																</tr>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#"><i data-feather='plus-circle'></i></a> Purchase Accounts</td>
																	<td>-</td>
																</tr>
																<tr>
																	<td>Purchases</td>
																	<td>-</td>
																</tr>
																<tr class="trail-bal-tabl-none">
																	<td><a href="#"><i data-feather='plus-circle'></i></a> Indirect Income</td>
																	<td>-</td>
																</tr>
																<tr>
																	<td>Cash Dis. Earned</td>
																	<td>-</td>
																</tr>
																<tr>
																	<td>Int.Earned</td>
																	<td>-</td>
																</tr>
															</tbody> --}}
															<tbody id="tableData"></tbody>

														</table> 
													</div> 
												</div> 		
											 </div>
											 <div class="tab-pane" id="Access">
												   <div class="table-responsive-md">
														 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
															<thead>
																 <tr>
																	<th width="50px">#</th>
																	<th width="480">Authorized By<span class="text-danger">*</span></th> 
																	<th width="400">Permissions</th>
																	<th>Action</th>
																  </tr>
																</thead>
																<tbody>
																	 <tr>
																		<td>1</td>
																		{{-- {{ dd($employees[0]->authUser(),$employees[0]->authUser()->roles) }} --}}
																		<td>
																		   <select class="form-select mw-100 select2" id="authUser" multiple>
																			<option value="" disabled>Select</option>
																			@foreach ($employees as $employee)
																			@php
																				// $authUser = $employee->authUser();
																				$permissions = $employee->authUser() ? $employee->authUser()->roles->pluck('name')->toArray() : [];
																			@endphp
																			<option 
																			value="{{ $employee->authUser() ? $employee->authUser()->id : '' }}"
																			data-permissions='@json($permissions)'>
																				{{ $employee->authUser() ? $employee->authUser()->name : 'N/A' }}
																			</option>
																		@endforeach
																			   {{-- <option>Select</option> 
																			   <option>Nishu Garg</option> 
																			   <option selected>Mahesh Bhatt</option> 
																			   <option>Inder Singh</option> 
																			   <option selected>Shivangi</option>  --}}
																		   </select> 
																		</td> 
																		 <td>
																			<select class="form-select mw-100 select2 permissions-box" multiple disabled>
																				<!-- Options will be populated dynamically -->
																			</select>
																		 </td>
																		 <td><a href="#" class="text-primary"><i data-feather="plus-square"></i></a></td>
																	  </tr>
																	{{-- <tr>
																		<td>2</td>
																		<td>
																		   <select class="form-select mw-100 select2" multiple>
																			   <option>Select</option> 
																			   <option>Nishu Garg</option> 
																			   <option selected>Mahesh Bhatt</option> 
																			   <option>Inder Singh</option> 
																			   <option selected>Shivangi</option> 
																		   </select> 
																		</td> 
																		 <td>
																			<select class="form-select mw-100 select2" multiple disabled>
																			   <option>Select</option>  
																			   <option selected>Finance Admin</option> 
																		   </select>
																		 </td>
																		<td><a href="#" class="text-danger"><i data-feather="trash-2"></i></a></td>
																	  </tr>  --}}
															   </tbody>


														</table>
													</div> 
											 </div>
										</div>
										  

									 </div>




 

						 </div>
						 
					 </div>
				
				</div>
                  

                 

            </div>
        </div>
    </div>
@endsection
    <!-- END: Content-->

	@section('scripts')
	<script>
		var reservesSurplus='';
	
		$(document).ready(function () {
			
		  
		getInitialGroups();
	   
				$('#company_name').text(
					$('#organization_id option:selected')
						.map(function() {
							return $(this).text();
						})
						.get()
						.join(', ')
				);
		  
	
			// Filter record
			$(".apply-filter").on("click", function () {
				// Hide the modal
				$(".modal").modal("hide");
				$('.collapse').click();
				$('#tableData').html('');
				let params = new URLSearchParams(window.location.search);
					params.set('date', $('#fp-range').val());
					params.set('cost_center_id', $('#cost_center_id').val());
	
	
					let newUrl = window.location.pathname + '?' + params.toString();
					window.history.pushState({}, '', newUrl);
				getInitialGroups();
	
				var selectedValues = $('#organization_id').val() || [];
				if (selectedValues.length === 0) {
					$('#company_name').text('All Companies');
				} else {
					$('#company_name').text(
						$('#organization_id option:selected')
							.map(function() {
								return $(this).text();
							})
							.get()
							.join(', ')
					);
				}
			})
	
			function getInitialGroups() {
			  
				
				var obj={ date:$('#fp-range').val(),cost_center_id:$('#cost_center_id').val(),currency:$('#currency').val(),'_token':'{!!csrf_token()!!}'};
				var selectedValues = $('#organization_id').val() || [];
				var filteredValues = selectedValues.filter(function(value) {
					return value !== null && value.trim() !== '';
				});
				if (filteredValues.length>0) {
					obj.organization_id=filteredValues
				}
	
				$.ajax({
					headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
					type    :"POST",
					url     :"{{route('getFyInitialGroups')}}",
					dataType:"JSON",
					data    :obj,
					success: function(data) {
						if (data['data'].length > 0) {
							reservesSurplus=data['profitLoss'];
							let html = '';
	
							var openingDrTotal=0;
							var openingCrTotal=0;
							var closingDrTotal=0;
							var closingCrTotal=0;
							var opening_tot =0
	
							for (let i = 0; i < data['data'].length; i++) {
								var total_debit=data['data'][i].total_debit;
								var total_credit=data['data'][i].total_credit;
	
								var opening=data['data'][i].open;
								var opening_type=data['data'][i].opening_type;
								var closingText='';
								var closing= opening +(total_debit-total_credit);
	
							
	
								if (closing != 0) {
									closingText=closing > 0 ? 'Dr' : 'Cr';
								}
							  
								// if (data['data'][i].name=="Liabilities") {
								//     if (data['profitLoss']['closing_type']==data['data'][i].opening_type) {
								//         opening_type=data['data'][i].opening_type;
								//         opening=parseFloat(opening) + parseFloat(data['profitLoss']['closingFinal']);
								//     } else {
								//         var openingDiff=parseFloat(opening) - parseFloat(data['profitLoss']['closingFinal']);
								//         if (openingDiff!=0) {
								//             var openingDiff=openingDiff > 0 ? openingDiff : -openingDiff;
								//             if (parseFloat(opening) > parseFloat(data['profitLoss']['closingFinal'])) {
								//                 opening_type=data['data'][i].opening_type;
								//             } else {
								//                 opening_type=data['profitLoss']['closing_type'];
								//             }
								//         }
								//         opening=openingDiff;
								//     }
	
								//     if (opening_type==closingText) {
								//         closingText=closingText;
								//         closing=parseFloat(opening) + parseFloat(closing);
								//     } else {
								//         var closingDiff=parseFloat(opening) - parseFloat(closing);
								//         if (closingDiff!=0) {
								//             var closingDiff=closingDiff > 0 ? closingDiff : -closingDiff;
								//             if (parseFloat(opening) > parseFloat(closing)) {
								//                 closingText=opening_type;
								//             } else {
								//                 closingText=closingText;
								//             }
								//         }
								//         closing=closingDiff;
								//     }
								// }
								
	
								
								
								
								opening_tot+=opening;
								let close = parseFloat(data['data'][i].open + (data['data'][i].total_debit-data['data'][i].total_credit));
								let closeType="";
								if(close<0)
								closeType = "Cr";
								else 
								closeType = "Dr";
	
									openingDrTotal+= parseFloat(data['data'][i].open);
								
									closingCrTotal+= parseFloat(closing);
								
								
	
								const groupUrl="{{ route('close-fy') }}/"+data['data'][i].id;
								
								html += `
									<tr class="trail-bal-tabl-none" id="${data['data'][i].id}">
										<input type="hidden" id="check${data['data'][i].id}">
										<td>
											<a href="#" class="trail-open-new-listplus-btn expand exp${data['data'][i].id}" data-id="${data['data'][i].id}"><i data-feather='plus-circle'></i></a>
											<a href="#" class="trail-open-new-listminus-btn collapse"><i data-feather='minus-circle'></i></a>
											<a class="urls" href="${groupUrl}">
												${data['data'][i].name}
											</a>
										</td>
										
										<td class="close_amt">${Math.abs(closing).toLocaleString('en-IN')} ${closingText}</td>
									</tr>`;
							}
	
							var openingTotalType='';
							var openingTotalDiff=parseFloat(openingDrTotal) - parseFloat(openingCrTotal);
							if (openingTotalDiff!=0) {
								var openingTotalDiff=openingTotalDiff > 0 ? openingTotalDiff : -openingTotalDiff;
								if (parseFloat(openingDrTotal) > parseFloat(openingCrTotal)) {
									openingTotalType='Dr';
								} else {
									openingTotalType='Cr';
								}
							}
	
							var closingTotalType='';
							var closingTotalDiff= (parseFloat(closingDrTotal) - parseFloat(closingCrTotal));
							if (closingTotalDiff!=0) {
								var closingTotalDiff=closingTotalDiff > 0 ? closingTotalDiff : -closingTotalDiff;
								if (parseFloat(closingDrTotal) > parseFloat(closingCrTotal)) {
									closingTotalType='Dr';
								} else {
									closingTotalType='Cr';
								}
							}
	
							// $('#openingAmt').text(openingTotalDiff.toLocaleString('en-IN')+openingTotalType);
							// $('#closingAmt').text(closingTotalDiff.toLocaleString('en-IN')+closingTotalType);
							$('#tableData').empty().append(html);
							calculate_cr_dr();
	
						}
	
						$('#startDate').text(data['startDate']);
						$('#endDate').text(data['endDate']);
	
						if (feather) {
							feather.replace({
								width: 14,
								height: 14
							});
						}
	
						calculate_cr_dr();
	
						$('#expand-all').click();
					}
				});
			}
	
			function calculate_cr_dr() {
				let cr_sum = 0;
				$('.crd_amt').each(function() {
					const value = removeCommas($(this).text()) || 0;
					cr_sum = parseFloat(parseFloat(cr_sum + value).toFixed(2));
				});
				$('#crd_total').text(cr_sum.toLocaleString('en-IN'));
	
				let dr_sum = 0;
				$('.dbt_amt').each(function() {
					const value = removeCommas($(this).text()) || 0;
					dr_sum = parseFloat(parseFloat(dr_sum + value).toFixed(2));
				});
				$('#dbt_total').text(dr_sum.toLocaleString('en-IN'));
	
				 // Opening balance
				let opening_total = 0;
				$('.open_amt').each(function () {
					const raw = $(this).text().trim();
					const match = raw.match(/^([\d,.\-]+)\s*(Dr|Cr)?$/i);
					
					if (match) {
						let amount = removeCommas(match[1]);
						let type = match[2] ? match[2].toLowerCase() : 'dr'
						console.log("type"+type);
	
						if (type.toLowerCase() === 'dr') {
							opening_total += amount;
						} else if (type.toLowerCase() === 'cr') {
							opening_total -= amount;
						}
					}
				});
	
			// $('#openingAmt').text(Math.abs(opening_total).toLocaleString('en-IN') + ' ' + (opening_total >= 0 ? 'Dr' : 'Cr'));
	
			// Closing balance
			let closing_total = 0;
			$('.close_amt').each(function () {
				const raw = $(this).text().trim();
				const match = raw.match(/^([\d,.\-]+)\s*(Dr|Cr)?$/i);
	
				if (match) {
					let amount = removeCommas(match[1]);
					let type = match[2] ? match[2].toLowerCase() : 'dr'
	
					if (type.toLowerCase() === 'dr') {
						closing_total += amount;
					} else if (type.toLowerCase() === 'cr') {
						closing_total -= amount;
					}
				}
			});
		  
	
			// $('#closingAmt').text(Math.abs(closing_total).toLocaleString('en-IN') + ' ' + (closing_total >= 0 ? 'Dr' : 'Cr'));
			$('.urls').each(function () {
			let currentHref = $(this).attr('href') || '';
			let baseUrl = currentHref.split('?')[0]; // remove old query params if any
	
			// Append new query parameters
			let updatedUrl = `${baseUrl}?date=${encodeURIComponent($('#fp-range').val())}&cost_center_id=${encodeURIComponent($('#cost_center_id').val())}`;
			$(this).attr('href', updatedUrl);
			
		});
		let r_date = "{{ request('date')}}";
		if(r_date!=""){
			console.log("date"+r_date);
		
			$("#fp-range").val(r_date);
		}
	
		   
				
			}
	
			function removeCommas(str) {
				return parseFloat(str.replace(/,/g, ""));
			}
	
			function getIncrementalPadding(parentPadding) {
				return parentPadding + 10; // Increase padding by 10px
			}
	
			$(document).on('click', '.expand', function() {
				const id = $(this).attr('data-id');
				const parentPadding = parseInt($(this).closest('td').css('padding-left'));
	
				if ($('#name' + id).text()=="Reserves & Surplus") {
					const padding = getIncrementalPadding(parentPadding);
	
					let html= `
						<tr class="trail-sub-list-open parent-${id}">
							<td style="padding-left: ${padding}px">Profit & Loss</td>
							<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
							<td></td>
							<td></td>
							<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
						</tr>`;
					$('#'+id).closest('tr').after(html);
				} else {
					if ($('#check' + id).val() == "") {
						var obj={ id:id,date:$('#fp-range').val(),cost_center_id:$('#cost_center_id').val(),currency:$('#currency').val(),'_token':'{!!csrf_token()!!}'};
						var selectedValues = $('#organization_id').val() || [];
						var filteredValues = selectedValues.filter(function(value) {
							return value !== null && value.trim() !== '';
						});
						if (filteredValues.length>0) {
							obj.organization_id=filteredValues
						}
	
						$.ajax({
							headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
							type    :"POST",
							url     :"{{route('getSubGroups')}}",
							dataType:"JSON",
							data    :obj,
							success: function(data) {
								$('#check' + id).val(id);
								if (data['data'].length > 0) {
									let html = '';
									if (data['type'] == "group") {
										for (let i = 0; i < data['data'].length; i++) {
											const padding = getIncrementalPadding(parentPadding);
											var closingText='';
											const closing= data['data'][i].open +(data['data'][i].total_debit - data['data'][i].total_credit);
											if (closing != 0) {
												closingText=closing > 0 ? 'Dr' : 'Cr';
											}
											const groupUrl="{{ route('trial_balance') }}/"+data['data'][i].id;
	
											if (data['data'][i].name=="Reserves & Surplus") {
												html += `
												<tr class="trail-sub-list-open expandable parent-${id}" id="${data['data'][i].id}">
													<input type="hidden" id="check${data['data'][i].id}">
													<td style="padding-left: ${padding}px">
														<a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
															<i data-feather='plus-circle'></i>
														</a>
														<a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
															<i data-feather='minus-circle'></i>
														</a>
														<span id="name${data['data'][i].id}">${data['data'][i].name}</span>
													</td>
													<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
													<td></td>
													<td></td>
													<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
												</tr>`;
											} else {
												html += `
												<tr class="trail-sub-list-open expandable parent-${id}" id="${data['data'][i].id}">
													<input type="hidden" id="check${data['data'][i].id}">
													<td style="padding-left: ${padding}px">
														<a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
															<i data-feather='plus-circle'></i>
														</a>
														<a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
															<i data-feather='minus-circle'></i>
														</a>
														<a class="urls" href="${groupUrl}">
															${data['data'][i].name}
														</a>
													</td>
													
													<td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
												</tr>`;
											}
										}
									} else {
										let tot_debt=0;
										let tot_credit=0;
										for (let i = 0; i < data['data'].length; i++) {
											const padding = getIncrementalPadding(parentPadding);
											var closingText='';
											const closing=data['data'][i].open + (data['data'][i].details_sum_debit_amt - data['data'][i].details_sum_credit_amt);
											if (closing != 0) {
												closingText=closing > 0 ? 'Dr' : 'Cr';
											}
	
											html += `
												<tr class="trail-sub-list-open parent-${id}">
													<td style="padding-left: ${padding}px">
														<i data-feather='arrow-right'></i>${data['data'][i].name}
													</td>
													
													<td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
												</tr>`;
												tot_debt+= data['data'][i].details_sum_debit_amt;
												tot_credit+= data['data'][i].details_sum_credit_amt;
										}
									}
									$('#' + id).closest('tr').after(html);
									$('.urls').each(function () {
			let currentHref = $(this).attr('href') || '';
			let baseUrl = currentHref.split('?')[0]; // remove old query params if any
	
			// Append new query parameters
			let updatedUrl = `${baseUrl}?date=${encodeURIComponent($('#fp-range').val())}&cost_center_id=${encodeURIComponent($('#cost_center_id').val())}`;
			$(this).attr('href', updatedUrl);
			
		});
								   
									
								}
	
								if (feather) {
									feather.replace({
										width: 14,
										height: 14
									});
								}
							}
						});
		   
					}
				}
	
				// Expand all direct children of this row
				$('.parent-' + id).show();
				$(this).hide();
				$(this).siblings('.collapse').show();
			});
	
			$(document).on('click', '.collapse', function() {
				const id = $(this).closest('tr').attr('id');
	
				// Collapse all children of this row recursively and hide their expand icons
				function collapseChildren(parentId) {
					$(`.parent-${parentId}`).each(function() {
						const childId = $(this).attr('id');
						$(this).hide(); // Hide the child row
						$(this).find('.collapse').hide(); // Hide the collapse icon
						$(this).find('.expand').show(); // Show the expand icon
						collapseChildren(childId); // Recursively collapse the child's children
					});
				}
	
				collapseChildren(id);
	
				$(this).hide();
				$(this).siblings('.expand').show();
			});
	
			// Expand All rows
			$('#expand-all').click(function() {
				$('.expand').hide();
	
				var trIds = $('tbody tr').map(function() {
					return this.id; // Return the ID of each tr element
				}).get().filter(function(id) {
					return id !== "" && $('#check' + id).val() == ""; // Filter out any empty IDs
				});
	
				if (trIds.length>0) {
	
					var obj={ ids:trIds,date:$('#fp-range').val(),cost_center_id:$('#cost_center_id').val(),currency:$('#currency').val(),'_token':'{!!csrf_token()!!}'};
					var selectedValues = $('#organization_id').val() || [];
					var filteredValues = selectedValues.filter(function(value) {
						return value !== null && value.trim() !== '';
					});
					if (filteredValues.length>0) {
						obj.organization_id=filteredValues
					}
	
					$.ajax({
						headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
						type    :"POST",
						url     :"{{route('getSubGroupsMultiple')}}",
						dataType:"JSON",
						data    :obj,
						success: function(res) {
							if (res['data'].length > 0) {
								
	
								res['data'].forEach(data => {
									let tot_credit =0;
								let tot_debt=0;
	
									$('#check'+data['id']).val(data['id']);
									const parentPadding = parseInt($('.exp'+data['id']).closest('td').css('padding-left'));
	
									if ($('#name' + data['id']).text()=="Reserves & Surplus") {
										const padding = getIncrementalPadding(parentPadding);
	
										let html= `
											<tr class="trail-sub-list-open parent-${data['id']}">
												
												<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
											</tr>`;
										$('#'+data['id']).closest('tr').after(html);
									} else {
										if (data['data'].length > 0) {
											let tot_debt=0;
										let tot_credit=0;
										
											let html = '';
											if (data['type'] == "group") {
												for (let i = 0; i < data['data'].length; i++) {
													const padding = getIncrementalPadding(parentPadding);
													var closingText='';
													const closing=data['data'][i].open + (data['data'][i].total_debit - data['data'][i].total_credit);
													if (closing != 0) {
														closingText=closing > 0 ? 'Dr' : 'Cr';
													}
													const groupUrl="{{ route('trial_balance') }}/"+data['data'][i].id;
													if (data['data'][i].name=="Reserves & Surplus") {
														html += `
														<tr class="trail-sub-list-open expandable parent-${data['id']}" id="${data['data'][i].id}">
															<input type="hidden" id="check${data['data'][i].id}">
															<td style="padding-left: ${padding}px">
																<a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
																	<i data-feather='plus-circle'></i>
																</a>
																<a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
																	<i data-feather='minus-circle'></i>
																</a>
																<span id="name${data['data'][i].id}">${data['data'][i].name}</span>
															</td>
															
															<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
														</tr>`;
													} else {
														html += `
														<tr class="trail-sub-list-open expandable parent-${data['id']}" id="${data['data'][i].id}">
															<input type="hidden" id="check${data['data'][i].id}">
															<td style="padding-left: ${padding}px">
																<a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
																	<i data-feather='plus-circle'></i>
																</a>
																<a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
																	<i data-feather='minus-circle'></i>
																</a>
																<a class="urls" href="${groupUrl}">
																	${data['data'][i].name}
																</a>
															</td>
													
															<td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
														</tr>`;
													}
												}
											} else {
												for (let i = 0; i < data['data'].length; i++) {
													const padding = getIncrementalPadding(parentPadding);
													var closingText='';
													const closing=data['data'][i].open + (data['data'][i].details_sum_debit_amt - data['data'][i].details_sum_credit_amt);
													if (closing != 0) {
														closingText=closing > 0 ? 'Dr' : 'Cr';
													}
													const ledgerUrl="{{ url('trailLedger') }}/"+data['data'][i].group_id;
	
													html += `
														<tr class="trail-sub-list-open parent-${data['id']}">
															<td style="padding-left: ${padding}px">
																<i data-feather='arrow-right'></i>${data['data'][i].name}
															</td>
															
															<td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
														</tr>`;
														tot_debt+=data['data'][i].details_sum_debit_amt;
														tot_credit+=data['data'][i].details_sum_credit_amt;
												}
												console.log(tot_credit,tot_debt);
											  
											}
											
								   
									$('#'+data['id']).closest('tr').after(html);
										}
									}
								});
							}
	
							if (feather) {
								feather.replace({
									width: 14,
									height: 14
								});
							}
							calculate_cr_dr();
						}
					});
				}
	
				$('.collapse').show();
				$('.expandable').show();
			});
	
			// Collapse All rows
			$('#collapse-all').click(function() {
				$('tbody tr').each(function() {
					const id = $(this).attr('id');
					if (id) {
						collapseChildren(id); // Collapse all children for each parent row
					}
				});
				$('.collapse').hide();
				$('.expand').show();
			});
	
			// Recursive collapse function
			function collapseChildren(parentId) {
				$(`.parent-${parentId}`).each(function() {
					const childId = $(this).attr('id');
					$(this).hide(); // Hide the child row
					$(this).find('.collapse').hide(); // Hide the collapse icon
					$(this).find('.expand').show(); // Show the expand icon
					collapseChildren(childId); // Recursively collapse the child's children
				});
			}
		});
	
		function exportTrialBalanceReport(level){
			var obj={ date:$('#fp-range').val(),cost_center_id:$('#cost_center_id').val(),currency:$('#currency').val(),'_token':'{!!csrf_token()!!}',group_id:group_id,level:level};
			var selectedValues = $('#organization_id').val() || [];
			var filteredValues = selectedValues.filter(function(value) {
				return value !== null && value.trim() !== '';
			});
			if (filteredValues.length>0) {
				obj.organization_id=filteredValues
			}
	
			$.ajax({
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
				type    :"POST",
				url     :"{{ route('exportTrialBalanceReport') }}",
				data    :obj,
				xhrFields: {
					responseType: 'blob'
				},
				success: function(data, status, xhr) {
					var link = document.createElement('a');
					var url = window.URL.createObjectURL(data);
					link.href = url;
					link.download = 'trialBalance.xlsx';
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				},
				error: function(xhr, status, error) {
					console.log('Export failed:', error);
				}
			});
		}
	
		// selected arrow using down, up key
		$(document).ready(function () {
			let selectedRow = null;
	
			function setSelectedRow(row) {
				if (selectedRow) {
					selectedRow.removeClass('trselected');
				}
				selectedRow = row;
				selectedRow.addClass('trselected');
			}
	
			function expandRow(row) {
				const id = row.attr('id');
				$('.parent-' + id).show();
				row.find('.expand').hide();
				row.find('.collapse').show();
			}
	
			function collapseRow(row) {
				const id = row.attr('id');
				collapseChildren(id);
				row.find('.expand').show();
				row.find('.collapse').hide();
			}
	
			function collapseChildren(parentId) {
				$(`.parent-${parentId}`).each(function() {
					const childId = $(this).attr('id');
					$(this).hide();
					$(this).find('.collapse').hide();
					$(this).find('.expand').show();
					collapseChildren(childId);
				});
			}
	
			// Arrow key navigation
			$(document).keydown(function (e) {
				const rows = $('tbody tr');
				if (rows.length === 0) return;
	
				let currentIndex = rows.index(selectedRow);
				let nextIndex = currentIndex;
	
				switch (e.which) {
					case 38: // Up arrow key
						if (currentIndex > 0) {
							nextIndex = currentIndex - 1;
							while (nextIndex >= 0 && rows.eq(nextIndex).is(':hidden')) {
								nextIndex--;
							}
							if (nextIndex >= 0) {
								setSelectedRow(rows.eq(nextIndex));
							}
						}
						break;
					case 40: // Down arrow key
						if (currentIndex < rows.length - 1) {
							nextIndex = currentIndex + 1;
							while (nextIndex < rows.length && rows.eq(nextIndex).is(':hidden')) {
								nextIndex++;
							}
							if (nextIndex < rows.length) {
								setSelectedRow(rows.eq(nextIndex));
							}
						}
						break;
					case 37: // Left arrow key
						if (selectedRow) {
							collapseRow(selectedRow);
						}
						break;
					case 39: // Right arrow key
						if (selectedRow) {
							expandRow(selectedRow);
						}
						break;
				}
			});
	
		
		});
	</script>
	<script>
		$(document).ready(function () {
    $('#authUser').select2();

    $('#authUser').on('change', function () {
        let selectedOptions = $(this).find(':selected');
        let permissionSelect = $(this).closest('tr').find('.permissions-box');

        let permissions = [];

        selectedOptions.each(function () {
            let perms = $(this).data('permissions');
            if (Array.isArray(perms)) {
                permissions = permissions.concat(perms);
            }
        });

        // Remove duplicates
        permissions = [...new Set(permissions)];

        // Clear and populate permission box
        permissionSelect.empty();

        if (permissions.length > 0) {
            permissionSelect.prop('disabled', false);
            permissions.forEach(function (permission) {
                permissionSelect.append(`<option selected>${permission}</option>`);
            });
        } else {
            permissionSelect.prop('disabled', true);
            permissionSelect.append(`<option>No Permissions</option>`);
        }

        permissionSelect.trigger('change');
    });
});

		</script>
		
	@endsection