<?php

use Cake\Routing\Router;

?>
<!-- Main content -->
<style>
    .filter-input { max-width: 100px; padding: 0 3px; }
    li { margin: 0 5px; }
</style>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Cards List</h3>
                    <div class="box-tools">
                        <?= $this->Form->create(null, ['type' => 'get']); ?>
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="q" class="form-control pull-right"
                                placeholder="Search" value="<?php
                                if (isset($_GET['q'])) {
                                    echo $_GET['q'];
                                }
                                ?>"
                            >

                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>

                        </div>
                        <?= $this->Form->end(); ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $this->Form->create(
                                null,
                                [
                                    'type' => 'get',
                                    'class' => 'form-inline',
                                    'id' => 'cardfilter'
                                ]
                            ); ?>
                            <label>Filter By</label>
                            <ul class="list-inline">
                                <li>
                                    <label>Card Type</label><br>
                                    <?= $this->Form->control(
                                        'card_type',
                                        [
                                            'options' => $cardTypes,
                                            'class' => 'form-control selectClass',
                                            'label' => false,
                                            'empty' => 'All',
                                            'value' => $_GET['card_type'] ?? ''
                                        ]
                                    ); ?>
                                </li>
                                <li>
                                    <label><?php echo $languageName;?></label><br>
                                    <div class="input-group">
                                    <?= $this->Form->control(
                                        'lakota',
                                        [
                                            'div' => false,
                                            'label' => false,
                                            'class' => 'form-control filter-input',
                                            'placeholder' => $languageName,
                                            'value' => $_GET['lakota'] ?? ''
                                        ]
                                    ); ?>
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fa fa-search"></i>
                                        </button>
                                        </div>
                                    </div>
                                    </li>
                                    <li>
                                        <label>English</label><br>
                                        <div class="input-group">
                                            <?= $this->Form->control(
                                                'english',
                                                [
                                                    'div' => false,
                                                    'label' => false,
                                                    'class' => 'form-control filter-input',
                                                    'placeholder' => 'English',
                                                    'value' => $_GET['english'] ?? ''
                                                ]
                                            ); ?>
                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-default">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <label>Audio Preview</label><br>
                                        <?= $this->Form->control(
                                            'audio',
                                            [
                                                'options' => $optionTypes,
                                                'class' => 'form-control selectClass',
                                                'label' => false,
                                                'empty' => 'All',
                                                'value' => $_GET['audio'] ?? ''
                                            ]
                                        ); ?>
                                    </li>
                                    <li>
                                        <label>Image Preview</label><br>
                                        <?= $this->Form->control(
                                            'image',
                                            [
                                                'options' => $optionTypes,
                                                'class' => 'form-control selectClass',
                                                'label' => false,
                                                'empty' => 'All',
                                                'value' => $_GET['image'] ?? ''
                                            ]
                                        ); ?>
                                    </li>
                                    <li>
                                        <label>Video</label><br>
                                        <?= $this->Form->control(
                                            'video',
                                            [
                                                'options' => $optionTypes,
                                                'class' => 'form-control selectClass',
                                                'label' => false,
                                                'empty' => 'All',
                                                'value' => $_GET['video'] ?? ''
                                            ]
                                        ); ?>
                                    </li>
                                    <li>
                                        <label>Gender</label><br>
                                        <?= $this->Form->control(
                                            'gender',
                                            [
                                                'options' => $genders,
                                                'class' => 'form-control selectClass',
                                                'label' => false,
                                                'empty' => 'All',
                                                'value' => $_GET['gender'] ?? ''
                                            ]
                                        ); ?>
                                    </li>
                                    <li>
                                        <label>Alternates</label><br>
                                        <?= $this->Form->control(
                                            'alternate',
                                            [
                                                'options' => $optionTypes,
                                                'class' => 'form-control selectClass',
                                                'label' => false,
                                                'empty' => 'All',
                                                'value' => $_GET['alternate'] ?? ''
                                            ]
                                        ); ?>
                                    </li>
                                </ul>
                            <?= $this->Form->end(); ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th style="width: 20px"><?php echo $this->Paginator->sort('id', 'Id', []); ?></th>
                                <th><?php echo $this->Paginator->sort('Cardtype.title', 'Card Type', []); ?></th>
                                <th><?php echo $this->Paginator->sort('lakota', $languageName, []); ?></th>
                                <th><?php echo $this->Paginator->sort('english', 'English', []); ?></th>
                                <th><?php echo $this->Paginator->sort('gender', 'Gender', []); ?></th>
                                <th><?php echo $this->Paginator->sort('alt_lakota', 'Alt ' . $languageName, []); ?></th>
                                <th><?php echo $this->Paginator->sort('alt_english', 'Alt English', []); ?></th>
                                <th><?php echo $this->Paginator->sort('audio', 'Audio', []); ?></th>
                                <th><?php echo $this->Paginator->sort('image.file_name', 'Image', []); ?></th>
                                <th><?php echo $this->Paginator->sort('video.file_name', 'Video', []); ?></th>
                                <th>Include in Review</th>
                                <th><?php echo $this->Paginator->sort('Inflections.headword', 'Inflection', []); ?></th>
                                <th style="width: 100px">Action</th>
                            </tr>
                            <?php foreach ($cards as $u) : ?>
                                <tr>
                                    <td>
                                        <div class="checkbox listchkbox">
                                            <label>
                                                <input type="checkbox" class="userckk"data-id="<?php echo $u->id; ?>">
                                            </label>
                                        </div>
                                    </td>
                                    <td><?php echo $u->id; ?></td>
                                    <td><?php echo $u->cardtype->title; ?></td>
                                    <td><?php echo htmlspecialchars($u->lakota, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($u->english, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $u->gender; ?></td>
                                    <td><?php echo $u->alt_lakota ? htmlspecialchars($u->alt_lakota, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></td>
                                    <td><?php echo $u->alt_english ? htmlspecialchars($u->alt_english, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></td>
<!--                                    <td><?php //echo $u->audio ? $u->audio : 'N/A'; ?></td>
                                    <td><?php //echo $u->image_id ? $u->image->file_name : 'N/A'; ?></td>
                                    <td><?php //echo $u->video_id ? $u->video->file_name : 'N/A'; ?></td>-->
                                    <td><?php echo $u->audio ? 'Available' : 'N/A'; ?></td>
                                    <td><?php echo $u->image_id ? 'Available' : 'N/A'; ?></td>
                                    <td><?php echo $u->video_id ? 'Available' : 'N/A'; ?></td>
                                    <td><?php echo $u->include_review ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $u->inflection ? $u->inflection->headword : 'N/A'; ?></td>
                                    <td>
                                        <a href="<?php echo $this->Url->build('/admin/cards/edit/' . $u->id); ?>">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="deleteCard('<?php echo $u->id; ?>')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix">
                    <div class="form-group pull-left">
                        <select class="form-control bulkaction">
                            <option value="">Bulk Action</option>
                            <option value="deletecard">Delete</option>
                        </select>
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

<div class="modal fade" id="delete_warning_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Card Delete Warning</h4>
            </div>
            <div class="modal-body">
                <div class="box-body" id="cardDeleteDiv">

                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.content -->
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
            if (action == 'deletecard') {
                if (id.length != 0) {

                 var data={'cardids':id};
                $.ajax({
                    type: "POST",
                    url: '<?php echo Router::url(
                        [
                            'controller' => 'Cards',
                            'action' => 'cardDeleteWarning',
                            'prefix' => 'Admin'
                        ],
                        true
                    ); ?>',
                    data: data,
                    success: function (res) {
                        if (res!='success') {
                            $('#cardDeleteDiv').html(res);
                            $('#delete_warning_modal').modal("show");
                        } else {
                            var data = {'ids': id, 'action': action};
                            $.ajax({
                                type: "POST",
                                url: '<?php echo Router::url(
                                    [
                                        'controller' => 'Cards',
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
                        console.log(res);
                    }
                });

                } else {
                    alert('Please select atleast one card.');
                    $(".bulkaction").val('');
                }

            }
        });

        $('.selectClass').change(function() {
            $('#cardfilter').submit();
        });
    });

    function deleteCard(cardId) {
        var r = confirm("Are you sure you want to delete this card?");
        if (r == true) {
            var data={'cardids': cardId};
            $.ajax({
                type: "POST",
                url: '<?php echo Router::url(
                    [
                        'controller' => 'Cards',
                        'action' => 'cardDeleteWarning',
                        'prefix' => 'Admin'
                    ],
                    true
                ); ?>',
                data: data,
                success: function (res) {
                    if (res != 'success') {
                        $('#cardDeleteDiv').html(res);
                        $('#delete_warning_modal').modal("show");
                    } else {
                        window.location.href='<?php echo Router::url(
                            [
                                'controller' => 'Cards',
                                'action' => 'delete',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>/'+cardId;
                    }


                    console.log(res);
                }
            });
        }
    }
<?php
$this->Html->scriptEnd();
?>
