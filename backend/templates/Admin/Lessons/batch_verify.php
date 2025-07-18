
<!-- Main content -->
<style>
    .filter-input {
        max-width: 100px;
        padding: 0 3px;
    }

    li {
        margin: 0 5px;
    }

    .indent-div {
        margin-left: 20px;
    }

    .center-text {
        text-align: center;
    }

    img, audio {
        max-width: 100px;
    }

    video {
        max-width: 200px;
    }
</style>
<section class="content-header">
    <h1>Bulk Lesson Data</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">

        <?php
        foreach ($data as $lessonAndChildren) { ?>
            <div class="col-md-12">
                <h2>Lesson: <?= $lessonAndChildren['lesson']['name'] ?></h2>
            </div>

            <?php
            foreach ($lessonAndChildren['frames'] as $frameAndChildren) { ?>
                <!-- FRAME TABLE -->
                <div class="indent-div col-md-6 col-lg-3">
                    <h3><?= $frameAndChildren['frame']['name'] ?></h3>

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
                            <?php
                            if ($frameAndChildren['frame']['FrameAudioUrl']) { ?>
                                <audio src="<?= $frameAndChildren['frame']['FrameAudioUrl'] ?>" controls
                                    controlslist="nodownload"/>
                                <?php
                            } ?>
                        </tr>
                    </table>
                </div>

                <!-- BLOCKS TABLE -->
                <div class="indent-div col-md-12">
                    <h3>Blocks: <?= $lessonAndChildren['lesson']['name'] ?>
                        , <?= $frameAndChildren['frame']['name'] ?>
                    </h3>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
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
                        foreach ($frameAndChildren['blocks'] as $blockAndChildren) { ?>
                            <tr>
                                <td><?= $blockAndChildren['block']['type'] ?></td>
                                <td><?php
                                if ($blockAndChildren['block']['AudioUrl']) { ?>
                                <audio src="<?= $blockAndChildren['block']['AudioUrl'] ?>" controls
                                    controlslist="nodownload"/>
                                    <?php
                                } ?>
                                </td>
                                <td>
                                <?php
                                if ($blockAndChildren['block']['ImageUrl']) { ?>
                                    <img src="<?= $blockAndChildren['block']['ImageUrl'] ?>"/>
                                    <?php
                                } ?>
                                </td>
                                <td>
                                <?php
                                if ($blockAndChildren['block']['VideoUrl']) { ?>
                                    <video src="<?= $blockAndChildren['block']['VideoUrl'] ?>" controls
                                        controlslist="nodownload"/>
                                    <?php
                                } ?>
                                </td>
                                <td><?= ($blockAndChildren['block']['is_card_lakota'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_english'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_audio'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_image'] == 'Y') ? '&#9989;' : '' ?></td>
                                <td><?= ($blockAndChildren['block']['is_card_video'] == 'Y') ? '&#9989;' : '' ?></td>
                            </tr>
                            <?php
                            if ($blockAndChildren['block']['custom_html']) { ?>
                            <tr>
                                <td colspan="9">
                                <?php
                                    //converts Unity Rich Text custom_html to HTML friendly by replacing
                                    //<size>-<font size>, <color>-<font color>, and \n-<br> along with endings
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
                                <?php
                            } ?>

                            <?php
                        } ?>
                        </tbody>
                    </table>
                </div>

                <!-- CARDS TABLE -->
                <div class="indent-div col-md-12">
                    <h3>Cards: <?= $lessonAndChildren['lesson']['name'] ?>
                        , <?= $frameAndChildren['frame']['name'] ?></h3>

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
                        <?php
                        foreach ($frameAndChildren['blocks'] as $blockAndChildren) {
                            if (array_key_exists('card', $blockAndChildren) && !empty($blockAndChildren['card'])) { ?>
                                <tr>
                                    <td>
                                    <?php
                                    switch ($blockAndChildren['card']['card_type_id']) {
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
                                    } ?>
                                    </td>
                                    <td><?= $blockAndChildren['card']['lakota'] ?></td>
                                    <td><?= $blockAndChildren['card']['english'] ?></td>
                                    <td><?= $blockAndChildren['card']['alt_lakota'] ?></td>
                                    <td><?= $blockAndChildren['card']['alt_english'] ?></td>
                                    <td><?= $blockAndChildren['card']['gender'] ?></td>
                                    <td>
                                    <?php
                                    if ($blockAndChildren['card']['FullAudioUrl']) { ?>
                                        <audio src="<?= $blockAndChildren['card']['FullAudioUrl'] ?>"
                                            controls controlslist="nodownload"/>
                                        <?php
                                    } ?>
                                    </td>
                                    <td>
                                    <?php
                                    if ($blockAndChildren['card']['FullImageUrl']) { ?>
                                        <img src="<?= $blockAndChildren['card']['FullImageUrl'] ?>"/>
                                        <?php
                                    } ?>
                                    </td>
                                    <td>
                                    <?php
                                    if ($blockAndChildren['card']['FullVideoUrl']) { ?>
                                        <video src="<?= $blockAndChildren['card']['FullVideoUrl'] ?>"
                                            controls controlslist="nodownload"/>
                                        <?php
                                    } ?>
                                    </td>
                                    <td>
                                    <?php
                                    if (!$blockAndChildren['card']['include_review']) { ?>
                                        &#10060;
                                        <?php
                                    } ?></td>
                                </tr>
                                <?php
                            }
                        } ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
        } ?>

            <div class="row">
                <div class="col-sm-12">
                    <?= $this->Form->create(
                        null,
                        [
                            'type' => 'post',
                            'class' => 'form-inline',
                            'id' => 'lessonsupload'
                        ]
                    ); ?>
                    <input type="hidden" name="datastring" value='<?= json_encode($data) ?>'/>
                    <input type="submit" name="savebtn" class="btn btn-primary" value="Save All"/>
                    <input type="submit" name="cancelbtn" class="btn btn-danger" value="Cancel"/>
                    <?= $this->Form->end(); ?>
                </div>
            </div>
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
