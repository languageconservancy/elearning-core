
$(function () {
//     $('body').on('click', function (event) {
//         console.log("Clicked body");
//         var element = event.target;
//         console.log(element);
//         if (element.matches('.prompt_card_id')) {
//             setPromptCardValueAndPreview();
//         } else if (element.matches('.response_card_id')) {
//             setResponseCardValueAndPreview();
//         }
//     });
// });
    //pagination script Start
    $('body').on('click', '.paginatepre', function (event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type, keyword, page - 1);
    });
    $('body').on('click', '.paginatenext', function (event) {
        var page = $(this).data('page');
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type, keyword, page + 1);
    });
    $('body').on('click', '.pick-image', function (event) {
        var page = 1;
        var keyword = '';
        var type = 'image';
        $('#typebox').val(type);
        $('#inputType').val($(this).data('name'));
        getFiles(type, keyword, page);
    });
    $('body').on('click', '.pick-audio', function (event) {
        var page = 1;
        var keyword = '';
        var type = 'audio';
        $('#typebox').val(type);
        $('#inputType').val($(this).data('name'));
        getFiles(type, keyword, page);
    });
    //pagination script End

    /*Exercise Event Start*/

    // Recording Card Function Start
    $('body').on('click', '.recording_prompt_card', function (event) {
        previewPromptRecordingCard();
    });
    $('body').on('click', '.recordingprompttype', function (event) {
        previewPromptRecordingCard();
    });
    $('body').on('click', '.recording_response_card', function (event) {
        previewResponseRecordingCard();
    });
    // Recording Card Function End

    // Recording Group Function Start
    $('#recordingPromptGroupCardId').change(function () {
        getRecordingCardListByGroup();
        RecordingPromptGroupPreview();
    });
    $('#recordingPromptGroupGroupId').change(function () {
        getRecordingCardListByGroup();
        RecordingPromptGroupPreview();
    });
    $('body').on('click', '.recording_group_cardcheck', function (event) {
        RecordingPromptGroupPreview();
    });
    $('body').on('click', '.recordingpromptgrouptype', function (event) {
        RecordingPromptGroupPreview();
    });
    // Recording Group Function End

    // Recording Custom Function Start
    $('body').on('change', '#recording_custom_prompt_type', function (event) {
        getRecordingCustomHtml();
    });
    $('body').on('click', '.Recording_CustomRadio', function (event) {
        setRecordingCustomPreview();
    });
    $('body').on('click', '.Recording_Custom', function (event) {
        setRecordingCustomPreview();
    });
    $('body').on('blur', '#Recording', function (event) {
        setRecordingCustomPreview();
    });
    // Recording Custom Function Start

    // Fill In The blank Card Function Start
    $('body').on('click', '.fill_in_the_blank_card_typing', function (event) {
        setFillInTheBlankPreview('change');
    });
    $('body').on('click', '.FillInTheBlankCardPromptType', function (event) {
        setFillInTheBlankPreview(null);
    });
    $('body').on('keyup', '#fill_blank_lakota', function (event) {
        setFillPreview();
    });
    $('#fill_in_the_blank_type').change(function () {
        renderOptionHtmlAndSet();
    });
    $('body').on('blur', '#option_counter', function (event) {
        generateAndSetOptionHtml();
    });
    $('body').on('focus', '.fill_option', function (event) {
        if (VirtualKeyboard.isOpen()) {
            VirtualKeyboard.hide();
        }
        if (!$(this).attr("readonly")) {
            VirtualKeyboard.show(this.id, 'keyboard');
            VirtualKeyboard.attachInput(this);
        }
    });
    // Fill In The blank Card Function End

    //Fill In The blank Custom Function Start
    $('body').on('change', '#fill_in_the_blanks_prompt_type', function (event) {
        getFillInTheBlanksCustomHtml();
    });
    $('body').on('blur', '#Fill_In_The_Blanks', function (event) {
        setFillInTheBalnksCustomOption();
    });
    $('body').on('click', '.Fill_In_The_Balnks_Custom', function (event) {
        setFillInTheBalnksCustomOption(true);
    });
    $('body').on('click', '.Fill_In_The_Balnks_CustomRadio', function (event) {
        setFillInTheBalnksCustomOption(false);
    });
    $('body').on('keyup', '#fill_blank_custom_lakota', function (event) {
        setFillInTheBalnksCustomPreviewOption();
    });
    //Fill In The blank Custom Function End

    // Anagram Group Function Start
    $('#anagram_group_group_id').change(function () {
        getAnagramGroupCheckboxOnchange();
    });
    $('#AnagramGroupCardId').change(function () {
        setAnagramGroupPreview();
    });
    $('body').on('click', '.anagram_group_cardcheck', function (event) {
        setAnagramGroupPreview();
    });
    $('body').on('click', '.anagramGroupPromptType', function (event) {
        setAnagramGroupPreview();
    });
    $('body').on('click', '.anagramGroupResponseType', function (event) {
        setAnagramGroupPreview();
    });
    // Anagram Group Function End

    // Anagram Card Function Start
    $('body').on('click', '.anagramCardPromptType', function (event) {
        previewAnagramCard();
    });
    $('body').on('click', '.anagramCardResponseType', function (event) {
        previewAnagramCard();
    });
    $('body').on('click', '.anagram_card', function (event) {
        previewAnagramCard();
    });
    // Anagram Card Function End

    // True False Group Function Start
    $('#true_false_prompt_group_id').change(function () {
        getCardByGroupIds($(this).val(), null, 'TrueFalseGruopPromptCardCheckbox', 'change');
        TrueFalseGroupPromptPreview();
        TrueFalseGroupResponsePreview();
    });
    $('#true_false_prompt_group_card_id').change(function () {
        TrueFalseGroupPromptPreview();
        TrueFalseGroupResponsePreview();
    });
    $('body').on('click', '.truefalsegruoppromptcheck', function (event) {
        TrueFalseGroupPromptPreview();
        TrueFalseGroupResponsePreview();
    });
    $('body').on('click', '.TrueFalseGruopPromptRadio', function (event) {
        TrueFalseGroupPromptPreview();
    });
    $('body').on('click', '.TrueFalseGruopResponseRadio', function (event) {
        TrueFalseGroupResponsePreview();
    });
    // True False Group Function End

    //True False Custom Function Start
    $('#true_false_prompt_type').change(function () {
        generateTruefalseHtml('TrueFalseCustomPromptInput', 'trueFalsePromptForm', $(this).val(), 'prompt');
    });
    $('#true_false_response_type').change(function () {
        generateTruefalseHtml('TrueFalseCustomResponseInput', 'trueFalseResponseForm', $(this).val(), 'response');
    });
    $('body').on('click', '.TrueFalseCustomPromptInput', function (event) {
        TrueFalseCustomPromptPreview();
    });
    $('body').on('click', '.TrueFalseCustomPromptInputRadio', function (event) {
        TrueFalseCustomPromptPreview();
    });
    $('body').on('click', '.TrueFalseCustomResponseInput', function (event) {
        TrueFalseCustomResponsePreview();
    });
    $('body').on('click', '.TrueFalseCustomResponseInputRadio', function (event) {
        TrueFalseCustomResponsePreview();
    });
    $('body').on('blur', '#TrueFalseCustomPromptInput', function (event) {
        TrueFalseCustomPromptPreview();
    });
    $('body').on('blur', '#TrueFalseCustomResponseInput', function (event) {
        TrueFalseCustomResponsePreview();
    });
    //True False Custom Function End

    //True False Card Function Start
    $('body').on('click', '.true_false_prompt_card_id', function (event) {
        TrueFalsePromptPreview();
    });
    $('body').on('click', '.TrueFalsePromptRadio', function (event) {
        TrueFalsePromptPreview();
    });
    $('body').on('click', '.true_false_response_card_id', function (event) {
        TrueFalseResponsePreview();
    });
    $('body').on('click', '.TrueFalseResponseRadio', function (event) {
        TrueFalseResponsePreview();
    });
    $('body').on('click', '#truefalsesamecard', function (event) {
        if ($(this).is(":checked")) {
            var prompt_card_id = $("input[name='true_false_prompt_card_id']:checked").map(function () {
                return $(this).val();
            }).get();
            var obj1 = $("input[name='true_false_response_card_id'][value=" + prompt_card_id[0] + "]");
            obj1.prop('checked', true);
            $('#response_card_type_true_false').val('Y');
        }
    });
    //True False Card Function End

    //Match The Pair Custom Function Start
    $('#MatchThePairCustomNo').blur(function () {
        getMatchThePairCustomHtmlForm();
        setMatchThePairCustomHtmlElement();
    });
    $('body').on('change', '.customResponse', function (event) {
        var block = $(this).data('block');
        var type = $(this).data('type');
        var ElementType = $(this).val();
        getCustomElement(block, type, ElementType);
    });
    $('body').on('change', '.customPrompt', function (event) {
        var block = $(this).data('block');
        var type = $(this).data('type');
        var ElementType = $(this).val();
        getCustomElement(block, type, ElementType);
    });
    $('body').on('click', '.Match_The_Pair_Custom', function (event) {
        var block = $(this).data('block');
        var type = $(this).data('type');
        getMatchThePairPreview(block, type);
    });
    $('body').on('click', '.Match_The_Pair_CustomRadioPrompt', function (event) {
        var block = $(this).data('block');
        var type = $(this).data('type');
        getMatchThePairPreview(block, type);
    });
    $('body').on('click', '.Match_The_Pair_CustomRadioResponse', function (event) {
        var block = $(this).data('block');
        var type = $(this).data('type');
        getMatchThePairPreview(block, type);
    });
    $('body').on('keyup', '.htmltextarea', function (event) {
        var block = $(this).data('block');
        var type = $(this).data('type');
        getMatchThePairPreview(block, type);
    });
    //Match The Pair Custom Function End


    //Match The Pair Group Function Start
    $('#MatchThePairPromptGroupCardId').change(function () {
        getMatchThePairCardList();
        MatchThePairGroupPromptPreview();
    });
    $('#MatchThePairPromptGroupGroupId').change(function () {
        getMatchThePairCardList()
    });
    $('.optiongroupcheck').change(function () {
        getMatchThePairCardList();
        MatchThePairGroupPromptPreview();
    });
    $('body').on('click', '.optiongroupcheck', function (event) {
        MatchThePairGroupPromptPreview();
    });
    $('body').on('click', '.matchthepairpromptgrouptype', function (event) {
        MatchThePairGroupPromptPreview();
    });
    $('body').on('click', '.matchthepairresponsegrouptype', function (event) {
        MatchThePairGroupPromptPreview();
    });
    //Match The Pair Group Function End

    //Match The Pair Card Function Start
    $('#MatchThePairCardNo').change(function () {
        getMatchThePairCHtml();
    });
    $('body').on('click', '.pair-prompt-card', function (event) {
        setMatchThePairPromptPreview($(this));
    });
    $('body').on('click', '.MatchThePairPromptRadio', function (event) {
        var name = $(this).attr("name");
        var count = name.substr(name.length - 3, 1);
        var obj = $("input[name='match_the_pair_prompt_card_id" + count + "']:checked");
        setMatchThePairPromptPreview(obj);
    });
    $('body').on('click', '.pair-response-card', function (event) {
        setMatchThePairResponsePreview($(this));
    });
    $('body').on('click', '.MatchThePairResponseRadio', function (event) {
        var name = $(this).attr("name");
        var count = name.substr(name.length - 3, 1);
        var obj = $("input[name='match_the_pair_response_card_id" + count + "']:checked");
        setMatchThePairResponsePreview(obj);
    });
    $('body').on('click', '.useprompt', function (event) {
        var i = $(this).data('id'); // prompt/response number
        var checkedPromptCard = $("input[name='match_the_pair_prompt_card_id" + i + "']:checked");
        var promptval = obj.val();
        var obj1 = $("input[name='match_the_pair_response_card_id" + i + "'][value=" + promptval + "]");
        obj1.prop('checked', true);
        setMatchThePairResponsePreview(obj1);
    });
    //Match The Pair Card Function End

    //Multiple Choise Card Function Start
    $('.promoteradio').change(function () {
        setPromptCardValueAndPreview();
    });
    $('.responseradio').change(function () {
        setResponseCardValueAndPreview();
        setResponseOptionValueAndPreview();
    });
    $('#card_id').change(function () {
        setResponseOptionValueAndPreview();
    });
    $('#card_group_id').change(function () {
        setResponseOptionValueAndPreview();
    });
    $('body').on('click', '.prompt_card_id', function (event) {
        setPromptCardValueAndPreview();
    });
    $('body').on('click', '.response_card_id', function (event) {
        setResponseCardValueAndPreview();
    });
    //Multiple Choise Card Function End

    //Multiple Choise Group Function Start
    $('#promptGroupGroupId').change(function () {
        getPromoteCardCheckbox('change');
    });
    $('.prompgroupttype').change(function () {
        promoteCardGroupPreview();
    });
    $('.responsegrouptype').change(function () {
        promoteCardGroupPreview();
        optionCardGroupPreview();
    });
    $('#OptionMultiCardGroupPicker').change(function () {
        getOptionCardCheckbox('change');
    });
    $('#group_card_option_id').change(function () {
        optionCardGroupPreview();
    });
    $('#promptGroupCardId').change(function () {
        promoteCardGroupPreview();
        getPromoteCardCheckbox('change');
    });
    //Multiple Choise Group Function End


    //Multiple Choise Custom Function Start
    $('body').on('change', '#mcq_custom_prompt_type', function (event) {
        var type = $(this).val();
        getMCQCustomHtml(type, 'McqCustomPromptForm', null);
    });
    $('body').on('change', '#mcq_custom_response_type', function (event) {
        var type = $(this).val();
        getMCQCustomHtml(type, 'McqCustomForm', null);
    });
    $('body').on('blur', '#mcq_custom_option_no', function (event) {
        var type = $(this).val();
        getMCQCustomTypeOptionHtml(type);
        customMCQOptionPreview();
        //alert('hth');
    });
    $('body').on('change', '.customMCQOption', function (event) {
        var type = $(this).val();
        var optionBlock = $(this).data('block');
        getMCQCustomOptionHtml(type, 'Mcq_custom_OptioElement' + optionBlock, optionBlock, null);
        customMCQOptionPreview();
    });

    $('body').on('click', '.MCQCustomPrompt', function (event) {
        customMCQPreview('Prompt');
    });
    $('body').on('click', '.MCQCustomPromptRadio', function (event) {
        customMCQPreview('Prompt');
    });
    $('body').on('blur', '#MCQCustomPrompt', function (event) {
        customMCQPreview('Prompt');
    });

    $('body').on('click', '.MCQCustomResponse', function (event) {
        customMCQPreview('Response');
    });
    $('body').on('click', '.MCQCustomResponseRadio', function (event) {
        customMCQPreview('Response');
    });
    $('body').on('blur', '#MCQCustomResponse', function (event) {
        customMCQPreview('Response');
    });

    $('body').on('click', '.MCQOptionCustom', function (event) {
        customMCQOptionPreview();
    });
    $('body').on('click', '.MCQOptionCustomRadio', function (event) {
        customMCQOptionPreview();
    });
    $('body').on('keyup', '.OptionCustomTextarea', function (event) {
        customMCQOptionPreview();
    });
    //Multiple Choise Custom Function End


    /*Exercise Event End*/

    //General Function Start
    $('#searchKeyword').click(function (event) {
        var keyword = $('#search').val();
        var type = $('#typebox').val();
        getFiles(type, keyword);
    });
    $('#promteResponseType').change(function () {
        var promteResponseValue = $('#promteResponseType').val();
        var cardType = $('#card_type').val();
        var exercise_type = $('#exercise_type').val();
        setRadioButtonValue();

        setTimeout(function () {
            if (cardType == 'card' && exercise_type == 'multiple-choice') {
                setPromptCardValueAndPreview();
                setResponseCardValueAndPreview();
                setResponseOptionValueAndPreview();
            }
            else if (cardType == 'card_group' && exercise_type == 'multiple-choice') {
                promoteCardGroupPreview();
                optionCardGroupPreview();
            }
            else if (cardType == 'card' && exercise_type == 'match-the-pair') {
                setMatchThePairPreview();
            }
            else if (cardType == 'card_group' && exercise_type == 'match-the-pair') {
                MatchThePairGroupPromptPreview();
            }
            else if (cardType == 'custom' && exercise_type == 'truefalse') {
                setTrueFalseCustomValue();
            }
            else if (cardType == 'card' && exercise_type == 'truefalse') {
                TrueFalsePromptPreview();
                TrueFalseResponsePreview();
            }
            else if (cardType == 'card' && exercise_type == 'anagram') {
                previewAnagramCard();
            }
            else if (cardType == 'card_group' && exercise_type == 'anagram') {
                previewAnagramCard();
            }

            /*Type*/
            var lastChar = promteResponseValue[promteResponseValue.length - 1];
            if (lastChar == 'r') {
                $('#exercise_type').val("");
                $('#exercise_type option').hide();
                $('#exercise_type option[value=recording]').show();
                $('#exercise_type').val("recording");
                $('#exerciseFormBlock').hide();
                $('#exerciseStepOneSubmit').show();
                $('#exerciseStepTwoSubmit').hide();
            }
            else
            {
                //$('#exercise_type').val("");
                $('#exercise_type option').show();
                $('#exercise_type option[value=recording]').hide();
            }
            filterOption();
        }, 1500);
    });
    $('#card_type').change(function () {
        $('#exerciseFormBlock').hide();
        $('#exerciseStepOneSubmit').show();
        $('#exerciseStepTwoSubmit').hide();
//        showHideFormBlock();
//        setRadioButtonValue();
    });
    $('#exercise_type').change(function () {
        $('#exerciseFormBlock').hide();
        $('#exerciseStepOneSubmit').show();
        $('#exerciseStepTwoSubmit').hide();
        $('#card_type').val('');
        filterOption();
//        showHideFormBlock();
//        setRadioButtonValue();
    });

    $('body').on('click', '.promotegroupcheck', function (event) {
        promoteCardGroupPreview();
    });
    $('body').on('click', '.optiongroupcheck', function (event) {
        optionCardGroupPreview();
    });
    $('body').on('click', '#select-file-btn', function (event) {
        var fileID = '';
        $('.file-res').each(function (index, el) {
            if ($(this).is(':checked')) {
                fileID = $(this).data('id');
            }
        });
        var inputType = $('#inputType').val();
        SetFile(fileID, inputType);
    });
    $("<style>").text(".fstElement {font-size:10px;}").appendTo("head");
    var $select = $('.fastselect');
    $('.fastselect').fastselect();
    $(document).on("change", '.fastselect', function () {
        $("#cardids").val($('.fastselect').val());
    })
    //General Function End*/
    setEditValue();
});

