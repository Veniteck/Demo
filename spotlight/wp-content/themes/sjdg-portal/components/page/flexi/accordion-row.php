<section class="section section--accordion">

    <div class="shell">

        <div class="section__body">

            <div class="accordion">

	            <?php if ( have_rows( 'accordions_row_accordions' ) ) : while ( have_rows( 'accordions_row_accordions' ) ) : the_row(); ?>

                    <div class="accordion__section">

                        <div class="accordion__head">
                            <h3><?php the_sub_field( 'accordion_title' ); ?></h3>
                        </div><!-- /.accordion__head -->

                        <div class="accordion__body">
	                        <?php the_sub_field( 'accordion_content' ); ?>
                        </div><!-- /.accordion__body -->

                    </div><!-- /.accordion__section -->

                <?php endwhile; endif; ?>

            </div><!-- /.accordion -->

        </div><!-- /.section__body -->

    </div><!-- /.shell -->

</section><!-- /.section section-/-accordion -->

