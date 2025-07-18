<?php

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Files List</h3>
                    <div class="pull-right">
                        <?php echo $this->Form->create(
                            null,
                            [
                                'type' => 'get',
                                'class' => 'form-inline',
                                'id' => 'filefilter'
                            ]
                        ); ?>
                            <div class="input-group input-group-sm" style="margin-right: 10px;">
                                <?= $this->Form->control(
                                    'type',
                                    [
                                        'options' => $Types,
                                        'label' => false,
                                        'class' => 'form-control',
                                        'empty' => 'Sort',
                                        'value' => $_GET['type'] ?? ''
                                    ]
                                ) ?>
                            </div>
                            <div class="input-group input-group-sm" style="margin-right: 10px;">
                                <?= $this->Form->control(
                                    'limit',
                                    [
                                        'options' => $limits,
                                        'label' => false,
                                        'class' => 'form-control',
                                        'value' => $_GET['limit'] ?? ''
                                    ]
                                ) ?>
                            </div>
                            <div class="input-group" style="margin-right: 10px;">
                                <?= $this->Form->control(
                                    'search',
                                    [
                                        'div' => false,
                                        'label' => false,
                                        'class' => 'form-control',
                                        'placeholder' => 'Search',
                                        'value' => $_GET['search'] ?? ''
                                    ]
                                ) ?>

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
                        <tr>
                            <th style="width: 20px"><?php echo $this->Paginator->sort('id', 'Id', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('name', 'Name', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('file_name', 'File Name', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('type', 'Type', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('uploaded_by', 'Uploaded By', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('modified', 'Last Modified', array()); ?></th>
                            <th style="width: 100px">Action</th>
                        </tr>
                        <?php foreach ($files as $f) { ?>
                            <tr>
                                <td><?php echo $f->id; ?></td>
                                <td><?php echo $f->name; ?></td>
                                <td><?php echo $f->file_name; ?></td>
                                <td><?php echo ucfirst($f->type); ?></td>
                                <td><?php
                                if (isset($f->user->name)) {
                                    echo $f->user->name;
                                }
                                ?>
                                </td>
                                <td><?php echo $f->modified; ?></td>
                                <td>
                                    <a href="<?php echo $this->Url->build('/admin/files/edit-file/' . $f->id); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-trash"></i>',
                                        '/admin/files/delete-file/' . $f->id,
                                        array(
                                            'escape' => false,
                                            'confirm' => 'Are you sure you want to delete this File?'
                                        )
                                    ); ?>
                                </td>
                            </tr>
                            <?php
                        }
                        if (empty($files)) {
                            echo '<tr><td colspan="4">No Files found.</td></tr>';
                        }
                        ?>

                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix">
                    <ul class="pagination pagination-sm no-margin pull-right">
                        <li><?= $this->Paginator->first('« First Page') ?></li>
                        <li><?= $this->Paginator->prev('« Previous') ?></li>
                        <li><?= $this->Paginator->numbers() ?></li>
                        <li><?= $this->Paginator->next('Next »') ?></li>
                        <li><?= $this->Paginator->last('Last Page »') ?></li>
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
$(function () {
    $( "#type" ).change(function() {
        $("#filefilter").submit();
    });
    $( "#limit" ).change(function() {
        $("#filefilter").submit();
    });
});
 <?php
    $this->Html->scriptEnd();
//echo $this->Html->script(['custom'], ['block' => 'scriptBottom']);
    ?>
