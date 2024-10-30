<?php

namespace QuadLayers\WLM\Backend\Controller\Orders;

use QuadLayers\WLM\Models\Order\Load as Model_Order;

class Table {

	protected static $instance;

	function add_column( $columns ) {

		$columns['license'] = esc_html__( 'License', 'licenses-manager-for-woocommerce' );

		return $columns;
	}

	function add_column_content( $column, $post_id ) {

		if ( 'license' !== $column ) {
			return;
		}

		$model_order = new Model_Order( $post_id );

		if ( ! $model_order->has_license() ) {
			return;
		}

		printf( '<mark class="order-status %s tips" data-tip="%s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-completed' ) ), esc_html__( 'This is a license order.', 'licenses-manager-for-woocommerce' ), esc_html__( 'License', 'licenses-manager-for-woocommerce' ) );
	}

	public function __construct() {

		// List Legacy
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_column' ), 20 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_column_content' ), 20, 2 );

		// List HPOS
		add_action( 'woocommerce_shop_order_list_table_columns', array( $this, 'add_column' ), 20 );
		add_action( 'woocommerce_shop_order_list_table_custom_column', array( $this, 'add_column_content' ), 5, 2 );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
