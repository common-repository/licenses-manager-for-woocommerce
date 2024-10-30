<?php

namespace QuadLayers\WLM\Backend\Controller\Products;

class Envato extends Base {

	protected static $instance;

	public static function add_tab( $tabs ) {

		$tabs['qlwlm_envato_license'] = array(
			'label'  => esc_html__( 'Envato', 'licenses-manager-for-woocommerce' ),
			'target' => 'qlwlm_envato_license_panel',
			'class'  => array( 'show_if_qlwlm' ),
		);

		return $tabs;
	}

	public static function add_tab_content() {

		global $thepostid, $product_object;

		?>
			<div id="qlwlm_envato_license_panel" class="panel woocommerce_options_panel" style="display: none;">
				<?php
				foreach ( self::get_fields( $thepostid ) as $field ) {
					self::add_setting_field( $field );
				}
				?>
			</div>
		<?php
	}

	public static function get_fields( $product_id = null ) {

		$product            = wc_get_product( $product_id );
		$product_variations = array();

		if ( $product && $product->is_type( 'variable' ) ) {
			$childrens = $product->get_children();
			if ( $childrens ) {
				foreach ( $childrens as $variation_id ) {
					$product_variations[ $variation_id ] = $variation_id;
				}
			}
		}

		return array(
			'start_group',
			array(
				'id'            => '_qlwlm_envato_item_id',
				'class'         => 'qlwlm-premium-field short wc_input_price v',
				'type'          => 'text',
				'label'         => esc_html__( 'Item Id', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Include the envato item id.', 'licenses-manager-for-woocommerce' ),
				'placeholder'   => esc_html__( '00000000', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => true,
				'wrapper_class' => 'show_if_simple show_if_variable show_if_qlwlm',
			),
			array(
				'id'            => '_qlwlm_envato_product_id',
				'class'         => 'qlwlm-premium-field short wc_input_price v',
				'type'          => 'select',
				'options'       => $product_variations,
				'label'         => esc_html__( 'Product Id', 'licenses-manager-for-woocommerce' ),
				'description'   => esc_html__( 'Select the variation product id.', 'licenses-manager-for-woocommerce' ),
				'placeholder'   => esc_html__( '00000000', 'licenses-manager-for-woocommerce' ),
				'desc_tip'      => true,
				'wrapper_class' => 'show_if_variable show_if_qlwlm',
			),
			array(
				'id'          => '_qlwlm_envato_license_email',
				'class'       => 'select short',
				'label'       => esc_html__( 'Email', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Require order email on license activation.', 'licenses-manager-for-woocommerce' ),
				'type'        => 'select',
				'options'     => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'    => true,
			),
			'end_group',
			'start_group',
			array(
				'id'          => '_qlwlm_envato_license_limit',
				'label'       => esc_html__( 'Activations Limit', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Limit amount of activations per license key.', 'licenses-manager-for-woocommerce' ),
				'placeholder' => esc_html__( 'Unlimited', 'licenses-manager-for-woocommerce' ),
				'type'        => 'number',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_envato_license_updates',
				'class'       => 'select short',
				'label'       => esc_html__( 'Automatic Updates Limit', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Limit automatic updates on license expiration.', 'licenses-manager-for-woocommerce' ),
				'type'        => 'select',
				'options'     => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_envato_license_support',
				'class'       => 'select short',
				'label'       => esc_html__( 'Support Limit', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Limit support on license expiration.', 'licenses-manager-for-woocommerce' ),
				'type'        => 'select',
				'options'     => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'    => true,
			),
			'end_group',
			'start_group',
			array(
				'id'          => '_qlwlm_envato_license_expiration_period',
				'label'       => esc_html__( 'Period', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'License expiration period.', 'licenses-manager-for-woocommerce' ),
				'placeholder' => esc_html__( 'Unlimited', 'licenses-manager-for-woocommerce' ),
				'type'        => 'number',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_envato_license_expiration_units',
				'class'       => 'select short',
				'label'       => esc_html__( 'Units', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'License expiration period unit.', 'licenses-manager-for-woocommerce' ),
				'type'        => 'select',
				'options'     => array(
					'days'   => esc_html__( 'Days', 'licenses-manager-for-woocommerce' ),
					'months' => esc_html__( 'Months', 'licenses-manager-for-woocommerce' ),
					'years'  => esc_html__( 'Years', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'    => true,
			),
			'end_group',
		);
	}

	public function __construct() {

		if ( 'yes' !== get_option( 'qlwlm_envato_license', 'no' ) ) {
			return;
		}

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
