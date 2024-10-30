<?php

namespace QuadLayers\WLM\Models\Activation;

use QuadLayers\WLM\Models\Base;
use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Activation\Entity as Model_Activation_Entity;

class Mapper extends Base {

	protected static $instance;
	protected static $table = 'woocommerce_qlwlm_activations';

	public static function get_table() {
		global $wpdb;
		return $wpdb->prefix . self::$table;
	}

	protected static function db_from() {
		return sprintf( 'FROM %s as activation LEFT JOIN %s as license ON activation.license_id = license.license_id', self::get_table(), Model_License_Mapper::get_table() );
	}

	public static function get_months() {
		global $wpdb;

		$table = self::get_table();

		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$query = $wpdb->prepare( 'SELECT DISTINCT YEAR( activation_created ) AS year, MONTH( activation_created ) AS month FROM %1s ORDER BY activation_created DESC', $table );

		$query_cache_key = self::get_cache_key( $query );

		$query_cache_results = wp_cache_get( $query_cache_key );

		if ( false !== $query_cache_results ) {
			return $query_cache_results;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $query );

		wp_cache_set( $query_cache_key, $results );

		return $results;
	}

	public static function build_query( array $args ) {

		global $wpdb;

		$where = array();

		$license_query = Model_License_Mapper::build_query( $args );

		if ( false === $license_query ) {
			return false;
		}

		if ( $license_query ) {
			$where[] = $license_query;
		}

		$defaults = array(
			'per_page'               => 100,
			'current_page'           => 1,
			'orderby'                => 'activation_created',
			'order'                  => 'DESC',
			'activation_ids'         => null,
			'activation_license_ids' => null,
			'activation_site_search' => null,
			'activation_site'        => null,
			'activation_instance'    => null,
			'activation_status'      => null,
			'activation_date_start'  => null,
			'activation_date_end'    => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		/**
		 * Select by activation_id
		 */

		if ( ! empty( $activation_ids ) && is_array( $activation_ids ) ) {
			$activation_ids = implode( "', '", $activation_ids );
			return sprintf( 'activation.activation_id IN(\'%s\')', $activation_ids );
		}

		/**
		 * Search by activation_site
		 */

		if ( ! empty( $activation_site_search ) ) {
			$where[] = $wpdb->prepare( 'activation.activation_site LIKE %s', "%{$activation_site_search}%" );
		}

		if ( ! empty( $activation_instance ) ) {
			$where[] = $wpdb->prepare( 'activation.activation_instance = %s', $activation_instance );
		}

		if ( ! empty( $activation_site ) ) {
			$where[] = $wpdb->prepare( 'activation.activation_site = %s', $activation_site );
		}

		/* TODO: remove activation_status, handle via license expiration time */
		if ( ! empty( $activation_status ) && 'active' == $activation_status ) {
			$where[] = $wpdb->prepare( 'activation.activation_status = %s', 1 );
		}

		if ( ! empty( $activation_status ) && 'inactive' == $activation_status ) {
			$where[] = $wpdb->prepare( 'activation.activation_status = %s', 0 );
		}

		if ( ! empty( $activation_date_start ) ) {
			$where[] = $wpdb->prepare( 'activation.activation_created >= %s', gmdate( 'Y-m-d H:i:s', $activation_date_start ) );
		}

		if ( ! empty( $activation_date_end ) ) {
			$where[] = $wpdb->prepare( 'activation.activation_created < %s', gmdate( 'Y-m-d H:i:s', $activation_date_end ) );
		}

		return count( $where ) ? implode( ' AND ', $where ) : '';
	}

	public static function get( array $args ) {

		global $wpdb;

		$where = '';
		$order = '';
		$limit = '';

		$from = self::db_from();

		$query = self::build_query( $args );

		if ( false === $query ) {
			return;
		}

		if ( $query ) {
			$where = sprintf( 'WHERE %s', $query );
		}

		$defaults = array(
			'per_page'            => 100,
			'current_page'        => 1,
			'orderby'             => 'activation_created',
			'order'               => 'DESC',
			'activation_instance' => null,
			'activation_site'     => null,
			'license_key'         => null,
			'license_id'          => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		if ( ( null !== $license_id || null !== $license_key ) && ( null !== $activation_instance || null !== $activation_site ) ) {

			if ( ! $license_id && ! $license_key && ! $activation_instance && ! $activation_site ) {
				return;
			}

			$query = sprintf( 'SELECT * %s %s LIMIT 1', $from, $where );

			$query_cache_key = self::get_cache_key( $query );

			$query_cache_results = wp_cache_get( $query_cache_key );

			if ( false !== $query_cache_results ) {
				return new Model_Activation_Entity( $query_cache_results );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			$results = $wpdb->get_row( $query );

			if ( ! $results || ! is_object( $results ) ) {
				return;
			}

			wp_cache_set( $query_cache_key, $results );

			return new Model_Activation_Entity( $results );
		}

		if ( $orderby || $order ) {
			$order = sprintf( 'ORDER BY %s %s', $orderby, $order );
		}

		if ( $per_page ) {
			$limit = sprintf( 'LIMIT %s, %s', ( $current_page - 1 ) * $per_page, $per_page );
		}

		$query = sprintf( 'SELECT * %s %s %s %s', $from, $where, $order, $limit );

		$query_cache_key = self::get_cache_key( $query );

		$query_cache_results = wp_cache_get( $query_cache_key );

		if ( false !== $query_cache_results ) {
			return array_map(
				function ( $args ) {
					return new Model_Activation_Entity( $args );
				},
				$query_cache_results
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $query );

		if ( ! $results || ! is_array( $results ) ) {
			return;
		}

		wp_cache_set( $query_cache_key, $results );

		return array_map(
			function ( $args ) {
				return new Model_Activation_Entity( $args );
			},
			$results
		);
	}

	public static function count( array $args ) {

		global $wpdb;

		$where = '';

		$query = self::build_query( $args );

		$from = self::db_from();

		if ( $query ) {
			$where = sprintf( 'WHERE %s', $query );
		}

		$query = sprintf( 'SELECT COUNT(activation_id) %s %s', $from, $where );

		$query_cache_key = self::get_cache_key( $query );

		$query_cache_results = wp_cache_get( $query_cache_key );

		if ( false !== $query_cache_results ) {
			return $query_cache_results;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_var( $query );

		wp_cache_set( $query_cache_key, $results );

		return $results;
	}

	public static function update( array $data ) {
	}

	public static function create( $license_id, $activation_site ) {

		global $wpdb;

		$activation_instance = time();
		$activation_created  = current_time( 'mysql' );

		$data = array(
			'license_id'          => $license_id,
			'activation_instance' => $activation_instance,
			'activation_status'   => 1,
			'activation_site'     => $activation_site,
			'activation_created'  => $activation_created,
		);

		$format = array(
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
		);

		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$status = $wpdb->insert( $table, $data, $format );

		if ( ! $status ) {
			return;
		}

		$status = Model_License_Mapper::update_activations(
			array(
				'license_id'        => $license_id,
				'activation_status' => 'active',
			)
		);

		if ( ! $status ) {
			return;
		}

		return self::get(
			array(
				'activation_instance' => $activation_instance,
				'license_id'          => $license_id,
			)
		);
	}

	public static function delete( array $args ) {

		global $wpdb;

		$activations = self::get( $args );

		if ( ! $activations ) {
			return true;
		}

		$table = self::get_table();

		if ( is_a( $activations, Model_Activation_Entity::class, false ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$status = $wpdb->delete(
				$table,
				array(
					'activation_instance' => $activations->get_activation_instance(),
				),
				array( '%d' )
			);
			if ( ! $status ) {
				return;
			}
			return Model_License_Mapper::update_activations(
				array(
					'license_id'        => $activations->get_license_id(),
					'activation_status' => 'active',
				)
			);
		}

		foreach ( $activations as $activation ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$delete = $wpdb->delete(
				$table,
				array(
					'activation_instance' => $activation->get_activation_instance(),
				),
				array( '%d' )
			);

			if ( ! $delete ) {
				continue;
			}

			$update = Model_License_Mapper::update_activations(
				array(
					'license_id'        => $activation->get_license_id(),
					'activation_status' => 'active',
				)
			);

		}

		return $activations;

		/**
		 * TODO: delete multiple
		 * $table = self::get_table();
		 * $delete = $wpdb->query( sprintf( "DELETE from $table WHERE %s", $where ) );
		 */
	}

	public static function toggle( $license_id, $activation_instance = null ) {

		global $wpdb;

		$table = self::get_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$status = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET activation_status = activation_status XOR 1 WHERE license_id = %s AND activation_instance = %s", $license_id, $activation_instance ) );

		if ( false !== $status ) {

			return Model_License_Mapper::update_activations(
				array(
					'license_id'        => $license_id,
					'activation_status' => 'active',
				)
			);

		}
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
