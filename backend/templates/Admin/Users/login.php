<?php

use Cake\Core\Configure;

?>
<!--<p class="login-box-msg"><?php echo __('Sign in to start your session') ?></p>-->
<?= $this->Flash->render() ?>
<?= $this->Form->create() ?>
        <div class="form-group has-feedback">
            <?= $this->Form->control(
                'email',
                [
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => "Email",
                    'autocomplete' => 'email'
                ]
            ) ?>
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <?= $this->Form->control(
                'password',
                [
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => "Password",
                    'type' => 'password',
                    'autocomplete' => 'current-password'
                ]
            ) ?>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="row">
            <div class="col-xs-8">
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
            </div>
            <!-- /.col -->
        </div>
<?= $this->Form->end() ?>
<a href="<?php echo Configure::read('FROENTEND_LINK') ?>forgot-password" target="_blank">Forgot password</a>
