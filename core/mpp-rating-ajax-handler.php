<?php
/**
 * Class handle all ajax request made by plugin
 *
 * @package mpp-media-rating
 */

// Exit if file access directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MPP_Media_Rating_Ajax_Handler
 */
class MPP_Media_Rating_Ajax_Handler {

	/**
	 * MPP_Media_Rating_Ajax_Handler constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_mpp_rate_media', array( $this, 'rate' ) );
		add_action( 'wp_ajax_nopriv_mpp_rate_media', array( $this, 'rate' ) );
	}

	/**
	 * Do rating asynchronously
	 */
	public function rate() {

		$media_id = absint( $_POST['media_id'] );
		$vote     = absint( $_POST['rating'] );

		check_ajax_referer( 'mpp-media-rating', '_nonce' );

		if ( ! mpp_rating_current_user_can_rate() || ! mpp_rating_is_media_rateable( $media_id ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => __( 'Invalid action', 'mpp-media-rating' ) ) );
			exit;
		}

		$this->save_rating( get_current_user_id(), $media_id, $vote );
		exit;
	}

	/**
	 * Save user rating
	 *
	 * @param int $user_id User id.
	 * @param int $media_id Media Id.
	 * @param int $rating Rating number.
	 */
	private function save_rating( $user_id, $media_id, $rating ) {

		global $wpdb;

		$table_name = mpp_rating_get_table_name();

		if ( mpp_rating_has_user_rated( $user_id, $media_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'You have already rated', 'mpp-media-rating' ),
			) );
		}

		$data = array(
			'media_id' => $media_id,
			'user_id'  => $user_id,
			'rating'   => $rating,
		);

		$data_format = array( '%d', '%d', '%d' );

		$insert = $wpdb->insert( $table_name, $data, $data_format );

		if ( is_null( $insert ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unable to add', 'mpp-media-rating' ),
			) );
		}

		do_action( 'mpp_media_rated', $media_id, $user_id, $rating );

		$average_rating = mpp_rating_get_average_rating( $media_id );

		wp_send_json_success( array(
			'message' => array( 'average_rating' => $average_rating ),
		) );
	}
}

new MPP_Media_Rating_Ajax_Handler();
