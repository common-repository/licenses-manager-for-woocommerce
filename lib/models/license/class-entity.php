<?php

namespace QuadLayers\WLM\Models\License;

use QuadLayers\WLM\Frontend\Controller\License\View as LicenseView;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\Order\Load as Model_Order;

/**
 * QLWLM_License class
 */

class Entity {

	protected $license_id         = -1;
	public $order_id              = -1;
	public $order_subscription    = null;
	protected $product_id         = -1;
	protected $license_key        = '';
	protected $license_email      = 1;
	protected $license_updates    = 1;
	protected $license_support    = 1;
	protected $license_limit      = 0;
	protected $activation_count   = 0;
	protected $license_expiration = '';
	public $license_created       = '';
	protected $product            = null;

	function __construct( object $args ) {

		if ( ! is_object( $args ) ) {
			return;
		}

		foreach ( $args as $key => $value ) {
			if ( property_exists( $this, $key ) ) {

				$type = gettype( $this->$key );

				settype( $value, $type );

				$this->{$key} = $value;
			}
		}

		if ( $this->get_product_id() ) {
			$this->product = new Model_Product_License( $this->get_product_id() );
		}
	}

	public function get_property( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->{$key};
		}
	}

	/* Get product */

	public function get_product_id() {
		return $this->product_id;
	}

	public function get_product_name() {

		if ( ! $this->product ) {
			return esc_html__( 'N/A', 'licenses-manager-for-woocommerce' );
		}

		return $this->product->get_name();
	}

	public function get_license_expiration_period() {

		if ( ! $this->product ) {
			return;
		}

		return $this->product->get_license_expiration_period();
	}

	public function get_license_expiration_units() {

		if ( ! $this->product ) {
			return;
		}

		return $this->product->get_license_expiration_units();
	}

	/* Get license */

	public function get_license_id() {
		return $this->license_id;
	}

	public function get_license_key() {
		return $this->license_key;
	}

	public function get_license_email() {
		return $this->license_email;
	}

	public function get_license_updates() {
		return $this->license_updates;
	}

	public function get_license_support() {
		return $this->license_support;
	}

	public function get_license_limit() {
		return $this->license_limit;
	}

	public function get_activation_count() {
		return $this->activation_count;
	}

	public function get_license_expiration() {

		$license_expiration = '0000-00-00 00:00:00';

		$subscription = $this->get_order_subscription();

		if ( $subscription ) {
			$next_payment = $subscription->get_date( 'next_payment' );
			$end          = $subscription->get_date( 'end' );
			if ( $next_payment ) {
				$license_expiration = $next_payment;
			} elseif ( $end ) {
				$license_expiration = $end;
			}
		} else {
			$license_expiration = $this->license_expiration;
		}

		return $license_expiration;
	}

	public function get_license_created() {
		return $this->license_created;
	}

	/* Get order */

	public function get_order_id() {
		return $this->order_id;
	}

	/* Get limits */

	public function is_expired() {

		if ( $this->get_license_expiration() == '0000-00-00 00:00:00' ) {
			return false;
		}

		return strtotime( current_time( 'mysql' ) ) > strtotime( $this->get_license_expiration() );
	}

	public function is_expired_support() {

		if ( ! $this->license_support ) {
			return false;
		}

		if ( ! $this->is_expired() ) {
			return false;
		}

		return true;
	}

	public function is_expired_updates() {

		if ( ! $this->license_updates ) {
			return false;
		}

		if ( ! $this->is_expired() ) {
			return false;
		}

		return true;
	}

	public function is_limit_reached() {

		if ( ! $this->get_license_limit() ) {
			return false;
		}

		if ( ( $this->get_license_limit() - $this->get_activation_count() ) > 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Get status
	 */
	public function get_license_updates_status() {

		if ( $this->is_expired_updates() ) {
			return esc_html__( 'Expired', 'licenses-manager-for-woocommerce' );
		}

		return esc_html__( 'Active', 'licenses-manager-for-woocommerce' );
	}

	public function get_license_support_status() {

		if ( $this->is_expired_support() ) {
			return esc_html__( 'Expired', 'licenses-manager-for-woocommerce' );
		}

		return esc_html__( 'Active', 'licenses-manager-for-woocommerce' );
	}

	public function get_license_email_status() {

		if ( $this->license_email ) {
			return esc_html__( 'Required', 'licenses-manager-for-woocommerce' );
		}

		return esc_html__( 'No', 'licenses-manager-for-woocommerce' );
	}

	public function get_license_limit_status() {

		if ( ! $this->license_limit ) {
			return esc_html__( 'Unlimited', 'licenses-manager-for-woocommerce' );
		}

		return $this->license_limit;
	}

	public function get_license_activation_remaining() {

		$limit_status = $this->get_license_limit_status();

		if ( ! is_numeric( $limit_status ) ) {
			return $limit_status;
		}

		return $limit_status - $this->get_activation_count();
	}

	public function get_license_created_date() {
		return date_i18n( get_option( 'date_format' ), strtotime( $this->license_created ) );
	}

	public function get_license_expiration_date() {

		$license_expiration = $this->get_license_expiration();

		if ( '0000-00-00 00:00:00' == $license_expiration ) {
			return esc_html__( 'Never', 'licenses-manager-for-woocommerce' );
		}

		return date_i18n( get_option( 'date_format' ), strtotime( $license_expiration ) );
	}

	/**
	 * Get upsells
	 */
	public function get_license_renew_price() {

		if ( ! $this->product ) {
			return false;
		}

		$price = $this->product->get_license_renew_price();

		if ( ! $price ) {
			return false;
		}

		if ( get_option( 'qlwlm_license_renew', 'no' ) !== 'yes' ) {
			return false;
		}

		return $price;
	}

	public function get_license_renew_link() {

		$subscription = $this->get_order_subscription();

		if ( $subscription ) {

			$renew_link = wcs_get_early_renewal_url( $subscription );

			return $renew_link;
		}

		if ( ! $this->is_expired() ) {
			return false;
		}

		if ( ! $this->get_license_renew_price() ) {
			return;
		}

		return wc_get_endpoint_url( 'renew-license', $this->get_license_key(), get_permalink( $this->get_product_id() ) );
	}

	public function get_license_upgrade_options_siblings() {

		if ( ! $this->product ) {
			return false;
		}

		$upgrade_options = $this->product->get_license_upgrade_options_siblings();

		if ( empty( $upgrade_options ) ) {
			return false;
		}

		$options = array_filter(
			$upgrade_options,
			function ( $variation ) {
				return ! empty( $variation['active'] ) && ! ( ( empty( $variation['price'] ) && absint( $variation['price'] ) > 0 ) || ( empty( $variation['default_price'] ) && absint( $variation['default_price'] ) > 0 ) );
			}
		);

		if ( empty( $options ) ) {
			return false;
		}

		return $options;
	}

	public function get_license_upgrade_link() {

		$subscription = $this->get_order_subscription();

		if ( $subscription ) {
			$product = wc_get_product( $this->get_product_id() );
			// Check if the product is a variable subscription product
			if ( ! $product->is_type( 'subscription_variation' ) ) {
				return false;
			}

			// Iterate through subscription items.
			foreach ( $subscription->get_items() as $item_id => $item ) {
				// Get the switch URL for the item.
				$switch_url = \WC_Subscriptions_Switcher::get_switch_url( $item_id, $item, $subscription );

				if ( $switch_url ) {
					return $switch_url;
				}
			}

			return false;
		}

		if ( ! $this->get_license_upgrade_options_siblings() ) {
			return;
		}

		return wc_get_endpoint_url( 'upgrade-license', $this->get_license_key(), get_permalink( $this->get_product_id() ) );
	}

	public function get_license_view_link() {

		if ( ! $this->license_key ) {
			return;
		}

		$endpoint = LicenseView::get_endpoint();

		return wc_get_endpoint_url( $endpoint, $this->license_key, wc_get_page_permalink( 'myaccount' ) );
	}

	/**
	 * Get links
	 */
	public function get_product_link() {

		if ( $this->product ) {

			$product_id = $this->product->get_parent_id() ? $this->product->get_parent_id() : $this->product->get_id();

			if ( is_admin() ) {
				return '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . $this->product->get_name() . '</a>';
			}

			return '<a href="' . $this->product->get_permalink() . '">' . $this->product->get_name() . '</a>';
		}
	}

	public function get_order_link() {

		if ( is_admin() ) {
			return '<a href="' . admin_url( 'post.php?post=' . $this->get_order_id() . '&action=edit' ) . '">' . $this->get_order_id() . '</a>';
		}

		return '<a href="' . wc_get_endpoint_url( 'view-order', $this->get_order_id(), wc_get_page_permalink( 'myaccount' ) ) . '">' . $this->get_order_id() . '</a>';
	}

	public function get_license_key_link() {

		if ( is_admin() ) {
			return '<a href="' . admin_url( 'admin.php?page=qlwlm_activations&s=' . $this->license_key . '&action=edit' ) . '">' . $this->license_key . '</a>';
		}

		return sprintf( '<a href="%s">%s</a>', $this->get_license_view_link(), $this->license_key );
	}

	/* SUBSCRIPTION */

	public function is_subscription() {
		if ( ! $this->product ) {
			return false;
		}
		return $this->product->is_subscription();
	}

	/* ORDER */

	public function get_order_email() {

		if ( ! $this->order_id ) {
			return esc_html__( 'None', 'licenses-manager-for-woocommerce' );
		}

		$model_order = new Model_Order( $this->order_id );

		if ( ! $model_order->get_id() ) {
			return esc_html__( 'Order not found', 'licenses-manager-for-woocommerce' );
		}

		return $model_order->get_email();
	}

	public function get_license_activations() {

		return Model_Activation_Mapper::get(
			array(
				'license_id' => $this->license_id,
			)
		);
	}

	private function get_order_subscription() {

		if ( ! $this->order_id ) {
			return;
		}

		if ( ! $this->is_subscription() ) {
			return;
		}

		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return;
		}

		if ( $this->order_subscription !== null ) {
			return $this->order_subscription;
		}

		$subscription = wcs_get_subscription( $this->order_id );

		if ( $subscription ) {
			$this->order_subscription = $subscription;
		} else {
			$this->order_subscription = false;
		}

		return $this->order_subscription;
	}
}
