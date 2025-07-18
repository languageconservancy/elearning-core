$(document).ready(function() {
    const currentUrl = window.location.href;

    if (currentUrl.endsWith('manage-exercises')) {
        localStorage.removeItem('exerciseListPage');
        localStorage.removeItem('exerciseListLength');
    }

    // On page load, get saved page number and length from local storage
    var savedPage = localStorage.getItem('exerciseListPage') || 0;
    var savedLength = localStorage.getItem('exerciseListLength') || 10;
    // Calculate start position based on saved page number and length
    var savedStart = parseInt(savedPage) * parseInt(savedLength);

    var table = $('#exercise-list-table').DataTable({
        'serverSide': true, // use server-side processing
        'processing': true, // show a loading indicator
        'pageLength': parseInt(savedLength),
        'ajax': {
            'url': API_ROUTES.getExerciseList,
            'type': 'GET',
            'data': function (d) {
                d.length = parseInt(localStorage.getItem('exerciseListLength') || 10);
                savedPage = localStorage.getItem('exerciseListPage') || 0;
                savedStart = parseInt(savedPage) * d.length;
                d.start = savedStart;
                return d;
            },
            'error': function(xhr, error, thrown) {
                console.error('DataTables AJAX Error: ', xhr, error, thrown);
            }
        },
        'columns': [
            { 'data': 'id' },
            { 'data': 'name' },
            { 'data': 'action' }
        ],
        'searching': true,
        'paging': true,
        'lengthChange': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        'scrollY': "200px",
        'dom': '<"top"i>frt<"bottom"lp><"clear">',
        'language': {
            'searchPlaceholder': 'ID or name',
        },
        'displayStart': savedStart, // Set correct start position on load
        'initComplete': function(settings, json) {
            // Fix incorrect highlighting of pagination buttons
            setTimeout(() => {
                table.page(parseInt(savedPage)).draw(false);
            }, 100);
        }
    });

    // Save page number when pagination changes
    $('#exercise-list-table').on('page.dt', function () {
        let page = table.page.info().page;
        localStorage.setItem('exerciseListPage', page);
    });

    $('#exercise-list-table').on('length.dt', function () {
        let length = table.page.len();
        localStorage.setItem('exerciseListLength', length);
    });

    setTimeout(function function_name() {
        $('#exercise-list-table tbody tr').each(function () {
            if ($(this).hasClass('activerow')) {
                var top = ($(".activerow").offset().top - $(window).height() + 150);
                if (top < 0) {
                    top = 0;
                }
                $('#exercise-list-table').parent('.dataTables_scrollBody').animate({
                    scrollTop: top
                }, 3000);
            }
        });
    }, 500);
});
