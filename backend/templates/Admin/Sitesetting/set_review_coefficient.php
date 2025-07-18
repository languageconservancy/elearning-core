<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Set Review Coefficient') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="Javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="Javascript:void(0)">Site Setting</a></li>
        <li class="active">Set Review Coefficient</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="box-body">
            <?php foreach ($ReviewVars as $var) { ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo $var['key']?></label>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <?= $this->Form->control('value.', ['label' => false,'value' => $var['value']]) ?>
                        <?= $this->Form->control('id.', ['label' => false,'value' => $var['id'],'type' => 'hidden']) ?>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>