//Multiple Choise Custom Function Start
function getMCQCustomHtml(htmlType, htmlDivId, callback, cardId = null) {
    let inputName = '';
    if (htmlDivId == 'McqCustomForm') {
        inputName = 'MCQCustomResponse';
        customMCQPreview('Response');
    }
    else {
        inputName = 'MCQCustomPrompt';
        customMCQPreview('Prompt');
    }

    if (htmlType == 'card') {
        addCardPickerHtml(htmlDivId, inputName, inputName, inputName, cardId, '', '', () => {
            setRadioButtonValue();
        });
    } else if (htmlType == 'html') {
        var customhtml = '' +
        '<div class="col-sm-6">' +
            'Html (Unity only support b,i,size and color tag of html) ' +
            '<textarea class="form-control" name="' + inputName + '" id="' + inputName + '">' +
            '</textarea>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<button type="button" class="btn btn-default pick-image" data-toggle="modal" ' +
                'data-target="#fileLibrary" data-name="' + inputName + '">' +
                'Choose Image' +
            '</button><br><br>' +
            '<button type="button" class="btn btn-default pick-audio" data-toggle="modal" ' +
                'data-target="#fileLibrary" data-name="' + inputName + '">' +
                'Choose Audio' +
            '</button>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<img id="' + inputName + '_imagePreview" height="70" src="" class="customimage"><br><br>' +
            '<audio controls id="' + inputName + 'audioPreview" class="customaudio" style="display: none;">' +
                '<source class="' + inputName + '_audio-source" type="audio/mpeg">' +
                '<source class="' + inputName + '_audio-source"  type="audio/ogg">' +
                '<source class="' + inputName + '_audio-source" type="audio/wav">' +
                'Your browser does not support the audio element.' +
            '</audio>' +
            '<input type="hidden" id="' + inputName + '_image_id" name="' + inputName + '_image_id">' +
            '<input type="hidden" id="' + inputName + '_audio_id" name="' + inputName + '_audio_id">' +
        '</div>';
        $("#" + htmlDivId).html(customhtml);
    }
    else {
        $("#" + htmlDivId).html('');
    }

}
function getMCQCustomOptionHtml(contentType, htmlDivId, block, eventType, cardId) {
    var inputName = 'MCQOptionCustom';
    if (contentType == 'card') {
        addCardPickerHtml(htmlDivId, inputName, inputName, inputName, cardId, block, '', () => {
            if (eventType == 'edit') {
                setRadioButtonValue();
            } else {
                var promteResponseValue = $('#promteResponseType').val();
                var promteResponseArr = promteResponseValue.split("-");
                var responseType = promteResponseArr[1];
                $("input[name='MCQOptionCustom" + block + "Radio[]']").prop('checked', false).prop('disabled', "");
                $("input[name='MCQOptionCustom" + block + "Radio[]'][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
            }
        });
    }
    else if (contentType == 'html') {
        inputName = 'MCQOptionCustom' + block;
        var customhtml = '' +
        '<div class="col-sm-6">' +
            'Html (Unity only support b,i,size and color tag of html) ' +
            '<textarea class="form-control OptionCustomTextarea" name="' +
            inputName + '" id="' + inputName + '">' +
            '</textarea>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<button type="button" class="btn btn-default pick-image" ' +
            'data-toggle="modal" data-target="#fileLibrary" data-name="' +
            inputName + '">' +
                'Choose Image' +
            '</button><br><br>' +
            '<button type="button" class="btn btn-default pick-audio" ' +
            'data-toggle="modal" data-target="#fileLibrary" data-name="' +
            inputName + '">' +
                'Choose Audio' +
            '</button>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<img id="' + inputName + '_imagePreview" height="70" src="" class="customimage">' +
            '<br><br>' +
            '<audio controls id="' + inputName + 'audioPreview" class="customaudio" style="display: none;">' +
                '<source class="' + inputName + '_audio-source" type="audio/mpeg">' +
                '<source class="' + inputName + '_audio-source"  type="audio/ogg">' +
                '<source class="' + inputName + '_audio-source" type="audio/wav">' +
                'Your browser does not support the audio element.' +
            '</audio>' +
            '<input type="hidden" id="' + inputName + '_image_id" name="' + inputName + '_image_id">' +
            '<input type="hidden" id="' + inputName + '_audio_id" name="' + inputName + '_audio_id">' +
        '</div>';
        $("#" + htmlDivId).html(customhtml);
    }
    else {
        $("#" + htmlDivId).html('');
    }
}
function getMCQCustomTypeOptionHtml(noOfElement, mode = null) {
    var htmlContent = '';
    for (var i = 1; i <= noOfElement; i++) {
        htmlContent += '' +
        '<div class="row mcqcustomoptionelement">' +
            '<div class="col-sm-2">' +
                '<label>Option #' + i +'</label>' +
            '</div>' +
            '<div class="col-sm-6">' +
                '<select name="Mcq_custom_Option_type' + i +
                '" class="form-control customMCQOption" id="Mcq_custom_Option_type' +
                i + '" data-block="' + i + '" data-type="Prompt">' +
                    '<option value="" selected="selected">Select Type</option>' +
                    '<option value="card">Card</option>' +
                    '<option value="html">HTML</option>' +
                '</select>' +
            '</div>' +
            '<div class="col-sm-12 custom-html-element" id="Mcq_custom_OptioElement' + i + '">' +
            '</div>' +
        '</div></div>';
    }
    $("#McqCustomOptionForm").html(htmlContent);
    if (mode == 'edit') {
        SetCustomTypeOptionselectBox();
    }
}
function customMCQPreview(ResponseType) {
    if (ResponseType == "Prompt") {
        $("#McqCustomPromptPreview").html('');
        var promptType = $('#mcq_custom_prompt_type').val();
        if (promptType == 'card') {
            var PromptPreviewType = $("input[name='MCQCustomPromptRadio[]']:checked").map(function () {
                return $(this).val();
            }).get();
            var CardId = $("input[name='MCQCustomPrompt']:checked").map(function () {
                return $(this).val();
            }).get();
            getAndSetPreviewHtml(PromptPreviewType, CardId, 'McqCustomPromptPreview');
        }
        else if (promptType == 'html') {
            var phtml = (typeof $("#MCQCustomPrompt")[0] != 'undefined') ? $("#MCQCustomPrompt")[0].value : '';
            phtml = phtml.replace(/\n/g, "<br/>");
            $("#McqCustomPromptPreview").html(phtml);
        }
        else
        {
            $("#McqCustomPromptPreview").html('');
        }
    }
    if (ResponseType == "Response") {
        $("#McqCustomPreview").html('');
        var responseType = $('#mcq_custom_response_type').val();
        if (responseType == 'card') {
            var ResponsePreviewType = $("input[name='MCQCustomResponseRadio[]']:checked").map(function () {
                return $(this).val();
            }).get();
            var CardId = $("input[name='MCQCustomResponse']:checked").map(function () {
                return $(this).val();
            }).get();
            getAndSetPreviewHtml(ResponsePreviewType, CardId, 'McqCustomPreview');
        }
        else if (responseType == 'html') {
            var phtml = (typeof $("#MCQCustomResponse")[0] != 'undefined') ? $("#MCQCustomResponse")[0].value : '';
            phtml = phtml.replace(/\n/g, "<br/>");
            $("#McqCustomPreview").html(phtml);
        }
        else
        {
            $("#McqCustomPreview").html('');
        }
    }
}
function customMCQOptionPreview() {
    var NoOfblock = $("#mcq_custom_option_no").val();
    var customoptionhtml = '';
    $("#McqCustomOptionPreview").html(customoptionhtml);
    for (var i = 1; i <= NoOfblock; i++) {
        var Type = $('#Mcq_custom_Option_type' + i).val();
        if (Type == 'card') {
            var PreviewType = $("input[name='MCQOptionCustom" + i + "Radio[]']:checked").map(function () {
                return $(this).val();
            }).get();
            var CardId = $("input[name='MCQOptionCustom" + i + "']:checked").map(function () {
                return $(this).val();
            }).get();

            var data = {type: PreviewType, cardId: CardId, groupId: null, responseCardId: null};
            var ajaxurl = getAjaxUrl('admin/exercises/previewBlock');
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                async: false,
                cache: false,
                success: function (data) {
                    $('#McqCustomOptionPreview').append(data);
                },
                error: function (e) {
                    console.error("Error getting customMCQOptionPreview: " + e);
                }
            });
        }
        else if (Type == 'html') {
            var phtml = (typeof $("#MCQOptionCustom" + i)[0] != 'undefined') ? $("#MCQOptionCustom" + i)[0].value : '';
            phtml = phtml.replace(/\n/g, "<br/>");
            $('#McqCustomOptionPreview').append(phtml);
        }
        else {
            $('#McqCustomOptionPreview').append('');
        }
    }
}
//Multiple Choise Custom Function End

