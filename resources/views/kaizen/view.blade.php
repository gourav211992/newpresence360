@extends('layouts.app')

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
                                <h2 class="content-header-title float-start mb-0">View Kaizen</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('kaizen.dashboard') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('kaizen.index') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</a>
                            <a href="{{ url('kaizen/download-pdf/') . '/' . $kaizen->id }}" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i data-feather="download"></i> 
                                Download PDF
                            </a>

                            @php
                                if ($kaizen->status == 'approved') {
                                    $className = 'btn-success';
                                } elseif ($kaizen->status == 'rejected') {
                                    $className = 'btn-danger';
                                } else {
                                    $className = 'btn-primary';
                                }
                            @endphp

                            <button class="btn {{ $className }} btn-sm mb-50 mb-sm-0"
                                @if ($kaizen->status != 'pending') data-bs-toggle="modal"
                                data-bs-target="#status-modal" @endif>
                                {{ ucfirst($kaizen->status) }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div
                                                class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                <div>
                                                    <h4 class="card-title text-theme">View details</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Department</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-control">
                                                        {{ isset($kaizen->department->name) ? $kaizen->department->name : '-' }}
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Kaizen Team</label>
                                                </div>
                                                <div class="col-md-4">
                                                    @php
                                                        $teams = @$kaizen->kaizenTeam;
                                                    @endphp

                                                    <a href="#" class="teamnum text-primary" data-bs-toggle="modal"
                                                        data-bs-target="#teamModal"
                                                        data-teams='@json($teams->pluck('name', 'email'))'>
                                                        <i data-feather="eye" class="me-50"></i>
                                                        <span>View</span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Kaizen No.</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-control">{{ $kaizen->kaizen_no }}</div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Kaizen Date <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-control">
                                                        {{ $kaizen->kaizen_date ? App\Helpers\CommonHelper::dateFormat2($kaizen->kaizen_date) : '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Before Kaizen</label>
                                                </div>
                                                <div class="col-md-4 attachment-container-before">
                                                    <div id="preview-before">
                                                        @if (isset($attachments[\App\Helpers\CommonHelper::BEFORE_KAIZEN]))
                                                            @forelse($attachments[\App\Helpers\CommonHelper::BEFORE_KAIZEN] as $id => $attachment)
                                                                @php
                                                                    $extension = pathinfo(
                                                                        $attachment,
                                                                        PATHINFO_EXTENSION,
                                                                    );
                                                                    $isImage = in_array(strtolower($extension), [
                                                                        'jpg',
                                                                        'jpeg',
                                                                        'png',
                                                                        'gif',
                                                                    ]);
                                                                @endphp
                                                                <div class="image-uplodasection"
                                                                    id="existing-before-{{ $id }}">
                                                                    <a href="{{ asset($attachment) }}" target="_blank">
                                                                        @if ($isImage)
                                                                            <i data-feather="image"
                                                                                class="fileuploadicon"></i>
                                                                        @elseif (strtolower($extension) === 'pdf')
                                                                            <i data-feather="file-text"
                                                                                class="fileuploadicon"></i>
                                                                        @else
                                                                            <i data-feather="file"
                                                                                class="fileuploadicon"></i>
                                                                        @endif
                                                                    </a>
                                                                </div>
                                                            @empty
                                                            @endforelse
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">After Kaizen</label>
                                                </div>

                                                <div class="col-md-4 attachment-container-after">
                                                    <div id="preview-after">
                                                        @if (isset($attachments[\App\Helpers\CommonHelper::AFTER_KAIZEN]))
                                                            @forelse($attachments[\App\Helpers\CommonHelper::AFTER_KAIZEN] as $id => $attachment)
                                                                @php
                                                                    $extension = pathinfo(
                                                                        $attachment,
                                                                        PATHINFO_EXTENSION,
                                                                    );
                                                                    $isImage = in_array(strtolower($extension), [
                                                                        'jpg',
                                                                        'jpeg',
                                                                        'png',
                                                                        'gif',
                                                                    ]);
                                                                @endphp
                                                                <div class="image-uplodasection"
                                                                    id="existing-after-{{ $id }}">
                                                                    <a href="{{ asset($attachment) }}" target="_blank">
                                                                        @if ($isImage)
                                                                            <i data-feather="image"
                                                                                class="fileuploadicon"></i>
                                                                        @elseif (strtolower($extension) === 'pdf')
                                                                            <i data-feather="file-text"
                                                                                class="fileuploadicon"></i>
                                                                        @else
                                                                            <i data-feather="file"
                                                                                class="fileuploadicon"></i>
                                                                        @endif
                                                                    </a>
                                                                </div>
                                                            @empty
                                                            @endforelse
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row  align-items-center  mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Problem</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="form-control">{{ $kaizen->problem }}</div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Countermeasure</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="form-control">{{ $kaizen->counter_measure }}</div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Benefits</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="form-control">{{ $kaizen->benefits }}</div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Approver Name</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-control">
                                                        {{ isset($kaizen->approver->name) ? $kaizen->approver->name : '-' }}
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Occurence</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-control">
                                                        {{ $kaizen->occurence ? $kaizen->occurence : '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div
                                                class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                <div>
                                                    <h4 class="card-title text-theme">Improvements</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div id="accordionWrapa50" role="tablist" aria-multiselectable="true">
                                                @if(!empty($kaizen->productivity?->description))
                                                <div class="accordion-item border" id="productivity-accordion">
                                                    <h2 class="accordion-header" id="productivity-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#productivity">
                                                            <strong>Productivity</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="productivity">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->productivity->description) ? $kaizen->productivity->description : '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($kaizen->quality?->description))
                                                <div class="accordion-item border" id="quality-accordion">
                                                    <h2 class="accordion-header" id="quality-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#quality">
                                                            <strong>Quality</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="quality">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->quality->description) ? $kaizen->quality->description : '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($kaizen->cost?->description))
                                                <div class="accordion-item border" id="cost-accordion">
                                                    <h2 class="accordion-header" id="cost-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#cost">
                                                            <strong>Cost</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="cost">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->cost->description) ? $kaizen->cost->description : '-' }}
                                                                            </td>
                                                                            <td>
                                                                                {{ $kaizen->cost_saving_amt }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($kaizen->delivery?->description))
                                                <div class="accordion-item border" id="delivery-accordion">
                                                    <h2 class="accordion-header" id="delivery-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#delivery">
                                                            <strong>Delivery</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="delivery">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->delivery->description) ? $kaizen->delivery->description : '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($kaizen->moral?->description))
                                                <div class="accordion-item border" id="moral-accordion">
                                                    <h2 class="accordion-header" id="moral-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#moral">
                                                            <strong>Moral</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="moral">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->moral->description) ? $kaizen->moral->description : '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($kaizen->innovation?->description))
                                                <div class="accordion-item border" id="innovation-accordion">
                                                    <h2 class="accordion-header" id="innovation-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#innovation">
                                                            <strong>Innovation</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="innovation">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->innovation->description) ? $kaizen->innovation->description : '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($kaizen->safety?->description))
                                                <div class="accordion-item border" id="safety-accordion">
                                                    <h2 class="accordion-header" id="safety-heading">
                                                        <button type="button"
                                                            class="accordion-button collapsed no-bg text-dark font-small-4"
                                                            data-bs-toggle="collapse" data-bs-target="#safety">
                                                            <strong>Safety</strong>
                                                        </button>
                                                    </h2>
                                                    <div class="accordion-collapse collapse show" id="safety">
                                                        <div class="accordion-body pt-0">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">

                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                {{ isset($kaizen->safety->description) ? $kaizen->safety->description : '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
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
    <!-- END: Content-->

    <!-- BEGIN: MODAL-->
    <div class="modal fade" id="teamModal" tabindex="-1" aria-labelledby="teamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Teams</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="teamModalBody">
                    <!-- Teams will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="status-modal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="status-modal-title"></h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                                <dic class="form-control">{{ $kaizen->remarks }}</dic>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const teamModal = document.getElementById('teamModal');
            teamModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const teams = JSON.parse(trigger.getAttribute('data-teams'));

                const body = teamModal.querySelector('#teamModalBody');
                body.innerHTML = ''; // Clear old content

                Object.entries(teams).forEach(([email, name]) => {
                    const badge =
                        `<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-1 mb-1">${name} (${email})</span>`;
                    body.innerHTML += badge;
                });
            });
        });
    </script>
@endsection
