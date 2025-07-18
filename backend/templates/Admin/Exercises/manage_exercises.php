<?php

use Cake\Routing\Router;
use Cake\Log\Log;

?>

<?php
echo $this->Html->css('fastselect.min');
?>

<section class="content-header">
    <h1><?= __('Add/Edit Exercise') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Exercises</a></li>
        <li class="active">Add/Edit Exercise</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary p-1p5">
        <div class="box-body addlessonpage">
            <div class="row display-wrap">
                <div class="col-sm-4">

                    <!--Create exercise form -->
                    <?= $this->Form->create($exercise) ?>
                    <div class="view-space">
                        <div class="form-group">
                            <!-- Exercise name field -->
                            <?= $this->Form->control('name', [
                                'div' => false,
                                'label' => "Name",
                                'class' => 'form-control',
                                'placeholder' => 'Name',
                                'id' => 'new-ex-name'
                            ]) ?>
                            <?= $this->Form->hidden('formtype', ['value' => 'namevalue', 'id' => 'new-ex-name-form']) ?>
                        </div>
                        <div class="form-group">
                            <!--Add Submit button-->
                            <button type="submit" class="btn btn-primary pull-left">Submit</button>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>

                    <div class="view-space">
                        <div class="row">
                            <div class="col-sm-12">
                                <!--Add Exercises label and Add New Exercise
                                    button which calls manageExercises() in the ExercisesController-->
                                <label>Exercises</label>
                                <a class="btn btn-primary pull-right"
                                    href="<?php echo Router::url(
                                        [
                                            'controller' => 'Exercises',
                                            'action' => 'manageExercises',
                                            'prefix' => 'Admin'
                                        ],
                                        true
                                    ); ?>"
                                >Add New Exercise</a>
                            </div>

                            <!--Create scrollable table for list of exercises in the database-->
                            <div class="col-sm-12">
                                <!-- Styling inline to avoid uncontained list of exercises before css loads -->
                                <div class="scroll-table">
                                    <?php echo $this->element('exerciseList', []); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Create exercise edit form-->
                <?php if (isset($exercise->id)) { ?>
                    <div class="col-sm-8" style="overflow-x: auto;">
                        <?= $this->Form->create($exercise, ['id' => 'exerciseForm']) ?>
                        <div class="view-space display-block">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-12" id="frm_message"></div>
                                </div>
                            </div>
                            <!--Exercise name form-->
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-3"><label for="name">Name</label></div>
                                    <div class="col-sm-6">
                                        <?= $this->Form->control('name', [
                                            'div' => false, 'label' => false,
                                            'class' => 'form-control', 'placeholder' => 'Name']) ?>
                                        <?= $this->Form->hidden('formtype', ['value' => 'editvalue']) ?>
                                    </div>
                                </div>
                            </div>
                            <!--Exercise instructions form-->
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-3"><label for="instruction">Instructions</label></div>
                                    <div class="col-sm-6">
                                        <?= $this->Form->control(
                                            'instruction',
                                            [
                                                'div' => false,
                                                'label' => false,
                                                'class' => 'form-control',
                                                'placeholder' => 'Instruction'
                                            ]
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <!--Exercise bonus points form-->
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-3"><label for="bonus">Bonus</label></div>
                                    <div class="col-sm-6">
                                        <?= $this->Form->control(
                                            'bonus',
                                            [
                                                'div' => false,
                                                'label' => false,
                                                'class' => 'form-control',
                                                'placeholder' => 'Bonus'
                                            ]
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <!--Exercise Prompt->Response pair type-->
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label for="promteResponseType">Prompt -> Response</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <select class="form-control" name="promteresponsetype" id="promteResponseType">
                                            <option value="">Select</option>
                                            <?php foreach ($promptResponse as $prompt) { ?>
                                                <?php if ($prompt['val'] != 'r') { ?>
                                                    <?php foreach ($promptResponse as $response) { ?>
                                                        <?php
                                                        if (
                                                            $prompt['val'] != $response['val']
                                                            || in_array(
                                                                $prompt['val'],
                                                                $promptResponseAllowedSelfPairs
                                                            )
                                                        ) { ?>
                                                            <!--Add prompt/response pair option
                                                                to list if they aren't the same-->
                                                            <option
                                                                value="<?= $prompt['val'] ?>-<?= $response['val'] ?>"
                                                                <?php
                                                                // If option is set, preload it by adding
                                                                // selected attribute to option tag
                                                                if (
                                                                    $exercise->promteresponsetype
                                                                        == $prompt['val'] . '-' . $response['val']
                                                                ) {
                                                                    echo 'selected';
                                                                }
                                                                ?>>
                                                                <!--Print label on dropdown menu-->
                                                                <?= $prompt['label'] ?> -> <?= $response['label'] ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!--Exercise form-->
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-3"><label for="exercise_type">Exercise Type</label></div>
                                    <div class="col-sm-6">
                                        <!--Set dropdown menu content-->
                                        <?php echo $this->Form->select(
                                            'exercise_type',
                                            [
                                                'multiple-choice' => 'Multiple Choice',
                                                'match-the-pair' => 'Match the Pair',
                                                'truefalse' => 'True/False',
                                                'anagram' => 'Anagram',
                                                'fill_in_the_blanks' => 'Fill In the Blanks',
                                                'recording' => 'Recording'
                                            ],
                                            [
                                                'class' => "form-control",
                                                'id' => 'exercise_type',
                                                'empty' => 'Select'
                                            ]
                                        ); ?>

                                    </div>
                                </div>
                            </div>
                            <!--Exercise subtype form-->
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-3"><label for="card_type">Card Type</label></div>
                                    <div class="col-sm-6">
                                        <!--Set subtype dropdown menu options-->
                                        <?php echo $this->Form->select(
                                            'card_type',
                                            [
                                                'card' => 'Card',
                                                'card_group' => 'Card Group',
                                                'custom' => 'Custom'
                                            ],
                                            [
                                                'class' => "form-control",
                                                'id' => 'card_type',
                                                'empty' => 'Select'
                                            ]
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Start conditional forms-->

                        <!--Add submit button if any options aren't selected, then display Submit & Continue button-->
                        <div class="box-footer" id="exerciseStepOneSubmit"
                            <?php
                            if (
                                !isset($exercise->promteresponsetype)
                                || !isset($exercise->card_type)
                                || !isset($exercise->exercise_type)
                            ) { ?>
                                style="display: block;" <?php
                            } else {
                                echo 'style="display: none;"';
                            } ?>
                        >
                            <button onclick="validateForm('continue')"
                                type="button" class="btn btn-primary">Submit & Continue</button>
                        </div>

                        <!--For multiple-choice and card subtype-->
                        <div class="view-space display-block" id="exerciseFormBlock">
                            <!--If multiple-choice and card subtype, create html
                                but don't display it. Displaying is in javascript-->
                            <?php
                            if (
                                $exercise->exercise_type == 'multiple-choice'
                                && $exercise->card_type == 'card'
                            ) { ?>
                                <div id="MultipleChoiceCardSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <!--Prompt Card label-->
                                            <div class="col-sm-12"><label>Prompt Card</label></div>
                                            <!--Cards list element-->
                                            <div class="col-sm-12">
                                                <!--candidate for adding  ['cache' => true] to this element-->
                                                <?php echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'prompt_card_id',
                                                        'type' => 'MultipleChoiceCardSectionPromt',
                                                        'value' => $promoteSingleCardId
                                                    ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-12">
                                                <span id="prompt-card"></span>
                                            </div>
                                            <!--Card data inclusion options-->
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="prompt1" type="checkbox" name="prompttype[]"
                                                        value="l" class="promoteradio"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt2" type="checkbox" name="prompttype[]"
                                                        value="e" class="promoteradio"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt3" type="checkbox" name="prompttype[]"
                                                        value="i" class="promoteradio"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt4" type="checkbox" name="prompttype[]"
                                                        value="a" class="promoteradio"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt5" type="checkbox" name="prompttype[]"
                                                        value="v" class="promoteradio"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <!--Preview of Card data that's checked-->
                                            <div class="col-sm-4">
                                                <label>Preview</label>
                                                <div id='prompt_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Response Card</label></div>
                                            <div class="col-sm-12">
                                                <?php echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'response_card_id',
                                                        'type' => 'MultipleChoiceCardSectionResponse',
                                                        'value' => $responseSingleCardId,
                                                    ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-12">
                                                <span id="response-card"></span>
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="response1" type="checkbox" name="responsetype[]"
                                                        value="l" class="responseradio"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response2" type="checkbox" name="responsetype[]"
                                                        value="e" class="responseradio"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response3" type="checkbox" name="responsetype[]"
                                                        value="i" class="responseradio"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response4" type="checkbox" name="responsetype[]"
                                                        value="a" class="responseradio"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response5" type="checkbox" name="responsetype[]"
                                                        value="v" class="responseradio"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <div class="col-sm-4">
                                                <label>Preview</label>
                                                <div id='response_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Choices</label></div>
                                            <div class="col-sm-7">
                                                <div class="col-sm-6 form-group">
                                                    <label for="card_id" class="show">Select cards</label>
                                                    <?php echo $this->element(
                                                        'select2CardList',
                                                        [
                                                            'id' => 'card_id',
                                                            'name' => 'card_id[]',
                                                            'preselectedCardIds' => $selectedOptioncardIds,
                                                        ]
                                                    ); ?>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label for="card_group_id">Select Groups</label>
                                                    <?php echo $this->Form->select(
                                                        'card_group_id',
                                                        $cardsgroup,
                                                        [
                                                            'class' => "form-control",
                                                            'id' => 'card_group_id',
                                                            'empty' => 'Card Group',
                                                            'value' => $OptionGroupId
                                                        ]
                                                    ); ?>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div id='single_card_preview'></div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label>Preview</label>
                                                <div id="option_preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'multiple-choice'
                                && $exercise->card_type == 'card_group'
                            ) { ?>
                                <div id="MultipleChoiseCardGroupSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3"><label for="noofcard">Number of Cards</label></div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'noofcard',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Number of Cards'
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Prompt</label></div>
                                            <div class="col-sm-4">
                                                <label for="promptGroupCardId">Select cards</label>
                                                <?php echo $this->element(
                                                        'select2CardList',
                                                        [
                                                            'id' => 'promptGroupCardId',
                                                            'name' => 'prompt_group_card_id[]',
                                                            'preselectedCardIds' => $promoteCardIds,
                                                        ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="promptGroupGroupId">Select Groups</label>
                                                <?php echo $this->Form->select(
                                                    'prompt_group_group_id[]',
                                                    $cardsgroup,
                                                    [
                                                        'class' => "form-control fastselect",
                                                        'id' => 'promptGroupGroupId',
                                                        'multiple',
                                                        'value' => $promoteGroupIds
                                                    ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <div id="PomptCardList"></div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div id='prompt_group_card_preview'></div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="prompt1" type="checkbox" name="promptgrouptype[]"
                                                        value="l" class="prompgroupttype"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt2" type="checkbox" name="promptgrouptype[]"
                                                        value="e" class="prompgroupttype"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt3" type="checkbox" name="promptgrouptype[]"
                                                        value="i" class="prompgroupttype"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt4" type="checkbox" name="promptgrouptype[]"
                                                        value="a" class="prompgroupttype"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt5" type="checkbox" name="promptgrouptype[]"
                                                        value="v" class="prompgroupttype"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <div class="col-sm-3">
                                                <label>Preview</label>
                                                <div id='prompt_group_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Response</label></div>
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="response1" type="checkbox" name="responsegrouptype[]"
                                                        value="l" class="responsegrouptype"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response2" type="checkbox" name="responsegrouptype[]"
                                                        value="e" class="responsegrouptype"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response3" type="checkbox" name="responsegrouptype[]"
                                                        value="i" class="responsegrouptype"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response4" type="checkbox" name="responsegrouptype[]"
                                                        value="a" class="responsegrouptype"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response5" type="checkbox" name="responsegrouptype[]"
                                                        value="v" class="responsegrouptype"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <label>Preview</label>
                                                <div id='response_group_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Choices</label></div>
                                            <div class="col-sm-7">
                                                <div class="col-sm-4">
                                                    <label for="group_card_option_id">Select Cards</label>
                                                    <?php echo $this->element(
                                                        'select2CardList',
                                                        [
                                                            'id' => 'group_card_option_id',
                                                            'name' => 'group_card_option_id[]',
                                                            'preselectedCardIds' => $optionCardIds,
                                                        ]
                                                    ); ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label for="OptionMultiCardGroupPicker">Select Groups</label>
                                                    <?php echo $this->Form->select(
                                                        'group_option_id[]',
                                                        $cardsgroup,
                                                        [
                                                            'class' => "form-control fastselect",
                                                            'id' => 'OptionMultiCardGroupPicker',
                                                            'multiple',
                                                            'value' => $optionGroupids
                                                        ]
                                                    ); ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div id="OptionCardList"></div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div id='choices_card_preview'></div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label>Preview</label>
                                                <div id="option_response_preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'multiple-choice'
                                && $exercise->card_type == 'custom'
                            ) { ?>
                                <div id="MultipleChoiseCustomSection" style="display:none;">
                                    <div class="row">
                                        <div class="col-sm-2"><label>Prompt</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                            $mcqcp = (isset($exercise->exerciseoptions[0])
                                                && $exercise->card_type == 'custom'
                                                && $exercise->exercise_type == 'multiple-choice')
                                                ? ($exercise->exerciseoptions[0]['card_id'] != '')
                                                ? 'card' : 'html' : '';
                                            echo $this->Form->select(
                                                'mcq_custom_prompt_type',
                                                [
                                                    'card' => 'Card',
                                                    'html' => 'HTML'
                                                ],
                                                [
                                                    'class' => "form-control",
                                                    'id' => 'mcq_custom_prompt_type',
                                                    'empty' => 'Select Type',
                                                    'value' => $mcqcp
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-12" id="McqCustomPromptForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="McqCustomPromptPreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2"><label>Response</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                            $mcqcr = (isset($exercise->exerciseoptions[0])
                                                && $exercise->card_type == 'custom'
                                                && $exercise->exercise_type == 'multiple-choice')
                                                ? ($exercise->exerciseoptions[0]['responce_card_id'] != '')
                                                ? 'card' : 'html' : '';
                                            echo $this->Form->select(
                                                'mcq_custom_response_type',
                                                [
                                                    'card' => 'Card',
                                                    'html' => 'HTML'
                                                ],
                                                [
                                                    'class' => "form-control",
                                                    'id' => 'mcq_custom_response_type',
                                                    'empty' => 'Select Type',
                                                    'value' => $mcqcr
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-12" id="McqCustomForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="McqCustomPreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <label for='mcq_custom_option_no'>Number of Options</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <?php echo $this->Form->control(
                                                'mcq_custom_option_no',
                                                [
                                                    'class' => "form-control",
                                                    'value' => '',
                                                    'id' => 'mcq_custom_option_no',
                                                    'label' => false,
                                                    'type' => 'number',
                                                    'min' => 0,
                                                    'max' => 3,
                                                    'placeholder' => 'Number of Options',
                                                    'value' => count($mcqCustomOptionAsset)
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-12" id="McqCustomOptionForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="McqCustomOptionPreview"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'match-the-pair'
                                && $exercise->card_type == 'card'
                            ) { ?>
                                <div id="MatchThePairCardSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="MatchThePairCardNo">Number of Cards</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'MatchPairCardNoOfCard',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'id' => 'MatchThePairCardNo',
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Number of Cards',
                                                        'value' => $exercise['noofcard']
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" id='MatchThePairCardSectionHtml'></div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'match-the-pair'
                                && $exercise->card_type == 'card_group'
                            ) { ?>
                                <div id="MatchThePairGroupSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="MatchThePairCardGroupNoOfCard">Number of Cards</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'MatchPairGroupNoOfCard',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Number of Cards',
                                                        'value' => $exercise['noofcard']
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Prompt</label></div>
                                            <div class="col-sm-4">
                                                <label for="MatchThePairPromptGroupCardId">Select cards</label>
                                                <?php echo $this->element(
                                                    'select2CardList',
                                                    [
                                                        'id' => 'MatchThePairPromptGroupCardId',
                                                        'name' => 'match_the_pair_prompt_group_card_id[]',
                                                        'preselectedCardIds' => $matchThePairGroupvalue['cardId'] ?? [],
                                                    ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="MatchThePairPromptGroupGroupId">Select Groups</label>
                                                <?php
                                                    $mtpgv = $matchThePairGroupvalue['groupId'] ?? array();
                                                    echo $this->Form->select(
                                                        'match_the_pair_prompt_group_group_id[]',
                                                        $cardsgroup,
                                                        [
                                                            'class' => "form-control fastselect",
                                                            'id' => 'MatchThePairPromptGroupGroupId',
                                                            'multiple',
                                                            'value' => $mtpgv
                                                        ]
                                                    );
                                                ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <div id="MatchThePairPomptCardList"></div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label>Preview</label>
                                                <div id='match_the_pair_prompt_group_preview'></div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="prompt1" type="checkbox"
                                                        name="matchthepairpromptgrouptype[]"
                                                        value="l" class="matchthepairpromptgrouptype"
                                                    /><?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt2" type="checkbox"
                                                        name="matchthepairpromptgrouptype[]"
                                                        value="e" class="matchthepairpromptgrouptype"
                                                    />English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt3" type="checkbox"
                                                        name="matchthepairpromptgrouptype[]"
                                                        value="i" class="matchthepairpromptgrouptype"
                                                    />Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt4" type="checkbox"
                                                        name="matchthepairpromptgrouptype[]"
                                                        value="a" class="matchthepairpromptgrouptype"
                                                    />Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt5" type="checkbox"
                                                        name="matchthepairpromptgrouptype[]"
                                                        value="v" class="matchthepairpromptgrouptype"
                                                    />Video
                                                </label>
                                            </div>

                                            <div class="col-sm-12">
                                                <div id='match_the_pair_prompt_group_card_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Response</label></div>
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="response1" type="checkbox"
                                                        name="matchthepairresponsegrouptype[]"
                                                        value="l" class="matchthepairresponsegrouptype"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response2" type="checkbox"
                                                        name="matchthepairresponsegrouptype[]"
                                                        value="e" class="matchthepairresponsegrouptype"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response3" type="checkbox"
                                                        name="matchthepairresponsegrouptype[]"
                                                        value="i" class="matchthepairresponsegrouptype"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response4" type="checkbox"
                                                        name="matchthepairresponsegrouptype[]"
                                                        value="a" class="matchthepairresponsegrouptype"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response5" type="checkbox"
                                                        name="matchthepairresponsegrouptype[]"
                                                        value="v" class="matchthepairresponsegrouptype"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <label>Preview</label>
                                                <div id='match_the_pair_response_group_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'match-the-pair'
                                && $exercise->card_type == 'custom'
                            ) { ?>
                                <div id="MatchThePairCustomSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="MatchThePairCustomNo">Number of Cards</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'MatchPairCustomNoOfCard',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'id' => 'MatchThePairCustomNo',
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'max' => 6, 'class' => 'form-control',
                                                        'placeholder' => 'Number of Cards',
                                                        'value' => $exercise['noofcard']
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="MatchThePairCustomForm" class="row"></div>
                                </div>
                            <?php } ?>
                            <?php if ($exercise->exercise_type == 'truefalse' && $exercise->card_type == 'card') { ?>
                                <div id="TrueFalseCardSection" style="display:none;">
                                    <div class="row">
                                        <div class="col-sm-12"><label>Prompt #1</label></div>
                                        <div class="col-sm-12">

                                            <?php
                                            if (isset($exercise['promteresponsetype'])) {
                                                $prtype = explode("-", $exercise['promteresponsetype']);
                                            } else {
                                                $prtype = array('s', 's');
                                            }
                                            ?>

                                            <?php
                                                $true_false_prompt_card_id = isset($exercise->exerciseoptions[0])
                                                    ? $exercise->exerciseoptions[0]['card_id']
                                                    : null;
                                                echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'true_false_prompt_card_id',
                                                        'type' => 'TrueFalseCardSectionPrompt',
                                                        'value' => $true_false_prompt_card_id,
                                                    ]
                                                );
                                            ?>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="prompt1" type="checkbox"
                                                        name="TrueFalseCardPromptType[]"
                                                        value="l" class="TrueFalsePromptRadio"
                                                        />
                                                        <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt2" type="checkbox"
                                                        name="TrueFalseCardPromptType[]"
                                                        value="e" class="TrueFalsePromptRadio"
                                                        />
                                                        English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt3" type="checkbox"
                                                        name="TrueFalseCardPromptType[]"
                                                        value="i" class="TrueFalsePromptRadio"
                                                        />
                                                        Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt4" type="checkbox"
                                                        name="TrueFalseCardPromptType[]"
                                                        value="a" class="TrueFalsePromptRadio"
                                                        />
                                                        Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt5" type="checkbox"
                                                        name="TrueFalseCardPromptType[]"
                                                        value="v" class="TrueFalsePromptRadio"
                                                        />
                                                        Video
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id="TrueFalseCardPromptPreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <?php $true_false_response_card_id = isset($exercise->exerciseoptions[0])
                                            ? $exercise->exerciseoptions[0]['responce_card_id']
                                            : null; ?>
                                        <div class="col-sm-6"><label>Prompt #2</label></div>
                                        <div class="col-sm-6">
                                            <input type="checkbox" name="truefalsesamecard"
                                                id="truefalsesamecard"
                                                <?php
                                                if (
                                                    $true_false_response_card_id == $true_false_prompt_card_id
                                                    && $true_false_prompt_card_id != ''
                                                ) {
                                                    echo 'checked="checked"';
                                                } ?>
                                            />
                                            <label>Same Card</label>
                                        </div>
                                        <div class="col-sm-12">
                                            <?php echo $this->element(
                                                'exerciseCardsList',
                                                [
                                                    'checkboxname' => 'true_false_response_card_id',
                                                    'type' => 'TrueFalseCardSectionResponse',
                                                    'value' => $true_false_response_card_id,
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="response1" type="checkbox"
                                                        name="TrueFalseCardResponseType[]"
                                                        value="l" class="TrueFalseResponseRadio"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response2" type="checkbox"
                                                        name="TrueFalseCardResponseType[]"
                                                        value="e" class="TrueFalseResponseRadio"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response3" type="checkbox"
                                                        name="TrueFalseCardResponseType[]"
                                                        value="i" class="TrueFalseResponseRadio"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response4" type="checkbox"
                                                        name="TrueFalseCardResponseType[]"
                                                        value="a" class="TrueFalseResponseRadio"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="response5" type="checkbox"
                                                        name="TrueFalseCardResponseType[]"
                                                        value="v" class="TrueFalseResponseRadio"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id="TrueFalseCardResponsePreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Response</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                                $response_card_type = isset($exercise->exerciseoptions[0])
                                                    ? $exercise->exerciseoptions[0]['response_true_false']
                                                    : null;
                                                echo $this->Form->select(
                                                    'response_card_type',
                                                    [
                                                        'Y' => 'True',
                                                        'N' => 'False'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'response_card_type_true_false',
                                                        'empty' => 'Select',
                                                        'value' => $response_card_type
                                                    ]
                                                );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'truefalse'
                                && $exercise->card_type == 'card_group'
                            ) { ?>
                                <div id="TrueFalseGruopSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="TrueFalseNoOfCard">Number of Cards</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'TrueFalseNoOfCard',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Number of Cards',
                                                        'id' => 'TrueFalseNoOfCard',
                                                        'value' => $exercise['noofcard']
                                                    ]
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Prompt #1</label></div>
                                        <div class="col-sm-5">
                                            <label for="true_false_prompt_group_card_id">Select Cards</label>
                                            <?php echo $this->element(
                                                'select2CardList',
                                                [
                                                    'id' => 'true_false_prompt_group_card_id',
                                                    'name' => 'true_false_prompt_group_card_id[]',
                                                    'preselectedCardIds' => $TrueFalseValue['PromptCardId'] ?? array(),
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-5">
                                            <label for="true_false_prompt_group_id">Select Groups</label>
                                            <?php echo $this->Form->select(
                                                'true_false_prompt_group_id[]',
                                                $cardsgroup,
                                                [
                                                    'class' => "form-control fastselect",
                                                    'id' => 'true_false_prompt_group_id',
                                                    'multiple',
                                                    'value' => $TrueFalseValue['PromptGroupId'] ?? array()
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-2" id="TrueFalseGruopPromptCardCheckbox"> </div>
                                        <div class="col-sm-12">
                                            <label class="radio-inline">
                                                <input id="prompt1" type="checkbox"
                                                    name="TrueFalseGruopPromptType[]"
                                                    value="l" class="TrueFalseGruopPromptRadio"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt2" type="checkbox"
                                                    name="TrueFalseGruopPromptType[]"
                                                    value="e" class="TrueFalseGruopPromptRadio"
                                                />
                                                English
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt3" type="checkbox"
                                                    name="TrueFalseGruopPromptType[]"
                                                    value="i" class="TrueFalseGruopPromptRadio"
                                                />
                                                Image
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt4" type="checkbox"
                                                    name="TrueFalseGruopPromptType[]"
                                                    value="a" class="TrueFalseGruopPromptRadio"
                                                />
                                                Audio
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt5" type="checkbox"
                                                    name="TrueFalseGruopPromptType[]"
                                                    value="v" class="TrueFalseGruopPromptRadio"
                                                />
                                                Video
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="TrueFalseGruopPromptPreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Prompt #2</label></div>
                                        <div class="col-sm-12">
                                            <label class="radio-inline">
                                                <input id="response1" type="checkbox"
                                                    name="TrueFalseGruopResponseType[]"
                                                    value="l" class="TrueFalseGruopResponseRadio"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                            <label class="radio-inline">
                                                <input id="response2" type="checkbox"
                                                    name="TrueFalseGruopResponseType[]"
                                                    value="e" class="TrueFalseGruopResponseRadio"
                                                />
                                                English
                                            </label>
                                            <label class="radio-inline">
                                                <input id="response3" type="checkbox"
                                                    name="TrueFalseGruopResponseType[]"
                                                    value="i" class="TrueFalseGruopResponseRadio"
                                                />
                                                Image
                                            </label>
                                            <label class="radio-inline">
                                                <input id="response4" type="checkbox"
                                                    name="TrueFalseGruopResponseType[]"
                                                    value="a" class="TrueFalseGruopResponseRadio"
                                                />
                                                Audio
                                            </label>
                                            <label class="radio-inline">
                                                <input id="response5" type="checkbox"
                                                    name="TrueFalseGruopResponseType[]"
                                                    value="v" class="TrueFalseGruopResponseRadio"
                                                />
                                                Video
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="TrueFalseGruopResponsePreview"></div>
                                        </div>
                                    </div>
                                    <div class="row" id="TrueFalseGruopDropdown" style="display: none;">;

                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($exercise->exercise_type == 'truefalse' && $exercise->card_type == 'custom') { ?>
                                <div id="TrueFalseCustomSection" style="display:none;">
                                    <div class="row">
                                        <div class="col-sm-2"><label>Prompt #1</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                                $pv1 = (isset($exercise->exerciseoptions[0])
                                                    && $exercise->card_type == 'custom'
                                                    && $exercise->exercise_type == 'truefalse')
                                                    ? ($exercise->exerciseoptions[0]['card_id'] != '')
                                                    ? 'card' : 'html' : '';
                                                echo $this->Form->select(
                                                    'true_false_prompt_type',
                                                    [
                                                        'card' => 'Card',
                                                        'html' => 'HTML'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'true_false_prompt_type',
                                                        'empty' => 'Select Type',
                                                        'value' => $pv1
                                                    ]
                                                );
                                            ?>
                                        </div>
                                        <div class="col-sm-12" id="trueFalsePromptForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="TrueFalseCustomPromptPreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2"><label>Prompt #2</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                                $rv1 = (isset($exercise->exerciseoptions[0])
                                                    && $exercise->card_type == 'custom'
                                                    && $exercise->exercise_type == 'truefalse' )
                                                    ? ($exercise->exerciseoptions[0]['responce_card_id'] != '')
                                                    ? 'card' : 'html' : '';
                                                echo $this->Form->select(
                                                    'true_false_response_type',
                                                    [
                                                        'card' => 'Card',
                                                        'html' => 'HTML'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'true_false_response_type',
                                                        'empty' => 'Select Type',
                                                        'value' => $rv1
                                                    ]
                                                );
                                            ?>
                                        </div>
                                        <div class="col-sm-12" id="trueFalseResponseForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="TrueFalseCustomResponsePreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Response</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                                $response_custom_type = isset($exercise->exerciseoptions[0])
                                                    ? $exercise->exerciseoptions[0]['response_true_false']
                                                    : null;
                                                echo $this->Form->select(
                                                    'response_custom_type',
                                                    [
                                                        'Y' => 'True',
                                                        'N' => 'False'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'response_custom_type_true_false',
                                                        'empty' => 'Select',
                                                        'value' => $response_custom_type
                                                    ]
                                                );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($exercise->exercise_type == 'anagram' && $exercise->card_type == 'card') { ?>
                                <div id="AnagramCardSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label>Select Card</label>
                                            </div>
                                            <div class="col-sm-12">
                                                <?php $card_id = (isset($exercise->exerciseoptions[0]['card_id']))
                                                    ? $exercise->exerciseoptions[0]['card_id']
                                                    : ''; ?>
                                                <?php echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'anagram_card',
                                                        'type' => 'AnagramCard',
                                                        'value' => $card_id,
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Prompt</label></div>
                                        <div class="col-sm-8">
                                            <label class="radio-inline">
                                                <input id="prompt1" type="checkbox"
                                                    name="anagramCardPromptType[]"
                                                    value="l" class="anagramCardPromptType"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt2" type="checkbox"
                                                    name="anagramCardPromptType[]"
                                                    value="e" class="anagramCardPromptType"
                                                />
                                                English
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt3" type="checkbox"
                                                    name="anagramCardPromptType[]"
                                                    value="i" class="anagramCardPromptType"
                                                />
                                                Image
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt4" type="checkbox"
                                                    name="anagramCardPromptType[]"
                                                    value="a" class="anagramCardPromptType"
                                                />
                                                Audio
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt5" type="checkbox"
                                                    name="anagramCardPromptType[]"
                                                    value="v" class="anagramCardPromptType"
                                                />
                                                Video
                                            </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id='anagram_card_prompt_preview'></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Response</label></div>
                                        <div class="col-sm-8">
                                            <label class="radio-inline">
                                                <input id="response1" type="checkbox"
                                                    name="anagramCardResponseType[]"
                                                    value="l" class="anagramCardResponseType"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id='anagram_card_response_preview'></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'anagram'
                                && $exercise->card_type == 'card_group'
                            ) { ?>
                                <div id="AnagramGroupSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="AnagramGroupCards">Number of Cards</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'AnagramGroupCards',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'cards',
                                                        'value' => $exercise['noofcard']
                                                    ]
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Select Cards</label></div>
                                            <div class="col-sm-4">
                                                <label for="AnagramGroupCardId">Select cards</label>
                                                <?php
                                                    $anagramGroupCardIds = (isset($AnagramGroupValue['PromptCardId']))
                                                        ? $AnagramGroupValue['PromptCardId']
                                                        : array();
                                                    echo $this->element(
                                                    'select2CardList',
                                                    [
                                                        'id' => 'AnagramGroupCardId',
                                                        'name' => 'anagram_group_card_id[]',
                                                        'preselectedCardIds' => $anagramGroupCardIds,
                                                    ]
                                                ); ?>
                                            </div>
                                        <div class="col-sm-4">
                                            <label for="anagram_group_group_id">Select Groups</label>
                                            <?php $GroupId = (isset($AnagramGroupValue['PromptGroupId']))
                                                ? $AnagramGroupValue['PromptGroupId']
                                                : array(); ?>
                                            <?php echo $this->Form->select(
                                                'anagram_group_group_id[]',
                                                $cardsgroup,
                                                [
                                                    'class' => "form-control fastselect",
                                                    'id' => 'anagram_group_group_id',
                                                    'multiple',
                                                    'empty' => 'Card Group',
                                                    'value' => $GroupId
                                                ]
                                            ); ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <div id='anagram_group_single_card_preview'></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12"><label>Prompt</label></div>
                                        <div class="col-sm-8">

                                            <label class="radio-inline">
                                                <input id="prompt1" type="checkbox"
                                                    name="anagramGroupPromptType[]"
                                                    value="l" class="anagramGroupPromptType"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt2" type="checkbox"
                                                    name="anagramGroupPromptType[]"
                                                    value="e" class="anagramGroupPromptType"
                                                />
                                                English
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt3" type="checkbox"
                                                    name="anagramGroupPromptType[]"
                                                    value="i" class="anagramGroupPromptType"
                                                />
                                                Image
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt4" type="checkbox"
                                                    name="anagramGroupPromptType[]"
                                                    value="a" class="anagramGroupPromptType"
                                                />
                                                Audio
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt5" type="checkbox"
                                                    name="anagramGroupPromptType[]"
                                                    value="v" class="anagramGroupPromptType"
                                                />
                                                Video
                                            </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id='anagram_prompt_group_preview'></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label for="ques-name">Response</label></div>
                                        <div class="col-sm-8">
                                            <label class="radio-inline">
                                                <input id="response1" type="checkbox"
                                                    name="anagramGroupResponseType[]"
                                                    value="l" class="anagramGroupResponseType"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id='anagram_response_group_preview'></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'fill_in_the_blanks'
                                && $exercise->card_type == 'card'
                            ) { ?>
                                <div id="FillInTheBlankCardTypingSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="fill_in_the_blank_type">Typing/MC</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?php $fill_in_the_blank_type =
                                                    (isset($exercise->exerciseoptions[0]['fill_in_the_blank_type']))
                                                    ? $exercise->exerciseoptions[0]['fill_in_the_blank_type']
                                                    : ''; ?>
                                                <?php echo $this->Form->select(
                                                    'fill_in_the_blank_type',
                                                    [
                                                        'typing' => 'Typing',
                                                        'mcq' => 'Multiple Choice'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'fill_in_the_blank_type',
                                                        'value' => $fill_in_the_blank_type
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label for="ques-name">Select Card</label></div>
                                            <div class="col-sm-12">
                                                <?php $card_id =
                                                (isset($exercise->exerciseoptions[0]['card_id']))
                                                    ? $exercise->exerciseoptions[0]['card_id']
                                                    : ''; ?>
                                                <?php echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'fill_in_the_blank_card_typing',
                                                        'type' => 'FillInTheBlankCardTyping',
                                                        'value' => $card_id,
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"><label>Prompt</label></div>
                                        <div class="col-sm-8">
                                            <label class="radio-inline">
                                                <input id="prompt1" type="checkbox"
                                                    name="FillInTheBlankCardPromptType[]"
                                                    value="l" class="FillInTheBlankCardPromptType"
                                                />
                                                <?php echo $languageName;?>
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt2" type="checkbox"
                                                    name="FillInTheBlankCardPromptType[]"
                                                    value="e" class="FillInTheBlankCardPromptType"
                                                />
                                                English
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt3" type="checkbox"
                                                    name="FillInTheBlankCardPromptType[]"
                                                    value="i" class="FillInTheBlankCardPromptType"
                                                />
                                                Image
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt4" type="checkbox"
                                                    name="FillInTheBlankCardPromptType[]"
                                                    value="a" class="FillInTheBlankCardPromptType"
                                                />
                                                Audio
                                            </label>
                                            <label class="radio-inline">
                                                <input id="prompt5" type="checkbox"
                                                    name="FillInTheBlankCardPromptType[]"
                                                    value="v" class="FillInTheBlankCardPromptType"
                                                />
                                                Video
                                            </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Preview</label>
                                            <div id='fill_in_the_blank_prompt_card_preview'></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12"><label>Response</label></div>
                                            <div class="col-sm-8">
                                                <label class="radio-inline">
                                                    <input type="checkbox"
                                                        name="FillInTheBlankCardResponseType[]"
                                                        value="l" class="FillInTheBlankCardResponseType"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                            </div>
                                            <div class="col-sm-4">
                                                <label>Preview</label>
                                                <div id='fill_in_the_blank_response_card_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" id="fill_blank_lakota_box">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="fill_blank_lakota">
                                                    Marks Blank <?php echo $languageName;?>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control('fill_blank_lakota', [
                                                    'div' => false, 'label' => false,
                                                    'class' => 'form-control',
                                                    'id' => 'fill_blank_lakota',
                                                    'value' => (isset($FillInTheBlankValue['text_option'])) ?
                                                        $FillInTheBlankValue['text_option'] : null]) ?>

                                                <?= $this->Form->hidden('fill_blank_lakota_ans', [
                                                    'id' => "fill_blank_ans", 'value' => '']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" id="fill_blank_choise_div" style="display:none;">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="option_counter">
                                                    Choices
                                                </label>
                                            </div>
                                            <div class="col-sm-3">
                                                <?= $this->Form->control('option_counter', [
                                                    'div' => false, 'label' => false,
                                                    'class' => 'form-control', 'id' => "option_counter",
                                                    'value' => (count($FillInTheBlankOptions) == 0) ?
                                                        1 : count($FillInTheBlankOptions)]) ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-3" id="choise_div"></div>
                                            <div class="col-sm-8">
                                                <div id="keyboard"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'fill_in_the_blanks'
                                && $exercise->card_type == 'custom'
                            ) { ?>
                                <div id="FillInTheBlankCustomSection" style="display:none;">
                                    <div class="row">
                                        <div class="col-sm-2"><label>Prompt #1</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                                $fillCustomP = (isset($exercise->exerciseoptions[0])
                                                    && $exercise->card_type == 'custom'
                                                    && $exercise->exercise_type == 'fill_in_the_blanks')
                                                    ? ($exercise->exerciseoptions[0]['card_id'] != '')
                                                    ? 'card' : 'html' : '';
                                                echo $this->Form->select(
                                                    'fill_in_the_blanks_prompt_type',
                                                    [
                                                        'card' => 'Card',
                                                        'html' => 'HTML'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'fill_in_the_blanks_prompt_type',
                                                        'empty' => 'Select Type',
                                                        'value' => $fillCustomP
                                                    ]
                                                );
                                            ?>
                                        </div>
                                        <div class="col-sm-12" id="FillInTheBlankPromptForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="FillInTheBlankCustomPromptPreview"></div>
                                        </div>
                                        <div class="col-sm-12" id="FillInTheBlankResponseForm">
                                            <div class="col-sm-3">
                                                <label for="fill_blank_custom_lakota">
                                                    Marks Blank <?php echo $languageName;?>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'fill_blank_custom_lakota',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'class' => 'form-control',
                                                        'value' => null,
                                                        'id' => 'fill_blank_custom_lakota'
                                                    ]
                                                ) ?>

                                                <?= $this->Form->hidden(
                                                    'fill_blank_custom_lakota_ans',
                                                    [
                                                        'id' => "fill_blank_custom_ans",
                                                        'value' => ''
                                                    ]
                                                ) ?>
                                            </div>

                                        </div>

                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="FillInTheBlankCustomResponsePreview"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($exercise->exercise_type == 'recording' && $exercise->card_type == 'card') { ?>
                                <div id="RecordingCardSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="ques-name">Select Prompt Card</label>
                                            </div>
                                            <div class="col-sm-12">
                                                <?php $recording_prompt_card_id =
                                                    (isset($exercise->exerciseoptions[0]['card_id']))
                                                    ? $exercise->exerciseoptions[0]['card_id']
                                                    : ''; ?>
                                                <?php echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'recording_prompt_card',
                                                        'type' => 'recordingPromptCard',
                                                        'value' => $recording_prompt_card_id,
                                                    ]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="prompt1" type="checkbox"
                                                        name="recording_prompt[]"
                                                        value="l" class="recordingprompttype"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt2" type="checkbox"
                                                        name="recording_prompt[]"
                                                        value="e" class="recordingprompttype"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt3" type="checkbox"
                                                        name="recording_prompt[]"
                                                        value="i" class="recordingprompttype"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt4" type="checkbox"
                                                        name="recording_prompt[]"
                                                        value="a" class="recordingprompttype"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt5" type="checkbox"
                                                        name="recording_prompt[]"
                                                        value="v" class="recordingprompttype"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <div id="recordingPromptPreview"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="ques-name">Select Response Card</label>
                                            </div>
                                            <div class="col-sm-12">
                                                <?php $recording_response_card_id =
                                                    (isset($exercise->exerciseoptions[0]['responce_card_id']))
                                                    ? $exercise->exerciseoptions[0]['responce_card_id']
                                                    : ''; ?>
                                                <?php echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'recording_response_card',
                                                        'type' => 'recordingResponseCard',
                                                        'value' => $recording_response_card_id,
                                                    ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-12">
                                                <div id="recordingResponsePreview"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="response_true_false_card">Display</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?php
                                                    $chkElement = array('value' => 'Y', 'hiddenField' => 'N');
                                                if (
                                                    isset($exercise->exerciseoptions[0]['response_true_false'])
                                                    && $exercise->exerciseoptions[0]['response_true_false'] == 'Y'
                                                ) {
                                                    $chkElement['checked'] = true;
                                                }
                                                ?>
                                                <?= $this->Form->checkbox('response_true_false_card', $chkElement) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            if (
                                $exercise->exercise_type == 'recording'
                                && $exercise->card_type == 'card_group'
                            ) { ?>
                                <div id="RecordingCardGroupSection" style="display:none;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="RecordinNoOfCard">Number of Cards</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $this->Form->control(
                                                    'RecordinNoOfCard',
                                                    [
                                                        'div' => false,
                                                        'label' => false,
                                                        'type' => 'number',
                                                        'min' => 0,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Number of Cards',
                                                        'value' => $exercise['noofcard']
                                                    ]
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <label for="recordingPromptGroupCardId">Select cards</label>
                                                <?php
                                                    echo $this->element(
                                                    'select2CardList',
                                                    [
                                                        'id' => 'recordingPromptGroupCardId',
                                                        'name' => 'recording_prompt_group_card_id[]',
                                                        'preselectedCardIds' => $RecordingGroupValue['PromptCardId'] ?? '',
                                                    ]
                                                ); ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="recordingPromptGroupGroupId">Select Groups</label>
                                                    <?php
                                                        $RecordingGroupValue['PromptGroupId'] =
                                                            $RecordingGroupValue['PromptGroupId'] ?? '';
                                                        echo $this->Form->select(
                                                            'recording_prompt_group_group_id[]',
                                                            $cardsgroup,
                                                            [
                                                                'class' => "form-control fastselect",
                                                                'id' => 'recordingPromptGroupGroupId',
                                                                'multiple',
                                                                'value' => $RecordingGroupValue['PromptGroupId']
                                                            ]
                                                        );
                                                    ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <div id="RecordingPomptCardList"></div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="radio-inline">
                                                    <input id="prompt1" type="checkbox"
                                                        name="recordingpromptgrouptype[]"
                                                        value="l" class="recordingpromptgrouptype"
                                                    />
                                                    <?php echo $languageName;?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt2" type="checkbox"
                                                        name="recordingpromptgrouptype[]"
                                                        value="e" class="recordingpromptgrouptype"
                                                    />
                                                    English
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt3" type="checkbox"
                                                        name="recordingpromptgrouptype[]"
                                                        value="i" class="recordingpromptgrouptype"
                                                    />
                                                    Image
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt4" type="checkbox"
                                                        name="recordingpromptgrouptype[]"
                                                        value="a" class="recordingpromptgrouptype"
                                                    />
                                                    Audio
                                                </label>
                                                <label class="radio-inline">
                                                    <input id="prompt5" type="checkbox"
                                                        name="recordingpromptgrouptype[]"
                                                        value="v" class="recordingpromptgrouptype"
                                                    />
                                                    Video
                                                </label>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-3"><label for="ques-name">Display</label></div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                            $chkElement = ['value' => 'Y', 'hiddenField' => 'N'];
                                                        if (
                                                            isset($exercise->exerciseoptions[0]['response_true_false'])
                                                            && $exercise->exerciseoptions[0]['response_true_false']
                                                                == 'Y'
                                                        ) {
                                                            $chkElement['checked'] = true;
                                                        }
                                                        ?>
                                                        <?= $this->Form->checkbox(
                                                            'response_true_false_group',
                                                            $chkElement
                                                        ) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <label>Preview</label>
                                                <div id='recording_prompt_group_card_preview'></div>
                                                <div id='recording_prompt_preview'></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($exercise->exercise_type == 'recording' && $exercise->card_type == 'custom') { ?>
                                <div id="RecordingCustomSection" style="display:none;">
                                    <div class="row">
                                        <div class="col-sm-2"><label>Prompt #1</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                                $rpv1 = (isset($exercise->exerciseoptions[0])
                                                    && $exercise->card_type == 'custom'
                                                    && $exercise->exercise_type == 'recording')
                                                    ? ($exercise->exerciseoptions[0]['card_id'] != '')
                                                    ? 'card' : 'html' : '';
                                                echo $this->Form->select(
                                                    'recording_custom_prompt_type',
                                                    [
                                                        'card' => 'Card',
                                                        'html' => 'HTML'
                                                    ],
                                                    [
                                                        'class' => "form-control",
                                                        'id' => 'recording_custom_prompt_type',
                                                        'empty' => 'Select Type',
                                                        'value' => $rpv1
                                                    ]
                                                );
                                            ?>
                                        </div>
                                        <div class="col-sm-12" id="RecordingPromptForm"></div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="RecordingCustomPromptPreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2"><label>Response #1</label></div>
                                        <div class="col-sm-12">
                                            <?php
                                                echo $this->element(
                                                    'exerciseCardsList',
                                                    [
                                                        'checkboxname' => 'recording_custom_response_card_id',
                                                        'type' => 'RecordingCustomSectionResponse',
                                                        'value' => $exercise->exerciseoptions[0]['responce_card_id'],
                                                    ]
                                                );
                                            ?>
                                        </div>
                                        <div class="col-sm-12">
                                            <label>Preview</label>
                                            <div id="RecordingCustomResponsePreview"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3"><label for="ques-name">Display</label></div>
                                        <div class="col-sm-6">
                                            <?php
                                            $chkElement = array('value' => 'Y', 'hiddenField' => 'N');
                                            if (
                                                isset($exercise->exerciseoptions[0]['response_true_false'])
                                                && $exercise->exerciseoptions[0]['response_true_false'] == 'Y'
                                            ) {
                                                $chkElement['checked'] = true;
                                            }
                                            ?>
                                            <?= $this->Form->checkbox('response_true_false_custom', $chkElement) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="box-footer" id="exerciseStepTwoSubmit"
                            <?php
                            if (
                                isset($exercise->promteresponsetype)
                                && isset($exercise->card_type)
                                && isset($exercise->exercise_type)
                            ) { ?>
                            style="display: block;"
                                   <?php
                            } else {
                                echo 'style="display: none;"';
                            } ?>
                        >
                            <button onclick="validateForm()" type="button"
                                class="btn btn-primary">Submit</button>
                        </div>
                        <?= $this->Form->end() ?> <!--End of exercise form-->
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="fileLibrary">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">File Library</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class='col-sm-6 col-md-6'>
                        <div class="form-group">
                            <span class="input-group">
                                <input type="text" autocomplete="off"
                                    class="form-control" placeholder="Keyword" id="search">
                                <input type="hidden" id="typebox">
                                <input type="hidden" id="inputType">
                                <span class="input-group-addon" id="searchKeyword">Search</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row text-center">
                        <img src='<?php echo $this->request->getAttribute("webroot") . 'img/loader.png'?>'
                            class="img-responsive">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"
                    id="select-file-btn">Select File</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="exerciseDeleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Exercise Delete Warning</h4>
            </div>
            <div class="modal-body">
                <div class="box-body" id="exerciseDeleteDiv">

                </div>
            </div>
        </div>
    </div>
</div>

<?php
// External CSS and JavaScript files
echo $this->Html->css('AdminLTE./bower_components/datatables.net-bs/css/dataTables.bootstrap.min');
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net/js/jquery.dataTables.min',
    ['block' => 'script']
);
echo $this->Html->script(
    'AdminLTE./bower_components/datatables.net-bs/js/dataTables.bootstrap.min',
    ['block' => 'script']
);
echo $this->Html->script('fastselect.standalone.min.js', ['block' => 'script']);
echo $this->Html->script(
    '/js/virtual_keyboard/vk_loader.js?vk_layout=LA%20Lakhota%20Standard',
    ['block' => 'script']
);
?>


<?php
// JavaScript validation functions
echo $this->Html->script('exerciseList', ['block' => 'scriptBottom']); // Include script for table
echo $this->Html->script('exerciseValidation', ['block' => 'scriptBottom']);
// Include centralized API routes
echo $this->element('api_routes');

$this->Html->scriptStart(['block' => 'scriptBottom']);
    // JavaScript/PHP template code
    echo $this->element(
        'manageexercises_js',
        ["mcqcp" => $mcqcp ?? null,
         "mcqcr" => $mcqcr ?? null]
    );
    $this->Html->scriptEnd();
?>

<?php
if (isset($exercise->id)) {
    // General JavaScript functions
    echo $this->Html->script('select2CardList.js', ['block' => 'scriptBottom']);
    echo $this->Html->script('exerciseScript', ['block' => 'scriptBottom']);
}
?>
