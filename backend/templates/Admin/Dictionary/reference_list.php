<?php

use Cake\Routing\Router;

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Dictionary References List</h3>
                    <div class="box-tools">
                        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
                        <div class="input-group input-group-sm" style="width: 150px;">

                            <?= $this->Form->text(
                                "q",
                                [
                                    'type' => "text",
                                    "class" => "form-control pull-right",
                                    "placeholder" => "Search",
                                    "value" => ($_GET['q'] ?? '')
                                ]
                            ) ?>
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>

                        </div> <?php echo $this->Form->end(); ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th style="width: 20px"><?php echo $this->Paginator->sort('id', 'Id', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('lakota', $languageName, array()); ?></th>
                            <th><?php echo $this->Paginator->sort('english', 'English', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('morphology', 'Morphology', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('reference', 'Reference', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('audio', 'Audio', array()); ?></th>
                            <th><?php echo $this->Paginator->sort('part_of_speech', 'Part of Speech', array()); ?></th>
                            <th style="width: 100px">Action</th>
                        </tr>
                        <?php foreach ($references as $ref) { ?>
                            <tr>
                                <td>
                                    <div class="checkbox listchkbox">
                                        <label>
                                            <input type="checkbox" class="userckk" data-id="<?php echo $ref->id; ?>">
                                        </label>
                                    </div>
                                </td>
                                <td><?php echo $ref->id; ?></td>
                                <td><?php echo $ref->lakota; ?></td>
                                <td><?php echo $ref->english; ?></td>
                                <td><?php echo $ref->morphology; ?></td>
                                <td><?php echo $ref->reference; ?></td>
                                <td><?php echo $ref->audio ? 'Available' : 'N/A'; ?></td>
                                <td><?php echo $ref->part_of_speech; ?></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="openDetails('<?php echo $ref->id; ?>')">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="<?php echo $this->Url->build('/admin/dictionary/edit/' . $ref->id); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-trash"></i>',
                                        '/admin/dictionary/delete/' . $ref->id,
                                        array(
                                            'escape' => false,
                                            'confirm' => 'Are you sure you want to delete this dictionary reference?'
                                        )
                                    ); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix">
                    <div class="form-group pull-left">
                        <select class="form-control bulkaction">
                            <option value="">Bulk Action</option>
                            <option value="deleteref">Delete</option>
                        </select>
                    </div>
                    <div class="form-group pull-left" style="margin-left: 10px;">
                        <button type="button" class="btn btn-primary" id='openModal'>Import Dictionary Data</button>
                    </div>
                    <ul class="pagination pagination-sm no-margin pull-right">
                        <li><?= $this->Paginator->prev('« Previous') ?></li>
                        <li><?= $this->Paginator->numbers() ?></li>
                        <li><?= $this->Paginator->next('Next »') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<div class="modal fade" id="dictionaryDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Details</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div class="row" id='response'></div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="importCsvExcelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Upload CSV/Excel</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div class="row" id='msgresponse'></div>
                </div>
                <div class="row">
                     <?= $this->Form->create(null, ['type' => 'file','id' => 'upload_csv_form']) ?>
                    <div class='col-sm-6 col-md-6'>
                        <div class="form-group">
                            <span class="input-group">
                                <?= $this->Form->text("uploadCsvFile", ['type' => "file", 'id' => "uploadCsvFile"]) ?>
                            </span>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                    <button type="button" class="btn btn-primary" id="submitFile">Submit</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
    function openDetails(id)
    {
        $('#response').html('');
        $('#dictionaryDetailsModal').modal('show');
        var data = {'id': id};
        $.ajax({
            type: "POST",
            url: '<?php echo Router::url(
                [
                    'controller' => 'Dictionary',
                    'action' => 'getDetails',
                    'prefix' => 'Admin'
                ],
                true
            ); ?>',
            data: data,
            success: function (res) {
                var result = JSON.parse(res);
                var lakota = result.data.lakota;
                var english = result.data.english;
                var morphology = result.data.morphology;
                var full_entry = result.data.full_entry;
                var part_of_speech = result.data.part_of_speech;
                var audio = result.data.FullUrl;
                var htmlStr='';
                htmlStr += '<div><?php echo $languageName;?>:<span>' + lakota + '</span></div>';
                htmlStr += '<div>English:<span>' + english + '</span></div>';
                htmlStr += '<div>Morphology:<span>' + morphology + '</span></div>';
                htmlStr += '<div>Part Of Speech:<span>' + part_of_speech + '</span></div>';
                if (audio) {
                    htmlStr += '<div>Audio:</div>';
                    htmlStr += '<audio controls id="audio-preview-modal">';
                    htmlStr += '<source class="audio-source" src="'+audio+'" type="audio/ogg">';
                    htmlStr += '<source class="audio-source" src="'+audio+'" type="audio/wav">';
                    htmlStr += 'Your browser does not support the audio element.';
                    htmlStr += '</audio>';
                }
                htmlStr += '<div>Full Entry:<br>' + full_entry + '</div>';
                $('#response').html(htmlStr);
            },
            error: function (err) {
                console.error(err);
            }
        });
    }
    $(function() {
        $(".bulkaction").on('change', function() {
            var action = $(this).val();
            var id = [];
            $(".userckk").each(function (index) {
                if ($(this).is(":checked")) {
                    id.push($(this).data('id'));
                }
            });

            if (action == 'deleteref') {

                if (id.length != 0) {
                    var data = {'ids': id, 'action': action};
                    $.ajax({
                        type: "POST",
                        url: '<?php echo Router::url(
                            [
                                'controller' => 'Dictionary',
                                'action' => 'bulkAction',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>',
                        data: data,
                        success: function (res) {
                            var result = JSON.parse(res);
                            if (result.status == 'success') {
                                alert('Action successfully Completed.');
                                location.reload();
                            }
                        },
                        error: function (err) {
                            console.error(err);
                        }
                    });
                } else
                {
                    alert('Please select atleast one instance.');
                    $(".bulkaction").val('');
                }

            }

        });

        $("#openModal").click(function () {
            $("#msgresponse").html('');
            $('#importCsvExcelModal').modal('show');
        });
         $("#submitFile").click(function () {

            var form = $('#upload_csv_form')[0];
            var data = new FormData(form);
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: "<?= Router::url('/admin/dictionary/importCsvExcel') ?>",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    var result = JSON.parse(data);
                    var htmlstr='';
                    $.each(result, function( index, value ) {
                        if (value.status)
                        {
                            htmlstr += "<p class='alert alert-success alert-dismissible'>" + value.message +
                                "<button type='button' class='close' data-dismiss='alert' " +
                                "aria-hidden='true'>×</button></p>";

                        } else {
                            htmlstr += "<p class='alert alert-danger alert-dismissible'>" + value.message +
                                "<button type='button' class='close' data-dismiss='alert' " +
                                "aria-hidden='true'>×</button></p>";
                        }

                    });
                    $("#msgresponse").html(htmlstr);
                  $('#upload_csv_form')[0].reset();
                  if(result[0].status)
                  {
                        $('#importCsvExcelModal').modal('hide');
                        alert('Data Successfully imported.');
                        location.reload();
                  }
                },
                error: function (err) {
                    console.error(err);
                }
            });
        });
    });

<?php
$this->Html->scriptEnd();
echo $this->fetch('scriptBottom');
?>
