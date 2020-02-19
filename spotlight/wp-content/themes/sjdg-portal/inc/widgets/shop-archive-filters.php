<?php
/**
 * Archive Filters Widget
 */
class Shop_Archive_Filters_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'shop_archive_filters_widget',
			'Shop Archive Filters',
			array( 'description' => 'Shop archive filters widget' )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		get_template_part( 'components/products/sidebar-search' );

	}

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {

        // Required to mute to "no fields" message

    }

}
