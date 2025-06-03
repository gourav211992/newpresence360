@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">
                                    <!-- {{ $level?->store?->store_name }} - {{ $level?->sub_store?->name }}  -->
                                </h4>
                                <div class="d-flex align-items-right breadcrumb-right">
                                    <button id="printBarcodesBtn" class="btn btn-dark" data-location-id="{{ $level->store_id }}" data-store-id="{{ $level->sub_store_id }}" data-level-id="{{ $level->id }}">
                                        🖨️ Print
                                    </button>
                                </div> 
                            </div>  
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Location</th>
                                                <th>Warehouse</th>
                                                <th>Name</th>
                                                <th>Parent</th>
                                                <th>QR Code/Bar Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($whDetails as $key => $val)
                                                <tr>
                                                    <td>
                                                        {{ $loop->iteration }}
                                                    </td>
                                                    <td>
                                                        {{ $val?->store?->store_name }}
                                                    </td>
                                                    <td>
                                                        {{ $val?->sub_store?->name }}
                                                    </td>   
                                                    <td>
                                                        {{ $val?->name }}
                                                    </td>
                                                    <td>
                                                        {{ $val?->parent?->name }}
                                                    </td>
                                                    <td>
                                                        @if($val->storage_number)
                                                            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($val->storage_number, 'QRCODE') }}" class="barcode-img" alt="{{ $val->storage_number }}" style="height:60px;width:60px;" />
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty 
                                                <tr>
                                                    <td colspan="6">
                                                        No Record Found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>

    <script>
        $(window).on("load", function () {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14,
                });
            }
        });

        $(document).on('click', '#printBarcodesBtn', function () {
            const locationId = $(this).data('location-id');
            const storeId = $(this).data('store-id');
            const levelId = $(this).data('level-id');

            $.ajax({
                url: `/warehouse-mappings/${locationId}/print-barcodes?sub_store=${storeId}&wh_level=${levelId}}`,
                method: 'GET',
                success: function (response) {
                    if (response.status === 200) {
                        const printWindow = window.open('', '', 'width=900,height=600');
                        printWindow.document.write(response.html);
                        printWindow.document.close();
                    } else {
                        Swal.fire('Error', 'Failed to generate barcode print view.', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'AJAX request failed.', 'error');
                }
            });
        });

    </script>
@endsection
