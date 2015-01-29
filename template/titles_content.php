<div class="title_inputs row">
    <div class="col-md-12">
        <input class="wph_title_input" type="text" autocomplete="off" i="cus_title_<?php echo $i ?>" data-key="<?php echo $key ?>" value="<?php echo $value ?>" size="70" name="cus_title[<?php echo $key ?>]" />
    </div>
    <div class="col-md-3">
        <a class="btn btn-danger remove_title" data-titlecount="<?php echo $i ?>" href="#" data-key="<?php echo $key ?>" data-post="<?php echo$post->ID ?>" data-version="old">Remove</a>
    </div>
    <div class="col-md-9">
        <div class="progress">
            <?php $progress = $this->get_counter_for_single($post->ID,$key) ?>
            <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress ?>%;">
                <span class=""><?php echo $progress ?>%</span>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>