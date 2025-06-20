$(document).ready(function() {
    $('#colorCheck1').change(function() {
        if ($(this).is(':checked')) {
            $('#whatsapp_number').val($('#phone_mobile').val());
        } else {
            $('#whatsapp_number').val('');
        }
    });

    $('#phone_mobile').on('input', function() {
        if ($('#colorCheck1').is(':checked')) {
            $('#whatsapp_number').val($(this).val());
        }
    });
});
//add-symbol
$(document).ready(function() {
    var $currencySymbol = $('#currencySymbol');
    function updateCurrencySymbol() {
        var selectedOption = $('#currencySelect option:selected');
        var shortName = selectedOption.data('short_name');
        $currencySymbol.text(shortName);
    }
    $('#currencySelect').on('change', updateCurrencySymbol);
    updateCurrencySymbol();
});
//edit-symbol
$(document).ready(function() {
    var $currencyShortName = $('#currencyShortName');
    var $currencySelect = $('#currencySelect');
    function updateCurrencyShortName() {
        var selectedOption = $currencySelect.find('option:selected');
        var shortName = selectedOption.data('short-name');
        $currencyShortName.text(shortName);
    }
    $currencySelect.on('change', updateCurrencyShortName);
    updateCurrencyShortName();
});
//Category Onchnage
// $(document).ready(function() {
//     function loadSubcategories(categoryId, selectedSubcategoryId, subcategorySelect) {
//         if (categoryId) {
//             $.ajax({
//                 url: '/categories/subcategories/' + categoryId,
//                 method: 'GET',
//                 success: function(response) {
//                     subcategorySelect.empty();
//                     subcategorySelect.append('<option value="">Sub-Category</option>');
//                     $.each(response, function(index, subcategory) {
//                         subcategorySelect.append(
//                             '<option value="' + subcategory.id + '"' + 
//                             (subcategory.id == selectedSubcategoryId ? ' selected' : '') + '>' + 
//                             subcategory.name + '</option>'
//                         );
//                     });
//                 },
//                 error: function() {
//                     alert('An error occurred while fetching subcategories.');
//                 }
//             });
//         } else {
//             subcategorySelect.empty();
//             subcategorySelect.append('<option value="">Sub-Category</option>');
//         }
//     }
//     $(document).on('change', 'select.category-chnage', function() {
//         var categorySelect = $(this);
//         var categoryId = categorySelect.val();
//         var subcategorySelect = $('select[name="subcategory_id"]');
//         var selectedSubcategoryId = subcategorySelect.data('selected-id');
//         loadSubcategories(categoryId, selectedSubcategoryId, subcategorySelect);
//     });
//     function initializeSubcategories() {
//         var categorySelect = $('select[name="category_id"]');
//         var initialCategoryId = categorySelect.val();
//         var subcategorySelect = $('select[name="subcategory_id"]');
//         var selectedSubcategoryId = subcategorySelect.data('selected-id');
//         console.log(selectedSubcategoryId);
//         loadSubcategories(initialCategoryId, selectedSubcategoryId, subcategorySelect);
//     }

//     initializeSubcategories();
// });








