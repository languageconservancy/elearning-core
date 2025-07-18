<?php

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Post/Reply List</h3>
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
                            <th scope="col"><?= $this->Paginator->sort('id', 'ID', ['direction' => 'desc']); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('user_id', 'User ID'); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('parent_id', 'Post Type'); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('title', 'Post Title'); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('content', 'Post Content'); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('sticky', 'Pin'); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('is_hide', 'Hide'); ?></th>
                            <th scope="col"><?= $this->Paginator->sort('entry_time', 'Created'); ?></th>
                            <th scope="col" class="actions">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                    <?php foreach ($posts as $post) : ?>
                            <tr>
                                <td><?= $post->id ?></td>
                                <td>
                                <?php
                                echo $this->Html->link(
                                    $post->user_id,
                                    [
                                        'controller' => 'users',
                                        'action' => 'edit',
                                        $post->user_id,
                                        '_full' => true
                                    ]
                                ); ?>
                                </td>
                                <td><?php
                                if ($post->parent_id == null) {
                                    echo "Post";
                                } else {
                                    echo "Reply";
                                } ?>
                                </td>
                                <td><?= h($post->title) ?></td>
                                <td><?= h($post->content) ?></td>
                                <td>
                                <?php
                                if ($post->sticky == 'Y') {
                                    echo $this->Html->link(
                                        '<i class="glyphicon glyphicon-ok"></i>',
                                        [
                                            'action' => 'isSticky',
                                            $post->id,
                                            'N'
                                        ],
                                        ['escape' => false]
                                    );
                                } else {
                                    echo $this->Html->link(
                                        '<i class="glyphicon glyphicon-remove"></i>',
                                        [
                                            'action' => 'isSticky',
                                            $post->id
                                        ],
                                        ['escape' => false]
                                    );
                                }
                                ?>
                                </td>
                                <td>
                              <?php
                                if ($post->is_hide == 'Y') {
                                    echo $this->Html->link(
                                        '<i class="glyphicon glyphicon-ok"></i>',
                                        [
                                            'action' => 'isHide',
                                            $post->id,
                                            'N'
                                        ],
                                        ['escape' => false]
                                    );
                                } else {
                                    echo $this->Html->link(
                                        '<i class="glyphicon glyphicon-remove"></i>',
                                        [
                                            'action' => 'isHide',
                                            $post->id
                                        ],
                                        ['escape' => false]
                                    );
                                }
                                ?>
                                </td>
                                <td><?php echo $post->entry_time->nice(); ?></td>
                                <td class="actions">
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    [
                                        'action' => 'editPost',
                                        $post->id
                                    ],
                                    ['escape' => false]
                                ) ?>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    [
                                        'action' => 'deletePost',
                                        $post->id
                                    ],
                                    [
                                        'confirm' => __('Are you sure you want to delete this Post/Reply?'),
                                        'escape' => false
                                    ]
                                ) ?>
                                </td>
                            </tr>
                    <?php endforeach; ?>

                    <?php
                    if (empty($posts)) {
                        echo '<tr><td colspan="5">No Post added yet.</td></tr>';
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
