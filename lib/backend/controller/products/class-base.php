<?php

namespace QuadLayers\WLM\Backend\Controller\Products;

use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

abstract class Base {

	public static function add_setting_field( $field ) {

		if ( 'start_group' == $field ) {
			echo '<div class="options_group">';
		} elseif ( 'end_group' == $field ) {
			echo '</div>';
		} elseif ( isset( $field['type'] ) ) {
			if ( 'start_group' == $field['type'] ) {
				echo '<div class="options_group ' . $field['class'] . '">';
			} elseif ( function_exists( 'woocommerce_wp_' . $field['type'] ) ) {
				$function = 'woocommerce_wp_' . $field['type'];
				$function( $field );
			} elseif ( function_exists( 'woocommerce_wp_' . $field['type'] . '_input' ) ) {
				$function = 'woocommerce_wp_' . $field['type'] . '_input';
				$function( $field );
			} else {
				woocommerce_wp_text_input( $field );
			}
		}
	}

	public static function save( $product_id ) {

		$model_product = new Model_Product_License( $product_id );

		if ( ! $model_product->get_id() ) {
			return;
		}

		if ( isset( $_POST['_qlwlm_product_data_license_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_qlwlm_product_data_license_nonce'] ) ), '_qlwlm_product_data_license_save_nonce' ) ) {
			if ( ! empty( $_POST['_is_qlwlm'] ) ) {
				$model_product->update_meta( '_is_qlwlm', 'yes' );
			} else {
				$model_product->delete_meta( '_is_qlwlm' );
			}
		}

		foreach ( static::get_fields() as $field ) {

			if ( isset( $field['id'] ) ) {

				$id = $field['id'];

				if ( isset( $_POST[ $id ] ) ) {

					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$value = wc_clean( wp_unslash( $_POST[ $id ] ) );

					if ( ! is_array( $value ) ) {
						$value = trim( $value );
					}
					$model_product->update_meta( $id, $value );
				} else {
					$model_product->delete_meta( $id );
				}
			}
		}

		$model_product->save();
	}

	abstract static function add_tab( $tabs );

	abstract static function add_tab_content();

	abstract static function get_fields( $product_id = null );
}
