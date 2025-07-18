<?php

//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Add/Edit Content') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">User</a></li>
        <li class="active">Add/Edit Content</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($contents) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">


                    <div class="form-group">
                        <label>Title</label>
                        <?= $this->Form->control(
                            'title',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Title",
                                'autocomplete' => 'off',
                                'type' => 'text'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label>Keyword</label>
                        <?= $this->Form->control(
                            'keyword',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Keyword",
                                'autocomplete' => 'off',
                                'type' => 'text'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <?= $this->Form->control(
                            'text',
                            [
                                'label' => false,
                                'id' => 'cmscontent',
                                'class' => 'form-control',
                                'placeholder' => "Enter content",
                                'autocomplete' => 'off',
                                'type' => 'textarea'
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
    </div>
</section>

<?php
echo $this->Html->css('/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css');
echo $this->Html->script(
    '/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
    ['block' => 'scriptBottom']
);
?>
<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>
$(function () {
    $('#cmscontent').wysihtml5({
        toolbar: {
            "image": false,
            "font-styles": true,
            "blockquote": false,
            "html": true,
            "lists": false,
            "link": false,
        }
    })
});
<?php
$this->Html->scriptEnd();
?>
