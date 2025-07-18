<?php

use Cake\Routing\Router;
use App\Lib\UtilLibrary;
use Cake\ORM\TableRegistry;
$SuperAdminRoleId = TableRegistry::getTableLocator()->get('Roles')->getRoleId(UtilLibrary::ROLE_SUPERADMIN_STR);
?>

<ul class="sidebar-menu" data-widget="tree">

    <li class="header">MAIN NAVIGATION</li>
    <li>
        <a href="<?php echo Router::url(
            [
                'controller' => 'Users',
                'action' => 'dashboard',
                'prefix' => 'Admin'
            ],
            true
        ); ?>">
        <i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-info"></i> <span>About Pages</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Content',
                    'action' => 'index',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View About Pages</a></li>
        </ul>
    </li>
    <?php if ($loggeduser->role_id === $SuperAdminRoleId): ?>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-users"></i> <span>Users</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Users',
                    'action' => 'addUsers',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add User</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Users',
                    'action' => 'userList',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View Users</a></li>
        </ul>
    </li>
    <?php endif; ?>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-address-card-o"></i> <span>Cards</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Cards',
                    'action' => 'addCards',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add Card</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Cards',
                    'action' => 'cardsList',
                    'prefix' => 'Admin',
                    'sort' => 'lakota',
                    'direction' => 'asc'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i>View Cards</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Cards',
                    'action' => 'uploadCards',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-upload"></i>Upload Cards</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-object-group"></i> <span>Card Groups</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'CardGroups',
                    'action' => 'addCardGroup',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add Card Group</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-book"></i> <span>Lessons</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Lessons',
                    'action' => 'manageLesson',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Manage Lessons</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-question"></i> <span>Exercises</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Exercises',
                    'action' => 'manageExercises',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Manage Exercises</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-graduation-cap"></i> <span>Learning Paths</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'LearningPath',
                    'action' => 'managePaths',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Manage Paths</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-home"></i> <span>Schools</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Schools',
                    'action' => 'addSchools',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add School</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Schools',
                    'action' => 'schoolList',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i>View Schools</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-files-o"></i> <span>Files</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Files',
                    'action' => 'uploadFiles',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i> Upload Files</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Files',
                    'action' => 'index',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View Files</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-comments"></i> <span>Forums</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Forum',
                    'action' => 'add',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add Forum</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Forum',
                    'action' => 'index',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View Forums</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Forum',
                    'action' => 'getPost',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View Posts/Replies</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-book"></i> <span>Dictionary References</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Dictionary',
                    'action' => 'addReference',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add Reference</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Dictionary',
                    'action' => 'referenceList',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View References</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-book"></i> <span>Inflection References</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Inflection',
                    'action' => 'addInflection',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-plus"></i>Add Reference</a></li>
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'Inflection',
                    'action' => 'inflectionList',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-eye"></i> View References</a></li>
        </ul>
    </li>
    <li class="treeview">
        <a href="#">
            <i class="fa fa-upload"></i> <span>Bulk Upload</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li><a href="<?php echo Router::url(
                [
                    'controller' => 'LearningPath',
                    'action' => 'uploadUnitContents',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>"><i class="fa fa-upload"></i>Bulk Unit Contents Upload</a></li>
        </ul>
    </li>
</ul>

<?php
echo $this->Html->script('AdminLTE./bower_components/jquery/dist/jquery.min');
$this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    $(function () {
        var params = window.location.pathname;
        params = params.toLowerCase();

        if (params != "/") {
            $(".sidebar-menu li a").each(function (i) {
                var obj = this;
                var url = $(this).attr("href");
                if (url == "" || url == "#") {
                    return true;
                }
                url = url.toLowerCase();
                if (url.indexOf(params) > -1) {
                    $(this).parent().addClass("active open menu-open");
                    $(this).parent().parent().addClass("active open menu-open");
                    $(this).parent().parent().parent().addClass("active open menu-open");
                    $(this).parent().parent().parent().parent().addClass("active open menu-open");
                    $(this).parent().parent().parent().parent().parent().addClass("active open menu-open");
                    return false;
                }
            });
        }
    });
<?php
$this->Html->scriptEnd();
?>
