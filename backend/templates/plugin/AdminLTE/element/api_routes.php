<script>
const API_ROUTES = {
    // Exercises Controller Routes
    getExerciseList: "<?= $this->Url->build('/admin/exercises/getExerciseList'); ?>",
    getTrueFalseCardPickerHtml: "<?= $this->Url->build('/admin/exercises/getTrueFalseCardPickerHtml'); ?>",
    getMatchThePairHtml: "<?= $this->Url->build('/admin/exercises/getMatchThePairHtml'); ?>",
    // Cards Controller Routes
    getCardList: "<?= $this->Url->build('/admin/cards/getCardList'); ?>",
    getSelectedCardsForSelect2: "<?= $this->Url->build('/admin/cards/getSelectedCardsForSelect2'); ?>",
    getCardListForSelect2: "<?= $this->Url->build('/admin/cards/getCardListForSelect2'); ?>",
};
</script>