<?php


namespace QuadLayers\WLM\Backend\Controller\Settings;

class Load {

	protected static $instance;

	public function __construct() {
		License::instance();
		Product::instance();
		Account::instance();
		Email::instance();
		Envato::instance();
		Tools::instance();
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
