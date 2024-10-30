<?php


namespace QuadLayers\WLM\Backend\Controller\Tables;

class Load {

	protected static $instance;

	public function __construct() {
		Licenses::instance();
		Activations::instance();
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
