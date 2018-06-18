<?php
/**
 * Plugin shortcode file
 *
 * @package mpp-media-rating
 */

// Exit if file accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modify mediapress shortcode default array to list media
 *
 * @param array $default Array of supported attributes.
 *
 * @return array
 */
function mpp_rating_modify_default_args( $default ) {
	$default['top-rated'] = 0;
	return $default;
}

add_filter( 'mpp_shortcode_list_media_defaults', 'mpp_rating_modify_default_args' );

/**
 * Modify shortcode media query
 *
 * @param array $atts Array of attributes.
 *
 * @return array
 */
function mpp_rating_modify_media_args( $atts ) {

	if ( isset( $atts['top-rated'] ) && 1 == $atts['top-rated'] ) {
		$media_ids = mpp_rating_get_top_rated_media( array(
			'component'    => $atts['component'],
			'component_id' => $atts['component_id'],
			'status'       => $atts['status'],
			'type'         => $atts['type'],
		) );

		$atts['in'] = $media_ids;
		$atts['orderby'] = 'post__in';
	}

	return $atts;
}

add_filter( 'mpp_shortcode_list_media_query_args', 'mpp_rating_modify_media_args' );

/**
 * Add rating html in shortcode media list
 */
function mpp_rating_show_rating() {
	echo mpp_rating_get_rating_html( mpp_get_current_media_id(), 1 );
}

add_action( 'mpp_media_shortcode_item_meta', 'mpp_rating_show_rating' );
