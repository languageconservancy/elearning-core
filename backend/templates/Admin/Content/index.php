<?php

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Content List</h3>
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
                            <th scope="col"><?= $this->Paginator->sort('keyword') ?></th>
                            <th scope="col" class="actions"><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                    <?php foreach ($contents as $content) : ?>
                            <tr>
                                <td><?= $this->Number->format($content->id) ?></td>
                                <td><?= h($content->title) ?></td>
                                <td><?= ($content->keyword) ?></td>
                                <td class="actions">

                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    [
                                        'action' => 'edit',
                                        $content->id
                                    ],
                                    ['escape' => false]
                                ) ?>
                                </td>
                            </tr>
                    <?php endforeach; ?>

                    <?php
                    if (count($contents) == 0) {
                        echo '<tr><td colspan="7">No Content added yet.</td></tr>';
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
