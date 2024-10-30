<?php

namespace QuadLayers\WLM\Backend\Controller\Tables\View;

use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;

class List_Table_Activations extends List_Table {

	public $items;

	function __construct() {
		parent::__construct(
			array(
				'singular' => 'activation', // singular name of the listed records
				'plural'   => 'activations', // plural name of the listed records
				'ajax'     => false,        // does this table support ajax?
			)
		);
	}

	function column_default( $activation, $column_name ) {

		switch ( $column_name ) {
			case 'order_id':
				return $activation->get_order_link();
			case 'product_id':
				return $activation->get_product_link();
			case 'license_created':
				return $activation->get_license_created_date();
			case 'license_key':
				return $activation->get_license_key_link();
			case 'license_email':
				return $activation->get_order_email();
			case 'license_limit':
				return $activation->get_license_limit_status();
			case 'license_updates':
				return $activation->get_license_updates_status();
			case 'license_support':
				return $activation->get_license_support_status();
			case 'license_expiration':
				return $activation->get_license_expiration_date();
			case 'activation_status':
				return $activation->get_activation_status_status();
			case 'activation_site':
				return $activation->get_activation_site_link();
			case 'activation_created':
				return $activation->get_activation_created_date();
			default:
				return $activation->get_property( $column_name );

		}
	}

	function column_cb( $activation ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'activation_id', $activation->get_activation_id() );
	}

	function get_columns() {
		$columns = array(
			'cb'                  => '<input type="checkbox" />',
			'order_id'            => esc_html__( 'Order', 'licenses-manager-for-woocommerce' ),
			'product_id'          => esc_html__( 'Product', 'licenses-manager-for-woocommerce' ),
			'license_key'         => esc_html__( 'Key', 'licenses-manager-for-woocommerce' ),
			'license_updates'     => esc_html__( 'Updates Status', 'licenses-manager-for-woocommerce' ),
			'license_support'     => esc_html__( 'Support Status', 'licenses-manager-for-woocommerce' ),
			'license_expiration'  => esc_html__( 'Expiration', 'licenses-manager-for-woocommerce' ),
			'activation_status'   => esc_html__( 'Activation Status', 'licenses-manager-for-woocommerce' ),
			'activation_instance' => esc_html__( 'Activation Instance', 'licenses-manager-for-woocommerce' ),
			'activation_site'     => esc_html__( 'Activation Site', 'licenses-manager-for-woocommerce' ),
			'activation_created'  => esc_html__( 'Activation Created', 'licenses-manager-for-woocommerce' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'order_id'           => array( 'order_id', false ),
			'product_id'         => array( 'product_id', true ),
			'license_key'        => array( 'license_key', true ),
			'activation_created' => array( 'activation_created', true ),
			'activation_site'    => array( 'activation_site', false ),
		);
		return $sortable_columns;
	}

	function prepare_items() {
		$current_page = $this->get_pagenum();
		$per_page     = $this->get_items_per_page( 'activations_per_page' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$orderby     = ! empty( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'activation.activation_created';
		$order       = empty( $_REQUEST['order'] ) || 'desc' == $_REQUEST['order'] ? 'DESC' : 'ASC';
		$s           = ! empty( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$m           = ! empty( $_REQUEST['m'] ) ? sanitize_key( wp_unslash( $_REQUEST['m'] ) ) : '';
		$status      = ! empty( $_REQUEST['_status'] ) ? sanitize_key( wp_unslash( $_REQUEST['_status'] ) ) : '';
		$product_id  = ! empty( $_REQUEST['_product_id'] ) ? intval( $_REQUEST['_product_id'] ) : '';
		$customer_id = ! empty( $_REQUEST['_customer_user'] ) ? intval( $_REQUEST['_customer_user'] ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$this->process_bulk_action();

		$activation_date_start = null;
		$activation_date_end   = null;

		if ( ! empty( $m ) ) {
			$m                     = strtotime( $m . '01' );
			$activation_date_start = strtotime( gmdate( 'Y-m-d H:i:s', $m ) );
			$activation_date_end   = strtotime( gmdate( 'Y-m-t H:i:s', $m ) );
		}

		$args = array(
			'per_page'               => $per_page,
			'current_page'           => $current_page,
			'orderby'                => $orderby,
			'order'                  => $order,
			'product_id'             => $product_id,
			'customer_id'            => $customer_id,
			'license_status'         => $status,
			'activation_site_search' => $s,
			'activation_date_start'  => $activation_date_start,
			'activation_date_end'    => $activation_date_end,
		);

		/**
		 * Total activations limited by pagination
		 */
		$activations = Model_Activation_Mapper::get( $args );

		/**
		 * Total activations per query
		 */
		$total_activations = Model_Activation_Mapper::count( $args );

		$this->items = $activations;

		$this->set_pagination_args(
			array(
				'total_items' => $total_activations,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_activations / $per_page ),
			)
		);
	}

	function process_bulk_action() {

		if ( 'delete' === $this->current_action() ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_REQUEST['activation_id'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$activation_ids = array_map( 'intval', $_REQUEST['activation_id'] );
				if ( $activation_ids ) {

					Model_Activation_Mapper::delete(
						array(
							'activation_ids' => $activation_ids,
						)
					);
				}

				echo '<div class="updated"><p>' . esc_html__( 'Activation deleted', 'licenses-manager-for-woocommerce' ) . '</p></div>';
			}
		}
	}
}
