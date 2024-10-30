<h2 id="licenses"><?php echo esc_html_e( 'Licenses', 'licenses-manager-for-woocommerce' ); ?></h2>
<?php if ( ! $licenses ) : ?> 
	<p>
		<?php esc_html_e( 'No licenses found.', 'licenses-manager-for-woocommerce' ); ?>
	</p>
	<?php
	return;
endif;
?>
<table class="woocommerce-orders-table shop_table shop_table_responsive my_account_orders">
	<thead>
		<th><?php esc_html_e( 'Order', 'licenses-manager-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'Product', 'licenses-manager-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'Key', 'licenses-manager-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'Sites', 'licenses-manager-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'License', 'licenses-manager-for-woocommerce' ); ?></th>
	</thead>
	<tbody>
		<?php
		foreach ( $licenses as $i => $license ) :
			$license_view_link    = $license->get_license_view_link();
			$license_renew_link   = $license->get_license_renew_link();
			$license_upgrade_link = $license->get_license_upgrade_link();
			?>
			<tr>
				<td data-title="<?php esc_html_e( 'Order', 'licenses-manager-for-woocommerce' ); ?>">
					#<?php echo wp_kses_post( $license->get_order_link() ); ?>
				</td>
				<td data-title="<?php esc_html_e( 'Product', 'licenses-manager-for-woocommerce' ); ?>">
					<?php echo wp_kses_post( $license->get_product_link() ); ?>
				</td>
				<td translate="no" data-title="<?php esc_html_e( 'Key', 'licenses-manager-for-woocommerce' ); ?>">
					<?php echo esc_html( $license->get_license_key() ); ?>
				</td>
				<td data-title="<?php esc_html_e( 'Limit', 'licenses-manager-for-woocommerce' ); ?>">
					<?php echo esc_html( $license->get_license_limit_status() ); ?>
				</td>
				<td data-title="<?php esc_html_e( 'License', 'licenses-manager-for-woocommerce' ); ?>">
					<a class="button" href="<?php echo esc_url( $license_view_link ); ?>"><?php esc_html_e( 'View', 'licenses-manager-for-woocommerce' ); ?></a>
					<?php if ( $license_renew_link ) : ?>
						<a class="button alt" href="<?php echo esc_url( $license_renew_link ); ?>"><?php esc_html_e( 'Renew', 'licenses-manager-for-woocommerce' ); ?></a>
					<?php endif; ?>
					<?php if ( $license_upgrade_link ) : ?>
						<a class="button alt" href="<?php echo esc_url( $license_upgrade_link ); ?>"><?php esc_html_e( 'Upgrade', 'licenses-manager-for-woocommerce' ); ?></a>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
