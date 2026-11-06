$(function () {
    var nextIndex = $('#vbl-content-rows .vbl-content-row').length;
    var activities = $('#vocab-builder-form').data('activities');
    var initialMode = $('#vocab-builder-form').data('mode');

    function updateResponses() {
        var activity = $('#vbl-activity').val();
        var definition = activities[activity];
        var mode = $('#vbl-mode').val();
        var active = $('#exercise_type').val() === 'vocab-builder';
        var letters = mode === 'letter' || activity === 'fill-letter';
        var help = '';
        if (definition) {
            if (activity === 'fill-letter') {
                help = 'Choose a target word card and its missing letter, then add at least two other letters. '
                    + 'Only the target needs a word card. Each letter needs its own pronunciation audio.';
            } else if (mode === 'letter') {
                help = 'Add at least ' + definition.letters + ' distinct letters with pronunciation audio. '
                    + 'No word cards are needed.';
                if (activity === 'memory-activity') {
                    help += ' Eight letters support all 8, 12 and 16-card grids.';
                }
                if (activity === 'coloring') {
                    help += ' The target letter needs a line-art image.';
                }
            } else {
                help = 'Add at least ' + definition.words + ' distinct word card(s) with language text and audio'
                    + (activity === 'match-sound' || activity === 'coloring' ? '. ' : ' and images. ')
                    + 'No letter attachments are needed.';
                if (activity === 'coloring') {
                    help += ' The target word needs a line-art image.';
                }
                if (activity === 'word-list') {
                    help += ' All cards appear in the word list; Continue completes this activity.';
                }
            }
        }
        $('#vbl-requirements').text(help).toggle(!!definition);
        $('#vbl-add-row').text(letters && activity !== 'fill-letter' ? 'Add letter response' : 'Add response');
        var reviewEnabled = active && !!definition && definition.review && mode === 'word';
        $('#vbl-review-enabled').closest('.input').toggle(reviewEnabled);
        $('#vbl-review-enabled').prop('disabled', !reviewEnabled);
        $('#vbl-content-rows .vbl-content-row').each(function (index) {
            var row = $(this);
            var target = row.find('.vbl-role').val() === '1';
            var word = mode === 'word' && (activity !== 'fill-letter' || target);
            row.find('.vbl-row-title').text((target ? 'Target' : 'Other') + ' response ' + (index + 1));
            var fields = {
                card: word,
                vbl_letter: letters,
                vbl_letter_audio_id: letters,
                vbl_letter_image_id: activity === 'coloring' && mode === 'letter' && target,
                vbl_coloring_image_id: activity === 'coloring' && mode === 'word' && target,
                text_option: activity === 'fill-letter' && target
            };
            row.find('[data-vbl-field]').each(function () {
                var visible = !!definition && fields[$(this).data('vbl-field')];
                $(this).toggle(visible).find(':input').prop('disabled', !active || !visible);
            });
        });
    }

    function updateModes() {
        var definition = activities[$('#vbl-activity').val()];
        var previous = $('#vbl-mode').val() || initialMode;
        var select = $('#vbl-mode').empty();
        if (definition) {
            $.each(definition.modes, function (value, label) {
                select.append($('<option>').val(value).text(label));
            });
            if (definition.modes[previous]) {
                select.val(previous);
            }
        }
        initialMode = null;
        updateResponses();
    }

    function initializeCardPickers() {
        $('#vbl-content-rows .vbl-card-picker').each(function () {
            if (!$(this).data('vbl-initialized')) {
                $(this).data('vbl-initialized', true);
                if (!this.id) {
                    this.id = 'vbl-card-' + nextIndex++;
                }
                var selected = $(this).val();
                setUpSelect2Element(
                    this.id,
                    API_ROUTES.getSelectedCardsForSelect2,
                    API_ROUTES.getCardListForSelect2,
                    selected ? [selected] : []
                );
            }
        });
    }

    function showActivityForm() {
        var isVbl = $('#exercise_type').val() === 'vocab-builder';
        $('#vocab-builder-form').toggle(isVbl);
        $('#vocab-builder-form :input').prop('disabled', !isVbl);
        $('#promteResponseType, #card_type').closest('.form-group').toggle(!isVbl);
        if (isVbl) {
            $('#exerciseFormBlock, #exerciseStepOneSubmit, #exerciseStepTwoSubmit').hide();
            $('#card_type').val('card');
            initializeCardPickers();
        }
        updateResponses();
    }

    $('#exercise_type').on('change', showActivityForm);
    $('#vbl-add-row').on('click', function () {
        var html = $('#vbl-row-template').html().replace(/__INDEX__/g, nextIndex++);
        $('#vbl-content-rows').append(html);
        initializeCardPickers();
        updateResponses();
    });
    $('#vbl-content-rows').on('click', '.vbl-remove-row', function () {
        $(this).closest('.vbl-content-row').remove();
        updateResponses();
    });
    $('#vbl-activity').on('change', updateModes);
    $('#vbl-mode').on('change', updateResponses);
    $('#vbl-content-rows').on('change', '.vbl-role', function () {
        if ($(this).val() === '1') {
            $('#vbl-content-rows .vbl-role').not(this).val('0');
        }
        updateResponses();
    });
    updateModes();
    showActivityForm();
});
