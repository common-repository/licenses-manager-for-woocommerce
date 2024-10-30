<?php

namespace QuadLayers\WLM\Backend\Controller\Settings;

class Product {

	protected static $instance;
	protected static $id = 'product';

	function add_tab( $sections ) {

		$sections[ self::$id ] = esc_html__( 'Product', 'licenses-manager-for-woocommerce' );

		return $sections;
	}

	function add_fields( $settings ) {

		global $current_section;

		if ( self::$id !== $current_section ) {
			return $settings;
		}

		return array(
			array(
				'name' => esc_html__( 'Product', 'licenses-manager-for-woocommerce' ),
				'type' => 'title',
				'id'   => 'qlwlm_section_title',
			),          // Licenses
			array(
				'title'         => esc_html__( 'Tab', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Display licenses in product tab.', 'licenses-manager-for-woocommerce' ),
				'id'            => 'qlwlm_product_tab_licenses',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
				'class'         => 'qlwlm-premium-field',
			),
			array(
				'desc'        => esc_html__( 'Licenses title.', 'woocommerce-checkout-manager' ),
				'desc_tip'    => esc_html__( 'Add custom title for the licenses tab.', 'licenses-manager-for-woocommerce' ),
				'id'          => 'qlwlm_product_tab_licenses_title',
				'type'        => 'text',
				'class'       => 'qlwlm-premium-field',
				'placeholder' => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
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
