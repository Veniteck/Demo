<?php

return;

/**
$show_existing_menu = get_field( 'sidebar_show_existing_menu' );

// Get menu title
if ( $show_existing_menu == 1 || is_null( $show_existing_menu ) ) {

    $existing_menu_obj_id = get_field( 'sidebar_existing_menu' );

    $existing_menu_obj = wp_get_nav_menu_object( $existing_menu_obj_id );

    // If menu ACF has not be set, use default sidebar menu
    if ( empty( $existing_menu_obj ) ) {
        $menu_locations = get_nav_menu_locations();
        $existing_menu_obj = get_term( $menu_locations[ 'sidebar-default' ], 'nav_menu' );
    }

    $menu_title = $existing_menu_obj->name;

} else {
    $menu_title = get_field( 'sidebar_custom_menu_title' );
}

// Get related widget area
if ( dt_is_news_section() ) {
    $widget_area_id = 'sidebar-news';
} elseif ( dt_is_services_section() ) {
    $widget_area_id = 'sidebar-services';
} else {
    $widget_area_id = 'sidebar-default';
}
?>

<div class="sidebar hidden-xs">

    <ul class="widgets">

        <li class="widget widget--main widget--menu">

            <?php if ( !empty( $menu_title ) ) : ?>

                <div class="widget__head">
                    <h3 class="widget__title widget__title--white"><?php echo $menu_title ?></h3><!-- /.widget__title widget__title-/-white -->
                </div><!-- /.widget__head -->

            <?php endif; ?>

            <?php if ( $show_existing_menu == 1 || is_null( $show_existing_menu ) ) : ?>

                <div class="widget__body">

                    <?php

                    if( $existing_menu_obj ){

	                    wp_nav_menu( array(
		                    'menu' => $existing_menu_obj->ID,
		                    'container'      => '',
		                    'menu_class'     => 'list-links',
		                    'echo'           => true
	                    ) );

                    }

                    ?>

                </div><!-- /.widget__body -->

            <?php else : ?>

                <?php if ( have_rows( 'sidebar_custom_menu' ) ) : ?>

                    <div class="widget__body">

                        <ul class="list-links">

                            <?php while ( have_rows( 'sidebar_custom_menu' ) ) : the_row(); ?>

                                <?php $link = get_sub_field( 'link' ); ?>
                                <?php if ( $link ) : ?>

                                    <li>
                                        <a <?php if ( '#' === substr( $link['url'], 0, 1 ) ) { echo 'class="scroll-trigger"'; } ?> href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>"><?php echo $link['title']; ?></a>
                                    </li><!-- /.current -->

                                <?php endif; ?>

                            <?php endwhile; ?>

                        </ul><!-- /.list-links -->

                    </div><!-- /.widget__body -->

                <?php endif; ?>

            <?php endif; ?>

        </li><!-- /.widget widget-/-main -->

        <?php if ( !dynamic_sidebar( $widget_area_id ) ) : ?>

            <!--Need widgets <?php echo $widget_area_id; ?> -->

        <?php endif; ?>

    </ul><!-- /.widgets -->
</div><!-- /.sidebar hidden-xs --> */
