function imageUpload(type) {
    $('#image_type').val(type);
}
$(function () {
    var typeVal = $('#set-type').val();
    $('.moveup').hide();
    $('.movedown').hide();
    $(".removecard").each(function (index) {
        if ($(this).data('type') == typeVal) {
            var dataId = $(this).data("id");
            $(".singlecard").each(function (index, element) {
                if ($(this).find('.card_id').html() == dataId) {
                    $(this).addClass("added");
                }
            });
        }
    });
    var dataTable = $('#path-comp-table').DataTable({
        'searching': true,
        'paging': false,
        'lengthChange': false,
        'ordering': true,
        'info': false,
        'autoWidth': false,
        'scrollY': "400px",
        'dom': '<"top"i>frt<"bottom"lp><"clear">'
    });
    $('#set-type').change(function (event) {
        var typeVal = $(this).val();
        var searchVal = $('#path-comp-table_filter').find("input[type='search']").val();
        var url = getAjaxUrl('admin/learning-path/getUnitDetailsData/');
        $.ajax({
            type: "GET",
            url: url + $(this).val(),
            success: function (res) {
                var result = JSON.parse(res);
                var rows = [];
                for (var i = 0; i < result.data.length; i++) {
                    var row = '<tr class="singlecard">' +
                            '<td class="card_id" data-type="' + typeVal + '">' + result.data[i].id + '</td>' +
                            '<td>' + result.data[i].name + '</td>' +
                            '</tr>';
                    rows.push(row);
                }
                $('#path-comp-table').DataTable().destroy();
                $('#unit-details-data').html(rows);

                $('#path-comp-table').DataTable({
                    'searching': true,
                    'paging': false,
                    'lengthChange': false,
                    'ordering': true,
                    'info': false,
                    'autoWidth': false,
                    'scrollY': "400px",
                    'dom': '<"top"i>frt<"bottom"lp><"clear">'
                });
                if (typeVal == 'lesson') {
                    $('#set-type-label').html('Lessons');
                }
                else {
                    $('#set-type-label').html('Exercises');
                }
                $(".removecard").each(function (index) {
                    if ($(this).data('type') == typeVal) {
                        var dataId = $(this).data("id");
                        $(".singlecard").each(function (index, element) {
                            if ($(this).find('.card_id').html() == dataId) {
                                $(this).addClass("added");
                            }
                        });
                    }
                });
                var searchBox = $('#path-comp-table_filter').find("input[type='search']");
                searchBox.val(searchVal);
                searchBox.keyup();
            }
        })
    });
    $('#learningpath-id').change(function (event) {
        var url = getAjaxUrl('admin/learning-path/managePaths/');
        window.location.href = url + $(this).val();
    });
    $('#level-id').change(function (event) {
        var levelId = $(this).val();
        var url = getAjaxUrl('admin/learning-path/managePaths/');
        window.location.href = url + $('#learningpath-id').val() + '/' + levelId;
    });
    $('body').on('click', '.paginatepre', function (event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $(this).data('type');
        getFiles(type, keyword, page - 1);
    });
    $('body').on('click', '.paginatenext', function (event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $(this).data('type');
        getFiles(type, keyword, page + 1);
    });
    $('#select-path-image').click(function (event) {
        $('#image_type').val('path');
        getFiles('image');
    });
    $('#select-level-image').click(function (event) {
        $('#image_type').val('level');
        getFiles('image');
    });
    $('#searchKeyword').click(function (event) {
        var keyword = $('#search').val();
        getFiles('image', keyword);
    });
    $('body').on('click', '#select-file-btn', function (event) {
        var fileID = '';
        $('.file-res').each(function (index, el) {
            if ($(this).is(':checked')) {
                fileID = $(this).data('id');
            }
        });
        if (fileID) {
            var url = getAjaxUrl('admin/files/getFile');
            $.ajax({
                method: "GET",
                url: url + "/" + fileID,
                success: function (res) {
                    var resultSet = JSON.parse(res);
                    if (resultSet.status == 'success') {
                        switch (resultSet.data.type) {
                            case 'image':
                                switch ($('#image_type').val()) {
                                    case 'path':
                                        $("#path-img-preview").attr('src', resultSet.data.FullUrl);
                                        $("#path-img").val(resultSet.data.id);
                                        break;
                                    case 'level':
                                        $("#level-img-preview").attr('src', resultSet.data.FullUrl);
                                        $("#level-img").val(resultSet.data.id);
                                        break;
                                    default:
                                        break;
                                }

                                break;
                            default:
                                break;
                        }
                    }
                }
            });
        }
    });
    $('body').on('click', '#uploadFileFormSubmit', function (event) {
        var form = $('#upload_file_form')[0];
        var data = new FormData(form);

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: getAjaxUrl('admin/files/uploadNewFile'),
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var responseData = JSON.parse(data);
                if (responseData.status == true) {
                    $('#upload_file_form')[0].reset();
                    $('#uploadFile').modal("hide");
                    switch (responseData.response.type) {
                        case 'image':
                            switch ($('#image_type').val()) {
                                case 'path':
                                    $("#path-img-preview").attr('src', responseData.response.FullUrl);
                                    $("#path-img").val(responseData.response.id);
                                    break;
                                case 'level':
                                    $("#level-img-preview").attr('src', responseData.response.FullUrl);
                                    $("#level-img").val(responseData.response.id);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        default:
                            break;
                    }
                }
                else
                {
                    $('#msgupload').html(responseData.message);
                }
            },
            error: function (e) {
            }
        });
    });
    $('body').on('click', ".singlecard", function () {
        var html = $(this).html();
        if ($(this).hasClass("selected"))
        {
            $(this).removeClass("selected");
        }
        else {
            $(this).addClass("selected");
        }
    });
    $(".addcard").click(function () {
        var action = 'add';
        var id = [];
        $(".singlecard").each(function (index) {
            if ($(this).hasClass("selected")) {
                var _this = $(this);
                var typeVal = $('#set-type').val();

                id.push("<tr class='removecard' data-type='" + typeVal + "' data-id='" + _this.find(".card_id").html() + "'>" + _this.html() + '<td class="typeClass">' + typeVal + '</td></tr>"');

                $(this).removeClass("selected");
                $(this).addClass("added");
            }
        });
        $("#selected_cards").append(id);
    });
    $(document).on("click", ".removecard", function () {
        var html = $(this).html();
        var _this = $(this);
        if ($(this).hasClass("selectremove")) {
            $(this).removeClass("selectremove");
        }
        else {
            $(this).addClass("selectremove");
        }
        /*show Details*/
        var count = $('.selectremove').length;
        var lastIndex = $('.removecard:last').index();
        var firstIndex = $('.selectremove:first').index();

        if (count >= 1) {
            // sort
            $('.moveup').show();
            $('.movedown').show();
            $('.selected-details').slideUp();
            // var rowIndex = _this.index();
            var rowIndex = null;
            $('.removecard').each(function (index, el) {
                if ($(this).hasClass('selectremove')) {
                    rowIndex = $(this).index();
                }
            });
            if (firstIndex == 0) {
                $('.moveup').hide();
            }else{
                $('.moveup').show();
            }
            if (rowIndex == lastIndex) {
                $('.movedown').hide();
            }else{
                $('.movedown').show();
            }
          }
          if(count == 1){
            var type = _this.data('type');
            $('.selected-details').slideDown();
            $('#selected-details-type').html(type);
            var detId = $('.selectremove').data('id');
            var type = $('.selectremove').data('type');
            var data = {'id': detId, 'type': type};
            var url = getAjaxUrl('admin/learning-path/getUnitDetailSingle/');
            $.ajax({
                type: "POST",
                url: url + $(this).val(),
                data: data,
                success: function (res) {


                    var result = JSON.parse(res);

                    if (type == 'lesson') {
                        var frameContent = ' ';
                        for (var i = 0; i < result.lessonframes.length; i++) {
                            frameContent += result.lessonframes[i].name;
                            // if (result.lessonframes[i].lesson_frame_blocks.length > 0) {
                            // 	frameContent += ' - ' + result.lessonframes[i].lesson_frame_blocks.length + ' block';
                            // 	frameContent += result.lessonframes[i].lesson_frame_blocks.length > 1 ? 's' : ' ';
                            // }
                            frameContent += i < result.lessonframes.length - 1 ? ', ' : ' ';
                        }
                        var content = '<li class="list-group-item">ID: ' + result.id + '</li>' +
                                '<li class="list-group-item">Name: ' + result.name + '</li>' +
                                '<li class="list-group-item">Frames: ' + result.lessonframes.length + ' (' + frameContent + ')</li>';
                        $('.selected-details .list-group').html(content);
                    }
                    else {
                        var exType = '';
                        switch (result.exercise_type) {
                            case 'multiple-choice':
                                exType = 'Multiple Choice';
                                break;
                            case 'match_the_pair':
                                exType = 'Match The Pair';
                                break;
                            case 'anagram':
                                exType = 'Anagram';
                                break;
                            default:
                                break;
                        }

                        var promptType = '';
                        var responseType = '';

                        var promteResponseArr = result.promteresponsetype.split("-");
                        var responseType = promteResponseArr[1];
                        var promotetype = promteResponseArr[0];

                        switch (promotetype) {
                            case 'i':
                                promptType = 'Image';
                                break;
                            case 'l':
                                promptType = 'Lakota';
                                break;
                            case 'a':
                                promptType = 'Audio';
                                break;
                            case 'e':
                                promptType = 'English';
                                break;
                            case 'v':
                                promptType = 'Video';
                                break;
                            default:
                                break;
                        }
                        switch (responseType) {
                            case 'i':
                                responseType = 'Image';
                                break;
                            case 'l':
                                responseType = 'Lakota';
                                break;
                            case 'a':
                                responseType = 'Audio';
                                break;
                            case 'e':
                                responseType = 'English';
                                break;
                            case 'v':
                                responseType = 'Video';
                                break;
                            default:
                                break;
                        }

                        var content = '<li class="list-group-item">ID: ' + result.id + '</li>' +
                                '<li class="list-group-item">Name: ' + result.name + '</li>' +
                                '<li class="list-group-item">Type: ' + exType + '</li>' +
                                '<li class="list-group-item">P->R: ' + promptType + ' -> ' + responseType + '</li>';
                        $('.selected-details .list-group').html(content);
                    }
                }
            });
        }
        if(count == 0) {
            $('.selected-details').slideUp();
            $('.moveup').hide();
            $('.movedown').hide();
        }
    });
    $('body').on('click', '.cardremove', function () {
        $('.selected-details').slideUp();
        $(".removecard").each(function (index) {
            if ($(this).hasClass("selectremove")) {
                var dataId = $(this).data("id");
                $(this).remove();
                $(".singlecard").each(function (index, element) {
                    if ($(this).hasClass("added") && $(this).find('.card_id').html() == dataId) {
                        $(this).removeClass("added");
                    }
                });
            }
        });
    });
    $('body').on('click', '#savedetailOK', function () {
        var url = getAjaxUrl('admin/learning-path/managePaths/');
        window.location.href = url + $('#learningpath-id').val() + '/' + $('#level-id').val() + '/' + $('#unit-id').val();
    });
    $('body').on('click', '#saveUnitDetails', function () {
        $('.selected-details').slideUp();
        var postData = {
            learningpath_id: $('#learningpath-id').val(),
            unit_id: $('#unit-id').val(),
            detailsData: []
        }
        $(".removecard").each(function (index) {
            var data = {
                id: $(this).data('id'),
                type: $(this).data('type')
            };
            postData.detailsData.push(data);
        });
        var url = getAjaxUrl('admin/learning-path/saveUnitDetailsData');
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: url,
                data: postData,
                dataType: 'json',
                success: function (res) {
                    $('#unitSaveSuccess').modal('show');
                }
            });
        }, 500);
    });
    $('body').on('click', '.movedown', function (event) {
        event.preventDefault();
        /* Act on the event */
        var tableSize = $('.removecard').length;
        jQuery.fn.reverse = function() {return this.pushStack(this.get().reverse(), arguments);};
        $('.removecard').reverse().each(function (index, el) {
            if ($(this).hasClass('selectremove')) {
                var _this = $(this);
                _this.next().after(_this);
                if ($(this).index() == tableSize - 1) {
                    $('.movedown').hide();
                    $('.moveup').show();
                }
                else if($(this).index() == $('.selectremove:last').index()){
                    $('.movedown').show();
                    $('.moveup').show();
                }
            }
        });
    });
    $('body').on('click', '.moveup', function (event) {
        event.preventDefault();
        /* Act on the event */
        $('.removecard').each(function (index, el) {
            if ($(this).hasClass('selectremove')) {
                var _this = $(this);
                _this.prev().before(_this);
                if ($(this).index() == 0) {
                    $('.moveup').hide();
                    $('.movedown').show();
                }
                else if($(this).index() == $('.selectremove:first').index()){
                    $('.moveup').show();
                    $('.movedown').show();
                }
            }
        });
    });

    $('body').on('click', "#save_level_btn", function () {

        var levelimg= $( "#level-img" ).val();
        if(levelimg==''){
            alert('Please select Image');
        }else
        {
             $( "#level_form" ).submit();
        }

    });
    function getFiles(type, search = '', page = 1) {
        var loader = getAjaxUrl("img/loader.png");
        $('#fileLibrary .box-body .row').html('<img src="' + loader + '" clsss="img-responsive">');
        var url = getAjaxUrl('admin/files/getFiles');
        $.ajax({
            method: "POST",
            url: url,
            data: {type: type, search: search, page: page},
            success: function (res) {
                var resultSet = JSON.parse(res);
                if (resultSet.status == 'success') {
                    var fileDiv = $('#fileLibrary .box-body .row');
                    var html = '';
                    for (var i = 0; i < resultSet.data.length; i++) {
                        var filePath = resultSet.data[i].FullUrl;

                        switch (type) {
                            case 'image':
                                html += "<div class='col-sm-6 col-md-4 modal-elemen-top-mergin'>";
                                html += "<img height='200' style='max-width: 100%' src='" + filePath + "' alt='" + resultSet.data[i].name + "'>";
                                break;
                            default:
                                html += '';
                                break;
                        }
                        html += "<label class='radio-inline'><input type='radio' name='file_res' class='file-res' data-id='" + resultSet.data[i].id + "'> " + resultSet.data[i].file_name + "</label>" +
                                "</div>";
                    }
                    html += '<div class="box-footer clearfix col-sm-12 col-md-12"><ul class="pagination pagination-sm no-margin pull-right">';
                    if (resultSet.pageinfo.currentpage > 1) {
                        html += "<li><a data-page='" + resultSet.pageinfo.currentpage + "' href='javascript:void(0)' class='paginatepre' data-type='" + resultSet.pageinfo.type + "'>Previous</a></li>";
                    }

                    if (resultSet.pageinfo.totalpage > resultSet.pageinfo.currentpage) {
                        html += "<li><a href='javascript:void(0)' class='paginatenext' data-page='" + resultSet.pageinfo.currentpage + "' data-type='" + resultSet.pageinfo.type + "'>Next</a></li>";
                    }
                    fileDiv.html(html);
                }
            }
        });
    }
});

