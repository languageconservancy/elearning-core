<?php
//use Cake\View\Helper;
//use Cake\I18n\Time;
?>
<section class="content-header">
    <h1><?= __('Under construction Setting') ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Site Setting</a></li>
        <li class="active">Under construction Setting</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <?= $this->Form->create(null) ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="is_construction">Is Under Construction</label>
                        <select class="form-control" name="is_construction" id="is_construction">
                            <option value="N" <?php echo ($settingConstruction->value == "N")
                                ? "selected" : ""; ?>>Off</option>
                            <option value="Y" <?php echo ($settingConstruction->value == "Y")
                                ? "selected" : ""; ?>>On</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="metadata">Under Construction Html</label>
                        <div class="col-sm-12">
                            <textarea name="under_construction_html" id="editor1" rows="3"
                                class="form-control" style="resize:none"><?= isset($underConstructionHtml->value)
                                    ? trim($underConstructionHtml->value) : '' ?>
                            </textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>
</section>