//Recording Custom  Function Start*/
function getRecordingCustomHtml(mode = null) {
    var type = $("#recording_custom_prompt_type").val();
    if (type == 'html') {
        var customhtml = '';
        var inputName = 'Recording';
        var customhtml = '' +
        '<div class="col-sm-6">Html (Unity only support b,i,size and color tag of html) ' +
            '<textarea class="form-control" name="' + inputName + '" id="' + inputName + '">' +
            '</textarea>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<button type="button" class="btn btn-default pick-image" ' +
            'data-toggle="modal" data-target="#fileLibrary" data-name="' + inputName + '">' +
                'Choose Image' +
            '</button><br><br>' +
            '<button type="button" class="btn btn-default pick-audio" ' +
            'data-toggle="modal" data-target="#fileLibrary" data-name="' + inputName + '">' +
                'Choose Audio' +
            '</button>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<img id="' + inputName + '_imagePreview" height="70" src="" class="customimage"><br><br>' +
            '<audio controls id="' + inputName + 'audioPreview" class="customaudio" style="display: none;">' +
                '<source class="' + inputName + '_audio-source" type="audio/mpeg">' +
                '<source class="' + inputName + '_audio-source"  type="audio/ogg">' +
                '<source class="' + inputName + '_audio-source" type="audio/wav">' +
                'Your browser does not support the audio element.' +
            '</audio>' +
            '<input type="hidden" id="' + inputName + '_image_id" name="' + inputName + '_image_id">' +
            '<input type="hidden" id="' + inputName + '_audio_id" name="' + inputName + '_audio_id">' +
        '</div>';
        $("#RecordingPromptForm").html(customhtml);
        $("#RecordingCustomPromptPreview").html('');
        if (mode == 'edit') {
            setRecordingCustomValue();
        }
    }
    else if (type == 'card') {
        var url = getAjaxUrl('admin/exercises/getCardPickerHtml');
        var inputName = 'Recording_Custom';
        var data = {inputName: inputName};
        $.ajax({
            type: "POST",
            url: url,
            async: false,
            data: data,
            cache: false,
            success: function (data) {
                $("#RecordingPromptForm").html(data);
                processDataTable(
                    `RecordingPromptForm`,
                    inputName,
                    inputName,
                    '',
                    '',
                    '',
                );
                setRadioButtonValue();
                $("#RecordingCustomPromptPreview").html('');
                if (mode == 'edit') {
                    setRecordingCustomPreview();
                }

            },
            error: function (e) {
            	console.error(e);
                $("#RecordingPromptForm").html('');
            }
        });
    }
    else {
        $("#RecordingPromptForm").html('');
}
}
function setRecordingCustomPreview() {
    var previewType = $('#recording_custom_prompt_type').val();
    if (previewType == 'card') {
        var PromptPreviewType = $("input[name='Recording_CustomRadio[]']:checked").map(function () {
            return $(this).val();
        }).get();
        var CardId = $("input[name='Recording_Custom']:checked").map(function () {
            return $(this).val();
        }).get();
        getAndSetPreviewHtml(PromptPreviewType, CardId, 'RecordingCustomPromptPreview');
    }
    else if (previewType == 'html') {
        var previewhtml = $('#Recording').val();
        $("#RecordingCustomPromptPreview").html(previewhtml);
    }
    var responseCardId = $("input[name='recording_custom_response_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    if (responseCardId.length != 0) {
        getAndSetPreviewHtml(['a'], responseCardId, 'RecordingCustomResponsePreview');
    }
}
//Recording Custom  Function Start*/

