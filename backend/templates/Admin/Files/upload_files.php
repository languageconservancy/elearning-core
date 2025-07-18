<?php

use Cake\Routing\Router;

?>

<section class="content-header">
    <h1><?= __('Upload Files') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
        <li><a href="#">Files</a></li>
        <li class="active">Upload files</li>
    </ol>
</section>

<section class="content">
    <p>Use this form to upload one or more files to AWS S3, where curriculum
        images, audio, and video are stored. For each file, an entry in the
        Files table will be created to allow the files to be used in content creation.<br/>
    </p>
    <div class="box box-primary">
        <?= $this->Form->create(null, ['type' => 'file', 'id' => 'files-upload-form']) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">

                    <p>How do you want the <b>Name</b> and <b>Description</b> fields to be set for each file?</p>
                    <div class="form-group">
                        <?= $this->Form->radio(
                            'filenames-method',
                            ['filenames' => 'Use filenames',
                            'constants' => 'Use constants with numbered suffix'],
                            ['default' => 'filenames']
                        ); ?>
                    </div>

                    <div id="file-prefix-fields" class="d-none" style="display: none;">
                        <div class="form-group">
                            <label for="name">Name Prefix (results in prefix_1, prefix_2, ...)</label>
                            <?= $this->Form->control('name', [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Name Prefix"]) ?>
                        </div>
                        <div class="form-group">
                            <label for="description">Description Prefix (results in prefix_1, prefix_2, ...)</label>
                            <?= $this->Form->control('description', [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Description Prefix"]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Upload Files</label>
                        <?= $this->Form->control('files.', [
                            'label' => false,
                            'type' => 'file',
                            'enctype' => 'multipart/form-data',
                            'multiple']) ?>
                    </div>
                    <div id="file-statuses" class="box-body table-responsive" hidden>
                    <table id="file-statuses-table" class="table table-bordered">
                        <tbody>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary hidden">Upload Files</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    function getAjaxUrl(urlValue){
        return '<?php echo Router::url('/');?>'+urlValue;
    }
<?php $this->Html->scriptEnd(); ?>
<?php
echo $this->Html->script('uploadFiles', ['block' => 'scriptBottom']);
?>
