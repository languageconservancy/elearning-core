<?php
$row = $row ?? [];
$prefix = 'vbl_options[' . $index . ']';
?>
<fieldset class="vbl-content-row well">
    <legend class="vbl-row-title"><?= __('Response') ?></legend>
    <div class="form-group">
        <label for="vbl-<?= h($index) ?>-target"><?= __('Response role') ?></label>
        <select class="form-control vbl-role" id="vbl-<?= h($index) ?>-target" name="<?= h($prefix) ?>[target]">
            <option value="1" <?= ($row['card_type'] ?? '') === 'P' || !empty($row['target']) ? 'selected' : '' ?>>
                <?= __('Target / correct response (select exactly one)') ?>
            </option>
            <option value="0" <?= ($row['card_type'] ?? '') !== 'P' && empty($row['target']) ? 'selected' : '' ?>>
                <?= __('Other response / distractor') ?>
            </option>
        </select>
    </div>
    <div class="form-group" data-vbl-field="card">
        <label><?= __('Word card') ?></label>
        <select class="form-control vbl-card-picker" name="<?= h($prefix) ?>[card_id]">
            <option value=""></option>
            <?php if (!empty($row['card_id'])) { ?>
                <option value="<?= h($row['card_id']) ?>" selected>
                    <?= h($row['card_label'] ?? ('Card #' . $row['card_id'])) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <?php foreach (
    [
        'vbl_letter' => ['Letter / multi-character letter', 'text'],
        'vbl_letter_audio_id' => ['Letter pronunciation audio', 'hidden'],
        'vbl_letter_image_id' => ['Letter line-art image', 'hidden'],
        'vbl_coloring_image_id' => ['Word line-art image', 'hidden'],
        'text_option' => ['Marked word (optional): use [letter] to choose the missing letter occurrence', 'text'],
    ] as $field => $definition
) {
        $inputId = 'vbl-' . $index . '-' . $field;
        $fileType = $field === 'vbl_letter_audio_id' ? 'audio' : 'image';
    ?>
        <div class="form-group" data-vbl-field="<?= h($field) ?>">
            <label for="<?= h($inputId) ?>"><?= h(__($definition[0])) ?></label>
                <input class="form-control" type="<?= h($definition[1]) ?>"
                    id="<?= h($inputId) ?>"
                    name="<?= h($prefix) ?>[<?= h($field) ?>]" value="<?= h($row[$field] ?? '') ?>"
                    >
            <?php if ($definition[1] === 'hidden') { ?>
                <button type="button" class="btn btn-default pick-<?= h($fileType) ?>"
                    data-name="<?= h($inputId) ?>" data-toggle="modal" data-target="#fileLibrary">
                    <?= __('Pick {0}', $fileType) ?>
                </button>
                <small id="<?= h($inputId) ?>-label">
                    <?= !empty($row[$field]) ? h(__('Selected file #{0}', $row[$field])) : '' ?>
                </small>
            <?php } ?>
        </div>
    <?php } ?>
    <button type="button" class="btn btn-default vbl-remove-row"><?= __('Remove response') ?></button>
</fieldset>
