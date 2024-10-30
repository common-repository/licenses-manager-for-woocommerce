<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\License;

use QuadLayers\WLM\Api\Endpoints\Base;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\Order\Load as Model_Order;

class Create extends Base {

	protected static $route_path = 'license';

	public static function callback( \WP_REST_Request $request ) {

		$product_key = $request->get_param( 'product_key' );
		$secret_key  = $request->get_param( 'secret_key' );
		$product_id  = $request->get_param( 'product_id' );
		$order_id    = $request->get_param( 'order_id' );

		$model_product = new Model_Product_License( $product_id );

		$is_valid = $model_product::validate_secret_key( $product_key, $secret_key );

		if ( ! $is_valid ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'We can\'t validate your secret key with product key %s.', 'licenses-manager-for-woocommerce' ), $product_key ),
			);
		}

		if ( ! $model_product->is_qlwlm() ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'Can\'t find product ID: %s.', 'licenses-manager-for-woocommerce' ), $product_id ),
			);
		}

		$product_key_id = $model_product::get_product_id( $product_key );

		if ( ! $product_key_id ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'No products found for the product_key %s.', 'licenses-manager-for-woocommerce' ), $product_key ),
			);
		}

		$parent_product_id = wp_get_post_parent_id( $product_id );

		$product_type = $model_product->get_type();

		$is_simple_product_and_not_match_product_key_id   = in_array( $product_type, array( 'simple' ), true ) && $product_key_id != $product_id;
		$is_variable_product_and_not_match_product_key_id = in_array( $product_type, array( 'variable', 'variation' ), true ) && $product_key_id != $parent_product_id;

		if ( $is_simple_product_and_not_match_product_key_id || $is_variable_product_and_not_match_product_key_id ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'Product %1$s dosen\'t match the product key id %2$s.', 'licenses-manager-for-woocommerce' ), $product_id, $product_key_id ),
			);
		}

		/**
		 * Validate order_id
		 */

		if ( $order_id ) {
			$model_order = new Model_Order( $order_id );
			if ( ! $model_order->get_id() ) {
				return array(
					'error'   => 1,
					'message' => sprintf( esc_html__( 'Can\'t find order ID: %s.', 'licenses-manager-for-woocommerce' ), $order_id ),
				);
			}
			$model_order->set_license();
			$model_order->save();
			$order_id = $model_order->get_id();
		}

		$license = Model_License_Mapper::create(
			array(
				'product_id'         => $product_id,
				'order_id'           => $order_id,
				'license_prefix'     => $model_product->get_license_prefix(),
				'license_email'      => $model_product->get_license_email(),
				'license_updates'    => $model_product->get_license_updates(),
				'license_support'    => $model_product->get_license_support(),
				'license_limit'      => $model_product->get_license_limit(),
				'_expiration_period' => $model_product->get_license_expiration_period(),
				'_expiration_units'  => $model_product->get_license_expiration_units(),
			)
		);

		if ( ! $license ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'We can\'t create your license key.', 'licenses-manager-for-woocommerce' ),
			);
		}

		return array(
			'order_id'             => $license->get_order_id(),
			'license_key'          => $license->get_license_key(),
			'license_email'        => $license->get_license_email(),
			'license_limit'        => $license->get_license_limit(),
			'license_updates'      => $license->get_license_updates(),
			'license_support'      => $license->get_license_support(),
			'license_expiration'   => $license->get_license_expiration(),
			'license_created'      => $license->get_license_created(),
			'activation_count'     => $license->get_activation_count(),
			'activation_remaining' => $license->get_license_activation_remaining(),
			'product'              => $model_product->get_name(),
		);
	}

	public static function get_rest_args() {
		return array(
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
			'product_id'  => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_numeric( $param ) && ! empty( $param );
				},
			),
			'order_id'    => array(
				'type' => 'string',
			),
		);
	}

	public static function get_rest_method() {
		return \WP_REST_Server::EDITABLE;
	}
}
