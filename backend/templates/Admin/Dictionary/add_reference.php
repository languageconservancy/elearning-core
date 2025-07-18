<?php

use Cake\Routing\Router;

?>

<?= $this->Html->css('fastselect.min') ?>

<section class="content-header">
    <h1><?= __('Add/Edit Dictionary Listing') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Cards</a></li>
        <li class="active">Add/Edit Dictionary Listing</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($reference, ['type' => 'file']) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= $this->Form->control(
                            'lakota',
                            [
                                'label' => $languageName,
                                'class' => 'form-control',
                                'placeholder' => $languageName,
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control(
                            'english',
                            [
                                'label' => 'English',
                                'class' => 'form-control',
                                'placeholder' => "English",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control(
                            'morphology',
                            [
                                'label' => 'Morphology',
                                'class' => 'form-control',
                                'placeholder' => "Morphology",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control(
                            'part_of_speech',
                            [
                                'label' => 'Part of Speech',
                                'class' => 'form-control',
                                'placeholder' => "Part of Speech"
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-12" for="audio">Audio</label>
                            <div class="col-sm-5">
                                <button type="button" class="btn btn-default"
                                    data-toggle="modal" data-target="#fileLibrary"
                                    id="pick-audio">Choose Audio</button>
                            </div>
                            <div class="col-sm-7">
                                <?= $this->Form->hidden('audio', [
                                    'id' => "hidden-audio",
                                    'value' => $reference->audio ?? ''
                                ]); ?>
                                <audio controls id="audio-preview">
                                <?php
                                $src = $reference->FullUrl;
                                ?>
                                    <source class="audio-source"
                                        src="<?php echo $reference->audio != '' ? $src : ''; ?>"
                                        type="audio/mpeg">
                                    <source class="audio-source"
                                        src="<?php echo $reference->audio != '' ? $src : ''; ?>"
                                        type="audio/ogg">
                                    <source class="audio-source"
                                        src="<?php echo $reference->audio != '' ? $src : ''; ?>"
                                        type="audio/wav">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-12" for="video">Or Upload File</label>
                                <div class="col-sm-5">
                                    <button type="button" class="btn btn-default"
                                        data-toggle="modal" data-target="#uploadFile">Upload File</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reference">References</label>
                        <?php $referenceArr = explode(',', $reference->reference);?>
                        <?= $this->Form->select("referenceval", $tags, [
                            'id' => 'referenceval',
                            'class' => 'form-control fastselect',
                            'value' => $refArray,
                            'multiple' => true
                        ]); ?>

                        <?= $this->Form->hidden('reference', ['id' => "reference"]); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="full_entry">Full Entry</label>
                        <?= $this->Form->textarea("full_entry", [
                            'id' => "full_entry", 'rows' => "3", 'class' => "form-control",
                            'style' => "resize:none", 'val' => $reference['full_entry'] ?? '']); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>
<div class="modal fade" id="fileLibrary">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">File Library</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class='col-sm-6 col-md-6'>
                        <div class="form-group">
                            <span class="input-group">
                                <input type="text" autocomplete="off"
                                    class="form-control" placeholder="Keyword" id="search">
                                <input type="hidden" id="typebox">
                                <span class="input-group-addon" id="searchKeyword">Search</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="box-body">

                    <div class="row text-center">
                        <img src='<?php echo $this->request->getAttribute("webroot") . 'img/loader.png'?>'
                            clsss="img-responsive">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"
                    id="select-file-btn">Select File</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="uploadFile">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Upload New File</h4>
            </div>
            <div class="modal-body">
                 <?= $this->Form->create(null, ['type' => 'file','id' => 'upload_file_form']) ?>
                <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Name</label>
                                    <?= $this->Form->control(
                                        'name',
                                        [
                                            'label' => false,
                                            'class' => 'form-control',
                                            'placeholder' => "Enter Name"
                                        ]
                                    ) ?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Description</label>
                                    <?= $this->Form->control(
                                        'description',
                                        [
                                            'label' => false,
                                            'class' => 'form-control',
                                            'placeholder' => "Enter Description",
                                            'type' => 'textarea'
                                        ]
                                    ) ?>
                                </div>
                                <div class="form-group">
                                    <label>Upload File</label>
                                    <?= $this->Form->control('file', ['label' => false, 'type' => 'file']) ?>
                                </div>
                            </div>
                        </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <div id="msgupload" class="pull-left"></div>
                <button type="button" class="btn btn-primary" id="uploadFileFormSubmit">Upload File</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<?php // echo $this->Html->script('fastselect.standalone.min'); ?>
<?php echo $this->Html->script('fastselect.standalone.min', ['block' => 'scriptBottom']);?>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
$(function() {
    if ($('#hidden-audio').val() == '' || $('#hidden-audio').val() == null) {
        $('#audio-preview').hide();
    }
    <?php //} ?>
    $('#pick-audio').click(function(event) {
        $('#search').val('');
        $('#typebox').val('audio');
        getFiles('audio','');
    });
    $( "#upload_new_file" ).click(function() {
        $('#fileLibrary').modal("hide");
        $('#uploadFile').modal("show");
    });
    $('#searchKeyword').click(function(event) {
        var keyword=$('#search').val();
        var type=$('#typebox').val();
        getFiles(type,keyword);
    });

    $( "#uploadFileFormSubmit" ).click(function() {
        var form = $('#upload_file_form')[0];
        var data = new FormData(form);
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "<?= Router::url('/admin/files/uploadNewFile') ?>",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var responseData=JSON.parse(data);
                if (responseData.status==true) {
                    $('#upload_file_form')[0].reset();
                    $('#uploadFile').modal("hide");
                    switch (responseData.response.type) {
                        case 'audio':
                            var audioSrc = responseData.response.FullUrl;
                            $('#audio-preview').attr('src', audioSrc);
                            $('#audio-preview').show();
                            $('#audio-preview').load(audioSrc);
                            $('#hidden-audio').val(responseData.response.id);
                            break;
                        case 'image':
                            var imgSrc = responseData.response.FullUrl;
                            $('#img-preview').show();
                            $('#img-preview').attr('src', imgSrc);
                            $('#hidden-img').val(responseData.response.id);
                            break;
                        case 'video':
                            var videoSrc = responseData.response.FullUrl;
                            $('.video-src').attr('src', videoSrc);
                            $('#video-preview').show();
                            try {
                            $('#video-preview').load();
                            } catch (e) {
                                console.error(e);
                            }
                            $('#hidden-video').val(responseData.response.id);
                            break;
                        default:
                            break;
                    }
                } else {
                    $('#msgupload').html(responseData.message);
                }
            },
            error: function (e) {
                console.error(e);
            }
        });
    });

    $('body').on('click', '.paginatepre', function(event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type,keyword,page - 1);
    });
    $('body').on('click', '.paginatenext', function(event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type,keyword,page + 1);
    });

    $('body').on('click', '#select-file-btn', function(event) {
        var fileID = '';
        $('.file-res').each(function(index, el) {
            if ($(this).is(':checked')) {
                fileID = $(this).data('id');
            }
        });

        if (fileID) {
            var url = "<?= Router::url('/admin/files/getFile') ?>"
            $.ajax({
                method: "GET",
                url: url+"/"+fileID,
                success: function (res) {
                    var resultSet = JSON.parse(res);
                    if (resultSet.status == 'success') {
                        switch (resultSet.data.type) {
                            case 'audio':
                                var audioSrc = resultSet.data.FullUrl;
                                $('#audio-preview').attr('src', audioSrc);
                                $('#audio-preview').show();
                                $('#audio-preview').load(audioSrc);
                                $('#hidden-audio').val(resultSet.data.id);
                                break;
                            case 'image':
                                var imgSrc = resultSet.data.FullUrl;
                                $('#img-preview').show();
                                $('#img-preview').attr('src', imgSrc);
                                $('#hidden-img').val(fileID);
                                break;
                            case 'video':
                                var videoSrc = resultSet.data.FullUrl;
                                $('.video-src').attr('src', videoSrc);
                                $('#video-preview').show();
                                $('#video-preview').load();
                                $('#hidden-video').val(fileID);
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
    function getFiles(type,search='',page=1) {
        $('#fileLibrary .box-body .row').html('<img ' +
            'src="<?php echo $this->request->getAttribute("webroot") . "img/loader.png"?>"' +
            'clsss="img-responsive">');
        var url = "<?= Router::url('/admin/files/getFiles') ?>";
        $.ajax({
            method: "POST",
            url: url,
            data: {type:type,search:search,page:page},
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
                                html += "<img height='200' style='max-width: 100%' src='" + filePath +
                                    "' alt='" + resultSet.data[i].name + "'>";
                                break;
                            case 'audio':
                                html += "<div class='col-sm-6 modal-elemen-top-mergin'>";
                                html += "<audio controls>" +
                                    "<source src='" + filePath + "' type='audio/mpeg'>" + "</audio>";
                                break;
                            case 'video':
                                html += "<div class='col-sm-6 col-md-6'>";
                                html += "<video height='150' controls>" +
                                    "<source src='" + filePath + "' type='video/mp4'>" + "</video>";
                                break;
                            default:
                                html += '';
                                break;
                        }
                        html += "<label class='radio-inline'><input type='radio' "
                            + "name='file_res' class='file-res' data-id='"
                            + resultSet.data[i].id + "'> " + resultSet.data[i].file_name
                            + "</label>" + "</div>";
                    }
                    html +='<div class="box-footer clearfix col-sm-12 col-md-12">'
                        + '<ul class="pagination pagination-sm no-margin pull-right">';
                    if (resultSet.pageinfo.currentpage > 1) {
                        html += "<li><a data-page='" + resultSet.pageinfo.currentpage
                            + "' href='javascript:void(0)' class='paginatepre'>Previous</a></li>";
                    }

                    if (resultSet.pageinfo.totalpage > resultSet.pageinfo.currentpage) {
                        html += "<li><a href='javascript:void(0)' class='paginatenext' data-page='"
                            + resultSet.pageinfo.currentpage + "'>Next</a></li>";
                    }
                    fileDiv.html(html);
                }
            }
        });
    }
    $("<style>").text(".fstElement {font-size:10px;}").appendTo("head");

    var $select = $('.fastselect');

    // Run, fire and forget
    $('.fastselect').fastselect();

    $(document).on("change",'.fastselect', function() {
        $("#reference").val($('.fastselect').val());
    })
});
<?php $this->Html->scriptEnd(); ?>

