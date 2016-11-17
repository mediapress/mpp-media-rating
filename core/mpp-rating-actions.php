<?php


class MPP_Media_Rating_Actions_Helper {

	private static $instance = null;

	private function __construct() {
		$this->setup();
	}

	public function setup() {
		//add to the media entry in the loop
		add_action( 'mpp_media_meta', array( $this, 'add_stars' ), 999 );
		//add to media entry in lightbox
		add_action( 'mpp_lightbox_media_meta', array( $this, 'add_lightbox_stars' ), 999 );
		//add_action( 'mpp_gallery_meta', array( $this, 'gallery_rating_star' ) );
		//add_action( 'mpp_lightbox_gallery_meta', array( $this, 'gallery_rating_star' ) );
		add_action( 'mpp_after_lightbox_media', array( $this, 'execute_script' ) );


	}

	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self ();
		}

		return self::$instance;

	}

	public function add_stars( $media = null ) {

		$media = mpp_get_media( $media );

		if ( is_null( $media ) || ! mpp_is_valid_media( $media->id ) ) {
			return;
		}

		$appearance = (array) mpp_get_option( 'mpp-rating-appearance' );

		if ( mpp_is_single_media() && in_array( 'single_media', $appearance ) ) {
			$this->add_interface( $media->id );
		} elseif ( mpp_is_single_gallery() && in_array( 'single_gallery', $appearance ) ) {
			$this->add_interface( $media->id );
		}

	}

	public function add_lightbox_stars( $media = null ) {

		$media = mpp_get_media( $media );

		if ( is_null( $media ) || ! mpp_is_valid_media( $media->id ) ) {
			return;
		}

		$appearance = (array) mpp_get_option( 'mpp-rating-appearance' );

		if ( in_array( 'light_box', $appearance ) ) {
			$this->add_interface( $media->id );
		}

	}

	public function add_interface( $media_id ) {

		if ( ! mpp_rating_is_media_rateable( $media_id ) ) {
			return;
		}

		echo mpp_rating_get_rating_html( $media_id, mpp_rating_is_read_only_media_rating( $media_id ) );
	}

	public function execute_script( $media = null ) {

		?>
		<script type="text/javascript">

			jQuery(".mpp-media-rating").rateit({resetable: false});

			jQuery('.mpp-media-rating').bind('rated', function (event, value) {

				var $this = jQuery(this),
					media_id = $this.attr('data-media-id');

				$this.rateit('readonly', true);

				var data = {
					action: 'mpp_rate_media',
					media_id: media_id,
					_nonce: _nonce,
					rating: value
				};

				jQuery.post(url, data, function (resp) {

					if (resp.type == 'error') {

					} else if (resp.type == 'success') {
						jQuery($this).rateit('value', resp.message.average_rating);
					}

				}, 'json');

			});

		</script>

		<?php
	}

}

MPP_Media_Rating_Actions_Helper::get_instance();

