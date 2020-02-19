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
                <p>
                    POWERED BY

                    <br>

                    <strong>SJ DISPLAY GROUP</strong>
                </p>
            </div><!-- /.credits -->
        </div><!-- /.footer__inner -->
    </div><!-- /.shell -->
</div><!-- /.footer -->


</div><!-- /.wrapper -->

<?php wp_footer(); ?>

</body>
</html>