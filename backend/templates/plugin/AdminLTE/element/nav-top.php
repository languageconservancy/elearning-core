<?php

use Cake\Routing\Router;

?>
<?= $this->Html->css('custom') ?>
<?= $this->Html->meta(
    [
        'property' => 'og:image',
        'content' => $this->request->getAttribute("webroot") . 'img/ro-red.png'
    ]
); ?>
<nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
    </a>

    <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <span class="hidden-xs"><?php echo $loggeduser['name']; ?></span>
                </a>
                <ul class="dropdown-menu">
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <p><small>Member since <?php echo date('Y/m/d', strtotime($loggeduser['created'])); ?></small>
                        </p>
                        <div class="pull-left">
                            <a href="<?php echo Router::url(
                                [
                                    'controller' => 'Users',
                                    'action' => 'edit',
                                    $loggeduser['id'],
                                    'prefix' => 'Admin'
                                ],
                                true
                            ); ?>"
                               class="btn btn-default btn-flat">Profile</a>
                        </div>
                        <div class="pull-right">
                            <a href="<?php echo Router::url(
                                [
                                    'controller' => 'Users',
                                    'action' => 'logout',
                                    'prefix' => 'Admin'
                                ],
                                true
                            ); ?>"
                            class="btn btn-default btn-flat">Sign out</a>
                        </div>
                    </li>
                </ul>
            </li>
            <!-- Control Sidebar Toggle Button -->
        </ul>
    </div>
</nav>
