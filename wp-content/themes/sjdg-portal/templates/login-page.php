<?php
/**
 * Template Name: Login Page
 */


//public login custom functionality
//user_password_reset true = they've reset their pass, let them sign in normally
//user_password_reset false = they haven't reset their pass, make them sign in
//default true
$user_password_reset = true;
$email_value = '';
if (isset($_GET['customer_email']) && $_GET['customer_email'] != '') {
    $email_value = esc_attr($_GET['customer_email']);

    $user_password_reset = false;

    //get user by email
    $user = get_user_by_email($email_value);
    
    //if user exists, get their 'reset user_password_reset' flag
    if($user) {
        $user_password_reset = get_field('user_password_reset', $user);
    }
}

get_header('login');
the_post();
?>

<noscript>
    <meta http-equiv="refresh" content="0;url=/wp-login.php">
</noscript>

<script>
    var _nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
    var login_ajax_url = '<?php echo rest_url('dt/handle_login'); ?>';
    var site_url = '<?php echo home_url(); ?>';
    var redirect_query_string = '<?php echo $_SERVER['QUERY_STRING']; ?>';
    var redirect_page = '<?php if (isset($_GET['view'])) {echo $_GET['view'];}?>';

    <?php if(!$user_password_reset) : ?>
        jQuery(document).ready(function ($) {
            $( document ).ready(function() {
                $('.lost--password-link').trigger("click");
            });
        }(jQuery));
    <?php endif ?>
</script>

<main class="main">

    <div class="intro intro--fullwidth intro--content-center">

        <div class="intro__image" style="background-image: url( <?php the_field('background_image');?> );"></div><!-- /.intro__image -->

        <div class="intro__content">

            <div class="shell">

                <?php

                    $class = 'login-form';

                    if (isset($_GET['form_action']) && $_GET['form_action'] === 'user_reset_password') {
                        $class = 'password-reset-form';
                    }

                    if (isset($_GET['form_action']) && $_GET['form_action'] === 'reset_password') {
                        $class = 'reset-password';
                    }

                ?>

                <div id="login--form-wrapper" class="<?php echo $class; ?> intro__content-inner">

                    <div class="login--form-wrapper form--wrapper">

                        <div class="form-main">

                            <form id="login_form" action="" method="post">

                                <div class="notifications_wrapper"></div>

                                <div class="form--heading">

                                    <h3 class="form-title">Client Access</h3>

                                    <p><?php the_field('login_form_description');?></p>

                                </div>

                                <p class="form--row">
                                    <input type="text" name="login-email" placeholder="Email Address" class="field" value="<?= $email_value ?>" />
                                </p>

                                <p class="form--row">
                                    <input type="password" name="login-password" placeholder="Password" class="field" />

                                    <span class="form--row-description">
                                    <a class="lost--password-link" href="javascript:void(0)">Lost your password?</a>
                                </span>
                                </p>

                                <p class="form--row">

                                    <button type="submit" name="action" value="user-login" class="btn">Login</button>

                                    <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('user-login'); ?>" />

                                </p>

                            </form>

                        </div>

                    </div>

                    <div class="reset--form-wrapper form--wrapper">

                        <div class="form-main">

                            <form id="user_reset_password_form" action="<?php echo add_query_arg(['form_action' => 'user_reset_password']); ?>" method="post">

                                <div class="notifications_wrapper"></div>

                                <div class="form--heading">

                                    <h3 class="form-title">Reset Password</h3>

                                    <p><?php the_field('reset_password_form_description');?></p>

                                </div>

                                <p class="form--row">

                                    <input type="email" name="login-email" placeholder="Email Address" class="field" value="<?= $email_value ?>" />

                                    <span class="form--row-description">
                                        <a class="cancel-reset-link" href="javascript:void(0)">Cancel</a>
                                    </span>

                                </p>

                                <p class="form--row">

                                    <button type="submit" name="action" value="user-password-reset" class="btn">Reset Password</button>

                                    <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('user-password-reset'); ?>" />

                                </p>

                            </form>

                        </div>

                    </div><!-- /.intro__content-inner -->

                    <?php if (isset($_GET['form_action']) && $_GET['form_action'] === 'reset_password'): ?>

                        <div class="reset--password-wrapper form--wrapper">

                            <div class="form-main">

                                <?php $check = check_password_reset_key($_GET['key'], $_GET['login']);?>

	                            <?php if (!is_wp_error($check)): ?>

                                    <form id="reset_password_form" action="" method="post">

                                        <div class="notifications_wrapper"></div>

                                        <div class="form--heading">

                                            <h3 class="form-title">Reset Password</h3>

                                            <p><?php the_field('resetting_password_form_description');?></p>

                                        </div>

                                        <p class="form--row">
                                            <input type="password" name="password-1" placeholder="New Password" class="field" />
                                        </p>

                                        <p class="form--row">
                                            <input type="password" name="password-2" placeholder="Confirm Password" class="field" />
                                        </p>

                                        <p class="form--row">

                                            <button type="submit" name="action" value="final-password-reset" class="btn">Reset Password</button>

                                            <input type="hidden" name="_key" value="<?php esc_attr_e($_GET['key']);?>" />
                                            <input type="hidden" name="_login" value="<?php esc_attr_e($_GET['login']);?>" />

                                            <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('final-password-reset'); ?>" />

                                        </p>

                                    </form>

	                            <?php else: ?>

                                    <div class="notifications_wrapper">
                                        <p class="error">
                                            Invalid reset password key. Please try again.
                                        </p>
                                    </div>

	                            <?php endif;?>

                            </div>


                        </div>

                    <?php endif;?>

                </div>

            </div><!-- /.shell -->

        </div><!-- /.intro__content -->

    </div><!-- /.intro -->

</main><!-- /.main -->

<?php get_footer();?>
