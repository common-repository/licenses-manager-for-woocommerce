<?php

namespace QuadLayers\WLM\Backend\Controller\Settings;

class Envato {

	protected static $instance;
	protected static $id = 'envato';

	function add_tab( $sections ) {

		$sections[ self::$id ] = esc_html__( 'Envato', 'licenses-manager-for-woocommerce' );

		return $sections;
	}

	function add_fields( $settings ) {

		global $current_section;

		if ( self::$id !== $current_section ) {
			return $settings;
		}

		return array(
			array(
				'name' => esc_html__( 'Envato', 'licenses-manager-for-woocommerce' ),
				'type' => 'title',
				'desc' => esc_html__( 'Validate Envato licenses', 'licenses-manager-for-woocommerce' ),
			),
			array(
				'id'       => 'qlwlm_envato_license',
				'class'    => 'qlwlm-premium-field chosen_select',
				'name'     => esc_html__( 'Validate', 'licenses-manager-for-woocommerce' ),
				'desc_tip' => esc_html__( 'Generate license when the license is validated', 'licenses-manager-for-woocommerce' ),
				'type'     => 'select',
				'options'  => array(
					'no'  => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
					'yes' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
				),
				'default'  => 'no',
			),
			array(
				'id'       => 'qlwlm_envato_api_key',
				'class'    => 'qlwlm-premium-field',
				'name'     => esc_html__( 'API Key', 'licenses-manager-for-woocommerce' ),
				'desc_tip' => esc_html__( 'Include Envato API key', 'licenses-manager-for-woocommerce' ),
				'type'     => 'text',
				'default'  => '',
			),
			array(
				'id'            => 'qlwlm_envato_license_created',
				'class'         => 'qlwlm-premium-field',
				'title'         => esc_html__( 'License', 'licenses-manager-for-woocommerce' ),
				'desc'          => esc_html__( 'Created', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Import the envato license creation date.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start',
				'autoload'      => false,
				'default'       => 'no',
			),
			array(
				'id'            => 'qlwlm_envato_license_expiration',
				'class'         => 'qlwlm-premium-field',
				'desc'          => esc_html__( 'Support Limit', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Import the envato license support expiration date.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => '',
				'autoload'      => false,
			),
			array(
				'id'            => 'qlwlm_envato_license_limit',
				'class'         => 'qlwlm-premium-field',
				'desc'          => esc_html__( 'Activations Limit', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => esc_html__( 'Import the envato license activations limit.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => '',
				'autoload'      => false,
			),
			array(
				'id'       => 'qlwlm_envato_order',
				'name'     => esc_html__( 'Order', 'licenses-manager-for-woocommerce' ),
				'class'    => 'qlwlm-premium-field chosen_select',
				'desc_tip' => esc_html__( 'Create order when the license is validated.', 'licenses-manager-for-woocommerce' ),
				'type'     => 'select',
				'options'  => array(
					'no'  => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
					'yes' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
				),
				'default'  => 'no',
			),
			array(
				'id'       => 'qlwlm_envato_order_status',
				'class'    => 'qlwlm-premium-field chosen_select',
				'name'     => esc_html__( 'Order status', 'licenses-manager-for-woocommerce' ),
				'type'     => 'select',
				'desc_tip' => esc_html__( 'Select the order status of the order created.', 'licenses-manager-for-woocommerce' ),
				'options'  => wc_get_order_statuses(),
				'default'  => 'wc-completed',
			),
			array(
				'id'       => 'qlwlm_envato_order_totals',
				'class'    => 'qlwlm-premium-field chosen_select',
				'name'     => esc_html__( 'Order totals', 'licenses-manager-for-woocommerce' ),
				'desc_tip' => esc_html__( 'This will include the total of the order and sum in the order reports.', 'licenses-manager-for-woocommerce' ),
				'type'     => 'select',
				'options'  => array(
					'no'  => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
					'yes' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
				),
				'default'  => 'no',
			),
			array(
				'id'       => 'qlwlm_envato_order_user',
				'class'    => 'qlwlm-premium-field chosen_select',
				'name'     => esc_html__( 'Order user', 'licenses-manager-for-woocommerce' ),
				'desc_tip' => esc_html__( 'This will create a user for the registrastion email and added to the order.', 'licenses-manager-for-woocommerce' ),
				'type'     => 'select',
				'options'  => array(
					'no'  => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
					'yes' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
				),
				'default'  => 'no',
			),
			array(
				'id'          => 'qlwlm_envato_order_market_name',
				'class'       => 'qlwlm-premium-field',
				'title'       => esc_html__( 'Market Name', 'licenses-manager-for-woocommerce' ),
				'desc_tip'    => esc_html__( 'This is the name of market that will be displayed on the market column of the order admin table.', 'licenses-manager-for-woocommerce' ),
				'type'        => 'text',
				'placeholder' => 'Envato',
				'default'     => 'Envato',
			),
			array(
				'type' => 'sectionend',
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
