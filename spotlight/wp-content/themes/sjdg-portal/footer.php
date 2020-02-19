<div class="footer">
    <div class="shell">
        <nav class="footer__nav">
	        <?php
	        $nav_args = array(
		        'container'      => '',
		        'theme_location' => 'footer',
		        'menu_class'     => '',
		        // 'menu_id'     => '', // empty string doesn't work
		        'echo'           => true
	        );
	        wp_nav_menu( $nav_args );
	        ?>
        </nav><!-- /.footer__nav -->

        <div class="footer__inner">

            <div class="credits">
                <a target="_blank" href="http://www.sjdg.com.au">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/build/assets/images/logo-image@2x.png" alt="SJ Display Group" title="SJ Display Group" />
                </a>
            </div><!-- /.credits -->

        </div><!-- /.footer__inner -->

    </div><!-- /.shell -->

</div><!-- /.footer -->


</div><!-- /.wrapper -->

<?php wp_footer(); ?>

</body>
</html>
