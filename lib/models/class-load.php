<?php

namespace QuadLayers\WLM\Models;

class Load {

	protected static $instance;

	public function __construct() {
		Order\Actions::instance();
		Product\Purchase\Actions::instance();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
