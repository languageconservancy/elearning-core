<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Add/Edit Inflection') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Inflections</a></li>
        <li class="active">Add/Edit Inflection</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create($inflection) ?>
        <div class="box-body addcardspage">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="card-type">Reference Dictionary ID</label>
                    <?= $this->Form->control(
                        'reference_dictionary_id',
                        [
                            'options' => $dictionary,
                            'label' => false,
                            'class' => 'form-control'
                        ]
                    ) ?>
                    </div>
                    <div class="form-group">
                        <label for="headword">Headword</label>
                        <?= $this->Form->text("headword", [
                            'id' => 'headword', 'class' => 'form-control',
                            'placeholder' => 'Headword', 'value' => $inflection->headword ?? '']) ?>
                    </div>
                    <div class="form-group">
                        <label for="headword">FSTR_INEXACT</label>
                        <?= $this->Form->text('FSTR_INEXACT', [
                            'id' => 'FSTR_INEXACT', 'class' => 'form-control',
                            'placeholder' => 'FSTR_INEXACT', 'value' => $inflection->FSTR_INEXACT ?? '']) ?>
                    </div>
                    <div class="form-group">
                        <label for="GSTR">GSTR</label>
                        <?= $this->Form->text('GSTR', [
                            'id' => 'GSTR', 'class' => 'form-control',
                            'placeholder' => 'GSTR', 'value' => $inflection->GSTR ?? '']) ?>
                    </div>
                    <div class="form-group">
                        <label for="PS">PS</label>
                        <?= $this->Form->text('PS', [
                            'id' => 'FSTR_INEXACT', 'class' => 'form-control',
                            'placeholder' => 'PS', 'value' => $inflection->PS ?? '']) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="inflection_full_entry">Inflection Full Entry</label>
                        <?= $this->Form->textarea('inflection_full_entry', [
                            'id' => 'inflection_full_entry', 'rows' => 3,
                            'class' => 'form-control', 'style' => 'resize:none',
                            'value' => $inflection->inflection_full_entry ?? '']) ?>
                    </div>
                    <div class="form-group">
                        <label for="FSTR_HTML">FSTR_HTML (Unity only support b,i,size and color tag of html)</label>
                        <?= $this->Form->textarea('FSTR_HTML', [
                            'id' => 'FSTR_HTML', 'rows' => 3,
                            'class' => 'form-control', 'style' => 'resize:none',
                            'value' => $inflection->FSTR_HTML ?? '']) ?>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
$(function () {
$('select#reference-dictionary-id').select2({placeholder: 'Select an option'});
});
<?php $this->Html->scriptEnd(); ?>
