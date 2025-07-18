<?php

use Cake\Routing\Router;

?>
<section class="content-header">
    <h1><?= __('Add/Edit Lesson') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Lessons</a></li>
        <li class="active">Add/Edit Lesson</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body addlessonpage">
            <div class="row display-wrap">
                <div class="col-sm-3 view-space-wrap">
                    <div class="view-space">
                        <div class="row">
                            <div class="col-sm-12">
                                <a type="button" class="btn btn-primary pull-left"
                                    href="<?php
                                    echo Router::url(
                                        [
                                            'controller' => 'Lessons',
                                            'action' => 'manageLesson',
                                            'prefix' => 'Admin'
                                        ],
                                        true
                                    ); ?>"
                                    title="Add new lesson from scratch">Create New Lesson</a>
                            </div>

                            <?= $this->Form->create($lesson) ?>
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <?= $this->Form->control(
                                        'name',
                                        [
                                            'div' => false,
                                            'label' => false,
                                            'class' => 'form-control',
                                            'placeholder' => 'Name'
                                        ]
                                    ) ?>
                                    <?= $this->Form->hidden('formtype', ['value' => 'lesson']) ?>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary pull-right"
                                        title="Create new lesson with the above name and add it to the list of lessons">
                                        <?php if ($lesson->id == null) {
                                            echo 'Add Lesson';
                                        } else {
                                            echo 'Update Lesson Name';
                                        }?>
                                    </button>
                                </div>
                            <?= $this->Form->end() ?>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label for="">Lessons</label>
                            </div>
                            <div class="col-sm-12 listchkbox">
                                <div class="scroll-table">
                                    <table class="table table-bordered" id="lessons-list-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th width="10%">Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($lessons as $l) { ?>
                                            <tr <?php
                                            if ($l->id == $lesson->id) {
                                                echo 'class="activerow"';
                                            }
                                            ?>>
                                                <td><?php echo $l->id; ?></td>
                                                <td>
                                                    <div class="divwrap"><?php echo $l->name; ?></div>
                                                </td>
                                                <td>
                                                    <a href="<?php
                                                                 echo $this->Url->build(
                                                                     '/admin/lessons/manage-lesson/' . $l->id
                                                                 );
                                                                ?>"
                                                    >
                                                        <i class="fa fa-pencil"></i>
                                                    </a> &nbsp;
                                                    <a href="javascript:void(0)"
                                                        onclick="deleteLesson('<?php echo $l->id; ?>')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (empty($lessons)) {
                                            echo '<tr><td colspan="3">No Lessons found.</td></tr>';
                                        } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 view-space-wrap">
                    <div class="view-space">
                        <?php if ($lesson->id) { ?>
                        <div class="row">
                            <?= $this->Form->create($newframe) ?>
                            <div class="form-group">
                                <?= $this->Form->hidden('formtype', [
                                    'value' => 'onlyframe']) ?>
                            </div>
                            <div id="framesUpdateResult" class="alert alert-success" style="visibility: hidden">
                                <i class="fa icon fa-check"></i>
                                <span>&nbsp;</span>
                            </div>
                            <!-- This is here to give JS access to active frame ID -->
                            <?= $this->Form->hidden('activeFrameId', [
                                'id' => "activeFrameId", 'value' => $frame->id ?? -1]) ?>
                            <?= $this->Form->hidden('sortingMethod', [
                                'id' => "sortingMethod",
                                'value' => $this->Url->build('/admin/lessons/sorting/')]) ?>
                            <?= $this->Form->hidden('reorderAndRenameMethod', [
                                'id' => "reorderAndRenameMethod",
                                'value' => Router::url([
                                    'controller' => 'Lessons',
                                    'action' => 'reorderAndRenameFrames',
                                    'prefix' => 'Admin'])]) ?>
                            <div class="col-sm-12 form-group">
                                <button type="submit" class="btn btn-primary pull-right">Add New Frame</button>
                            </div>
                            <?= $this->Form->end() ?>
                        </div>
                        <?php } ?>
                        <div class="col-sm-12">
                                <label for="">Frame</label>
                            </div>
                        <div class="col-sm-12">
                            <table class="table table-bordered" id="frames-list-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Sort</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($lesson->lessonframes == null || empty($lesson->lessonframes)) {
                                        echo '<tr><td colspan="4">No Frames found.</td></tr>';
                                    } else {
                                        $i = 1;
                                        //print_r($lesson->lessonframes);

                                        foreach ($framelist as $framedetails) { ?>
                                            <tr <?php
                                            if ($framedetails->id == $frame->id) {
                                                echo 'class="activerow"';
                                            }
                                            echo 'id="' . $framedetails->id . '"';
                                            ?>>
                                                <td><?php echo $framedetails->id; ?></td>
                                                <td><?php echo $framedetails->name; ?></td>
                                                <td>
                                                <?php if ($i != count($lesson->lessonframes)) { ?>
                                                    <a href="<?php
                                                                 echo $this->Url->build(
                                                                     '/admin/lessons/sorting/'
                                                                     . $framedetails->id . '/down/' . $i
                                                                 );
                                                                ?>"
                                                    >
                                                        <i class="fa fa-chevron-down down"></i>
                                                    </a>
                                                    <?php
                                                }
                                                if ($i != 1) { ?>
                                                    <a href="<?php
                                                                 echo $this->Url->build(
                                                                     '/admin/lessons/sorting/'
                                                                     . $framedetails->id . '/up/' . $i
                                                                 );
                                                                ?>"
                                                    >
                                                        <i class="fa fa-chevron-up up"></i>
                                                    </a>
                                                    <?php
                                                } ?>

                                                </td>
                                                <td>
                                                    <a href="<?php
                                                                 echo $this->Url->build(
                                                                     '/admin/lessons/manage-lesson/'
                                                                     . $framedetails->lesson_id
                                                                     . '/' . $framedetails->id
                                                                 );
                                                                ?>"
                                                    >
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    &nbsp;
                                                    <?php
                                                    echo $this->Html->link(
                                                        '<i class="fa fa-trash"></i>',
                                                        '/admin/lessons/deleteframe/' . $framedetails->id,
                                                        array('escape' => false,
                                                            'confirm' => 'Are you sure you want to '
                                                                . 'delete this lesson frame?'
                                                        )
                                                    ); ?>
                                                </td>
                                            </tr>

                                            <?php
                                            $i++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!--                        <div class="col-sm-4 move-up-down text-center">
                                                    <button type="button" class="btn btn-primary">
                                                        <i class="fa fa-chevron-up"></i>
                                                        Move Up
                                                    </button>
                                                    <button type="button" class="btn btn-primary">
                                                        <i class="fa fa-chevron-down"></i>
                                                        Move Down
                                                    </button>
                                                </div>-->
                    </div>
                </div>
                <?php if (isset($frame->id)) {?>
                <div class="col-sm-4 view-space-wrap">
<!--                    <pre>-->
                    <?= $this->Form->create($frame, ['id' => 'frameForm']);?>
                    <?php
                    $blocks = array();
                    if (!empty($frame['lesson_frame_blocks'])) {
                        foreach ($frame['lesson_frame_blocks'] as $b) {
                            $blocks[$b['block_no']] = $b;
                        }
                    }
                   //print_r($submitted_block);

                    ?>
<!--                    </pre>-->
                    <div class="view-space display-block">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-3"><label for="name">Name</label></div>
                                <div class="col-sm-9">
                                    <?= $this->Form->control('name', [
                                        'id' => 'formID', 'div' => false, 'label' => false,
                                        'class' => 'form-control', 'placeholder' => 'Name',
                                        'readonly' => true]) ?>
                                    <?= $this->Form->hidden('formtype', ['value' => 'frame']) ?>
                                    <?= $this->Form->hidden('lesson_id', ['value' => $lesson->id]) ?>
                                    <?= $this->Form->control('id') ?>
                                </div>
                            </div>
                        </div>

                        <?php
//                        print_r($frame);
//                        $frame
                        ?>
                        <div class="row">
                            <label class="col-sm-12" for="audio">Audio</label>
                            <div class="col-sm-5">
                                <button type="button" class="btn btn-default openFrameAudioModal"
                                    data-block="frameaudio">Choose Audio</button>
                            </div>
                            <div class="col-sm-7">
                                <?= $this->Form->hidden('audio_id', [
                                    'id' => "hidden-audio",
                                    'value' => $frame->audio_id ?? '']) ?>
                                <audio style="max-width: 95%;" controls id="audio-preview"
                                <?php if ($frameFile == '') {
                                    echo 'style="display:none;"';
                                }?>>
                                    <?php $src = $frameFile; ?>
                                    <source class="audio-source"
                                        src="<?php echo $frameFile != '' ? $src : ''; ?>" type="audio/mpeg">
                                    <source class="audio-source"
                                        src="<?php echo $frameFile != '' ? $src : ''; ?>" type="audio/ogg">
                                    <source class="audio-source"
                                        src="<?php echo $frameFile != '' ? $src : ''; ?>" type="audio/wav">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-12" for="video">Or Upload Audio File</label>
                                <div class="col-sm-5">
                                    <button type="button" class="btn btn-default upload-new-File"
                                        data-type="audio" data-uploadpos="frame">Upload Audio File</button>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-3"><label for="duration">Duration</label></div>
                                <div class="col-sm-9">
                                    <?= $this->Form->text('duration', [
                                        'type' => 'number', 'id' => 'audio_duration',
                                        'min' => "0", 'class' => 'form-control',
                                        'placeholder' => 'in seconds', 'value' => $frame->duration]) ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="blocks">Number of Blocks</label>
                            <?= $this->Form->select(
                                'number_of_block',
                                [
                                "1" => '1',"2" => '2',"3" => '3'],
                                ['class' => "form-control",'id' => 'numberOfBlock']
                            );?>
                        </div>




                       <div class="form-group" id="blockRadioInput"></div>
                       <div  id="blockContentType"></div>
                        <div class="form-group radiobtn">
                            <label for="frame-preview">Frame Preview</label><br>
                            <?= $this->Form->radio(
                                'frame_preview',
                                [
                                    'portrait' => 'Portrait',
                                    'landscape' => 'Landscape'
                                ]
                            );?>
                        </div>

                       <div class="form-group text-right">
                           <a class="btn btn-primary pull-left previewFrame">Preview</a>
                           <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
                <?php }?>
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
                <div class="row">
                    <div class='col-sm-6 col-md-6'>
                        <div class="form-group">
                            <span class="input-group">
                                <input type="text" autocomplete="off"
                                    class="form-control" placeholder="Keyword" id="search">
                                <input type="hidden" id="typebox">
                                <input type="hidden" id="format">
                                <input type="hidden" id="blockno">
                                <input type="hidden" id="SelectFileId">
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
                <button type="button" class="btn btn-primary"
                    data-dismiss="modal" id="select-file-btn">Select File</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadFile">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">'
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Upload New File</h4>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, ['type' => 'file', 'id' => 'upload_file_form']) ?>
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
                                <?= $this->Form->hidden('uploadtype', ['id' => 'uploadtype']) ?>
                                <?= $this->Form->hidden('newblock', ['id' => 'newblock']) ?>
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
    </div>
</div>

<div class="modal fade" id="FramePreviewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Preview</h4>
            </div>
            <div class="modal-body">
                <div class="box-body" id="previewmodalhtml">
                    <img src=''>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lessonDeleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Lesson Delete Warning</h4>
            </div>
            <div class="modal-body">
                <div class="box-body" id="lessonDeleteDiv">

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->Html->script('/js/tablednd.js', ['block' => 'scriptBottom']);?>

<?php
echo $this->Html->css('AdminLTE./bower_components/datatables.net-bs/css/dataTables.bootstrap.min');
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net/js/jquery.dataTables.min',
    ['block' => 'script']
);
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net-bs/js/dataTables.bootstrap.min',
    ['block' => 'script']
);
?>
<?php //if (isset($frame->id)) { echo $this->Html->script('lessonScript', ['block' => 'scriptBottom']); } ?>
<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    function getAjaxUrl(urlvalue) {
        return '<?php echo Router::url('/');?>'+urlvalue;
    }
