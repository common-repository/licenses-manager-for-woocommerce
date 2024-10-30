<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\Product;

use QuadLayers\WLM\Api\Endpoints\Base;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class Information extends Base {

	protected static $route_path = 'product/information';

	public static function callback( \WP_REST_Request $request ) {

		$product_key = $request->get_param( 'product_key' );

		/**
		 * TODO: remove satic methods and initialize class with product key
		 */
		$product_id = Model_Product_License::get_product_id( $product_key );

		$cache = self::get_cache_engine();

		$cache_key = self::get_cache_key(
			array(
				$product_key,
			)
		);

		if ( ! QLWLM_DEVELOPER ) {

			$cached_data = $cache->get( $cache_key, null );

			if ( $cached_data ) {
				/**
				 * TODO: remove
				 * Compatibility with older license client version
				 */
				$download_link = Update::callback( $request );
				return array_merge(
					$cached_data,
					array(
						'download_link' => $download_link,
					)
				);
			}
		}

		if ( ! $product_id ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'No products found for the product_key %s.', 'licenses-manager-for-woocommerce' ), $product_key ),
			);
		}

		$model_product = new Model_Product_License( $product_id );

		if ( ! $model_product->is_qlwlm() ) {
			return array(
				'error'   => 1,
				'message' => sprintf( esc_html__( 'No products found for the product_id %s.', 'licenses-manager-for-woocommerce' ), $product_id ),
			);
		}

		$data = array(
			'version'        => $model_product->get_wordpress_version(),
			'added'          => $model_product->get_date_created(),
			'name'           => $model_product->get_wordpress_name(),
			'requires'       => $model_product->get_wordpress_requires(),
			'tested'         => $model_product->get_wordpress_tested(),
			'author'         => $model_product->get_wordpress_author(),
			'last_updated'   => $model_product->get_wordpress_last_updated(),
			'banner_high'    => $model_product->get_wordpress_banner_high(),
			'banner_low'     => $model_product->get_wordpress_banner_low(),
			'icon'           => $model_product->get_wordpress_icon(),
			'homepage'       => $model_product->get_wordpress_homepage(),
			'upgrade_notice' => $model_product->get_wordpress_upgrade_notice(),
			'changelog'      => $model_product->get_wordpress_changelog(),
			'description'    => $model_product->get_wordpress_description(),
			'screenshots'    => $model_product->get_wordpress_screenshots(),
			// 'reviews' => Model_Product_License::get_product_reviews(),
			'product'        => $model_product->get_name(),
		);

		$cache->set(
			$cache_key,
			$data,
			1 * HOUR_IN_SECONDS
		);

		return $data;
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
		);
	}

	public static function get_rest_method() {
		return \WP_REST_Server::READABLE;
	}
}
