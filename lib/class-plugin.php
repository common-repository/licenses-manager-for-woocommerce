<?php

namespace QuadLayers\WLM;

final class Plugin {

	protected static $instance;
	protected static $menu_slug = 'licenses-manager-for-woocommerce';

	private function __construct() {
		/**
		 * Load plugin textdomain.
		 */
		load_plugin_textdomain( 'licenses-manager-for-woocommerce', false, QLWLM_PLUGIN_DIR . '/languages/' );
		/**
		 * Load plugin classes.
		 */
		Setup::instance();
		Upload::instance();

		add_action(
			'woocommerce_init',
			function () {
				Api\Routes_Library::instance();
				Models\Load::instance();
				Backend\Load::instance();
				Frontend\Load::instance();
				do_action( 'qlwlm_init' );
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

Plugin::instance();