<?php $this->Html->scriptEnd(); ?>
<?php  echo $this->Html->script('lessonScript', ['block' => 'scriptBottom']); ?>
<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
$(function () { <?php
if (!empty($lessons)) { ?>
        $('#lessons-list-table').DataTable({
            'searching'   : true,
            'paging'      : false,
            'lengthChange': false,
            'ordering'    : true,
            'info'        : false,
            'autoWidth'   : false,
            'scrollY'     : "200px",
            'dom'         : '<"top"i>frt<"bottom"lp><"clear">',
            'order'       : [[1, 'asc']]
        });

        setTimeout(function function_name(argument) {
            $('#lessons-list-table tbody tr').each(function(index, el) {
                if ($(this).hasClass('activerow')) {
                var top =($(".activerow").offset().top - $(window).height()+350);
                if (top<0) {top =0;}
                    $('.dataTables_scrollBody').animate({
                        scrollTop: top
                    }, 1000);
                }
            });
        }, 500); <?php
} ?> <?php
if (isset($frame['number_of_block'])) {?>
        generateBlockContentTypeInput(<?php echo $frame['number_of_block'];?>); <?php
} else { ?>
        generateBlockContentTypeInput(1); <?php
} ?>
        eventBinding();
        setEditValue();
});

/*for frame js work*/
/* for content type Card */
function generateCardDetails(blocknumber) {
    var cardhtml = `
        <div class="form-group">
            <label for="content-type">Select Card</label>
            <select class="form-control cardSelect" id="card_id${blocknumber}" name="block_card_id${blocknumber}"
                data-placeholder="Select Card">
                <?php foreach ($cards as $key => $val) { ?>
                    <option value="<?php echo $key?>">
                        <?php echo str_replace(
                            array('\'', '"', ',' , ';'),
                            '',
                            strip_tags($val)
                        ); ?>
                    </option>
                    <?php
                }?>
            </select>
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="is_card_lakota${blocknumber}" id="card_lakota${blocknumber}">
                    <?php echo $languageName;?>
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="is_card_english${blocknumber}" id="is_card_english${blocknumber}">
                    English
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="is_card_audio${blocknumber}" id="is_card_audio${blocknumber}">
                    Audio
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="is_card_video${blocknumber}" id="is_card_video${blocknumber}">
                    Video
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="is_card_image${blocknumber}" id="is_card_image${blocknumber}">
                    Image
                </label>
            </div>
        </div>`;

        return cardhtml;
}

