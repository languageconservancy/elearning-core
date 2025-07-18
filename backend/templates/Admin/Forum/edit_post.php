<?php

//use Cake\View\Helper;
//use Cake\I18n\Time;

?>
<section class="content-header">
    <h1><?= __('Add/Edit Post') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Forum</a></li>
        <li class="active">Edit Post</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($forumPost) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Title</label>
                    <?= $this->Form->control(
                        'title',
                        [
                            'label' => false,
                            'class' => 'form-control',
                            'placeholder' => "Edit Title",
                            'autocomplete' => 'off'
                        ]
                    ) ?>
                    <?= $this->Form->control('id') ?>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Sub Title</label>
                    <?= $this->Form->control(
                        'content',
                        [
                            'label' => false,
                            'class' => 'form-control',
                            'placeholder' => "Edit Content",
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
