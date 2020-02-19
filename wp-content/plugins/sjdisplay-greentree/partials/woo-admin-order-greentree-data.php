<?php
global $post;
$order_data_sent_timestamp = get_post_meta($post->ID, 'order_data_sent_timestamp', true);
$order_data_to_greentree = get_post_meta($post->ID, 'order_data_to_greentree', true);
$order_greentree_response = get_post_meta($post->ID, 'order_greentree_response', true);
$order_greentree_reference_id = get_post_meta($post->ID, 'order_greentree_reference_id', true);
$order_greentree_status = get_post_meta($post->ID, 'order_greentree_status', true);
$order_greentree_modified_timestamp = get_post_meta($post->ID, 'order_greentree_modified_timestamp', true);
$order_greentree_modified_user = get_post_meta($post->ID, 'order_greentree_modified_user', true);
$order_greentree_invoice = get_post_meta($post->ID, 'order_greentree_invoice', true);
$order_stripe_result = get_post_meta($post->ID, 'order_stripe_result', true);
$order_greentree_receipt_response = get_post_meta($post->ID, 'order_receipt_response', true);
$order_greentree_packing_slip = get_post_meta($post->ID, 'order_greentree_packing_slip', true);
?>
<?php if ($order_data_sent_timestamp): ?>
    <p><strong>Timestamp of data sent to GreenTree</strong></p>
    <p><?= $order_data_sent_timestamp ?></p>

    <p><strong>Data sent to GreenTree</strong></p>
    <textarea name="" id="" cols="60" rows="10"><?= $order_data_to_greentree ?></textarea>

    <p><strong>Response from GreenTree</strong></p>
    <textarea name="" id="" cols="60" rows="5"><?= $order_greentree_response ?></textarea>

    <p><strong>GreenTree Order Reference</strong></p>
    <p><?= $order_greentree_reference_id ?></p>

    <p><strong>GreenTree Order Status</strong></p>
    <p><?= $order_greentree_status ?></p>

    <p><strong>GreenTree Order Modified Timestamp</strong></p>
    <p><?= $order_greentree_modified_timestamp ?></p>

    <p><strong>GreenTree Order Modified User</strong></p>
    <p><?= $order_greentree_modified_user ?></p>

    <p><strong>AR Invoice Data</strong></p>
    <textarea name="" id="" cols="60" rows="5"><?= $order_greentree_invoice ?></textarea>

    <p><strong>Packing Slip Data</strong></p>
    <textarea name="" id="" cols="60" rows="5"><?= $order_greentree_packing_slip ?></textarea>

    <p><strong>Stripe Result Data</strong></p>
    <textarea name="" id="" cols="60" rows="5"><?= $order_stripe_result ?></textarea>

    <p><strong>AR Receipt Response</strong></p>
    <textarea name="" id="" cols="60" rows="5"><?= $order_greentree_receipt_response ?></textarea>
<?php else: ?>
    <p>Data has not been sent to GreenTree yet...</p>
<?php endif ?>
