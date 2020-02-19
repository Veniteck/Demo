<section class="section section--downloads">
    <div class="shell">
        <div class="section__inner">
            <header class="section__head">
                <h2>DOWNLOADS</h2>
            </header><!-- /.section__head -->

            <div class="section__body">
                <ul class="list-downloads">

	                <?php if ( have_rows( 'downloads_row_items' ) ) : while ( have_rows( 'downloads_row_items' ) ) : the_row(); ?>

                    <?php $file = get_sub_field( 'file' ); ?>

                    <li>
                        <a href="<?php echo $file['url']; ?>" target="_blank">
                            <img src="<?php bloginfo( 'template_url' ); ?>/assets/build/assets/images/download.svg" width="40" height="37" alt="">

                            <?php echo $file['title']; ?>
                        </a>
                    </li>

                    <?php endwhile; endif; ?>

                </ul><!-- /.list-downloads -->

            </div><!-- /.section__body -->

        </div><!-- /.section__inner -->

    </div><!-- /.shell -->

</section><!-- /.section section-/-downloads -->