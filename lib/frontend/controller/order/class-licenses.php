<?php

namespace QuadLayers\WLM\Frontend\Controller\Order;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Order\Load as Model_Order;

class Licenses {

	protected static $instance;

	function add_column( $columns ) {

		global $wp;

		if ( count( array_intersect_key( array_flip( array( 'downloads', 'order-received', 'view-order' ) ), $wp->query_vars ) ) ) {
			$columns['license'] = esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' );
		}

		return $columns;
	}

	function add_column_content( $download ) {

		if ( ! isset( $download['order_id'] ) ) {
			return;
		}

		?>
			<a class="woocommerce-button button view" href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $download['order_id'], wc_get_page_permalink( 'myaccount' ) ) ); ?>#licenses">#<?php echo esc_html( $download['order_id'] ); ?></a>
		<?php
	}

	function add_template( $order_id ) {

		if ( 'yes' !== get_option( 'qlwlm_account_orders_licenses', 'no' ) ) {
			return;
		}

		$model_order = new Model_Order( $order_id );

		if ( ! $model_order->is_qlwlm() ) {
			return;
		}

		$licenses = Model_License_Mapper::get(
			array(
				'order_id' => $model_order->get_id(),
			)
		);

		wc_get_template(
			'templates/order/licenses.php',
			array(
				'order_id' => $model_order->get_id(),
				'licenses' => $licenses,
			),
			'',
			QLWLM_PLUGIN_DIR
		);
	}

	public function __construct() {
		add_filter( 'woocommerce_account_downloads_columns', array( $this, 'add_column' ) );
		add_action( 'woocommerce_account_downloads_column_license', array( $this, 'add_column_content' ) );
		add_action( 'woocommerce_view_order', array( $this, 'add_template' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'add_template' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