/**
 * Enables Drag and Drop behavior for Units within a level
 */
$(function () {
    // Save the starting table contents for resetability and comparison
    const tableEl = document.getElementById('units-table');
    const tableStartClone = tableEl.cloneNode(true);

    $('#units-table').tableDnD({
        onDragStart: function (table, row) {
            console.log("Drag started: ", row);
        },
        onDrop: async function (table, row) {
            // Make sure user reordered on purpose
            setTimeout(async () => {
                if (!confirmAndResetTable(table, tableStartClone, 'Reorder units?')) {
                    console.debug("User cancelled the unit reorder action.");
                    return;
                }

                // Create local copy of table rows after drag and drop
                const newOrder = [];

                $('#units-table tbody tr').each(function () {
                    const id = $(this).attr('id') || $(this).data('id');
                    if (id) {
                        newOrder.push(parseInt(id));
                    }
                });

                const pathId = parseInt($('#learningpath-id').val());
                const levelId = parseInt($('#level-id').val());

                // Update database with new unit sequence
                const ok = await updateUnitOrderInDabase(newOrder, pathId, levelId);

                if (ok) {
                    $(row).addClass('reordered-temp');
                    setTimeout(function () {
                        $(row).removeClass('reordered-temp');
                    }, 1500);
                } else {
                    // Reset to original table contents on error
                    tableEl.innerHTML = tableStartClone.innerHTML;
                    $(tableEl).tableDnDUpdate();
                }
            }, 0);
        },
    });


    /**
     *
     * @param {array<number>} newUnitOrder - The new order of unit IDs
     * @param {number} pathId - The ID of the learning path
     * @param {number} levelId - The ID of the level
     * @returns
     */
    async function updateUnitOrderInDabase(newUnitOrder, pathId, levelId) {
        // This function can be used to update the order in the database
        // For example, you can send an AJAX request to update the order
        if (newUnitOrder.length === 0) {
            console.warn('No new order to update.');
            return;
        }
        if (!pathId) {
            console.error('Path ID is undefined.');
            return;
        }
        if (!levelId) {
            console.error('Level ID is undefined.');
            return;
        }

        const urlString = getAjaxUrl('admin/learning-path/updateUnitOrder');

        try {
            const response = await jQuery.ajax({
                type: 'POST',
                url: urlString,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    pathId: pathId,
                    levelId: levelId,
                    unitOrder: newUnitOrder
                })
            });
            return response.success === true;
        } catch (error) {
            // Handle error response
            console.error('Error updating order:', error);
            return false;
        }
    }
});