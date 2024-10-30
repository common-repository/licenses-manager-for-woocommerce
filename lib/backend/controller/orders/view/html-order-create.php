<div id="qlwlm_order_create_inner" class="wc-metaboxes-wrapper qlwlm-metaboxes-wrapper">	
	<div class="toolbar">
		<p class="buttons">
			<select name="add_product_id" class="qlwlm-add-license wc-enhanced-select" data-placeholder="<?php esc_html_e( 'Choose a software product&hellip;', 'licenses-manager-for-woocommerce' ); ?>">
				<option value=""></option>
				<?php

				$items = $model_order->get_items();

				if ( $items ) :
					foreach ( $items as $item ) :
						?>
					<option value="<?php echo esc_attr( $item->get_id() ); ?>"><?php echo esc_html( $item->get_name() ); ?> (#<?php echo esc_attr( $item->get_id() ); ?>)</option>
						<?php
				endforeach;
				endif;
				?>
			</select>
			<button type="button" class="button qlwlm_create_license" disabled="disabled"><?php esc_html_e( 'Add License Key', 'licenses-manager-for-woocommerce' ); ?></button>
		</p>
		<div class="clear"></div>
	</div>
</div>
