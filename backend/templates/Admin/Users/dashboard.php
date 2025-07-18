<?php

use Cake\Routing\Router;

?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Dashboard</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<!-- Main content -->
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?php echo $pathscount;?></h3>
                    <p>Learning Paths</p>
                </div>
                <div class="icon">
                    <i class="ion ion-arrow-graph-up-left"></i>
                </div>
                <a href="javascript:void(0)"
                    class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                   <h3><?php echo $speedscount;?></h3>
                    <p>Learning Speed</p>
                </div>
                <div class="icon">
                    <i class="ion ion-speedometer"></i>
                </div>
                <a href="javascript:void(0)"
                    class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?php echo $usercount;?></h3>
                    <p>User Registrations</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a
                    href=
                        "<?php echo Router::url(
                            [
                                'controller' => 'Users',
                                'action' => 'userlist',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>"
                    class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
            <!-- TO DO List -->
            <div class="box box-primary">
                <div class="box-header">
                    <i class="fa fa-user"></i>
                    <h3 class="box-title">Latest 5 Users</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 20px">Id</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                        <?php foreach ($users as $u) { ?>
                            <tr>
                                <td><?php echo $u->id; ?></td>
                                <td><?php echo $u->name; ?></td>
                                <td><?php echo $u->email; ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix no-border">
                    <a href=
                        "<?php echo Router::url(
                            [
                                'controller' => 'Users',
                                'action' => 'addUsers',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>"
                        class="btn btn-default pull-right">
                        <i class="fa fa-plus"></i> Add User</a>
                </div>
            </div>
            <!-- /.box -->
        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-5 connectedSortable">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Latest 5 Paths</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Label</th>
                            <th>Description</th>
                        </tr>
                        <?php foreach ($paths as $p) { ?>
                            <tr>
                                <td><?php echo $p->label; ?></td>
                                <td><?php echo $p->description; ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Latest 5 Speed</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Label</th>
                            <th>Description</th>
                        </tr>
                        <?php foreach ($speeds as $s) { ?>
                            <tr>
                                <td><?php echo $s->label; ?></td>
                                <td><?php echo $s->description; ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>

        </section>
        <!-- right col -->
    </div>
    <!-- /.row (main row) -->
</section>
<!-- /.content -->
