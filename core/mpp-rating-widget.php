<?php
/**
 * Plugin widget class to list top rated media.
 *
 * @package mpp-media-rating
 */

// Exit if file access directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *  MPP rating widget for displaying
 *  Top n media of a user
 *  Top n photos of a user
 *  Top n media last weak, month, year
 */

/**
 * Class MPP_Rating_Widget
 */
class MPP_Rating_Widget extends WP_Widget {

	/**
	 * MPP_Rating_Widget constructor.
	 */
	public function __construct() {

		$widget_ops = array(
			'description' => __( 'A Widget for displaying top n media of user, photos of user, weak, month, year ', 'mpp-media-rating' ),
		);

		parent::__construct( false, _x( 'Top Rated Media( MediaPress )', 'widget name', 'mpp-media-rating' ), $widget_ops );
	}

	/**
	 * Render widget on frontend using saved settings.
	 *
	 * @param array $args Array of values.
	 * @param array $instance Current value of widget settings
	 */
	public function widget( $args, $instance ) {

		if ( $instance['user_type'] == 'loggedin' && ! is_user_logged_in() ) {
			return;
		}

		$user_type = $instance['user_type'];

		if ( $instance['component'] != 'members' || $user_type == 'any' ) {
			$user_id = 0;
		} else {
			$user_id = ( $instance['user_type'] == 'displayed' ) ? bp_displayed_user_id() : bp_loggedin_user_id();
		}

		$media_args = array(
			'component'    => $instance['component'],
			'component_id' => $user_id,
			'status'       => $instance['status'],
			'type'         => $instance['type'],
			'post_status'  => 'inherit',
		);

		$media_ids = mpp_get_object_ids( $media_args, mpp_get_media_post_type() );

		$rated_media = mpp_rating_get_top_rated_media( $media_ids, $instance['interval'], $instance['max_to_list'] );

		echo $args['before_widget'];

		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

		?>
        <div class="mpp-container mpp-widget-container mpp-media-widget-container mpp-media-video-widget-container">

			<?php if ( $rated_media ): ?>

				<?php foreach ( $rated_media as $id ): ?>

                    <div class='mpp-g mpp-item-list mpp-media-list mpp-<?php echo $instance["type"] ?>-list'>

                        <div
                                class="<?php mpp_media_class( 'mpp-widget-item mpp-widget-' . $instance["type"] . '-item ' . mpp_get_grid_column_class( 1 ) ); ?>">

                            <div class='mpp-item-entry mpp-media-entry mpp-photo-entry'>

                                <a href="<?php mpp_media_permalink( $id ); ?>" <?php mpp_media_html_attributes( array(
									'class'            => "mpp-item-thumbnail mpp-media-thumbnail mpp-" . $instance['type'] . "-thumbnail",
									'data-mpp-context' => 'widget'
								) ); ?>>
                                    <img src="<?php mpp_media_src( 'thumbnail', $id ); ?>"
                                         alt="<?php echo esc_attr( mpp_get_media_title( $id ) ); ?> "/>
                                </a>

                            </div>

                            <a href="<?php mpp_media_permalink( $id ); ?>" <?php mpp_media_html_attributes( array(
								'class'            => "mpp-item-title mpp-media-title ",
								'data-mpp-context' => 'widget'
							) ); ?> ><?php mpp_media_title( $id ); ?></a>

                            <div
                                    class="mpp-item-meta mpp-media-meta mpp-media-widget-item-meta mpp-media-meta-bottom mpp-media-widget-item-meta-bottom">
								<?php echo mpp_rating_get_rating_html( $id, 1 ); ?>
                            </div>

                        </div>
                    </div>

				<?php endforeach; ?>

			<?php else: ?>
				<?php _e( 'Nothing to show', 'mpp-media-rating' ); ?>
			<?php endif; ?>

        </div>

		<?php

		echo $args['after_widget'];
	}

