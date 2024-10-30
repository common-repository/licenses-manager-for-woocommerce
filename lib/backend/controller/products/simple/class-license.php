<?php
namespace QuadLayers\WLM\Backend\Controller\Products\Simple;

use QuadLayers\WLM\Backend\Controller\Products\Base;

use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class License extends Base {

	protected static $instance;

	public static function add_tab( $tabs ) {

		$tabs['qlwlm_license'] = array(
			'label'  => esc_html__( 'License', 'licenses-manager-for-woocommerce' ),
			'target' => 'qlwlm_license_panel',
			'class'  => array( 'show_if_simple', 'show_if_subscription', 'show_if_qlwlm' ),
		);

		return $tabs;
	}

	public static function add_tab_content() {

		global $thepostid;

		?>
			<div id="qlwlm_license_panel" class="panel woocommerce_options_panel" style="display: none;">
				<?php
				foreach ( self::get_fields( $thepostid ) as $field ) {

					if ( ! empty( $field['wrapper_class'] ) ) {
						unset( $field['wrapper_class'] );
					}

					self::add_setting_field( $field );
				}
				?>
				</div>
			<?php
	}

	public static function get_fields( $product_id = null ) {

		$model_product = new Model_Product_License( $product_id );

		return array(
			array(
				'id' => '_qlwlm_upgrade_options',
			),
			'start_group',
			array(
				'id'       => '_qlwlm_product_data_license_nonce',
				'type'     => 'text',
				'value'    => wp_create_nonce( '_qlwlm_product_data_license_save_nonce' ),
				'desc_tip' => true,
				'type'     => 'hidden',
			),
			array(
				'id'            => '_qlwlm_license_prefix',
				'label'         => esc_html__( 'Prefix', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Optional prefix for generated license keys.', 'licenses-manager-for-woocommerce' ),
				'placeholder'   => esc_html__( 'N/A', 'licenses-manager-for-woocommerce' ),
				'type'          => 'text',
				'desc_tip'      => true,
				'wrapper_class' => 'form-field form-row',
			),
			array(
				'id'            => '_qlwlm_license_email',
				'class'         => 'select short',
				'label'         => esc_html__( 'Email', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Require order email on license activation.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'select',
				'options'       => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'      => true,
				'wrapper_class' => 'form-field form-row qlwlm-form-field-column-4',
			),
			'end_group',
			'start_group',
			array(
				'id'                => '_qlwlm_license_limit',
				'label'             => esc_html__( 'Activations Limit', 'licenses-manager-for-woocommerce' ),
				'description'       => esc_html__( 'Limit amount of activations per license key.', 'licenses-manager-for-woocommerce' ),
				'placeholder'       => esc_html__( 'Unlimited', 'licenses-manager-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0 ),
				'desc_tip'          => true,
				'wrapper_class'     => 'form-field form-row qlwlm-form-field-column-4',
			),
			array(
				'id'            => '_qlwlm_license_updates',
				'class'         => 'select short',
				'label'         => esc_html__( 'Automatic Updates Limit', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Limit automatic updates on license expiration.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'select',
				'options'       => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'      => true,
				'wrapper_class' => 'form-field form-row qlwlm-form-field-column-4',
			),
			array(
				'id'            => '_qlwlm_license_support',
				'class'         => 'select short',
				'label'         => esc_html__( 'Support Limit', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Limit support on license expiration.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'select',
				'options'       => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'      => true,
				'wrapper_class' => 'form-field form-row qlwlm-form-field-column-4',
			),
			'end_group',
			array(
				'type'  => 'start_group',
				'class' => 'hide_if_qlwlm_subscription',
			),
			array(
				'id'                => '_qlwlm_license_expiration_period',
				'label'             => esc_html__( 'Period', 'licenses-manager-for-woocommerce' ),
				'description'       => esc_html__( 'License expiration period.', 'licenses-manager-for-woocommerce' ),
				'placeholder'       => esc_html__( 'Unlimited', 'licenses-manager-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0 ),
				'desc_tip'          => true,
				'wrapper_class'     => 'hide_if_qlwlm_subscription form-field form-row qlwlm-form-field-column-2',
			),
			array(
				'id'            => '_qlwlm_license_expiration_units',
				'class'         => 'select short',
				'label'         => esc_html__( 'Units', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'License expiration period unit.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'select',
				'options'       => array(
					'days'   => esc_html__( 'Days', 'licenses-manager-for-woocommerce' ),
					'months' => esc_html__( 'Months', 'licenses-manager-for-woocommerce' ),
					'years'  => esc_html__( 'Years', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'      => true,
				'wrapper_class' => 'hide_if_qlwlm_subscription form-field form-row qlwlm-form-field-column-2',
			),
			'end_group',
			'start_group',
			array(
				'id'            => '_qlwlm_automatic_updates',
				'label'         => esc_html__( 'Automatic Updates File', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Select a file for the automatic updates.', 'licenses-manager-for-woocommerce' ),
				'type'          => 'select',
				'options'       => $model_product->get_downloads(),
				'desc_tip'      => true,
				'wrapper_class' => 'form-field form-row',
			),
			'end_group',
			'start_group',
			array(
				'id'                => '_qlwlm_renew_price',
				'class'             => 'qlwlm-premium-field short wc_input_price',
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 0 ),
				'label'             => sprintf( esc_html__( 'Renewal price (%s)', 'licenses-manager-for-woocommerce' ), get_woocommerce_currency_symbol() ),
				'description'       => esc_html__( 'This is the price to renew the license support period', 'licenses-manager-for-woocommerce' ),
				'placeholder'       => esc_html__( 'Renew license price', 'licenses-manager-for-woocommerce' ),
				'desc_tip'          => true,
				'position'          => 'left',
				'wrapper_class'     => 'form-field form-row show_if_simple show_if_variable show_if_qlwlm',
			),
			'end_group',
		);
	}

	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'add_tab_content' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
