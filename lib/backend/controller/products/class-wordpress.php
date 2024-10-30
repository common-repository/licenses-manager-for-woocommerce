<?php
namespace QuadLayers\WLM\Backend\Controller\Products;

// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledClassName
class Wordpress extends Base {

	protected static $instance;
	public $fields;

	public static function add_tab( $tabs ) {

		$tabs['qlwlm_wordpress'] = array(
			'label'  => esc_html__( 'WordPress', 'licenses-manager-for-woocommerce' ),
			'target' => 'qlwlm_wordpress_panel',
			'class'  => array( 'show_if_qlwlm' ),
		);

		return $tabs;
	}

	public static function add_tab_content() {

		global $thepostid, $product_object;
		?>
			<div id="qlwlm_wordpress_panel" class="panel woocommerce_options_panel" style="display: none;">
				<?php
				foreach ( self::get_fields( $thepostid ) as $field ) {
					self::add_setting_field( $field );
				}
				?>
			</div>			
		<?php
	}

	public static function get_fields( $product_id = null ) {
		return array(
			'start_group',
			array(
				'id'          => '_qlwlm_wordpress_name',
				'label'       => esc_html__( 'Name', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Plugin name for the plugin info', 'licenses-manager-for-woocommerce' ),
				'placeholder' => esc_html__( 'Plugin name', 'licenses-manager-for-woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
			),
			'end_group',
			'start_group',
			array(
				'id'          => '_qlwlm_wordpress_requires',
				'label'       => esc_html__( 'Requires', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Version number WordPress', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '4.5.0',
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_tested',
				'label'       => esc_html__( 'Tested', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Version number WordPress', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '5.0.0',
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_author',
				'label'       => esc_html__( 'Author', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Software Author', 'licenses-manager-for-woocommerce' ),
				'placeholder' => esc_html__( 'QuadLayers', 'licenses-manager-for-woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_last_updated',
				'class'       => 'qlwlm-field-datepicker',
				'label'       => esc_html__( 'Last updated', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Latest update date', 'licenses-manager-for-woocommerce' ),
				'placeholder' => current_time( 'mysql' ),
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_homepage',
				'label'       => esc_html__( 'Homepage', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Software Author', 'licenses-manager-for-woocommerce' ),
				'placeholder' => 'https://quadlayers.com',
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_icon',
				'class'       => 'qlwlm-field-upload',
				'label'       => esc_html__( 'Icon', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Plugin icon', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_banner_low',
				'class'       => 'qlwlm-field-upload',
				'label'       => esc_html__( 'Banner low', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Image size of 772x250', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_banner_high',
				'class'       => 'qlwlm-field-upload',
				'label'       => esc_html__( 'Banner high', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Image size of 1544x500', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
				'type'        => 'text',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_upgrade_notice',
				'label'       => esc_html__( 'Notice', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'A notice to display in the upgrade alert', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
				'type'        => 'textarea',
				'desc_tip'    => true,
			),
			'end_group',
			'start_group',
			array(
				'id'          => '_qlwlm_wordpress_description',
				'label'       => esc_html__( 'Description', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Plugin description', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
				'type'        => 'select',
				'options'     => array(
					'1' => esc_html__( 'Yes', 'licenses-manager-for-woocommerce' ),
					'0' => esc_html__( 'No', 'licenses-manager-for-woocommerce' ),
				),
				'desc_tip'    => true,
			),
			array(
				'id'          => '_qlwlm_wordpress_screenshots',
				'label'       => esc_html__( 'Screenshots', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Display screenshots tab with product galery images', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
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
				'id'          => '_qlwlm_wordpress_changelog',
				'label'       => esc_html__( 'Changelog', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Plugin changelog description', 'licenses-manager-for-woocommerce' ),
				'placeholder' => '',
				'rows'        => 10,
				'type'        => 'textarea',
				'desc_tip'    => true,
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
