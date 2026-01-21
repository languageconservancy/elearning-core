<?php

use Cake\Core\Configure;

$appName = Configure::read('App.name');
?>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; <?php echo date('Y')?> <a href="javascript:void(0)"><?php echo $appName; ?></a>.</strong> All rights
    reserved.
</footer>
