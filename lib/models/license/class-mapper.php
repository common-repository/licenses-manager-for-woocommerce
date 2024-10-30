<?php

namespace QuadLayers\WLM\Models\License;

use QuadLayers\WLM\Models\Base;
use QuadLayers\WLM\Helpers;
use QuadLayers\WLM\Models\License\Entity as Model_License_Entity;
use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class Mapper extends Base {

	protected static $instance;
	protected static $table = 'woocommerce_qlwlm_licenses';

	public static function get_table() {
		global $wpdb;
		return $wpdb->prefix . self::$table;
	}

	protected static function db_from() {
		return sprintf( 'FROM %s as license', self::get_table() );
	}

	public static function get_months() {
		global $wpdb;

		$from = self::db_from();

		$query = "SELECT DISTINCT YEAR( license_created ) AS year, MONTH( license_created ) AS month $from ORDER BY license.license_created DESC";

		$query_cache_key = self::get_cache_key( $query );

		$query_cache_results = wp_cache_get( $query_cache_key );

		if ( false !== $query_cache_results ) {
			return $query_cache_results;
		}

		$results = $wpdb->get_results( $query );

		wp_cache_set( $query_cache_key, $results );

		return $results;
	}

	public static function build_query( array $args ) {

		global $wpdb;

		$where = array();

		$defaults = array(
			'per_page'           => 100,
			'current_page'       => 1,
			'orderby'            => 'license_created',
			'order'              => 'DESC',
			'product_id'         => null,
			'product_ids'        => null,
			'order_id'           => null,
			'customer_id'        => null,
			'license_key_search' => null,
			'license_status'     => null,
			'license_date_start' => null,
			'license_date_end'   => null,
			'license_key'        => null,
			'license_id'         => null,
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		/**
		 * Get by license_id
		 */

		if ( ! empty( $license_id ) ) {
			return $wpdb->prepare( 'license.license_id = %s', $license_id );
		}

		/**
		 * Get by license_key
		 */

		if ( ! empty( $license_key ) ) {
			return $wpdb->prepare( 'license.license_key = %s', $license_key );
		}

		/**
		 * Select by license_ids
		 */

		if ( ! empty( $license_ids ) && is_array( $license_ids ) ) {
			$license_ids = implode( "', '", $license_ids );
			return sprintf( 'license.license_id IN(\'%s\')', $license_ids );
		}

		if ( ! empty( $license_key_search ) ) {
			$where[] = $wpdb->prepare( 'license.license_key LIKE %s', "%{$license_key_search}%" );
		}

		if ( ! empty( $license_status ) && $license_status == 'active' ) {
			$where[] = $wpdb->prepare( 'license.license_expiration > %s OR license.license_expiration LIKE %s', current_time( 'mysql' ), '0000-00-00 00:00:00' );
		}

		if ( ! empty( $license_status ) && $license_status == 'expired' ) {
			$where[] = $wpdb->prepare( 'license.license_expiration < %s AND license.license_expiration NOT LIKE %s', current_time( 'mysql' ), '0000-00-00 00:00:00' );
		}

		if ( ! empty( $order_id ) ) {
			$where[] = $wpdb->prepare( 'license.order_id = %s', $order_id );
		}

		if ( ! empty( $license_date_start ) ) {
			$where[] = $wpdb->prepare( 'license.license_created >= %s', gmdate( 'Y-m-d H:i:s', $license_date_start ) );
		}

		if ( ! empty( $license_date_end ) ) {
			$where[] = $wpdb->prepare( 'license.license_created < %s', gmdate( 'Y-m-d H:i:s', $license_date_end ) );
		}

		if ( ! empty( $product_id ) ) {
			$model_product = new Model_Product_License( $product_id );
			if ( $model_product->is_qlwlm() ) {
				$product_ids = $model_product->get_children();
				if ( count( $product_ids ) ) {
					$product_id = implode( "', '", array_merge( $product_ids, (array) $product_id ) );
				}
				$where[] = sprintf( "license.product_id IN('%s')", $product_id );
			}
		}

		if ( ! empty( $product_ids ) && is_array( $product_ids ) ) {
			$product_ids = implode( "', '", $product_ids );
			$where[]     = sprintf( "license.product_id IN('%s')", $product_ids );
		}

		if ( ! empty( $customer_id ) ) {

			$orders = Helpers::get_customer_orders( $customer_id );

			if ( ! $orders ) {
				return false;
			}

			$where[] = sprintf( "license.order_id IN('%s')", implode( "', '", $orders ) );
		}

		return count( $where ) ? implode( ' AND ', $where ) : '';
	}

	public static function create_license_key( array $args ) {

		extract( $args );

		return sprintf( '%s%04x%04x-%04x-%04x-%04x-%04x%04x%04x', ! empty( $license_prefix ) ? sanitize_key( $license_prefix . '-' ) : '', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
	}

	public static function get_expiration_date( $period = null, $units = null, $current_time = null ) {

		if ( ! $period ) {
			return '';
		}

		if ( ! $units ) {
			return '';
		}

		if ( ! $current_time ) {
			$current_time = current_time( 'mysql' );
		}

		return gmdate( 'Y-m-d H:i:s', strtotime( "+{$period} {$units}", strtotime( $current_time ) ) );
	}

	public static function count( array $args ) {

		global $wpdb;

		$where = '';

		$query = self::build_query( $args );

		$from = self::db_from();

		if ( $query ) {
			$where = sprintf( 'WHERE %s', $query );
		}

		return $wpdb->get_var( sprintf( 'SELECT COUNT(license_id) %s %s', $from, $where ) );
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
			'per_page'     => 100,
			'current_page' => 1,
			'orderby'      => 'license_created',
			'order'        => 'DESC',
			'license_key'  => null,
			'license_id'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		/**
		 * If we have a license key or license ID, we don't need to do any pagination or ordering.
		 */
		if ( null !== $license_id || null !== $license_key ) {

			if ( ! $license_id && ! $license_key ) {
				return;
			}

			$query = sprintf( 'SELECT * %s %s LIMIT 1', $from, $where );

			$query_cache_key = self::get_cache_key( $query );

			$query_cache_result = wp_cache_get( $query_cache_key );

			if ( false !== $query_cache_result ) {
				return new Model_License_Entity( $query_cache_result );
			}

			$result = $wpdb->get_row( $query );

			if ( ! $result || ! is_object( $result ) ) {
				return;
			}

			wp_cache_set( $query_cache_key, $result );

			return new Model_License_Entity( $result );
		}

		/**
		 * If we have an orderby or order, we need to do some ordering.
		 */
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
					if ( ! $args || ! is_object( $args ) ) {
						return;
					}
					return new Model_License_Entity( $args );
				},
				$query_cache_results
			);
		}

		$results = $wpdb->get_results( $query );

		if ( ! $results ) {
			return;
		}

		wp_cache_set( $query_cache_key, $results );

		return array_map(
			function ( $args ) {
				if ( ! $args || ! is_object( $args ) ) {
					return;
				}
				return new Model_License_Entity( $args );
			},
			$results
		);
	}

	public static function update( array $data ) {

		global $wpdb;

		$table = self::get_table();

		$defaults = array(
			'license_id'         => null,
			'order_id'           => null,
			'product_id'         => null,
			'license_key'        => null,
			'license_email'      => null,
			'license_limit'      => null,
			'license_updates'    => null,
			'license_support'    => null,
			'license_expiration' => null,
			'license_created'    => null,
			'activation_count'   => null,
		);

		$data = array_filter(
			wp_parse_args( $data, $defaults ),
			function ( $value ) {
				if ( null !== $value ) {
					return true;
				}
			}
		);

		if ( ! $data['license_id'] ) {
			return;
		}

		return $wpdb->update( $table, $data, array( 'license_id' => $data['license_id'] ) );
	}

	public static function create( array $data ) {

		global $wpdb;

		$license_key = self::create_license_key( $data );

		$table = self::get_table();

		$current_time = current_time( 'mysql' );

		$defaults = array(
			'order_id'           => '',
			'product_id'         => '',
			'license_key'        => $license_key,
			'license_email'      => '',
			'license_limit'      => '',
			'license_updates'    => '',
			'license_support'    => '',
			'license_expiration' => '',
			'license_created'    => $current_time,
			'activation_count'   => 0,
		);

		if ( isset( $data['_expiration_period'], $data['_expiration_units'] ) ) {
			$data['license_expiration'] = self::get_expiration_date( $data['_expiration_period'], $data['_expiration_units'], $current_time );
		}

		$data = wp_parse_args( array_intersect_key( array_filter( $data ), $defaults ), $defaults );

		// if ($license = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE license_key = %s LIMIT 1", $data['license_key']))) {
		// return $license;
		// }

		$format = array(
			'%d',
			'%d',
			'%s',
			'%d',
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
		);

		$status = $wpdb->insert( $table, $data, $format );

		if ( ! $status ) {
			return;
		}

		$args = (object) wp_parse_args( $data, array( 'license_id' => $wpdb->insert_id ) );

		return new Model_License_Entity( $args );
	}

	public static function delete( array $args ) {

		global $wpdb;

		Model_Activation_Mapper::delete( $args );

		$table = self::get_table();

		$where = self::build_query( $args );

		$where = str_replace( 'license.', '', $where );

		$query = sprintf( 'DELETE from %s WHERE %s', $table, $where );

		return $wpdb->query( $query );
	}

	public static function update_activations( array $args ) {

		global $wpdb;

		$activations_count = Model_Activation_Mapper::count( $args );

		if ( ! is_numeric( $activations_count ) ) {
			return false;
		}

		$table = self::get_table();

		if ( false !== $wpdb->update( $table, array( 'activation_count' => $activations_count ), array( 'license_id' => $args['license_id'] ), array( '%d' ) ) ) {
			return true;
		}

		return false;
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
