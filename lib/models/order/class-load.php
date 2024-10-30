<?php

namespace QuadLayers\WLM\Models\Order;

use QuadLayers\WLM\Helpers;
use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class Load {

	protected static $instance;
	protected $model;

	public function __construct( $model ) {

		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$this->model = wc_get_order( $model );
	}

	public function __call( $method, $args ) {
		if ( method_exists( $this->model, $method ) ) {
			return call_user_func_array( array( $this->model, $method ), $args );
		}
	}

	/**
	 * Is qlwlm
	 */
	public function is_qlwlm() {

		if ( ! $this->model ) {
			return;
		}

		return (bool) $this->model->get_meta( '_qlwlm_processed', true );
	}

	public function set_processed() {

		if ( ! $this->model ) {
			return;
		}

		$this->model->add_meta_data( '_qlwlm_processed', true );
	}

	/**
	 * License
	 */
	public function has_license() {

		if ( ! $this->model ) {
			return;
		}

		return (bool) $this->model->get_meta( '_qlwlm_license', true );
	}

	public function set_license() {

		if ( ! $this->model ) {
			return;
		}

		$this->set_processed();
		$this->model->add_meta_data( '_qlwlm_license', true );
	}

	/**
	 * Renew
	 */
	public function has_license_renew() {

		if ( ! $this->model ) {
			return;
		}

		return (bool) $this->model->get_meta( '_qlwlm_renewed', true );
	}

	public function set_license_renew() {

		if ( ! $this->model ) {
			return;
		}

		$this->set_processed();
		$this->model->add_meta_data( '_qlwlm_renewed', true );
	}

	/**
	 * Upgrade
	 */
	public function has_license_upgrade() {

		if ( ! $this->model ) {
			return;
		}

		return (bool) $this->model->get_meta( '_qlwlm_upgraded', true );
	}

	public function set_license_upgrade() {

		if ( ! $this->model ) {
			return;
		}

		$this->model->add_meta_data( '_qlwlm_upgraded', true );
		$this->set_processed();
	}

	/**
	 * Envato
	 */
	public function has_license_envato() {

		if ( ! $this->model ) {
			return;
		}

		return (bool) $this->model->get_meta( '_qlwlm_envato', true );
	}

	public function set_envato_license() {

		if ( ! $this->model ) {
			return;
		}

		$this->model->add_meta_data( '_qlwlm_envato', true );
		$this->set_processed();
	}

	/**
	 * Save
	 */
	public function save() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->save();
	}

	/**
	 * Woocoommerce
	 */
	public function is_active() {

		if ( $this->is_subscription() ) {
			if ( in_array( $this->model->get_status(), array( 'active', 'pending-cancel' ), true ) ) {
				return true;
			}
			return false;
		}

		$order_status = get_option( 'qlwlm_activation_validate_order_status', 'wc-completed' );

		if ( '' === $order_status ) {
			return true;
		}

		if ( ! $this->is_qlwlm() ) {
			return false;
		}

		return $this->model->has_status( str_replace( 'wc-', '', $order_status ) );
	}

	public function get_email() {

		if ( ! $this->model ) {
			return false;
		}

		return $this->model->get_billing_email();
	}

	public function get_status() {

		if ( ! $this->model ) {
			return false;
		}

		return $this->model->get_status();
	}

	public function get_id() {

		if ( ! $this->model ) {
			return false;
		}

		return $this->model->get_id();
	}

	public function get_user_id() {

		if ( ! $this->model ) {
			return false;
		}

		return $this->model->get_user_id();
	}

	public function get_items() {

		if ( ! $this->model ) {
			return false;
		}

		$oder_items = $this->model->get_items();

		if ( ! $oder_items ) {
			return false;
		}

		$items = array();

		foreach ( $oder_items as $item ) {

			$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();

			$model_product = new Model_Product_License( $product_id );

			if ( ! $model_product->is_qlwlm() ) {
				continue;
			}

			$items[ $model_product->get_id() ] = $model_product;
		}

		return $items;
	}

	public function get_downloadable_items() {

		if ( ! $this->model ) {
			return false;
		}

		return $this->model->get_downloadable_items();
	}

	/**
	 * License
	 */
	public function process_items() {

		if ( ! $this->model ) {
			return;
		}

		if ( empty( $this->model->get_items() ) ) {
			return;
		}

		foreach ( $this->model->get_items() as $item_id => $item ) {

			$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();

			$model_product = new Model_Product_License( $product_id );

			if ( ! $model_product->is_qlwlm() ) {
				continue;
			}

			/**
			 * Skip subscription products
			 * This is handled by License Manager for WooCommerce PRO
			 */
			if ( $model_product->is_subscription() ) {
				continue;
			}

			$product_license_type = self::get_order_item_product_license_type( $item );

			switch ( $product_license_type ) {
				case 'renew':
					do_action( 'qlwlm_order_completed_license_renew', $this, $model_product, $item );
					break;
				case 'upgrade':
					do_action( 'qlwlm_order_completed_license_upgrade', $this, $model_product, $item );
					break;
				default:
				do_action( 'qlwlm_order_completed_license_purchase', $this, $model_product, $item );
					break;
			}
		}

		$this->save();
	}

	public function delete_licenses() {

		if ( ! $this->model || ! $this->model->get_meta( '_qlwlm_processed', true ) ) {
			return;
		}

		$licenses = Model_License_Mapper::delete(
			array(
				'order_id' => $this->model->get_id(),
			)
		);

		$this->model->delete_meta_data( '_qlwlm_license', true, true );
		$this->model->save();
	}

	public function get_licenses() {

		if ( ! $this->is_qlwlm() ) {
			return;
		}

		return Model_License_Mapper::get(
			array(
				'order_id' => $this->model->get_id(),
			)
		);
	}

	public static function get_order_item_product_license_type( $item ) {

		if ( $item->get_meta( 'license_key', true ) && $item->get_meta( 'license_renew', true ) ) {
			return 'renew';
		}

		if ( $item->get_meta( 'license_key', true ) && $item->get_meta( 'license_upgrade', true ) ) {
			return 'upgrade';
		}

		return 'purchase';
	}

	/**
	 * Check if this is a subscription
	 */
	public function is_subscription() {

		if ( function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $this->model->get_id() ) ) {
			return true;
		}
		return false;
	}

	public function get_label() {

		if ( $this->is_subscription() ) {
			return esc_html__( 'subscription', 'licenses-manager-for-woocommerce' );
		}

		return esc_html__( 'order', 'licenses-manager-for-woocommerce' );
	}
}
