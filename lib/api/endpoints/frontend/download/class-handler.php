<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\Download;

use QuadLayers\WLM\Api\Endpoints\Base;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;

class Handler extends Base {

	protected static $route_path = 'download';

	public static function callback( \WP_REST_Request $request ) {

		$license_key         = $request->get_param( 'license_key' );
		$activation_instance = $request->get_param( 'activation_instance' );

		$activation = Model_Activation_Mapper::get(
			array(
				'activation_instance' => $activation_instance,
				'license_key'         => $license_key,
			)
		);

		if ( ! $activation ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'The activation instance provided is invalid.', 'licenses-manager-for-woocommerce' ),
			);
		}

		if ( ! $activation->get_activation_status() ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'Your site license %1$s have been banned for the site %2$s. Please contact site admin in %3$s.', 'licenses-manager-for-woocommerce' ), $activation->get_license_key(), $activation->get_activation_site(), home_url() ),
			);
		}

		if ( $activation->is_expired_updates() ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'The license has expired on %s.', 'licenses-manager-for-woocommerce' ), $activation->get_license_created_date() ),
			);
		}

		$model_product = new Model_Product_License( $activation->get_product_id() );

		if ( ! $model_product->is_qlwlm() ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'License doesn\'t match any product ID: %s.', 'licenses-manager-for-woocommerce' ), $activation->get_product_id() ),
			);
		}

		$download_file_path = $model_product->get_license_file_download_path();

		if ( ! $download_file_path ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'Can\'t find any file path for product id %s.', 'licenses-manager-for-woocommerce' ), $activation->get_product_id() ),
			);
		}

		if ( ! class_exists( 'WC_Download_Handler' ) ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'Can\'t find class %s.', 'licenses-manager-for-woocommerce' ), 'WC_Download_Handler' ),
			);
		}

		\WC_Download_Handler::download( $download_file_path, $model_product->get_id() );
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
