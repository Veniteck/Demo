<section class="section section--form">

    <div class="shell">

        <div class="section__inner">

            <header class="section__head">
                <h3><?php the_sub_field( 'form_row_title' ); ?></h3>
            </header><!-- /.section__head -->

            <div class="section__body">
                <aside class="section__aside">

                    <?php the_sub_field( 'form_row_description' ); ?>

                </aside><!-- /.section__aside -->

                <div class="section__content">

                    <div class="form form--default">
	                    <?php $form = get_sub_field('form_row_gravity_form_id');
	                    gravity_form($form, false, false, true, '', true); ?>
                    </div><!-- /.form form-/-default -->

                </div><!-- /.section__content -->

            </div><!-- /.section__body -->

        </div><!-- /.section__inner -->

    </div><!-- /.shell -->

</section><!-- /.section section-/-form -->
