<div class="col-sm-12">
    <?php
    echo $this->element(
        'exerciseCardsList',
        array(
            'checkboxname' => $inputName,
            'type' => $inputName,
            'value' => '',
            'blockNo' => $blockNo,
            'responseType' => $responseType,
            'processTable' => false,
        )
    ); ?>
</div>
<div class="col-sm-12">
    <label class="radio-inline">
        <input type="checkbox"
            name="<?php echo $inputName?><?php echo $blockNo?><?php echo $responseType?>Radio[]"
            value="l" class="<?php echo $inputName?>Radio<?php echo $responseType?>"
            data-type="<?php if (isset($responseType)) {
                            echo $responseType;
                       } ?>"
            data-block="<?php if (isset($blockNo)) {
                            echo $blockNo;
                        } ?>"
        />
        <?php echo $languageName;?>
    </label>
    <label class="radio-inline">
        <input type="checkbox"
            name="<?php echo $inputName?><?php echo $blockNo?><?php echo $responseType?>Radio[]"
            value="e" class="<?php echo $inputName?>Radio<?php echo $responseType?>"
            data-type="<?php if (isset($responseType)) {
                            echo $responseType;
                       } ?>"
            data-block="<?php if (isset($blockNo)) {
                            echo $blockNo;
                        } ?>"
        />
        English
    </label>
    <label class="radio-inline">
        <input type="checkbox"
            name="<?php echo $inputName?><?php echo $blockNo?><?php echo $responseType?>Radio[]"
            value="i" class="<?php echo $inputName?>Radio<?php echo $responseType?>"
            data-type="<?php if (isset($responseType)) {
                           echo $responseType;
                       } ?>"
            data-block="<?php if (isset($blockNo)) {
                            echo $blockNo;
                        } ?>"
        />
        Image
    </label>
    <label class="radio-inline">
        <input type="checkbox"
            name="<?php echo $inputName?><?php echo $blockNo?><?php echo $responseType?>Radio[]"
            value="a" class="<?php echo $inputName?>Radio<?php echo $responseType?>"
            data-type="<?php if (isset($responseType)) {
                           echo $responseType;
                       } ?>"
            data-block="<?php if (isset($blockNo)) {
                            echo $blockNo;
                        } ?>"
        />
        Audio
    </label>
    <label class="radio-inline">
        <input type="checkbox"
            name="<?php echo $inputName?><?php echo $blockNo?><?php echo $responseType?>Radio[]"
            value="v" class="<?php echo $inputName?>Radio<?php echo $responseType?>"
            data-type="<?php if (isset($responseType)) {
                           echo $responseType;
                       } ?>"
            data-block="<?php if (isset($blockNo)) {
                            echo $blockNo;
                        } ?>"
        />
        Video
    </label>
</div>
