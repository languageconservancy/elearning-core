<?php

use Cake\Routing\Router;

?>
<section class="content-header">
    <h1><?= __('Upload Login Page Image') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Site Setting</a></li>
        <li class="active">Upload Login Page Image</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Upload File</label>
                        <?= $this->Form->control('file', ['label' => false, 'type' => 'file']) ?>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
    <div class="box box-primary">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary processfile"
                        onclick="startProcessFile()">Process File</button>
                    <div id="per"> </div>
                </div>
            </div>
            <div class="box-footer">
                <div class="progress active">
                <div class="progress-bar progress-bar-success progress-bar-striped"
                    role="progressbar" aria-valuenow="0" aria-valuemin="0"
                    aria-valuemax="100" style="width: 20%" id="bar-p">
                  <span class="sr-only">0% Complete</span>
                </div>
              </div>
            </div>
        </div>
    </div>
</section>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>

var lastindex;
function startProcessFile() {
  $(".processfile").prop('disabled', true);
  uploadFile(0,'start');
}

function uploadFile(procressIndex, status) {
    var Index;
    if (procressIndex != 0) {
        Index = procressIndex;
    }
    else {
        Index = 0;
    }
    var data = {index: Index};
    if (status != "stop") {
        $.ajax({
            type: "POST",
            url: "<?= Router::url('/admin/sitesetting/ajaxUploadFileProgress') ?>",
            data: data,
            async: false,
            cache: false,
            success: function (data) {
                var result = JSON.parse(data);
                console.log(result);
                $("#bar-p").css("width", result.persentage + '%');
                $("#per").html(result.persentage + '%');
                uploadFile(result.nextindex, result.status);
                if (result.status == 'stop') {
                    $("#bar-p").css("width", '0%');
                    $(".processfile").prop('disabled', false);
                }
            },
            error: function (e) {
            }
        });
    }
}
<?php $this->Html->scriptEnd(); ?>
