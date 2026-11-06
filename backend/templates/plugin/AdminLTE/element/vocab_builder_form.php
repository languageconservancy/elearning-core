<?php
$rows = $this->getRequest()->getData('vbl_options');
if (!is_array($rows)) {
    $rows = [];
    if ($exercise->exercise_type === 'vocab-builder') {
        foreach ($exercise->exerciseoptions as $option) {
            $rows[] = $option->toArray();
        }
    }
}
if (!$rows) {
    $rows = [['target' => true]];
}
$activities = [];
foreach (\App\Lib\VocabBuilder::ACTIVITIES as $key => $definition) {
    $activities[$key] = $definition + ['modes' => \App\Lib\VocabBuilder::modes($key)];
}
$mode = $this->getRequest()->getData('vbl_mode') ?? $exercise->vbl_mode;
if (!$mode && isset($activities[$exercise->vbl_activity ?? ''])) {
    $mode = array_key_first($activities[$exercise->vbl_activity]['modes']);
}
?>
<div id="vocab-builder-form" style="display: none"
    data-activities="<?= h(json_encode($activities)) ?>" data-mode="<?= h($mode ?? '') ?>">
    <h3><?= __('Vocab Builder content') ?></h3>
    <p><?= __(
        'Choose an activity, then its word or letter version where supported. '
        . 'Select one target response and add the other responses needed to play.'
    ) ?></p>
    <?= $this->Form->control('vbl_activity', [
        'label' => __('Activity'), 'type' => 'select', 'options' => $vblActivities,
        'empty' => __('Select activity'), 'class' => 'form-control',
    ]) ?>
    <div class="form-group">
        <label for="vbl-mode"><?= __('Version / response type') ?></label>
        <select id="vbl-mode" name="vbl_mode" class="form-control"></select>
    </div>
    <p id="vbl-requirements" class="alert alert-info" aria-live="polite"></p>
    <?= $this->Form->control('vbl_review_enabled', [
        'type' => 'checkbox',
        'label' => __(
            'Allow generated word review from this pool. Completion counts as practice, not a measured skill score.'
        ),
    ]) ?>
    <div id="vbl-content-rows">
        <?php foreach (array_values($rows) as $index => $row) {
            echo $this->element('vocab_builder_row', compact('index', 'row'));
        } ?>
    </div>
    <template id="vbl-row-template">
        <?= $this->element('vocab_builder_row', ['index' => '__INDEX__', 'row' => []]) ?>
    </template>
    <button type="button" class="btn btn-default" id="vbl-add-row"><?= __('Add response') ?></button>
    <button type="button" class="btn btn-primary" onclick="validateForm()"><?= __('Save activity') ?></button>
</div>
