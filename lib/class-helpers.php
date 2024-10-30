<?php

namespace QuadLayers\WLM;

class Helpers {

	protected static $instance;

	const ORDER_SCREENS = array( 'shop_order', 'woocommerce_page_wc-orders' );

	const SUBSCRIPTION_SCREENS = array( 'shop_subscription', 'woocommerce_page_wc-orders--shop_subscription' );

	public static function delete_plugin_uploads_dir() {

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		global $wp_filesystem;

		$plugin_upload_dir = self::get_plugin_upload_dir( false );

		$wp_filesystem->delete( $plugin_upload_dir, true );
	}

	public static function get_plugin_upload_dir( $create_if_not_exists = true ) {

		$upload_dir = wp_upload_dir()['basedir'];

		$plugin_basename = plugin_basename( __FILE__ );
		$plugin_slug     = explode( '/', $plugin_basename )[0];

		$plugin_upload_dir = $upload_dir . '/' . $plugin_slug;

		if ( $create_if_not_exists && ! is_dir( $plugin_upload_dir ) ) {
			self::create_folder_path( $plugin_upload_dir );
		}

		return $plugin_upload_dir;
	}

	public static function create_folder_path( $path ) {

		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
			return $path;
		}

		return $path;
	}

	public static function secure_folder_path( $path ) {

		global $wp_filesystem;
		/**
		 * WP_Filesystem() needs to be called before the file is created.
		 */
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$files = array(
			array(
				'file'    => 'index.php',
				'content' => array(
					'<?php',
					'// Silence is golden.',
				),
			),
			array(
				'file'    => '.htaccess',
				'content' => array(
					'Options -Indexes',
					'<ifModule mod_headers.c>',
					'   <Files *.*>',
					'       Header set Content-Disposition attachment',
					'   </Files>',
					'</IfModule>',
				),
			),
		);

		foreach ( $files as $file ) {
			if ( ! file_exists( trailingslashit( $path ) . $file['file'] ) ) {
					$content = implode( PHP_EOL, $file['content'] );
					$wp_filesystem->put_contents( trailingslashit( $path ) . $file['file'], $content );
			}
		}

		return $path;
	}


	public static function get_customer_orders( $customer_id ) {

		// Get order IDs for the customer.
		$order_ids = wc_get_orders(
			array(
				'customer_id' => $customer_id,
				'limit'       => -1,
				'return'      => 'ids',
			)
		);

		$subscription_ids = array();

		// Check if the WooCommerce Subscriptions extension is active.
		if ( class_exists( 'WC_Subscriptions' ) ) {

			$subscriptions = wcs_get_subscriptions(
				array(
					'customer_id' => $customer_id,
					'limit'       => -1,
					'return'      => 'ids',
				)
			);

			// Extract IDs from subscription objects.
			foreach ( $subscriptions as $subscription ) {
				$subscription_ids[] = $subscription->get_id();
			}
		}

		if ( $order_ids || $subscription_ids ) {
			return array_merge( (array) $order_ids, (array) $subscription_ids );
		}

		return array();
	}

	public static function array_reduce( array $array, callable $callback, $carry = null ) {
		foreach ( $array as $key => $value ) {
			$carry = $callback( $carry, $key, $value, $array );
		}
		return $carry;
	}
}
