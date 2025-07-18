<?php

use Cake\Routing\Router;

//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Add/Edit School') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">School</a></li>
        <li class="active">Add/Edit School</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($school) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">School Name</label>
                        <?= $this->Form->control(
                            'name',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Name",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">

                        <label>Grades</label>
                        <?= $this->Form->control(
                            'grade_low',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Low Grade",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                        <?= $this->Form->control(
                            'grade_high',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "High Grade",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="form-group">
                        <label>Select image for school</label>
                        <button type="button" class="btn btn-default"
                            style="margin: 3px 0; display: block;" id="select-school-image"
                            data-toggle="modal" data-target="#fileLibrary">Select Image
                        </button>
                        <span class="text-center">or</span>
                        <button type="button" class="btn btn-default"
                            style="margin: 3px 0; display: block;" data-toggle="modal"
                            data-target="#uploadFile" onclick="imageUpload('school')">Choose Image
                        </button>
                    </div>
                </div>
                <div class="col-sm-3">
                    <img id="school-img-preview"
                        src="<?= isset($school['ImageFile']) ? $school['FullImageUrl'] : '' ?>"
                        alt="" height="100">
                    <?= $this->Form->hidden(
                        'image_id',
                        [
                            'value' => !empty($school['ImageFile']) ? $school['ImageFile']['id'] : null,
                            'id' => 'school-img'
                        ]
                    ) ?>
                </div>
            </div>

        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
    <?= $this->Form->end() ?>
</section>

<div class="modal fade" id="uploadFile">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Upload New File</h4>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, ['type' => 'file', 'id' => 'upload_file_form']) ?>
                <?= $this->Form->hidden('uploadtype', ['value' => 'image', 'id' => 'uploadtype']) ?>
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
                <button type="button" class="btn btn-primary"
                    id="uploadFileFormSubmit">Upload File</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="fileLibrary">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">File Library</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class='col-sm-6 col-md-6'>
                        <div class="form-group">
                            <span class="input-group">
                                <?= $this->Form->control(
                                    'search',
                                    [
                                        'id' => 'search',
                                        'label' => false,
                                        'class' => 'form-control',
                                        'placeholder' => 'Keyword',
                                        'autocomplete' => 'off',
                                        'type' => 'text'
                                    ]
                                ); ?>
                                <?= $this->Form->hidden(
                                    'typebox',
                                    ['id' => 'typebox']
                                ); ?>
                                <?= $this->Form->hidden(
                                    'format',
                                    ['id' => 'format']
                                ); ?>
                                <span class="input-group-addon" id="searchKeyword">Search</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row text-center">
                        <img src='<?php echo $this->request->getAttribute("webroot") . 'img/loader.png' ?>'
                            clsss="img-responsive">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    data-dismiss="modal" id="select-file-btn">Select File
                </button>
            </div>
        </div>
    </div>
</div>

<?php
echo $this->Html->script('AdminLTE./bower_components/jquery/dist/jquery.min');
echo $this->Html->css('AdminLTE./bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min');
echo $this->Html->script('AdminLTE./bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min');
$this->Html->scriptStart(['block' => 'scriptBottom']); ?>

function getAjaxUrl(urlvalue){
    return '<?php echo Router::url('/'); ?>'+urlvalue;
}
<?php
if (isset($user->id) && isset($user->dob)) { ?>
    $(function () {
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        }).datepicker("update", "<?php echo $user->dob->format('Y-m-d'); ?>");
    });
    <?php
} else { ?>
    $(function () {
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
    <?php
}
$this->Html->scriptEnd();
echo $this->Html->script('schoolScript', ['block' => 'scriptBottom']);
?>