function setEditValue() { <?php
if (!empty($submitted_block)) {
    $blocks = $submitted_block;
}
if (!empty($blocks)) {
    foreach ($blocks as $key => $val) {
        if (isset($val['type'])) { ?>
                $('select[name="block<?php echo $key?>type"]').val('<?php echo $val['type']?>'); <?php
                if (!empty($submitted_block)) { ?>
                    generateBlockDetailsHtml(<?php echo $key?>,'<?php echo $val['type']?>',''); <?php
                } else { ?>
                    generateBlockDetailsHtml(
                        <?php echo $key?>,
                        '<?php echo $val['type']?>',
                        '<?php echo $val['id']?>'
                    ); <?php
                } ?> <?php
if ($val['type'] == 'html') { ?>
                    <?php
                    $Htmlval = '';
                    $Htmlval = str_replace(array("\r", "\n" ,), '@@', $val['custom_html']);
                    $Htmlval = preg_replace("/'/", "\&#39;", $Htmlval);
                    ?>
                    var htmlStr='<?php echo $Htmlval;?>'

                    htmlStr= htmlStr.replace(new RegExp('@@@@', 'g'), '@');
                    htmlStr= htmlStr.replace(new RegExp('@', 'g'), '\n');
                    htmlStr= htmlStr.replace(new RegExp("\&#39;", 'g'), "'");
                    $('textarea[name="custom_html<?php echo $key?>"]').text(htmlStr);
                    <?php
} ?>
                <?php
                if ($val['type'] == 'audio') { ?>
                    var audioSrc = "<?= $val['AudioUrl']; ?>";
                    $("#block-audio-preview<?php echo $key?>").attr('src', audioSrc);
                    $("#block-audio-preview<?php echo $key?>").show();
                    $("#block-audio-preview<?php echo $key?>")[0].load();
                    $("#block_audio_id<?php echo $key?>").val('<?php echo $val['audio_id']?>'); <?php
                } ?> <?php
if ($val['type'] == 'image') { ?>
                    var imgSrc = "<?= $val['ImageUrl'] ?>";
                    $("#img-preview<?php echo $key?>").show();
                    $("#img-preview<?php echo $key?>").attr('src', imgSrc);
                    $("#hidden-img<?php echo $key?>").val('<?php echo $val['image_id']?>'); <?php
} ?> <?php
if ($val['type'] == 'video') { ?>
                    var videoSrc = "<?= $val['VideoUrl'] ?>";
                    $(".video-src<?php echo $key?>").attr('src', videoSrc);
                    $("#video-preview<?php echo $key?>").show();
                    $("#video-preview<?php echo $key?>")[0].load();
                    $("#hidden-video<?php echo $key?>").val('<?php echo $val['video_id']?>'); <?php
} ?> <?php
if ($val['type'] == 'card') { ?>
                    $('select[name="block_card_id<?php echo $key?>"]').val('<?php echo $val['card_id']?>'); <?php
                    if (isset($val['is_card_lakota']) && $val['is_card_lakota'] == 'Y') { ?>
                        $('input[name="is_card_lakota<?php echo $key?>"]').prop('checked', true);<?php
                    } ?> <?php
if (isset($val['is_card_english']) && $val['is_card_english'] == 'Y') { ?>
                        $('input[name="is_card_english<?php echo $key?>"]').prop('checked', true);<?php
} ?> <?php
if (isset($val['is_card_audio']) && $val['is_card_audio'] == 'Y') { ?>
                        $('input[name="is_card_audio<?php echo $key?>"]').prop('checked', true);<?php
} ?> <?php
if (isset($val['is_card_video']) && $val['is_card_video'] == 'Y') { ?>
                        $('input[name="is_card_video<?php echo $key?>"]').prop('checked', true);<?php
} ?> <?php
if (isset($val['is_card_image']) && $val['is_card_image'] == 'Y') { ?>
                        $('input[name="is_card_image<?php echo $key?>"]').prop('checked', true);<?php
} ?> <?php
}?> <?php
        }
    }
} ?>
}
<?php $this->Html->scriptEnd(); ?>
