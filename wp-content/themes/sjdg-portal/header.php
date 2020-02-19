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

    <script>
        var AutocompleteSearchUrl = '<?php echo rest_url( 'sjgroup/autocomplete_search' ); ?>';
        var LoadMorePostsUrl = '<?php echo rest_url( 'sjgroup/load_products' ); ?>';
    </script>

</head>

<?php

$queried_object = get_queried_object();

?>

<body <?php body_class(); ?> data-page="1" <?php if( is_product_taxonomy() ): ?>data-taxonomy="<?php echo $queried_object->taxonomy; ?>" data-term_id="<?php echo $queried_object->term_id; ?>"<?php endif; ?> >
<div class="wrapper">
    <header class="header header--portal">
        <div class="header__inner">
            <div class="shell">
                <div class="header__content">
                    <a href="<?php echo home_url(); ?>" class="logo">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/build/assets/images/logo-image@2x.png" alt="">
                    </a>

                    <div class="form-search">

                        <form method="post" action="<?php echo wc_get_page_permalink( 'shop' ); ?>">

                            <div class="form__controls">
                                <input type="text" class="field" id="search--input" name="s" placeholder="Search our product range" value="<?php echo get_search_query(); ?>" />
                            </div><!-- /.form__controls -->

                            <div class="form__controls">

                                <div class="select select--search">

                                    <?php $categories = get_terms( [ 'taxonomy' => 'product_cat', 'parent' => 0 ] ); ?>

                                    <select name="search_category" id="select-search">
                                        <option value="">Entire range</option>
                                        <?php if( ! empty( $categories ) ): foreach( $categories as $category ): ?>
                                            <?php $selected = ( isset( $_POST['search_category'] ) && $_POST['search_category'] == $category->term_id ) ? 'selected="selected"' : '' ; ?>
                                            <option <?php echo $selected; ?> value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>

                                </div><!-- /.select select-/-search -->

                            </div><!-- /.form__controls -->

                            <button type="submit" class="form__btn form__btn--save">
                                <i class="ico-search-grey"></i>

                                <i class="ico-search-white"></i>
                            </button>

                            <div class="live_results"></div>

                        </form>

                    </div><!-- /.form-search -->

                </div><!-- /.header__content -->

                <?php if( WC()->cart->get_cart_contents_count() > 0 ): ?>
                    <a href="<?php echo wc_get_page_permalink( 'cart' ); ?>" class="basket">
                        <i class="ico-basket"></i>

                        <span><?php echo WC()->cart->get_cart_contents_count(); ?> items</span>
                    </a>
                <?php endif; ?>

            </div><!-- /.shell -->
        </div><!-- /.header__inner -->

        <div class="header__nav">
            <a href="#" class="btn-menu-wrapper">
                <span class="btn-menu-text">Menu</span>
                <span class="btn-menu">
                    <span></span>
                </span>
            </a>

            <div class="header__nav-inner">

                <nav class="nav">

	                <?php
	                $nav_args = array(
		                'container'      => '',
		                'theme_location' => 'primary',
		                'menu_class'     => '',
		                // 'menu_id'     => '', // empty string doesn't work
		                'echo'           => true
	                );
	                wp_nav_menu( $nav_args );
	                ?>

                </nav>

                <?php /* No need for this here, user must be logged in to access anything ??
                <div class="header__nav-actions">
                    <a href="#" class="btn btn--fullwidth btn--white">Customer Login</a>
                </div><!-- /.header__nav-actions -->
                */ ?>

            </div><!-- /.header__nav-inner -->

        </div><!-- /.header__nav -->

    </header><!-- /.header -->