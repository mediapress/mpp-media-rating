<?php

class MPP_Media_Rating_Ajax_Handler {

	public function __construct() {

		add_action( 'wp_ajax_vote_me', array( $this, 'vote_me' ) );
		add_action( 'wp_ajax_nopriv_vote_me', array( $this, 'vote_me' ) );
	}

	public function vote_me() {

		$media_id = absint( $_POST['media_id'] );
		$_nonce   = $_POST['_nonce'];
		$vote     = absint( $_POST['vote'] );

		if ( ! wp_verify_nonce( $_nonce, 'mpp-media-rating' ) ) {

			wp_send_json( array( 'type' => 'error', 'message' => 'security error can not process' ) );
			exit;

		}

		if ( ! mpp_rating_is_user_can_rate() || ! mpp_rating_is_media_rateable( $media_id ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => 'can not be rated' ) );
			exit;
		}

		$this->insert_vote( get_current_user_id(), $media_id, $vote );

		exit;

	}

	public function insert_vote( $user_id, $media_id, $vote ) {

		global $wpdb;

		$table_name = mpp_rating_get_table_name();

		if ( mpp_rating_is_user_rated_on_media( $user_id, $media_id ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => 'can not vote again for same media' ) );
			exit;
		}

		$data = array(
			'media_id' => $media_id,
			'user_id'  => $user_id,
			'votes'    => $vote
		);

		$insert = $wpdb->insert( $table_name, $data );

		if ( is_null( $insert ) ) {
			wp_send_json( array( 'type' => 'error', 'message' => 'unable to insert' ) );
			exit;
		}

		do_action( 'mpp_media_rated', $media_id, $user_id, $vote );

		$average_vote = mpp_rating_get_average_vote_for_media( $media_id );

		wp_send_json( array( 'type' => 'success', 'message' => array( 'average_vote' => $average_vote ) ) );

	}

}

new MPP_Media_Rating_Ajax_Handler();
