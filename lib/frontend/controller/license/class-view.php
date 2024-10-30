<?php

namespace QuadLayers\WLM\Frontend\Controller\License;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;

class View {

	protected static $instance;
	protected static $endpoint = 'view-license';

	public static function get_endpoint() {
		return self::$endpoint;
	}

	public static function add_endpoint() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}

	function add_endpoint_content( $license_key ) {

		$license = Model_License_Mapper::get(
			array(
				'license_key' => $license_key,
			)
		);

		$activations = null;

		if ( $license ) {

			$activations = Model_Activation_Mapper::get(
				array(
					'license_id' => $license->get_license_id(),
				)
			);
		}

		wc_get_template(
			'templates/license/view.php',
			array(
				'license'     => $license,
				'license_key' => $license_key,
				'activations' => $activations,
			),
			'',
			QLWLM_PLUGIN_DIR
		);
	}

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'add_endpoint' ) );
		add_action( 'woocommerce_account_view-license_endpoint', array( $this, 'add_endpoint_content' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
