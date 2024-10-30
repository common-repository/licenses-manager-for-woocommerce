<?php
namespace QuadLayers\WLM\Backend\Controller\Products;

use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class Software extends Base {

	protected static $instance;

	public static function add_tab( $tabs ) {

		$tabs['qlwlm_software'] = array(
			'label'  => esc_html__( 'Software', 'licenses-manager-for-woocommerce' ),
			'target' => 'qlwlm_software_panel',
			'class'  => array( 'show_if_qlwlm' ),
		);

		return $tabs;
	}

	public static function add_tab_content() {
		global $thepostid;

		$product_key        = get_post_meta( $thepostid, '_qlwlm_product_key', true );
		$product_secret_key = get_post_meta( $thepostid, '_qlwlm_secret_key', true );

		/* TODO: implement in product model */
		if ( ! $product_key && ! $product_secret_key ) {
			$product_key        = Model_Product_License::create_product_key();
			$product_secret_key = Model_Product_License::create_product_secret_key( $product_key );
			add_post_meta( $thepostid, '_qlwlm_product_key', $product_key, true );
			add_post_meta( $thepostid, '_qlwlm_secret_key', $product_secret_key, true );
		}

		?>
			<div id="qlwlm_software_panel" class="panel woocommerce_options_panel" style="display: none;">
				<p class="form-field _qlwlm_product_api_url_field ">
					<label for="_qlwlm_product_api_url">API URL</label>
					<span class="woocommerce-help-tip"></span>
					<input type="text" class="short" id="_qlwlm_product_api_url" value="<?php echo esc_url( home_url( '/wp-json/wc/wlm/' ) ); ?>" placeholder="<?php echo esc_html__( 'Copy and paste in your client', 'licenses-manager-for-woocommerce' ); ?>" readonly="1"> 
				</p>
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
				'id'                => '_qlwlm_product_key',
				'label'             => esc_html__( 'Product key', 'licenses-manager-for-woocommerce' ),
				'description'       => esc_html__( 'Public product key to use for API', 'licenses-manager-for-woocommerce' ),
				'type'              => 'text',
				'desc_tip'          => true,
				'custom_attributes' => array( 'readonly' => true ),
			),
			'end_group',
			'start_group',
			array(
				'id'                => '_qlwlm_secret_key',
				'class'             => 'qlwlm-field-password',
				'label'             => esc_html__( 'Secret key', 'licenses-manager-for-woocommerce' ),
				'description'       => esc_html__( 'Secret product key to use for API', 'licenses-manager-for-woocommerce' ),
				'type'              => 'password',
				'desc_tip'          => true,
				'custom_attributes' => array( 'readonly' => true ),
			),
			array(
				'id'          => '_qlwlm_version',
				'label'       => esc_html__( 'Version', 'licenses-manager-for-woocommerce' ),
				'description' => esc_html__( 'Version number for the software', 'licenses-manager-for-woocommerce' ),
				'placeholder' => esc_html__( 'e.g. 1.0', 'licenses-manager-for-woocommerce' ),
				'type'        => 'text',
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
