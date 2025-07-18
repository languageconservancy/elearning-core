<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>

<style>
    .table {
        width: 100%;
        table-layout: auto; /* adjust columns to fit content */
        border-collapse: collapse;
        border-spacing: 2px;
    }

    .table td {
        white-space: nowrap;
        overflow-x: auto;
        max-width: 100%;
    }

    /* Optional: Add styling to make the scrolling effect clearer */
    .table td::-webkit-scrollbar {
        height: 8px; /* Height of the scrollbar */
    }

    .table td::-webkit-scrollbar-thumb {
        background-color: #888; /* Scrollbar color */
    }

    .table td::-webkit-scrollbar-track {
        background-color: #f1f1f1; /* Track color */
    }

    /* Optional: Add styling to make the scrolling effect clearer */
    .table-container {
        overflow-x: auto;
        white-space: nowrap;
    }
    .table-container::-webkit-scrollbar {
        height: 8px; /* Height of the scrollbar */
    }

    .table-container::-webkit-scrollbar-thumb {
        background-color: #888; /* Scrollbar color */
    }

    .table-container::-webkit-scrollbar-track {
        background-color: #f1f1f1; /* Track color */
    }

    .overflow-x-auto {
        overflow-x: auto;
    }

    .p1 {
        padding: 1rem;
    }
</style>

<section class="content-header">
    <h1><?= __('Upload Cards from Excel File') ?></h1>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Cards</a></li>
        <li class="active">Upload Cards</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="p1">
            <h4>Notes:</h4>
            <div><b>Uploader template: </b> <?= $this->Html->link('Download', '/webroot/templates/upload-cards-template.xlsx') ?></div>
            <div><b>Adding New Cards: </b> New cards should have their ID field blank, since it will be auto-assigned a new ID on import.</div>
            <div><b>Updating Existing Cards: </b> To update an existing card in the database, the ID must match the ID in the database's Cards table.</div>
            <div><b>Checkboxes: </b> All updates, additions, and removals can be checked on/off individually or globally. Updates and additions are auto-checked. Empty values default to unchecked.</div>
        </div>

        <!-- separator -->
        <hr>

        <?= $this->Form->create($file, ['type' => 'file']) ?>

        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?php if (!isset($file['id'])) { ?>
                        <div class="form-group">
                            <label>Upload Card List (Excel)</label>
                            <?=
                              $this->Form->control('file', [
                                'label' => false,
                                'type' => 'file',
                                'accept' => '.xls,.xlsx',
                                'title' => 'Please select a card list file in Excel format (.xlsx, .xls)',
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
