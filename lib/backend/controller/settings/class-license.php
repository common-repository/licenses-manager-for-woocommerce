<?php

namespace QuadLayers\WLM\Backend\Controller\Settings;

class License {

	protected static $instance;
	protected static $id = '';

	function add_tab( $sections ) {

		$sections[ self::$id ] = esc_html__( 'License', 'licenses-manager-for-woocommerce' );

		return $sections;
	}

	function add_fields( $settings ) {

		global $current_section;

		if ( self::$id !== $current_section ) {
			return $settings;
		}

		return array(
			array(
				'name' => esc_html__( 'License', 'licenses-manager-for-woocommerce' ),
				'type' => 'title',
				'id'   => 'qlwlm_section_title',
			),
			// Create
			array(
				'name'     => esc_html__( 'Generation', 'licenses-manager-for-woocommerce' ),
				'desc'     => esc_html__( 'Order Status', 'licenses-manager-for-woocommerce' ),
				'desc_tip' => esc_html__( 'Create licenses when the order match this status', 'licenses-manager-for-woocommerce' ),
				'id'       => 'qlwlm_license_create_order_status',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => wc_get_order_statuses(),
				'default'  => 'wc-completed',
				'class'    => 'qlwlm-premium-field',
			),
			array(
				'desc'          => esc_html__( 'Renew', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Allow users to renew their licenses expiratio date. Make sure to include a renewal price.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_license_renew',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
				'class'         => 'qlwlm-premium-field',
			),
			array(
				'desc'          => esc_html__( 'Upgrade', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Allow users to upgrade their licenes limits. Upgrade price are calculated based on the difference between the variable products prices.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_license_upgrade',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'end',
				'autoload'      => false,
				'class'         => 'qlwlm-premium-field',
			),
			// Activation Validate
			array(
				'title'    => esc_html__( 'Validation', 'licenses-manager-for-woocommerce' ),
				'desc'     => esc_html__( 'Order Status', 'licenses-manager-for-woocommerce' ),
				'desc_tip' => esc_html__( 'If you disable this option, all licenses will be validated, regardless of the order status.', 'licenses-manager-for-woocommerce' ),
				'id'       => 'qlwlm_activation_validate_order_status',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array_merge(
					array(
						'' => esc_html__( 'All', 'licenses-manager-for-woocommerce' ),
					),
					wc_get_order_statuses()
				),
				'default'  => 'wc-completed',
			),
			array(
				'desc'          => esc_html__( 'Product', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'If you disable this option, all licenses will be validated, regardless of the product key on the client.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_activation_validate_product',
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			// Download
			array(
				'title'         => esc_html__( 'Automatic Updates File', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'License', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Automatic update require the license validation.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_download_validate_license',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			array(
				'desc'          => esc_html__( 'User Agent', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Automatic update require user agent validation.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_download_validate_user_agent',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'end',
				'autoload'      => false,
			),
			// User
			array(
				'title'         => esc_html__( 'User Permissions', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Deactivation', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Allow users to deactivate license activations.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_user_license_deactivation',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			array(
				'desc'          => esc_html__( 'Reset', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Allow users to reset their license activations.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_user_license_reset',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'end',
				'autoload'      => false,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'qlwlm_section_end',
			),
		);
	}

	public function __construct() {
		add_filter( 'qlwlm_backend_settings_section_tab', array( $this, 'add_tab' ) );
		add_filter( 'qlwlm_backend_settings_section_fields', array( $this, 'add_fields' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
