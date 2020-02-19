<?php

$search_title = "Search";
$search_action = get_home_url();
$search_value = 's';

?>
<form action="<?php echo $search_action; ?>" method="get">
    <label for="<?php echo $search_value; ?>>" class="search__label"><?php echo $search_title; ?></label>

    <div class="search__controls">
        <input type="search" name="<?php echo $search_value; ?>" id="<?php echo $search_value; ?>_input" value="" placeholder="" class="search__field">

        <button type="submit" class="btn btn--primary btn--primary-blue search__btn">Go</button>
    </div><!-- /.search__controls -->
</form>
