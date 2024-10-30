<?php

namespace QuadLayers\WLM\Api\Endpoints;

use QuadLayers\WLM\Api\Routes_Library;
use QuadLayers\WLM\Helpers;
use PinkCrab\WP_PSR16_Cache\File_Cache;

/**
 * Base class
 */
abstract class Base implements Route {

	/**
	 * REST route path
	 *
	 * @var string
	 */
	protected static $route_path = null;

	protected static $cache;

	/**
	 * Class constructor
	 */
	public function __construct() {

		register_rest_route(
			Routes_Library::get_namespace(),
			static::get_rest_path(),
			array(
				'args'                => static::get_rest_args(),
				'methods'             => static::get_rest_method(),
				'callback'            => array( static::class, 'callback' ),
				'permission_callback' => array( static::class, 'get_rest_permission' ),
			)
		);

		Routes_Library::instance()->register( $this );
	}

	/**
	 * Get Rest path and method
	 *
	 * @return string
	 */
	public static function get_name() {
		$path   = static::get_rest_path();
		$method = strtolower( static::get_rest_method() );
		return "$path/$method";
	}

	/**
	 * Get REST route path
	 *
	 * @return string
	 */
	public static function get_rest_path() {
		return static::$route_path;
	}

	/**
	 * Get REST args
	 *
	 * @return array
	 */
	public static function get_rest_args() {
		return array();
	}

	/**
	 * Get RESt URL
	 *
	 * @return string
	 */
	public static function get_rest_url() {

		$blog_id   = get_current_blog_id();
		$namespace = Routes_Library::get_namespace();
		$path      = static::get_rest_path();

		return get_rest_url( $blog_id, "{$namespace}/$path" );
	}

	/**
	 * Get REST permission
	 *
	 * @return boolean
	 */
	public static function get_rest_permission() {

		if ( 'yes' !== get_option( 'qlwlm_download_validate_user_agent', 'no' ) ) {
			return true;
		}

		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		if ( strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 'WLM' ) !== 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Get REST cache dir
	 *
	 * @return string
	 */
	protected static function get_rest_cache_dir() {

		$plugin_upload_dir = Helpers::get_plugin_upload_dir();

		$cache_dir = $plugin_upload_dir . '/cache/';

		Helpers::create_folder_path( $cache_dir );

		Helpers::secure_folder_path( $cache_dir );

		return $cache_dir . '/rest/';
	}

	public static function get_cache_engine() {

		if ( ! self::$cache ) {
			self::$cache = new File_Cache( self::get_rest_cache_dir() );
		}
		return self::$cache;
	}

	/**
	 * Get REST cache key
	 *
	 * @param array $args Arguments to build cache key.
	 * @return string
	 */
	public static function get_cache_key( array $args ) {

		$rest_path = self::get_rest_path();
		$rest_path = str_replace( '/', '-', $rest_path );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$serialized_args = serialize( $args );

		$md5_serialized_args = md5( $serialized_args );

		$cache_key = $rest_path . '-' . $md5_serialized_args;

		return $cache_key;
	}
}
