@extends('layouts.app')

@section('content')
    <form class="ajax-input-form" method="POST" action="{{ route('machine.store') }}" data-redirect="{{ route('machine.index') }}">
    @csrf
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">   
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Machine Details</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('machine.index') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <a href="{{ route('machine.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Create</button>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="newheader border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Machine Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Machine Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="machine_name" class="form-control" placeholder="Enter Machine Name" k="{{ old('machine_name') }}" />
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Attribute Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="attribute_group_id" class="form-control" id="attribute_group_id">
                                                        @if(count($attributes) > 1)
                                                            <option value="" disabled selected>Select Attribute</option>
                                                        @else
                                                            @foreach ($attributes as $attribute)
                                                            <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <input type="hidden" name="attribute_group_name" class="form-control" id="attribute_group_name">
                                                    <select id="attribute-options-master" class="d-none">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Production Route <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" id="production_route_id" name="production_route_id">
                                                        @foreach($productionRoutes as $productionRoute)
                                                            <option value="{{$productionRoute->id}}">{{ucfirst($productionRoute->name)}}</option>
                                                        @endforeach     
                                                     </select>  
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-3">
                                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="status_active" name="status" value="active" class="form-check-input" checked="">
                                                                <label class="form-check-label fw-bolder" for="status_active">
                                                                    Active
                                                                </label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="status_inactive" name="status" value="inactive" class="form-check-input">
                                                            <label class="form-check-label fw-bolder" for="status_inactive">
                                                                Inactive
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive-md">
                                            <div class="table-responsive-md">
                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                    <thead>
                                                        <tr>
                                                            <th>S.NO</th>
                                                            <th id="dynamic-attribute-label">Attribute <span class="text-danger">*</span></th>
                                                            <th id="dynamic-length-label">Length <span class="text-danger">*</span></th>
                                                            <th id="dynamic-width-label">Width <span class="text-danger">*</span></th>
                                                            <th id="dynamic-pairs-label">No. of Pairs <span class="text-danger">*</span></th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="machine-details-box">
                                                        <tr>
                                                            <td>1</td>
                                                            <td>
                                                                <select name="machine_details[0][attribute_id]" class="form-control mw-100 attribute-values">
                                                                    <option k="Test" disabled selected>Select Attribute</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="machine_details[0][length]" class="form-control mw-100" placeholder="Enter Length" /></td>
                                                            <td><input type="number" name="machine_details[0][width]" class="form-control mw-100" placeholder="Enter Width" /></td>
                                                            <td><input type="number" name="machine_details[0][no_of_pairs]" class="form-control mw-100" placeholder="Enter No. of Pairs" /></td>
                                                            <td>
                                                                <a href="javascript:;" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="6" class="text-end">
                                                                <a href="javascript:;" class="add-contactpeontxt mt-0 text-primary addnew mt-0">
                                                                    <i data-feather="plus"></i> Add New Item
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
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
</form>
@endsection

@section('scripts')
<script>
setTimeout(() => {
    let attrName = $("#attribute_group_id option:selected").text() || '';
    $("#attribute_group_name").val(attrName);
    $("#dynamic-attribute-label").html(attrName + ' <span class="text-danger">*</span>');
    if(headerFilled()) {
        $('#machine-details-box :input, .addnew, .delete-row').prop('disabled', false);
    } else {
        $('#machine-details-box :input, .addnew, .delete-row').prop('disabled', true);
    }
}, 0);

$('input[name="machine_name"], select[name="attribute_group_id"], select[name="production_route_id"]').on('input change', function () {
    if(headerFilled()) {
        $('#machine-details-box :input, .addnew, .delete-row').prop('disabled', false);
    } else {
        $('#machine-details-box :input, .addnew, .delete-row').prop('disabled', true);
    }
});

