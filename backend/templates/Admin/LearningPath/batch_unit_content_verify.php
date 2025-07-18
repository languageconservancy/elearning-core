<?php
$errorsExist = false;
?>

<!-- Main content -->
<style>
    .table {
        width: 100%;
        table-layout: auto; /* adjust columns to fit content */
        border-collapse: collapse;
        border-spacing: 2px;
    }

    .table td {
        white-space: nowrap;
        overflow-x: auto;
        max-width: 100%;
    }

    /* Optional: Add styling to make the scrolling effect clearer */
    .table td::-webkit-scrollbar {
        height: 8px; /* Height of the scrollbar */
    }

    .table td::-webkit-scrollbar-thumb {
        background-color: #888; /* Scrollbar color */
    }

    .table td::-webkit-scrollbar-track {
        background-color: #f1f1f1; /* Track color */
    }

    /* Optional: Add styling to make the scrolling effect clearer */
    .table-container {
        overflow-x: auto;
        white-space: nowrap;
    }
    .table-container::-webkit-scrollbar {
        height: 8px; /* Height of the scrollbar */
    }

    .table-container::-webkit-scrollbar-thumb {
        background-color: #888; /* Scrollbar color */
    }

    .table-container::-webkit-scrollbar-track {
        background-color: #f1f1f1; /* Track color */
    }

    .overflow-x-auto {
        overflow-x: auto;
    }

    .filter-input {
        max-width: 100px;
        padding: 0 3px;
    }

    h4, h3, h2 {
        font-weight: bold;
    }

    li {
        margin: 0 5px;
    }

    .mw-40 {
        max-width: 40rem;
    }

    .p5 {
        padding: 5px;
    }

    .pt-1 {
        padding-top: 1rem;
    }

    .indent-div {
        margin-left: 20px;
    }

    .center-text {
        text-align: center;
    }

    img, audio {
        max-height: 20px;
    }

    video {
        max-height: 20px;
    }

    .isUpdated {
        background-color: lightblue;
    }

    .isRemoved {
        background-color: orange;
    }

    .inline {
        display: inline;
    }

    .isNew {
        background-color: lightgreen;
    }

    .isAdded {
        background-color: lightgreen;
    }

    .hasErrors {
        background-color: red;
    }

    .styled-checkbox {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 16px;
        height: 16px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 3px;
        position: relative;
    }

    .styled-checkbox:checked {
        background-color: #007bff;
        border-color: #007bff;
    }

    .styled-checkbox:checked::before {
        content: '✔';
        color: #fff;
        position: absolute;
        top: -3px;
        left: 2px;
    }

    .styled-checkbox:disabled {
        background-color: #e9ecef;
        border-color: #adb5bd;
        cursor: not-allowed;
    }

    .styled-checkbox:disabled:checked::before {
        color: #6c757d;
    }
</style>
<section class="content-header">
    <h1>Bulk Unit Content Data</h1>
