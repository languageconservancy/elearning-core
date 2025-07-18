<?php

//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Add/Edit User') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">User</a></li>
        <li class="active">Add/Edit User</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary" style="margin-bottom: 0px">
        <?= $this->Form->create($user ?? null) ?>
        <div class="box-body">
            <div class="box-header with-border">
                <h3 class="box-title">User Info</h3>
            </div>
            <div class="row">
                <div class="col-md-6">
                <?php if (!isset($user->fb_id) || $user->fb_id == '') { ?>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <?= $this->Form->control(
                            'email',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter email",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div><?php
                } else {
                    echo $this->Form->control('email', ['type' => 'hidden']);
                } ?>
                <?php
                if (!isset($user->id)) { ?>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <?= $this->Form->control(
                            'password',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Password",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                    <?php
                } ?>
                    <div class="form-group">
                        <label>Platform Role</label>
                        <?= $this->Form->control(
                            'role_id',
                            [
                                'options' => $roles,
                                'label' => false,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label>Learning Speed</label>
                        <?= $this->Form->control(
                            'learningspeed_id',
                            [
                                'options' => $learningspeed,
                                'label' => false,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name</label>
                        <?= $this->Form->control(
                            'name',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Name",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                <?php if (!isset($user->id)) { ?>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Confirm Password</label>
                            <?= $this->Form->control(
                                'repassword',
                                [
                                    'label' => false,
                                    'class' => 'form-control',
                                    'placeholder' => "Enter Confirm Password",
                                    'autocomplete' => 'off',
                                    'type' => 'password'
                                ]
                            );
                            ?>
                    </div><?php
                } ?>
                    <div class="form-group">
                        <label>Date Of Birth:</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <?= $this->Form->control(
                                'dob',
                                [
                                    'label' => false,
                                    'class' => 'form-control pull-right datepicker',
                                    'type' => 'text',
                                    'placeholder' => "Date of Birth",
                                    'readonly' => true
                                ]
                            ) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Learning Path</label>
                        <?= $this->Form->control(
                            'learningpath_id',
                            [
                                'options' => $learningpaths,
                                'label' => false,
                                'class' => 'form-control'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save User Info</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>
<!-- End of User info -->

<!-- Associated Schools -->
<?php if ($showSchoolTable) { ?>
<section class="content">
    <div class="box box-secondary">
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header with-border">
                        <h3 class="box-title">Associated Schools</h3>
                        <div class="box-tools">
                        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
                            <div class="input-group input-group-sm" style="width: 150px;">

                                <input type="text" name="q" class="form-control pull-right"
                                    placeholder="Search" value='<?php
                                    if (isset($_GET['q'])) {
                                        echo $_GET['q'];
                                    }
                                    ?>'
                                >

                            </div>
                        <?= $this->Form->end() ?>

                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-bordered" id="school_list">
                            <tr>
                                <th style="width: 20px">School User Id</th>
                                <th>School Name</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>School Role</th>
                                <th style="width: 100px">Actions</th>
                            </tr>
                            <?php
                            foreach ($enlistedSchools as $u) { ?>
                                <?= $this->Form->create(
                                    $u,
                                    [
                                        'url' => '/admin/users/editSchoolUser/' . $user->id . '/' . $u->id,
                                        'method' => 'POST', 'id' => 'edit_school_user_' . $u->id
                                    ]
                                ) ?>
                                    <?= $this->Form->hidden(
                                        'school_name',
                                        [
                                            'form' => 'edit_school_user_' . $u->id,
                                            'value' => $u['school']['name']
                                        ]
                                    ); ?>
                                    <tr>
                                        <td><?php echo $u->id; ?></td>
                                        <td><?php echo $u['school']['name'] ?></td>
                                        <td><?=
                                            $this->Form->control(
                                                'f_name',
                                                [
                                                    'label' => false,
                                                    'class' => 'form-control',
                                                    'placeholder' => "Enter Name",
                                                    'autocomplete' => 'off',
                                                    'form' => 'edit_school_user_' . $u->id
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td><?=
                                            $this->Form->control(
                                                'l_name',
                                                [
                                                    'label' => false,
                                                    'class' => 'form-control',
                                                    'placeholder' => "Enter Name",
                                                    'autocomplete' => 'off',
                                                    'form' => 'edit_school_user_' . $u->id
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td><?=
                                            $this->Form->control(
                                                'role_id',
                                                [
                                                    'options' => $schoolRoles,
                                                    'label' => false,
                                                    'class' => 'form-control',
                                                    'form' => 'edit_school_user_' . $u->id
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td>
                                            <button form="edit_school_user_<?php echo $u->id ?>"
                                                style="border: none; background: none">
                                                <span class="label bg-blue">
                                                    <i class="fa fa-check" title="Update school user"></i>
                                                </span>
                                            </button>

                                            <?php
                                                echo $this->Html->link(
                                                    '<i class="fa fa-trash"></i>',
                                                    '/admin/users/deleteSchoolUser/' . $u->id,
                                                    array(
                                                        'escape' => false,
                                                        'confirm' =>
                                                            'Are you sure you want to delete this user?'
                                                    )
                                                );
                                            ?>
                                        </td>
                                    </tr>
                                <?= $this->Form->end() ?>
                            <?php } ?>
                                <tr>
                                    <?= $this->Form->create(
                                        null,
                                        [
                                            'url' => '/admin/users/addSchoolUser/' . $user->id ?? 0,
                                            'method' => 'POST',
                                            'id' => 'create-user-form'
                                        ]
                                    ) ?>
                                        <?= $this->Form->hidden(
                                            'user_id',
                                            [
                                                'value' => $user->id
                                            ]
                                        ) ?>
                                        <?= $this->Form->hidden(
                                            'school_name',
                                            [
                                                'value' => $user->id
                                            ]
                                        ) ?>
                                        <td>N/A</td>
                                        <td><?=
                                            $this->Form->control(
                                                'school_id',
                                                [
                                                    'options' => $schools,
                                                    'label' => false,
                                                    'class' => 'form-control'
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td><?=
                                            $this->Form->control(
                                                'f_name',
                                                [
                                                    'label' => false,
                                                    'class' => 'form-control',
                                                    'placeholder' => "Enter first name",
                                                    'autocomplete' => 'off'
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td><?=
                                            $this->Form->control(
                                                'l_name',
                                                [
                                                    'label' => false,
                                                    'class' => 'form-control',
                                                    'placeholder' => "Enter last name",
                                                    'autocomplete' => 'off'
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td><?=
                                            $this->Form->control(
                                                'role_id',
                                                [
                                                    'options' => $schoolRoles,
                                                    'label' => false,
                                                    'class' => 'form-control'
                                                ]
                                            );
                                            ?>
                                        </td>
                                        <td>
                                            <button type="submit" id="add_row" class="btn btn-default"
                                                title="Create school user">
                                                <i class="fa fa-plus" form="create-user-form"></i> Create
                                            </button>
                                        </td>
                                    <?= $this->Form->end() ?>
                                </tr>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section><?php
} ?>

<?php
echo $this->Html->css('AdminLTE./bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min');
echo $this->Html->script(
    'AdminLTE./bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min',
    ['block' => 'scriptBottom']
);
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>

<?php
if (isset($user->id) && isset($user->dob)) {
    ?>
    $(function () {
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        }).datepicker("update", "<?php echo $user->dob->format('Y-m-d'); ?>");
    });
    <?php
} else {
    ?>
    $(function () {
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
    <?php
}
$this->Html->scriptEnd();
?>
