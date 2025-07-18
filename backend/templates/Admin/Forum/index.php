<?php

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Forum List</h3>
                    <div class="box-tools">
                    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="q" class="form-control pull-right" placeholder="Search"
                                   value='<?php
                                    if (isset($_GET['q'])) {
                                        echo $_GET['q'];
                                    }
                                    ?>'>

                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                        <?php echo $this->Form->end(); ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('path_id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('level_id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('unit_id') ?></th>
                            <th scope="col" class="actions"><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                    <?php foreach ($forums as $forum) : ?>
                            <tr>
                                <td><?= $this->Number->format($forum->id) ?></td>
                                <td><?= h($forum->title) ?></td>
                                <td><?= $forum->has('learningpath') ? $forum->learningpath->label : '' ?></td>
                                <td><?= $forum->has('level') ? $forum->level->name : '' ?></td>
                                <td><?= $forum->has('unit') ? $forum->unit->name : '' ?></td>
                                <td class="actions">
                              <?php if ($forum->title != 'Lessons by Unit') { ?>
                                    <?= $this->Html->link(
                                        '<i class="fa fa-pencil"></i>',
                                        [
                                            'action' => 'edit',
                                            $forum->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fa fa-trash"></i>',
                                        [
                                            'action' => 'delete',
                                            $forum->id
                                        ],
                                        [
                                            'confirm' => __('Are you sure you want to delete this forum?'),
                                            'escape' => false
                                        ]
                                    ) ?>
                              <?php } ?>

                                </td>
                            </tr>
                    <?php endforeach; ?>

                    <?php
                    if (empty($forums)) {
                        echo '<tr><td colspan="7">No Forum added yet.</td></tr>';
                    }
                    ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix">
                    <div class="form-group pull-left">
                        <!--                    <select class="form-control bulkaction">
                                                <option value="">Bulk Action</option>
                                                <option value="deleteuser">Delete</option>
                                                <option value="resetpassword">Reset Password</option>
                                            </select>-->
                    </div>
                    <ul class="pagination pagination-sm no-margin pull-right">
                        <li><?= $this->Paginator->prev('« Previous') ?></li>
                        <li><?= $this->Paginator->numbers() ?></li>
                        <li><?= $this->Paginator->next('Next »') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>


<?php
$this->Html->scriptEnd();
?>
