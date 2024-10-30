<div id="qlwlm_order_licenses_activations_inner" class="wc-metaboxes-wrapper qlwlm-metaboxes-wrapper">
	<?php if ( sizeof( $activations ) > 0 ) : ?>
		<table class="striped" cellspacing="0">
			<thead>
				<tr>
				<th><?php esc_html_e( 'License', 'licenses-manager-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Instance', 'licenses-manager-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Product', 'licenses-manager-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Activation Status', 'licenses-manager-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Activation Created', 'licenses-manager-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Activation Site', 'licenses-manager-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Activation Action', 'licenses-manager-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $activations as $activation ) : ?>
				<tr>
					<td translate="no"><?php echo esc_html( $activation->get_license_key() ); ?></td>
					<td><?php echo esc_html( $activation->get_activation_instance() ); ?></td>
					<td><?php echo wp_kses_post( $activation->get_product_link() ); ?></td>
					<td><?php echo esc_html( $activation->get_activation_status_status() ); ?></td>
					<td><?php echo esc_html( $activation->get_activation_created_date() ); ?></td>
					<td><?php echo wp_kses_post( $activation->get_activation_site_link() ); ?></td>
					<td>
						<span class="spinner"></span>
						<button class="button qlwlm_toggle_license_activation" data-order_id="<?php echo esc_attr( $activation->get_order_id() ); ?>" data-license_id="<?php echo esc_attr( $activation->get_license_id() ); ?>" data-activation_instance="<?php echo esc_attr( $activation->get_activation_instance() ); ?>"><?php ( ! $activation->get_activation_status() ) ? esc_html_e( 'Activate', 'licenses-manager-for-woocommerce' ) : esc_html_e( 'Deactivate', 'licenses-manager-for-woocommerce' ); ?></button>
						<button class="button qlwlm_delete_license_activation" data-order_id="<?php echo esc_attr( $activation->get_order_id() ); ?>" data-license_id="<?php echo esc_attr( $activation->get_license_id() ); ?>" data-activation_instance="<?php echo esc_attr( $activation->get_activation_instance() ); ?>"><?php esc_html_e( 'Delete', 'licenses-manager-for-woocommerce' ); ?></button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="wc-metabox closed">
			<p><?php esc_html_e( 'No activations yet', 'licenses-manager-for-woocommerce' ); ?></p>
		</div>
	<?php endif; ?>
</div>
