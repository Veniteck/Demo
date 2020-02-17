<?php

//base path detection function by WPHosting
function fs_get_wp_config_path($base)
{
    if (file_exists($base . '/wp-config.php')) {
        $path = $base;
    } else {
        $path = fs_get_wp_config_path(dirname($base));
    }
    return $path;
}
