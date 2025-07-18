<!--
Parameters:
'id' // ID of the select element
'name' // Name of the select element
'preselectedCardIds' // Preselected cards

Example:
<?php
// $this->element('select2CardList', [
//    'id' => 'cardList',
//    'name' => 'data[cardList]',
//    'preselectedCardIds' => $preselectedCardIds
// ])
?>
-->

<select class="form-control" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>" multiple="multiple">
    <!-- Options populated by Select2 -->
</select>

<?php $this->Html->scriptStart(['block' => 'scriptBottom']); ?>
$(document).ready(function () {
    const selectId = "<?= htmlspecialchars($id) ?>";
    const getSelectedCardsUrl = API_ROUTES.getSelectedCardsForSelect2;
    const getCardListUrl = API_ROUTES.getCardListForSelect2;
    const preselectedCardIds = <?= json_encode($preselectedCardIds) ?>;

    setUpSelect2Element(selectId, getSelectedCardsUrl, getCardListUrl, preselectedCardIds);
});
<?php $this->Html->scriptEnd(); ?>
