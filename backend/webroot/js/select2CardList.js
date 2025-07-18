/**
 * Setup Select2 element for CardList
 * @param {string} selectId - ID of the select element
 * @param {string} getSelectedUrl - URL to fetch preselected data
 * @param {string} getListUrl - URL to fetch list data
 * @param {array} preselectedCardIds - Preselected IDs
 */
function setUpSelect2Element(selectId, getSelectedUrl, getListUrl, preselectedCardIds = []) {
    if (preselectedCardIds.length > 0) {
        $.ajax({
            url: getSelectedUrl,
            type: 'GET',
            data: { ids: preselectedCardIds },
            dataType: 'json',
            success: function (response) {
                initializeSelect2(selectId, getListUrl, response.items || []);
            },
            error: function (xhr, status, error) {
                console.error(`Error fetching preselected data from ${getSelectedUrl}: `, error);
            }
        });
    } else {
        initializeSelect2(selectId, getListUrl, []);
    }
}

/**
 * Initialize Select2 with AJAX for CardList
 * @param {*} id - ID of the select element
 * @param {*} url - URL for AJAX request
 * @param {*} preselectedData - Preselected data
 */
function initializeSelect2(id, url, preselectedData) {
    if (!id) {
        console.warn('initializeSelect2 called with an empty ID.');
        return;
    }
    $(`#${id}`).select2({
        data: preselectedData,
        ajax: {
            url: url,
            dataType: 'json',
            delay: 250, // Delay for better user experience
            data: function (params) {
                return {
                    q: params.term, // Search term
                    page: params.page || 1 // Pagination
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.items, // Format as Select2 expects
                    pagination: {
                        more: data.pagination.more // Indicate if there are more pages
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 1, // Require at least 1 character to start searching
        placeholder: 'Search text or ID...',
        allowClear: true
    });

    if (preselectedData.length > 0) {
        const ids = preselectedData.map(item => item.id);
        $(`#${id}`).val(ids).trigger('change');
    }
}