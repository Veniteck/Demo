<?php
global $post;
$order_data_to_openfreight = get_post_meta($post->ID, 'order_data_to_openfreight', true);
$order_openfreight_response = get_post_meta($post->ID, 'order_openfreight_response', true);
?>
<p><strong>Data sent to OpenFreight</strong></p>
<textarea name="" id="" cols="60" rows="10"><?= $order_data_to_openfreight ?></textarea>

<p><strong>Response from OpenFreight</strong></p>
<textarea name="" id="" cols="60" rows="5"><?= $order_openfreight_response ?></textarea>