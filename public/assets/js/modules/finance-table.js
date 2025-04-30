function handleRowSelection(tableSelector) {
    $(tableSelector).on('click', 'tbody tr', function () {
        // Remove from all, then add to clicked row
        $(tableSelector).find('tr').removeClass('trselected');
        $(this).addClass('trselected');
    });

    $(document).on('keydown', function (e) {
        const $selected = $(tableSelector).find('.trselected');
        if (e.which == 38) {  // Up arrow key
            $selected.prev('tr').addClass('trselected').siblings().removeClass('trselected');
        } else if (e.which == 40) {  // Down arrow key
            $selected.next('tr').addClass('trselected').siblings().removeClass('trselected');
        }
    });
}