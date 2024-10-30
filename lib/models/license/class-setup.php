<?php

namespace QuadLayers\WLM\Models\License;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;

class Setup {

	public static function create_capability() {

		global $wp_roles;
		$roles = get_editable_roles();
		foreach ( $wp_roles->role_objects as $key => $role ) {
			if ( isset( $roles[ $key ] ) && $role->has_cap( 'edit_shop_orders' ) ) {
				$role->add_cap( 'qlwlm_edit_license' );
			}
		}
	}

	public static function delete_capability() {
		global $wp_roles;
		$roles = get_editable_roles();
		foreach ( $wp_roles->role_objects as $key => $role ) {
			if ( isset( $roles[ $key ] ) && $role->has_cap( 'qlwlm_edit_license' ) ) {
				$role->remove_cap( 'qlwlm_edit_license' );
			}
		}
	}

	public static function delete_table() {

		global $wpdb;

		$table = Model_License_Mapper::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare( 'DROP TABLE IF EXISTS %s', $table );
	}

	public static function create_table() {

		global $wpdb;

		$wpdb->hide_errors();

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$table = Model_License_Mapper::get_table();

		dbDelta(
			"CREATE TABLE {$table} (
	license_id bigint(20) NOT NULL auto_increment,
	order_id bigint(20) NOT NULL DEFAULT 0,
	product_id bigint(20) NOT NULL DEFAULT 0,
	license_key varchar(200) NOT NULL,
	license_email int(1) NOT NULL DEFAULT 1,
	license_updates int(1) NOT NULL DEFAULT 1,
	license_support int(1) NOT NULL DEFAULT 1,
	license_limit bigint(20) NOT NULL DEFAULT 0,
	activation_count bigint(20) NOT NULL DEFAULT 0,
	license_expiration DATETIME,
	license_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY  (license_id)
	) {$wpdb->get_charset_collate()};"
		);
	}
}
