@extends('layouts.app')

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
                            <h2 class="content-header-title float-start mb-0">Dashboard</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Dashboard
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7">
                    <div class="form-group d-flex flex-wrap align-items-center justify-content-sm-end mb-sm-0 mb-1">
                        <a href="{{ route('kaizen.create') }}" class="btn btn-primary box-shadow-2 btn-sm"><i
                                data-feather="upload"></i> Upload Kaizen</a>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">


                <section id="chartjs-chart">

                    <div class="row match-height">
                        <div class="col-xl-12 col-md-6 col-12">
                            <div class="row cutomerdardhcrminfo">

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="productivity" >0</h4>
                                                    <p class="card-text mb-0">Productivity</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-info">
                                                        <div class="avatar-content">
                                                            <i data-feather="archive" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="quality">0</h4>
                                                    <p class="card-text mb-0">Quality</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-primary">
                                                        <div class="avatar-content">
                                                            <i data-feather="activity" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="cost">50</h4>
                                                    <p class="card-text mb-0">Cost</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-success">
                                                        <div class="avatar-content">
                                                            <i data-feather="check-circle" class="avatar-icon">₹</i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="delivery">17<h4>
                                                            <p class="card-text mb-0">Delivery</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-danger">
                                                        <div class="avatar-content">
                                                            <i data-feather="truck" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="safety">12<h4>
                                                            <p class="card-text mb-0">Safety</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-secondary">
                                                        <div class="avatar-content">
                                                            <i data-feather="alert-triangle" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="moral">19<h4>
                                                            <p class="card-text mb-0">Moral</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-warning">
                                                        <div class="avatar-content">
                                                            <i data-feather="target" class="avatar-icon"></i>
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



                    <div class="row match-height">

                        <div class="col-md-8 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Monthly Kaizen</h4>
                                        <p class="card-text">No of Kaizen</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <canvas class="bar-chart-ex chartjs" data-height="265"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Employee Engagement</h4>
                                        <p class="card-text">Info Detail</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="donut-opentask"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Evaluator - April (02 Jul, 25)</h4>
                                        <p class="card-text">Shivam Sharma, Ashutosh Pratap</p>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">


                                        <div class="col-md-12 ">
                                            <table class="table border payrollconfigured customerdataapp">
                                                <thead>
                                                    <tr>
                                                        <th>S.NO</th>
                                                        <th>Description</th>
                                                        <th>Department</th>
                                                        <th>Cost</th>
                                                        <th>Innovation</th>
                                                        <th>LLevel Merit</th>
                                                        <th>Quality</th>
                                                        <th>Safety</th>
                                                        <th>Productivity</th>
                                                        <th>Level Score</th>
                                                        <th>Aggregated Score</th </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1</td>
                                                        <td class="text-dark fw-bolder">
                                                            <h6 class="font-small-2"><span
                                                                    class="text-danger">Problem:</span> Glue and mecl2 mix
                                                                in the glue gun as defined marking, Conventional method,
                                                                mixing with wooden stick.</h6>
                                                            <h6 class="font-small-2"><span class="text-success">Counter
                                                                    Measure:</span> Provide a beaker and stirrer for the
                                                                proper mixing of Glue and mecl2</h6>
                                                        </td>
                                                        <td><span
                                                                class="badge rounded-pill badge-light-secondary">Peeling</span>
                                                        </td>
                                                        <td>1</td>
                                                        <td>8</td>
                                                        <td>11</td>
                                                        <td>8</td>
                                                        <td>2</td>
                                                        <td>1</td>
                                                        <td>8</td>
                                                        <td>28</td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                    </div>
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
    <script>
      
$(document).ready(function () {
    initializeAutocomplete();

    function initializeAutocomplete() {
        const url = "{{ route('kaizen.get-dashboard') }}";

        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                console.log("Fetched data:", data);
                $("#cost").html(data.counts.cost_imp_id);
                $("#delivery").html(data.counts.delivery_imp_id);
                $("#moral").html(data.counts.moral_imp_id);
                $("#productivity").html(data.counts.productivity_imp_id);
                $("#quality").html(data.counts.quality_imp_id);
                $("#safety").html(data.counts.safety_imp_id);

                let rows = data.data; 
            //    $(".customerdataapp tbody").empty();

            //     $.each(rows, function (i, row) {
                    
            //         let newRow = `
            //             <tr>
            //                 <td>${i+1}</td>
            //                 <td class="text-dark fw-bolder">
            //                     <h6 class="font-small-2">
            //                         <span class="text-danger">Problem:</span> ${row.problem}
            //                     </h6>
            //                     <h6 class="font-small-2">
            //                         <span class="text-success">Counter Measure:</span> ${row.countermeasure}
            //                     </h6>
            //                 </td>
            //                 <td><span class="badge rounded-pill badge-light-secondary">${row.department}</span></td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //                 <td>${row.department}</td>
            //             </tr>
            //             `;
            //         $(".customerdataapp tbody").append(newRow);
            //     });
            },
            error: function (xhr) {
                console.error('Error while fetching data:', xhr.responseText);
                alert('An error occurred while fetching data.');
            }
        });
    }
});
</script>


    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/charts/chart-chartjs-expense.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/charts/chart-kaizen.js') }}"></script>
@endsection
