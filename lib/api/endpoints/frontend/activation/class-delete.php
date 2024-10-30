<?php
namespace QuadLayers\WLM\Api\Endpoints\Frontend\Activation;

use QuadLayers\WLM\Api\Endpoints\Base;

use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;

/**
 * Activation_Delete class
 */
class Delete extends Base {

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

		$license_key         = $request->get_param( 'license_key' );
		$activation_instance = $request->get_param( 'activation_instance' );

		$activation = Model_Activation_Mapper::get(
			array(
				'activation_instance' => $activation_instance,
				'license_key'         => $license_key,
			)
		);

		if ( ! $activation ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'The activation instance provided is invalid.', 'licenses-manager-for-woocommerce' ),
			);
		}
		if ( ! $activation->get_activation_status() ) {
			return array(
				'error'   => 1,
				// Translators: Your license have been banned for the site. Please contact site admin.
				'message' => sprintf( esc_html__( 'Your site license %1$s have been banned for the site %2$s. Please contact site admin in %3$s.', 'licenses-manager-for-woocommerce' ), $activation->get_license_key(), $activation->get_activation_site(), home_url() ),
			);
		}

		$status = Model_Activation_Mapper::delete(
			array(
				'activation_instance' => $activation_instance,
				'license_key'         => $license_key,
			)
		);

		if ( ! $status ) {
			return array(
				'error'   => 1,
				'message' => esc_html__( 'Unknown error.', 'licenses-manager-for-woocommerce' ),
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
			'license_key'         => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
			'activation_instance' => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_numeric( $param ) && ! empty( $param );
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
		return \WP_REST_Server::DELETABLE;
	}

	/**
	 * Get REST permission
	 *
	 * @return boolean
	 */
	public static function get_rest_permission() {

		if ( ! parent::get_rest_permission() ) {
			return false;
		}

		return 'yes' === get_option( 'qlwlm_user_license_deactivation', 'no' );
	}
}
