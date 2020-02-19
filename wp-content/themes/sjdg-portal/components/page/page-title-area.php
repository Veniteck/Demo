<section class="section-intro">

    <?php

    $tertiary_navigation = get_field( 'tertiary_menu' );

    if( $tertiary_navigation && $tertiary_navigation !== 'none' ): ?>

    <div class="shell">

        <?php wp_nav_menu( [
            'menu' => $tertiary_navigation,
            'container' => 'nav',
            'container_class' => 'nav--tertiary'
        ] ); ?>

    </div><!-- /.shell -->

    <?php else: ?>

        <div class="shell">

            <header class="section__head">
                <h3><?php the_title(); ?></h3>
            </header><!-- /.section__head -->

            <div class="section__body">
                
            </div><!-- /.section__body -->

        </div><!-- /.shell -->

    <?php endif; ?>

</section><!-- /.section-intro -->
