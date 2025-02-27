<?php

if ( class_exists( 'QuadLayers\\WP_Notice_Plugin_Promote\\Load' ) ) {
	/**
	 *  Promote constants
	 */
	define( 'QLWLM_PROMOTE_LOGO_SRC', plugins_url( '/assets/backend/img/logo.jpg', QLWLM_PLUGIN_FILE ) );
	/**
	 * Notice review
	 */
	define( 'QLWLM_PROMOTE_REVIEW_URL', 'https://wordpress.org/support/plugin/licenses-manager-for-woocommerce/reviews/?filter=5#new-post' );
	/**
	 * Notice premium sell
	 */
	define( 'QLWLM_PROMOTE_PREMIUM_SELL_SLUG', 'licenses-manager-for-woocommerce-pro' );
	define( 'QLWLM_PROMOTE_PREMIUM_SELL_NAME', 'WooCommerce Licenses Manager PRO' );
	define( 'QLWLM_PROMOTE_PREMIUM_INSTALL_URL', 'https://quadlayers.com/product/woocommerce-license-manager/?utm_source=qlwlm_admin' );
	define( 'QLWLM_PROMOTE_PREMIUM_SELL_URL', QLWLM_PREMIUM_SELL_URL );
	/**
	 * Notice cross sell 1
	 */
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_1_SLUG', 'woocommerce-checkout-manager' );
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_1_NAME', 'WooCommerce Checkout Manager' );
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_1_DESCRIPTION', esc_html__( 'This plugin allows you to add custom fields to the checkout page, related to billing, shipping or additional fields sections.', 'licenses-manager-for-woocommerce' ) );
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_1_URL', 'https://quadlayers.com/products/woocommerce-checkout-manager/?utm_source=qlwlm_admin' );
	/**
	 * Notice cross sell 2
	 */
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_2_SLUG', 'woocommerce-direct-checkout' );
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_2_NAME', 'WooCommerce Direct Checkout' );
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_2_DESCRIPTION', esc_html__( 'It allows you to reduce the steps in the checkout process by skipping the shopping cart page. This can encourage buyers to shop more and quickly. You will increase your sales reducing cart abandonment.', 'licenses-manager-for-woocommerce' ) );
	define( 'QLWLM_PROMOTE_CROSS_INSTALL_2_URL', 'https://quadlayers.com/products/woocommerce-direct-checkout/?utm_source=qlwlm_admin' );

	new \QuadLayers\WP_Notice_Plugin_Promote\Load(
		QLWLM_PLUGIN_FILE,
		array(
			array(
				'type'               => 'ranking',
				'notice_delay'       => MONTH_IN_SECONDS,
				'notice_logo'        => QLWLM_PROMOTE_LOGO_SRC,
				'notice_title'       => sprintf(
					esc_html__(
						'Hello! Thank you for choosing the %s plugin!',
						'licenses-manager-for-woocommerce'
					),
					QLWLM_PLUGIN_NAME
				),
				'notice_description' => esc_html__( 'Could you please give it a 5-star rating on WordPress? Your feedback boosts our motivation, helps us promote, and continues to improve this product. Your support matters!', 'licenses-manager-for-woocommerce' ),
				'notice_link'        => QLWLM_PROMOTE_REVIEW_URL,
				'notice_link_label'  => esc_html__(
					'Yes, of course!',
					'licenses-manager-for-woocommerce'
				),
				'notice_more_link'   => QLWLM_SUPPORT_URL,
				'notice_more_label'  => esc_html__(
					'Report a bug',
					'licenses-manager-for-woocommerce'
				),
			),
			array(
				'plugin_slug'        => QLWLM_PROMOTE_PREMIUM_SELL_SLUG,
				'plugin_install_link'   => QLWLM_PROMOTE_PREMIUM_INSTALL_URL,
				'plugin_install_label'  => esc_html__(
					'Purchase Now',
					'licenses-manager-for-woocommerce'
				),
				'notice_delay'       => MONTH_IN_SECONDS,
				'notice_logo'        => QLWLM_PROMOTE_LOGO_SRC,
				'notice_title'       => esc_html__(
					'Hello! We have a special gift!',
					'licenses-manager-for-woocommerce'
				),
				'notice_description' => sprintf(
					esc_html__(
						'Today we have a special gift for you. Use the coupon code %1$s within the next 48 hours to receive a %2$s discount on the premium version of the %3$s plugin.',
						'licenses-manager-for-woocommerce'
					),
					'ADMINPANEL20%',
					'20%',
					QLWLM_PROMOTE_PREMIUM_SELL_NAME
				),
				'notice_more_link'   => QLWLM_PROMOTE_PREMIUM_SELL_URL,
				'notice_more_label'  => esc_html__(
					'More info!',
					'licenses-manager-for-woocommerce'
				),
			),
			array(
				'plugin_slug'        => QLWLM_PROMOTE_CROSS_INSTALL_1_SLUG,
				'notice_delay'       => MONTH_IN_SECONDS * 4,
				'notice_logo'        => QLWLM_PROMOTE_LOGO_SRC,
				'notice_title'       => sprintf(
					esc_html__(
						'Hello! We want to invite you to try our %s plugin!',
						'licenses-manager-for-woocommerce'
					),
					QLWLM_PROMOTE_CROSS_INSTALL_1_NAME
				),
				'notice_description' => QLWLM_PROMOTE_CROSS_INSTALL_1_DESCRIPTION,
				'notice_more_link'   => QLWLM_PROMOTE_CROSS_INSTALL_1_URL,
				'notice_more_label'  => esc_html__(
					'More info!',
					'licenses-manager-for-woocommerce'
				),
			),
			array(
				'plugin_slug'        => QLWLM_PROMOTE_CROSS_INSTALL_2_SLUG,
				'notice_delay'       => MONTH_IN_SECONDS * 6,
				'notice_logo'        => QLWLM_PROMOTE_LOGO_SRC,
				'notice_title'       => sprintf(
					esc_html__(
						'Hello! We want to invite you to try our %s plugin!',
						'licenses-manager-for-woocommerce'
					),
					QLWLM_PROMOTE_CROSS_INSTALL_2_NAME
				),
				'notice_description' => QLWLM_PROMOTE_CROSS_INSTALL_2_DESCRIPTION,
				'notice_more_link'   => QLWLM_PROMOTE_CROSS_INSTALL_2_URL,
				'notice_more_label'  => esc_html__(
					'More info!',
					'licenses-manager-for-woocommerce'
				),
			),
		)
	);
}
