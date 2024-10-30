<?php

namespace QuadLayers\WLM\Backend\Controller\Orders;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\Order\Load as Model_Order;
use QuadLayers\WLM\Helpers;

class License {

	protected static $instance;

	public static function ajax_delete_license( $param ) {

		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'qlwlm_edit_license' ) ) {
			wp_die( -1 );
		}

		$license_id = intval( isset( $_POST['license_id'] ) ? $_POST['license_id'] : '' );
		$order_id   = intval( isset( $_POST['order_id'] ) ? $_POST['order_id'] : '' );

		try {

			$post = get_post( $order_id );

			if ( $post ) {

				$license = Model_License_Mapper::delete(
					array(
						'license_id' => $license_id,
					)
				);

				ob_start();

				self::add_meta_box_licenses( $post );

				wp_send_json_success( ob_get_clean() );
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	public static function ajax_create_license() {

		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'qlwlm_edit_license' ) ) {
			wp_die( -1 );
		}

		$order_id   = intval( isset( $_POST['order_id'] ) ? $_POST['order_id'] : '' );
		$product_id = intval( isset( $_POST['product_id'] ) ? $_POST['product_id'] : '' );

		try {
			$post = get_post( $order_id );

			if ( ! $post ) {
				return;
			}

			$model_product = new Model_Product_License( $product_id );

			if ( ! $model_product->is_qlwlm() ) {
				return;
			}

			Model_License_Mapper::create(
				array(
					'order_id'           => $order_id,
					'product_id'         => $product_id,
					'license_prefix'     => $model_product->get_license_prefix(),
					'license_email'      => $model_product->get_license_email(),
					'license_updates'    => $model_product->get_license_updates(),
					'license_support'    => $model_product->get_license_support(),
					'license_limit'      => $model_product->get_license_limit(),
					'_expiration_period' => $model_product->get_license_expiration_period(),
					'_expiration_units'  => $model_product->get_license_expiration_units(),
				)
			);

			if ( $order_id ) {
				$model_order = new Model_Order( $order_id );
				if ( $model_order->get_id() ) {
					$model_order->set_license();
					$model_order->save();
				}
			}

			ob_start();

			self::add_meta_box_licenses( $post );

			wp_send_json_success( ob_get_clean() );

		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	public static function ajax_toggle_license_activation() {

		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'qlwlm_edit_license' ) ) {
			wp_die( -1 );
		}

		$activation_instance = intval( isset( $_POST['activation_instance'] ) ? $_POST['activation_instance'] : '' );
		$license_id          = intval( isset( $_POST['license_id'] ) ? $_POST['license_id'] : '' );
		$order_id            = intval( isset( $_POST['order_id'] ) ? $_POST['order_id'] : '' );

		try {

			$post = get_post( $order_id );
			if ( $post ) {

				Model_Activation_Mapper::toggle( $license_id, $activation_instance );

				ob_start();

				self::add_meta_box_activations( $post );

				wp_send_json_success( ob_get_clean() );
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	public static function ajax_delete_license_activation() {

		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'qlwlm_edit_license' ) ) {
			wp_die( -1 );
		}

		$activation_instance = intval( isset( $_POST['activation_instance'] ) ? $_POST['activation_instance'] : '' );
		$license_id          = intval( isset( $_POST['license_id'] ) ? $_POST['license_id'] : '' );

		try {

			$activation = Model_Activation_Mapper::delete(
				array(
					'activation_instance' => $activation_instance,
					'license_id'          => $license_id,
				)
			);

			wp_send_json_success();

		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	public static function add_meta_box_licenses( $post ) {

		$licenses = Model_License_Mapper::get(
			array(
				'order_id' => $post->ID,
			)
		);

		if ( ! $licenses ) {
			return;
		}

		include 'view/html-order-licenses.php';
	}

	public static function add_meta_box_create( $post ) {

		$model_order = new Model_Order( $post );

		include 'view/html-order-create.php';
	}

	public static function add_meta_box_activations( $post ) {

		$activations = Model_Activation_Mapper::get(
			array(
				'order_id' => $post->ID,
			)
		);

		if ( ! $activations ) {
			return;
		}

		include 'view/html-order-activations.php';
	}

	public static function add_meta_boxes() {

		global $post;

		if ( ! isset( $post->ID ) && ! isset( $_REQUEST['id'] ) ) {
			return;
		}

		$model_order = new Model_Order( $post->ID ?? $_REQUEST['id'] );

		if ( ! $model_order->get_id() ) {
			return;
		}

		add_meta_box(
			'qlwlm_order_create',
			esc_html__( 'Create', 'licenses-manager-for-woocommerce' ),
			array( __CLASS__, 'add_meta_box_create' ),
			Helpers::ORDER_SCREENS,
			'normal',
			'high'
		);

		if ( ! $model_order->is_qlwlm() ) {
			return;
		}

		add_meta_box(
			'qlwlm_order_licenses',
			esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
			array( __CLASS__, 'add_meta_box_licenses' ),
			Helpers::ORDER_SCREENS,
			'normal',
			'high'
		);

		add_meta_box(
			'qlwlm_order_licenses_activations',
			esc_html__( 'Activations', 'licenses-manager-for-woocommerce' ),
			array( __CLASS__, 'add_meta_box_activations' ),
			Helpers::ORDER_SCREENS,
			'normal',
			'high'
		);
	}

	public function __construct() {
		add_action( 'wp_ajax_qlwlm_create_license', array( __CLASS__, 'ajax_create_license' ) );
		add_action( 'wp_ajax_qlwlm_delete_license', array( __CLASS__, 'ajax_delete_license' ) );
		add_action( 'wp_ajax_qlwlm_delete_license_activation', array( __CLASS__, 'ajax_delete_license_activation' ) );
		add_action( 'wp_ajax_qlwlm_toggle_license_activation', array( __CLASS__, 'ajax_toggle_license_activation' ) );
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {

				if ( ! in_array( $post_type, Helpers::ORDER_SCREENS, true ) ) {
					return;
				}

				self::add_meta_boxes();
			}
		);
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
