<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\Activation;

use QuadLayers\WLM\Api\Endpoints\Base;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;
use QuadLayers\WLM\Models\Order\Load as Model_Order;

/**
 * Activation_Create class
 */
class Create extends Base {

	/**
	 * REST route path
	 *
	 * @var string
	 */
	protected static $route_path = 'activation';

	/**
	 * REST callback function
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return array
	 */
	public static function callback( \WP_REST_Request $request ) {

		$license_key     = $request->get_param( 'license_key' );
		$activation_site = $request->get_param( 'activation_site' );
		$license_email   = $request->get_param( 'license_email' );
		$product_key     = $request->get_param( 'product_key' );

		$license = Model_License_Mapper::get(
			array(
				'license_key' => $license_key,
			)
		);

		if ( ! $license ) {
			return array(
				'error'   => 1,
				// Translators: The license is invalid.
				'message' => sprintf( esc_html__( 'The license %s is invalid.', 'licenses-manager-for-woocommerce' ), $license_key ),
			);
		}

		if ( $license->get_license_email() && empty( $license_email ) ) {
			return array(
				'error'   => 1,
				// Translators: The license email is required.
				'message' => sprintf( esc_html__( 'The %s is required.', 'licenses-manager-for-woocommerce' ), 'license_email' ),
			);
		}

		/**
		 * Validate license product key and product
		 */

		if ( 'yes' === get_option( 'qlwlm_activation_validate_product', 'yes' ) ) {

			if ( empty( $product_key ) ) {
				return array(
					'error'   => 1,
					// Translators: The product key is required.
					'message' => sprintf( esc_html__( 'The %s is required.', 'licenses-manager-for-woocommerce' ), 'product_key' ),
				);
			}

			$product_id = Model_Product_License::get_product_id( $product_key );

			if ( ! $product_id ) {
				return array(
					'error'   => 1,
					// Translators: No products found for the product key.
					'message' => sprintf( esc_html__( 'No products found for the product_key %s.', 'licenses-manager-for-woocommerce' ), $product_key ),
				);
			}

			$parent_product_id = wp_get_post_parent_id( $license->get_product_id() );

			if ( $product_id !== $license->get_product_id() && $product_id !== $parent_product_id ) {
				return array(
					'error'   => 1,
					// Translators: The license does not match this product key.
					'message' => sprintf( esc_html__( 'The license %1$s doesn\'t match this product key.', 'licenses-manager-for-woocommerce' ), $license_key ),
				);
			}
		}

		/**
		 * Validate order status and email
		 */

		$model_order = new Model_Order( $license->get_order_id() );

		if ( ! $model_order->get_id() ) {
			return array(
				'error'   => 1,
				// Translators: The order id is not found.
				'message' => sprintf( esc_html__( 'The %1$s %2$s is not found.', 'licenses-manager-for-woocommerce' ), $model_order->get_label(), $license->get_order_id() ),
			);
		}

		if ( ! $model_order->is_active() ) {
			return array(
				'error'   => 1,
				// Translators: The order id matching this license.
				'message' => sprintf( esc_html__( 'The %1$s %2$s matching this license is %3$s.', 'licenses-manager-for-woocommerce' ), $model_order->get_label(), $model_order->get_id(), $model_order->get_status() ),
			);
		}

		if ( $license->get_license_email() && $license_email !== $model_order->get_email() ) {
			return array(
				'error'   => 1,
				// Translators: The email does not match the order email.
				'message' => sprintf( esc_html__( 'The email %1$s dosen\'t match the %2$s email.', 'licenses-manager-for-woocommerce' ), $model_order->get_label(), $license_email ),
			);
		}

		$activation = Model_Activation_Mapper::get(
			array(
				'activation_site' => $activation_site,
				'license_key'     => $license_key,
			)
		);

		if ( ! $activation ) {

			if ( $license->is_limit_reached() ) {
				return array(
					'error'   => 1,
					'message' => esc_html__( 'Remaining activations is equal to zero.', 'licenses-manager-for-woocommerce' ),
				);
			}

			$activation = Model_Activation_Mapper::create(
				$license->get_license_id(),
				$activation_site
			);

			if ( ! $activation ) {
				return array(
					'error'   => 1,
					'message' => esc_html__( 'Can\'t activate the license.', 'licenses-manager-for-woocommerce' ),
				);
			}
		}

		$activation_status = $activation->get_activation_status();
		if ( ! $activation_status ) {
			return array(
				'error'   => 1,
				// Translators: Your license have been banned for the site. Please contact site admin.
				'message' => sprintf( esc_html__( 'Your license %1$s have been banned for the site %2$s. Please contact site admin in %3$s.', 'licenses-manager-for-woocommerce' ), $license->get_license_key(), $activation_site, home_url() ),
			);
		}

		return array(
			'order_id'             => $activation->get_order_id(),
			'license_key'          => $activation->get_license_key(),
			'license_email'        => $activation->get_license_email(),
			'license_limit'        => $activation->get_license_limit(),
			'license_updates'      => $activation->get_license_updates(),
			'license_support'      => $activation->get_license_support(),
			'license_expiration'   => $activation->get_license_expiration(),
			'license_created'      => $activation->get_license_created(),
			'activation_limit'     => $activation->get_license_limit_status(),
			'activation_count'     => $activation->get_activation_count(),
			'activation_remaining' => $activation->get_license_activation_remaining(),
			'activation_instance'  => $activation->get_activation_instance(),
			'activation_status'    => $activation->get_activation_status(),
			'activation_site'      => $activation->get_activation_site(),
			'activation_created'   => $activation->get_activation_created(),
		);
	}

	/**
	 * Get REST arguments
	 *
	 * @return array
	 */
	public static function get_rest_args() {
		return array(
			'license_key'     => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
			'activation_site' => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {

					$param = trim( $param );
					$param = str_replace( ' ', '%20', $param );

					/**
					 * Check if the site is valid
					 */
					if ( strpos( $param, 'http' ) !== 0 ) {
						return false;
					}

					return true;
				},
			),
			'license_email'   => array(
				'type'              => 'string',
				'required'          => 'yes' === get_option( 'qlwlm_activation_validate_product', 'yes' ),
				'validate_callback' => function ( $param, $request, $key ) {
					return is_email( $param );
				},
			),
			'product_key'     => array(
				'type'              => 'string',
				'required'          => 'yes' === get_option( 'qlwlm_activation_validate_product', 'yes' ),
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
		);
	}

	/**
	 * Get REST method
	 *
	 * @return string
	 */
	public static function get_rest_method() {
		return \WP_REST_Server::EDITABLE;
	}
}
