<?php

namespace QuadLayers\WLM\Backend\Controller\Tables\View;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;

class List_Table_Licenses extends List_Table {

	public $items;

	function __construct() {
		parent::__construct(
			array(
				'singular' => 'license', // singular name of the listed records
				'plural'   => 'licenses', // plural name of the listed records
				'ajax'     => false,     // does this table support ajax?
			)
		);
	}

	function column_default( $license, $column_name ) {
		switch ( $column_name ) {
			case 'order_id':
				return $license->get_order_link();
			case 'product_id':
				return $license->get_product_link();
			case 'license_created':
				return $license->get_license_created_date();
			case 'license_key':
				return $license->get_license_key_link();
			case 'license_email':
				return $license->get_order_email();
			case 'license_limit':
				return $license->get_license_limit_status();
			case 'license_updates':
				return $license->get_license_updates_status();
			case 'license_support':
				return $license->get_license_support_status();
			case 'license_expiration':
				return $license->get_license_expiration_date();
			case 'activation_created':
				return $license->get_activation_created_date();
			case 'activation_status':
				return $license->get_activation_status_status();
			case 'activation_site':
				return $license->get_activation_site_link();
			default:
				return $license->get_property( $column_name );

		}
	}

	function column_cb( $license ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'license_id', $license->get_license_id() );
	}

	function get_columns() {
		$columns = array(
			'cb'                 => '<input type="checkbox" />',
			'license_created'    => esc_html__( 'Date', 'licenses-manager-for-woocommerce' ),
			'order_id'           => esc_html__( 'Order', 'licenses-manager-for-woocommerce' ),
			'product_id'         => esc_html__( 'Product', 'licenses-manager-for-woocommerce' ),
			'license_key'        => esc_html__( 'Key', 'licenses-manager-for-woocommerce' ),
			'license_email'      => esc_html__( 'Email', 'licenses-manager-for-woocommerce' ),
			'license_updates'    => esc_html__( 'Updates Status', 'licenses-manager-for-woocommerce' ),
			'license_support'    => esc_html__( 'Support Status', 'licenses-manager-for-woocommerce' ),
			'license_limit'      => esc_html__( 'Activations Limit', 'licenses-manager-for-woocommerce' ),
			'activation_count'   => esc_html__( 'Activations', 'licenses-manager-for-woocommerce' ),
			'license_expiration' => esc_html__( 'Expiration', 'licenses-manager-for-woocommerce' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'order_id'           => array( 'order_id', false ),
			'license_created'    => array( 'license_created', true ),
			'activation_count'   => array( 'activation_count', true ),
			'license_expiration' => array( 'license_expiration', true ),
		);
		return $sortable_columns;
	}

	function prepare_items() {

		$current_page = $this->get_pagenum();
		$per_page     = $this->get_items_per_page( 'licenses_per_page' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$orderby     = ! empty( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'license_created';
		$order       = empty( $_REQUEST['order'] ) || 'desc' == $_REQUEST['order'] ? 'DESC' : 'ASC';
		$s           = ! empty( $_REQUEST['s'] ) ? trim( sanitize_key( wp_unslash( $_REQUEST['s'] ) ) ) : '';
		$m           = ! empty( $_REQUEST['m'] ) ? sanitize_key( wp_unslash( $_REQUEST['m'] ) ) : '';
		$status      = ! empty( $_REQUEST['_status'] ) ? sanitize_key( wp_unslash( $_REQUEST['_status'] ) ) : '';
		$product_id  = ! empty( $_REQUEST['_product_id'] ) ? intval( $_REQUEST['_product_id'] ) : '';
		$customer_id = ! empty( $_REQUEST['_customer_user'] ) ? intval( $_REQUEST['_customer_user'] ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$this->process_bulk_action();

		$license_date_start = null;
		$license_date_end   = null;

		if ( ! empty( $m ) ) {
			$m                  = strtotime( $m . '01' );
			$license_date_start = strtotime( gmdate( 'Y-m-d H:i:s', $m ) );
			$license_date_end   = strtotime( gmdate( 'Y-m-t H:i:s', $m ) );
		}

		$args = array(
			'per_page'           => $per_page,
			'current_page'       => $current_page,
			'orderby'            => $orderby,
			'order'              => $order,
			'product_id'         => $product_id,
			'customer_id'        => $customer_id,
			'license_key_search' => $s,
			'license_status'     => $status,
			'license_date_start' => $license_date_start,
			'license_date_end'   => $license_date_end,
		);

		/**
		 * Total licenses limited by pagination
		 */
		$licenses = Model_License_Mapper::get( $args );

		/**
			 * Total licenses per query
			 */
		$total_licenses = Model_License_Mapper::count( $args );

		$this->items = $licenses;

		$this->set_pagination_args(
			array(
				'total_items' => $total_licenses,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_licenses / $per_page ),
			)
		);
	}

	function process_bulk_action() {

		if ( 'delete' === $this->current_action() ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_REQUEST['license_id'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$license_ids = array_map( 'intval', $_REQUEST['license_id'] );
				if ( $license_ids ) {

					Model_License_Mapper::delete(
						array(
							'license_ids' => $license_ids,
						)
					);
				}

				echo '<div class="updated"><p>' . esc_html__( 'License deleted', 'licenses-manager-for-woocommerce' ) . '</p></div>';
			}
		}
	}
}
