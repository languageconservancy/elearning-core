<?php

use Cake\Routing\Router;

//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Add/Edit Learning Path') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Learning Path</a></li>
        <li class="active">Add/Edit Learning Path</li>
    </ol>
</section>
<style>

</style>

<section class="content">
    <div class="box box-primary" style="margin-bottom: 0px;">
        <div class="box-body addlessonpage">
            <div class="box-header with-border">
                <h3 class="box-title">Learning Paths</h3>
            </div>
            <div class="row display-wrap">
                <input type="hidden" name="image_type" id="image_type">
                <!-- PATH CREATE -->
                <div class="col-sm-12 view-space-wrap no-padding">
                    <?= $this->Form->create($path, ['type' => 'file']) ?>
                    <?= $this->Form->hidden('form_type', ['value' => 'path']) ?>
                    <div class="row">
                        <div class="col-sm-4 view-space-wrap">
                            <?= $this->Form->control(
                                'learningpath_id',
                                [
                                    'options' => $allPaths,
                                    'empty' => 'Select',
                                    'value' => $path->id,
                                    'label' => 'Learning Path',
                                    'class' => 'form-control'
                                ]
                            ) ?>
                        </div>
                        <div class="col-sm-4">
                            <a type="button" class="btn btn-primary pull-left" href="<?= Router::url(
                                [
                                    'controller' => 'LearningPath',
                                    'action' => 'managePaths',
                                    'prefix' => 'Admin'
                                ],
                                true
                            ); ?>" >Add New Path</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <?= $this->Form->control(
                                'label',
                                [
                                    'div' => false,
                                    'value' => $path->label,
                                    'label' => 'Name',
                                    'class' => 'form-control',
                                    'placeholder' => 'Name',
                                    'required' => false
                                ]
                            ) ?>
                            <div class="col-sm-12">
                                <label for="description">Admin Access &nbsp;&nbsp; </label>

                            <?php

                                $fl = $path->admin_access ? $path->admin_access : 1;
                            echo $this->Form->checkbox('admin_access', ['label' => false, 'value' => $fl]);

                            ?>

                            </div>

                            <div class="col-sm-12">
                                <label for="description">All User Access &nbsp;&nbsp; </label>
                        <?php
                        $fl = $path->admin_access ? $path->admin_access : 1;
                        echo $this->Form->checkbox('user_access', ['label' => false,'value' => $fl]);
                        ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="description">Description</label>
                            <?= $this->Form->textarea(
                                'description',
                                [
                                    'div' => false,
                                    'value' => $path->description,
                                    'label' => false,
                                    'rows' => 3,
                                    'class' => 'form-control',
                                    'placeholder' => 'Description',
                                    'required' => false
                                ]
                            ) ?>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-default"
                                style="margin: 3px 0; display: block;" id="select-path-image"
                                data-toggle="modal" data-target="#fileLibrary">Select Image</button>
                            <span class="text-center">or</span>
                            <button type="button" class="btn btn-default"
                                style="margin: 3px 0; display: block;" data-toggle="modal"
                                data-target="#uploadFile" onclick="imageUpload('path')">Choose Image</button>
                        </div>
                        <div class="col-sm-3">
                            <img id="path-img-preview"
                                src="<?=$path->image ? $path->image->FullUrl : ''?>"
                                alt="" height="100">
                            <?= $this->Form->hidden('image_id', ['value' => $path->image_id, 'id' => 'path-img']) ?>
                        </div>
                        <div class="col-sm-4 view-space-wrap">
                            <button type="submit" class="btn btn-primary mar-top-25">Save Path</button>
                            <?php if ($path->id && $path->id != 1) : ?>
                                <?= $this->Html->link(
                                    'Delete Path',
                                    '/admin/learning-path/delete-path/' . $path->id,
                                    [
                                        'escape' => false,
                                        'confirm' => 'Are you sure you want to delete this path?',
                                        'class' => 'btn btn-danger mar-top-25'
                                    ]
                                ); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($path->id) : ?>