</section>
<section class="content">
    <div class="box box-secondary p5">
        <div><b>Legend:</b></div>
        <div class="p5">
            <div class="inline p5">Fields required to be identical for an update to an existing card to occur:</div>
            <div class="p5"> If any of these fields is different, a new card will be created.</div>
            <span style="margin-left: 3px;"><b>Card Type</b>, <b>Language</b>, <b>English</b>, <b>Gender</b>.</span>
        </div>
        <table>
            <tr>
                <td class="isUpdated p5">
                    <input class="styled-checkbox" type="checkbox" checked disabled/> Updated value
                </td>
                <td class="mw-40">
                    <div style="margin-left: 5px;">If the box is checked, the new value is accepted. Otherwise value in database is left unchanged.</div>
                </td>
            </tr>
            <tr>
                <td class="isRemoved p5">
                    <input class="styled-checkbox" type="checkbox" disabled/> Removed value
                </td>
                <td class="mw-40">
                    <div style="margin-left: 5px;">Value of card in database will only be removed if this box is checked.</div>
                </td>
            </tr>
            <tr>
                <td class="isNew p5">
                    New card / Added Field
                </td>
                <td class="mw-40">
                    <div style="margin-left: 5px;">A green row means the card is new. A green field means an added field.</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box box-primary">
        <div class="box-body">
            <?= $this->Form->create(
                null,
                [
                    'type' => 'post',
                    'class' => 'form-inline',
                    'id' => 'lessonsupload'
                ]
            );
            //print_r($data,false); ?>
            <?php foreach ($data as $lessonAndChildren): ?>
                <div class="col-md-12">
                    <h2>Lesson: <?= $lessonAndChildren['lesson']['name'] ?></h2>
                </div>
                <!-- CARDS TABLE -->
                <?= (!empty($lessonAndChildren['cards']))
                    ? '<div class="indent-div col-md-12"><h3>Cards: '
                    . $lessonAndChildren['lesson']['name'] . '</h3></div>' : '' ?>
                <div class="indent-div col-md-12 table-container">

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Lakota</th>
                            <th>English</th>
                            <th>Alt. Lakota</th>
                            <th>Alt. English</th>
                            <th>Gender</th>
                            <th>Audio</th>
                            <th>Image</th>
                            <th>Video</th>
                            <th>Incl. Review</th>
                            <!-- <th>Include Review</th> -->
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lessonAndChildren['cards'] as $card): ?>
                            <tr class="<?= ($card['isNew'] ?? false) ? 'isNew' : (!empty($card['errors']) ? 'hasErrors' : '') ?>">
                                <td>
                                    <?php switch ($card['card_type_id'] ?? ""):
                                        case 1:
                                            echo 'Word';
                                            break;
                                        case 2:
                                            echo 'Verb';
                                            break;
                                        case 3:
                                            echo 'Pattern';
                                            break;
                                        default:
                                            echo 'ERROR!';
                                    endswitch ?>
                                </td>

                                <!-- Display lakota or errors for this card -->
                                <td><?= !empty($card['errors']) ? $card['errors'][0] : htmlspecialchars($card['lakota'], ENT_QUOTES, 'UTF-8') ?></td>

                                <?php if (!empty($card['errors'])):
                                    $errorsExist = true;
                                    continue;
                                endif; ?>

                                <!-- Display english for this card -->
                                <td><?= htmlspecialchars($card['english'], ENT_QUOTES, 'UTF-8') ?></td>

                                <!-- Display alt_lakota for this card -->
                                <td class="<?= $card['alt_lakotaIsUpdated'] ? 'isUpdated' : ($card['alt_lakotaIsRemoved'] ? 'isRemoved' : ($card['alt_lakotaIsAdded'] ? 'isAdded' : '')) ?>">
                                    <?php if ($card['alt_lakotaIsUpdated']): ?>
                                        <input type="checkbox" name="alt_lakotaIsUpdated[]" value="<?= $card['id'] ?>" checked>
                                    <?php elseif ($card['alt_lakotaIsAdded']): ?>
                                        <input type="checkbox" name="alt_lakotaIsAdded[]" value="<?= $card['id'] ?>" checked>
                                    <?php elseif ($card['alt_lakotaIsRemoved']): ?>
                                        <input type="checkbox" name="alt_lakotaIsRemoved[]" value="<?= $card['id'] ?>">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($card['alt_lakota'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <!-- Display alt_english for this card -->
                                <td class="<?= $card['alt_englishIsUpdated'] ? 'isUpdated' : ($card['alt_englishIsRemoved'] ? 'isRemoved' : ($card['alt_englishIsAdded'] ? 'isAdded' : '')) ?>">
                                    <?php if (!empty($card['alt_englishIsUpdated'])): ?>
                                        <input type="checkbox" name="alt_englishIsUpdated[]" value="<?= $card['id'] ?>" checked>
                                    <?php elseif (!empty($card['alt_englishIsAdded'])): ?>
                                        <input type="checkbox" name="alt_englishIsAdded[]" value="<?= $card['id'] ?>" checked>
                                    <?php elseif (!empty($card['alt_englishIsRemoved'])): ?>
                                        <input type="checkbox" name="alt_englishIsRemoved[]" value="<?= $card['id'] ?>">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($card['alt_english'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <!-- Display gender for this card. Cannot be removed, and if updated, it'll be a new card -->
                                <td><?= $card['gender'] ?></td>

                                <!-- Display audio for this card -->
                                <td class="<?= $card['audioIsUpdated'] ? 'isUpdated' : ($card['audioIsRemoved'] ? 'isRemoved' : ($card['audioIsAdded'] ? 'isAdded' : '')) ?>">
                                    <?php if ($card['FullAudioUrlArray']): ?>
                                        <?php if ($card['audioIsUpdated']): ?>
                                            <input type="checkbox" name="audioIsUpdated[]" value="<?= $card['id'] ?>" checked>
                                        <?php elseif ($card['audioIsAdded']): ?>
                                            <input type="checkbox" name="audioIsAdded[]" value="<?= $card['id'] ?>" checked>
                                        <?php endif; ?>
                                        <?php for ($i = 0; $i < $card['AudioCount']; $i++): ?>
                                            <audio src="<?= $card['FullAudioUrlArray'][$i] ?>" controls controlslist="nodownload"></audio>
                                        <?php endfor; ?>
                                    <?php elseif ($card['audioIsRemoved']): ?>
                                        <input type="checkbox" name="audioIsRemoved[]" value="<?= $card['id'] ?>">
                                    <?php endif; ?>
                                </td>

                                <!-- Display image for this card -->
                                <td class="<?= $card['image_idIsUpdated'] ? 'isUpdated' : ($card['image_idIsRemoved'] ? 'isRemoved' : ($card['image_idIsAdded'] ? 'isAdded' : '')) ?>">
                                    <?php if ($card['FullImageUrl']): ?>
                                        <?php if ($card['image_idIsUpdated']): ?>
                                            <input type="checkbox" name="image_idIsUpdated[]" value="<?= $card['id'] ?>" checked>
                                        <?php elseif ($card['image_idIsAdded']): ?>
                                            <input type="checkbox" name="image_idIsAdded[]" value="<?= $card['id'] ?>" checked>
                                        <?php endif; ?>
                                        <img src="<?= $card['FullImageUrl'] ?>"/>
                                    <?php elseif ($card['image_idIsRemoved']): ?>
                                        <input type="checkbox" name="image_idIsRemoved[]" value="<?= $card['id'] ?>">
                                    <?php endif; ?>
                                </td>

                                <!-- Display video for this card -->
                                <td class="<?= $card['video_idIsUpdated'] ? 'isUpdated' : ($card['video_idIsRemoved'] ? 'isRemoved' : ($card['video_idIsAdded'] ? 'isAdded' : '')) ?>">
                                    <?php if ($card['FullVideoUrl']): ?>
                                        <?php if ($card['video_idIsUpdated']): ?>
                                            <input type="checkbox" name="video_idIsUpdated[]" value="<?= $card['id'] ?>" checked>
                                        <?php elseif ($card['video_idIsAdded']): ?>
                                            <input type="checkbox" name="video_idIsAdded[]" value="<?= $card['id'] ?>" checked>
                                        <?php endif; ?>
                                        <video src="<?= $card['FullVideoUrl'] ?>"
                                            controls controlslist="nodownload">
                                        </video>
                                    <?php elseif ($card['video_idIsRemoved']): ?>
                                        <input type="checkbox" name="video_idIsRemoved[]" value="<?= $card['id'] ?>">
                                    <?php endif; ?>
                                </td>

                                <!-- Display include_review for this card -->
                                <td class="<?= $card['include_reviewIsUpdated'] ? 'isUpdated' : ($card['include_reviewIsRemoved'] ? 'isRemoved' : ($card['include_reviewIsAdded'] ? 'isAdded' : '')) ?>">
                                    <?php if ($card['include_reviewIsUpdated']): ?>
                                        <input type="checkbox" name="include_reviewIsUpdated[]" value="<?= $card['id'] ?>" checked>
                                    <?php elseif ($card['include_reviewIsRemoved']): ?>
                                        <input type="checkbox" name="include_reviewIsRemoved[]" value="<?= $card['id'] ?>">
                                    <?php elseif ($card['include_reviewIsAdded']): ?>
                                        <input type="checkbox" name="include_reviewIsAdded[]" value="<?= $card['id'] ?>" checked>
                                    <?php endif; ?>
                                    <?php if ($card['include_review']): ?>
                                        Yes
                                    <?php else: ?>
                                        No
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?= !empty($lessonAndChildren['frames']) ? '<div class="indent-div col-md-12"><h3>Lesson: '
                    . $lessonAndChildren['lesson']['name'] . '</h3></div>' : '' ?>
                <?php foreach ($lessonAndChildren['frames'] as $frameAndChildren): ?>
                    <!-- FRAME TABLE -->
                    <div class="indent-div col-md-6 col-lg-3">
                        <h4>Frame: <?= $frameAndChildren['frame']['name'] ?></h4>

                        <table class="table table-bordered">
                            <tr>
                                <th>Order</th>
                                <td><?= $frameAndChildren['frame']['frameorder'] ?></td>
                            </tr>
                            <tr>
                                <th>Orientation</th>
                                <td><?= $frameAndChildren['frame']['frame_preview'] ?></td>
                            </tr>
                            <tr>
                                <th>Audio</th>

                                <td>
                                    <?php if ($frameAndChildren['frame']['FrameAudioUrl']): ?>
                                        <audio src="<?= $frameAndChildren['frame']['FrameAudioUrl'] ?>" controls
                                            controlslist="nodownload"></audio>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- BLOCKS TABLE -->
                    <div class="indent-div col-md-12">
                        <h4>Blocks: <?= $lessonAndChildren['lesson']['name'] ?>
                            , <?= $frameAndChildren['frame']['name'] ?></h4>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>Card Details</th>
                                <th>Audio</th>
                                <th>Image</th>
                                <th>Video</th>
                                <th>Lakota?</th>
                                <th>English?</th>
                                <th>Audio?</th>
                                <th>Image?</th>
                                <th>Video?</th>
                            </tr>
                            </thead>
                            <tbody>
                        <?php
                        foreach ($frameAndChildren['blocks'] as $blockAndChildren): ?>
                            <tr>
                                <td><?= $blockAndChildren['block']['type'] ?></td>
                                <td>
                                    <?php if (!empty($blockAndChildren['card'])):?>
                                        <?= $blockAndChildren['card']['lakota'] ?>
                                         - <?= $blockAndChildren['card']['english'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($blockAndChildren['block']['AudioUrl']): ?>
                                        <audio src="<?= $blockAndChildren['block']['AudioUrl'] ?>" controls
                                            controlslist="nodownload"></audio>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($blockAndChildren['block']['ImageUrl']): ?>
                                        <img src="<?= $blockAndChildren['block']['ImageUrl'] ?>"/>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($blockAndChildren['block']['VideoUrl']): ?>
                                        <video src="<?= $blockAndChildren['block']['VideoUrl'] ?>" controls
                                            controlslist="nodownload"></video>
                                    <?php endif; ?>
                                </td>
                                <td><?= ($blockAndChildren['block']['is_card_lakota'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_english'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_audio'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_image'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_video'] == 'Y') ? '&#9989;' : '' ?></td>
                            </tr>
                            <?php if (!empty($blockAndChildren['block']['custom_html'])): ?>
                                <tr>
                                    <td colspan="9">
                                    <?php
                                    //converts Unity Rich Text custom_html to HTML
                                    //friendly by replacing <size>-<font size>,
                                    //<color>-<font color>, and \n-<br> along with endings
                                    echo str_replace(
                                        "\n",
                                        "<br>",
                                        str_replace(
                                            "</color>",
                                            "</font>",
                                            str_replace(
                                                "</size>",
                                                "</font>",
                                                str_replace(
                                                    "<color=",
                                                    "<font color=",
                                                    str_replace(
                                                        "<size=",
                                                        "<font size=",
                                                        $blockAndChildren['block']['custom_html']
                                                    )
                                                )
                                            )
                                        )
                                    ); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>

                <?= !empty($lessonAndChildren['exercises'])
                    ? '<div class="indent-div col-md-12"><h3>Exercises: '
                    . $lessonAndChildren['lesson']['name'] . '</h3></div>' : '' ?>

                <?php foreach ($lessonAndChildren['exercises'] as $exercisesAndChildren): ?>
                    <div class="indent-div col-md-12">
                        <h4>Exercise: <?= $exercisesAndChildren['exercise']['name'] ?></h4>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Exercise Type</th>
                                <th>Card Type</th>
                                <th>No. of Cards</th>
                                <th>Instructions</th>
                                <th>Bonus</th>
                                <th>Prompt Response Type</th>
                                <th>Prompt Type</th>
                                <th>Response Type</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?= $exercisesAndChildren['exercise']['name'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['exercise_type'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['card_type'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['noofcard'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['instruction'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['bonus'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['promteresponsetype'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['promotetype'] ?></td>
                                <td><?= $exercisesAndChildren['exercise']['responsetype'] ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>Card Type</th>
                                <th>Card Details</th>
                                <th>Prompt Preview Option</th>
                                <th>Response Preview Option</th>
                                <th>Fill in Blank Type</th>
                                <th>Text Option</th>
                                <th>Option Position</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($exercisesAndChildren['exercise_options'] as $exerciseOptionsAndChildren):
                            //display associated exercise options for P, R, and O?>

                                <tr>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['type'] ?></td>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['card_type'] ?></td>
                                <td><?= $exerciseOptionsAndChildren['card']['lakota'] ?>
                                    - <?= $exerciseOptionsAndChildren['card']['english'] ?></td>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['prompt_preview_option'] ?></td>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['responce_preview_option'];
                                ?>
                                </td>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['fill_in_the_blank_type'] ?></td>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['text_option'] ?></td>
                                <td><?= $exerciseOptionsAndChildren['exercise_option']['option_position'] ?></td>
                                </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <div class="row">
                <div class="col-sm-12 pt-1">
                    <input type="hidden" name="datastring" value="<?= htmlspecialchars(json_encode($data)); ?>"/>
                    <?php if ($errorsExist): ?>
                        <input type="button" value="Back" class="btn btn-danger"
                            onclick="history.back()"/>
                    <?php else: ?>
                        <input type="submit" name="savebtn" class="btn btn-primary" value="Save All"/>
                        <input type="submit" name="cancelbtn" class="btn btn-danger" value="Cancel"/>
                    <?php endif; ?>
                </div>
            </div>
            <?= $this->Form->end(); ?>
        </div>
    </div>
</section>
<!-- /.content -->
<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
?>
<?php
$this->Html->scriptEnd();
?>
