<?php

class MPP_Media_Rating_Admin {
	
    private static $instance = null; 
	
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

	    $component_can_be_rated = mpp_rating_get_component_can_be_rated();

	    $who_can_rate = mpp_rating_get_who_can_rate();

	    $type_can_be_rated = array();

	    if ( ! empty( mpp_get_active_types() ) ) {

		    foreach ( mpp_get_active_types() as $type => $value ) {

			    $type_can_be_rated[$type]  = __( $value->label, 'mpp-media-rating' );
		    }
	    }

		$fields = array(
			array(
				'name'		=> 'component-can-be-rated',
				'label'		=> __( 'Select Component', 'mpp-media-rating' ),
				'type'		=> 'multicheck',
				'options'	=> $component_can_be_rated
			),
			array(
				'name'		=> 'type-can-be-rated',
				'label'		=> __( 'Select Type', 'mpp-media-rating' ),
				'type'		=> 'multicheck',
				'options'	=> $type_can_be_rated
			),
			array(
				'name'		=> 'who-can-rate',
				'label'		=> __( 'Who Can Rate', 'mpp-media-rating' ),
				'type'		=> 'radio',
				'options'	=> $who_can_rate
			),
			array(
				'name'		=> 'appearance',
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