<section class="content">
    <div class="box box-primary" style="margin-bottom: 0px;">
        <div class="box-body addlessonpage">
            <div class="box-header with-border">
                <h3 class="box-title">Levels (in <i><?php echo $path->label; ?></i> path)</h3>
            </div>
            <div class="row display-wrap">
                <!-- Level selector -->
                <div class="col-sm-5 view-space-wrap mar-top-25">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="hidden" id="level-id" value="<?= $level->id ? $level->id : ''; ?>">
                            <label for="groups">Levels</label>
                            <table class="table table-responsive">
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
                                    if (!empty($allLevels)) : ?>
                                        <?php
                                        foreach ($allLevels as $levelIndex => $curLevel) : ?>
                                    <tr class="<?= ($curLevel['id'] == $level->id) ? 'selected' : ''; ?>">
                                        <td><?= $curLevel['id']; ?></td>
                                        <td><?= $curLevel['name']; ?></td>
                                        <td>
                                            <?php if ($levelIndex < count($allLevels) - 1) : ?>
                                            <a href="<?php
                                                         echo $this->Url->build(
                                                             '/admin/learning-path/move-level/'
                                                             . $path->id
                                                             . '/' . $curLevel['id']
                                                             . '/down/'
                                                             . ($levelIndex + 1)
                                                         )
                                                        ?>"
                                            >
                                                <i class="fa fa-chevron-down down"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($levelIndex > 0) : ?>
                                            <a
                                                href="<?php
                                                          echo $this->Url->build(
                                                              '/admin/learning-path/move-level/'
                                                              . $path->id
                                                              . '/' . $curLevel['id']
                                                              . '/up/'
                                                              . ($levelIndex + 1)
                                                          )
                                                        ?>"
                                            >
                                                <i class="fa fa-chevron-up up"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $this->Html->link(
                                                '<i class="fa fa-pencil"></i>',
                                                '/admin/learning-path/manage-paths/'
                                                    . $path->id
                                                    . '/' . $curLevel['id'],
                                                array('escape' => false)
                                            ); ?>
                                            &nbsp;
                                            <?= $this->Html->link(
                                                '<i class="fa fa-trash"></i>',
                                                '/admin/learning-path/deleteLevel/'
                                                    . $path->id . '/'
                                                    . $curLevel['id'],
                                                [
                                                    'escape' => false,
                                                    'confirm' => "Are you sure you want to delete level '"
                                                        . $curLevel['name'] . "'?"
                                                ]
                                            ); ?>
                                        </td>
                                     </tr>
                                        <?php endforeach ?>
                                    <?php else : ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No levels added to this path yet.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 view-space-wrap no-padding mar-top-25">
                    <?= $this->Form->create($level, ['type' => 'file', 'id' => "level_form"]) ?>
                    <?= $this->Form->hidden('form_type', ['value' => 'level']) ?>
                    <?= $this->Form->hidden('learningpath_id', ['value' => $path->id]) ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <a type="button" class="btn btn-primary pull-left"
                                href="<?=
                                          Router::url(
                                              [
                                                  'controller' => 'LearningPath',
                                                  'action' => 'managePaths',
                                                  'prefix' => 'Admin',
                                                  $path->id
                                              ],
                                              true
                                          );
                                        ?>"
                            >Add New Level</a>
                        </div>
                    </div>
                    <div class="row mar-top-25">
                        <div class="col-sm-3">
                            <?= $this->Form->control(
                                'name',
                                [
                                    'div' => false,
                                    'value' => $level->name,
                                    'label' => 'Name',
                                    'class' => 'form-control',
                                    'placeholder' => 'Name',
                                    'required' => false
                                ]
                            ) ?>
                        </div>
                        <div class="col-sm-4">
                            <label for="description">Description</label>
                            <?= $this->Form->textarea(
                                'description',
                                [
                                    'div' => false,
                                    'value' => $level->description,
                                    'label' => false,
                                    'rows' => 3,
                                    'class' => 'form-control',
                                    'placeholder' => 'Description',
                                    'required' => false
                                ]
                            ) ?>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-default"
                                style="margin: 3px 0; display: block;" id="select-level-image"
                                data-toggle="modal" data-target="#fileLibrary">Select Image</button>
                            <span class="text-center">or</span>
                            <button type="button" class="btn btn-default" style="margin: 3px 0; display: block;"
                                data-toggle="modal" data-target="#uploadFile"
                                onclick="imageUpload('level')">Choose Image</button>
                        </div>
                        <div class="col-sm-3">
                            <img id="level-img-preview" src="<?=$level->image ? $level->image->FullUrl : ''?>"
                                alt="" height="100">
                            <?= $this->Form->hidden('image_id', ['value' => $level->image_id, 'id' => 'level-img']) ?>
                        </div>
                        <div class="col-sm-4 view-space-wrap">
                            <button type="button" class="btn btn-primary mar-top-25"
                                id="save_level_btn">Save Level</button>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
                <!-- END LEVEL CREATE -->
                <!-- UNIT CREATE -->
                <?php if ($level->id) : ?>
