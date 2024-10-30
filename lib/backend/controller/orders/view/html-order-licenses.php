<div id="qlwlm_order_licenses_inner" class="wc-metaboxes-wrapper qlwlm-metaboxes-wrapper">
	<div class="wc-metaboxes">
		<?php if ( $licenses && sizeof( $licenses ) > 0 ) : ?>
			<?php foreach ( $licenses as $i => $license ) : ?>
			<div class="wc-metabox closed">
			<h3 class="fixed">
				<button type="button" data-order_id="<?php echo esc_attr( $license->get_order_id() ); ?>" data-license_id="<?php echo esc_attr( $license->get_license_id() ); ?>" class="button qlwlm_delete_license"><?php esc_html_e( 'Delete', 'licenses-manager-for-woocommerce' ); ?></button>
				<div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'licenses-manager-for-woocommerce' ); ?>"></div>
				<strong translate="no"><?php printf( '%1$s: %2$s - %3$s', esc_html__( 'Key', 'licenses-manager-for-woocommerce' ), esc_html( $license->get_license_key() ), wp_kses_post( $license->get_product_link() ) ); ?></strong>
				<input type="hidden" name="license_id[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $license->get_license_id() ); ?>" />
			</h3>
			<table class="wc-metabox-content qlwlm-premium-field">
				<tbody>
				<tr>
					<td>
						<label><?php esc_html_e( 'Key', 'licenses-manager-for-woocommerce' ); ?>:</label>
						<input type="text" class="short" name="license_key[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $license->get_license_key() ); ?>" />
					</td>
					<td>
						<label><?php esc_html_e( 'Email', 'licenses-manager-for-woocommerce' ); ?>:</label>
						<select class="short" name="license_email[<?php echo esc_attr( $i ); ?>]" >
							<option value="1"
							<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo wc_selected( $license->get_license_email(), 1 );
							?>
							><?php echo esc_html_e( 'Yes', 'licenses-manager-for-woocommerce' ); ?></option>
							<option value="0"
							<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo wc_selected( $license->get_license_email(), 0 );
							?>
							><?php echo esc_html_e( 'No', 'licenses-manager-for-woocommerce' ); ?></option>
						</select>
					</td>
					<td>
						<label><?php esc_html_e( 'Activations Limit', 'licenses-manager-for-woocommerce' ); ?>:</label>
						<input type="number" min="0" class="short" name="license_limit[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $license->get_license_limit() ); ?>" placeholder="<?php esc_html_e( 'Unlimited', 'licenses-manager-for-woocommerce' ); ?>" />
					</td>
					<td>
						<label><?php esc_html_e( 'Updates Limit', 'licenses-manager-for-woocommerce' ); ?>:</label>
						<select class="short" name="license_updates[<?php echo esc_attr( $i ); ?>]" >
							<option value="1"
							<?php
							  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo wc_selected( $license->get_license_updates(), 1 );
							?>
								><?php echo esc_html_e( 'Yes', 'licenses-manager-for-woocommerce' ); ?></option>
							<option value="0"
							<?php
							  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wc_selected( $license->get_license_updates(), 0 );
							?>
				><?php echo esc_html_e( 'No', 'licenses-manager-for-woocommerce' ); ?></option>
						</select>
					</td>
					<td>
						<label><?php esc_html_e( 'Support Limit', 'licenses-manager-for-woocommerce' ); ?>:</label>
						<select class="short" name="license_support[<?php echo esc_attr( $i ); ?>]" >
							<option value="1"
							<?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wc_selected( $license->get_license_support(), 1 );
							?>
				><?php echo esc_html_e( 'Yes', 'licenses-manager-for-woocommerce' ); ?></option>
							<option value="0"
							<?php
							  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo wc_selected( $license->get_license_support(), 0 );
							?>
								><?php echo esc_html_e( 'No', 'licenses-manager-for-woocommerce' ); ?></option>
						</select>
					</td>
					<td>
						<label><?php esc_html_e( 'Expiration', 'licenses-manager-for-woocommerce' ); ?>:</label>
						<input class="qlwlm-field-datepicker" type="text" class="short" name="license_expiration[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_html( $license->get_license_expiration() ); ?>" placeholder="<?php esc_html_e( 'Unlimited', 'licenses-manager-for-woocommerce' ); ?>" />
					</td>
				</tr>
				<tr>
					<td colspan="5">
						<span><?php printf( esc_html__( 'This license was created for the product %1$s on %2$s and expires on %3$s', 'licenses-manager-for-woocommerce' ), esc_html( $license->get_product_name() ), esc_html( $license->get_license_created_date() ), esc_html( $license->get_license_expiration_date() ) ); ?></span>
					</td>
				</tr>
				</tbody>
			</table>
			</div>
		<?php endforeach; ?>
		<?php else : ?>
		<div class="wc-metabox closed">
			<p><?php esc_html_e( 'No license yet', 'licenses-manager-for-woocommerce' ); ?></p>
		</div>
		<?php endif; ?>
	</div>
</div>
