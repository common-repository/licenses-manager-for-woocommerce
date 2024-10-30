<?php

namespace QuadLayers\WLM\Models\Product\License;

use QuadLayers\WLM\Models\Base;
use QuadLayers\WLM\Helpers;

class Load extends Base {

	protected $model;

	function __construct( $model ) {

		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$this->model = wc_get_product( $model );
	}

	public function __call( $method, $args ) {
		if ( method_exists( $this->model, $method ) ) {
			return call_user_func_array( array( $this->model, $method ), $args );
		}
	}

	public static function get_table() {
		global $wpdb;
		return $wpdb->postmeta;
	}

	public static function get_product_id( $product_key ) {
		global $wpdb;

		$table = self::get_table();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->prepare( "SELECT post_id FROM {$table} WHERE meta_key = '_qlwlm_product_key' AND meta_value = %s LIMIT 1", $product_key );

		$query_cache_key = self::get_cache_key( $query );

		$query_cache_results = wp_cache_get( $query_cache_key );

		if ( false !== $query_cache_results ) {
			return $query_cache_results;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = (int) $wpdb->get_var( $query );

		wp_cache_set( $query_cache_key, $results );

		return $results;
	}

	public function get_product_key() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_meta( '_qlwlm_product_key', true );
	}

	public static function create_product_key() {
		$product_key = substr( str_shuffle( MD5( microtime() ) ), 0, 32 );

		return $product_key;
	}

	public static function get_product_by_secret_key( $product_secret_key ) {
		global $wpdb;

		$table = self::get_table();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->prepare( "SELECT post_id FROM {$table} WHERE meta_key = '_qlwlm_secret_key' AND meta_value = %s LIMIT 1", $product_secret_key );

		$query_cache_key = self::get_cache_key( $query );

		$query_cache_results = wp_cache_get( $query_cache_key );

		if ( false !== $query_cache_results ) {
			return $query_cache_results;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = (int) $wpdb->get_var( $query );

		wp_cache_set( $query_cache_key, $results );

		return $results;
	}

	public static function get_product_secret_key( $product_id ) {
		return get_post_meta( $product_id, '_qlwlm_secret_key', true );
	}

	public static function create_product_secret_key( $product_key ) {

		if ( ! $product_key ) {
			return;
		}

		$product_secret_key = hash_hmac( 'ripemd160', $product_key, wp_rand() );

		return $product_secret_key;
	}

	public static function validate_secret_key( $product_key, $validate_secret_key ) {

		$product_id = self::get_product_id( $product_key );

		if ( ! $product_id ) {
			return false;
		}

		$product_secret_key = self::get_product_secret_key( $product_id );

		if ( $product_secret_key !== $validate_secret_key ) {
			return false;
		}

		return $product_id;
	}

	public function is_qlwlm() {

		if ( ! $this->model ) {
			return;
		}

		return (bool) $this->model->get_meta( '_is_qlwlm', true );
	}

	public function get_license_prefix() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_meta( '_qlwlm_license_prefix', true );
	}

	public function get_license_limit() {

		if ( ! $this->model ) {
			return;
		}

		return absint( $this->model->get_meta( '_qlwlm_license_limit', true ) );
	}

