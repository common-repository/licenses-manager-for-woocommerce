<?php
if ( ! $license ) :
	?>
	<div class="wc-notice woocommerce-error woocommerce-error-list">
		<?php
			printf( esc_html__( 'Invalid license key %s', 'licenses-manager-for-woocommerce' ), esc_html( $license_key ) );
		?>
		<a class="wc-forward" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My account', 'licenses-manager-for-woocommerce' ); ?></a>
	</div>
	<?php
	return;
endif;

$license_renew_link   = $license->get_license_renew_link();
$license_upgrade_link = $license->get_license_upgrade_link();

?>
<?php if ( ! current_user_can( 'view_order', $license->get_order_id() ) ) : ?>
	<div class="wc-notice woocommerce-error woocommerce-error-list"><?php esc_html_e( 'Invalid order', 'licenses-manager-for-woocommerce' ); ?>
		<a class="wc-forward" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My account', 'licenses-manager-for-woocommerce' ); ?></a>
	</div>
	<?php
	return;
endif;
?>

<?php if ( $license->is_limit_reached() ) : ?>
	<div class="wc-notice woocommerce-error woocommerce-info">
		<?php esc_html_e( 'Remaining activations is equal to zero', 'licenses-manager-for-woocommerce' ); ?>
		<?php if ( $license_upgrade_link ) : ?>
			<a class="wc-forward" href="<?php echo esc_url( $license_upgrade_link ); ?>"><?php esc_html_e( 'Upgrade', 'licenses-manager-for-woocommerce' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( $license->is_expired_updates() ) : ?>
	<div class="wc-notice woocommerce-error woocommerce-info">
	<?php printf( esc_html__( 'The license updates has expired on %s', 'licenses-manager-for-woocommerce' ), esc_html( $license->get_license_expiration_date() ) ); ?>
		<?php if ( $license_renew_link ) : ?>
			<a class="wc-forward" href="<?php echo esc_url( $license_renew_link ); ?>"><?php esc_html_e( 'Renew', 'licenses-manager-for-woocommerce' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( $license->is_expired_support() ) : ?>
	<div class="wc-notice woocommerce-error woocommerce-info">
	<?php printf( esc_html__( 'The license support has expired on %s', 'licenses-manager-for-woocommerce' ), esc_html( $license->get_license_expiration_date() ) ); ?>
		<?php if ( $license_renew_link ) : ?>
			<a class="wc-forward" href="<?php echo esc_url( $license_renew_link ); ?>"><?php esc_html_e( 'Renew', 'licenses-manager-for-woocommerce' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<h2><?php echo esc_html_e( 'License', 'licenses-manager-for-woocommerce' ); ?></h2>
<table class="woocommerce-orders-table shop_table shop_table_responsive my_account_orders">
	<thead>
		<th><?php esc_html_e( 'License Key', 'licenses-manager-for-woocommerce' ); ?></th>
		<th translate="no"><?php echo esc_html( $license->get_license_key() ); ?></th>		
	</thead>
	<tbody>
		<tr>
			<td><?php esc_html_e( 'Email', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_order_email() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Created', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_license_created_date() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Expiration', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_license_expiration_date() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Order', 'licenses-manager-for-woocommerce' ); ?></td>
			<td>#<?php echo wp_kses_post( $license->get_order_link() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Product', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo wp_kses_post( $license->get_product_link() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Activations', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_activation_count() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Activations Status', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_license_limit_status() ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Support Status', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_license_support_status() ); ?></td>
		</tr>	
		<tr>
			<td><?php esc_html_e( 'Updates Status', 'licenses-manager-for-woocommerce' ); ?></td>
			<td><?php echo esc_html( $license->get_license_updates_status() ); ?></td>
		</tr>	
	</tbody>
	<tfoot>
		<tr>
			<?php
			if ( $license_renew_link ) :
				?>
				<th>
					<a target="_blank" href="<?php echo esc_url( $license_renew_link ); ?>" class="button wc-forward btn btn-default"><?php esc_html_e( 'Renew', 'licenses-manager-for-woocommerce' ); ?></a>
				</th>
				<th>
					<?php printf( esc_html__( 'Renew the "%1$s" license for %2$s %3$s.', 'licenses-manager-for-woocommerce' ), esc_html( $license->get_product_name() ), esc_html( $license->get_license_expiration_period() ), esc_html( $license->get_license_expiration_units() ) ); ?>
				</th>
			<?php else : ?>
				<th colspan="2">
					<?php esc_html_e( 'Renewal is not available for this license.', 'licenses-manager-for-woocommerce' ); ?>
				</th>
			<?php endif; ?>
		</tr>
		<tr>
			<?php
			if ( $license_upgrade_link ) :
				?>
				<th>
					<a target="_blank" href="<?php echo esc_url( $license_upgrade_link ); ?>" class="button wc-forward btn btn-default"><?php esc_html_e( 'Upgrade', 'licenses-manager-for-woocommerce' ); ?></a>
				</th>
				<th>
					<?php printf( esc_html__( 'Upgrade the "%1$s" license to get %2$s activations limit.', 'licenses-manager-for-woocommerce' ), esc_html( $license->get_product_name() ), esc_html( $license->get_license_limit_status() ) /* , esc_html( $license->get_license_support() ) */ ); ?>
				</th>
			<?php else : ?>
				<th colspan="2">
					<?php esc_html_e( 'Upgrade is not available for this license.', 'licenses-manager-for-woocommerce' ); ?>
				</th>
			<?php endif; ?>
		</tr>
	</tfoot>
</table>
<?php
if ( $activations ) :
	?>
	<h2><?php echo esc_html_e( 'Activations', 'licenses-manager-for-woocommerce' ); ?></h2>
	<table class="woocommerce-orders-table shop_table shop_table_responsive my_account_orders">
	<thead>
		<tr>
		<th><?php esc_html_e( 'Activation Status', 'licenses-manager-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'Activation Created', 'licenses-manager-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'Activation Site', 'licenses-manager-for-woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $activations as $activation ) : ?>
		<tr>
			<td><?php echo esc_html( $activation->get_activation_status_status() ); ?></td>
			<td><?php echo esc_html( $activation->get_activation_created_date() ); ?></td>
			<td><?php echo wp_kses_post( $activation->get_activation_site_link() ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	</table>
<?php else : ?>
	<p><?php esc_html_e( 'No activations yet', 'licenses-manager-for-woocommerce' ); ?></p>
<?php endif; ?>
