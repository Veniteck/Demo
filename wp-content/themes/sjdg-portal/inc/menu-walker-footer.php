<?php
/**
 * DT_Footer_Walker_Nav_Menu class
 *
 * Class used to implement a custom HTML list of nav menu items for footer menu.
 *
 * Code copied from WordPress core is from version 4.8
 */
class DT_Footer_Walker_Nav_Menu extends Walker_Nav_Menu
{

  function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
     {
           global $wp_query;
          $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

          $class_names = $value = '';

          $classes = empty( $item->classes ) ? array() : (array) $item->classes;

          $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
          $class_names = ' class="'. esc_attr( $class_names ) . '"';

          $output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

          $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
          $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
          $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
          $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

          $prepend = '';
          $append = '';
          $description  = '';

          // Custom functionality
          if (0 == $depth ) {
              //$atts['class'] = 'footer__nav-link';
              $attributes .=  'class="footer__nav-link"';

          }

           $item_output = $args->before;
           $item_output .= '<a'. $attributes .'>';
           $item_output .= $args->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
           $item_output .= $description.$args->link_after;
           $item_output .= '</a>';
           $item_output .= $args->after;

           $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args, $id );
   }

   function start_lvl(&$output, $depth = 0, $args  = array()) {
       $indent = str_repeat("\t", $depth);
       $output .= "\n$indent<ul>\n";
   }

}
