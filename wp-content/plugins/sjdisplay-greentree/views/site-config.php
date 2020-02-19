<?php $sjdisplay_greentree = SJDisplay_Greentree::get_instance() ?>
<div class="wrap">
    <?php include_once(SJDISPLAY_GREENTREE_PLUGIN_DIR . '/partials/header.php') ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('greentree-site-config');
        do_settings_sections('greentree-site-config');

        //approval settings
        $approval_enabled = esc_attr(get_option('greentree_approval_enabled'));
        $approval_enabled_checked = '';
        if ($approval_enabled == 'on') {
            $approval_enabled_checked = 'checked';
        }

        //payment settings
        $payment_enabled = esc_attr(get_option('greentree_payment_enabled'));
        $payment_enabled_checked = '';
        if ($payment_enabled == 'on') {
            $payment_enabled_checked = 'checked';
        }

        //order API toggle
        //useful if client would like to test without sending order data to GreenTree
        $order_api_disable = esc_attr(get_option('greentree_order_api_disable'));
        $order_api_disable_checked = '';
        if ($order_api_disable == 'on') {
            $order_api_disable_checked = 'checked';
        }

        //parent store import toggle
        //this is to enable the parent store to be imported during bulk import cron script
        $parent_store_import = esc_attr(get_option('greentree_parent_store_import'));
        $parent_store_import_checked = '';
        if ($parent_store_import == 'on') {
            $parent_store_import_checked = 'checked';
        }
        ?>

        <h2>Site Configuration</h2>

        <table class="form-table">

            <tr valign="top">
                <th scope="row">Parent Customer Code</th>
                <td>
                    <input type="text" name="greentree_customer_code"
                           value="<?= esc_attr(get_option('greentree_customer_code')) ?>">
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Approval System Enabled</th>
                <td>
                    <label>
                        <input type="checkbox" name="greentree_approval_enabled" <?= $approval_enabled_checked ?>>
                    </label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Payment System Enabled</th>
                <td>
                    <label>
                        <input type="checkbox" name="greentree_payment_enabled" <?= $payment_enabled_checked ?>>
                    </label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Disable API calls for orders</th>
                <td>
                    <label>
                        <input type="checkbox" name="greentree_order_api_disable" <?= $order_api_disable_checked ?>>
                    </label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Parent store import</th>
                <td>
                    <label>
                        <input type="checkbox" name="greentree_parent_store_import" <?= $parent_store_import_checked ?>>
                    </label>
                </td>
            </tr>

        </table>

        <?php submit_button() ?>
    </form>

    <hr>

</div>