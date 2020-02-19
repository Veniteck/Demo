<?php
global $woocommerce;
$checkout = $woocommerce->checkout();
$fields = $woocommerce->checkout()->get_checkout_fields('billing');
?>

<h2 class="form__title">Contact Details</h2>

<div id="checkout--contact">
    <?php
    woocommerce_form_field(
        'billing_first_name',
        $fields['billing_first_name'],
        $checkout->get_value('billing_first_name')
    );
    ?>
</div>