<?php

function dt_restrict_access()
{
    if (!is_admin() && !is_user_logged_in() && !is_page('login') && !in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
        wp_redirect(site_url('login') . '?' . $_SERVER['QUERY_STRING']); // get_site_url( get_current_blog_id(), 'login' ) );
        exit;
    }

}
add_action('template_redirect', 'dt_restrict_access');
