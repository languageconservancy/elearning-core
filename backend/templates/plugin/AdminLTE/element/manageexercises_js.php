<?php

//phpcs:disable
use Cake\Routing\Router;

function htmlToTextAreaPreview($htmltext)
{
    $Htmlval = '';
    $Htmlval = str_replace(array("\r", "\n" ,), '@@', $htmltext);
    $Htmlval = preg_replace("/'/", "\&#39;", $Htmlval);
    $Htmlval = str_replace(array("@@@@"), '@', $Htmlval);
    $Htmlval = str_replace(array("@"), '\n', $Htmlval);
    $Htmlval = str_replace(array("\&#39;"), "'", $Htmlval);
    return $Htmlval;
}
//phpcs:enable
?>

function getAjaxUrl(urlvalue){
    return '<?php echo Router::url('/');?>' + urlvalue;
}

var getFromBetween = {
    results:[],
    string:"",
    getFromBetween:function (sub1,sub2) {
        if (this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0) {
            return false;
        }
        var SP = this.string.indexOf(sub1)+sub1.length;
        var string1 = this.string.substr(0,SP);
        var string2 = this.string.substr(SP);
        var TP = string1.length + string2.indexOf(sub2);
        return this.string.substring(SP,TP);
    },
    removeFromBetween:function (sub1,sub2) {
        if (this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0) {
            return false;
        }
        var removal = sub1+this.getFromBetween(sub1,sub2)+sub2;
        this.string = this.string.replace(removal,"");
    },
    getAllResults:function (sub1,sub2) {
        // first check to see if we do have both substrings
        if (this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0) {
            return;
        }

        // find one result
        var result = this.getFromBetween(sub1,sub2);
        // push it to the results array
        this.results.push(result);
        // remove the most recently found one from the string
        this.removeFromBetween(sub1,sub2);

        // if there's more substrings
        if (this.string.indexOf(sub1) > -1 && this.string.indexOf(sub2) > -1) {
            this.getAllResults(sub1,sub2);
        } else {
            return;
        }
    },
    get:function (string,sub1,sub2) {
        this.results = [];
        this.string = string;
        this.getAllResults(sub1,sub2);
        return this.results;
    }
};

    function deleteExercise(exerciseId) {
        var r = confirm("Are you sure you want to delete this Exercise?");
        if (r == true) {
            var data={'exerciseId':exerciseId};
            $.ajax({
                type: "POST",
                url: `
                    <?php echo Router::url(
                        [
                            'controller' => 'Exercises',
                            'action' => 'exerciseDeleteWarning',
                            'prefix' => 'Admin'
                        ],
                        true
                    ); ?>`,
                data: data,
                success: function (res) {
                    if (res != 'success') {
                        $('#exerciseDeleteDiv').html(res);
                        $('#exerciseDeleteModal').modal("show");
                    } else {
                        window.location.href = `
                            <?php echo Router::url(
                                [
                                    'controller' => 'Exercises',
                                    'action' => 'deleteExercises',
                                    'prefix' => 'Admin'
                                ],
                                true
                            ); ?>/${exerciseId}`;
                    }
                }
            });
        }
    }

