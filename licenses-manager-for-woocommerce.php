<?php

/**
 * Plugin Name:             Licenses Manager for WooCommerce
 * Plugin URI:              https://quadlayers.com/products/woocommerce-license-manager/
 * Description:             Licenses Manager for WooCommerce is a complete solution for selling digital products on WooCommerce.
 * Version:                 2.4.2
 * Text Domain:             licenses-manager-for-woocommerce
 * Author:                  QuadLayers
 * Author URI:              https://quadlayers.com
 * License:                 GPLv3
 * Domain Path:             /languages
 * Request at least:        4.7
 * Tested up to:            6.6
 * Requires PHP:            5.6
 * WC requires at least:    4.0
 * WC tested up to:         9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'QLWLM_PLUGIN_VERSION', '2.4.2' );
define( 'QLWLM_PLUGIN_NAME', 'Licenses Manager for WooCommerce' );
define( 'QLWLM_PLUGIN_FILE', __FILE__ );
define( 'QLWLM_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'QLWLM_DOMAIN', 'qlwlm' );
define( 'QLWLM_PREFIX', QLWLM_DOMAIN );
define( 'QLWLM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'QLWLM_WORDPRESS_URL', 'https://wordpress.org/plugins/licenses-manager-for-woocommerce/' );
define( 'QLWLM_REVIEW_URL', 'https://wordpress.org/support/plugin/licenses-manager-for-woocommerce/reviews/?filter=5#new-post' );
define( 'QLWLM_DEMO_URL', 'https://quadlayers.com/woocommerce-license-manager/?utm_source=qlwlm_admin' );
define( 'QLWLM_DOCUMENTATION_URL', 'https://quadlayers.com/documentation/woocommerce-license-manager/?utm_source=qlwlm_admin' );
define( 'QLWLM_PURCHASE_URL', 'https://quadlayers.com/products/woocommerce-license-manager/?utm_source=qlwlm_admin' );
define( 'QLWLM_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=qlwlm_admin' );
define( 'QLWLM_GROUP_URL', 'https://www.facebook.com/groups/quadlayers' );
define( 'QLWLM_DEVELOPER', false );
define( 'QLWLM_PREMIUM_SELL_URL', 'https://quadlayers.com/products/woocommerce-license-manager/?utm_source=qlwlm_admin' );

/**
 * Load composer autoload
 */
require_once __DIR__ . '/vendor/autoload.php';
/**
 * Load vendor_packages packages
 */
require_once __DIR__ . '/vendor_packages/wp-i18n-map.php';
require_once __DIR__ . '/vendor_packages/wp-dashboard-widget-news.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-table-links.php';
require_once __DIR__ . '/vendor_packages/wp-notice-plugin-required.php';
require_once __DIR__ . '/vendor_packages/wp-notice-plugin-promote.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-feedback.php';
/**
 * Load plugin classes
 */
require_once __DIR__ . '/lib/class-plugin.php';
/**
 * Load plugin activation
 */
register_activation_hook(
	__FILE__,
	function () {
		do_action( 'qlwlm_activation' );
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		do_action( 'qlwlm_deactivation' );
	}
);

/**
 * Declarate compatibility with WooCommerce Custom Order Tables
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
