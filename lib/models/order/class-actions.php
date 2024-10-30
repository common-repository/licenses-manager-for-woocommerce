<?php

namespace QuadLayers\WLM\Models\Order;

use QuadLayers\WLM\Models\Order\Load as Model_Order;

class Actions {

	protected static $instance;

	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'completed' ), 100, 2 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'refunded' ), 100, 2 );
		add_action( 'before_delete_post', array( $this, 'delete' ), 100, 2 );
	}

	public function completed( $order_id, $order ) {

		$model_order = new Model_Order( $order_id );

		$model_order->process_items();
	}

	public function refunded( $order_id, $order ) {

		$model_order = new Model_Order( $order_id );

		$model_order->delete_licenses();
	}

	public function delete( $post_id, $post ) {

		if ( 'shop_order' !== $post->post_type ) {
			return;
		}

		$model_order = new Model_Order( $post );

		$model_order->delete_licenses();
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
