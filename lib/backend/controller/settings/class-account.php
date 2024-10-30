<?php

namespace QuadLayers\WLM\Backend\Controller\Settings;

class Account {

	protected static $instance;
	protected static $id = 'account';

	function add_tab( $sections ) {

		$sections[ self::$id ] = esc_html__( 'Account', 'licenses-manager-for-woocommerce' );

		return $sections;
	}

	function add_fields( $settings ) {

		global $current_section;

		if ( self::$id !== $current_section ) {
			return $settings;
		}

		return array(
			array(
				'name' => esc_html__( 'Account', 'licenses-manager-for-woocommerce' ),
				'type' => 'title',
				'id'   => 'qlwlm_section_title',
			),
			// Orders
			array(
				'title'         => esc_html__( 'Orders', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Display licenses in user orders.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_account_orders_licenses',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			// Dashboard
			array(
				'title'         => esc_html__( 'Dashboard', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Display licenses in user dashboard.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_account_dashboard_licenses',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
				'class'         => 'qlwlm-premium-field',
			),
			// Licenses
			array(
				'title'         => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Display licenses tab in user account.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_account_licenses',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
				'class'         => 'qlwlm-premium-field',
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
