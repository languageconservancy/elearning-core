<?php
use Cake\Log\Log;
if (isset($matchThePairValue['promteresponsetype'])) {
    $prtype = explode("-", $matchThePairValue['promteresponsetype']);
} else {
    $prtype = ['s','s'];
}
?>
<?php for ($i = 1; $i <= $count; $i++) {?>
<div class="row">
    <div class="col-sm-12"><label>Card #<?= $i?></label></div>
    <div class="col-sm-12"><label>Prompt</label></div>
    <div class="col-sm-12">
        <?php
        $pcardvalue = $matchThePairValue['promptcard' . $i] ?? '';
        echo $this->element(
            'exerciseCardsList',
            [
                'checkboxname' => 'match_the_pair_prompt_card_id' . $i,
                'type' => 'MatchThePairCardSectionPrompt',
                'value' => $pcardvalue,
                'idSuffix' => $i
            ]
        ); ?>
    </div>
    <div class="col-sm-8">
        <div id="MatchThePairCardPrompt<?php echo $i?>"></div>
        <div class="col-sm-12">
            <?php $pcheckvalue = isset($matchThePairValue['promptpreview' . $i])
                ? explode(", ", $matchThePairValue['promptpreview' . $i])
                : [];
            ?>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardPromptType<?php echo $i?>[]"
                    value="l" class="MatchThePairPromptRadio"/>
                <?php echo $languageName;?>
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardPromptType<?php echo $i?>[]"
                    value="e" class="MatchThePairPromptRadio"/>
                English
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardPromptType<?php echo $i?>[]"
                    value="i" class="MatchThePairPromptRadio"/>
                Image
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardPromptType<?php echo $i?>[]"
                    value="a" class="MatchThePairPromptRadio"/>
                Audio
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardPromptType<?php echo $i?>[]"
                    value="v" class="MatchThePairPromptRadio"/>
                Video
            </label>
       </div>
    </div>
    <div class="col-sm-4">
        <label>Preview</label>
        <div id="MatchThePairCardPromptPreview<?= $i; ?>"></div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6"><label>Response</label></div>
    <div class="col-sm-6">
        <?php
        $rcardvalue = $matchThePairValue['responcecard' . $i] ?? '';
        ?>
        <input type="checkbox" name="useprompt<?php echo $i?>"
            id="useprompt<?php echo $i?>" class="useprompt"
            data-id="<?php echo $i?>"
            <?php if ($rcardvalue == $pcardvalue && $rcardvalue != '') {
                echo "checked='checked'";
            } ?>
        >
        <label>Use Prompt</label>
    </div>
    <div class="col-sm-12">
        <?php
        echo $this->element(
            'exerciseCardsList',
            [
                'checkboxname' => 'match_the_pair_response_card_id' . $i,
                'type' => 'MatchThePairCardSectionResponse',
                'value' => $rcardvalue,
                'idSuffix' => $i
            ]
        ); ?>
    </div>
    <div class="col-sm-8">
        <div id="MatchThePairCardResponse<?php echo $i?>"></div>
         <div class="col-sm-12">
            <?php $rcheckvalue = isset($matchThePairValue['responcepreview' . $i])
                ? explode(", ", $matchThePairValue['responcepreview' . $i])
                : []; ?>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardResponseType<?php echo $i?>[]"
                    value="l" class="MatchThePairResponseRadio"
                />
                <?php echo $languageName;?>
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardResponseType<?php echo $i?>[]"
                    value="e" class="MatchThePairResponseRadio"
                />
                English
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardResponseType<?php echo $i?>[]"
                    value="i" class="MatchThePairResponseRadio"
                />
                Image
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardResponseType<?php echo $i?>[]"
                    value="a" class="MatchThePairResponseRadio"
                />
                Audio
            </label>
            <label class="radio-inline">
                <input type="checkbox" name="MatchThePairCardResponseType<?php echo $i?>[]"
                    value="v" class="MatchThePairResponseRadio"
                />
                Video
            </label>
        </div>
    </div>
    <div class="col-sm-4">
        <label>Preview</label>
        <div id="MatchThePairCardResponsePreview<?php echo $i?>"></div>
    </div>
</div>

<?php }?>
