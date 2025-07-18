function  validateForm(type = 'validate') {
    var valid = true;
    var message = '';
    var name = $('#name').val().trim();
    var cardType = $('#card_type').val();
    var exercise_type = $('#exercise_type').val();
    var promteResponseType = $('#promteResponseType').val();
    $("#frm_message").html('');

    if (name == '') {
        valid = false;
        message = 'Name field cannot be left blank.';
    }
    else if (promteResponseType == '') {
        valid = false;
        message = 'Please select Prompt -> Response Type';
    }
    else if (exercise_type == '') {
        valid = false;
        message = 'Please select an Exercise Type';
    }
    else if (cardType == '') {
        valid = false;
        message = 'Please select a Card Type';
    }

    if (valid && type == 'validate') {
        if (exercise_type == 'multiple-choice' && cardType == 'card') {
            var response = multipleChoiceCardTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'multiple-choice' && cardType == 'card_group') {
            var response = multipleChoiceGroupTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'multiple-choice' && cardType == 'custom') {
            var response = multipleChoiceCustomTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'match-the-pair' && cardType == 'card') {
            var response = MatchThePairCardTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'match-the-pair' && cardType == 'card_group') {
            var response = MatchThePairGroupTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'match-the-pair' && cardType == 'custom') {
            var response = MatchThePairCustomTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'truefalse' && cardType == 'card') {
            var response = TrueFalseCardTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'truefalse' && cardType == 'card_group') {
            var response = TrueFalseGroupTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'truefalse' && cardType == 'custom') {
            var response = TrueFalseCustomTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'anagram' && cardType == 'card') {
            var response = AnagramCardTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'anagram' && cardType == 'card_group') {
            var response = AnagramGroupTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'fill_in_the_blanks' && cardType == 'card') {
            var response = FillInTheBlanksCardTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'fill_in_the_blanks' && cardType == 'custom') {
            var response = FillInTheBlanksCustomTypeValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'recording' && cardType == 'card') {
            var response = RecordingCardValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'recording' && cardType == 'card_group') {
            var response = RecordingCardGroupValidation();
            valid = response.status;
            message = response.message;
        }
        else if (exercise_type == 'recording' && cardType == 'custom') {
            var response = RecordingCustomValidation();
            valid = response.status;
            message = response.message;
        }
    }

    if (valid == true) {
        $("#exerciseForm").submit();
    }
    else {
        $("#frm_message").html(message).css({'color': 'red', 'font-size': '15px'});
        $('html, body').animate({
            scrollTop: $("#frm_message").offset().top - 100
        }, 1000);
    }
}
function FillInTheBlanksCardTypeValidation() {

    var FillInTheBlankType = $('#fill_in_the_blank_type').val();
    var instructions = $('#fill-in-the-blank-instructions').val();
    var card = $("input[name='fill_in_the_blank_card_typing']:checked").map(function () {
        return $(this).val();
    }).get();
    var PromptType = $("input[name='FillInTheBlankCardPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseType = $("input[name='FillInTheBlankCardResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var fill_blank_lakota = $('#fill_blank_lakota').val();
    var option_counter = $('#option_counter').val();
    var startIndex = fill_blank_lakota.indexOf("[");
    var endIndex = fill_blank_lakota.indexOf("]");
    var optionBlank = false;
    if (FillInTheBlankType == 'mcq') {
        $(".fill_option").each(function (index) {
            if ($(this).val() == '') {
                optionBlank = true;
            }
        });
    }


    if (instructions == '') {
        return {'status': false, 'message': 'Please enter instructions.'};
    }
    else if (card.length == '') {
        return {'status': false, 'message': 'Please select a Card.'};
    }
    else if (PromptType.length == 0) {
        return {'status': false, 'message': 'Prompt Preview Type must not be blank.'};
    }
    else if (ResponseType.length == 0) {
        return {'status': false, 'message': 'Response Preview Type must not be blank.'};
    }
    else if (ResponseType.indexOf("l") == -1) {
        return {'status': false, 'message': 'Response Lakota Preview must be selected.'};
    }
    else if (fill_blank_lakota == '') {
        return {'status': false, 'message': 'Please enter Marks Blank text.'};
    }
    else if (startIndex == -1 || endIndex == -1) {
        return {'status': false, 'message': 'Please Put "[" "]" to create question in the Marks Blank text.'};
    }
    else if (option_counter > 8) {
        return {'status': false, 'message': 'A max of 8 options is allowed.'};
    }
    else if (optionBlank == true) {
        return {'status': false, 'message': 'Option value cannot be blank.'};
    }
    return {'status': true, 'message': ''};
}
function FillInTheBlanksCustomTypeValidation() {
    var FillInTheBlankPromptType = $('#fill_in_the_blanks_prompt_type').val();
    var card = $("input[name='Fill_In_The_Balnks_Custom']:checked").map(function () {
        return $(this).val();
    }).get();
    var FillInTheBlankHtml = $('#Fill_In_The_Blanks').val();
    var fill_blank_lakota = $('#fill_blank_custom_lakota').val();
    var startIndex = fill_blank_lakota.indexOf("[");
    var endIndex = fill_blank_lakota.indexOf("]");

    if (FillInTheBlankPromptType == '') {
        return {'status': false, 'message': 'Please select a prompt type.'};
    }
    else if (FillInTheBlankPromptType == 'html' && FillInTheBlankHtml == '') {
        return {'status': false, 'message': 'Please enter prompt Html'};
    }
    else if (FillInTheBlankPromptType == 'card' && card.length == 0) {
        return {'status': false, 'message': 'Please select a card'};
    }
    else if (startIndex == -1 || endIndex == -1) {
        return {'status': false, 'message': 'Please Put "[" "]" to create question in the Marks Blank text.'};
    }
    return {'status': true, 'message': ''};
}
function AnagramGroupTypeValidation() {
    var anagramgroupcards = $('#anagramgroupcards').val();
    var anagramgroupinstructions = $('#anagramgroupinstructions').val();

    var singleCardIds = $('#AnagramGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.anagram_group_cardcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    if (anagramgroupcards == '' || parseInt(anagramgroupcards) <= 0) {
        return {'status': false, 'message': 'Please enter Number of Cards.'};
    }
    else if (anagramgroupinstructions == '') {
        return {'status': false, 'message': 'Please type Anagram Instructions.'};
    }
    else if (allUniqueCardIds.length == 0) {
        return {'status': false, 'message': 'Please select one or more cards.'};
    }
    else if (parseInt(anagramgroupcards) > allUniqueCardIds.length) {
        return {'status': false, 'message': 'Please select the same number of cards as entered (' + parseInt(anagramgroupcards) + ')'};
    }
    return {'status': true, 'message': ''};
}
function AnagramCardTypeValidation() {
    var anagramcardinstructions = $('#anagramcardinstructions').val();
    var PromptCardId = $("input[name='anagram_card']:checked").map(function () {
        return $(this).val();
    }).get();
    var PromptType = $("input[name='anagramCardPromptType[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseType = $("input[name='anagramCardResponseType[]']:checked").map(function () {
        return $(this).val();
    }).get();

    if (anagramcardinstructions == '') {
        return {'status': false, 'message': 'Please enter Anagram Instructions'};
    }
    else if (PromptCardId.length == 0) {
        return {'status': false, 'message': 'Please Select Card'};
    }
    else if (PromptType.length == 0) {
        return {'status': false, 'message': 'Please Select Prompt Preview Type'};
    }
    else if (ResponseType.length == 0) {
        return {'status': false, 'message': 'Please Select Response Preview Type'};
    }
    return {'status': true, 'message': ''};
}
function TrueFalseCardTypeValidation() {
    var PromptCardId = $("input[name='true_false_prompt_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseCardId = $("input[name='true_false_response_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    var response_card_type = $('#response_card_type_true_false').val();
    if (PromptCardId.length == 0) {
        return {'status': false, 'message': 'Please select Prompt card 1'};
    }
    else if (ResponseCardId.length == 0) {
        return {'status': false, 'message': 'Please select Prompt card 2 '};
    }
    else if (response_card_type == '') {
        return {'status': false, 'message': 'Please select Response Type'};
    }
    return {'status': true, 'message': ''};
}
function TrueFalseGroupTypeValidation() {
    var PromptCardId = $("input[name='truefalsegruoppromptcardid[]']:checked").map(function () {
        return $(this).val();
    }).get();
    var cardsId = $("#true_false_prompt_group_card_id").val();
    var card_no = $('#TrueFalseNoOfCard').val();
    const cardsIdLength = (!!cardsId && cardsId != '') ? cardsId.length : 0;
    var total = PromptCardId.length + cardsIdLength;
    if (card_no == '') {
        return {'status': false, 'message': 'Please Enter no of card'};
    }
    else if (PromptCardId.length == 0 && cardsId.length == 0) {
        return {'status': false, 'message': 'Please select Prompt card 1'};
    }
    else if (parseInt(card_no) > total) {
        return {'status': false, 'message': 'Please select more Card than no of cards'};
    }
    return {'status': true, 'message': ''};
}
function TrueFalseCustomTypeValidation() {
    var prompt_type = $('#true_false_prompt_type').val();
    var response_type = $('#true_false_response_type').val();
    var PromptInputHtml = $('#TrueFalseCustomPromptInput').val();
    var PromptCardId = $("input[name='TrueFalseCustomPromptInput']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseInputHtml = $('#TrueFalseCustomResponseInput').val();
    var ResponseCardId = $("input[name='TrueFalseCustomResponseInput']:checked").map(function () {
        return $(this).val();
    }).get();
    var response_card_type = $('#response_custom_type_true_false').val();
    if (prompt_type == '') {
        return {'status': false, 'message': 'Please select Prompt #1 type'};
    }
    else if (prompt_type == 'html' && PromptInputHtml == '') {
        return {'status': false, 'message': 'Please enter Prompt #1 html'};
    }
    else if (prompt_type == 'card' && PromptCardId.length == 0) {
        return {'status': false, 'message': 'Please enter Prompt #1 card'};
    }
    else if (response_type == '') {
        return {'status': false, 'message': 'Please select Prompt #2 type.'};
    }
    else if (response_type == 'html' && ResponseInputHtml == '') {
        return {'status': false, 'message': 'Please enter Prompt #2 html'};
    }
    else if (response_type == 'card' && ResponseCardId.length == 0) {
        return {'status': false, 'message': 'Please enter Prompt #2 card'};
    }
    else if (response_card_type == '') {
        return {'status': false, 'message': 'Please select Response Type'};
    }
    return {'status': true, 'message': ''};
}
function multipleChoiceCardTypeValidation() {
    var PromptCardId = $("input[name='prompt_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseCardId = $("input[name='response_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    var card_id = $('#card_id').val();
    var card_group_id = $('#card_group_id').val();
    if (PromptCardId.length == 0) {
        return {'status': false, 'message': 'Please select Prompt Card '};
    }
    else if (ResponseCardId.length == 0) {
        return {'status': false, 'message': 'Please select Responce Card '};
    }
    else if (card_id == null && card_group_id == '') {
        return {'status': false, 'message': 'Please select at least one option card or group'};
    }
    else {
        return {'status': true, 'message': ''};
    }
}
function multipleChoiceGroupTypeValidation() {
    var noofcard = $("#noofcard").val();
    if (noofcard == '') {
        return {'status': false, 'message': 'Please enter number of cards'};
    }
    var singleCardIds = $('#promptGroupCardId').val();
    if (singleCardIds == null)
    {
        singleCardIds = [];
    }
    var checkedVals = $('.promotegroupcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    var singleCardIds1 = $('#group_card_option_id').val();
    if (singleCardIds1 == null) {
        singleCardIds1 = [];
    }
    var checkedVals1 = $('.optiongroupcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds1 = $.merge($.merge([], singleCardIds1), checkedVals1);
    var allUniqueCardIds1 = unique(allcardIds1);

//    if (allUniqueCardIds1.length > allUniqueCardIds.length) {
//        var result = allUniqueCardIds1.diff(allUniqueCardIds);
//        var intersectresult = intersect(allUniqueCardIds1, allUniqueCardIds);
//    } else {
//        var result = allUniqueCardIds.diff(allUniqueCardIds1);
//        var intersectresult = intersect(allUniqueCardIds, allUniqueCardIds1);
//    }


    if (allUniqueCardIds.length == 0) {
        return {'status': false, 'message': 'Please select Prompt card'};
    }
    else if (noofcard > allUniqueCardIds.length) {
        return {'status': false, 'message': 'Please select ' + (noofcard - allUniqueCardIds.length) + ' more cards'};
    }
    else if (allUniqueCardIds1.length == 0) {
        return {'status': false, 'message': 'Please select Option Card.'};
    }
//    else if (result.length == 0 || (allUniqueCardIds1.length == intersectresult.length)) {
//        return {'status': false, 'message': 'Please select other option card which is not same as prompt card'};
//    }
    else {
        return {'status': true, 'message': ''};
    }
}
function multipleChoiceCustomTypeValidation() {
    var prompt_type = $("#mcq_custom_prompt_type").val();
    var PromptCard = $("input[name='MCQCustomPrompt']:checked").map(function () {
        return $(this).val();
    }).get();
    var PromptHtml = $("#MCQCustomPrompt").val();

    var response_type = $("#mcq_custom_response_type").val();
    var ResponseCard = $("input[name='MCQCustomResponse']:checked").map(function () {
        return $(this).val();
    }).get();
    var responseHtml = $("#MCQCustomResponse").val();

    var NoOfOption = $("#mcq_custom_option_no").val();
    var OptionType = false
    $(".customMCQOption").each(function (index) {
        if ($(this).val() == '' && OptionType != true) {
            OptionType = true;
        }
    });

    var OptionhtmlValidate = false
    $(".OptionCustomTextarea").each(function (index) {
        if ($(this).val() == '' && OptionhtmlValidate != true) {
            OptionhtmlValidate = true;
        }
    });

    var OptionCardValidate = false
    for (var i = 1; i <= NoOfOption; i++) {
        var otype = $("#Mcq_custom_Option_type" + i).val();
        if (otype == 'card') {
            var option = $("input[name='MCQOptionCustom" + i + "'][type='radio']:checked").map(function () {
                return $(this).val();
            }).get();
            if (option.length == 0 && OptionCardValidate != true) {
                OptionCardValidate = true;
            }
        }
    }

    if (prompt_type == '') {
        return {'status': false, 'message': 'Please select Prompt Type'};
    }
    else if (prompt_type == 'card' && PromptCard.length == 0) {
        return {'status': false, 'message': 'Please select Prompt Card'};
    }
    else if (prompt_type == 'html' && PromptHtml == '') {
        return {'status': false, 'message': 'Please enter prompt Html'};
    }
    else if (response_type == '')
    {
        return {'status': false, 'message': 'Please select Response Type'};
    }
    else if (response_type == 'card' && ResponseCard.length == 0) {
        return {'status': false, 'message': 'Please select Response Card'};
    }
    else if (response_type == 'html' && responseHtml == '') {
        return {'status': false, 'message': 'Please type response Html'};
    }
    else if (NoOfOption == '' || NoOfOption == 0) {
        return {'status': false, 'message': 'Please enter at least 1 for Number of Options'};
    }
    else if (NoOfOption > 3) {
        return {'status': false, 'message': 'Please select 3 or fewer options'};
    }
    else if (OptionType) {
        return {'status': false, 'message': 'Please select type for Option'};
    }
    else if (OptionhtmlValidate) {
        return {'status': false, 'message': 'Html cannot be blank.'};
    }
    else if (OptionCardValidate) {
        return {'status': false, 'message': 'Please select an Option card.'};
    }
    else
    {
        return {'status': true, 'message': ''};
    }
}
function MatchThePairCardTypeValidation() {
    var noofcard = $("#MatchThePairCardNo").val();
    for (i = 1; i <= noofcard; i++) {
        var prompt_card_id = $("input[name='match_the_pair_prompt_card_id" + i + "']:checked").val();
        var response_card_id = $("input[name='match_the_pair_response_card_id" + i + "']:checked").val();
        if (typeof prompt_card_id === "undefined") {
            return {'status': false, 'message': 'Please select Prompt Card #' + i};
        }
        if (typeof response_card_id === "undefined") {
            return {'status': false, 'message': 'Please select Response Card #' + i};
        }
    }
    if (noofcard == '') {
        return {'status': false, 'message': 'Please enter Number of Cards'};
    }
    else {
        return {'status': true, 'message': ''};
    }
}
function MatchThePairGroupTypeValidation() {
    var noofcard = $("#MatchThePairCardGroupNo").val();
    var singleCardIds = $('#MatchThePairPromptGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.optiongroupcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    if (allUniqueCardIds.length == 0) {
        return {'status': false, 'message': 'Please select at least one card from the cards list.'};
    }
    if (noofcard == '') {
        return {'status': false, 'message': 'Please enter Number of Cards'};
    }
    else if (allUniqueCardIds.length == 0) {
        return {'status': false, 'message': 'Please select at least one card from the cards list.'};
    }
    else if (allUniqueCardIds.length < noofcard) {
        return {'status': false, 'message': 'Please select ' + (noofcard - allUniqueCardIds.length) + ' more cards from the cards list.'};
    }
    return {'status': true, 'message': ''};
}
function MatchThePairCustomTypeValidation() {
    var noofcard = $("#MatchThePairCustomNo").val()
    var cardBlank = false;
    var PRType = false;
    var htmltype = false;
    $(".customPrompt").each(function (index) {
        if ($(this).val() == '' && PRType != true) {
            PRType = true;
        }
    });
    for (var s = 1; s <= noofcard; s++) {
        var pType = '';
        var rType = '';
        pType = $("#match_the_pair_custom_prompt_type" + s).val();
        rType = $("#match_the_pair_custom_response_type" + s).val();
        if (pType == 'html') {
            var htmlVal = $("#Match_The_Pair_Prompt_" + s).val();
            if (htmlVal == '') {
                htmltype = true;
                break;
            }
        }
        else {
            var PromptCardId = $("input[name='Match_The_Pair_Custom_Prompt_" + s + "']:checked").map(function () {
                return $(this).val();
            }).get();
            if (PromptCardId.length == 0) {
                cardBlank = true;
                break;
            }
        }
        if (rType == 'html') {
            var htmlVal = $("#Match_The_Pair_Response_" + s).val();
            if (htmlVal == '') {
                htmltype = true;
                break;
            }
        }
        else {
            var responseCardId = $("input[name='Match_The_Pair_Custom_Response_" + s + "']:checked").map(function () {
                return $(this).val();
            }).get();
            if (responseCardId.length == 0) {
                cardBlank = true;
                break;
            }
        }
    }
    if (noofcard == 0 || noofcard == '') {
        return {'status': false, 'message': 'Please enter at least 1 for Number of Cards'};
    }
    if (noofcard > 6) {
        return {'status': false, 'message': 'Number of Cards cannot be greater than 6.'};
    }
    else if (cardBlank) {
        return {'status': false, 'message': 'Please select the prompt or response card Id.'};
    }
    else if (PRType) {
        return {'status': false, 'message': 'Please select the prompt or response type.'};
    }
    else if (htmltype) {
        return {'status': false, 'message': 'Please type the prompt or response html.'};
    }
    return {'status': true, 'message': ''};
}
function RecordingCardValidation() {
    var PromptCardId = $("input[name='recording_prompt_card']:checked").map(function () {
        return $(this).val();
    }).get();
    var ResponseCardId = $("input[name='recording_response_card']:checked").map(function () {
        return $(this).val();
    }).get();
    if (PromptCardId.length == 0) {
        return {'status': false, 'message': 'Please select a prompt card'};
    }
    else if (ResponseCardId.length == 0) {
        return {'status': false, 'message': 'Please select a response card '};
    }
    return {'status': true, 'message': ''};
}
function RecordingCardGroupValidation() {
    var noofcard = $("#recordinnoofcard").val();
    var singleCardIds = $('#recordingPromptGroupCardId').val();
    if (singleCardIds == null) {
        singleCardIds = [];
    }
    var checkedVals = $('.recording_group_cardcheck:checkbox:checked').map(function () {
        return this.value;
    }).get();
    var allcardIds = $.merge($.merge([], singleCardIds), checkedVals);
    var allUniqueCardIds = unique(allcardIds);
    if (noofcard == '') {
        return {'status': false, 'message': 'Please enter Number of Cards'};
    }
    else if (allUniqueCardIds.length == 0) {
        return {'status': false, 'message': 'Please select at least one card from the cards list.'};
    }
    else if (allUniqueCardIds.length < noofcard) {
        return {'status': false, 'message': 'Please select ' + (noofcard - allUniqueCardIds.length) + ' more card from the cards list.'};
    }
    return {'status': true, 'message': ''};
}
function RecordingCustomValidation() {
    var customPromptType = $("#recording_custom_prompt_type").val();
    var customPromptTypeHtml = $("#Recording").val();
    var PromptCardId = $("input[name='Recording_Custom']:checked").map(function () {
        return $(this).val();
    }).get();
    var responseCardId = $("input[name='recording_custom_response_card_id']:checked").map(function () {
        return $(this).val();
    }).get();
    if (customPromptType == '') {
        return {'status': false, 'message': 'Please select a Prompt Type.'};
    }
    else if (customPromptType == 'html' && customPromptTypeHtml == '') {
        return {'status': false, 'message': 'Please type valid html.'};
    }
    else if (customPromptType == 'card' && PromptCardId.length == 0) {
        return {'status': false, 'message': 'Please select a Prompt Card.'};
    }
    else if (responseCardId.length == 0) {
        return {'status': false, 'message': 'Please select a Response Card.'};
    }
    return {'status': true, 'message': ''};
}
function unique(list) {
    var result = [];
    $.each(list, function (i, e) {
        if ($.inArray(e, result) == -1)
            result.push(e);
    });
    return result;
}
function intersect(a, b) {
    var d = {};
    var results = [];
    for (var i = 0; i < b.length; i++) {
        d[b[i]] = true;
    }
    for (var j = 0; j < a.length; j++) {
        if (d[a[j]])
            results.push(a[j]);
    }
    return results;
}
Array.prototype.diff = function (a) {
    return this.filter(function (i) {
        return a.indexOf(i) < 0;
    });
};