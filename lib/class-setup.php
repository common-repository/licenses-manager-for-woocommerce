<?php

namespace QuadLayers\WLM;

use QuadLayers\WLM\Frontend\Controller\License;
use QuadLayers\WLM\Models\License\Setup as Model_License_Setup;
use QuadLayers\WLM\Models\Activation\Setup as Model_Activation_Setup;

class Setup {

	protected static $instance;

	public function __construct() {

		add_action(
			'qlwlm_activation',
			function () {
				Model_License_Setup::create_capability();
				Model_License_Setup::create_table();
				Model_Activation_Setup::create_table();
				License\View::add_endpoint();
				flush_rewrite_rules();
				wp_cache_flush();
			}
		);

		add_action(
			'qlwlm_deactivation',
			function () {
				flush_rewrite_rules();
				wp_cache_flush();
				if ( 'yes' == get_option( 'qlwlm_tools_data_delete_user_capability' ) ) {
					Model_License_Setup::delete_capability();
				}
			}
		);
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
