<?php

use Cake\Routing\Router;

?><!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Inflections List</h3>
                    <div class="box-tools">
                        <?php echo $this->Form->create(
                            null,
                            ['type' => 'get']
                        ); ?>
                        <div class="input-group input-group-sm" style="width: 150px;">

                            <input type="text" name="q" class="form-control pull-right"
                                placeholder="Search" value="<?php
                                if (isset($_GET['q'])) {
                                    echo $_GET['q'];
                                }
                                ?>"
                            >

                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>

                        </div><?php echo $this->Form->end(); ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th style="width: 20px"><?php echo $this->Paginator->sort('id', 'Id'); ?></th>
                            <th><?php echo $this->Paginator->sort('headword', 'HeadWord'); ?></th>
                            <th>
                                <?php echo $this->Paginator->sort(
                                    'Dictionary.english',
                                    'Reference Dictionary Content'
                                ); ?>
                            </th>
                            <th style="width: 100px">Action</th>
                        </tr>
                        <?php foreach ($inflections as $u) { ?>
                            <tr>
                                <td>
                                    <div class="checkbox listchkbox">
                                        <label>
                                            <input type="checkbox" class="userckk" data-id="<?php echo $u->id; ?>">
                                        </label>
                                    </div>
                                </td>
                                <td><?php echo $u->id; ?></td>
                                <td><?php echo $u->headword; ?></td>
                                <td><?php echo $u->dictionary->english ?? ''; ?></td>
                                <td>
                                    <a href="<?php echo $this->Url->build('/admin/inflection/edit/' . $u->id); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-trash"></i>',
                                        '/admin/inflection/delete/' . $u->id,
                                        [
                                            'escape' => false,
                                            'confirm' => 'Are you sure you want to delete this inflection?'
                                        ]
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
                            <option value="deleteinflection">Delete</option>
                        </select>
                    </div>
                    <div class="form-group pull-left" style="margin-left: 10px;">
                        <button type="button" class="btn btn-primary" id='openModal'>Import Inflection Data</button>
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
                                <input type="file" id="uploadCsvFile" name="uploadCsvFile">
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

<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>

    $(function () {
        $(".bulkaction").change(function () {
            var action = $(this).val();
            var id = [];
            $(".userckk").each(function (index) {
                if ($(this).is(":checked")) {
                    id.push($(this).data('id'));
                }
            });
            if (action == 'deleteinflection') {

                if (id.length != 0) {
                    var data = {'ids': id, 'action': action};
                    $.ajax({
                        type: "POST",
                        url: '<?php echo Router::url(
                            [
                                'controller' => 'Inflection',
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
                        }
                    });
                }
                else
                {
                    alert('Please select atleast one inflection.');
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
            url: "<?= Router::url('/admin/inflection/importCsvExcel') ?>",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var result = JSON.parse(data);
                var htmlstr='';
                $.each(result, function( index, value ) {
                    if(value.status) {
                        htmlstr +=
                            "<p class='alert alert-success alert-dismissible'>" + value.message
                            + "<button type='button' class='close' "
                            + "data-dismiss='alert' aria-hidden='true'>×</button></p>";
                    } else {
                        htmlstr += "<p class='alert alert-danger alert-dismissible'>"
                            + value.message + "<button type='button' class='close' "
                            + "data-dismiss='alert' aria-hidden='true'>×</button></p>";
                    }

                });
                $("#msgresponse").html(htmlstr);
                $('#upload_csv_form')[0].reset();
                if (result[0].status) {
                    $('#importCsvExcelModal').modal('hide');
                    alert('Data Successfully imported.');
                    location.reload();
                }
                }
            });
        });
    });
<?php
$this->Html->scriptEnd();
?>
