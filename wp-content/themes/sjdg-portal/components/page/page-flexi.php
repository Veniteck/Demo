<?php
/**
* The following code replaces the typical
* "if ( get_row_layout() == X )" conditionals used when using flexi's
*
* The prerequisite for this code is that flexi partials have the same name
* as the flexi layout but with dashes replacing underscores
* eg. layout name: "text_row", flexi partial: "text-row"
*/

if ( have_rows( 'content_holder' ) ) {
	while ( have_rows( 'content_holder' ) ) {
        the_row();

		$partial = str_replace( '_', '-', get_row_layout() );

		if ( !empty( $partial ) ) {
			include get_template_directory() . '/components/page/flexi/' . $partial . '.php';
		}
    }
}
