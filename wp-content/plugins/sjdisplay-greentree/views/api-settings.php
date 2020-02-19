<div class="wrap">
    <?php include_once(SJDISPLAY_GREENTREE_PLUGIN_DIR . '/partials/header.php') ?>

    <h2>API Settings</h2>

    <form method="post" action="options.php">
        <?php
        settings_fields('greentree-api-settings');
        do_settings_sections('greentree-api-settings');
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Endpoint URL</th>
                <td>
                    <input type="text" name="greentree_endpoint_url" class="regular-text"
                           value="<?= esc_attr(get_option('greentree_endpoint_url')) ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">API Key</th>
                <td><input type="text" name="greentree_api_key" class="regular-text"
                           value="<?= SJDisplay_Greentree_Admin::get_secret_mask() ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Username</th>
                <td><input type="text" name="greentree_username" class="regular-text"
                           value="<?= SJDisplay_Greentree_Admin::get_secret_mask() ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Password</th>
                <td><input type="text" name="greentree_password" class="regular-text"
                           value="<?= SJDisplay_Greentree_Admin::get_secret_mask() ?>">
                </td>
            </tr>
        </table>
        <?php submit_button() ?>
    </form>

    <hr>

</div>