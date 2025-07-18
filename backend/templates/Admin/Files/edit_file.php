<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Add/Edit File') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Files</a></li>
        <li class="active">Manage Files</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($File, ['type' => 'file']) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name</label>
                        <?= $this->Form->control(
                            'name',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Name"
                            ]
                        ) ?>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Description</label>
                        <?= $this->Form->control(
                            'description',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Enter Description",
                                'type' => 'textarea'
                            ]
                        ) ?>
                    </div>
                    <?php if (!isset($File['id'])) { ?>
                        <div class="form-group">
                            <label>Upload File</label>
                            <?= $this->Form->control('file', ['label' => false, 'type' => 'file']) ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <?php
                    if (isset($File['id'])) {
                        if ($File['type'] == 'image') {
                            echo $this->Html->image(
                                $File['FullUrl'],
                                [
                                    'alt' => 'image',
                                    "width" => 'auto',
                                    'class' => "img-responsive"
                                ]
                            );
                        } elseif ($File['type'] == 'video') {
                            ?>
                            <video width="320" height="240" controls>
                                <source src="<?php echo $File['FullUrl']; ?>" type="video/mp4">
                                <source src="<?php echo $File['FullUrl']; ?>" type="video/ogg">
                                <source src="<?php echo $File['FullUrl']; ?>" type="video/webm">
                                Your browser does not support the video tag.
                            </video>
                            <?php
                        } elseif ($File['type'] == 'audio') {
                            ?>
                            <audio controls>
                                <source src="<?php echo $File['FullUrl']; ?>" type="audio/ogg">
                                <source src="<?php echo $File['FullUrl']; ?>" type="audio/mpeg">
                                <source src="<?php echo $File['FullUrl']; ?>" type="audio/wav">
                                Your browser does not support the audio element.
                            </audio>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>
