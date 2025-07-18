<?php

//use Cake\View\Helper;
//use Cake\I18n\Time;
use Cake\Routing\Router;

?>
<section class="content-header">
    <h1><?= __('Add/Edit Forum') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">User</a></li>
        <li class="active">Add/Edit Forum</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
         <?= $this->Form->create($forum) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Path</label>
                        <?= $this->Form->control(
                            'path_id',
                            [
                                'options' => $learningpaths,
                                'empty' => 'Select Path',
                                'label' => false,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    <?= $this->Form->control('id'); ?>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Unit</label>
                        <?= $this->Form->control(
                            'unit_id',
                            [
                                'empty' => 'Select Units',
                                'label' => false,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Sub Title</label>
                        <?= $this->Form->control(
                            'subtitle',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Sub Title",
                                'autocomplete' => 'off',
                                'type' => 'textarea'
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Level</label>
                        <?= $this->Form->control(
                            'level_id',
                            [
                                'empty' => 'Select Level',
                                'label' => false,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Title</label>
                        <?= $this->Form->control(
                            'title',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Title",
                                'autocomplete' => 'off'
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
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>
$(function () {

$("#path-id").change(function () {
    var pathevalue=$(this).val();
    getLevel(pathevalue);
});
$("#level-id").change(function () {
    var levelId=$(this).val();
    getUnit(levelId);
});
<?php if ($forum->path_id) {
    ?> getLevel(<?php echo $forum->path_id?>); <?php
}?>
<?php if ($forum->level_id) {
    ?> getUnit(<?php echo $forum->level_id?>); <?php
}?>


    function getLevel(pathId){
        var data={pathId:pathId};
        $.ajax({
            type: "POST",
            async: false,
            url: '<?php echo Router::url(
                [
                    'controller' => 'Forum',
                    'action' => 'getLevelsForPath',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>',
            data: data,
            success: function (res) {
                $("#level-id").html(res);
                <?php if ($forum->level_id) {
                    ?>$("#level-id").val(<?php echo $forum->level_id?>); <?php
                }?>
            }
        });
    }
    function getUnit(levelId){
      var data={levelId:levelId};
        $.ajax({
            type: "POST",
            url: '<?php echo Router::url(
                [
                    'controller' => 'Forum',
                    'action' => 'getUnitForLevel',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>',
            data: data,
            success: function (res) {
                $("#unit-id").html(res);
                <?php if ($forum->unit_id) {
                    ?>$("#unit-id").val(<?php echo $forum->unit_id?>); <?php
                }?>
            }
        });
    }
});
<?php
$this->Html->scriptEnd();
?>
