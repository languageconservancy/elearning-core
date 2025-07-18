<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Excel Bulk Lesson Upload') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Lessons</a></li>
        <li class="active">Bulk Upload</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($File, ['type' => 'file']) ?>

        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?php if (!isset($File['id'])) { ?>
                        <div class="form-group">
                            <label>Upload Lesson List (Excel)</label>
                            <?=
                              $this->Form->control('file', [
                                'label' => false,
                                'type' => 'file',
                                'accept' => '.xls,.xlsx',
                                'title' => 'Please select a Lesson sheet file in Excel format (.xlsx, .xls)',
                              ])
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>

        </div>

        <?= $this->Form->end() ?>
    </div>
</section>
