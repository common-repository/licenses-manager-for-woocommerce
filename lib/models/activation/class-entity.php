<?php

namespace QuadLayers\WLM\Models\Activation;

use QuadLayers\WLM\Models\License\Entity as Model_License_Entity;

/**
 * QLWLM_Activation class
 */

class Entity extends Model_License_Entity {

	protected $activation_id       = -1;
	protected $license_id          = -1;
	protected $activation_instance = -1;
	protected $activation_status   = 0;
	protected $activation_site     = '';
	public $activation_created     = '';

	public function get_property( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->{$key};
		}
	}

	public function get_activation_id() {
		return $this->activation_id;
	}

	public function get_activation_instance() {
		return $this->activation_instance;
	}

	public function get_activation_status() {
		return $this->activation_status;
	}

	public function get_activation_site() {
		return $this->activation_site;
	}

	public function get_activation_created() {
		return $this->activation_created;
	}

	/**
	 * Get status
	 */
	public function get_activation_created_date() {
		return date_i18n( get_option( 'date_format' ), strtotime( $this->activation_created ) );
	}

	public function get_activation_status_status() {
		if ( ! $this->activation_status ) {
			return esc_html__( 'Disabled', 'licenses-manager-for-woocommerce' );
		}
		// If order_id is not set, it means that the license is not assigned to any order.
		if ( ! $this->order_id ) {
			if ( $this->activation_status ) {
				return esc_html__( 'Active', 'licenses-manager-for-woocommerce' );
			}
		}
		// If order_id is set and is a subscription, check if the subscription is active.
		if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $this->order_id ) ) {
			$subscription = wcs_get_subscription( $this->order_id );
			if ( $subscription && $subscription->has_status( 'active' ) ) {
				return esc_html__( 'Active', 'licenses-manager-for-woocommerce' );
			} else {
				return esc_html__( 'Disabled', 'licenses-manager-for-woocommerce' );
			}
		}
		// If order_id is set and is not a subscription, check if the order is completed.
		if ( $this->order_id ) {
			$order = wc_get_order( $this->order_id );
			if ( $order && $order->has_status( 'completed' ) ) {
				return esc_html__( 'Active', 'licenses-manager-for-woocommerce' );
			} else {
				return esc_html__( 'Disabled', 'licenses-manager-for-woocommerce' );
			}
		}
	}

	public function get_activation_site_link() {

		if ( $this->get_activation_site() ) {
			return '<a target="_blank" href="' . esc_url( $this->get_activation_site() ) . '">' . $this->get_activation_site() . '</a>';
		}
	}
}