	/**
	 * TODO: rename to is_email_required
	 */
	public function get_license_email() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_meta( '_qlwlm_license_email', true );
	}

	public function get_license_updates() {

		if ( ! $this->model ) {
			return;
		}
		return $this->model->get_meta( '_qlwlm_license_updates', true );
	}

	public function get_license_support() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_meta( '_qlwlm_license_support', true );
	}

	public function get_license_expiration_period() {

		if ( ! $this->model ) {
			return;
		}

		if ( $this->is_subscription() ) {
			return $this->model->get_meta( '_subscription_period_interval' );
		}

		return $this->model->get_meta( '_qlwlm_license_expiration_period', true );
	}

	public function get_license_expiration_units() {

		if ( ! $this->model ) {
			return;
		}

		if ( ! $this->model ) {
			return;
		}

		if ( $this->is_subscription() ) {
			return $this->model->get_meta( '_subscription_period' );
		}

		return $this->model->get_meta( '_qlwlm_license_expiration_units', true );
	}

	public function get_license_renew_price() {

		if ( ! $this->model ) {
			return;
		}

		return absint( $this->model->get_meta( '_qlwlm_renew_price', true ) );
	}


	public function get_license_extend_price() {

		if ( ! $this->model ) {
			return;
		}

		return absint( $this->model->get_meta( '_qlwlm_extend_price', true ) );
	}

	public function get_license_upgrade_options() {

		if ( ! $this->model ) {
			return;
		}

		if ( get_option( 'qlwlm_license_upgrade', 'no' ) !== 'yes' ) {
			return false;
		}

		return $this->model->get_meta( '_qlwlm_upgrade_options', true );
	}

	public function get_license_upgrade_options_siblings() {

		$siblings = $this->get_siblings();

		if ( ! $siblings ) {
			return;
		}

		$upgrade_options = $this->get_license_upgrade_options();

		return Helpers::array_reduce(
			$siblings,
			function ( $carry, $variation_id, $variation_option ) use ( $upgrade_options ) {
				if ( $variation_id !== $this->get_id() ) {
					$title_placeholder                       = $variation_option->get_name();
					$price_placeholder                       = intval( $variation_option->model->get_price() ) - intval( $this->model->get_price() );
					$price                                   = isset( $upgrade_options[ $variation_id ]['price'] ) && is_numeric( $upgrade_options[ $variation_id ]['price'] ) ? absint( $upgrade_options[ $variation_id ]['price'] ) : '';
					$active                                  = isset( $upgrade_options[ $variation_id ]['active'] ) ? absint( $upgrade_options[ $variation_id ]['active'] ) : 0;
					$carry[ $variation_id ]['default_title'] = $title_placeholder;
					$carry[ $variation_id ]['default_price'] = $price_placeholder;
					$carry[ $variation_id ]['price']         = $price;
					$carry[ $variation_id ]['active']        = $active;
				}
				return $carry;
			},
			array()
		);
	}

	public function get_license_automatic_updates() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_meta( '_qlwlm_automatic_updates', true );
	}

	public function get_license_file_download_path() {

		$download_key = $this->get_license_automatic_updates();

		if ( ! $download_key ) {
			return;
		}

		$download_file_path = $this->model->get_file_download_path( $download_key );

		if ( ! $download_file_path ) {
			return;
		}

		return $download_file_path;
	}

	/**
	 * Extra
	 */
	public function get_downloads() {

		$downloads_options = array(
			'' => esc_html__( 'Automatic updates disabled', 'licenses-manager-for-woocommerce' ),
		);

		if ( ! $this->model ) {
			return $downloads_options;
		}

		if ( $this->model && $this->model->is_downloadable() ) {
			foreach ( $this->model->get_downloads() as $file_id => $file ) {
				$downloads_options[ $file_id ] = "{$file['name']} - (#{$file_id})";
			}
		}

		return $downloads_options;
	}

	public function get_variations() {

		if ( ! $this->model ) {
			return false;
		}

		$variations = array();

		$childrens = $this->model->get_children();

		if ( ! $childrens ) {
			return false;
		}

		foreach ( $childrens as $variation_id ) {
			$variations[ $variation_id ] = new Load( $variation_id );
		}

		return $variations;
	}

	public function get_siblings() {

		if ( ! $this->model ) {
			return false;
		}

		$variations = array();

		$parent_id = $this->get_parent_id();

		if ( ! $parent_id ) {
			return false;
		}

		$parent = wc_get_product( $parent_id );

		foreach ( $parent->get_children() as $variation_id ) {
			$variations[ $variation_id ] = new Load( $variation_id );
		}

		return $variations;
	}

	/**
	 * Envato
	 */
	public function get_envato_item_id() {

		if ( ! $this->model ) {
			return '';
		}
		return $this->model->get_meta( '_qlwlm_envato_item_id', true );
	}


	public function get_envato_product_id() {

		if ( ! $this->model ) {
			return '';
		}

		if ( ! $this->model->is_type( 'variable' ) ) {
			return $this->model->get_id();
		}

		return $this->model->get_meta( '_qlwlm_envato_product_id', true );
	}

	public function get_envato_license_email() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_envato_license_email', true );
	}

	public function get_envato_license_updates() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_envato_license_updates', true );
	}

	public function get_envato_license_support() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_envato_license_support', true );
	}

	public function get_envato_license_limit() {

		if ( ! $this->model ) {
			return '';
		}

		return absint( $this->model->get_meta( '_qlwlm_envato_license_limit', true ) );
	}

	public function get_envato_license_expiration_period() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_envato_license_expiration_period', true );
	}

	public function get_envato_license_expiration_units() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_envato_license_expiration_units', true );
	}

	// public function get_envato_license_created() {

	// if ( ! $this->model ) {
	// return '';
	// }

	// return( 'no' != get_option( 'qlwlm_envato_license_created' ) ) ? gmdate( 'Y-m-d H:i:s', strtotime( $response->sold_at ) ) : '';
	// }


	/**
	 * WordPress
	 */
	public function get_wordpress_version() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_version', true );
	}

	public function get_wordpress_name() {

		if ( ! $this->model ) {
			return '';
		}

		$name = $this->model->get_meta( '_qlwlm_wordpress_name', true );

		if ( $name ) {
			return $name;
		}

		return $this->get_name();
	}

	public function get_wordpress_requires() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_requires', true );
	}

	public function get_wordpress_tested() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_tested', true );
	}

	public function get_wordpress_author() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_author', true );
	}

	public function get_wordpress_last_updated() {

		if ( ! $this->model ) {
			return '';
		}

		return date_i18n( get_option( 'date_format' ), strtotime( $this->model->get_meta( '_qlwlm_wordpress_last_updated', true ) ) );
	}

	public function get_wordpress_banner_high() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_banner_high', true );
	}

	public function get_wordpress_banner_low() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_banner_low', true );
	}

	public function get_wordpress_icon() {

		if ( ! $this->model ) {
			return '';
		}
		return $this->model->get_meta( '_qlwlm_wordpress_icon', true );
	}

	public function get_wordpress_homepage() {

		if ( ! $this->model ) {
			return '';
		}
		return $this->model->get_meta( '_qlwlm_wordpress_homepage', true );
	}

	public function get_wordpress_upgrade_notice() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_upgrade_notice', true );
	}

	public function get_wordpress_changelog() {

		if ( ! $this->model ) {
			return '';
		}

		return $this->model->get_meta( '_qlwlm_wordpress_changelog', true );
	}

	public function get_wordpress_description() {

		if ( ! $this->model ) {
			return '';
		}

		$description = $this->model->get_meta( '_qlwlm_wordpress_description', true );

		if ( ! $description ) {
			return '';
		}

		return $this->model->get_description();
	}

	public function get_wordpress_screenshots() {

		$screenshots = array();

		if ( ! $this->model ) {
			return false;
		}

		$attachment_ids = $this->model->get_gallery_image_ids();

		if ( ! $attachment_ids ) {
			return false;
		}

		foreach ( $attachment_ids as $attachment_id ) {
			$screenshots[] = array(
				'src'     => wp_get_attachment_image_src( $attachment_id, 'full' )[0],
				'caption' => wp_get_attachment_caption( $attachment_id ),
			);
		}

		return $screenshots;
	}

	/**
	 * Product methods
	 */
	public function get_date_created() {

		if ( ! $this->model ) {
			return '';
		}

		return date_i18n( get_option( 'date_format' ), strtotime( $this->model->get_date_created() ) );
	}

	public function get_name() {

		if ( ! $this->model ) {
			return;
		}

		return ucfirst( $this->model->get_name() );
	}

	public function get_id() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_id();
	}

	public function get_parent_id() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_parent_id();
	}

	public function get_permalink() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_permalink();
	}

	public function get_children() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_children();
	}

	public function is_type( string $type ) {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->is_type( $type );
	}

	public function get_type( string $type = null ) {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->get_type( $type );
	}

	/**
	 * Meta
	 */
	public function get_meta( string $key ) {

		if ( ! $this->model ) {
			return;
		}

		if ( ! $key ) {
			return;
		}

		return $this->model->get_meta( $key, true );
	}

	public function update_meta( string $key, $value, $save = false ) {

		if ( ! $this->model ) {
			return;
		}

		if ( ! $key ) {
			return;
		}

		return $this->model->update_meta_data( $key, $value );
	}

	public function delete_meta( string $key ) {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->delete_meta_data( $key );
	}

	public function save() {

		if ( ! $this->model ) {
			return;
		}

		return $this->model->save();
	}

	public function is_subscription() {

		if ( $this->model && class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $this->model ) ) {
			return true;
		}

		return false;
	}
}
