<?php

namespace QuadLayers\WLM\Backend\Controller\Settings;

class Tools {

	protected static $instance;
	protected static $id = 'tools';

	function add_tab( $sections ) {

		$sections[ self::$id ] = esc_html__( 'Tools', 'licenses-manager-for-woocommerce' );

		return $sections;
	}

	function add_fields( $settings ) {

		global $current_section;

		if ( self::$id !== $current_section ) {
			return $settings;
		}

		return array(
			array(
				'name' => esc_html__( 'Tools', 'licenses-manager-for-woocommerce' ),
				'type' => 'title',
				'id'   => 'qlwlm_section_title',
			),
			// Data
			array(
				'title'         => esc_html__( 'Data', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Delete licenses', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Delete licenses and activations on plugin uninstall.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_tools_data_delete_licenses',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			array(
				'desc'          => esc_html__( 'Delete product meta', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Delete product meta on plugin uninstall.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_tools_data_delete_product_meta',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),
			array(
				'desc'          => esc_html__( 'Delete user capability', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Delete edit user role capability on plugin uninstall.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_tools_data_delete_user_capability',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
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
