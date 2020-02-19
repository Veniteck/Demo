<?php
//order object
//$order = wc_get_order($order->ID);
?>

<?php if ($order_po_number = get_post_meta($order->get_id(), 'order_po_number', true)): ?>
    <p class="form-field form-field-wide">
        <label for="order_po_number">PO Number:</label>
        <input type="text" disabled value="<?= $order_po_number ?>">
    </p>
<?php endif ?>