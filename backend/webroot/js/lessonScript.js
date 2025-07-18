function deleteLesson(lessonId) {
    var r = confirm("Are you sure you want to delete this Lesson?");
    if (r == true) {
        var data = {'lessonId': lessonId};
        var lessonDeleteWarningUrl = getAjaxUrl('admin/lessons/lessonDeleteWarning');
        var deleteLessonUrl = getAjaxUrl('admin/lessons/deletelesson/');
        $.ajax({
            type: "POST",
            url: lessonDeleteWarningUrl,
            data: data,
            success: function (res) {
                if (res != 'success') {
                    $('#lessonDeleteDiv').html(res);
                    $('#lessonDeleteModal').modal("show");
                } else {
                    window.location.href = deleteLessonUrl + lessonId;
                }
            }
        });
    }
}
function eventBinding() {
    $('#numberOfBlock').change(function () {
        //generateBlockRadioInput($(this).val());
        generateBlockContentTypeInput($(this).val());
    });

    $('.blockContentType').change(function () {
        var type = $(this).val();
        var blocknumber = $(this).data('block');
        generateBlockDetailsHtml(blocknumber, type);
    });

    $('.openAudioModal').click(function () {
        $('#search').val('');
        $('#fileLibrary').modal('show');
        $('#typebox').val('audio');
        $('#format').val('blockaudio');
        var Blockno = $(this).data('block');
        $('#blockno').val(Blockno);
        getFiles('audio', '');
    });
    $('.openImageModal').click(function () {
        $('#search').val('');
        $('#fileLibrary').modal('show');
        $('#typebox').val('image');
        $('#format').val('blockimage');
        var Blockno = $(this).data('block');
        $('#blockno').val(Blockno);
        getFiles('image', '');
    });

    $('.openVideoModal').click(function () {
        $('#search').val('');
        $('#fileLibrary').modal('show');
        $('#typebox').val('video');
        $('#format').val('blockvideo');
        var Blockno = $(this).data('block');
        $('#blockno').val(Blockno);
        getFiles('video', '');
    });


    $('.upload-new-File').click(function () {
        var uploadType = $(this).data('type');
        $('#uploadtype').val(uploadType);
        $('#newblock').val('frameblock');
        $('#uploadFile').modal('show');
    });

    $('.upload-new-block-File').click(function () {
        var uploadType = $(this).data('type');
        $('#uploadtype').val(uploadType);
        $('#newblock').val($(this).data('uploadpos'));
        $('#uploadFile').modal('show');
    });

    setTimeout(function () {
        $('select.cardSelect').select2();
    }, 200);
}
/* for content type custom html*/
function generateHtmlDetails(blocknumber) {
    var customhtml =
        `<div class="form-group">
            <label>Enter Html (Unity only support b,i,size and color tag of html)</label>
            <textarea name="custom_html${blocknumber}" rows="3"
                class="form-control editor" style="resize: none;"></textarea>
        </div>`;
    return customhtml;
}
/* for content type audio*/
function generateAudioDetails(blocknumber) {
    var audiohtml =
        `<div class="row">
            <label class="col-sm-12" for="audio">Audio</label>
            <div class="col-sm-5">
                <button type="button" class="btn btn-default openAudioModal"
                    data-block="${blocknumber}">Choose Audio</button>
            </div>
            <div class="col-sm-7">
                <input type="hidden" id="block_audio_id${blocknumber}" name="block_audio_id${blocknumber}" value="">
                <audio style="max-width: 95%;" controls id="block-audio-preview${blocknumber}" style="display: none;">
                    <source class="block-audio-source${blocknumber}" src="" type="audio/mpeg">
                    <source class="block-audio-source${blocknumber}" src="" type="audio/ogg">
                    <source class="block-audio-source${blocknumber}" src="" type="audio/wav">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label class="col-sm-12" for="video">Or Upload Audio File</label>
                <div class="col-sm-5">
                    <button type="button" class="btn btn-default upload-new-block-File"
                        data-type="audio" data-uploadpos="${blocknumber}">Upload Audio File</button>
                </div>
            </div>
        </div>`;
    return audiohtml;
}
/* for content type image*/
function generateImageDetails(blocknumber) {
    var imagehtml =
        `<div class="row">
            <label class="col-sm-12" for="image">Image</label>
            <div class="col-sm-5">
                <button type="button" class="btn btn-default openImageModal"
                    data-block="${blocknumber}">Choose Image</button>
            </div>
            <div class="col-sm-7">
                <input type="hidden" id="hidden-img${blocknumber}"
                    name="block_image_id${blocknumber}" value="">
                <img id="img-preview${blocknumber}" height="70" src="" style="display: none;">
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label class="col-sm-12" for="video">Or Upload Image File</label>
                <div class="col-sm-5">
                    <button type="button" class="btn btn-default upload-new-block-File"
                        data-type="image" data-uploadpos="${blocknumber}">Upload Image File</button>
                </div>
            </div>
        </div>`;
    return imagehtml;
}
/* for content type Video*/
function generateVideoDetails(blocknumber) {
    var videohtml =
        `<div class="row">
            <label class="col-sm-12" for="video">Video</label>
            <div class="col-sm-5">
                <button type="button" class="btn btn-default openVideoModal"
                    data-block="${blocknumber}">Choose Video</button>
            </div>
            <div class="col-sm-7">
                <input type="hidden" id="hidden-video${blocknumber}" name="block_video_id${blocknumber}" value="">
                <video id="video-preview${blocknumber}" height="65" controls style="display: none;">
                    <source class="video-src${blocknumber}" src="" type="video/mp4">
                    <source class="video-src${blocknumber}" src="" type="video/ogg">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label class="col-sm-12" for="video">Or Upload Video File</label>
                <div class="col-sm-5">
                    <button type="button" class="btn btn-default upload-new-block-File"
                        data-type="video" data-uploadpos="${blocknumber}">Upload Video File</button>
                </div>
            </div>
        </div>`;
    return videohtml;
}
/*for frame js work*/
function generateBlockRadioInput(numberOfBlock) {
    var radiohtml = '';
    for (i = 1; i <= numberOfBlock; i++) {
        radiohtml +=
            `<div class="radio-inline">
                <label><input type="radio" name="optionBlock" id="optionsRadios${i}"
                    value="Block${i}">Block ${i}</label>
            </div>`;
    }
    $('#blockRadioInput').html(radiohtml);
    eventBinding();
}
function generateBlockContentTypeInput(numberOfBlock) {
    var ContentTypehtml = '';
    for (i = 1; i <= numberOfBlock; i++) {
        ContentTypehtml +=
           `<div class="form-group">
                <label for="content-type">Block ${i} Content Type</label>
                <select class="form-control blockContentType" name="block${i}type" data-block="${i}">
                    <option value="">Select Type</option>
                    <option value="card" >Card</option>
                    <option value="html">Custom HTML</option>
                    <option value="audio">Audio</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="row" id="detailsBlock${i}">
            </div>`;
    }
    $('#blockContentType').html(ContentTypehtml);
    eventBinding();
}
function generateBlockDetailsHtml(blocknumber, type, id = '') {
    var blockhtml = '';
    if (id != '') {
        blockhtml += '<input type="hidden" name="block' + blocknumber + 'id" value="' + id + '">';
    }
    if (type == 'html') {
        blockhtml += generateHtmlDetails(blocknumber);
    } else if (type == 'audio')
    {
        blockhtml += generateAudioDetails(blocknumber);
    } else if (type == 'image')
    {
        blockhtml += generateImageDetails(blocknumber);
    } else if (type == 'video')
    {
        blockhtml += generateVideoDetails(blocknumber);
    } else if (type == 'card') {
        blockhtml += generateCardDetails(blocknumber);
    }
    $("#detailsBlock" + blocknumber + "").html(blockhtml);
    eventBinding();
}
function getFiles(type, search = '', page = 1) {
    var url = getAjaxUrl('admin/files/getFiles');
    var loader = getAjaxUrl("img/loader.png");
    $('#fileLibrary .box-body .row').html('<img src="' + loader + '" clsss="img-responsive">');
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
                        case 'audio':
                            html += "<div class='col-sm-6 modal-elemen-top-mergin'>";
                            html += "<audio style='max-width: 95%;' controls>" +
                                    "<source src='" + filePath + "' type='audio/mpeg'>" +
                                    "</audio>";
                            break;
                        case 'video':
                            html += "<div class='col-sm-6 col-md-6'>";
                            html += "<video height='150' controls>" +
                                    "<source src='" + filePath + "' type='video/mp4'>" +
                                    "</video>";
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
                    html += "<li><a data-page='" + resultSet.pageinfo.currentpage + "' href='javascript:void(0)' class='paginatepre'>Previous</a></li>";
                }

                if (resultSet.pageinfo.totalpage > resultSet.pageinfo.currentpage) {
                    html += "<li><a href='javascript:void(0)' class='paginatenext' data-page='" + resultSet.pageinfo.currentpage + "'>Next</a></li>";
                }
                fileDiv.html(html);
            }
        }
    });
}
$(function () {
    $('#FramePreviewModal').on('hidden.bs.modal', function () {
        var audios = document.getElementsByTagName('audio');
        for (var i = 0, len = audios.length; i < len; i++) {
            audios[i].pause();
        }
    });
    $('.openFrameAudioModal').click(function () {
        $('#search').val('');
        $('#fileLibrary').modal('show');
        $('#typebox').val('audio');
        $('#format').val('frameaudio');
        var Blockno = $(this).data('block');
        $('#blockno').val(Blockno);
        getFiles('audio', '');
    });
    $('body').on('click', '.file-res', function (event) {
        var Id = $(this).data('id');
        $('#SelectFileId').val(Id);
    });
    $('body').on('click', '.paginatepre', function (event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type, keyword, page - 1);
    });
    $('body').on('click', '.paginatenext', function (event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type, keyword, page + 1);
    });
    $('body').on('click', '#select-file-btn', function (event) {
        var fileID = $('#SelectFileId').val();
        if (fileID) {
            var url = getAjaxUrl('admin/files/getFile');
            $.ajax({
                method: "GET",
                url: url + "/" + fileID,
                success: function (res) {
                    var resultSet = JSON.parse(res);
                    if (resultSet.status == 'success') {
                        switch (resultSet.data.type) {
                            case 'audio':
                                var format = $('#format').val();
                                var blockno = $('#blockno').val();
                                if (format == 'frameaudio')
                                {
                                    var audioSrc = resultSet.data.FullUrl;
                                    $('.audio-source').attr('src', audioSrc);
                                    $('#audio-preview').show();
                                    $('#audio-preview')[0].load();
                                    $('#hidden-audio').val(resultSet.data.id);

                                    setTimeout(function () {
                                        var vid = document.getElementById("audio-preview");
                                        var duration = vid.duration
                                        $('#audio_duration').val(Math.round(duration));
                                    }, 1000);
                                } else
                                {
                                    var audioSrc = resultSet.data.FullUrl;
                                    $(".block-audio-source" + blockno + "").attr('src', audioSrc);
                                    $("#block-audio-preview" + blockno + "").show();
                                    $("#block-audio-preview" + blockno + "")[0].load();
                                    $("#block_audio_id" + blockno + "").val(resultSet.data.id);
                                }
                                break;
                            case 'image':
                                var blockno = $('#blockno').val();
                                var imgSrc = resultSet.data.FullUrl;
                                $("#img-preview" + blockno + "").show();
                                $("#img-preview" + blockno + "").attr('src', imgSrc);
                                $("#hidden-img" + blockno + "").val(resultSet.data.id);
                                break;
                            case 'video':
                                var blockno = $('#blockno').val();
                                var videoSrc = resultSet.data.FullUrl;
                                $(".video-src" + blockno + "").attr('src', videoSrc);
                                $("#video-preview" + blockno + "").show();
                                $("#video-preview" + blockno + "")[0].load();
                                $("#hidden-video" + blockno + "").val(resultSet.data.id);
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
        var url = getAjaxUrl('admin/files/uploadNewFrameFile');
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: url,
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var responseData = JSON.parse(data);
                if (responseData.status == true) {
                    $('#uploadFile').modal("hide");
                    switch (responseData.response.type) {
                        case 'audio':
                            var format = $('#format').val();
                            var newblock = $('#newblock').val();
                            if (newblock == 'frameblock')
                            {
                                var audioSrc = responseData.response.FullUrl;
                                $('.audio-source').attr('src', audioSrc);
                                $('#audio-preview').show();
                                $('#audio-preview')[0].load();
                                $('#hidden-audio').val(responseData.response.id);
                                setTimeout(function () {
                                    var vid = document.getElementById("audio-preview");
                                    var duration = vid.duration
                                    $('#audio_duration').val(Math.round(duration));
                                }, 1000);
                            } else
                            {
                                var audioSrc = responseData.response.FullUrl;
                                $(".block-audio-source" + newblock + "").attr('src', audioSrc);
                                $("#block-audio-preview" + newblock + "").show();
                                $("#block-audio-preview" + newblock + "")[0].load();
                                $("#block_audio_id" + newblock + "").val(responseData.response.id);
                            }
                            $('#upload_file_form')[0].reset();
                            break;
                        case 'image':
                            var newblock = $('#newblock').val();
                            var imgSrc = responseData.response.FullUrl;
                            $("#img-preview" + newblock + "").show();
                            $("#img-preview" + newblock + "").attr('src', imgSrc);
                            $("#hidden-img" + newblock + "").val(responseData.response.id);
                            $('#upload_file_form')[0].reset();
                            break;
                        case 'video':
                            var newblock = $('#newblock').val();
                            var videoSrc = responseData.response.FullUrl;
                            $(".video-src" + newblock + "").attr('src', videoSrc);
                            $("#video-preview" + newblock + "").show();
                            $("#video-preview" + newblock + "")[0].load();
                            $("#hidden-video" + newblock + "").val(responseData.response.id);
                            $('#upload_file_form')[0].reset();
                            break;
                        default:
                            break;
                    }
                } else
                {
                    $('#msgupload').html(responseData.message);
                }
            },
            error: function (e) {
            }
        });
    });
    $('#searchKeyword').click(function (event) {
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type, keyword);
    });
    $('.previewFrame').click(function () {
        var frameFormdata = $('#frameForm')[0];
        var data = new FormData(frameFormdata);
        var url = getAjaxUrl('admin/lessons/previewBlock');
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                $('#previewmodalhtml').html(data);
                $('#FramePreviewModal').modal('show');
                $('.playaudio').click(function () {
                    var id = $(this).data('id');
                    var vid = document.getElementById("preview" + id);
                    vid.play();
                });
                $('.stopaudio').click(function () {
                    var id = $(this).data('id');
                    var vid = document.getElementById("preview" + id);
                    vid.pause();
                });
            },
            error: function (e) {}
        });
    });
});

