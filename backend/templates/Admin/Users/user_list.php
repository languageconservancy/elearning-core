<?php

use Cake\Routing\Router;

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Users List</h3>
                    <div class="box-tools">
                        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
                        <div class="input-group input-group-sm" style="width: 150px;">

                            <input type="text" name="q" class="form-control pull-right"
                                placeholder="Search" value='<?php
                                if (isset($_GET['q'])) {
                                    echo $_GET['q'];
                                }
                                ?>'>

                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>

                        </div> <?php echo $this->Form->end(); ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th style="width: 20px"><?php echo $this->Paginator->sort('id', 'Id', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('name', 'Name', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('email', 'Email/Social Id', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('role_id', 'Role', array()); ?></th>
                            <th style="width: 100px">Action</th>
                        </tr>
                        <?php foreach ($users as $u) { ?>
                            <tr>
                                <td>
                                    <div class="checkbox listchkbox">
                                        <label>
                                            <input type="checkbox" class="userckk" data-id="<?php echo $u->id; ?>">
                                        </label>
                                    </div>
                                </td>
                                <td><?php echo $u->id; ?></td>
                                <td><?php echo $u->name; ?></td>
                                <td><?php echo $u->email; ?></td>
                                <td><?php echo $u->role->role; ?></td>
                                <td>
                                    <?php
                                    if ($u->is_active == 1) {
                                        ?>
                                        <a href="<?php
                                            echo $this->Url->build('/admin/users/status/' . $u->id . '/0'); ?>"
                                            onclick="return confirm('Are you sure to change the user status?')">
                                            <span class="label bg-blue">
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                        <?php
                                    } else {
                                        ?>
                                        <a href="<?php
                                            echo $this->Url->build('/admin/users/status/' . $u->id . '/1'); ?>"
                                            onclick="return confirm('Are you sure to change the user status?')">
                                            <span class="label bg-red">
                                                <i class="fa fa-times"></i>
                                            </span>
                                        </a>
                                        <?php
                                    }
                                    ?>
                                    &nbsp;
                                    <a href="<?php echo $this->Url->build('/admin/users/edit/' . $u->id); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-trash"></i>',
                                        '/admin/users/delete/' . $u->id,
                                        array(
                                            'escape' => false,
                                            'confirm' => 'Are you sure you want to delete this user?'
                                        )
                                    ); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix">
                    <div class="form-group pull-left">
                        <select class="form-control bulkaction">
                            <option value="">Bulk Action</option>
                            <option value="deleteuser">Delete</option>
                            <option value="resetpassword">Reset Password</option>
                        </select>
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

<div class="modal fade" id="resetpasswordmodal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="closeResetPassword()">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Enter password</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-5 control-label">Password</label>

                        <div class="col-sm-7">
                            <input type="password" class="form-control" id="password" placeholder="Password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-5 control-label">Conform Password</label>
                        <div class="col-sm-7">
                            <input type="password" class="form-control" id="repassword" placeholder="Conform Password">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="msgmodal" class="pull-left"></div>
                <button type="button" class="btn btn-primary" id="updatepasswordbutton">Save changes</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>

    $(function () {
        $(".bulkaction").change(function () {
            var action = $(this).val();
            var id = [];
            $(".userckk").each(function (index) {
                if ($(this).is(":checked")) {
                    id.push($(this).data('id'));
                }
            });
            if (action == 'deleteuser') {

                if (id.length != 0) {
                    var data = {'ids': id, 'action': action};
                    $.ajax({
                        type: "POST",
                        url: '<?php echo Router::url(
                            [
                                'controller' => 'Users',
                                'action' => 'bulkAction',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>',
                        data: data,
                        success: function (res) {
                            var result = JSON.parse(res);
                            if (result.status == 'success') {
                                alert('Action successfully Completed.');
                                window.location='<?php echo Router::url(
                                    [
                                        'controller' => 'Users',
                                        'action' => 'userList',
                                        'prefix' => 'Admin'
                                    ],
                                    true
                                ); ?>'
                                //location.reload();
                            }
                        }
                    });
                } else
                {
                    alert('Please select atleast one user.');
                    $(".bulkaction").val('');
                }

            }

            if (action == 'resetPassword') {
                if (id.length != 0) {
                    $("#resetpasswordmodal").modal("show");
                }
                else {
                    alert('Please select atleast one user.');
                    $(".bulkaction").val('');
                }

            }
        });
        $("#updatepasswordbutton").click(function () {
            var password= $("#password").val();
            var repassword= $("#repassword").val();
            if(password != repassword)
            {
                $("#msgmodal").html("<span>Password and Confirm Password Does not match</span>");
            }else
            {
                var action = $(".bulkaction").val();
                var id = [];
                $(".userckk").each(function (index) {
                    if ($(this).is(":checked")) {
                        id.push($(this).data('id'));
                    }
                });
                    var data = {'ids': id, 'action': action,'password':password};
                    $.ajax({
                        type: "POST",
                        url: '<?php echo Router::url(
                            [
                                'controller' => 'Users',
                                'action' => 'bulkAction',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>',
                        data: data,
                        success: function (res) {
                            var result = JSON.parse(res);
                            if (result.status == 'success') {
                                alert('Action successfully Completed.');
                                $('.userckk').attr('checked', false);
                                $(".bulkaction").val('');
                                $("#password").val('');
                                $("#repassword").val('');
                                $("#resetpasswordmodal").modal("hide");
                            }
                        }
                    });
            }
        });

    });
    /*close reset password modal*/
    function closeResetPassword()
    {
        $("#resetpasswordmodal").modal("hide");
    }
<?php
$this->Html->scriptEnd();
?>
