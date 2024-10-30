<?php

if ( class_exists( 'QuadLayers\\WP_Plugin_Table_Links\\Load' ) ) {
	new \QuadLayers\WP_Plugin_Table_Links\Load(
		QLWLM_PLUGIN_FILE,
		array(
			array(
				'text'   => esc_html__( 'Settings', 'licenses-manager-for-woocommerce' ),
				'url'    => admin_url( 'admin.php?page=wc-settings&tab=qlwlm' ),
				'target' => '_self',
			),
			array(
				'text' => esc_html__( 'Premium', 'licenses-manager-for-woocommerce' ),
				'url'  => QLWLM_PURCHASE_URL,
			),
			array(
				'place' => 'row_meta',
				'text'  => esc_html__( 'Support', 'licenses-manager-for-woocommerce' ),
				'url'   => QLWLM_SUPPORT_URL,
			),
			array(
				'place' => 'row_meta',
				'text'  => esc_html__( 'Documentation', 'licenses-manager-for-woocommerce' ),
				'url'   => QLWLM_DOCUMENTATION_URL,
			),
		)
	);
}
