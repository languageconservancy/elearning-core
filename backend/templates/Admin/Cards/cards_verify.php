<?php
    $errorsExist = false;
?>

<!-- Main content -->
<style>
    li {
        margin: 0 5px;
    }

    .isUpdated {
        background-color: lightblue;
    }

    .isRemoved {
        background-color: orange;
    }

    .isAdded {
        background-color: lightgreen;
    }

    .p-1 {
        padding: 1rem;
    }

    .p-1-2 {
        padding: 0.5rem;
    }

    .pb-1 {
        padding-bottom: 1rem;
    }

    .inline {
        display: inline;
    }

    .max-w-100 {
        max-width: 100%;
    }

    .max-w-18 {
        max-width: 18rem;
    }

    .overlow-x-auto {
        overflow-x: auto;
    }
</style>

<section class="content">
    <div class="box box-secondary box-header p-1">
        <h3 class="box-title pb-1">Legend</h3>
        <table>
            <tr>
                <td class="isUpdated p-1-2">Updated value</td>
                <td class="p-1-2">Value is only updated if checkbox is checked</td>
            </tr>
            <tr>
                <td class="isAdded p-1-2">New card / Added Field</td>
                <td class="p-1-2">Value is only updated if checkbox is checked</td>
            </tr>
            <tr>
                <td class="isRemoved p-1-2">Removed value</td>
                <td class="p-1-2">Value is only removed if checkbox is checked</td>
            </tr>
        </table>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Cards List</h3>
                    <div class="box-tools">
                        <?= $this->Form->create(null, ['type' => 'get']); ?>
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="q" class="form-control pull-right"
                                placeholder="Search" value='<?php
                                    if (isset($_GET['q'])):
                                        echo $_GET['q'];
                                    endif;
                                ?>'>

                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <?= $this->Form->end(); ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $this->Form->create(
                                null,
                                [
                                    'type' => 'post',
                                    'class' => 'form-inline',
                                    'id' => 'cardfilter'
                                ]
                            ); ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="max-width: 100px;">Row in Spreadsheet</th>
                                        <th style="max-width: 100px;">Card ID in Database</th>
                                        <th>Card Type</br>
                                            <?php if ($checkboxData['cardTypeIdHasUpdates']): ?>
                                                <input type="checkbox" name="all_card_type_id_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th><?= $languageName; ?></br>
                                            <?php if ($checkboxData['lakotaHasUpdates']): ?>
                                                <input type="checkbox" name="all_lakota_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th>English</br>
                                            <?php if ($checkboxData['englishHasUpdates']): ?>
                                                <input type="checkbox" name="all_english_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th>Gender</br>
                                            <?php if ($checkboxData['genderHasUpdates']): ?>
                                                <input type="checkbox" name="all_gender_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th> Alt <?= $languageName; ?></br>
                                            <?php if ($checkboxData['altLakotaHasUpdates']): ?>
                                                <input type="checkbox" name="all_alt_lakota_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['altLakotaHasAdditions']): ?>
                                                <input type="checkbox" name="all_alt_lakota_added_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isAdded"> Additions</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['altLakotaHasRemovals']): ?>
                                                <input type="checkbox" name="all_alt_lakota_removed_checkboxes">
                                                <span style="font-weight: normal;" class="isRemoved"> Removals</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th>Alt English</br>
                                            <?php if ($checkboxData['altEnglishHasUpdates']): ?>
                                                <input type="checkbox" name="all_alt_english_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['altEnglishHasAdditions']): ?>
                                                <input type="checkbox" name="all_alt_english_added_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isAdded"> Additions</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['altEnglishHasRemovals']): ?>
                                                <input type="checkbox" name="all_alt_english_removed_checkboxes">
                                                <span style="font-weight: normal;" class="isRemoved"> Removals</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th>Audio</br>
                                            <?php if ($checkboxData['audioHasUpdates']): ?>
                                                <input type="checkbox" name="all_audio_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['audioHasAdditions']): ?>
                                                <input type="checkbox" name="all_audio_added_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isAdded"> Additions</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['audioHasRemovals']): ?>
                                                <input type="checkbox" name="all_audio_removed_checkboxes">
                                                <span style="font-weight: normal;" class="isRemoved"> Removals</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th>Image</br>
                                            <?php if ($checkboxData['imageIdHasUpdates']): ?>
                                                <input type="checkbox" name="all_image_id_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['imageIdHasAdditions']): ?>
                                                <input type="checkbox" name="all_image_id_added_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isAdded"> Additions</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['imageIdHasRemovals']): ?>
                                                <input type="checkbox" name="all_image_id_removed_checkboxes">
                                                <span style="font-weight: normal;" class="isRemoved"> Removals</span></br>
                                            <?php endif; ?>
                                        <th>Video</br>
                                            <?php if ($checkboxData['videoIdHasUpdates']): ?>
                                                <input type="checkbox" name="all_video_id_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['videoIdHasAdditions']): ?>
                                                <input type="checkbox" name="all_video_id_added_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isAdded"> Additions</span></br>
                                            <?php endif; ?>
                                            <?php if ($checkboxData['videoIdHasRemovals']): ?>
                                                <input type="checkbox" name="all_video_id_removed_checkboxes">
                                                <span style="font-weight: normal;" class="isRemoved"> Removals</span></br>
                                            <?php endif; ?>
                                        </th>
                                        <th>Include in Review</br>
                                            <?php if ($checkboxData['includeReviewHasUpdates']): ?>
                                                <input type="checkbox" name="all_include_review_updated_checkboxes" checked>
                                                <span style="font-weight: normal;" class="isUpdated"> Updates</span></br>
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                                    <?php if ($cards && !empty($cards)):
                                        //skip the header row, their first entry
                                        //should be on row 2 of the spreadsheet
                                        $count = 2;
                                        foreach ($cards as $card):
                                            if (!empty($card['error'])):
                                                $errorsExist = true; ?>
                                                <tr style="background-color: red;">
                                                    <td><?= $count++ ?></td>
                                                    <td><?= $card['error'] ?></td>
                                                </tr>
                                            <?php else: ?>
                                                <tr class="<?= empty($card->id) ? 'isAdded': '' ?>">
                                                    <td><?= $count++ ?></td>
                                                    <td><?= $card->id ?>
                                                    <td class="<?= $card['card_type_idIsUpdated'] ? 'isUpdated' : '' ?>">
                                                        <?php if ($card['card_type_idIsUpdated']): ?>
                                                            <input type="checkbox" name="card_type_idIsUpdated[]" value="<?= $card->id ?>" checked />
                                                        <?php endif; ?>
                                                        <?php switch ($card['card_type_id'] ?? "") {
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
                                                                if (empty($card->id)) {
                                                                    echo 'ERROR!';
                                                                }
                                                                echo '';
                                                        } ?>
                                                    </td>
                                                    <td class="<?= $card['lakotaIsUpdated'] ? 'isUpdated' : '' ?>">
                                                        <?php if ($card['lakotaIsUpdated']): ?>
                                                            <input type="checkbox" name="lakotaIsUpdated[]" value="<?= $card->id ?>" checked />
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($card->lakota, ENT_QUOTES, 'UTF-8') ?>
                                                    </td>
                                                    <td class="<?= $card['englishIsUpdated'] ? 'isUpdated' : '' ?>">
                                                        <?php if ($card['englishIsUpdated']): ?>
                                                            <input type="checkbox" name="englishIsUpdated[]" value="<?= $card->id ?>" checked />
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($card->english, ENT_QUOTES, 'UTF-8') ?>
                                                    </td>
                                                    <td class="<?= $card['genderIsUpdated'] ? 'isUpdated' : '' ?>">
                                                        <?php if ($card['genderIsUpdated']): ?>
                                                            <input type="checkbox" name="genderIsUpdated[]" value="<?= $card->id ?>" checked />
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($card->gender, ENT_QUOTES, 'UTF-8') ?>
                                                    </td>
                                                    <td class="<?= $card['alt_lakotaIsUpdated'] ? 'isUpdated' : ($card['alt_lakotaIsAdded'] ? 'isAdded' : ($card['alt_lakotaIsRemoved'] ? 'isRemoved' : '')) ?>">
                                                        <?php if ($card['alt_lakotaIsUpdated']): ?>
                                                            <input type="checkbox" name="alt_lakotaIsUpdated[]" value="<?= $card->id ?>" checked />
                                                        <?php elseif ($card['alt_lakotaIsAdded']): ?>
                                                            <input type="checkbox" name="alt_lakotaIsAdded[]" value="<?= $card->id ?>" checked />
                                                        <?php elseif ($card['alt_lakotaIsRemoved']): ?>
                                                            <input type="checkbox" name="alt_lakotaIsRemoved[]" value="<?= $card->id ?>" />
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($card->alt_lakota, ENT_QUOTES, 'UTF-8') ?>
                                                    </td>
                                                    <td class="<?= $card['alt_englishIsUpdated'] ? 'isUpdated' : ($card['alt_englishIsAdded'] ? 'isAdded' : ($card['alt_englishIsRemoved'] ? 'isRemoved' : '')) ?>">
                                                        <?php if ($card['alt_englishIsUpdated']): ?>
                                                            <input type="checkbox" name="alt_englishIsUpdated[]" value="<?= $card->id ?>" checked />
                                                        <?php elseif ($card['alt_englishIsAdded']): ?>
                                                            <input type="checkbox" name="alt_englishIsAdded[]" value="<?= $card->id ?>" checked />
                                                        <?php elseif ($card['alt_englishIsRemoved']): ?>
                                                            <input type="checkbox" name="alt_englishIsRemoved[]" value="<?= $card->id ?>" />
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($card->alt_english, ENT_QUOTES, 'UTF-8') ?>
                                                    </td>
                                                    <td class="<?= $card['audioIsUpdated'] ? 'isUpdated' : ($card['audioIsAdded'] ? 'isAdded' : ($card['audioIsRemoved'] ? 'isRemoved' : '')) ?>">
                                                        <?php if ($card['FullAudioUrlArray']): ?>
                                                            <?php if ($card['audioIsUpdated']): ?>
                                                                <input type="checkbox" name="audioIsUpdated[]" value="<?= $card->id ?>" checked/>
                                                            <?php elseif ($card['audioIsAdded']): ?>
                                                                <input type="checkbox" name="audioIsAdded[]" value="<?= $card->id ?>" checked/>
                                                            <?php endif; ?>
                                                            <?php for ($i = 0; $i < $card['AudioCount']; $i++): ?>
                                                                <audio controls class="max-w-18">
                                                                    <source src="<?= $card['FullAudioUrlArray'][$i] ?>" type="audio/mpeg" controls controlslist="nodownload">
                                                                    Your browser does not support the audio element.
                                                                </audio>
                                                            <?php endfor; ?>
                                                        <?php elseif ($card['audioIsRemoved']): ?>
                                                            <input type="checkbox" name="audioIsRemoved[]" value="<?= $card->id ?>"/>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="<?= $card['image_idIsUpdated'] ? 'isUpdated' : ($card['image_idIsAdded'] ? 'isAdded' : ($card['image_idIsRemoved'] ? 'isRemoved' : '')) ?>">
                                                        <?php if ($card['FullImageUrl']): ?>
                                                            <?php if ($card['image_idIsUpdated']): ?>
                                                                <input type="checkbox" name="image_idIsUpdated[]" value="<?= $card->id ?>" checked/>
                                                            <?php elseif ($card['image_idIsAdded']): ?>
                                                                <input type="checkbox" name="image_idIsAdded[]" value="<?= $card->id ?>" checked/>
                                                            <?php endif; ?>
                                                            <img src="<?= $card['FullImageUrl'] ?>" class="max-w-18" />
                                                        <?php elseif ($card['image_idIsRemoved']): ?>
                                                            <input type="checkbox" name="image_idIsRemoved[]" value="<?= $card->id ?>"/>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="<?= $card['video_idIsUpdated'] ? 'isUpdated' : ($card['video_idIsAdded'] ? 'isAdded' : ($card['video_idIsRemoved'] ? 'isRemoved' : '')) ?>">
                                                        <?php if ($card['FullVideoUrl']): ?>
                                                            <?php if ($card['video_idIsUpdated']): ?>
                                                                <input type="checkbox" name="video_idIsUpdated[]" value="<?= $card->id ?>" checked/>
                                                            <?php elseif ($card['video_idIsAdded']): ?>
                                                                <input type="checkbox" name="video_idIsAdded[]" value="<?= $card->id ?>" checked/>
                                                            <?php endif; ?>
                                                            <video controls class="max-w-18">
                                                                <source src="<?= $card['FullVideoUrl'] ?>" type="video/mp4" controls controlslist="nodownload">
                                                                Your browser does not support the video element.
                                                            </video>
                                                        <?php elseif ($card['video_idIsRemoved']): ?>
                                                            <input type="checkbox" name="video_idIsRemoved[]" value="<?= $card->id ?>"/>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="<?= $card['include_reviewIsUpdated'] ? 'isUpdated' : '' ?>">
                                                        <?php if ($card['include_reviewIsUpdated']): ?>
                                                            <input type="checkbox" name="include_reviewIsUpdated[]" value="<?= $card->id ?>" checked/>
                                                        <?php endif; ?>
                                                        <?php if ($card['include_review']): ?>
                                                            Yes
                                                        <?php else: ?>
                                                            No
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <input type="hidden" name="cards" value='<?= json_encode($cards) ?>' />
                            <?php if ($errorsExist): ?>
                                <div>
                                    <!-- Back button -->
                                    <input type="button" value="Back" class="btn btn-danger" onclick="history.back()"/>
                                </div>
                            <?php else: ?>
                                <!-- Save and cancel buttons -->
                                <input type="submit" name="savebtn" class="btn btn-primary" value="Save Cards" />
                                <input type="submit" name="cancelbtn" class="btn btn-danger" value="Cancel" />
                            <?php endif; ?>
                            <?= $this->Form->end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="delete_warning_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Card Delete Warning</h4>
            </div>
            <div class="modal-body">
                <div class="box-body" id="cardDeleteDiv">

                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<?php
$this->Html->scriptStart(['block' => 'scriptBottom']);
$this->Html->scriptEnd();
echo $this->Html->script('uploadCardsScript', ['block' => 'scriptBottom']);
?>