//Recording Group  Function Start*/
function getRecordingCardListByGroup() {
    var promteResponseValue = $('#recordingPromptGroupGroupId').val();
    var excludeCardIds = $('#recordingPromptGroupCardId').val();
    getCardByGroupIds(promteResponseValue, excludeCardIds, 'RecordingPomptCardList', 'change');
}
function getRecordingCardListByGroupOnEdit() {
    var promteResponseValue = $('#recordingPromptGroupGroupId').val();
    var excludeCardIds = $('#recordingPromptGroupCardId').val();
    getCardByGroupIds(promteResponseValue, excludeCardIds, 'RecordingPomptCardList', null);
}
function RecordingPromptGroupPreview() {
    var promteResponseValue = $('#promteResponseType').val();
    var promteResponseArr = promteResponseValue.split("-");
    var promteType = promteResponseArr[0];
    var singleCardIds = $('#recordingPromptGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.recording_group_cardcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    var values1 = $("input[name='recordingpromptgrouptype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(values1, allUniqueCardIds, 'recording_prompt_preview');
    //$('#prompt_group_card_preview').html('Card: ' + allUniqueCardIds.join(', '));
}
//Recording Group Function End*/


// Recording Card Function Start
function previewPromptRecordingCard() {
    var PromptPreviewType = $("input[name='recording_prompt[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var CardId = $("input[name='recording_prompt_card']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(PromptPreviewType, CardId, 'recordingPromptPreview');
}
function previewResponseRecordingCard() {
    var PromptPreviewType = ['a'];
    var CardId = $("input[name='recording_response_card']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(PromptPreviewType, CardId, 'recordingResponsePreview');
}
// Recording Card Function End


// Fill In The blank Card Function Start
function renderOptionHtmlAndSet() {
    var fillType = $('#fill_in_the_blank_type').val();
    if (fillType == 'mcq') {
        generateAndSetOptionHtml();
        $('#fill_blank_choise_div').show();
    }
    else {
        $('#fill_blank_choise_div').hide();
    }
}
function setFillPreview() {
    var previewstring = $("#fill_blank_lakota").val();
    var startIndex = previewstring.indexOf("[");
    var endIndex = previewstring.indexOf("]");
    var str = previewstring;
    var result = getFromBetween.get(str, "[", "]");
    var response = previewstring;
    $.each(result, function (index, value) {
        var ss = '_';
        var stringV = "[" + value + "]";
        var replaceWord = ss.repeat(value.length);
        response = response.replace(stringV, replaceWord);
    });
    $("#fill_in_the_blank_response_card_preview").html(response);
    $("#fill_blank_choise").val(result);
    $("#fill_blank_ans").val(result);
    renderOptionHtmlAndSet();
}
function setFillInTheBlankPreview(type = null) {
    var PromptPreviewType = $("input[name='FillInTheBlankCardPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var CardId = $("input[name='fill_in_the_blank_card_typing']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewFillInTheBlankHtml(PromptPreviewType, CardId, 'fill_in_the_blank_prompt_card_preview', type);
}
function setResponsePreview(actiontype = null) {
    var cardId = $("input[name='fill_in_the_blank_card_typing']:checked").val();
    var ResponsePreviewType = $("input[name='FillInTheBlankCardResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();

    if (!cardId) {
        return;
    }

    var data = {cardId: cardId};
    $.ajax({
        type: "POST",
        async: false,
        url: getAjaxUrl('admin/exercises/getCard'),
        data: data,
        cache: false,
        success: function (data) {
            var resultSet = JSON.parse(data);
            if (actiontype != null) {
                $("#fill_in_the_blank_response_card_preview").html(resultSet.lakota);
                $("#fill_blank_lakota").val(resultSet.lakota);
            }
        },
        error: function (e) {
        	console.error(e);
        }
    });
}
function getAndSetPreviewFillInTheBlankHtml(type, cardId, divid, actiontype = null, groupId = null, responseCardId = null) {
    var data = {type: type, cardId: cardId, groupId: groupId, responseCardId: responseCardId};
	  $('#' + divid).html('<p>Loading...</p>');
    $.ajax({
        type: "POST",
        async: false,
        url: getAjaxUrl('admin/exercises/previewBlock'),
        data: data,
        cache: false,
        success: function (data) {
            $('#' + divid).html(data);
            if (actiontype == null) {
                setResponsePreview(null);
            }
            else {
                setResponsePreview('change');
            }

        },
        error: function (e) {
            console.error("Error in getAndSetPreviewFillInTheBlankHtml: " + e);
            $('#' + divid).html('');
        }
    });
}
// Fill In The blank Card Function End

//Fill In The blank Custom Function Start
function getFillInTheBlanksCustomHtml(mode = null) {
    var type = $("#fill_in_the_blanks_prompt_type").val();
    // $("#fill_blank_custom_lakota").val('');
    // $("#fill_blank_custom_ans").val('');
    setFillInTheBalnksCustomPreviewOption();
    if (type == 'html') {
        var fillcustomhtml = '';
        var InputName = 'Fill_In_The_Blanks';
        var fillcustomhtml = '' +
        '<div class="col-sm-6">' +
            'Html (Unity only support b,i,size and color tag of html) ' +
            '<textarea class="form-control" name="' + InputName + '" id="' + InputName + '">' +
            '</textarea>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<button type="button" class="btn btn-default pick-image" ' +
                'data-toggle="modal" data-target="#fileLibrary" data-name="' + InputName + '">' +
                'Choose Image' +
            '</button><br><br>' +
            '<button type="button" class="btn btn-default pick-audio" ' +
                'data-toggle="modal" data-target="#fileLibrary" data-name="' + InputName + '">' +
                'Choose Audio' +
            '</button>' +
        '</div>' +
        '<div class="col-sm-3">' +
            '<img id="' + InputName + '_imagePreview" height="70" src="" class="customimage"><br><br>' +
            '<audio controls id="' + InputName + 'audioPreview" class="customaudio" style="display: none;">' +
                '<source class="' + InputName + '_audio-source" type="audio/mpeg">' +
                '<source class="' + InputName + '_audio-source"  type="audio/ogg">' +
                '<source class="' + InputName + '_audio-source" type="audio/wav">' +
                'Your browser does not support the audio element.' +
            '</audio>' +
            '<input type="hidden" id="' + InputName + '_image_id" name="' + InputName + '_image_id">' +
            '<input type="hidden" id="' + InputName + '_audio_id" name="' + InputName + '_audio_id">' +
        '</div>';
        $("#FillInTheBlankPromptForm").html(fillcustomhtml);
        $("#FillInTheBlankCustomPromptPreview").html('');
        if (mode == 'edit') {
            setFillInTheBlankCustomValue();
        }
    }
    else if (type == 'card') {
        var url = getAjaxUrl('admin/exercises/getCardPickerHtml');
        var inputName = 'Fill_In_The_Balnks_Custom';
        var data = {inputName: inputName};
        $.ajax({
            type: "POST",
            url: url,
            async: false,
            data: data,
            cache: false,
            success: function (data) {
                $("#FillInTheBlankPromptForm").html(data);
                processDataTable(
                    `${inputName}Table`, // id
                    inputName, // name
                    inputName, // class
                    '', // value
                    '', // blockNo
                    '', // responseType
                );
                setRadioButtonValue();
                if (mode == 'edit') {
                    setTimeout(function () {
                        setFillInTheBlankCustomValue();
                        setFillInTheBalnksCustomOption();
                    }, 500);
                }

            },
            error: function (e) {
            	console.error(e);
            }
        });
}
}
function setFillInTheBalnksCustomOption(newCard = false) {
    var Type = $('#fill_in_the_blanks_prompt_type').val();
    if (Type == 'html') {
        var htmlContent = $('#Fill_In_The_Blanks').val();
        htmlContent = htmlContent.replace(/<[^>]*>/g, '');
        $('#fill_blank_custom_lakota').val(htmlContent);

        var phtml = (typeof $("#Fill_In_The_Blanks")[0] != 'undefined') ? $("#Fill_In_The_Blanks")[0].value : '';
        phtml = phtml.replace(/\n/g, "<br/>");
        $("#FillInTheBlankCustomPromptPreview").html(phtml);
    }
    else if (Type == 'card') {
        var cardId = $("input[name='Fill_In_The_Balnks_Custom']:checked").map(function () {
            return $(this).val();
        }).get();
        var PreviewType = $("input[name='Fill_In_The_Balnks_CustomRadio[]']:checked").map(function () {
            return $(this).val();
        }).get();

        var data = {cardId: cardId[0]};
        $.ajax({
            type: "POST",
            url: getAjaxUrl('admin/exercises/getCard'),
            data: data,
            async: false,
            cache: false,
            success: function (data) {
                var resultSet = JSON.parse(data);
                if (newCard) {
                    $('#fill_blank_custom_lakota').val(resultSet.lakota);
                }
                getAndSetPreviewHtml(PreviewType, cardId, 'FillInTheBlankCustomPromptPreview');

//                if(actiontype!=null){
//                    $("#fill_in_the_blank_response_card_preview").html(resultSet.lakota);
//                    $("#fill_blank_lakota").val(resultSet.lakota);
//                }
            },
            error: function (e) {
            }
        });
    }
}
function setFillInTheBalnksCustomPreviewOption() {
    var previewstring = $("#fill_blank_custom_lakota").val();
    var startIndex = previewstring.indexOf("[");
    var endIndex = previewstring.indexOf("]");
    var str = previewstring;
    var result = getFromBetween.get(str, "[", "]");
    var response = previewstring;
    $.each(result, function (index, value) {
        var ss = '_';
        var stringV = "[" + value + "]";
        var replaceWord = ss.repeat(value.length);
        response = response.replace(stringV, replaceWord);
    });
    $("#FillInTheBlankCustomResponsePreview").html(response);
    $("#fill_blank_custom_ans").val(result);

}
//Fill In The blank Custom Function End

// Anagram Group Function Start
function getAnagramGroupCheckboxOnchange() {
    var gvalues = $('#anagram_group_group_id').val();
    var excludeCardIds = $('#AnagramGroupCardId').val();
    getCardByGroupIds(gvalues, excludeCardIds, 'anagram_group_single_card_preview', 'change');
}
function getAnagramGroupCheckbox() {
    var gvalues = $('#anagram_group_group_id').val();
    var excludeCardIds = $('#AnagramGroupCardId').val();
    getCardByGroupIds(gvalues, excludeCardIds, 'anagram_group_single_card_preview', null);
}
function setAnagramGroupPreview() {
    var singleCardIds = $('#AnagramGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $("input[name='anagram_group_group_card_id[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    var PromptPreviewType = $("input[name='anagramGroupPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponsePreviewType = $("input[name='anagramGroupResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(PromptPreviewType, allUniqueCardIds, 'anagram_prompt_group_preview');
    getAndSetPreviewHtml(ResponsePreviewType, allUniqueCardIds, 'anagram_response_group_preview');
}
// Anagram Group Function End

// Anagram Card Function Start
function previewAnagramCard() {
    var PromptPreviewType = $("input[name='anagramCardPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponsePreviewType = $("input[name='anagramCardResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var CardId = $("input[name='anagram_card']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(PromptPreviewType, CardId, 'anagram_card_prompt_preview');
    getAndSetPreviewHtml(ResponsePreviewType, CardId, 'anagram_card_response_preview', null, null, true);
}
// Anagram Card Function End

// True False Group Function Start */
function TrueFalseGroupPromptPreview() {
    var PromptPreviewType = $("input[name='TrueFalseGruopPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var singleCardIds = $('#true_false_prompt_group_card_id').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $("input[name='truefalsegruoppromptcardid[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    getAndSetPreviewHtml(PromptPreviewType, allUniqueCardIds, 'TrueFalseGruopPromptPreview');
    setTimeout(function () {
        GenerateAndSetDropDown();
    }, 500);
}
function TrueFalseGroupResponsePreview() {
    var ResponsePreviewType = $("input[name='TrueFalseGruopResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var singleCardIds = $('#true_false_prompt_group_card_id').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $("input[name='truefalsegruoppromptcardid[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    getAndSetPreviewHtml(ResponsePreviewType, allUniqueCardIds, 'TrueFalseGruopResponsePreview');
    setTimeout(function () {
        GenerateAndSetDropDown();
    }, 500);
}
function GenerateAndSetDropDown() {
    var PCardId = $("input[name='truefalsegruoppromptcardid[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var RCardId = $("input[name='truefalsegruopresponsecardid[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var diffarr = [];
    var maxarray = 0;
    if (PCardId.length > RCardId.length) {
        diffarr = PCardId.diff(RCardId);
        maxarray = PCardId.length;
    }
    else {
        diffarr = RCardId.diff(PCardId);
        maxarray = RCardId.length;
    }

    var dhtml = '';
    if (diffarr.length == 0) {
        for (i = 1; i <= maxarray; i++) {
            dhtml += '' +
            '<div class="col-sm-4">' +
                '<label>Response for Card ' + PCardId[i - 1] + '</label>' +
                '<select name="response_custom_type" class="form-control" id="response_custom_type_true_false">' +
                    '<option value="">Select</option>' +
                    '<option value="Y" selected="selected">True</option>' +
                    '<option value="N">False</option>' +
                '</select>' +
            '</div>';
        }
    }
    else if (diffarr.length > 0) {
        for (i = 1; i <= PCardId.length; i++) {
            if ($.inArray(PCardId[i - 1], RCardId) != -1) {
                dhtml += '' +
                '<div class="col-sm-4">' +
                    '<label>Response for Card ' + PCardId[i - 1] + '</label>' +
                    '<select name="response_custom_type" class="form-control" id="response_custom_type_true_false">' +
                        '<option value="">Select</option>' +
                        '<option value="Y" selected="selected">True</option>' +
                        '<option value="N">False</option>' +
                    '</select>' +
                '</div>';
            }
            else
            {
                dhtml += '' +
                '<div class="col-sm-4">' +
                    '<label>Response for Card ' + PCardId[i - 1] + '</label>' +
                    '<select name="response_custom_type" class="form-control" id="response_custom_type_true_false">' +
                        '<option value="">Select</option>' +
                        '<option value="Y">True</option>' +
                        '<option value="N" selected="selected"selected="selected">False</option>' +
                    '</select>' +
                '</div>';
            }
        }
    }
    $('#TrueFalseGruopDropdown').html(dhtml);
}
// True False Group Function End */

//True False Custom Function Start*/
function TrueFalseCustomResponsePreview() {
    var rval = $('#true_false_response_type').val();
    if (rval == 'card') {
        var response_card_id = $("input[name='TrueFalseCustomResponseInput']:checked").map(function () {
            return $(this).val();
        }).get();
        var ResponseType = $("input[name='TrueFalseCustomResponseInputRadio[]']:checked").map(function () {
            return $(this).val();
        }).get();
        getAndSetPreviewHtml(ResponseType, response_card_id, 'TrueFalseCustomResponsePreview');
    }
    else if (rval == 'html') {
        var rhtml = $('#TrueFalseCustomResponseInput').val();
        $('#TrueFalseCustomResponsePreview').html(rhtml);
    }
}
function TrueFalseCustomPromptPreview() {
    var pval = $('#true_false_prompt_type').val();
    if (pval == 'card') {
        var prompt_card_id = $("input[name='TrueFalseCustomPromptInput']:checked").map(function () {
            return $(this).val();
        }).get();
        var PromptType = $("input[name='TrueFalseCustomPromptInputRadio[]']:checked").map(function () {
            return $(this).val();
        }).get();
        getAndSetPreviewHtml(PromptType, prompt_card_id, 'TrueFalseCustomPromptPreview');
    }
    else if (pval == 'html') {
        var phtml = $('#TrueFalseCustomPromptInput').val();
        $('#TrueFalseCustomPromptPreview').html(phtml);
    }
}
function setTrueFalseCustomValue() {
    var pval = $('#true_false_prompt_type').val();
    generateTruefalseHtml('TrueFalseCustomPromptInput', 'trueFalsePromptForm', pval, 'prompt');
    var rval = $('#true_false_response_type').val();
    generateTruefalseHtml('TrueFalseCustomResponseInput', 'trueFalseResponseForm', rval, 'response');
    setTimeout(function () {
        TrueFalseCustomPromptPreview();
        TrueFalseCustomResponsePreview();
    }, 1000);
}
//True False Custom Function End*/

//True False Card Function Start*/
function TrueFalsePromptPreview() {
    var prompt_card_id = $("input[name='true_false_prompt_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    var PromptType = $("input[name='TrueFalseCardPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(PromptType, prompt_card_id, 'TrueFalseCardPromptPreview');
}
function TrueFalseResponsePreview() {
    var response_card_id = $("input[name='true_false_response_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseType = $("input[name='TrueFalseCardResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(ResponseType, response_card_id, 'TrueFalseCardResponsePreview');
    var prompt_card_id = $("input[name='true_false_prompt_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    if (response_card_id.toString() == prompt_card_id.toString()) {
        $('#response_card_type_true_false').val('Y');
        $('#truefalsesamecard').prop('checked', true);
    }
    else
    {
        $('#response_card_type_true_false').val('N');
        $('#truefalsesamecard').prop('checked', false);
    }
}
//True False Card Function End*/

//Match The Pair Custom Function Start*/
function getMatchThePairCustomHtmlForm() {
    var noOfElement = $("#MatchThePairCustomNo").val();
    var htmlContent = '';
    for (var i = 1; i <= noOfElement; i++) {
        htmlContent += '' +
        '<div class="row">' +
            '<div class="col-sm-2">' +
                '<label>Prompt #' + i + '</label>' +
            '</div>' +
            '<div class="col-sm-6">' +
                '<select name="match_the_pair_custom_prompt_type' + i +
                    '" class="form-control customPrompt" id="match_the_pair_custom_prompt_type' +
                    i + '" data-block="' + i + '" data-type="Prompt">' +
                    '<option value="" selected="selected">Select Type</option>' +
                    '<option value="card">Card</option>' +
                    '<option value="html">HTML</option>' +
                '</select>' +
            '</div>' +
            '<div class="col-sm-12 custom-html-element" id="CustomPromptElement' + i + '">' +
            '</div>' +
            '<div class="col-sm-12">' +
                '<label>Preview</label>' +
                '<div id="CustomPromptElementPreview' + i + '"></div>' +
            '</div>' +
        '</div>' +
        '<div class="row">' +
            '<div class="col-sm-2">' +
                '<label>Response #' + i + '</label>' +
            '</div>' +
            '<div class="col-sm-6">' +
                '<select name="match_the_pair_custom_response_type' + i +
                    '" class="form-control customResponse" id="match_the_pair_custom_response_type' +
                    i + '" data-block="' + i + '" data-type="Response">' +
                    '<option value="" selected="selected">Select Type</option>' +
                    '<option value="card">Card</option>' +
                    '<option value="html">HTML</option>' +
                '</select>' +
            '</div>' +
            '<div class="col-sm-12 custom-html-element" id="CustomResponseElement' + i + '">' +
            '</div>' +
            '<div class="col-sm-12">' +
                '<label>Preview</label>' +
                '<div id="CustomResponseElementPreview' + i + '">' +
                '</div>' +
            '</div>' +
        '</div>';
    }
    $("#MatchThePairCustomForm").html(htmlContent);
}
function getCustomElement(block, type, ElementType) {
    if (ElementType == 'card') {
        generateCard(block, type);
    }
    else if (ElementType == 'html') {
        generateHtml(block, type);
    }
}
function generateHtml(blockNo, ResponseType) {
    var htmlContent = 'html';
    var InputName = 'Match_The_Pair_' + ResponseType + '_' + blockNo;
    var fromhtml = '' +
    '<div class="col-sm-6">' +
        'Html (Unity only support b,i,size and color tag of html) ' +
        '<textarea data-type="' + ResponseType + '" data-block ="' + blockNo +
            '" class="form-control htmltextarea" name="' + InputName + '" id="' + InputName + '">' +
        '</textarea>' +
    '</div>' +
    '<div class="col-sm-3">' +
        '<button type="button" class="btn btn-default pick-image" data-toggle="modal" ' +
            'data-target="#fileLibrary" data-name="' + InputName + '">' +
            'Choose Image' +
        '</button><br><br>' +
        '<button type="button" class="btn btn-default pick-audio" data-toggle="modal" ' +
            'data-target="#fileLibrary" data-name="' + InputName + '">' +
            'Choose Audio' +
        '</button>' +
    '</div>' +
    '<div class="col-sm-3">' +
        '<img id="' + InputName + '_imagePreview" height="70" src="" class="customimage"><br><br>' +
        '<audio controls id="' + InputName + 'audioPreview" class="customaudio" style="display: none;">' +
            '<source class="' + InputName + '_audio-source" type="audio/mpeg">' +
            '<source class="' + InputName + '_audio-source"  type="audio/ogg">' +
            '<source class="' + InputName + '_audio-source" type="audio/wav">' +
            'Your browser does not support the audio element.' +
        '</audio>' +
        '<input type="hidden" id="' + InputName + '_image_id" name="' + InputName + '_image_id">' +
        '<input type="hidden" id="' + InputName + '_audio_id" name="' + InputName + '_audio_id">' +
    '</div>';
    $("#Custom" + ResponseType + "Element" + blockNo).html(fromhtml);
}
function generateCard(blockNo, responseType) {
    const url = getAjaxUrl('admin/exercises/getCardPickerHtml');
    const inputName = 'Match_The_Pair_Custom';
    const data = {inputName: inputName, blockNo: blockNo, responseType: responseType};
		$("#Custom" + responseType + "Element" + blockNo).html('<p>Loading...</p>');
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        async: false,
        cache: false,
        success: function (data) {
            $("#Custom" + responseType + "Element" + blockNo).html(data);
            processDataTable(
                `${inputName}Table${blockNo}${responseType}`, // id
                inputName, // name
                inputName, // class
                '', // value
                blockNo, // blockNo
                responseType, // responseType
            );
            setRadioButtonValue();
        },
        error: function (e) {
        	console.error(e);
            $("#Custom" + responseType + "Element" + blockNo).html('');
        }
    });
}
function getMatchThePairPreview(blockNo, responseType) {
    var previewhtml = '';
    if (responseType == 'Prompt') {
        var restype = 'prompt';
    }
    else if (responseType == 'Response') {
        var restype = 'response';
    }
    var type = $('#match_the_pair_custom_' + restype + '_type' + blockNo).val();
    if (type == 'html') {
        previewhtml = $('#Match_The_Pair_' + responseType + '_' + blockNo).val();
        $('#Custom' + responseType + 'ElementPreview' + blockNo).html(previewhtml);
    }
    else if (type == 'card') {
        var cardid = $("input[name='Match_The_Pair_Custom_" + responseType + "_" + blockNo + "']:checked").map(function () {
            return $(this).val();
        }).get();
        var Type = $("input[name='Match_The_Pair_Custom" + blockNo + responseType + "Radio[]']:checked").map(function () {
            return $(this).val();
        }).get();
        getAndSetPreviewHtml(Type, cardid, 'Custom' + responseType + 'ElementPreview' + blockNo);
    }

}
//Match The Pair Custom Function Start*/

//Match The Pair Group Function Start*/
function getMatchThePairCardList() {
    var promteResponseValue = $('#MatchThePairPromptGroupGroupId').val();
    var excludeCardIds = $('#MatchThePairPromptGroupCardId').val();
    getCardByGroupIds(promteResponseValue, excludeCardIds, 'MatchThePairPomptCardList', 'change');
}
function getMatchThePairCardEditList() {
    var promteResponseValue = $('#MatchThePairPromptGroupGroupId').val();
    var excludeCardIds = $('#MatchThePairPromptGroupCardId').val();
    getCardByGroupIds(promteResponseValue, excludeCardIds, 'MatchThePairPomptCardList', null);
}
function MatchThePairGroupPromptPreview() {

    var singleCardIds = $('#MatchThePairPromptGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.optiongroupcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    $('#match_the_pair_prompt_group_preview').html('Card: ' + allUniqueCardIds.join(', '));
    var values1 = $("input[name='matchthepairpromptgrouptype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var values2 = $("input[name='matchthepairresponsegrouptype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(values1, allUniqueCardIds, 'match_the_pair_prompt_group_card_preview');
    getAndSetPreviewHtml(values2, allUniqueCardIds, 'match_the_pair_response_group_preview');
}
//Match The Pair Group Function End*/

//Match The Pair Card Function Start*/
function setMatchThePairPreview() {
    var divcount = $('#MatchThePairCardNo').val();
    for (i = 1; i <= divcount; i++) {
        var obj = $("input[name='match_the_pair_prompt_card_id" + i + "']:checked");
        setMatchThePairPromptPreview(obj);
        var obj1 = $("input[name='match_the_pair_response_card_id" + i + "']:checked");
        setMatchThePairResponsePreview(obj1);
    }
}
function setMatchThePairResponsePreview(obj) {
    var value = obj.val();
    if (value != undefined) {
        var name = obj.attr("name");
        var count = name.substr(name.length - 1, 1);
        $('#MatchThePairCardResponse' + count).html('Card: ' + value);
        var values1 = $("input[name='MatchThePairCardResponseType" + count + "[]']:checked").map(function () {
            return $(this).val();
        }).get();
        getAndSetPreviewHtml(values1, value, 'MatchThePairCardResponsePreview' + count);
    }
}
function setMatchThePairPromptPreview(obj) {
    var value = obj.val();
    if (value != undefined) {
        var name = obj.attr("name");
        var count = name.substr(name.length - 1, 1);
        $('#MatchThePairCardPrompt' + count).html('Card: ' + value);
        var values1 = $("input[name='MatchThePairCardPromptType" + count + "[]']:checked").map(function () {
            return $(this).val();
        }).get();
        getAndSetPreviewHtml(values1, value, 'MatchThePairCardPromptPreview' + count);
    } else {
    	console.warn("setMatchThePairPromptPreview(obj), obj.val() undefined");
    }
}
//Match The Pair Card Function End*/

//Multiple Choise Card Function Start*/
function setResponseCardValueAndPreview() {
//var cardId=$('#response_card_id').val();
    var cardId = $("input[name='response_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    if (cardId != '') {
        $('#response-card').html('Card :' + cardId);
    }
    else
    {
        $('#response-card').html('');
    }
    var promteResponseValue = $('#promteResponseType').val();
    var promteResponseArr = promteResponseValue.split("-");
    var responseType = promteResponseArr[1];
    var values = $("input[name='responsetype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(values, cardId, 'response_preview');
}
function setPromptCardValueAndPreview() {
// var cardId=$('.prompt_card_id').val();
    var cardId = $("input[name='prompt_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    if (cardId != '') {
        $('#prompt-card').html('Card :' + cardId);
    }
    else
    {
        $('#prompt-card').html('');
    }
    var promteResponseValue = $('#promteResponseType').val();
    var promteResponseArr = promteResponseValue.split("-");
    var promteType = promteResponseArr[0];
    var values = $("input[name='prompttype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(values, cardId, 'prompt_preview');
    //$('#response_card_id').val(cardId);
    //setResponseCardValueAndPreview();
}
function setResponseOptionValueAndPreview() {
    var cardId = $('#card_id').val();
    var responsecardId = $('#response_card_id').val();
    var promteResponseValue = $('#promteResponseType').val();
    var promteResponseArr = promteResponseValue.split("-");
    var responseType = promteResponseArr[1];
    var groupId = $('#card_group_id').val();
    var values = $("input[name='responsetype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    if (groupId == '') {
        getAndSetPreviewHtml(values, cardId, 'option_preview', null, responsecardId);
    }
    else {
        getAndSetPreviewHtml(values, cardId, 'option_preview', groupId, responsecardId);
    }
    getCardLabelPreview(cardId, 'single_card_preview', groupId, responsecardId);
}
//Multiple Choise Card Function End*/

//Multiple Choise Group Function Start*/
function promoteCardGroupPreview() {
    var promteResponseValue = $('#promteResponseType').val();
    var promteResponseArr = promteResponseValue.split("-");
    var promteType = promteResponseArr[0];
    var responseType = promteResponseArr[1];
    var singleCardIds = $('#promptGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.promotegroupcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    var values1 = $("input[name='promptgrouptype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var values2 = $("input[name='responsegrouptype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(values1, allUniqueCardIds, 'prompt_group_preview');
    getAndSetPreviewHtml(values2, allUniqueCardIds, 'response_group_preview');
    $('#prompt_group_card_preview').html('Card: ' + allUniqueCardIds.join(', '));
}
function optionCardGroupPreview() {
    var promteResponseValue = $('#promteResponseType').val();
    var promteResponseArr = promteResponseValue.split("-");
    var responseType = promteResponseArr[1];
    var singleCardIds = $('#group_card_option_id').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.optiongroupcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    var values2 = $("input[name='responsegrouptype[]']:checked").map(function () {
        return $(this).val();
    }).get();
    getAndSetPreviewHtml(values2, allUniqueCardIds, 'option_response_preview');
    $('#choices_card_preview').html('Card: ' + allUniqueCardIds.join(', '));
}
function getPromoteCardCheckbox(change = null) {
    var promteResponseValue = $('#promptGroupGroupId').val();
    var excludeCardIds = $('#promptGroupCardId').val();
    getCardByGroupIds(promteResponseValue, excludeCardIds, 'PomptCardList', change);
    promoteCardGroupPreview();
}
function getOptionCardCheckbox(change = null) {
    var GroupIds = $('#OptionMultiCardGroupPicker').val();
    var excludeCardIds = $('#group_card_option_id').val();
    getCardByGroupIds(GroupIds, excludeCardIds, 'OptionCardList', change);
}
//Multiple Choise Group Function End*/

//General Function Start*/
function showHideFormBlock() {
    var cardType = $('#card_type').val();
    var exercise_type = $('#exercise_type').val();
    allblockHide();
    if (exercise_type == 'multiple-choice' && cardType == 'card') {
        $('#MultipleChoiceCardSection').show();
    }
    else if (exercise_type == 'multiple-choice' && cardType == 'card_group') {
        $('#MultipleChoiseCardGroupSection').show();
    }
    else if (exercise_type == 'multiple-choice' && cardType == 'custom') {
        $('#MultipleChoiseCustomSection').show();
    }
    else if (exercise_type == 'match-the-pair' && cardType == 'card') {
        $('#MatchThePairCardSection').show();
    }
    else if (exercise_type == 'match-the-pair' && cardType == 'card_group') {
        $('#MatchThePairGroupSection').show();
    }
    else if (exercise_type == 'match-the-pair' && cardType == 'custom') {
        $('#MatchThePairCustomSection').show();
    }
    else if (exercise_type == 'truefalse' && cardType == 'card') {
        $('#TrueFalseCardSection').show();
    }
    else if (exercise_type == 'truefalse' && cardType == 'card_group') {
        $('#TrueFalseGruopSection').show();
    }
    else if (exercise_type == 'truefalse' && cardType == 'custom') {
        $('#TrueFalseCustomSection').show();
    }
    else if (exercise_type == 'anagram' && cardType == 'card') {
        $('#AnagramCardSection').show();
    }
    else if (exercise_type == 'anagram' && cardType == 'card_group') {
        $('#AnagramGroupSection').show();
    }
    else if (exercise_type == 'fill_in_the_blanks' && cardType == 'card') {
        $('#FillInTheBlankCardTypingSection').show();
    }
    else if (exercise_type == 'fill_in_the_blanks' && cardType == 'custom') {
        $('#FillInTheBlankCustomSection').show();
    }
    else if (exercise_type == 'recording' && cardType == 'card') {
        $('#RecordingCardSection').show();
    }
    else if (exercise_type == 'recording' && cardType == 'card_group') {
        $('#RecordingCardGroupSection').show();
    }
    else if (exercise_type == 'recording' && cardType == 'custom') {
        $('#RecordingCustomSection').show();
    }
    else {
        allblockHide();
    }
}
function allblockHide() {
    // $('#MultipleChoiceCardSection').hide();
    // $('#MultipleChoiseCardGroupSection').hide();
    // $('#MultipleChoiseCustomSection').hide();
    // $('#MatchThePairCardSection').hide();
    // $('#MatchThePairGroupSection').hide();
    // $('#MatchThePairCustomSection').hide();
    // $('#TrueFalseCardSection').hide();
    // $('#TrueFalseCustomSection').hide();
    // $('#TrueFalseGruopSection').hide();
    // $('#AnagramCardSection').hide();
    // $('#AnagramGroupSection').hide();
    // $('#FillInTheBlankCardTypingSection').hide();
    // $('#FillInTheBlankCustomSection').hide();
    // $('#RecordingCardSection').hide();
    // $('#RecordingCardGroupSection').hide();
    // $('#RecordingCustomSection').hide();

    filterOption();
}
function setRadioButtonValue() {
    var promteResponseValue = $('#promteResponseType').val();
    var cardTypeValue = $('#card_type').val();
    var exercise_type = $('#exercise_type').val();
    if (!!promteResponseValue && promteResponseValue != '') {
        var promteResponseArr = promteResponseValue.split("-");
        var promteType = promteResponseArr[0];
        var responseType = promteResponseArr[1];
        if (cardTypeValue == 'card' && exercise_type == 'multiple-choice') {
            $("input[class=promoteradio]").prop('checked', false).prop('disabled', "");
            $("input[class=responseradio]").prop('checked', false).prop('disabled', "");
            $("input[class=promoteradio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=responseradio][value=" + responseType + "]").prop('checked', 'checked').prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card_group' && exercise_type == 'multiple-choice') {
            $("input[class=prompgroupttype]").prop('checked', false).prop('disabled', "");
            $("input[class=responsegrouptype]").prop('checked', false).prop('disabled', "");
            $("input[class=prompgroupttype][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=responsegrouptype][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'custom' && exercise_type == 'multiple-choice') {
            $("input[class=MCQCustomPromptRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=MCQCustomPromptRadio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");

            $("input[class=MCQCustomResponseRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=MCQCustomResponseRadio][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=MCQOptionCustomRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=MCQOptionCustomRadio][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card' && exercise_type == 'match-the-pair') {
            $(".MatchThePairPromptRadio").each(function (index, element) {
                if ($(this).val() == promteType) {
                    $(this).prop('checked', true).prop('disabled', "disabled");
                }
                else {
                    $(this).prop('checked', false).prop('disabled', "");
                }
            });
            $(".MatchThePairResponseRadio").each(function (index, element) {
                if ($(this).val() == responseType) {
                    $(this).prop('checked', true).prop('disabled', "disabled");
                }
                else {
                    $(this).prop('checked', false).prop('disabled', "");
                }
            });
        }
        else if (cardTypeValue == 'card_group' && exercise_type == 'match-the-pair') {
            $("input[class=matchthepairpromptgrouptype]").prop('checked', false).prop('disabled', "");
            $("input[class=matchthepairresponsegrouptype]").prop('checked', false).prop('disabled', "");
            $("input[class=matchthepairpromptgrouptype][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=matchthepairresponsegrouptype][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'custom' && exercise_type == 'match-the-pair') {
            $("input[class=Match_The_Pair_CustomRadioPrompt]").prop('checked', false).prop('disabled', "");
            $("input[class=Match_The_Pair_CustomRadioResponse]").prop('checked', false).prop('disabled', "");
            $("input[class=Match_The_Pair_CustomRadioPrompt][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=Match_The_Pair_CustomRadioResponse][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card' && exercise_type == 'truefalse') {
            $("input[class=TrueFalsePromptRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=TrueFalseResponseRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=TrueFalsePromptRadio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=TrueFalseResponseRadio][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'custom' && exercise_type == 'truefalse') {
            $("input[class=TrueFalseCustomPromptInputRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=TrueFalseCustomResponseInputRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=TrueFalseCustomPromptInputRadio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=TrueFalseCustomResponseInputRadio][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card_group' && exercise_type == 'truefalse') {
            $("input[class=TrueFalseGruopPromptRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=TrueFalseGruopResponseRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=TrueFalseGruopPromptRadio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=TrueFalseGruopResponseRadio][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card' && exercise_type == 'anagram') {
            $("input[class=anagramCardPromptType]").prop('checked', false).prop('disabled', "");
            $("input[class=anagramCardResponseType]").prop('checked', false).prop('disabled', "");
            $("input[class=anagramCardPromptType][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=anagramCardResponseType][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card_group' && exercise_type == 'anagram') {
            $("input[class=anagramGroupPromptType]").prop('checked', false).prop('disabled', "");
            $("input[class=anagramGroupResponseType]").prop('checked', false).prop('disabled', "");
            $("input[class=anagramGroupPromptType][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=anagramGroupResponseType][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card' && exercise_type == 'fill_in_the_blanks') {
            $("input[class=FillInTheBlankCardPromptType]").prop('checked', false).prop('disabled', "");
            $("input[class=FillInTheBlankCardResponseType]").prop('checked', false).prop('disabled', "");
            $("input[class=FillInTheBlankCardPromptType][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
            $("input[class=FillInTheBlankCardResponseType][value=" + responseType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'custom' && exercise_type == 'fill_in_the_blanks') {
            $("input[class=Fill_In_The_Balnks_CustomRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=Fill_In_The_Balnks_CustomRadio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card' && exercise_type == 'recording') {
            $("input[class=recordingprompttype]").prop('checked', false).prop('disabled', "");
            $("input[class=recordingprompttype][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'card_group' && exercise_type == 'recording') {
            $("input[class=recordingpromptgrouptype]").prop('checked', false).prop('disabled', "");
            $("input[class=recordingpromptgrouptype][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
        }
        else if (cardTypeValue == 'custom' && exercise_type == 'recording') {
            $("input[class=Recording_CustomRadio]").prop('checked', false).prop('disabled', "");
            $("input[class=Recording_CustomRadio][value=" + promteType + "]").prop('checked', true).prop('disabled', "disabled");
        }
    }
}
function unique(list) {
    var result = [];
    $.each(list, function (i, e) {
        if ($.inArray(e, result) == -1)
            result.push(e);
    });
    return result;
}

function promptAndResponseIdentical() {
    const promptResponseValue = $('#promteResponseType').val();
    if (!!promptResponseValue && promptResponseValue != '') {
        const promptResponseArr = promptResponseValue.split("-");
        if (promptResponseArr[0] == promptResponseArr[1]) {
            return true;
        }
    }
    return false;
}

function filterOption() {
    $('#card_type option').show();
    var exercise_type = $('#exercise_type').val();
    switch (exercise_type) {
        case 'multiple-choice':
            if (promptAndResponseIdentical()) {
                $('#card_type option[value=card_group]').hide();
            }
            break;
        case 'anagram':
            $('#card_type option[value=custom]').hide();
            break;
        case 'match-the-pair':
            if (promptAndResponseIdentical()) {
                $('#card_type option[value=card_group]').hide();
            }
            break;
        case 'fill_in_the_blanks':
            $('#card_type option[value=card_group]').hide();
            break;
        case 'recording':
            $('#exercise_type').val("");
            $('#exercise_type option').hide();
            $('#exercise_type option[value=recording]').show();
            $('#exercise_type').val("recording");
    }
    if (exercise_type != 'anagram') {
        if (promptAndResponseIdentical()) {
            $('#exercise_type option[value=anagram]').hide();
        }
    }
}

function getFiles(type, search = '', page = 1) {
    var loader = getAjaxUrl("img/loader.png");
    var url = getAjaxUrl('admin/files/getFiles');
    $('#fileLibrary .box-body .row').html('<img src="' + loader + '" clsss="img-responsive">');
    $.ajax({
        method: "POST",
        url: url,
        async: false,
        data: {type: type, search: search, page: page},
        success: function (res) {
            var resultSet = JSON.parse(res);
            if (resultSet.status == 'success') {
                var fileDiv = $('#fileLibrary .box-body .row');
                var html = '';
                for (var i = 0; i < resultSet.data.length; i++) {
                    var filePath = resultSet.data[i].FullUrl;
                    switch (type) {
                        case 'image':
                            html += "<div class='col-sm-6 col-md-4 modal-elemen-top-mergin'>";
                            html += "<img height='200' style='max-width: 100%' src='" + filePath + "' alt='" + resultSet.data[i].name + "'>";
                            break;
                        case 'audio':
                            html += "<div class='col-sm-6 modal-elemen-top-mergin'>";
                            html += "<audio controls>" +
                                    "<source src='" + filePath + "' type='audio/mpeg'>" +
                                    "</audio>";
                            break;
                        default:
                            html += '';
                            break;
                    }
                    html += "<label class='radio-inline'><input type='radio' name='file_res' class='file-res' data-id='" + resultSet.data[i].id + "'> " + resultSet.data[i].file_name + "</label>" +
                            "</div>";
                }
                html += '<div class="box-footer clearfix col-sm-12 col-md-12"><ul class="pagination pagination-sm no-margin pull-right">';
                if (resultSet.pageinfo.currentpage > 1) {
                    html += "<li><a data-page='" + resultSet.pageinfo.currentpage + "' href='javascript:void(0)' class='paginatepre'>Previous</a></li>";
                }
                if (resultSet.pageinfo.totalpage > resultSet.pageinfo.currentpage) {
                    html += "<li><a href='javascript:void(0)' class='paginatenext' data-page='" + resultSet.pageinfo.currentpage + "'>Next</a></li>";
                }
                fileDiv.html(html);
            }
        },
        error: function (err) {
        	console.error(err);
        }
    });
}
function SetFile(fileID, inputType) {
    if (fileID) {
        var url = getAjaxUrl('admin/files/getFile');
        $.ajax({
            method: "GET",
            async: false,
            url: url + "/" + fileID,
            success: function (res) {
                var resultSet = JSON.parse(res);
                if (resultSet.status == 'success') {
                    switch (resultSet.data.type) {
                        case 'audio':
                            var audioSrc = resultSet.data.FullUrl;
                            $('.' + inputType + '_audio-source').attr('src', audioSrc);
                            $('#' + inputType + 'audioPreview').show();
                            document.getElementById(inputType + 'audioPreview').load();
                            $('#' + inputType + '_audio_id').val(resultSet.data.id);
                            break;
                        case 'image':
                            var imgSrc = resultSet.data.FullUrl;
                            $('#' + inputType + '_imagePreview').attr('src', imgSrc);
                            $('#' + inputType + '_imagePreview').show();
                            $('#' + inputType + '_image_id').val(resultSet.data.id);
                            break;
                        default:
                            break;
                    }
                }
            },
            error: function (err) {
            	console.error(err);
            }
        });
    }
}
function getAndSetPreviewHtml(type, cardId, divid, groupId = null, responseCardId = null, stripTags = false) {
    var data = {type: type, cardId: cardId, groupId: groupId, responseCardId: responseCardId};
    var ajaxurl = getAjaxUrl('admin/exercises/previewBlock');
    $('#' + divid).html('<p>Loading...</p>');
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        async: true,
        cache: false,
        success: function (data) {
            if (stripTags) {
                data = data.replace(/(<([^>]+)>)/ig, "");
            }
            $('#' + divid).html(data);
        },
        error: function (e) {
            console.error("Error in getAndSetPreviewHtml: ", e);
            $('#' + divid).html('');
        }
    });
}
function getCardLabelPreview(cardId, divid, groupId = null, responseCardId = null) {
    var data = {cardId: cardId, groupId: groupId, responseCardId: responseCardId};
    var ajaxurl = getAjaxUrl('admin/exercises/getCardLabelPreview');
	  $('#' + divid).html('<p>Loading...</p>');
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        async: false,
        cache: false,
        success: function (data) {
            $('#' + divid).html(data);
        },
        error: function (e) {
            console.error("Error in getCardLabelPreview: ", e);
					  $('#' + divid).html('');
        }
    });
}

function addCardPickerHtml(containerDivId, tableIdPrefix, tableName, tableClass, value, blockNo, responseType, postSuccessCallback) {
    let data = { inputName: tableIdPrefix };
    if (blockNo) {
        data.blockNo = blockNo;
    }
    if (responseType) {
        data.responseType = responseType;
    }

    $.ajax({
        type: "POST",
        url: getAjaxUrl('admin/exercises/getCardPickerHtml'),
        data: data,
        async: false,
        cache: false,
        success: function (data) {
            $("#" + containerDivId).html(data);
            processDataTable(`${tableIdPrefix}Table${blockNo}`, tableName, tableClass, value, blockNo, responseType);
            postSuccessCallback();
        },
        error: function (e) {
            console.error(e);
            $("#" + tableIdPrefix).html('');
        }
    })
}

function processDataTable(tableId, tableName, tableClass, value, blockNo, responseType) {
    // return datatable so subsequent call work
    return $(`#${tableId}`).DataTable({
        'serverSide': true, // use server-side processing
        'processing': true, // show a loading indicator
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        'ajax': {
            'url': API_ROUTES.getCardList,
            'type': 'POST',
            'data': function (d) {
                d.name = tableName;
                d.class = tableClass;
                d.value = value;
                d.blockNo = blockNo;
                d.responseType = responseType;
            },
            'error': function (xhr, error, thrown) {
                console.error('Error loading card list table: ', xhr, error, thrown);
            }
        },
        'columns': [
            { 'data': 'select', 'orderable': false, 'searchable': false },
            { 'data': 'id' },
            { 'data': 'type' },
            { 'data': 'lakota' },
            { 'data': 'english' },
            { 'data': 'alt_lakota' },
            { 'data': 'alt_english' },
            { 'data': 'gender' },
            { 'data': 'audio' },
            { 'data': 'image' },
            { 'data': 'video' },
        ],
        'language': {
            'searchPlaceholder': 'text or ID',
        },
        'initComplete': function (setting, json) {
            // Run anything needed after table is loaded
        }
    });
}
//General Function End*/
