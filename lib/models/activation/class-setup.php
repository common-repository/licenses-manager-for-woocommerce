<?php

namespace QuadLayers\WLM\Models\Activation;

use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;

class Setup {

	public static function delete_table() {

		global $wpdb;

		$table = Model_Activation_Mapper::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare( 'DROP TABLE IF EXISTS %s', $table );
	}

	public static function create_table() {

		global $wpdb;

		$wpdb->hide_errors();

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$table = Model_Activation_Mapper::get_table();

		dbDelta(
			"CREATE TABLE {$table} (
	activation_id bigint(20) NOT NULL auto_increment,
	license_id bigint(20) NOT NULL,
	activation_instance bigint(10) DEFAULT NULL,
	activation_status int(1) NOT NULL DEFAULT 1,
	activation_site varchar(200) NULL,
	activation_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY  (activation_id)
  ) {$wpdb->get_charset_collate()};"
		);
	}
}
