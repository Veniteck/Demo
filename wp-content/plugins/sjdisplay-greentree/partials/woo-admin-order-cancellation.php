<?php
global $post;
$order_cancellation_note = get_post_meta($post->ID, 'order_cancellation_note', true);
?>
<p>Use the field below to add a cancellation note which will be sent along with the cancellation notification
    to the store manager</p>
<textarea name="order-cancellation-note"
          id="order-cancellation-note" cols="60" rows="5"><?= $order_cancellation_note ?></textarea>