/**
 * Enables Drag 'n' Drop behavior for the LessonFrames table on the managelessons page.
 */
$(function () {
    const FRAME_PREFIX = "Frame";
    const FRAME_ID_COLUMN = 0;
    const FRAME_NAME_COLUMN = 1;
    const FRAME_SORT_COLUMN = 2;
    const FRAME_EDIT_DELETE_COLUMN = 3;
    const ACTIVE_FRAME_CLASS = "activerow";
    /* Ternary here since element 'sortingMethod' doesn't exist in the managelessons page, only when editing a lesson */
    const HREF_STR = (document.getElementById('sortingMethod')) ? document.getElementById('sortingMethod').value : '';
    /* Save the starting table contents for resetability */
    var tableStart = document.getElementById('frames-list-table').cloneNode(true);
    /* Initialize draggable table. Default css class is tDnD_whileDrag.
       Reference: https://github.com/isocra/TableDnD/ */
    $('#frames-list-table').tableDnD({
        /**
         * After a LessonFrame row in the LessonFrames table is dropped in a new position,
         * causing a shift in the rows, the database in updated to reflect the new order of lesson frames,
         * then a result message is shown for N seconds with success/error message. If there's an error,
         * the page automatically reloads to make sure the user is working with correct data.
         * @param {HTML DOM Table Object} table LessonFrames table object
         * @param {HTML DOM Table Object} row row that was dropped
         */
        onDrop: function(table, row) {
            /* Make sure user reordered on purpose */
            if (!confirm("Reorder and rename frames?")) {
                /* If the user cancels the row drag, then reset the table */
                table.innerHTML = tableStart.innerHTML;
                $('#frames-list-table').tableDnDUpdate();
                return;
            }

            /* Create local copy of table rows after drag and drop */
            var rows = Array.prototype.slice.call(table.rows);
            rows.shift(); // remove table header row

            /* Update databse with new frame order */
            this.updateDatabase(rows);

            /* Update HTML visuals */
            table.innerHTML = tableStart.innerHTML;
            var activeFrameId = document.getElementById('activeFrameId').value;
            var k;
            var activeFrameIndex = -1;
            var oldFrameId;
            var oldID;
            var newID;
            var re;
            for (var i = 0; i < rows.length; i++) {
                k = i+1
                /* Set convenience variables for old and new frame IDs */
                oldFrameId = tableStart.rows[k].cells.item(FRAME_ID_COLUMN).innerHTML;
                newFrameId = rows[i].cells.item(FRAME_ID_COLUMN).innerHTML

                /* Update activerow class */
                if (activeFrameId >= 0) {
                    if (parseInt(newFrameId, 10) == activeFrameId) {
                        table.rows[k].classList.add(ACTIVE_FRAME_CLASS);
                        activeFrameIndex = k;
                    } else {
                        /* Remove class from row that causes it to be highlighted */
                        table.rows[k].classList.remove(ACTIVE_FRAME_CLASS);
                    }
                }

                /* Update ID column */
                table.rows[k].cells.item(FRAME_ID_COLUMN).innerHTML = newFrameId;

                /* Update Frame name column */
                table.rows[k].cells.item(FRAME_NAME_COLUMN).innerHTML = FRAME_PREFIX + " " + (k);

                /* Update frame ID in sorting column (the chevrons (up and down arrows) by replacing old ID with new one.
                   Including 'sorting' here to avoid the situation where the lesson number is the same as the frame number. */
                oldID = "sorting\/"+oldFrameId;
                newID = "sorting\/"+newFrameId;
                re = new RegExp(oldID, "g");
                table.rows[k].cells.item(FRAME_SORT_COLUMN).innerHTML = table.rows[k].cells.item(FRAME_SORT_COLUMN).innerHTML.replace(re, newID);

                /* Update edit pencil button link and update trashcan delete button link, both in same column */
                oldID = "\/"+oldFrameId+'\"';
                newID = "\/"+newFrameId+'\"';
                re = new RegExp(oldID, "g");
                table.rows[k].cells.item(FRAME_EDIT_DELETE_COLUMN).innerHTML = table.rows[k].cells.item(FRAME_EDIT_DELETE_COLUMN).innerHTML.replace(re, newID);
            }
            /* Update read-only FrameForm text in editing area that indicates the active frame being edited */
            if (activeFrameIndex >= 0) {
                document.getElementById('formID').value = FRAME_PREFIX + " " + activeFrameIndex;
            }
            /* Update 'starting table' contents for next drag and drop, since we aren't reloading the page */
            tableStart.innerHTML = table.innerHTML;
            $('#frames-list-table').tableDnDUpdate();
            /* Reload page (this takes time and is jarring so preferring to not reload until user clicks a button */
            // document.location.reload(true);
        },
        /**
         * Updates the order of current lesson's frames by creating an array of frame IDs corresponding to the new order
         * and POSTing it to the PHP api.
         * @param {HTML DOM Row Object array} rows Array of row objects corresponding to the new correct order.
         */
        updateDatabase(rows) {
            const CLEAR_RESULT_MSEC = 1500;
            const resultID = 'framesUpdateResult';
            var cells;
            /* Create and fill in array with frame IDs */
            var idArray = new Array(rows.length);
            for (var i = 0; i < rows.length; i++) {
                cells = rows[i].cells;
                var frameId = cells.item(FRAME_ID_COLUMN).innerHTML;
                idArray[i] = frameId;
            }

            /* Re-sort by calling , in controller and pass new or of frame ids */
            var urlString = document.getElementById('reorderAndRenameMethod').value;
            jQuery.ajax({
                type: "POST",
                url: urlString,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify(idArray),
                success: function(data, textStatus, jqXHR) {
                    document.getElementById(resultID).innerHTML = "Successfully updated frames";
                    document.getElementById(resultID).className = "alert alert-success";
                    document.getElementById(resultID).style = "visibility: visible;";
                    setTimeout(function function_name(argument) {
                        document.getElementById("framesUpdateResult").style = "visibility: hidden;";
                    }, CLEAR_RESULT_MSEC);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    document.getElementById(resultID).innerHTML = "Error updating frames" + (errorThrown ? (": " + errorThrown) : "") + "\nRefreshing page...";
                    document.getElementById(resultID).className = "alert alert-error";
                    document.getElementById(resultID).style = "visibility: visible;";
                    setTimeout(function function_name(argument) {
                        document.getElementById(resultID).style = "visibility: hidden;";
                    }, CLEAR_RESULT_MSEC);
                    /* Reload page to not confuse user with false information */
                    document.location.reload(true);
                }
            });
        }
    });
});
