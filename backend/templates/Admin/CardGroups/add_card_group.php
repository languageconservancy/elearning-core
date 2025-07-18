<?php

use Cake\Routing\Router;

?>
<style>
    .selected { background: rgb(60, 141, 188); color: #fff; cursor: pointer;}
    .added { background: #ccc; pointer-events: none; }
    .selectremove { background: red; }
</style>
<section class="content-header">
    <h1><?= __('Add/Edit Card Group') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Card Groups</a></li>
        <li class="active">Add/Edit Card Group</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body addcardgrouppage">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <a class="btn btn-primary" href="<?php echo Router::url(
                            [
                                'controller' => 'CardGroups',
                                'action' => 'addCardGroup',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>"
                        >Add New Group</a>
                    </div>
                </div>
            </div>
            <?= $this->Form->create($Cardgroup) ?>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <?= $this->Form->control(
                            'name',
                            [
                                'label' => false,
                                'class' => 'form-control',
                                'placeholder' => "Name",
                                'autocomplete' => 'off'
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="group-type">Type</label>
                        <?= $this->Form->control(
                            'card_group_type_id',
                            [
                                'options' => $cardgrouptypes,
                                'label' => false,
                                'class' => 'form-control',
                                'id' => "group-type",
                                'empty' => 'Select Type'
                            ]
                        ) ?>

<!--                        <select class="form-control" name="type" id="group-type">
                            <option value="">Select</option>
                            <option value="1">Type 1</option>
                            <option value="2">Type 2</option>
                            <option value="3">Type 3</option>
                        </select>-->
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <label for="groups">Groups</label>
                    <table class="table" id="cardgroup-list-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Group Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Cardgroups as $g) { ?>
                                <tr <?php
                                if ($g->id == $Cardgroup->id) {
                                            echo 'class="activerow"';
                                }
                                ?>>
                                    <td><?php echo $g['id']; ?></td>
                                    <td><?php echo $g['name']; ?></td>
                                    <td><?php echo $g['cardgrouptype']['title']; ?></td>
                                    <td>
                                        <a href="<?php
                                            echo $this->Url->build('/admin/card-groups/edit/' . $g['id']);
                                        ?>"
                                        >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <?php echo $this->Html->link(
                                            '<i class="fa fa-trash"></i>',
                                            '/admin/card-groups/delete/' . $g['id'],
                                            [
                                                'escape' => false,
                                                'confirm' => 'Are you sure you want to delete this Group?'
                                            ]
                                        ); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            if (empty($Cardgroups)) {
                                echo '<tr><td colspan="4">No Group Added.</td></tr>';
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
                <div class="col-sm-4">
                    <label for="groups">Selected Group's Cards</label>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo $languageName;?></th>
                                <th>English</th>
                            </tr>
                        </thead>
                        <tbody id="selected_cards">
                            <?php
                            foreach ($assigncards as $card) {
                                ?>
                                <tr class="removecard" data-id="<?php echo $card['card']['id']; ?>">
                                    <td class="card_id"><?php echo $card['card']['id']; ?></td>
                                    <td>
                                    <?php echo (isset($card['card']['lakota']))
                                        ? $card['card']['lakota']
                                        : ''; ?>
                                    </td>
                                    <td>
                                    <?php echo (isset($card['card']['english']))
                                        ? $card['card']['english']
                                        : ''; ?>
                                    </td>
                                <?= $this->Form->hidden('cardid[]', [
                                    'value' => $card['card']['id']]); ?>
                            </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <button type="button" class="btn btn-default addcard">
                            <i class="fa fa-chevron-left"></i>&nbsp;Add Card
                        </button>
                        <button type="button" class="btn btn-danger cardremove">Remove Card&nbsp;
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="selected-details" style="display: none;">
                        <label>Selected Card Details</label>
                        <ul class="list-group">
                            <li class="list-group-item">ID : <span id="cardid"></span></li>
                            <li class="list-group-item">Type: <span id="cardtype"></span></li>
                            <li class="list-group-item"><?php echo $languageName;?> :<span id="cardlakota"></span></li>
                            <li class="list-group-item">English : <span id="cardenglish"></span></li>
                            <li class="list-group-item">Gender : <span id="cardgender"></span></li>
                            <li class="list-group-item">Alternate English:  <span id="cardAEnglish"></span></li>
                            <li class="list-group-item">Alternate <?php echo $languageName;?>:  <span id="cardAlakota">
                                </span></li>
                            <li class="list-group-item">Metadata: <span id="cardmetadata"></span></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="groups">Cards</label>
                    <div class="scroll-table" >
                        <table class="table" id="card-list-table">
                        <thead>
                            <tr>
                                <th><?php echo $this->Paginator->sort('id', 'ID', array()); ?></th>
                                <th><?php echo $this->Paginator->sort('lakota', $languageName, array()); ?></th>
                                <th><?php echo $this->Paginator->sort('english', 'English', array()); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cards as $u) {
                                ?>
                                <tr class="singlecard <?php
                                if (in_array($u->id, $cardids)) {
                                    echo 'added';
                                }
                                ?>" data-id="<?php echo $u->id; ?>">
                                    <td class="card_id"><?php echo $u->id; ?></td>
                                    <td><?php echo (isset($u->lakota)) ? $u->lakota : ''; ?></td>

                                    <td><?php echo (isset($u->english)) ? $u->english : ''; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>

                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</section>
<?php
echo $this->Html->css('AdminLTE./bower_components/datatables.net-bs/css/dataTables.bootstrap.min');
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net/js/jquery.dataTables.min',
    ['block' => 'script']
);
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net-bs/js/dataTables.bootstrap.min',
    ['block' => 'script']
);
?>
<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
//<script>
?>

 <?php if (!empty($cards)) { ?>
$('#card-list-table').DataTable({
        'searching': true,
        'paging': false,
        'lengthChange': false,
        'ordering': true,
        'info': false,
        'autoWidth': false,
        'scrollY': "200px",
        'dom': '<"top"i>frt<"bottom"lp><"clear">'
    });
 <?php } if (!empty($Cardgroups)) { ?>
    $('#cardgroup-list-table').DataTable({
        'searching': true,
        'paging': false,
        'lengthChange': false,
        'ordering': true,
        'info': false,
        'autoWidth': false,
        'scrollY': "200px",
        'dom': '<"top"i>frt<"bottom"lp><"clear">'
    });
 <?php } ?>
    setTimeout(function function_name(argument) {
        $('#cardgroup-list-table tbody tr').each(function (index, el) {
            if ($(this).hasClass('activerow')) {
                var top = ($(".activerow").offset().top - $(window).height() + 150);
                if (top < 0) {
                    top = 0;
                }
                $('#cardgroup-list-table_wrapper .dataTables_scrollBody').animate({
                    scrollTop: top
                }, 3000);
            }
        });
    }, 500);

    $(function () {
        $(".singlecard").click(function () {
            var html = $(this).html();
            if ($(this).hasClass("selected"))
            {
                $(this).removeClass("selected");
            } else {
                $(this).addClass("selected");
            }
        });

        $(document).on("click", ".removecard", function () {
            var html = $(this).html();
            if ($(this).hasClass("selectremove"))
            {
                $(this).removeClass("selectremove");
            } else {
                $(this).addClass("selectremove");
            }

            /*show Details*/
            var count=$('.selectremove').size();
            if(count==1)
            {
                $('.selected-details').slideDown();
                var cardid=$('.selectremove').data('id');
                var data = {'id': cardid};
                    $.ajax({
                        type: "POST",
                        url: '<?php echo Router::url(
                            [
                                'controller' => 'CardGroups',
                                'action' => 'getCard',
                                'prefix' => 'Admin'
                            ],
                            true
                        ); ?>',
                        data: data,
                        success: function (res) {
                            var result = JSON.parse(res);
                            $('#cardid').html(result.id);
                            $('#cardtype').html(result.type);
                            $('#cardlakota').html(result.lakota);
                            $('#cardenglish').html(result.english);
                            $('#cardgender').html(result.gender);
                            $('#cardAEnglish').html(result.alternate_english);
                            $('#cardAlakota').html(result.alternate_lakota);
                            $('#cardmetadata').html(result.metadata);

                        }
                    });
            }else{
                $('.selected-details').slideUp();
            }
        });

        $(".addcard").click(function () {
            var action = 'add';
            var id = [];
            $(".singlecard").each(function (index) {
//$(this).removeClass("added");
                if ($(this).hasClass("selected")) {
//alert($(this).find(".card_id").html());
                    id.push("<tr class='removecard' data-id='" + $(this).find(".card_id").html() + "'>"
                        + $(this).html() + "<input name='cardid[]' type='hidden' value='"
                        + $(this).find(".card_id").html() + "'></tr>");
                    $(this).removeClass("selected");
                    $(this).addClass("added");
                }
            });
            $("#selected_cards").append(id);
        });

        $(".cardremove").click(function () {
            $('.selected-details').slideUp();
            var action = 'add';
            var id = [];
            $(".removecard").each(function (index) {
                if ($(this).hasClass("selectremove")) {
                    $(this).remove();
                    var dataId = $(this).data("id");
                    $(".singlecard").each(function (index, element) {
                        if ($(this).hasClass("added") && $(this).data("id") == dataId) {
                            $(this).removeClass("added");
                        }
                    });
                }
            });
        });
    });

<?php
//</script>
$this->Html->scriptEnd();
?>