	/**
	 * Update widget settings
	 *
	 * @param array $new_instance New instance of settings.
	 * @param array $old_instance Old instance of settings.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance                = $old_instance;
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['component']   = $new_instance['component'];
		$instance['status']      = $new_instance['status'];
		$instance['type']        = $new_instance['type'];
		$instance['max_to_list'] = $new_instance['max_to_list'];
		$instance['user_type']   = $new_instance['user_type'];
		$instance['interval']    = $new_instance['interval'];

		return $instance;
	}

	/**
	 * Render widget settings form
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'       => __( 'Ratings', 'mpp-media-rating' ),
			'component'   => 'members',
			'status'      => 'public',
			'type'        => 'photo',
			'max_to_list' => 5,
			'user_type'   => 'displayed',
			'interval'    => 'lweak'
		);

		$instance            = wp_parse_args( (array) $instance, $defaults );
		$title               = strip_tags( $instance['title'] );
		$component           = $instance['component'];
		$status              = $instance['status'];
		$type                = $instance['type'];
		$max_to_list         = strip_tags( $instance['max_to_list'] );
		$user_type           = $instance['user_type'];
		$interval            = $instance['interval'];
		$active_types        = mpp_get_active_types();
		$active_statuses     = mpp_get_active_statuses();
		$rateable_components = mpp_rating_get_rateable_components();
		$intervals           = mpp_rating_get_intervals();
		$media_of            = mpp_rating_show_media_of();

		?>

        <p>
            <label>
				<?php _e( 'Title:', 'mpp-media-rating' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                       value="<?php echo esc_attr( $title ); ?>"/>
            </label>
        </p>
        <p>
			<?php _e( 'Select Component: ', 'mpp-media-rating' ); ?>
			<?php foreach ( $rateable_components as $key => $label ) : ?>
            <label>
                <input class="widefat" name="<?php echo $this->get_field_name( 'component' ); ?>" type="radio"
                       value="<?php echo $key; ?>" <?php checked( $component, $key ); ?>/>
				<?php echo $label; ?>
            </label>
			<?php endforeach; ?>
        </p>

        <p>
			<?php _e( 'Select Type: ', 'mpp-media-rating' ); ?>
			<?php if ( ! empty( $active_types ) ) : ?>
                <select name="<?php echo $this->get_field_name( 'type' ); ?>">
                <?php foreach ( $active_types as $key => $label ) : ?>
                    <option value="<?php echo $key ?>" <?php selected( $type, $key ) ?>>
					    <?php _e( $label->label, 'mpp-media-rating' ); ?>
                    </option>
                <?php endforeach; ?>
                </select>
			<?php else: ?>
				<?php _e( 'No Active Media Type!', 'mpp-media-rating' ); ?>
			<?php endif; ?>
        </p>

        <p>
			<?php _e( 'Select Status: ', 'mpp-media-rating' ); ?>
			<?php if ( ! empty( $active_statuses ) ): ?>
                <select name="<?php echo $this->get_field_name( 'status' ); ?>">
				<?php foreach ( $active_statuses as $key => $label ) : ?>
                    <option value="<?php echo $key ?>" <?php selected( $status, $key ) ?>>
					    <?php _e( $label->label, 'mpp-media-rating' ); ?>
                    </option>
                <?php endforeach; ?>
                </select>
			<?php endif; ?>
        </p>

        <p>
            <label>
				<?php echo __( 'Max media to show', 'mpp-media-rating' ) ?>
                <input type="number" name="<?php echo $this->get_field_name( 'max_to_list' ); ?>"
                       value="<?php echo $max_to_list; ?>"/>
            </label>
        </p>

        <p>
			<?php echo __( 'Interval: ', 'mpp-media-rating' ) ?>
            <select name="<?php echo $this->get_field_name( 'interval' ); ?>">
			<?php foreach ( $intervals as $key => $label ) : ?>
                <option value="<?php echo $key ?>" <?php selected( $interval, $key ) ?>>
				    <?php _e( $label, 'mpp-media-rating' ); ?>
                </option>
            <?php endforeach; ?>
            </select>
        </p>

        <span><?php _e( "Section for user component", "mpp-media-rating" ); ?></span>
        <p>
			<?php echo __( 'List media of user: ', 'mpp-media-rating' ) ?>
			<?php foreach ( $media_of as $key => $label ): ?>
            <label>
                <input name="<?php echo $this->get_field_name( 'user_type' ); ?>" type="radio"
                       value="<?php echo $key; ?>" <?php checked( $key, $user_type ); ?>/>
				<?php echo $label; ?>
            </label>
			<?php endforeach; ?>
        </p>

		<?php
	}
}
