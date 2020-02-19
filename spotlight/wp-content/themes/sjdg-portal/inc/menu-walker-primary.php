<?php
/**
 * DT_Main_Walker_Nav_Menu class
 *
 * Class used to implement a custom HTML list of nav menu items for main menu.
 *
 * Code copied from WordPress core is from version 4.8
 */
class DT_Main_Walker_Nav_Menu extends Walker_Nav_Menu
{

    public $sub_menu_link_descriptions = array();

    /**
    * Starts the list before the elements are added.
    *
    * Adds classes to the unordered list sub-menus.
    *
    * @param string $output Passed by reference. Used to append additional content.
    * @param int    $depth  Depth of menu item. Used for padding.
    * @param array  $args   An array of arguments. @see wp_nav_menu()
    */
    public function start_lvl( &$output, $depth = 0, $args = array() ) {

        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );

        // Custom functionality

        // $output .= "$indent</ul>{$n}";
		$output .= "$indent<div class=\"nav__dropdown tabs-outer\"><a href=\"#\" class=\"nav__back desktop-hidden\" role=\"button\">Back</a><ul class=\"nav__links\">{$n}";

        // Initialize array for sub menu link descriptions
        $this->sub_menu_link_descriptions = array();
    }

    /**
     * Ends the list of after the elements are added.
     *
     * @param string   $output Passed by reference. Used to append additional content.
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function end_lvl( &$output, $depth = 0, $args = array() ) {

        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );

        // Custom functionality

		// $output .= "$indent</ul>{$n}";
        $output .= "$indent</ul>";
        $this->add_sub_menu_link_descriptions( $output );
        $output .= "</div>{$n}";

    }

    /**
	 * Starts the element output.
	 *
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';

        // Custom functionality
        if (1 == $depth ) {
            $atts['class'] = 'js-tab-toggle-hover';
        }

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before;

        // Custom functionality
        if (1 == $depth ) {
            $item_output .= '<span class="fa fa-chevron-right"></span>';
        }

        $item_output .= $args->link_before;
		$item_output .= $title . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

        // Custom functionality

        // Save sub menu link description data for use in end_lvl(). This needs to be done here, as end_lvl() does not have access to $item data.
        if ( 1 == $depth ) {

            $dt_link_description = array(
                'title' => $item->title,
                'summary' => get_field( 'summary', $item->ID ),
                'read_time' => get_field( 'read_time', $item->ID ),
                'url' => $item->url
            );

            $this->sub_menu_link_descriptions[] = $dt_link_description;

        }

	}

    /**
	 * Adds sub memu link descriptions to output (custom functionality)
	 *
	 * @param string   $output Passed by reference. Used to append additional content.
	 */
    public function add_sub_menu_link_descriptions( &$output ) {

        if ( !empty( $this->sub_menu_link_descriptions ) ) {

            $output .= '<div class="link-descriptions tabs mobile-hidden">';

            foreach ( $this->sub_menu_link_descriptions as $index => $link_description ) {

                // Add 'current' class to first sub menu item by default
                $current_class = ( 0 == $index ) ? 'current' : '';

                $output .= '<div class="link-description ' . $current_class . ' tab">';

                    // Title
                    $output .= '<div class="link-description__title">' . $link_description['title'] . '</div>';

                    // Summary (ACF)
                    $output .= $link_description['summary'];

                    // Read time (ACF)
                    if ( $link_description['read_time'] ) {
                        $output .= '<span class="link-description__meta">' . $link_description['read_time'] . '</span>';
                    }

                    // Learn more link
                    $output .= '<a href="' . $link_description['url'] . '" class="btn btn--primary">Learn More</a>';

                $output .= '</div>'; // <div class="link-description tab">

            }

            $output .= '</div>'; // <div class="link-descriptions tabs mobile-hidden">

        }

    }

}
