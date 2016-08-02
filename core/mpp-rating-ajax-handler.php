<?php

class MPP_Media_Rating_Ajax_Handler {

	public function __construct() {

		add_action( 'wp_ajax_mpp_rate_media', array( $this, 'rate' ) );
		add_action( 'wp_ajax_nopriv_mpp_rate_media', array( $this, 'rate' ) );
	}

	public function rate() {

		$media_id = absint( $_POST['media_id'] );
		$_nonce   = $_POST['_nonce'];
		$vote     = absint( $_POST['rating'] );

		if ( ! wp_verify_nonce( $_nonce, 'mpp-media-rating' ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => __( 'Action unauthorized', 'mpp-media-rating' ) ) );
			exit;
		}

		if ( ! mpp_rating_current_user_can_rate() || ! mpp_rating_is_media_rateable( $media_id ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => __( 'Invalid action', 'mpp-media-rating' ) ) );
			exit;
		}

		$this->save_rating( get_current_user_id(), $media_id, $vote );

		exit;

	}

	private function save_rating( $user_id, $media_id, $rating ) {

		global $wpdb;

		$table_name = mpp_rating_get_table_name();

		if ( mpp_rating_has_user_rated( $user_id, $media_id ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => __( 'You have already rated', 'mpp-media-rating' ) ) );
			exit;
		}

		$data = array(
			'media_id' => $media_id,
			'user_id'  => $user_id,
			'rating'   => $rating
		);

		$insert = $wpdb->insert( $table_name, $data );

		if ( is_null( $insert ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => __( 'Unable to add', 'mpp-media-rating' ) ) );
			exit;
		}

		do_action( 'mpp_media_rated', $media_id, $user_id, $rating );

		$average_rating = mpp_rating_get_average_rating( $media_id );

		wp_send_json( array( 'type' => 'success', 'message' => array( 'average_rating' => $average_rating ) ) );

	}

}

new MPP_Media_Rating_Ajax_Handler();
