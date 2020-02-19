<?php
/**
 * Call To Action Widget
 */
class Call_To_Action_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'call_to_action_widget',
			'Call To Action',
			array( 'description' => 'Call to action with a title, icon and button' )
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
		?>

		<li class="widget widget--small">
            <div class="widget__head">
                <h3 class="widget__title">
                    <?php the_field( 'title', $widget_id_prefixed ); ?>

					<?php if ( get_field( 'icon', $widget_id_prefixed ) ) { ?>
						<img src="<?php the_field( 'icon', $widget_id_prefixed ); ?>" width="<?php the_field( 'icon_width', $widget_id_prefixed ); ?>" height="<?php the_field( 'icon_height', $widget_id_prefixed ); ?>" />
					<?php } ?>
                </h3><!-- /.widget__title -->
            </div><!-- /.widget__head -->

			<?php $button = get_field( 'button', $widget_id_prefixed ); ?>
			<?php if ( $button ) : ?>
				<div class="widget__body">
					<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn btn--blue"><?php echo $button['title']; ?></a>
				</div><!-- /.widget__body -->
			<?php endif; ?>

        </li><!-- /.widget widget-/-small -->

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
