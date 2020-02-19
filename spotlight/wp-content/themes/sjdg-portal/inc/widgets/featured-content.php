<?php
/**
 * Featured Content Widget
 */
class Featured_Content_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'featured_content_widget',
			'Featured Content',
			array( 'description' => 'Feature a News item or Project' )
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

        // Define widget ID
        $widget_id = $args['widget_id'];

        // Define prefixed widget ID
        $widget_acf_prefix = 'widget_';
        $widget_id_prefixed = $widget_acf_prefix . $widget_id;

		$featured_item_ids = get_field( 'featured_item_id', $widget_id_prefixed );
        $featured_item_id = $featured_item_ids[0];
		?>

        <li class="widget widget--main">
            <div class="widget__head">
                <h3 class="widget__title"><?php the_field( 'title', $widget_id_prefixed ); ?></h3><!-- /.widget__title -->
            </div><!-- /.widget__head -->

            <div class="widget__body">
                <div class="card card--article">
                    <a href="<?php echo get_the_permalink( $featured_item_id ) ?>" class="card__inner">
                        <div class="card__image" style="background-image: url(<?php echo get_the_post_thumbnail_url( $featured_item_id, '710x480-crop-710' ); ?>);"></div><!-- /.card__image -->

                        <div class="card__content">

                            <?php if ( 'post' == get_post_type( $featured_item_id ) ) : ?>
                                <p class="news-item-date">OCT 9</p>
                            <?php endif; ?>

                            <h5 class="lg"><?php echo get_the_title( $featured_item_id ); ?></h5><!-- /.lg -->
                        </div>
                    </a>
                </div><!-- /.card card-/-article -->
            </div><!-- /.widget__body -->
        </li><!-- /.widget widget-/-main -->

        <?php
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
