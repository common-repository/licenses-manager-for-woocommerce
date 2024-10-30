<?php

namespace QuadLayers\WLM\Backend\Controller\Tables;

use QuadLayers\WLM\Backend\Controller\Tables\View\List_Table_Licenses;

class Licenses {

	protected static $instance;

	public $messages = array();

	function add_page() {

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		$qlwlm_licenses_admin = new List_Table_Licenses();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) );
			exit;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		/*
		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), isset( $_SERVER['REQUEST_URI'] ) ? esc_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '' ) );
			exit;
		} */

		$qlwlm_licenses_admin->prepare_items();
		?>
				<div id="<?php echo esc_attr( sanitize_key( __CLASS__ ) ); ?>" class="wrap post-type-shop_order">
					<h2><?php esc_html_e( 'Licenses Keys', 'licenses-manager-for-woocommerce' ); ?></h2>
					<form id="<?php echo esc_attr( sanitize_key( __CLASS__ ) ); ?>" method="get">
					<?php
					if ( $this->messages ) {
						echo '<div class="updated">';
						foreach ( $this->messages as $message ) {
							echo '<p>' . wp_kses_post( $message ) . '</p>';
						}
						echo '</div>';
					}
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$page = isset( $_REQUEST['page'] ) ? wp_unslash( $_REQUEST['page'] ) : '';
					?>
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
					<?php
						$qlwlm_licenses_admin->search_box( esc_html__( 'Search', 'licenses-manager-for-woocommerce' ), 's', esc_html__( 'Search by license key', 'licenses-manager-for-woocommerce' ) );
						$qlwlm_licenses_admin->display();
					?>
					</form>
				</div>
			<?php
	}

	function add_screen_options() {

		$option = 'per_page';

		$args = array(
			'label'   => esc_html__( 'Number of items per page', 'licenses-manager-for-woocommerce' ),
			'default' => 20,
			'option'  => 'licenses_per_page',
		);

		add_screen_option( $option, $args );
	}

	function save_screen_options( $status, $option, $value ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['qlwlm_licenses'] ) ) {
			return $value;
		}

		return $status;
	}

	function add_js() {
		wp_enqueue_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.min.js', array( 'jquery', 'selectWoo' ), WC_VERSION, true );
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
	}

	function menu() {
		$page = add_submenu_page( 'woocommerce', esc_html__( 'Licenses Keys', 'licenses-manager-for-woocommerce' ), esc_html__( 'Licenses Keys', 'licenses-manager-for-woocommerce' ), 'manage_woocommerce', 'qlwlm_licenses', array( $this, 'add_page' ) );
		add_action( "load-$page", array( $this, 'add_screen_options' ) );
		add_action( "load-$page", array( $this, 'add_js' ) );
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
