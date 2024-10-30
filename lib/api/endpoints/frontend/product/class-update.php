<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\Product;

use QuadLayers\WLM\Api\Endpoints\Base;
use QuadLayers\WLM\Api\Endpoints\Frontend\Download\Handler as Download_Handler;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;

class Update extends Base {

	protected static $route_path = 'product/update';

	public static function callback( \WP_REST_Request $request ) {

		if ( empty( $request->get_param( 'license_key' ) ) ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'License key is not defined', 'licenses-manager-for-woocommerce' ),
			);
		}

		if ( empty( $request->get_param( 'activation_instance' ) ) ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'Activation instance is not defined', 'licenses-manager-for-woocommerce' ),
			);
		}

		$cache = self::get_cache_engine();

		$cache_key = self::get_cache_key(
			array(
				$request->get_param( 'license_key' ),
				$request->get_param( 'activation_instance' ),
			)
		);

		if ( ! QLWLM_DEVELOPER ) {

			$cached_data = $cache->get( $cache_key, null );

			if ( $cached_data ) {
				return $cached_data;
			}
		}

		$activation = Model_Activation_Mapper::get(
			array(
				'license_key'         => $request->get_param( 'license_key' ),
				'activation_instance' => $request->get_param( 'activation_instance' ),
			)
		);

		if ( ! $activation ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'The activation instance provided is invalid', 'licenses-manager-for-woocommerce' ),
			);
		}

		$model_product = new Model_Product_License( $activation->get_product_id() );

		if ( ! $model_product->is_qlwlm() ) {
			return sprintf( esc_html__( 'License doesn\'t match any product ID: %s', 'licenses-manager-for-woocommerce' ), $activation->get_product_id() );
		}

		$download_key = $model_product->get_license_automatic_updates();

		if ( ! $download_key ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'Automatic update file is not defined for product %s', 'licenses-manager-for-woocommerce' ), $activation->get_product_id() ),
			);
		}

		$rest_url = Download_Handler::get_rest_url();

		$params = http_build_query(
			array(
				'license_key'         => $activation->get_license_key(),
				'activation_instance' => $activation->get_activation_instance(),
			)
		);

		$builded_url = "{$rest_url}?{$params}";

		$cache->set(
			$cache_key,
			$builded_url,
			1 * HOUR_IN_SECONDS
		);

		return $builded_url;
	}

	public static function get_rest_args() {
		return array(
			'license_key'         => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
			'activation_instance' => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_numeric( $param ) && ! empty( $param );
				},
			),
		);
	}

	public static function get_rest_method() {
		return \WP_REST_Server::READABLE;
	}
}
