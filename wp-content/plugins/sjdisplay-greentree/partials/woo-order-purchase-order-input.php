<?php
//check if po is required
$po_number_required = SJDisplay_Greentree::get_customer_po_number_required(get_current_user_id());

//po mandatory default
$po_mandatory = '';
$po_mandatory_class = '';
$po_mandatory_label = false;

//if po required
if ($po_number_required) {
    $po_mandatory = 'required="required"';
    $po_mandatory_class = 'validate-required';
    $po_mandatory_label = true;
}
?>
<div class="order-po-input">
    <h2 class="form__title">Purchase Order</h2>

    <p class="form-row form-row-wide form__col <?= $po_mandatory_class ?>" id="order_po_number_field">
        <label for="order_po_number" class="">
            Enter the purchase order number for this order
            <?php if ($po_mandatory_label) : ?>
                <abbr class="required" title="required">*</abbr>
            <?php endif ?>
        </label>
        <span class="woocommerce-input-wrapper">
            <input class="input-text field" name="order_po_number" <?= $po_mandatory ?>
                   id="order_po_number" placeholder="" type="text">
        </span>
    </p>
</div>
