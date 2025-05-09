let levelCounter = 1;
let levelCounter2 = 0;
// Initialize the level counter based on existing levels
$('.approvlevelflow').each(function () {
    const index = parseInt($(this).data('index'), 10);
    if (index > levelCounter) {
        levelCounter2 = index;
    }
    
});
levelCounter = levelCounter2 + 1; // Start from the next index

$(document).ready(function () {
    renumberLevels();
    // Add new level
    function addNewLevel() {
        levelCounter++;
        const newLevel = createLevelHTML(levelCounter);
        $('.levelContainer').append(newLevel);
    }

    // Generate HTML for a level
    function createLevelHTML(index) {
        return `
            <div class="approvlevelflow row align-items-center mb-1" data-index="${index}">
                <div class="col-md-3"> 
                    <label class="form-label">Level ${index} <span class="text-danger">*</span></label>  
                </div>
                <div class="col-md-5">  
                    <input type="text" class="form-control mw-100" name="levels[${index}][name]">
                    <input type="hidden" class="form-control mw-100" name="levels[${index}][level]" value="${index}">
                    <input type="hidden" class="form-control mw-100" name="levels[${index}][l_id]" value="">
                </div>
                <div class="col-md-3">
                    <a href="#" class="text-primary addLevel" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </a>
                    <a href="#" class="text-danger deleteLevel" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </a>
                </div>
            </div>
        `;
    }

    // Recalculate level numbers
    function renumberLevels() {
        let newIndex = 1;
        const rows = $('.approvlevelflow');
        const rowCount = rows.length;
        $('.approvlevelflow').each(function (i) {
            $(this).attr('data-index', newIndex);
            $(this).find('label.form-label').html(`Level ${newIndex} <span class="text-danger">*</span>`);
            $(this).find('input[name$="[name]"]').attr('name', `levels[${newIndex}][name]`);
            $(this).find('input[name$="[level]"]')
                .attr('name', `levels[${newIndex}][level]`)
                .val(newIndex);
            
            $(this).find('.addLevel').attr('data-index', newIndex);
            $(this).find('.deleteLevel').attr('data-index', newIndex);
            // Update button visibility
            if (rowCount === 1) {
                // Only 1 row: show Add button, hide Delete button
                $(this).find('.addLevel').show();
                $(this).find('.deleteLevel').hide();
            } else {
                if (i === rowCount - 1) {
                    // Last row: show Add button, show Delete button
                    $(this).find('.addLevel').show();
                    $(this).find('.deleteLevel').show();
                } else {
                    // Previous rows: hide Add button, show Delete button
                    $(this).find('.addLevel').hide();
                    $(this).find('.deleteLevel').show();
                }
            }

            newIndex++;
        });
        levelCounter = newIndex - 1;
    }

    $('.levelContainer').on('click', '.addLevel', function (e) {
        e.preventDefault();
        addNewLevel();
        renumberLevels(); // << After adding new row, re-arrange buttons properly
    });

    // Delete level and renumber
    $('.levelContainer').on('click', '.deleteLevel', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                let id = e.target.getAttribute('data-id');
                if (id) {
                    $.ajax({
                        type: "POST",
                        url: "/warehouse-structures/delete-level",
                        data: { id: id },
                        success: function (data) {
                            if (data.status == 200) {
                                console.log(data);
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Selected rows have been deleted.',
                                    icon: 'success',
                                });
                                $(this).closest('.approvlevelflow').remove();
                                renumberLevels();
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.data.message,
                                    icon: 'error',
                                });
                                return flase;
                                console.log(data);
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Selected rows have been deleted.',
                        icon: 'success',
                    });
                    $(this).closest('.approvlevelflow').remove();
                    renumberLevels();
                }
            }
        });
    });
});

// Get Store Wise Sub Stores 
function getSubStores(storeLocationId)
{
    console.log('storeLocationId', storeLocationId);
    
    const storeId = storeLocationId;
    $.ajax({
        url: "/sub-stores/store-wise",
        method: 'GET',
        dataType: 'json',
        data: {
            store_id : storeId,
        },
        success: function(data) {
            if((data.status == 200) && data.data.length) {
                let options = '';
                data.data.forEach(function(location) {
                    options+= `<option value="${location.id}">${location.name}</option>`;
                });
                $(".sub_store").empty();
                $(".sub_store").html(options);
            } else {
                $(".sub_store").empty();
                Swal.fire({
                    title: 'Error!',
                    text: "Warehouse does not exist for location.",
                    icon: 'error',
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message,
                icon: 'error',
            });
        }
    });
}