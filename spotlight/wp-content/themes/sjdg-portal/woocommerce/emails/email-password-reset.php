<?php
/**
 * Custom password reset email
 *
 */
if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', 'Password Reset', $email);
?>

<p>Dear Valued Customer,</p>
<p>Someone has requested a password reset for the following account:</p>
<p><strong>Site Name: </strong><?=$site_name?></p>
<p><strong>Username: </strong><?=$user_login?></p>
<p>If this was a mistake, just ignore this email and nothing will happen</p>
<p>To reset your password, please click on the link below:</p>
<p>
    <a href="<?=home_url("login?form_action=reset_password&key=$key&login=" . rawurlencode($user_login), 'login')?>">
        <img src="<?= get_bloginfo('template_url') ?>/assets/build/assets/images/email-btn-reset-password.png" alt="RESET PASSWORD">
    </a>
</p>

<?php
do_action('woocommerce_email_footer', $email);
