<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\License;

use QuadLayers\WLM\Api\Endpoints\Base;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class Delete extends Base {

	protected static $route_path = 'license';

	public static function callback( \WP_REST_Request $request ) {

		$product_key = $request->get_param( 'product_key' );
		$secret_key  = $request->get_param( 'secret_key' );
		$license_key = $request->get_param( 'license_key' );

		$is_valid = Model_Product_License::validate_secret_key( $product_key, $secret_key );

		if ( ! $is_valid ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'We can\'t validate your secret key %s.', 'licenses-manager-for-woocommerce' ), $secret_key ),
			);
		}

		$license = Model_License_Mapper::get(
			array(
				'license_key' => $license_key,
			)
		);

		if ( ! $license ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'The license %s is invalid.', 'licenses-manager-for-woocommerce' ), $license_key ),
			);
		}

		$status = Model_License_Mapper::delete(
			array(
				'license_key' => $license_key,
			)
		);

		if ( ! $status ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'We can\'t delete the license %s.', 'licenses-manager-for-woocommerce' ), $license_key ),
			);
		}
		return $license;
	}

	public static function get_rest_args() {
		return array(
			'license_key' => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
			'product_key' => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
			'secret_key'  => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
		);
	}

	public static function get_rest_method() {
		return \WP_REST_Server::DELETABLE;
	}
}
