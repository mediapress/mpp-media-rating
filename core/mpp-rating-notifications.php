<?php

define( 'BP_MPP_RATING_NOTIFIER_SLUG', 'mpp_rating_notifier' );

Class MPP_Rating_Notifications {

	public function __construct() {

		add_action( 'mpp_media_rated', array( $this, 'send_notifications' ), 10, 3 );
		add_action( 'bp_setup_globals', array( $this, 'setup_globals' ) );
		add_action( 'bp_template_redirect', array( $this, 'mark_notification_read' ) );
		add_action( 'mpp_media_deleted', array( $this, 'clear_all_media_notification' ) );
	}

	public function send_notifications( $media_id, $user_id, $vote ) {

		$media = mpp_get_media( $media_id );

		if ( is_null( $media ) || ! buddypress() || ! bp_is_active( 'notifications' ) ) {
			return;
		}

		$bp = buddypress();

		$notification_id = bp_notifications_add_notification( array(
			'item_id'           => $media->id,
			'user_id'           => $media->user_id,
			'component_name'    => $bp->mpp_rating_notifier->id,
			'component_action'  => 'new_mpp_ratings_' . $media->id,
			'secondary_item_id' => $user_id
		) );

		if ( $notification_id ) {
			bp_notifications_add_meta( $notification_id, '_user_vote', $vote );
		}

		return;
	}

	public function setup_globals() {

		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		$bp = buddypress();

		$bp->mpp_rating_notifier                               = new stdClass();
		$bp->mpp_rating_notifier->id                           = 'mpp_rating_notifier';
		$bp->mpp_rating_notifier->slug                         = BP_MPP_RATING_NOTIFIER_SLUG;
		$bp->mpp_rating_notifier->notification_callback        = array( $this, 'format_notifications' );
		$bp->active_components[ $bp->mpp_rating_notifier->id ] = 1;// $bp->mpp_rating_notifier->id;

	}

	public function format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string', $notification_id ) {


		if ( $action != 'new_mpp_ratings_' . $item_id ) {
			return;
		}

		//$media = mpp_get_media( $item_id );

		$vote_given = bp_notifications_get_meta( $notification_id, '_user_vote' );

		$name = bp_core_get_user_displayname( $secondary_item_id );

		$name = ( $name ) ? $name : __( 'Anonymous User', 'mpp-media-rating' );

		$link  = mpp_get_media_permalink( $item_id );
		$title = mpp_get_media_title( $item_id );

		$text = sprintf( __( '<a href="%s"> %s has rated %d star on %s </a>', 'buddy-wall' ), $link, $name, $vote_given, $title );

		return $text;
	}

	public function mark_notification_read() {

		if ( ! is_user_logged_in() || ! mpp_is_single_media() ) {
			return;
		}

		$bp = buddypress();

		$component_action = 'new_mpp_ratings_' . mpp_get_current_media_id();

		bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), $bp->mpp_rating_notifier->id, $component_action, false );

	}

	public function clear_all_media_notification( $item_id ) {

		$bp = buddypress();

		$component_action = 'new_mpp_ratings_' . $item_id;

		bp_notifications_delete_notifications_by_item_id( false, $item_id, $bp->mpp_rating_notifier->id, $component_action );

	}

}

new MPP_Rating_Notifications();

