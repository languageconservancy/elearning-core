<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Excel Bulk Unit Contents Upload (Can be used for cards)') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Bulk Upload</a></li>
        <li class="active">Bulk Unit Contents Upload</li>
    </ol>
</section>

<section class="content">
    <div class="box box-secondary pad">
        <div style="margin-left: 5px;"><b>Notes:</b></div>
        <ul style="margin-left: 0px !important;">
            <li>
                <div class="inline p5">If you want to update an existing card, the following fields must be identical, otherwise a new card will be created:</div>
                <span style="margin-left: 3px;"><b>Card Type</b>, <b>Language</b>, <b>English</b>, <b>Gender</b>.</span>
            </li>
            <li>
                <div class="inline p5"><span style="background: lightblue; padding: 2px;">Updated values</span> to existing cards will be highlighted in blue. Leave the box checked to accept the update.</div>
            </li>
            <li>
                <div class="inline p5"><span style="background: orange; padding: 2px;">Removed values</span> from existing cards will be highlighted in orange. Check the box to accept removal.</div>
            </li>
            <li>
                <div class="inline p5"><span style="background: lightgreen; padding: 2px;">New cards</span> will be highlighted in green.</div>
            </li>
            <li>
                <div class="inline p5"><span style="background: red; padding: 2px;">Rows with errors</span> will be highlighted in red.</div>
            </li>
        </ul>
    </div>
    <div class="box box-primary">
        <?= $this->Form->create($File, ['type' => 'file']) ?>

        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?php if (!isset($File['id'])) { ?>
                        <div class="form-group">
                            <label>Upload Unit Contents List (Excel)</label>
                            <?=
                              $this->Form->control('file', [
                                'label' => false,
                                'type' => 'file',
                                'accept' => '.xls,.xlsx',
                                'title' => 'Please select a Unit Contents sheet file in Excel format (.xlsx, .xls)',
                              ])
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Preview</button>
            </div>

        </div>

        <?= $this->Form->end() ?>
    </div>
</section>
