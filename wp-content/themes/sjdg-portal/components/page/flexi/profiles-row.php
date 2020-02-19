
<?php $is_manager_section = get_sub_field( 'display_type' ) === 'small'; ?>

<?php $class = ( $is_manager_section ) ? 'section section--manager' : 'section section-profile' ; ?>

<section class="<?php echo $class; ?>">

    <div class="shell">

        <div class="section__body">

	        <?php if( have_rows( 'profiles_row_profiles' ) ): while( have_rows( 'profiles_row_profiles' ) ): the_row(); ?>

                <?php if( $is_manager_section ): ?>

                    <div class="profile profile--manager">
                        <div class="profile__image">
                            <img src="<?php the_sub_field( 'profile_image_id' ); ?>" alt="" width="410" height="320">
                        </div><!-- /.profile__image -->

                        <div class="profile__body">
                            <div class="profile__body-title">
                                <h3>
	                                <?php the_sub_field( 'profile_name' ); ?>

                                    <span><?php the_sub_field( 'profile_job_title' ); ?></span>
                                </h3>
                            </div><!-- /.profile__body-title -->

                            <p class="hidden-xs">
                                <a href="tel:<?php the_sub_field( 'profile_phone' ); ?>"><strong><?php the_sub_field( 'profile_phone' ); ?></strong></a>
                            </p>

                            <a href="tel:<?php the_sub_field( 'profile_phone' ); ?>" class="btn visible-xs-block">CALL</a>

                            <a href="mailto:<?php the_sub_field( 'profile_email' ); ?>" class="btn">EMAIL</a>

                        </div><!-- /.profile__body -->

                    </div><!-- /.profile -->

                <?php else: ?>

                    <div class="profile">

                        <div class="profile__image">
                            <img src="<?php the_sub_field( 'profile_image_id' ); ?>" alt="" width="410" height="320">
                        </div><!-- /.profile__image -->

                        <div class="profile__body">

                            <div class="profile__body-title">
                                <h3>
                                    <?php the_sub_field( 'profile_name' ); ?>

                                    <span><?php the_sub_field( 'profile_job_title' ); ?></span>
                                </h3>

                                <div class="btn-group">
                                    <a href="tel:<?php the_sub_field( 'profile_phone' ); ?>" class="btn visible-xs-inline-block">CALL</a>

                                    <a href="mailto:<?php the_sub_field( 'profile_email' ); ?>" class="btn">EMAIL</a>
                                </div><!-- /.btn-group -->
                            </div><!-- /.profile__body-title -->

                            <?php the_sub_field( 'profile_description' ); ?>

                            <p class="hidden-xs">
                                <a href="tel:<?php the_sub_field( 'profile_phone' ); ?>"><strong><?php the_sub_field( 'profile_phone' ); ?></strong></a>
                            </p>

                        </div><!-- /.profile__body -->

                    </div><!-- /.profile -->

                <?php endif; ?>

	        <?php endwhile; endif; ?>

        </div>

    </div><!-- /.shell -->

</section><!-- /.section section-/-profile -->
