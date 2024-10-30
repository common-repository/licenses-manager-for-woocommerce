<?php

namespace QuadLayers\WLM\Backend\Controller\Tables\View;

use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class List_Table extends \WP_List_Table {

	public $messages = array();

	function get_bulk_actions() {
		$actions = array(
			'delete' => esc_html__( 'Delete', 'licenses-manager-for-woocommerce' ),
		);
		return $actions;
	}

	protected function date_dropdown() {

		global $wp_locale;

		$months = Model_License_Mapper::get_months();

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		?>
		<label for="filter-by-date" class="screen-reader-text"><?php esc_html_e( 'Filter by date', 'licenses-manager-for-woocommerce' ); ?></label>

		<select name="m" id="filter-by-date">
			<option <?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates', 'licenses-manager-for-woocommerce' ); ?></option>

			<?php
			foreach ( $months as $arc_row ) {

				if ( 0 === $arc_row->year ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );

				$year = $arc_row->year;

				printf( "<option %s value='%s'>%s</option>\n", selected( $m, $year . $month, false ), esc_attr( $arc_row->year . $month ), sprintf( '%1$s %2$d', esc_attr( $wp_locale->get_month( $month ) ), esc_attr( $year ) ) );
			}
			?>
		</select>
		<?php
	}

	protected function expired_dropdown() {
		$status = '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['_status'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$status = sanitize_text_field( wp_unslash( $_GET['_status'] ) );
		}
		?>
		<select name="_status">
			<option value="">
				<?php esc_html_e( 'Filter by status', 'licenses-manager-for-woocommerce' ); ?>
			</option>
			<option value="expired" 
			<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wc_selected( 'expired', $status );
			?>
		>
				<?php esc_html_e( 'Expired', 'licenses-manager-for-woocommerce' ); ?>
			</option>
			<option value="active" 
			<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wc_selected( 'active', $status );
			?>
		>
				<?php esc_html_e( 'Active', 'licenses-manager-for-woocommerce' ); ?>
			</option>
		</select>
		<?php
	}

	protected function product_dropdown() {
		$product_string = '';
		$product_id     = '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['_product_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$product_id     = sanitize_text_field( wp_unslash( $_GET['_product_id'] ) );
			$product_object = wc_get_product( $product_id );
			if ( $product_object ) {
				$product_string = rawurldecode( $product_object->get_formatted_name() );
			}
		}
		?>
		<select class="wc-product-search" name="_product_id" data-placeholder="<?php esc_attr_e( 'Filter by product', 'licenses-manager-for-woocommerce' ); ?>" data-allow_clear="true">
			<option value="<?php echo esc_attr( $product_id ); ?>" selected="selected"><?php echo wp_kses_post( $product_string ); ?><option>
		</select>
		<?php
	}

	protected function customer_dropdown() {
		$user_string = '';
		$user_id     = '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['_customer_user'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$user_id = absint( $_GET['_customer_user'] );
			$user    = get_user_by( 'id', $user_id );

			$user_string = sprintf( esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'licenses-manager-for-woocommerce' ), $user->display_name, absint( $user->ID ), $user->user_email );
		}
		?>
		<select class="wc-customer-search" name="_customer_user" data-placeholder="<?php esc_attr_e( 'Filter by registered customer', 'licenses-manager-for-woocommerce' ); ?>" data-allow_clear="true">
			<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected"><?php echo wp_kses_post( $user_string ); ?><option>
		</select>
		<?php
	}

	protected function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which && ! is_singular() ) {

				ob_start();

				$this->date_dropdown();
				$this->expired_dropdown();
				$this->product_dropdown();
				$this->customer_dropdown();

				$output = ob_get_clean();

				if ( ! empty( $output ) ) {

					echo wp_kses(
						$output,
						array(
							'label'  => array(
								'for'   => array(),
								'class' => array(),
							),
							'select' => array(
								'name'             => array(),
								'id'               => array(),
								'class'            => array(),
								'data-placeholder' => array(),
								'data-allow_clear' => array(),
							),
							'option' => array(
								'value'    => array(),
								'selected' => array(),
							),
						)
					);

					submit_button( esc_html__( 'Filter', 'licenses-manager-for-woocommerce' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
				}
			}

			if ( $this->is_trash && current_user_can( get_post_type_object( $this->screen->post_type )->cap->edit_others_posts ) && $this->has_items() ) {
				submit_button( esc_html__( 'Empty Trash', 'licenses-manager-for-woocommerce' ), 'apply', 'delete_all', false );
			}
			?>
		</div>
		<?php
		// do_action('manage_posts_extra_tablenav', $which);
	}

	public function search_box( $text, $input_id, $placeholder = null ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
	<input type="search" placeholder="<?php echo esc_attr( $placeholder ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>
		<?php
	}
}
