<?php

class MPP_Media_Rating_Admin {

    public function __construct() {
        //setup hooks
	    add_action( 'mpp_admin_register_settings', array( $this, 'register_settings' ) );
    }

    /**
    * 
    * @param MPP_Admin_Settings_Page $page
    */
    
    public function register_settings( $page ) {

		$panel = $page->get_panel( 'addons' );

	    $rateable_components = mpp_rating_get_rateable_components();

	    $who_can_rate = mpp_rating_get_rating_permissions();

	    $rateable_types = array();
	    $active_types = mpp_get_active_types();

	    if ( ! empty( $active_types ) ) {
		    foreach ( $active_types as $type => $value ) {
			    $rateable_types[ $type ]  = $value->label;
		    }
	    }

		$fields = array(
			array(
				'name'		=> 'mpp-rating-rateable-components',
				'label'		=> __( 'Enabled for Components', 'mpp-media-rating' ),
				'type'		=> 'multicheck',
				'options'	=> $rateable_components
			),
			array(
				'name'		=> 'mpp-rating-rateable-types',
				'label'		=> __( 'Enabled for Types', 'mpp-media-rating' ),
				'type'		=> 'multicheck',
				'options'	=> $rateable_types
			),
			array(
				'name'		=> 'mpp-rating-required-permission',
				'label'		=> __( 'Who Can Rate', 'mpp-media-rating' ),
				'type'		=> 'radio',
				'options'	=> $who_can_rate
			),
			array(
				'name'		=> 'mpp-rating-appearance',
				'label'		=> __( 'Appearance', 'mpp-media-rating' ),
				'type'		=> 'multicheck',
				'options'	=> array(
					'single_media'  => __( 'Single Media Page', 'mpp-media-rating' ),
					'light_box'     => __( 'LightBox', 'mpp-media-rating' ),
					'single_gallery'=> __( 'Single Gallery', 'mpp-media-rating' )
				)
			)

		);
        
        $panel->add_section( 'rating-settings', __( 'Media Rating Setting', 'mpp-media-rating' ) )->add_fields( $fields );
        	
    }
}

new MPP_Media_Rating_Admin();

