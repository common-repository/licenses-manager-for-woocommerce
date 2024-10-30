<?php
namespace QuadLayers\WLM\Api\Endpoints;

interface Route {

	public static function callback( \WP_REST_Request $request );

	public static function get_name();

	public static function get_rest_args();

	public static function get_rest_path();

	public static function get_rest_method();

	public static function get_rest_permission();

	public static function get_rest_url();
}
