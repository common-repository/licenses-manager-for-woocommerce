<?php
namespace QuadLayers\WLM\Backend\Controller\Products\Variable;

use QuadLayers\WLM\Backend\Controller\Products\Simple\License as Simple_License;

use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class License {

	protected static $instance;

	public static function save( $variation_id, $loop ) {

		$model_product = new Model_Product_License( $variation_id );

		if ( ! $model_product->get_id() ) {
			return;
		}

		if ( isset( $_POST[ "_qlwlm_product_data_license_nonce_{$loop}" ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ "_qlwlm_product_data_license_nonce_{$loop}" ] ) ), '_qlwlm_product_data_license_save_nonce' ) ) {
			if ( ! empty( $_POST['_is_qlwlm'] ) ) {
				$model_product->update_meta( '_is_qlwlm', 'yes' );
			} else {
				$model_product->delete_meta( '_is_qlwlm' );
			}
		}

		foreach ( Simple_License::get_fields( $model_product->get_id() ) as $field ) {

			if ( isset( $field['id'] ) ) {
				$id = $field['id'];

				if ( isset( $_POST[ $id ][ $loop ] ) ) {

					$value = wc_clean( wp_unslash( $_POST[ $id ][ $loop ] ) );
					if ( is_numeric( $value ) ) {
						$value = intval( $value );
					}
					$model_product->update_meta( $id, $value );
				} else {
					$model_product->delete_meta( $id );
				}
			}
		}

		$model_product->save();
	}

	public static function add_props( $variation_id, $variation ) {

		$model_product = new Model_Product_License( $variation->get_parent_id() );

		if ( ! $model_product->is_qlwlm() ) {
			return;
		}

		$variation->set_props(
			array(
				'virtual'      => 'yes',
				'downloadable' => 'yes',
				'manage_stock' => 'no',
				'stock_status' => 'instock',
			)
		);
		$variation->update_meta_data( '_is_qlwlm', 'yes' );
		$variation->save();
	}

	public static function add_options( $loop, $variation_data, $variation ) {
		$model_product = new Model_Product_License( $variation );
		?>
			<label class="tips show_if_qlwlm" data-tip="<?php esc_html_e( 'Enable this option if you want to manage license keys', 'licenses-manager-for-woocommerce' ); ?>">
				<?php esc_html_e( 'License', 'licenses-manager-for-woocommerce' ); ?>
				<input id="_is_qlwlm_variable" type="checkbox" class="checkbox" name="_is_qlwlm[<?php echo esc_attr( $loop ); ?>]" <?php checked( $model_product->is_qlwlm(), true ); ?> />
			</label>
		<?php
	}

	public static function add_tab_content( $loop, $variation_data, $variation ) {

		$model_product = new Model_Product_License( $variation );

		?>
			<div class="qlwlm_license_variable_panel show_if_qlwlm show_if_qlwlm_variable">
				<h3><?php esc_html_e( 'License Options', 'licenses-manager-for-woocommerce' ); ?></h3>
					<?php
					foreach ( Simple_License::get_fields( $model_product->get_id() ) as $field ) {

						if ( ! isset( $field['id'] ) || '_qlwlm_renew_price' === $field['id'] ) {
							continue;
						}

						$args = array(
							'id'    => "{$field['id']}_$loop",
							'name'  => "{$field['id']}[{$loop}]",
							'value' => $model_product->get_meta( $field['id'] ),
						);

						// Exception to catch field value and set it to args value, because get_meta( $field['id'] ) return empty string.
						if ( '_qlwlm_product_data_license_nonce' === $field['id'] ) {
							$args['value'] = $field['value'];
						}

						$field = wp_parse_args( $args, $field );

						Simple_License::add_setting_field( $field );

					}
					?>
			</div>
			<?php if ( 'yes' === get_option( 'qlwlm_license_renew', 'no' ) ) : ?>
				<div class="qlwlm_license_variable_panel show_if_qlwlm show_if_qlwlm_variable hide_if_qlwlm_subscription">
					<h3><?php esc_html_e( 'License Renew', 'licenses-manager-for-woocommerce' ); ?></h3>
					<?php
					Simple_License::add_setting_field(
						array(
							'id'                => "_qlwlm_renew_price[{$loop}]",
							'name'              => "_qlwlm_renew_price[{$loop}]",
							'value'             => $model_product->get_license_renew_price(),
							'class'             => 'short wc_input_price',
							'type'              => 'number',
							'custom_attributes' => array( 'min' => 0 ),
							'label'             => sprintf( esc_html__( 'Renewal price (%s)', 'licenses-manager-for-woocommerce' ), get_woocommerce_currency_symbol() ),
							'description'       => esc_html__( 'License renew price after expiration period.', 'licenses-manager-for-woocommerce' ),
							'placeholder'       => esc_html__( 'Renew license price', 'licenses-manager-for-woocommerce' ),
							'desc_tip'          => true,
							'position'          => 'left',
							'wrapper_class'     => 'form-field form-row show_if_simple show_if_variable show_if_qlwlm',
						)
					);
					?>
				</div>
			<?php endif; ?>
			<?php if ( 'yes' === get_option( 'qlwlm_license_upgrade', 'no' ) ) : ?>
				<div class="qlwlm_license_variable_panel show_if_qlwlm show_if_qlwlm_variable hide_if_qlwlm_subscription">
					<h3><?php esc_html_e( 'License Upgrade', 'licenses-manager-for-woocommerce' ); ?></h3>
					<?php

					$upgrade_options = $model_product->get_license_upgrade_options_siblings();

					if ( $upgrade_options ) {

						foreach ( $upgrade_options as $variation_id => $variation_option ) {

							$title_placeholder = $variation_option['default_title'];
							$price_placeholder = $variation_option['default_price'];
							$price             = $variation_option['price'];
							$active            = $variation_option['active'];
							$is_possible       = $price_placeholder > 0;

							?>
								<p class="form-field form-row qlwlm-form-field-column-2">
									<label>
									<?php
									// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
									esc_html_e( sprintf( 'Upgrade from "%s" to:', $variation->post_title ), 'licenses-manager-for-woocommerce' );
									?>
									</label>
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NonSingularStringLiteralText
									echo wc_help_tip( __( $is_possible ? 'Variation name.' : 'The option is dissabled because variation is lower tier product.', 'licenses-manager-for-woocommerce' ) );
									?>

									<input name="_qlwlm_upgrade_options[<?php echo esc_attr( $loop ); ?>][<?php echo esc_attr( $variation_id ); ?>][title]" value="" placeholder="<?php echo esc_html( $title_placeholder ); ?>" type="text" class="short" disabled>
								</p>
								<p class="form-field form-row qlwlm-form-field-column-4 qlwlm-form-field-variation-price">
									<label><?php esc_html_e( 'Price', 'licenses-manager-for-woocommerce' ); ?></label>
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo wc_help_tip( __( 'Show price difference between actual variation and this variation. You must set final price.', 'licenses-manager-for-woocommerce' ) );
									?>

									<input name="_qlwlm_upgrade_options[<?php echo esc_attr( $loop ); ?>][<?php echo esc_attr( $variation_id ); ?>][price]" <?php echo $is_possible ? '' : 'disabled'; ?>  value="<?php echo esc_attr( $is_possible ? $price : '' ); ?>" placeholder="<?php echo esc_attr( $price_placeholder ); ?>" type="number" class="short" min="0">
								</p>
								<p class="form-field form-row qlwlm-form-field-column-4">
									<label><?php esc_html_e( 'Active', 'licenses-manager-for-woocommerce' ); ?></label>
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NonSingularStringLiteralText
									echo wc_help_tip( __( $is_possible ? 'Set as possible to upgrade from actual variation to this variation. Price should be higher than zero to set as active.' : 'Is not possible to upgrade to a lower product variation', 'licenses-manager-for-woocommerce' ) );
									?>

									<select name="_qlwlm_upgrade_options[<?php echo esc_attr( $loop ); ?>][<?php echo esc_attr( $variation_id ); ?>][active]" class="select short" <?php echo $is_possible ? '' : 'disabled'; ?>>
										<option value="1" <?php echo selected( (bool) $active ); ?>><?php esc_html_e( 'Yes', 'licenses-manager-for-woocommerce' ); ?></option>
										<option value="0" <?php echo selected( ! $active || ! $is_possible ); ?>><?php esc_html_e( 'No', 'licenses-manager-for-woocommerce' ); ?></option>
									</select>
								</p>
								<hr/>
							<?php
						}
					}
					?>
				</div>
			<?php endif; ?>
			<?php
	}

	public function __construct() {
		add_action( 'woocommerce_new_product_variation', array( $this, 'add_props' ), 10, 2 );
		add_action( 'woocommerce_variation_options', array( $this, 'add_options' ), 10, 3 );
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'add_tab_content' ), 999999, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save' ), 10, 2 );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
