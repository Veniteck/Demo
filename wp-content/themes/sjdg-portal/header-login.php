<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php wp_title(); ?></title>

	<?php get_template_part( 'components/global/header-favicons' ); ?>
	<?php //get_template_part( 'components/global/header-app-meta' ); ?>

	<?php wp_head(); ?>

    <script>
        (function (d) {
            var config = {
                    kitId: 'kma7eqc',
                    scriptTimeout: 3000,
                    async: true
                },
                h = d.documentElement, t = setTimeout(function () {
                    h.className = h.className.replace(/\bwf-loading\b/g, "") + " wf-inactive";
                }, config.scriptTimeout), tk = d.createElement("script"), f = false,
                s = d.getElementsByTagName("script")[0], a;
            h.className += " wf-loading";
            tk.src = 'https://use.typekit.net/' + config.kitId + '.js';
            tk.async = true;
            tk.onload = tk.onreadystatechange = function () {
                a = this.readyState;
                if (f || a && a != "complete" && a != "loaded") return;
                f = true;
                clearTimeout(t);
                try {
                    Typekit.load(config)
                } catch (e) {
                }
            };
            s.parentNode.insertBefore(tk, s)
        })(document);
    </script>
</head>

<body <?php body_class( 'login' ); ?>>

<div class="wrapper">

    <header class="header">

        <div class="header__inner">
            <div class="shell">
                <a href="<?php echo site_url( '/' ); ?>" class="logo">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/build/assets/images/logo-image@2x.png" alt="">
                </a>
            </div><!-- /.shell -->
        </div><!-- /.header__inner -->

    </header><!-- /.header -->