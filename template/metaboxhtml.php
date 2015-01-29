<?php $key = wph_generate_random_key(5); ?>
<div id="wph_metabox_content">
    <div class="title_inputs row">
        <div class="col-md-12">
            <input class="wph_title_input" type="text" placeholder="New Title Here" data-key="<?php echo $key ?>" autocomplete="off" id="cus_title" value="" size="70" name="cus_title[<?php echo $key ?>]" />
        </div>
        <div class="col-md-12">
            <a class="btn btn-danger remove_title" href="#" data-key="<?php echo $key ?>" data-version="new">Remove</a>
        </div>
        <br>
        <div class="clearfix"></div>
    </div>
</div>

<?php

function wph_generate_random_key($length)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $string = '';
    for ($i = 0; $i < $length; $i++)
    {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}
?>