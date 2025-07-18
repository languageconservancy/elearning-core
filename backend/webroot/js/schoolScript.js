function imageUpload(type) {
    $('#image_type').val(type);
}
$(function () {
    // event listeners
    // select school image button is clicked
    $('#select-school-image').click(function (event) {
        getFiles('image');
    });
    // select file button is clicked
    $('body').on('click', '#select-file-btn', function (event) {
        var fileId = '';
        $('.file-res').each(function (index, el) {
            if ($(this).is(':checked')) {
                fileId = $(this).data('id');
            }
        });
        if (fileId) {
            var url = getAjaxUrl('admin/files/getFile');
            $.ajax({
                method: "GET",
                url: url + "/" + fileId,
                success: function (res) {
                    var resultSet = JSON.parse(res);
                    if (resultSet.status == 'success') {
                        switch (resultSet.data.type) {
                            case 'image':
                                $("#school-img-preview").attr('src', resultSet.data.FullUrl);
                                $("#school-img").val(resultSet.data.id);
                                break;
                            default:
                                break;
                        }
                    }
                },
                error: function (err) {
                  console.error(err);
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
                            $("#school-img-preview").attr('src', responseData.response.FullUrl);
                            $("#school-img").val(responseData.response.id);
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
            },
            error: function (err) {
                console.error(err);
            }
        });
    }
});
