<div class="col-sm-12">
    <?php
    echo $this->element(
        'exerciseCardsList',
        array(
            'checkboxname' => $InputName,
            'type' => $InputName,
            'value' => '',
        )
    ); ?>
</div>
<div class="col-sm-12">
    <label class="radio-inline">
        <input type="checkbox" name="<?php echo $InputName?>Radio[]"
            value="l" class="<?php echo $InputName?>Radio"/><?php echo $languageName;?></label>
    <label class="radio-inline">
        <input type="checkbox" name="<?php echo $InputName?>Radio[]"
            value="e" class="<?php echo $InputName?>Radio"/>English</label>
    <label class="radio-inline">
        <input type="checkbox" name="<?php echo $InputName?>Radio[]"
            value="i" class="<?php echo $InputName?>Radio"/>Image</label>
    <label class="radio-inline">
        <input type="checkbox" name="<?php echo $InputName?>Radio[]"
            value="a" class="<?php echo $InputName?>Radio"/>Audio</label>
    <label class="radio-inline">
        <input type="checkbox" name="<?php echo $InputName?>Radio[]"
            value="v" class="<?php echo $InputName?>Radio"/>Video</label>
</div>
