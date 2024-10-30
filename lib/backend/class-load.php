<?php


namespace QuadLayers\WLM\Backend;

class Load {

	protected static $instance;

	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 50 );
		add_filter( 'woocommerce_sections_qlwlm', array( $this, 'add_tabs' ) );
		add_action( 'woocommerce_sections_qlwlm', array( $this, 'add_settings' ) );
		add_action( 'woocommerce_settings_save_qlwlm', array( $this, 'save_settings' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_premium_css' ) );

		Controller\Settings\Load::instance();
		Controller\Tables\Load::instance();
		Controller\Reports\Load::instance();
		Controller\Orders\License::instance();
		Controller\Orders\Table::instance();
		Controller\Products\Load::instance();
	}

	function enqueue_scripts() {

		$screen = get_current_screen();

		$backend = include QLWLM_PLUGIN_DIR . 'build/backend/js/index.asset.php';

		wp_register_style( 'qlwlm-admin', plugins_url( '/build/backend/css/style.css', QLWLM_PLUGIN_FILE ), false, QLWLM_PLUGIN_VERSION );

		wp_register_script( 'qlwlm-admin', plugins_url( '/build/backend/js/index.js', QLWLM_PLUGIN_FILE ), $backend['dependencies'], $backend['version'], false );

		//  phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
		if ( ! isset( $screen->id ) || ! in_array( $screen->id, array( 'product', 'edit-product', 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ), false ) ) {
			return;
		}

		wp_enqueue_script( 'qlwlm-admin' );
		wp_enqueue_style( 'qlwlm-admin' );
	}

	function add_tab( $settings_tabs ) {
		$settings_tabs[ QLWLM_PREFIX ] = esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' );
		return $settings_tabs;
	}

	function add_tabs( $sections = array() ) {

		global $current_section;

		$sections = apply_filters( 'qlwlm_backend_settings_section_tab', array() );

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $section ) {

			if ( is_string( $section ) ) {
				$label = $section;
				$link  = admin_url( 'admin.php?page=wc-settings&tab=qlwlm&section=' . sanitize_title( $id ) );
			} else {
				$label = $section['label'];
				$link  = $section['link'];
			}

			echo '<li><a href="' . esc_url( $link ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_attr( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	function add_settings() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	function get_settings() {

		$fields = apply_filters( 'qlwlm_backend_settings_section_fields', array() );

		return $fields;
	}

	public static function add_premium_css() {
		?>
		<style>
			.qlwlm-premium-field {
				opacity: 0.5;
				pointer-events: none;
			}
			.qlwlm-premium-field .description {
				display: block!important;
			}
		</style>
		<script>
			const fields = document.querySelectorAll('.qlwlm-premium-field')
			Array.from(fields).forEach((field)=> {
				field.closest('tr')?.classList.add('qlwlm-premium-field');
			})
		</script>
		<?php
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
