@extends('layouts.app')

@section('styles')
@endsection

@section('content')

<!-- BEGIN: Content-->
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper container-xxl p-0">
		<div class="content-header row">
			<div class="content-header-left col-md-5 mb-2">
				<div class="row breadcrumbs-top">
					<div class="col-12">
						<h2 class="content-header-title float-start mb-0">Financial Setup</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
								<li class="breadcrumb-item active">Financial List</li>
							</ol>
						</div>
					</div>

				</div>

			</div>
			<div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
				<div class="form-group breadcrumb-right">
					
                    @if(!empty($data))
                    <a href="{{route('loan.financial-setup-edit', $data->id)}}" class="btn btn-sm btn-primary"><i data-feather="plus-circle"></i>Edit</a>
                    @else
                    <a class="btn btn-primary btn-sm" href="{{ route('loan.financial-setup-add') }}"><i data-feather="plus-circle"></i>
						Add New</a>
                    @endif
                                        
				</div>
			</div>
		</div>

		<section id="basic-datatable">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="table-responsive">
							<table class="datatables-basic table myrequesttablecbox">
								<thead>
                                        <tr> 
                                            <th>ID</th>
                                            <th>Pro Ledger ID</th>
                                            <th>Pro Ledger Group ID</th>
                                            <th>Dis Ledger ID</th>
                                            <th>Dis Ledger Group ID</th>
                                            <th>Int Ledger ID</th>
                                            <th>Int Ledger Group ID</th>
                                            <th>Wri Ledger ID</th>
                                            <th>Wri Ledger Group ID</th>
                                            <th>Status</th>
                                            <th>Created Date</th>
                                        </tr>
                                        <tr>
                                            <td>{{$data->id}}</td>
                                            <td>{{$data->ledgers($data->pro_ledger_id)->name}}</td>
                                            <td>{{$data->groups($data->pro_ledger_group_id)->name}}</td>
                                            <td>{{$data->ledgers($data->dis_ledger_id)->name}}</td>
                                            <td>{{$data->groups($data->dis_ledger_group_id)->name}}</td>
                                            <td>{{$data->ledgers($data->int_ledger_id)->name}}</td>
                                            <td>{{$data->groups($data->int_ledger_group_id)->name}}</td>
                                            <td>{{$data->ledgers($data->wri_ledger_id)->name}}</td>
                                            <td>{{$data->groups($data->wri_ledger_group_id)->name}}</td>
                                            <td>{{$data->status ? 'Active' : 'Inactive'}}</td>
                                            <td>{{date('Y-m-d',strtotime($data->created_at))}}</td>
                                        </tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			
		</section>
	</div>
</div>
<!-- END: Content-->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection