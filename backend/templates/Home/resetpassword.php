<?= $this->Form->create() ?>
<div class="form-group has-feedback">
    <?= $this->Form->control(
        'password',
        [
            'label' => false,
            'class' => 'form-control',
            'placeholder' => "Password",
            'type' => 'password'
        ]
    ) ?>
    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
</div>
<div class="form-group has-feedback">
    <?= $this->Form->control(
        'password',
        [
            'label' => false,
            'class' => 'form-control',
            'placeholder' => "Confirm Password",
            'type' => 'password'
        ]
    ) ?>
    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
</div>
<div class="row">
    <div class="col-xs-8">
    </div>
    <!-- /.col -->
    <div class="col-xs-4">
        <button type="submit" class="btn btn-primary btn-block btn-flat">Change</button>
    </div>
    <!-- /.col -->
</div>
<?php echo $this->Form->end(); ?>
