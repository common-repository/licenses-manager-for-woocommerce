<?php

namespace QuadLayers\WLM\Models\Product\Purchase;

use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;

class Actions {

	protected static $instance;

	function __construct() {
		add_action( 'qlwlm_order_completed_license_purchase', array( $this, 'process_order_licenses' ), 10, 3 );
		/* Fix duplicate product and ensure unique key and secret key */
		add_filter( 'sanitize_post_meta__qlwlm_product_key', array( $this, 'create_key' ) );
		add_filter( 'sanitize_post_meta__qlwlm_secret_key', array( $this, 'create_secret_key' ) );
	}

	function process_order_licenses( $model_order, $model_product, $item ) {

		$processed = false;

		for ( $i = 0; $i < $item->get_quantity(); $i++ ) {

			$license = Model_License_Mapper::create(
				array(
					'order_id'           => $model_order->get_id(),
					'product_id'         => $model_product->get_id(),
					'license_prefix'     => $model_product->get_license_prefix(),
					'license_limit'      => $model_product->get_license_limit(),
					'license_email'      => $model_product->get_license_email(),
					'license_updates'    => $model_product->get_license_updates(),
					'license_support'    => $model_product->get_license_support(),
					'_expiration_period' => $model_product->get_license_expiration_period(),
					'_expiration_units'  => $model_product->get_license_expiration_units(),
				)
			);

			if ( ! $license && 0 == $i ) {
				continue;
			}

			$item->add_meta_data( 'license_key', $license->get_license_key() );
			$item->save();

			$processed = true;
		}

		if ( $processed ) {
			$model_order->set_license();
			$model_order->save();
		}
	}

	function create_key( $meta_value ) {

		$product_id = Model_Product_License::get_product_id( $meta_value );

		if ( ! $product_id ) {
			return $meta_value;
		}

		$product_key = Model_Product_License::create_product_key();

		return $product_key;
	}

	function create_secret_key( $meta_value ) {

		$product_id = Model_Product_License::get_product_by_secret_key( $meta_value );

		if ( ! $product_id ) {
			return $meta_value;
		}

		$product_key = get_post_meta( $product_id, '_qlwlm_product_key', true );

		$product_secret_key = Model_Product_License::create_product_key( $product_key );

		return $product_secret_key;
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
