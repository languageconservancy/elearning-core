<?php

use Cake\Routing\Router;

?>
<section class="content-header">
    <h1><?= __('Add/Edit Card') ?></h1>

    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Cards</a></li>
        <li class="active">Add/Edit card</li>
    </ol>
</section>
<?php //print_r($card); ?>
<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($card) ?>
        <div class="box-body addcardspage">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="card-type">Card Type *</label>
                        <?= $this->Form->select('card_type_id', $cardTypes, ['class' => 'form-control']) ?>
                    </div>
                    <div class="form-group">
                        <label for="lakota"><?php echo $languageName; ?> *</label>
                        <span> (allowed tags: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;,
                            &lt;color&gt;, &lt;size&gt;. See examples at bottom of page.)
                        </span>
                        <span>
                            <?= $this->Form->textarea(
                                'lakota',
                                [
                                    'id' => 'lakota',
                                    'div' => false,
                                    'label' => false,
                                    'autocomplete' => 'off',
                                    'placeholder' => $languageName,
                                    'class' => 'form-control',
                                    'required' => true,
                                    'style' => 'resize: none'
                                ]
                            ) ?>
                            <!-- <span class="input-group-addon" id="searchLakota">Search</span> -->
                        </span>
                        <!-- <ul id="autocomplete"></ul> -->
                        <div id="lakota_preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="english">English *</label>
                        <span> (allowed tags: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;,
                            &lt;color&gt;, &lt;size&gt;. See examples at bottom of page.)
                        </span>
                        <?= $this->Form->textarea(
                            'english',
                            [
                                'id' => 'english',
                                'div' => false,
                                'label' => 'English *',
                                'placeholder' => 'English',
                                'class' => 'form-control',
                                'required' => true,
                                'style' => 'resize: none'
                            ]
                        ) ?>
                        <div id="english_preview"></div>
                    </div>


                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <?= $this->Form->select("gender", [
                            ["text" => "Default", "value" => "default"],
                            ["text" => "Female", "value" => "female"],
                            ["text" => "Male", "value" => "male"],
                            ["text" => "Neuter", "value" => "neuter"]
                        ], ["val" => $card->gender, "id" => "gender", "class" => "form-control"]) ?>
                    </div>
                    <div class="form-group">
                        <label for="alt_lakota">Alt Lakota</label>
                        <?= $this->Form->text(
                            'alt_lakota',
                            [
                                'div' => false,
                                'label' => 'Alternate ' . $languageName,
                                'placeholder' => 'Alternate ' . $languageName,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label for="alt_english">Alt English</label>
                        <?= $this->Form->text(
                            'alt_english',
                            [
                                'div' => false,
                                'label' => 'Alternate English',
                                'placeholder' => 'Alternate English',
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label for="include_review">Include Review</label>
                        <?php
                             $fl = $card->include_review ? $card->include_review : 1;
                            echo $this->Form->checkbox('include_review', ['label' => false, 'value' => $fl]);

                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-12" for="audio">Audio</label>
                            <div class="col-sm-5">
                                <button type="button" class="btn btn-default" data-toggle="modal"
                                    data-target="#fileLibrary" id="pick-audio">Add Audio</button>
                            </div>
                            <div class="col-sm-7" id="audio-group">
                                <?php
                                if ($card->audio != null && $card->audio != "") {
                                    $cardAudioIdArray = explode(",", $card->audio);
                                    for ($i = 0; $i < $card->AudioCount; $i++) {
                                        ?>
                                    <div id="audio-row<?=$i?>" class="audio-row">
                                        <?= $this->Form->hidden(
                                            'audios[]',
                                            [
                                                'id' => 'hidden-audio' . $i,
                                                'value' => $cardAudioIdArray[$i]
                                            ]
                                        ); ?>


                                        <audio controls id="audio-preview<?=$i?>">
                                        <?php

                                        $src = $card->FullAudioUrlArray[$i];
                                        ?>
                                            <source class="audio-source<?=$i?>"
                                                src="<?php echo $src; ?>" type="audio/mpeg">
                                            <source class="audio-source<?=$i?>"
                                                src="<?php echo $src; ?>" type="audio/ogg">
                                            <source class="audio-source<?=$i?>"
                                                src="<?php echo $src; ?>" type="audio/wav">
                                            Your browser does not support the audio element.
                                        </audio>
                                        <i class="fa fa-remove cursor-pointer" id="remove-audio<?=$i?>"
                                            onclick="removeAudio(<?=$i?>)"
                                            style="font-size:35px;color:red;position:relative;top:-9px">
                                        </i>
                                    </div>
                                    <?php }
                                }?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-12" for="image">Image</label>
                            <div class="col-sm-5">
                                <button type="button" class="btn btn-default" data-toggle="modal"
                                    data-target="#fileLibrary" id="pick-image">Choose Image</button>
                            </div>
                            <div class="col-sm-7">
                                <?= $this->Form->hidden(
                                    'image_id',
                                    [
                                        'id' => 'hidden-img',
                                        'value' => $card->image_id
                                    ]
                                ); ?>
                                <?php if ($card->image_id != '') { ?>
                                    <?php $src = $card->image->FullUrl; ?>
                                    <img id="img-preview" height="70"
                                        src="<?php echo $card->image->file_name != '' ? $src : ''; ?>"
                                        alt="Image">
                                <?php } else { ?>
                                    <img id="img-preview" height="70" src="">
                                <?php } ?>
                                <i class="fa fa-remove cursor-pointer" id="remove-image"
                                    onclick="removeImage()"
                                    style="font-size:35px;color:red;position:relative;top:-9px;<?php echo $card->image_id != '' ? '' : 'display:none;'; ?>">
                                </i>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-12" for="video">Video</label>
                            <div class="col-sm-5">
                                <button type="button" class="btn btn-default" data-toggle="modal"
                                    data-target="#fileLibrary" id="pick-video">Choose Video</button>
                            </div>
                            <div class="col-sm-7">
                                <?= $this->Form->hidden(
                                    'video_id',
                                    [
                                        'id' => 'hidden-video',
                                        'value' => $card->video_id
                                    ]
                                ); ?>
                                <?php if ($card->video_id != '') { ?>
                                    <?php $src = $card->video->FullUrl; ?>
                                <video id="video-preview" height="65" controls>
                                    <source class="video-src"
                                        src="<?php echo $card->video->FullUrl != '' ? $src : ''; ?>" type="video/mp4">
                                    <source class="video-src"
                                        src="<?php echo $card->video->FullUrl != '' ? $src : ''; ?>" type="video/ogg">
                                    Your browser does not support the video tag.
                                </video>
                                <?php } else { ?>
                                    <video id="video-preview" height="65" controls>
                                    <source class="video-src" src="" type="video/mp4">
                                    <source class="video-src" src="" type="video/ogg">
                                    Your browser does not support the video tag.
                                </video>
                                <?php } ?>
                                <i class="fa fa-remove cursor-pointer" id="remove-video"
                                    onclick="removeVideo()"
                                    style="font-size:35px;color:red;position:relative;top:-9px;<?php echo $card->video_id != '' ? '' : 'display:none;'; ?>">
                                </i>
                            </div>
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
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-12" for="metadata">Metadata</label>
                            <?php
                            if ($card->reference_dictionary_id != '') {
                                $card->metadata = "Reference Dictionary ID: " . $card->reference_dictionary_id;
                                if ($card->inflection_id != '') {
                                    $card->metadata .= "\r\n" . "Inflection ID: " . $card->inflection_id;
                                }
                            }
                            ?>
                            <div class="col-sm-12">
                                <textarea name="metadata" id="metadata" rows="3" class="form-control"
                                    style="resize:none" disabled>
                                    <?= isset($card->metadata) ? trim($card->metadata) : '' ?>
                                </textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary" id="card-submitter">Submit</button>
            </div>
            <div class="col-md-6">
                <?php echo $this->element('rich_text_helper_table'); ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
        <div id="audio-template" style="display: none;">
            <div id="audio-row" class="audio-row">
                <?= $this->Form->hidden(
                    'audios[]',
                    [
                        'id' => 'hidden-audio',
                        'value' => ""
                    ]
                ); ?>
                <audio controls="" id="audio-preview">
                  <source class="audio-source" src="" type="audio/mpeg">
                  <source class="audio-source" src="" type="audio/ogg">
                  <source class="audio-source" src="" type="audio/wav">
                  Your browser does not support the audio element.
                </audio>
                <i class="fa fa-remove cursor-pointer" id="remove-audio"
                    style="font-size:35px;color:red;position:relative;top:-9px">
                </i>
            </div>
        </div>
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
                 <?= $this->Form->create(null); ?>
                <div class="row">
                    <div class='col-sm-6 col-md-6'>
                        <div class="form-group">
                            <span class="input-group">
                                <?= $this->Form->text('search', [
                                    'autocomplete' => "off", 'class' => "form-control",
                                    'placeholder' => "Keyword", 'id' => "search"]); ?>
                                <?= $this->Form->hidden('typebox', ['id' => 'typebox']); ?>
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
                <?= $this->Form->end(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    data-dismiss="modal" id="select-file-btn">Select File</button>
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


<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    let validForm = false;

    function removeAudio(id){
      if(id!=null){
        var audiorowid = "#audio-row"+id;
        $(audiorowid).remove()
      }
    }

    function removeImage() {
        $('#img-preview').hide();
        $('#hidden-img').val('');
        $('#remove-image').hide();
    }

    function removeVideo() {
        $('#video-preview').hide();
        $('#hidden-video').val('');
        $('#remove-video').hide();
    }

    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };

    function renderPreview(element, value, render) {
        if (!value) {
            return value;
        }
        let replaceTags = '';
        replaceTags = value.replace(/<size=(\d*)/g, (resp) => {
            let sizeVal = resp.match(/=(\d*)/);
            let replaceText = '<span style="font-size:' + sizeVal[1] + 'px"';
            return replaceText;
        });
        replaceTags = replaceTags.replace(/<\/size>/g, '</span>');
        replaceTags = replaceTags.replace(/<color=(\#*\w*)/g, (resp) => {
            let colorVal = resp.match(/=(\#*\w*)/);
            let replaceText = '<span style="color: ' + colorVal[1] + '"';
            return replaceText;
        });
        replaceTags = replaceTags.replace(/<\/color>/g, '</span>');
        replaceTags = replaceTags.replace(/[\n\r]+/g, '<br>');
        replaceTags = '<p>'+replaceTags+'</p>';

        if (render) {
            $("#" + element).html(replaceTags);
        }
        return replaceTags;
    }

    function handlePreview(language) {
        const submitButton = $("#card-submitter");

        let doc = document.createElement('div');
        // validate
        doc.innerHTML = renderPreview(language + '_preview', $('#' + language).val());

        // render
        let preview = renderPreview(language + '_preview', $('#' + language).val());
        if (doc.innerHTML === preview) {
            this.validForm = true;
            submitButton.prop("disabled", false);

            renderPreview(language + '_preview', $('#' + language).val(), true);
        } else {
            this.validForm = false;
            submitButton.prop("disabled", true);
            renderPreview(
                language + '_preview',
                '<p style="color:#700000; font-weight: bold">Invalid richtext</p>',
                true
            );
        }
    }

    $(function() {
        if ($('#hidden-img').val() == '' || $('#hidden-img').val() == null) {
            $('#img-preview').hide();
        }

        $("#english").on("input", function() {
            handlePreview('english');
        });

        $("#lakota").on("input", function() {
            handlePreview('lakota');
        });

        handlePreview('lakota');
        handlePreview('english');

        <?php if (!isset($card->audio)) { ?>
            if (
                $('#audio-preview').val() == '' ||
                $('#audio-preview').val() == null
            ) {
                $('#audio-preview').hide();
            }
        <?php }

        if (!isset($card->video_id)) { ?>
            if (
                $('#video-preview').val() == '' ||
                $('#video-preview').val() == null
            ) {
                $('#video-preview').hide();
            }
        <?php } ?>

        $('#autocomplete').hide();
        $('#lakota').keyup(debounce(function() {
            var queryString = $('#lakota').val();
            if (queryString.length > 2) {
                var url = "<?= Router::url('/admin/dictionary/autoCompleteList') ?>"
                $.ajax({
                    method: "GET",
                    url: url+"?q="+queryString,
                    success: function (res) {
                        var resultSet = JSON.parse(res);
                        if (resultSet.status == 'success') {
                            $('#autocomplete').show();
                            var autoList = $('#autocomplete');
                            var item = '';
                            for (var i = 0; i < resultSet.data.length; i++) {
                                item += "<li class='autolakota' data-id='" + resultSet.data[i].id +
                                    "'>" + resultSet.data[i].lakota + "</li>";
                            }
                            autoList.html(item);
                        } else {
                            $('#autocomplete').hide();
                        }
                    }
                });
            } else {
                $('#autocomplete').hide();
            }
        },1000));

        $("#upload_new_file").click(function() {
            $('#fileLibrary').modal("hide");
            $('#uploadFile').modal("show");
        });

        $("#uploadFileFormSubmit").click(function() {
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
                    if (responseData.status == true) {
                        $('#upload_file_form')[0].reset();
                        $('#uploadFile').modal("hide");
                        switch (responseData.response.type) {
                            case 'audio':
                                var audioSrc = responseData.response.FullUrl;
                                var n = $('.audio-row:nth-last-of-type(1)').attr('id').replace('audio-row','');
                                n++;
                                $('#audio-group').append($('#audio-template').html());
                                $('#audio-preview').attr('src', audioSrc);
                                $('#audio-preview').show();
                                $('#audio-preview')[0].load();
                                $('#hidden-audio').val(responseData.response.id);
                                $('#audio-preview:first').attr('id','audio-preview'+n);
                                $('#audio-row:first').attr('id','audio-row'+n);
                                $('#hidden-audio:first').attr('id','hidden-audio'+n);
                                $('#remove-audio:first').attr('onclick','removeAudio('+n+')');
                                $('#remove-audio:first').attr('id','remove-audio'+n);
                                $('.audio-source:first').attr('class','audio-source'+n);
                                $('.audio-source:first').attr('class','audio-source'+n);
                                $('.audio-source:first').attr('class','audio-source'+n);
                                break;
                            case 'image':
                                var imgSrc = responseData.response.FullUrl;
                                $('#img-preview').show();
                                $('#img-preview').attr('src', imgSrc);
                                $('#hidden-img').val(responseData.response.id);
                                $('#remove-image').show();
                                break;
                            case 'video':
                                var videoSrc = responseData.response.FullUrl;
                                $('#video-preview').attr('src', videoSrc);
                                $('#video-preview').show();
                                $('#video-preview')[0].load();
                                $('#hidden-video').val(responseData.response.id);
                                $('#remove-video').show();
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

        $('body').on('click', '.autolakota', function(event) {
            var refID = $(this).data('id');
            var url = "<?= Router::url('/admin/dictionary/getRef') ?>"
            $.ajax({
                method: "GET",
                url: url+"/"+refID,
                success: function (res) {
                    var resultSet = JSON.parse(res);
                    if (resultSet.status == 'success') {
                        $('#lakota').val(resultSet.data.lakota);
                        $('#autocomplete').hide();
                    }
                }
            });
        });

        $('#searchLakota').click(function(event) {
            var queryString = $('#lakota').val();
            var url = "<?= Router::url('/admin/dictionary/autoCompleteList') ?>"
            $.ajax({
                method: "GET",
                url: url+"?search="+queryString,
                success: function (res) {
                    var resultSet = JSON.parse(res);
                    if (resultSet.status == 'success') {
                        var autoTable = $('#searchRes tbody');
                        var item = '';
                        for (var i = 0; i < resultSet.data.length; i++) {
                            item += "<tr><td>" +
                                "<div class='checkbox listchkbox'>" +
                                    "<label><input type='radio' name='populate_res'class='lakota-res' data-id='" +
                                        resultSet.data[i].id + "'></label>"+
                                "</div>" +
                                "</td>" +
                                "<td>" + resultSet.data[i].id + "</td>" +
                                "<td>" + resultSet.data[i].lakota + "</td>" +
                                "<td>" + resultSet.data[i].english + "</td>" +
                                "<td>" + resultSet.data[i].reference + "</td>" +
                                "<td>" + resultSet.data[i].part_of_speech + "</td></tr>";
                        }
                        autoTable.html(item);
                    }
                }
            });
        });

        $('#populate-results').click(function(event) {
            var refID = '';
            $('input[name=populate_res]').each(function(index, el) {
                if($(this).is(':checked')){
                    refID = $(this).data('id');
                }
            });
            if (refID) {
                var url = "<?= Router::url('/admin/dictionary/getRef') ?>"
                $.ajax({
                    method: "GET",
                    url: url+"/"+refID,
                    success: function (res) {
                        var resultSet = JSON.parse(res);
                        if (resultSet.status == 'success') {
                            $('#lakota').val(resultSet.data.lakota);
                            $('#english').val(resultSet.data.english);
                            $('#alt-lakota').val(resultSet.data.morphology);
                            $('input[name="reference_dictionary_id"]').val(resultSet.data.id);
                            if(resultSet.data.inflection) {
                                $('input[name="inflection_id"]').val(resultSet.data.inflection.id);
                                var metadata = 'Reference Dictionary ID: '
                                    + resultSet.data.id + '\nInflection ID: ' + resultSet.data.inflection.id;
                            } else {
                                $('input[name="inflection_id"]').val('');
                                var metadata = 'Reference Dictionary ID: ' + resultSet.data.id;
                            }

                            $('textarea[name="metadata"]').val(metadata);

                            if (resultSet.data.audio) {
                                var audioSrc = resultSet.data.FullUrl;
                                $('.audio-source').attr('src', audioSrc);
                                $('#hidden-audio').val(resultSet.data.audio);
                                setTimeout(function(){
                                    $('#audio-preview').show();
                                    $('#audio-preview')[0].load();
                                }, 500);
                            }
                        }
                    }
                });
            }
        });

        $('#pick-audio').click(function(event) {
            $('#search').val('');
            $('#typebox').val('audio');
            getFiles('audio','');
        });

        $('#pick-image').click(function(event) {
            $('#search').val('');
            $('#typebox').val('image');
            getFiles('image','');
        });

        $('#pick-video').click(function(event) {
            $('#search').val('');
            $('#typebox').val('video');
            getFiles('video','');
        });

        $('#searchKeyword').click(function(event) {
            var keyword=$('#search').val();
            var type=$('#typebox').val();
            getFiles(type,keyword);
        });

        function getFiles(type,search='',page=1) {
            $('#fileLibrary .box-body .row').html(
                '<img src="<?php echo $this->request->getAttribute("webroot") . 'img/loader.png'?>" '
                + 'clsss="img-responsive">');
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
                                    html += "<img height='200' style='max-width: 100%' src='"
                                        + filePath + "' alt='"+resultSet.data[i].name+"'>";
                                    break;
                                case 'audio':
                                    html += "<div class='col-sm-6 modal-elemen-top-mergin'>";
                                    html += "<audio controls>" +
                                                "<source src='"+filePath+"' type='audio/mpeg'>" +
                                            "</audio>";
                                    break;
                                case 'video':
                                    html += "<div class='col-sm-6 col-md-6'>";
                                    html += "<video height='150' controls>"+
                                                "<source src='"+filePath+"' type='video/mp4'>" +
                                            "</video>";
                                    break;
                                default:
                                    html += '';
                                    break;
                            }
                            html += "<label class='radio-inline'>"
                                + "<input type='radio' name='file_res' class='file-res' data-id='"
                                + resultSet.data[i].id+"'> "+resultSet.data[i].file_name+"</label>"+
                                    "</div>";
                        }

                        html +='<div class="box-footer clearfix col-sm-12 col-md-12">'
                            + '<ul class="pagination pagination-sm no-margin pull-right">';
                        if (resultSet.pageinfo.currentpage > 1) {
                            html += "<li><a data-page='"+resultSet.pageinfo.currentpage
                                + "' href='javascript:void(0)' class='paginatepre'>Previous</a></li>";
                        }

                        if (resultSet.pageinfo.totalpage > resultSet.pageinfo.currentpage) {
                            html += "<li><a href='javascript:void(0)' class='paginatenext' data-page='"
                                + resultSet.pageinfo.currentpage+"'>Next</a></li>";
                        }

                        fileDiv.html(html);
                    }
                }
            });
        }

        $('body').on('click', '.paginatepre', function(event) {
            var page=$(this).data('page');
            var keyword=$('#search').val();
            var type=$('#typebox').val();
            getFiles(type,keyword,page-1);
        });
        $('body').on('click', '.paginatenext', function(event) {
            var page=$(this).data('page');
            var keyword=$('#search').val();
            var type=$('#typebox').val();
            getFiles(type,keyword,page+1);
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
                                    var audioSrc =resultSet.data.FullUrl;
                                    var n = $('.audio-row:nth-last-of-type(1)').attr('id').replace('audio-row','');
                                    n++;
                                    $('#audio-group').append($('#audio-template').html());
                                    $('#audio-preview').attr('src', audioSrc);
                                    $('#audio-preview').show();
                                    $('#audio-preview')[0].load();
                                    $('#hidden-audio').val(resultSet.data.id);
                                    $('#audio-preview:first').attr('id','audio-preview'+n);
                                    $('#audio-row:first').attr('id','audio-row'+n);
                                    $('#hidden-audio:first').attr('id','hidden-audio'+n);
                                    $('#remove-audio:first').attr('onclick','removeAudio('+n+')');
                                    $('#remove-audio:first').attr('id','remove-audio'+n);
                                    $('.audio-source:first').attr('class','audio-source'+n);
                                    $('.audio-source:first').attr('class','audio-source'+n);
                                    $('.audio-source:first').attr('class','audio-source'+n);
                                    break;
                                case 'image':
                                    var imgSrc = resultSet.data.FullUrl;
                                    $('#img-preview').show();
                                    $('#img-preview').attr('src', imgSrc);
                                    $('#hidden-img').val(fileID);
                                    $('#remove-image').show();
                                    break;
                                case 'video':
                                    var videoSrc = resultSet.data.FullUrl;
                                    $('#video-preview').attr('src', videoSrc);
                                    $('#video-preview').show();
                                    $('#video-preview')[0].load();
                                    $('#hidden-video').val(fileID);
                                    $('#remove-video').show();
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                });
            }
        });
    });
<?php $this->Html->scriptEnd(); ?>
