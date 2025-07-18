<?php
    use App\Lib\UtilLibrary;
    use Cake\ORM\TableRegistry;
    $rolesTable = TableRegistry::getTableLocator()->get('Roles');
    $roleName = $rolesTable->getRoleName($loggeduser['role_id']);
?>

<div class="user-panel">
    <div class="pull-left image">
        <?php echo $this->Html->image('user.jpeg', array('class' => 'img-circle', 'alt' => 'User Image')); ?>
    </div>
    <div class="pull-left info">
        <p><?php echo $loggeduser['name']; ?></p>
        <!-- Display user's platform role -->
        <a href="#"><i class="fa fa-circle text-success"></i> <?php echo $roleName; ?></a>
    </div>
</div>
