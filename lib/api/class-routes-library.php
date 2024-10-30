<?php

namespace QuadLayers\WLM\Api;

use QuadLayers\WLM\Api\Endpoints\Route;

class Routes_Library {

	protected static $instance;
	protected $routes = array();

	private static $rest_namespace = 'wc/wlm';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, '_rest_init' ) );
	}

	public static function get_namespace() {
		return self::$rest_namespace;
	}

	public function get_routes( $route_path = null ) {
		if ( ! $route_path ) {
			return $this->routes;
		}
		if ( isset( $this->routes[ $route_path ] ) ) {
			return $this->routes[ $route_path ];
		}
	}

	public function register( Route $instance ) {
		$this->routes[ $instance::get_name() ] = $instance;
	}

	public function _rest_init() {
		new Endpoints\Frontend\License\Create();
		new Endpoints\Frontend\License\Get();
		new Endpoints\Frontend\License\Delete();
		new Endpoints\Frontend\License\Reset();
		new Endpoints\Frontend\Activation\Create();
		new Endpoints\Frontend\Activation\Get();
		new Endpoints\Frontend\Activation\Delete();
		new Endpoints\Frontend\Product\Information();
		new Endpoints\Frontend\Product\Update();
		new Endpoints\Frontend\Download\Handler();
		/**
		 * TODO: delete this after 2.0.0
		 * Compatibility with the old route.
		 */
		new Endpoints\Frontend\Download\Product_Download();
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
