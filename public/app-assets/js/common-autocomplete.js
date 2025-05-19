function initializeAutocompleteCommon(config) {
    const {
        selector,
        selectorSibling,
        sourceUrl,
        minLength = 0,
        appendTo = 'body',
        additionalData = {},
        labelKey1,
        labelKey2 = "",
        onSelectCallback = null
    } = config;

    $(selector).autocomplete({
        source: function (request, response) {
            const requestData = {
                q: request.term,
                ...additionalData
            };

            $.ajax({
                url: sourceUrl,
                method: 'GET',
                dataType: 'json',
                data: requestData,
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            id: item.id,
                            label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                            code: item[labelKey1] || '',
                        };
                    }));
                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        appendTo: appendTo,
        minLength: minLength,
        select: function (event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            if (selectorSibling) {
                $(selectorSibling).val(ui.item.id);
            }
            if (onSelectCallback) {
                onSelectCallback(ui.item);
            }
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
                $(this).val("");
                if (selectorSibling) {
                    $(selectorSibling).val("");
                }
            }
        }
    }).focus(function () {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}