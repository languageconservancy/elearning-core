<?php
/*
Parameters:
- $checkboxname: The name of the checkbox input.
- $type: The type of card section.
- $value: Preselected card ID.
- $blockNo: The block number.
- $responseType: The response type.
- $languageName: The name of the language.
- $idSuffix: The suffix to append to the ID of the table.
- $processTable: Whether to process the table here or not.
*/

use Cake\Log\Log;
use Cake\Core\Configure;

$languageName = Configure::read('LANGUAGE');
$checkboxname = $checkboxname ?? '';
$type = $type ?? '';
$idSuffix = $idSuffix ?? '';
$value = $value ?? '';
$blockNo = $blockNo ?? '';
$responseType = $responseType ?? '';
$tableClass = '';
$processTable = isset($processTable) ? $processTable : true;

switch ($type) {
    case 'MultipleChoiceCardSectionPromt':
    case 'MultipleChoiceCardSectionResponse':
    case 'TrueFalseCardSectionPrompt':
    case 'TrueFalseCardSectionResponse':
    case 'TrueFalseCustomPromptInput':
    case 'TrueFalseCustomResponseInput':
    case 'AnagramCard':
    case 'FillInTheBlankCardTyping':
    case 'Fill_In_The_Balnks_Custom':
    case 'recordingPromptCard':
    case 'recordingResponseCard':
    case 'Recording_Custom':
    case 'RecordingCustomSectionResponse':
    case 'MCQCustomPrompt':
    case 'MCQCustomResponse':
        $name = $checkboxname;
        $class = $checkboxname;
        break;
    case 'MultipleChoiceCardSectionOption':
        $name = $checkboxname . '[]';
        $class = $checkboxname;
        break;
    case 'MatchThePairCardSectionPrompt':
        $name = $checkboxname;
        $class = $checkboxname . ' pair-prompt-card';
        $tableClass = 'matchpairtable';
        break;
    case 'MatchThePairCardSectionResponse':
        $name = $checkboxname;
        $class = $checkboxname . ' pair-response-card';
        $tableClass = 'matchpairtable';
        break;
    case 'Match_The_Pair_Custom':
        $name = $checkboxname . '_' . $responseType . '_' . $blockNo;
        $class = $checkboxname;
        break;
    case 'MCQOptionCustom':
        $name = $checkboxname . $blockNo;
        $class = $checkboxname;
        break;
    default:
        $name = $checkboxname;
        $class = $checkboxname;
        break;
}

// This template element is used by different exercise types,
// so only define the function if it doesn't already exist.
if (!function_exists('generateTableId')) {
    function generateTableId($type, $blockNo, $responseType, $idSuffix = '')
    {
        $id = $type . 'Table';
        if (isset($blockNo)) {
            $id .= $blockNo;
        }
        if (isset($responseType)) {
            $id .= $responseType;
        }
        $id = preg_replace('/\s+/', '', $id); // Remove any unintended spaces
        $id .= $idSuffix;
        Log::debug("Table ID: $id");
        return $id;
    }
}

?>
<div style="max-height: 250px; overflow: scroll;">
<table id="<?= generateTableId($type, $blockNo, $responseType, $idSuffix); ?>"
    class="<?= $tableClass; ?> table table-bordered table-hover">
    <thead>
        <tr>
            <th></th>
            <th>ID</th>
            <th>Card Type</th>
            <th><?= $languageName; ?></th>
            <th>English</th>
            <th>Alt <?= $languageName; ?></th>
            <th>Alt English</th>
            <th>Gender</th>
            <th>Audio</th>
            <th>Image</th>
            <th>Video</th>
        </tr>
    </thead>
    <tbody>
        <!-- This is populated by DataTables -->
    </tbody>
</table>
</div>
<?php
if (!$processTable) {
    return;
}
$this->Html->scriptStart(['block' => 'scriptBottom']); ?>
$(document).ready(function () {
    processDataTable(
        '<?= generateTableId($type, $blockNo, $responseType, $idSuffix); ?>',
        '<?= $name ?? ""; ?>',
        '<?= $class ?? ""; ?>',
        <?= json_encode($value); ?>,
        <?= isset($blockNo) ? json_encode($blockNo) : "null"; ?>,
        '<?= $responseType ?? ""; ?>',
        API_ROUTES.getCardList
    );
});
<?php $this->Html->scriptEnd(); ?>
