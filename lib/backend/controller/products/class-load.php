<?php
namespace QuadLayers\WLM\Backend\Controller\Products;

use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Api\Endpoints\Frontend\Product\Information;

class Load {

	protected static $instance;

	public function __construct() {
		add_action( 'woocommerce_product_options_product_type', array( __CLASS__, 'add_is_qlwlm' ) );
		add_filter( 'product_type_options', array( __CLASS__, 'add_is_qlwlm' ) );
		Simple\License::instance();
		Variable\License::instance();
		Software::instance();
		Wordpress::instance();
		Envato::instance();
		add_action( 'woocommerce_update_product', array( $this, 'mp_sync_on_product_save' ), 10, 1 );
	}

	public static function add_is_qlwlm( $options ) {

		$options['is_qlwlm'] = array(
			'id'            => '_is_qlwlm',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label'         => esc_html__( 'License', 'licenses-manager-for-woocommerce' ),
			'description'   => esc_html__( 'Enable this option if you want to manage license keys', 'licenses-manager-for-woocommerce' ),
		);

		return $options;
	}

	function mp_sync_on_product_save( $product_id ) {

		// $product      = wc_get_product( $product_id );
		// $product_data = $product->get_data();

		$model_product = new Model_Product_License( $product_id );

		$is_qlwlm = $model_product->is_qlwlm();

		if ( ! $is_qlwlm ) {
			return;
		}

		$product_key = $model_product->get_product_key();

		if ( ! $product_key ) {
			return;
		}

		$cache_key = Information::get_cache_key(
			array(
				$product_key,
			)
		);

		$cache_engine = Information::get_cache_engine();

		$cache_engine->delete( $cache_key );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