function headerFilled() 
{
    let n = $("input[name='machine_name']").val() || '';
    let a_g_i = $("#attribute_group_id").val() || '';
    let p_r_i = $("#production_route_id").val() || '';
    if( n.trim() === '' || a_g_i.trim() === '' || p_r_i.trim() === '') {
        return false;
    }
    return true;
}
function getAttributeValues() {
    let attributeGroupId = $('#attribute_group_id').val() || '';
    let actionUrl = "{{ route('machine.attribute.values') }}"+'?attribute_group_id='+attributeGroupId;

    fetch(actionUrl)
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            let options = '<option value="">Select Attribute Value</option>';
            data.data.values.forEach(attribute => {
                options += `<option value="${attribute.id}">${attribute.value}</option>`;
            });

            // Save to master and set initial dropdown
            $('#attribute-options-master').html(options);
            $('.attribute-values').html(options);

            updateAttributeDropdowns();
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error'
            });
        }
    });
}


getAttributeValues();

let isEdit = false;
let rowIndex = $('#machine-details-box tr').length;

// ➕ Add new row
$(document).on('click', '.addnew', function () {
    const options = getFilteredAttributeOptions();
    rowIndex++;

    const newRow = `
        <tr>
            <td>${rowIndex}</td>
            <td>
                <select name="machine_details[${rowIndex}][attribute_id]" class="form-control mw-100 attribute-values" required>
                    ${options}
                </select>
            </td>
            <td><input type="number" name="machine_details[${rowIndex}][length]" class="form-control mw-100" placeholder="Enter Length" required /></td>
            <td><input type="number" name="machine_details[${rowIndex}][width]" class="form-control mw-100" placeholder="Enter Width" required /></td>
            <td><input type="number" name="machine_details[${rowIndex}][no_of_pairs]" class="form-control mw-100" placeholder="Enter No. of Pairs" required /></td>
            <td>
                <a href="javascript:;" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
            </td>
        </tr>`;

    $('#machine-details-box').append(newRow);
    feather.replace();
    updateAttributeDropdowns();
});

// 🗑️ Delete row
$(document).on('click', '.delete-row', function () {
    const $row = $(this).closest('tr');

    if ($('#machine-details-box tr').length === 1) {
        Swal.fire({
            title: 'Action Denied',
            text: 'You must have at least one row.',
            icon: 'warning'
        });
        return;
    }

    const proceedDelete = () => {
        $row.remove();
        updateRowNumbers();
        updateAttributeDropdowns();
    };

    if (isEdit) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete this row.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                proceedDelete();
            }
        });
    } else {
        proceedDelete();
    }
});

// 🔁 Update row numbers
function updateRowNumbers() {
    $('#machine-details-box tr').each(function (index) {
        $(this).find('td:first').text(index + 1);
    });
    rowIndex = $('#machine-details-box tr').length - 1;
}

// 🚫 Prevent duplicate attribute selection
function updateAttributeDropdowns() {
    const selectedValues = [];

    $('.attribute-values').each(function () {
        const val = $(this).val();
        if (val) selectedValues.push(val);
    });

    $('.attribute-values').each(function () {
        const $this = $(this);
        const currentVal = $this.val();
        const baseOptions = $('#attribute-options-master option');

        let options = '';

        baseOptions.each(function () {
            const optVal = $(this).val();
            const optText = $(this).text();

            if (!optVal) {
                options += `<option value="" >Select Attribute</option>`;
            } else if (optVal === currentVal || !selectedValues.includes(optVal)) {
                options += `<option value="${optVal}" ${optVal === currentVal ? 'selected' : ''}>${optText}</option>`;
            }
        });

        $this.html(options);
    });
}


// 👂 On attribute change, refresh dropdowns
$(document).on('change', '.attribute-values', function () {
    updateAttributeDropdowns();
});

// 🔁 Get options with filtered duplicates
function getFilteredAttributeOptions() {
    const baseOptions = $('#attribute-options-master option');
    const selectedValues = $('.attribute-values').map(function () {
        return $(this).val();
    }).get();

    let options = '';
    baseOptions.each(function () {
        const val = $(this).val();
        const text = $(this).text();
        if (!val || !selectedValues.includes(val)) {
            options += `<option value="${val}">${text}</option>`;
        }
    });

    return options;
}

</script>
@endsection