<section class="content">
    <div class="box box-primary">
        <div class="box-body addlessonpage">
            <div class="box-header with-border">
                <h3 class="box-title">Units (in <i><?php echo $level->name; ?></i> level)</h3>
            </div>
            <div class="row display-wrap">
                <div class="col-sm-12 view-space-wrap no-padding mar-top-25">
                    <?= $this->Form->create($unit) ?>
                    <?= $this->Form->hidden('form_type', ['value' => 'unit']) ?>
                    <?= $this->Form->hidden('learningpath_id', ['value' => $path->id]) ?>
                    <?= $this->Form->hidden('level_id', ['value' => $level->id]) ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <a type="button" class="btn btn-primary pull-left"
                                href="<?=
                                          Router::url(
                                              [
                                                  'controller' => 'LearningPath',
                                                  'action' => 'managePaths',
                                                  'prefix' => 'Admin',
                                                  $path->id,
                                                  $level->id
                                              ],
                                              true
                                          );
                                        ?>"
                            >Add New Unit</a>
                        </div>
                    </div>
                    <div class="row mar-top-25">
                        <div class="col-sm-3">
                            <?= $this->Form->control(
                                'name',
                                [
                                    'div' => false,
                                    'value' => $unit->name,
                                    'label' => 'Name',
                                    'class' => 'form-control',
                                    'placeholder' => 'Name',
                                    'required' => false
                                ]
                            ) ?>
                        </div>
                        <div class="col-sm-4">
                            <label for="description">Description</label>
                            <?= $this->Form->textarea(
                                'description',
                                [
                                    'div' => false,
                                    'value' => $unit->description,
                                    'label' => false,
                                    'rows' => 3,
                                    'class' => 'form-control',
                                    'placeholder' => 'Description',
                                    'required' => false
                                ]
                            ) ?>
                        </div>

                        <div class="col-sm-4 view-space-wrap">
                            <button type="submit" class="btn btn-primary mar-top-25">Save Unit</button>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>

                <div class="col-sm-12 view-space-wrap mar-top-25">
                    <div class="row">
                        <div class="col-sm-4">
                            <input type="hidden" id="unit-id" value="<?= $unit->id ? $unit->id : ''; ?>">
                            <label for="groups">Units</label>
                            <table class="table table-responsive" id="units-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Optional</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($allUnits)): ?>
                                        <?php
                                        foreach ($allUnits as $unitKey => $singleUnit) : ?>
                                    <tr id="<?= $singleUnit->id; ?>" class="<?= ($singleUnit->id == $unit->id) ? 'selected' : ''; ?>" data-id="<?= $singleUnit->id; ?>">
                                        <td><?= $singleUnit->id; ?></td>
                                        <td><?= $singleUnit->name; ?></td>
                                        <td><?php
                                        if ($singleUnit->_joinData->optional) {?>
                                            <span style='font-weight:bold; text-decoration:none'>Yes</span> /
                                            <a href="<?php
                                                         echo $this->Url->build(
                                                             '/admin/learning-path/make-optional/'
                                                             . $singleUnit->id . '/no'
                                                         );
                                                        ?> ">No</a>
                                            <?php
                                        } else {?>
                                          <a href="<?php
                                                       echo $this->Url->build(
                                                           '/admin/learning-path/make-optional/'
                                                           . $singleUnit->id
                                                           . '/yes'
                                                       ); ?>">Yes</a> /
                                          <span style='font-weight:bold; text-decoration:none'>No</span>
                                            <?php
                                        }
                                        ?></td>
                                        <td>
                                            <?= $this->Html->link(
                                                '<i class="fa fa-pencil"></i>',
                                                '/admin/learning-path/manage-paths/' . $path->id
                                                    . '/' . $level->id . '/' . $singleUnit->id,
                                                array('escape' => false)
                                            ); ?>
                                            &nbsp;
                                            <?= $this->Html->link(
                                                '<i class="fa fa-trash"></i>',
                                                '/admin/learning-path/delete-unit/' . $path->id . '/'
                                                . $level->id . '/' . $singleUnit->id,
                                                [
                                                    'escape' => false,
                                                    'confirm' => 'Are you sure you want to delete this unit?'
                                                ]
                                            ); ?>
                                        </td>
                                     </tr>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No units added to this level yet.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($unit->id) : ?>
                        <div class="col-sm-4">
                            <label for="groups">Unit Contents</label>
                            <button class="btn btn-primary pull-right" id="saveUnitDetails"> Save Unit Contents</button>
                            <div style="height: 450px;overflow: auto;width: 100%;">
                              <table class="table table-responsive">
                                  <thead>
                                      <tr>
                                          <th>ID</th>
                                          <th>Name</th>
                                          <th>Type</th>
                                      </tr>
                                  </thead>
                                  <tbody id="selected_cards">
                                    <?php foreach ($unitDataSet as $dsKey => $unitData) : ?>
                                      <tr class='removecard' data-type="<?= $unitData->type; ?>"
                                        data-id="<?= $unitData->type == 'lesson'
                                            ? $unitData->lesson->id : $unitData->exercise->id; ?>">
                                          <td><?= $unitData->type == 'lesson'
                                            ? $unitData->lesson->id : $unitData->exercise->id; ?></td>
                                          <td><?= $unitData->type == 'lesson'
                                            ? $unitData->lesson->name : $unitData->exercise->name; ?></td>
                                          <td class="typeClass"><?= $unitData->type; ?></td>
                                      </tr>
                                    <?php endforeach; ?>
                                  </tbody>
                              </table>
                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-default addcard">
                                    <i class="fa fa-chevron-left"></i>&nbsp;Add</button>
                                <button type="button" class="btn btn-danger cardremove">Remove&nbsp;
                                    <i class="fa fa-chevron-right"></i></button>
                                <button type="button" class="btn btn-default moveup">Up&nbsp;
                                    <i class="fa fa-chevron-up"></i></button>
                                <button type="button" class="btn btn-default movedown">Down&nbsp;
                                    <i class="fa fa-chevron-down"></i></button>
                            </div>
                            <div class="selected-details" style="display: none;">
                                <label>Selected <span id="selected-details-type" class="typeClass">
                                    </span> Details</label>

                                <ul class="list-group">

                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <?= $this->Form->create(null, ['id' => 'less-exe']) ?>
                            <label for="set-type" id="set-type-label"><?= $set_type['label']; ?></label>
                            <?= $this->Form->control(
                                'set_type',
                                [
                                    'options' => $setTypeVals,
                                    'empty' => 'Select',
                                    'value' => $set_type['value'],
                                    'label' => false,
                                    'class' => 'form-control'
                                ]
                            ) ?>
                            <?= $this->Form->end() ?>
                            <table class="table" id="path-comp-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody id="unit-details-data">
                                    <?php if (!empty($unitDetails)) : ?>
                                        <?php foreach ($unitDetails as $det) : ?>
                                    <tr class="singlecard">
                                        <td class="card_id" data-type="lesson"><?= $det->id; ?></td>
                                        <td><?= $det->name; ?></td>
                                    </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No <?= $set_type['value'];
                                        ?> added to this level yet.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

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
                <button type="button" class="btn btn-primary" id="uploadFileFormSubmit">Upload File</button>
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

<div class="modal fade" id="unitSaveSuccess">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Unit Details Saved</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-md-12 text-center">
                        <h5>Unit details data has been saved successfully.</h5>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="savedetailOK">Ok</button>
            </div>
        </div>
    </div>
</div>
<?php
echo $this->Html->script('/js/tablednd.js', ['block' => 'scriptBottom']);
echo $this->Html->css('AdminLTE./bower_components/datatables.net-bs/css/dataTables.bootstrap.min');
echo $this->Html->script('AdminLTE./bower_components/datatables.net/js/jquery.dataTables.min',
    ['block' => 'script']
);
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net-bs/js/dataTables.bootstrap.min',
    ['block' => 'script']
);
?>
<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
function getAjaxUrl(urlvalue){
return '<?php echo Router::url('/');?>'+urlvalue;
}
<?php $this->Html->scriptEnd(); ?>
<?php echo $this->Html->script('dragDropUtils', ['block' => 'scriptBottom']); ?>
<?php echo $this->Html->script('pathScript', ['block' => 'scriptBottom']); ?>
