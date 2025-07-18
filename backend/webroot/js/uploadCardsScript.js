
$(function () {
    function toggleCheckboxes(masterCheckbox, checkboxes) {
        const isChecked = $(masterCheckbox).is(':checked');
        checkboxes.forEach(function(checkboxName) {
            $(`input[name="${checkboxName}"]`).prop('checked', isChecked);
        });
    }

    // Single event listener for all checkbox groups
    $('body').on('click', 'input[type="checkbox"]', function (event) {
        const name = $(this).attr('name');

        if (name === 'all_card_type_id_updated_checkboxes') {
            toggleCheckboxes(this, ['card_type_idIsUpdated[]']);

        } else if (name === 'all_lakota_updated_checkboxes') {
            toggleCheckboxes(this, ['lakotaIsUpdated[]']);

        } else if (name === 'all_english_updated_checkboxes') {
            toggleCheckboxes(this, ['englishIsUpdated[]']);

        } else if (name === 'all_gender_updated_checkboxes') {
            toggleCheckboxes(this, ['genderIsUpdated[]']);

        } else if (name === 'all_alt_lakota_updated_checkboxes') {
            toggleCheckboxes(this, ['alt_lakotaIsUpdated[]']);
        } else if (name === 'all_alt_lakota_added_checkboxes') {
            toggleCheckboxes(this, ['alt_lakotaIsAdded[]']);
        } else if (name === 'all_alt_lakota_removed_checkboxes') {
            toggleCheckboxes(this, ['alt_lakotaIsRemoved[]']);

        } else if (name === 'all_alt_english_updated_checkboxes') {
            toggleCheckboxes(this, ['alt_englishIsUpdated[]']);
        } else if (name === 'all_alt_english_added_checkboxes') {
            toggleCheckboxes(this, ['alt_englishIsAdded[]']);
        } else if (name === 'all_alt_english_removed_checkboxes') {
            toggleCheckboxes(this, ['alt_englishIsRemoved[]']);

        } else if (name === 'all_audio_updated_checkboxes') {
            toggleCheckboxes(this, ['audioIsUpdated[]']);
        } else if (name === 'all_audio_added_checkboxes') {
            toggleCheckboxes(this, ['audioIsAdded[]']);
        } else if (name === 'all_audio_removed_checkboxes') {
            toggleCheckboxes(this, ['audioIsRemoved[]']);

        } else if (name === 'all_image_id_updated_checkboxes') {
            toggleCheckboxes(this, ['image_idIsUpdated[]']);
        } else if (name === 'all_image_id_added_checkboxes') {
            toggleCheckboxes(this, ['image_idIsAdded[]']);
        } else if (name === 'all_image_id_removed_checkboxes') {
            toggleCheckboxes(this, ['image_idIsRemoved[]']);

        } else if (name === 'all_video_id_updated_checkboxes') {
            toggleCheckboxes(this, ['video_idIsUpdated[]']);
        } else if (name === 'all_video_id_added_checkboxes') {
            toggleCheckboxes(this, ['video_idIsAdded[]']);
        } else if (name === 'all_video_id_removed_checkboxes') {
            toggleCheckboxes(this, ['video_idIsRemoved[]']);

        } else if (name === 'all_include_review_updated_checkboxes') {
            toggleCheckboxes(this, ['include_reviewIsUpdated[]']);
        }
    });
});