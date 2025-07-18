<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Upload Login Page Image') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Site Setting</a></li>
        <li class="active">Upload Login Page Image</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Upload File</label>
                        <?= $this->Form->control('file.', ['label' => false, 'type' => 'file']) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php
                    //print_r($val);


//                    echo $val->Sitesetting->value;
//                    echo $val->value;
//                    echo $val['value'];
                    echo $this->Html->image('UploadedFile/' . $val['value'], ['alt' => 'image', "width" => 'auto']);


                    ?>

                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>
