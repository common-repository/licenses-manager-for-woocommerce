<?php

namespace QuadLayers\WLM\Frontend;

class Load {

	protected static $instance;

	public function __construct() {
		Controller\License\View::instance();
		Controller\Order\Licenses::instance();
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