<?php // if isset($exercise->id) Start
if (isset($exercise->id)) { ?>
    function previewCheckBoxCheck(previewType, className) {
        const previewData = {
            'prompt': <?php echo !empty($exercise->promotetype) ? json_encode(explode(",", $exercise->promotetype)) : '[]'; ?>,
            'response': <?php echo !empty($exercise->responsetype) ? json_encode(explode(",", $exercise->responsetype)) : '[]'; ?>
        };

        if (previewData[previewType]) {
            previewData[previewType].forEach(function(value) {
                $("input[class=" + className + "][value=" + value.trim() + "]").prop('checked', true);
            });
        }
    }

    /* Fill In The blank Card Function Start */
    function generateAndSetOptionHtml() {
        var optionCounter=$('#option_counter').val();
        var optionCounterHtml='';
        for (var i = 1; i <= optionCounter; i++) {
            optionCounterHtml += '<div class="row"><div class="col-sm-12">'
                + '<input name="fill_blank_lakota_options[]" '
                + 'class="form-control fill_option" type="text" id="fill_blank_lakota_options'+i+'"></div></div>';
        }
        $('#choise_div').html(optionCounterHtml);

        var previewstring=$("#fill_blank_lakota").val();
        var result = getFromBetween.get(previewstring,"[","]");
        var type=$('#fill_in_the_blank_type').val();
        if (type=='mcq') {
            <?php if (!empty($FillInTheBlankOptions)) { ?>
                var op=JSON.parse(`<?php echo json_encode($FillInTheBlankOptions)?>`);
                $( ".fill_option" ).each(function( index ) {
                    if (op[index] !== undefined) {
                        $(this).val(op[index]['text_option']);
                    }
                });
                <?php
            } ?>
        }
        $.each(result, function( indexMain, value ) {
            $( ".fill_option" ).each(function( index ) {
                if (index==indexMain) {
                    $(this).val(value).prop("readonly", true);
                }
            });
        });
    }

    function setFillInTheBlankPromoteResponseCheck() {
        previewCheckBoxCheck('prompt','FillInTheBlankCardPromptType');
        previewCheckBoxCheck('response','FillInTheBlankCardResponseType');
    }
    /* Fill In The Card Function End */

    /* Fill In The Custom Function Start */
    function setFillInTheBlankCustomValue() {
        var type=$("#fill_in_the_blanks_prompt_type").val();
        if (type=='html') {
            <?php
            if (!empty($exercise->exerciseoptions[0]['exercise_custom_options'])) {
                $Htmlval = htmlToTextAreaPreview(
                    $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_html']
                );
                ?>
                fromhtmlvalue= <?php echo json_encode($Htmlval);?>;
                $('#Fill_In_The_Blanks').text(fromhtmlvalue);

                <?php
                if (!empty($exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id'])) { ?>
                    SetFile(
                        <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id']?>,
                        'Fill_In_The_Blanks'
                    );
                    <?php
                } ?>

                <?php
                if (!empty($exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id'])) { ?>
                    SetFile(
                        <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id']?>,
                        'Fill_In_The_Blanks'
                    );
                    <?php
                }

                $questionHtml = $exercise->exerciseoptions[0]['text_option'];
                ?>
                if ($('#fill_blank_custom_lakota').length) {
                    $('#fill_blank_custom_lakota').val("<?php echo $questionHtml;?>");
                    setTimeout(function() {
                        setFillInTheBalnksCustomPreviewOption();
                    }, 500);
                }
                <?php
            } ?>
        } else if (type=='card') {
            <?php
            if (!empty($exercise->exerciseoptions[0]['card_id'])) {
                $cardid = $exercise->exerciseoptions[0]['card_id'];
                ?>
                $("input[name='Fill_In_The_Balnks_Custom'][value=<?= $cardid;?>]").prop('checked', true);
                <?php
                if (
                    isset($exercise->exerciseoptions[0])
                    && $exercise->exerciseoptions[0]['prompt_preview_option'] != ''
                ) {
                    $ppo = explode(", ", $exercise->exerciseoptions[0]['prompt_preview_option']);
                    foreach ($ppo as $op) { ?>
                        $("input[class=Fill_In_The_Balnks_CustomRadio][value=<?php echo $op;?>]").prop('checked', true);
                        <?php
                    }
                }
                $questionHtml = $exercise->exerciseoptions[0]['text_option'];
                ?>
                if ($('#fill_blank_custom_lakota').length) {
                    $('#fill_blank_custom_lakota').val("<?php echo $questionHtml;?>");
                    setTimeout(function() {
                        setFillInTheBalnksCustomPreviewOption();
                    });
                }
                <?php
            } ?>
        }
    }
    /* Fill In The Custom Function End */

    /* Anagram Group Function Start */
     function setAnagramGroupPromoteResponseCheck() {
        previewCheckBoxCheck('prompt','anagramGroupPromptType');
        previewCheckBoxCheck('response','anagramGroupResponseType');
    }
     /* Anagram Group Function End */

     /* Anagram Card Function Start */
    function seteditPreviewType() {
        previewCheckBoxCheck('prompt','anagramCardPromptType');
        previewCheckBoxCheck('response','anagramCardResponseType');
        previewAnagramCard();
    }
    /* Anagram Card Function End */

    /* True False Group Function Start */
    function setPromoteResponseCheck() {
        previewCheckBoxCheck('prompt','TrueFalseGruopPromptRadio');
        previewCheckBoxCheck('response','TrueFalseGruopResponseRadio');
    }
    /*True False Group Function End*/

    /*True False Custom Function Start*/
    function generateTruefalseHtml(InputName, Divid, type, promptOrResponse='') {
        if (type == 'html') {
            var textareahtml = '';
            var fromhtmlvalue = '';
            if (Divid == 'trueFalsePromptForm') {
                <?php
                if (
                    isset($exercise->exerciseoptions[0])
                    && isset($exercise->exerciseoptions[0]['exercise_custom_options'][0])
                    && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_html'] != ''
                ) {
                    $Htmlval = htmlToTextAreaPreview(
                        $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_html']
                    );
                    ?>
                    fromhtmlvalue = <?php echo json_encode($Htmlval);?>;
                    <?php
                }
                ?>
            }
            else if (Divid == 'trueFalseResponseForm') {
                <?php
                if (
                    isset($exercise->exerciseoptions[0])
                    && isset($exercise->exerciseoptions[0]['exercise_custom_options'][0])
                    && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_html'] != ''
                ) {
                    $Htmlval = htmlToTextAreaPreview(
                        $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_html']
                    );
                    ?>
                    fromhtmlvalue = <?php echo json_encode($Htmlval);?>;
                    <?php
                }
                ?>
            }

            var fromhtml = '' +
                '<div class="col-sm-6">Html (Unity only support b,i,size and color tag of html) ' +
                    '<textarea class="form-control" name="' + InputName + '" id="' +
                    InputName + '">' + fromhtmlvalue + '</textarea>' +
                '</div>' +
                '<div class="col-sm-3">' +
                    '<button type="button" class="btn btn-default pick-image" data-toggle="modal"' +
                        'data-target="#fileLibrary" id="pick-image" data-name="' +
                        InputName + '">Choose Image</button><br><br>' +
                    '<button type="button" class="btn btn-default pick-audio" data-toggle="modal"' +
                        'data-target="#fileLibrary" id="pick-audio" data-name="'+InputName+'">Choose Audio</button>' +
                '</div>' +
                '<div class="col-sm-3">' +
                    '<img id="' + InputName + '_imagePreview" height="70" src="" class="truefalseimage"><br><br>' +
                    '<audio controls id="' + InputName +'audioPreview" class="truefalseaudio" style="display: none;">' +
                        '<source class="' + InputName +'_audio-source" type="audio/mpeg">' +
                        '<source class="' + InputName +'_audio-source"  type="audio/ogg">' +
                        '<source class="' + InputName +'_audio-source" type="audio/wav">' +
                        'Your browser does not support the audio element.' +
                    '</audio>' +
                    '<input type="hidden" id="' + InputName + '_image_id" name="' + InputName + '_image_id">' +
                    '<input type="hidden" id="' + InputName + '_audio_id" name="' + InputName + '_audio_id">' +
                '</div>';
            $('#'+Divid).html(fromhtml);
            <?php
            if (!empty($truefalseCustomAsset['prompt_audio'])) { ?>
                $('.TrueFalseCustomPromptInput_audio-source').attr(
                    'src', '<?php echo $truefalseCustomAsset['prompt_audio']->FullUrl?>');
                $('#TrueFalseCustomPromptInputaudioPreview').show();
                const trueFalseCustomPromptAudioPreview = document.getElementById('TrueFalseCustomPromptInputaudioPreview');
                if (trueFalseCustomPromptAudioPreview) {
                    trueFalseCustomPromptAudioPreview.load();
                }
                $('#TrueFalseCustomPromptInput_audio_id').val(<?php echo $truefalseCustomAsset['prompt_audio']['id']?>);
                <?php
            } ?>

            <?php
            if (!empty($truefalseCustomAsset['prompt_image'])) { ?>
                var imgSrc = '<?php echo $truefalseCustomAsset['prompt_image']->FullUrl?>';
                $('#TrueFalseCustomPromptInput_imagePreview').attr('src', imgSrc);
                $('#TrueFalseCustomPromptInput_imagePreview').show();
                $('#TrueFalseCustomPromptInput_image_id').val(<?php echo $truefalseCustomAsset['prompt_image']['id']?>);
                <?php
            } ?>

            <?php

            //pr($truefalseCustomAsset['responce_image']['FullUrl']);

            if (!empty($truefalseCustomAsset['responce_image'])) {?>
                var imgSrc = '<?php echo $truefalseCustomAsset['responce_image']['FullUrl']?>';
                $('#TrueFalseCustomResponseInput_imagePreview').attr('src', imgSrc);
                $('#TrueFalseCustomResponseInput_imagePreview').show();
                $('#TrueFalseCustomResponseInput_image_id')
                    .val(<?php echo $truefalseCustomAsset['responce_image']['id']?>);
                <?php
            } ?>

            <?php
            if (!empty($truefalseCustomAsset['responce_audio'])) {?>
                $('.TrueFalseCustomResponseInput_audio-source')
                    .attr('src', '<?php echo $truefalseCustomAsset['responce_audio']->FullUrl?>');
                $('#TrueFalseCustomResponseInputaudioPreview').show();
                const trueFalseCustomResponseAudioPreview = document.getElementById('TrueFalseCustomResponseInputaudioPreview');
                if (trueFalseCustomResponseAudioPreview) {
                    trueFalseCustomResponseAudioPreview.load();
                }
                $('#TrueFalseCustomResponseInput_audio_id')
                    .val(<?php echo $truefalseCustomAsset['responce_audio']['id']?>);
                <?php
            } ?>
        } else if (type == 'card') {
            var data = {InputName: InputName};
            $.ajax({
                type: "POST",
                processing: true,
                url: API_ROUTES.getTrueFalseCardPickerHtml,
                data: data,
                cache: false,
                success: function (data) {
                    $('#' + Divid).html(data);

                    <?php
                    $promptCardId = $exercise->exerciseoptions[0]['card_id'] ?? null;
                    $responseCardId = $exercise->exerciseoptions[0]['responce_card_id'] ?? null;
                    ?>
                    const cardId = promptOrResponse === 'prompt'
                        ? <?= json_encode($promptCardId); ?>
                        : <?= json_encode($responseCardId); ?>;
                    const name = promptOrResponse === 'prompt'
                        ? 'TrueFalseCustomPromptInput'
                        : 'TrueFalseCustomResponseInput';

                    // Init DataTable
                    const table = processDataTable(
                        `${InputName}Table`, // id
                        name, // name
                        name, // class
                        cardId, // value
                        '', // blockNo
                        '', // responseType
                        API_ROUTES.getCardList, // url
                    );

                    setRadioButtonValue();
                    <?php
                    if (isset($exercise->exerciseoptions[0])) {

                        if ($exercise->exerciseoptions[0]['card_id'] != '') { ?>
                            $("input[name=TrueFalseCustomPromptInput][value=" +
                                "<?php echo $exercise->exerciseoptions[0]['card_id']?>]").prop('checked', true);
                            <?php
                        } ?>

                        <?php
                        if ($exercise->exerciseoptions[0]['responce_card_id'] != '') { ?>
                            $("input[name=TrueFalseCustomResponseInput][value=" +
                                "<?php echo $exercise->exerciseoptions[0]['responce_card_id']?>]").prop('checked', true);
                            <?php
                        } ?>

                        <?php
                        if ($exercise->exerciseoptions[0]['prompt_preview_option'] != '') {
                            $ppo = explode(", ", $exercise-> exerciseoptions[0]['prompt_preview_option']);
                            foreach ($ppo as $op) { ?>
                                $("input[class=TrueFalseCustomPromptInputRadio][value=" +
                                    "<?php echo $op;?>]").prop('checked', true);
                                <?php
                            }
                        } ?>

                        <?php
                        if ($exercise->exerciseoptions[0]['responce_preview_option'] != '') {
                            $rpo = explode(", ", $exercise-> exerciseoptions[0]['responce_preview_option']);
                            foreach ($rpo as $op) { ?>
                                $("input[class=TrueFalseCustomResponseInputRadio][value=" +
                                    "<?php echo $op;?>]").prop('checked', true);
                                <?php
                            }
                        }
                    } ?>
                },
                error: function (e) {
                    console.error(e);
                    $('#' + Divid).html('');
                }
            });
        } else {
            $('#' + Divid).html('');
        }
    }
    /*True False custom Function End*/

    /* Match The pair Custom Function Start */
    function setMatchThePairCustomHtmlElement() {
        <?php
        $i = 1;
        foreach ($exercise->exerciseoptions as $o) { ?>
            var block = <?php echo $i;?>;
            <?php
            if ($o['card_id'] != null) { ?>
                $("#match_the_pair_custom_prompt_type<?php echo $i;?>").val('card');
                getCustomElement(block, 'Prompt', 'card');
                setTimeout(function() {
                    $("input[name=Match_The_Pair_Custom_Prompt_<?php echo $i;?>][value=<?php echo $o['card_id'];?>]")
                        .prop('checked', true);
                    <?php
                    $parr = explode(",", $o->prompt_preview_option);
                    foreach ($parr as $p) { ?>
                        $("input[name='Match_The_Pair_Custom" +
                            "<?php echo $i;?>PromptRadio[]'][value='<?php echo trim($p);?>']")
                            .prop('checked', 'checked');
                        <?php
                    } ?>
                    getMatchThePairPreview(<?php echo $i;?>, 'Prompt');
                }, 800);
                <?php
            } else { ?>
                $("#match_the_pair_custom_prompt_type<?php echo $i;?>").val('html');
                getCustomElement(block, 'Prompt', 'html');

                <?php
                if (
                    isset($o['exercise_custom_options'][0])
                    && isset($o['exercise_custom_options'][0]['prompt_html'])
                ) {
                    $Htmlval = htmlToTextAreaPreview($o['exercise_custom_options'][0]['prompt_html']); ?>
                    fromhtmlvalue= <?php echo json_encode($Htmlval);?>;
                    $('#Match_The_Pair_Prompt_<?php echo $i;?>').val(fromhtmlvalue);
                    <?php
                } ?>
                <?php
                if (
                    isset($o['exercise_custom_options'][0])
                    && $o['exercise_custom_options'][0]['prompt_audio_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $o['exercise_custom_options'][0]['prompt_audio_id'];?>,
                        'Match_The_Pair_Prompt_' + block
                    );
                    <?php
                } ?>
                <?php
                if (
                    isset($o['exercise_custom_options'][0])
                    && $o['exercise_custom_options'][0]['prompt_image_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $o['exercise_custom_options'][0]['prompt_image_id'];?>,
                        'Match_The_Pair_Prompt_'+block
                    );
                    <?php
                } ?>
                getMatchThePairPreview(<?php echo $i;?>, 'Prompt');
                <?php
            }

            if ($o['responce_card_id'] != null) { ?>
                $("#match_the_pair_custom_response_type<?php echo $i;?>").val('card');
                getCustomElement(block, 'Response', 'card');
                setTimeout(function () {
                    $("input[name=Match_The_Pair_Custom_Response_" +
                        "<?php echo $i;?>][value=<?php echo $o['responce_card_id'];?>]").prop('checked', true);
                    <?php
                    $rarr = explode(",", $o->responce_preview_option);
                    foreach ($rarr as $r) { ?>
                        $("input[name='Match_The_Pair_Custom" +
                            "<?php echo $i;?>ResponseRadio[]'][value='<?php echo trim($r);?>']")
                            .prop('checked', 'checked');
                        <?php
                    } ?>
                    getMatchThePairPreview(<?php echo $i;?>, 'Response');
                }, 800);
                <?php
            } else { ?>
                $("#match_the_pair_custom_response_type<?php echo $i;?>").val('html');
                getCustomElement(block, 'Response', 'html');

                <?php
                if (
                    isset($o['exercise_custom_options'][0])
                    && isset($o['exercise_custom_options'][0]['response_html'])
                ) {
                    $Htmlval = htmlToTextAreaPreview($o['exercise_custom_options'][0]['response_html']); ?>
                    fromhtmlvalue= <?php echo json_encode($Htmlval);?>;
                    $('#Match_The_Pair_Response_<?php echo $i;?>').val(fromhtmlvalue);
                    <?php
                } ?>
                <?php
                if (
                    isset($o['exercise_custom_options'][0])
                    && $o['exercise_custom_options'][0]['response_image_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $o['exercise_custom_options'][0]['response_image_id'];?>,
                        'Match_The_Pair_Response_' + block
                    );
                    <?php
                } ?>
                <?php
                if (
                    isset($o['exercise_custom_options'][0])
                    && $o['exercise_custom_options'][0]['response_audio_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $o['exercise_custom_options'][0]['response_audio_id'];?>,
                        'Match_The_Pair_Response_'+block
                    );
                    <?php
                } ?>
                getMatchThePairPreview(<?php echo $i;?>, 'Response');
                <?php
            } ?>

            <?php
            //print_r($o['exercise_custom_options']);
            $i++;
        } ?>
    }
    /* Match The pair Custom Function End */

    /*Match The Pair Group Function Start*/
    function setMatchThePairGroupCheckedOption() {
        previewCheckBoxCheck('prompt','matchthepairpromptgrouptype');
        previewCheckBoxCheck('response','matchthepairresponsegrouptype');
    }
    /* Match The Pair Group Function End */

    /* Match The Pair Card Function Start */
    function setMatchThePairCardPreviewOption() {
        <?php
        $noofcardmatch = $exercise->noofcard;
        for ($k = 1; $k <= $noofcardmatch; $k++) {
            $pcheckvalue = isset($matchThePairvalue['promptpreview' . $k])
                ? explode(", ", $matchThePairvalue['promptpreview' . $k]) : array();
            foreach ($pcheckvalue as $pv) { ?>
                $("input[name='MatchThePairCardPromptType<?php echo $k;?>[]'][value='<?php echo $pv;?>']")
                    .prop('checked', 'checked');
                <?php
            }
            $rcheckvalue = isset($matchThePairvalue['responcepreview' . $k])
                ? explode(", ", $matchThePairvalue['responcepreview' . $k]) : array();
            foreach ($rcheckvalue as $rv) { ?>
                $("input[name='MatchThePairCardResponseType<?php echo $k;?>[]'][value='<?php echo $rv;?>']")
                    .prop('checked', 'checked');
                <?php
            }
        } ?>
    }

    function getMatchThePairCHtml() {
        var count = $('#MatchThePairCardNo').val();
        var type = $('#card_type').val();
        var data = {
            count: count,
            type: type,
            exerciseId: <?= $exercise->id; ?>
        };

        $.ajax({
            type: "POST",
            processing: true,
            url: API_ROUTES.getMatchThePairHtml,
            data: data,
            cache: false,
            success: function (data) {
                const matchThePairValue = <?= json_encode($matchThePairvalue); ?>;
                $('#MatchThePairCardSectionHtml').html(data);

                let completedTables = 0; // Track completed table rendres

                for (let i = 1; i <= count; i++) {
                    const promptValue = matchThePairValue[`promptcard${i}`];
                    const promptTable = processDataTable( //MatchThePairCardSectionResponseTable
                        `MatchThePairCardSectionPromptTable${i}`, // id
                        `match_the_pair_prompt_card_id${i}`, // name
                        'pair-prompt-card', // class
                        promptValue, // value
                        '', // blockNo
                        '', // responseType
                        API_ROUTES.getCardList, // url
                    );

                    const responseValue = matchThePairValue[`responcecard${i}`];
                    const responseTable = processDataTable( //MatchThePairCardSectionResponseTable
                        `MatchThePairCardSectionResponseTable${i}`, // id
                        `match_the_pair_response_card_id${i}`, // name
                        'pair-response-card', // class
                        responseValue, // value
                        '', // blockNo
                        '', // responseType
                        API_ROUTES.getCardList, // url
                    );

                    promptTable.on('draw.dt', function() {
                        completedTables++;
                        checkIfAllTablesRendered();
                    });
                    responseTable.on('draw.dt', function() {
                        completedTables++;
                        checkIfAllTablesRendered();
                    });
                }

                function  checkIfAllTablesRendered() {
                    if (completedTables === count * 2) {
                        setRadioButtonValue();
                        setMatchThePairCardPreviewOption();
                        setMatchThePairPreview();
                    }
                }
            },
            error: function (e) {
                console.error(e);
                $('#MatchThePairCardSectionHtml').html('');
            }
        });
    }
    /* Match The Pair Card Function End */

    /* Recording Card Function Start */
    function setRecordingCardPromoteCheck() {
        previewCheckBoxCheck('prompt','recordingprompttype');
    }
    /*  Recording Card Function End */

    /* Recording Card Group Function Start */
    function setRecordingCardGroupPromoteCheck() {
        previewCheckBoxCheck('prompt','recordingpromptgrouptype');
    }
    /*  Recording Card Group Function End */

    /* Recording Custom Function Start */
    function setRecordingCustomValue() {
        <?php
        if ($exercise->exercise_type == 'recording') {
            if (
                isset($exercise->exerciseoptions[0]['card_id'])
                && $exercise->exerciseoptions[0]['card_id'] != ''
            ) {
                $parr = explode(",", $exercise->promotetype);
                foreach ($parr as $p) { ?>
                    $("input[class=Recording_CustomRadio][value=<?php echo $p;?>]").prop('checked', true);
                    <?php
                } ?>
                $("input[class=Recording_Custom][value=<?php echo $exercise->exerciseoptions[0]['card_id'];?>]")
                    .prop('checked', true);
                <?php
            } else {
                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'])
                    && !empty($exercise->exerciseoptions[0]['exercise_custom_options'])
                ) {
                    $Htmlval = htmlToTextAreaPreview(
                        $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_html']
                    );
                    ?>
                    fromhtmlvalue= <?php echo json_encode($Htmlval);?>;
                    $('#Recording').text(fromhtmlvalue);

                    <?php
                    if (
                        isset($exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id'])
                        && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id'] != ''
                    ) { ?>
                        SetFile(
                            <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id']?>,
                            'Recording');
                        <?php
                    } ?>

                    <?php
                    if (
                        isset($exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id'])
                        && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id'] != ''
                    ) { ?>
                        SetFile(
                            <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id']?>,
                            'Recording');
                        <?php
                    }
                }
            }
        } ?>
        setRecordingCustomPreview();
    }
    /* Recording Custom Function End */

    /* multiple-choice Custom Function Start */
    function SetCustomTypeOptionselectBox() {
        <?php
        $index = 1;//pete
        foreach ($mcqCustomOptionAsset as $CustomOption) {
            if (!empty($CustomOption['responce_card_id'])) { ?>
                $('#Mcq_custom_Option_type<?php echo $index;?>').val('card');
                getMCQCustomOptionHtml(
                    'card',
                    'Mcq_custom_OptioElement<?php echo $index;?>',
                    <?php echo $index;?>,
                    'edit',
                    <?= json_encode($CustomOption['responce_card_id']); ?>,
                );
                setTimeout(function () {
                    $("input[name='MCQOptionCustom<?php echo $index;?>'][value='" +
                        "<?php echo $CustomOption['responce_card_id'];?>']").prop('checked', true);
                    <?php
                    $parr = explode(",", $CustomOption->responce_preview_option);
                    foreach ($parr as $p) { ?>
                        $("input[name='MCQOptionCustom<?php echo $index;?>Radio[]'][value='" +
                            "<?php echo $p;?>']").prop('checked', true);
                        <?php
                    } ?>
                }, 500);
                <?php
            } else { ?>
                $('#Mcq_custom_Option_type<?php echo $index;?>').val('html');
                getMCQCustomOptionHtml(
                    'html',
                    'Mcq_custom_OptioElement<?php echo $index;?>',<?php echo $index;?>,
                    'edit'
                );
                <?php
                $Htmlval = htmlToTextAreaPreview($CustomOption['exercise_custom_options'][0]['response_html']);
                ?>
                fromhtmlvalue= <?php echo json_encode($Htmlval);?>;
                $('#MCQOptionCustom<?php echo $index;?>').text(fromhtmlvalue);

                <?php
                if (
                    isset($CustomOption['exercise_custom_options'][0]['response_image_id'])
                    && $CustomOption['exercise_custom_options'][0]['response_image_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $CustomOption['exercise_custom_options'][0]['response_image_id']?>,
                        'MCQOptionCustom<?php echo $index;?>'
                    );
                    <?php
                } ?>

                <?php
                if (
                    isset($CustomOption['exercise_custom_options'][0]['response_audio_id'])
                    && $CustomOption['exercise_custom_options'][0]['response_audio_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $CustomOption['exercise_custom_options'][0]['response_audio_id']?>,
                        'MCQOptionCustom<?php echo $index;?>'
                    );
                    <?php
                }
            }
            $index++;
        } ?>
    }

    function SetMCQCustomvalue() {
        <?php
        if (
            isset($exercise->exerciseoptions[0])
            && $exercise->card_type == 'custom'
            && $exercise->exercise_type == 'multiple-choice'
        ) {
            if (!empty($exercise->exerciseoptions[0]['card_id'])) { ?>
                // Select the prompt card in the card list
                $("input[name='MCQCustom'][value='<?php echo $exercise->exerciseoptions[0]['card_id'];?>']")
                    .prop('checked', true);

                setTimeout(function () {
                    <?php
                    $parr = explode(",", $exercise->exerciseoptions[0]->prompt_preview_option);
                    foreach ($parr as $p) { ?>
                        $("input[name='MCQCustomPromptRadio[]'][value='<?php echo $p;?>']").prop('checked', true);
                        <?php
                    } ?>
                }, 1000);
                setTimeout(function () {
                    $("input[name='MCQCustomPrompt'][value='<?php echo $exercise->exerciseoptions[0]['card_id'];?>']")
                        .prop('checked', true);
                }, 500);
                <?php
            } else {
                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'])
                    && !empty($exercise->exerciseoptions[0]['exercise_custom_options'])
                ) {
                    $Htmlval = htmlToTextAreaPreview(
                        $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_html']
                    );
                } else {
                    $Htmlval = '';
                } ?>
                var fromhtmlvalue = <?php echo json_encode($Htmlval);?>;
                $('#MCQCustomPrompt').text(fromhtmlvalue);

                <?php
                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id'])
                    && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_image_id'];?>,
                        'MCQCustomPrompt'
                    );
                    <?php
                }

                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id'])
                    && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['prompt_audio_id'];?>,
                        'MCQCustomPrompt'
                    );
                    <?php
                }
            }

            if (isset($exercise->exerciseoptions[0]['responce_card_id']) && $exercise->exerciseoptions[0] != '') { ?>
                setTimeout(function () {
                    <?php
                    $parr = explode(",", $exercise->exerciseoptions[0]->responce_preview_option);
                    foreach ($parr as $p) { ?>
                        $("input[name='MCQCustomResponseRadio[]'][value='<?php echo $p;?>']").prop('checked', true);
                        <?php
                    } ?>
                }, 1000);
                setTimeout(function () {
                    $("input[name='MCQCustomResponse'][value='<?php echo $exercise->exerciseoptions[0]['responce_card_id'];?>']")
                        .prop('checked', true);
                }, 500);
            <?php
            } else {
                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'])
                    && !empty($exercise->exerciseoptions[0]['exercise_custom_options'])
                ) {
                    $Htmlval = htmlToTextAreaPreview(
                        $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_html']
                    );
                } else {
                    $Htmlval = '';
                } ?>
                var fromhtmlvalue = <?php echo json_encode($Htmlval);?>;
                $('#MCQCustomResponse').text(fromhtmlvalue);
                <?php
                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_image_id'])
                    && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_image_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_image_id'];?>,
                        'MCQCustomResponse'
                    );
                    <?php
                }
                if (
                    isset($exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_audio_id'])
                    && $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_audio_id'] != ''
                ) { ?>
                    SetFile(
                        <?php echo $exercise->exerciseoptions[0]['exercise_custom_options'][0]['response_audio_id'];?>,
                        'MCQCustomResponse'
                    );
                    <?php
                }
            }
        } ?>
    }
    /* multiple-choice Custom Function End */

    /* General Function Start */
    function getCardByGroupIds(groupIds, excludeCardIds, divid, onchange) {

        var currentOptionCheckValue = $('.optiongroupcheck:checkbox:checked').map(function () {
            return this.value;
        }).get();

        var currentPromoteCheckValue = $('.promotegroupcheck:checkbox:checked').map(function () {
            return this.value;
        }).get();


        if (divid=='TrueFalseGruopPromptCardCheckbox') {
            var currentTrueFalseGruopCheckValue = $('.truefalsegruoppromptcheck:checkbox:checked').map(function () {
                return this.value;
            }).get();
        }

        if (divid=='anagram_group_single_card_preview') {
            var currentAnagramGruopCheckValue = $('.anagram_group_cardcheck:checkbox:checked').map(function () {
               return this.value;
            }).get();
        }

        if (divid=='RecordingPomptCardList') {
            var currentRecordingGruopCheckValue = $('.recording_group_cardcheck:checkbox:checked').map(function () {
                return this.value;
            }).get();
        }

        var data = {groupIds: groupIds, excludeCardIds: excludeCardIds, divid: divid};
        $.ajax({
            type: "POST",
            url: `
                <?php echo Router::url(
                    [
                        'controller' => 'Exercises',
                        'action' => 'getCardByGroupIds',
                        'prefix' => 'Admin'
                    ],
                    true
                ); ?>`,
            data: data,
            async: false,
            cache: false,
            success: function (data) {
                $('#' + divid).html(data);
                $.each(currentOptionCheckValue, function (i, e) {
                    $("input[class=optiongroupcheck][value=" + e + "]").prop('checked', true)
                });

                $.each(currentPromoteCheckValue, function (i, e) {
                    $("input[class=promotegroupcheck][value=" + e + "]").prop('checked', true)
                });

                if (divid=='TrueFalseGruopPromptCardCheckbox') {
                    $.each(currentTrueFalseGruopCheckValue, function (i, e) {
                        $("input[class=truefalsegruoppromptcheck][value=" + e + "]").prop('checked', true)
                    });
                }

                if (divid=='anagram_group_single_card_preview') {
                    $.each(currentAnagramGruopCheckValue, function (i, e) {
                        $("input[class=anagram_group_cardcheck][value=" + e + "]").prop('checked', true)
                    });
                }

                if (divid=='RecordingPomptCardList') {
                    $.each(currentRecordingGruopCheckValue, function (i, e) {
                        $("input[class=recording_group_cardcheck][value=" + e + "]").prop('checked', true)
                    });
                }


                if (onchange == null) {

                    <?php
                    if (isset($promoteGroupCardIds) && !empty($promoteGroupCardIds)) {
                        foreach ($promoteGroupCardIds as $gcid) { ?>
                            $("#promotegroupcheck<?php echo $gcid ?>").prop('checked', true);
                            <?php
                        }
                    } ?>

                    <?php
                    if (isset($optionGroupCardIds) && !empty($optionGroupCardIds)) {
                        foreach ($optionGroupCardIds as $gcid) { ?>
                            $("#optiongroupcheck<?php echo $gcid ?>").prop('checked', true);
                            <?php
                        }
                    } ?>

                    if (divid=='OptionCardList' || divid=='PomptCardList') {
                        promoteCardGroupPreview();
                        optionCardGroupPreview();
                    }
                }
                if (onchange == null && (divid=='TrueFalseGruopPromptCardCheckbox')) {
                    <?php
                    if (isset($TrueFalseValue['PromptGroupCardId']) && !empty($TrueFalseValue['PromptGroupCardId'])) {
                        foreach ($TrueFalseValue['PromptGroupCardId'] as $pgid) {
                            ?>
                            $("#truefalsegruoppromptcheck<?php echo $pgid ?>").prop('checked', true);
                            <?php
                        }
                    } ?>
                }

                if (onchange == null && (divid=='anagram_group_single_card_preview')) {

                    <?php
                    if (
                        isset($AnagramGroupValue['PromptGroupCardId'])
                        && !empty($AnagramGroupValue['PromptGroupCardId'])
                    ) {
                        foreach ($AnagramGroupValue['PromptGroupCardId'] as $pgid) {
                            ?>
                            $("#anagram_group_cardcheck<?php echo $pgid ?>").prop('checked', true);
                            <?php
                        }
                    } ?>
                    setAnagramGroupPreview();
                }

                if (onchange == null &&  divid=='RecordingPomptCardList') {
                    <?php
                    if (
                        isset($RecordingGroupValue['PromptGroupCardId'])
                        && !empty($RecordingGroupValue['PromptGroupCardId'])
                    ) {
                        foreach ($RecordingGroupValue['PromptGroupCardId'] as $pgid) {
                            ?>
                            $("#recording_group_cardcheck<?php echo $pgid ?>").prop('checked', true);
                            <?php
                        }
                    } ?>
                    RecordingPromptGroupPreview();
                }
            },
            error: function (e) {
            }
        });
    }


    function setTrueFalseCardType() {
        <?php
        if (isset($exercise->exerciseoptions[0]) && $exercise->exerciseoptions[0]['prompt_preview_option'] != '') {
            $promptarr1 = explode(",", $exercise->exerciseoptions[0]['prompt_preview_option']);
            foreach ($promptarr1 as $p) { ?>
                $("input[class=TrueFalsePromptRadio][value=<?php echo $p; ?>]").prop('checked', 'checked');
                <?php
            }
        } ?>
        <?php
        if (isset($exercise->exerciseoptions[0]) && $exercise->exerciseoptions[0]['responce_preview_option'] != '') {
            $responsearr1 = explode(",", $exercise->exerciseoptions[0]['responce_preview_option']);
            foreach ($responsearr1 as $r) { ?>
                $("input[class=TrueFalseResponseRadio][value=<?php echo $r; ?>]").prop('checked', 'checked');
                <?php
            }
        } ?>
    }

    function setEditValue() {
        var promteResponseValue = $('#promteResponseType').val();
        var cardTypeValue = $('#card_type').val();
        var exercise_type = $('#exercise_type').val();
        /*general function to set edit button*/
        showHideFormBlock();
        setRadioButtonValue();
        filterOption();
        if (exercise_type=='multiple-choice' && cardTypeValue != 'custom') {
            getPromoteCardCheckbox();
            getOptionCardCheckbox();

            setTimeout(function () {
                <?php
                if (isset($exercise->promotetype) && $exercise->promotetype != '') {
                    $promotearr = explode(",", $exercise->promotetype);
                    foreach ($promotearr as $p) { ?>
                        $("input[class=" +
                            "<?php echo ($exercise->card_type == 'card') ? 'promoteradio' : 'prompgroupttype'; ?>" +
                            "][value=" +
                            "<?php echo trim($p); ?>" +
                            "]").prop('checked', 'checked');
                        <?php
                    }
                } ?>

                <?php
                if (isset($exercise->responsetype) && $exercise->responsetype != '') {
                    $responsearr = explode(",", $exercise->responsetype);
                    foreach ($responsearr as $r) {
                        ?>
                        $("input[class=" +
                            "<?php echo ($exercise->card_type == 'card') ? 'responseradio' : 'responsegrouptype'; ?>" +
                            "][value=" +
                            "<?php echo $r; ?>]").prop('checked', 'checked');
                        <?php
                    }
                } ?>
            }, 200);
            setTimeout(function () {
                setResponseCardValueAndPreview();
                setPromptCardValueAndPreview();
                setResponseOptionValueAndPreview();
            }, 500);
        }
        else if (cardTypeValue == 'custom' && exercise_type=='multiple-choice') {
            <?php
            if ($exercise->exercise_type == 'multiple-choice' && $exercise->card_type == 'custom') { ?>
                let promptCardId = <?= json_encode($exercise->exerciseoptions[0]['card_id']); ?>;
                let responseCardId = <?= json_encode($exercise->exerciseoptions[0]['responce_card_id']); ?>;
                getMCQCustomHtml('<?php echo $mcqcp;?>', 'McqCustomPromptForm', null, promptCardId);
                getMCQCustomHtml('<?php echo $mcqcr;?>', 'McqCustomForm', null, responseCardId);
                getMCQCustomTypeOptionHtml(<?php echo count($mcqCustomOptionAsset);?>);
                SetCustomTypeOptionselectBox();
                SetMCQCustomvalue();
                setTimeout(function () {
                    customMCQPreview('Response');
                    customMCQPreview('Prompt');
                    customMCQOptionPreview();
                }, 1000);
                <?php
            } ?>
        }
        else if (cardTypeValue == 'card' && exercise_type=='match-the-pair') {
            getMatchThePairCHtml();
        }
        else if (cardTypeValue == 'card_group' && exercise_type=='match-the-pair') {
            getMatchThePairCardEditList();
            setMatchThePairGroupCheckedOption();
            setTimeout(function () {
                MatchThePairGroupPromptPreview();
            }, 500);
        }
        else if (cardTypeValue == 'custom' && exercise_type=='match-the-pair') {
            getMatchThePairCustomHtmlForm();
            setMatchThePairCustomHtmlElement();
        }
        else if (cardTypeValue == 'card' && exercise_type=='truefalse') {
            setTrueFalseCardType();
            setTimeout(function () {
                TrueFalsePromptPreview();
                TrueFalseResponsePreview();
            }, 500);
        }
        else if (cardTypeValue == 'custom' && exercise_type=='truefalse') {
            setTrueFalseCustomValue();
        }
        else if (cardTypeValue == 'card_group' && exercise_type=='truefalse') {
            var pgids=$('#true_false_prompt_group_id').val();
            getCardByGroupIds(pgids, null, 'TrueFalseGruopPromptCardCheckbox', null);
            setPromoteResponseCheck();

            setTimeout(function () {
                TrueFalseGroupPromptPreview();
                TrueFalseGroupResponsePreview();
            }, 1000);
        }
        else if (cardTypeValue == 'card' && exercise_type=='anagram') {
            setTimeout(function () {
                seteditPreviewType();
            }, 500);
        }
        else if (cardTypeValue == 'card_group' && exercise_type=='anagram') {
            setAnagramGroupPromoteResponseCheck();
            getAnagramGroupCheckbox();
        }
        else if (cardTypeValue == 'custom' && exercise_type=='fill_in_the_blanks') {
            getFillInTheBlanksCustomHtml('edit');
        }
        else if (cardTypeValue == 'card' && exercise_type=='fill_in_the_blanks') {
            setFillInTheBlankPromoteResponseCheck();
            setTimeout(function () {
                setFillInTheBlankPreview();
                setFillPreview();
            }, 250);
            renderOptionHtmlAndSet();
        }
        else if (cardTypeValue == 'card' && exercise_type=='recording') {
            setRecordingCardPromoteCheck();
            previewPromptRecordingCard();
            previewResponseRecordingCard();
        }
        else if (cardTypeValue == 'card_group' && exercise_type=='recording') {
            setRecordingCardGroupPromoteCheck();
            getRecordingCardListByGroupOnEdit();
        }
        else if (cardTypeValue == 'custom' && exercise_type=='recording') {
            getRecordingCustomHtml('edit');
        }
    }

    <?php
} ?>
