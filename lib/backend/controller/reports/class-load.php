<?php

namespace QuadLayers\WLM\Backend\Controller\Reports;

use QuadLayers\WLM\Backend\Controller\Reports\View\Licenses_By_Date;
use QuadLayers\WLM\Backend\Controller\Reports\View\Activations_By_Product;

class Load {

	protected static $instance;

	function license_by_date() {
		$report = new Licenses_By_Date();
		$report->output_report();
	}

	function activations_by_product() {
		$report = new Activations_By_Product();
		$report->output_report();
	}

	/*
	function activations_by_category() {
	$report = new QLWLM_Report_Activations_By_Category();
	$report->output_report();
	}*/

	function reports_tab( $reports ) {

		if ( ! class_exists( 'WC_Admin_Report' ) ) {
			require_once WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php';
		}

		$reports['licenses'] = array(
			'title'  => esc_html__( 'Licenses', 'licenses-manager-for-woocommerce' ),
			'charts' => array(
				'licenses_by_date'       => array(
					'title'       => esc_html__( 'Activations by date', 'licenses-manager-for-woocommerce' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( $this, 'license_by_date' ),
				),
				'activations_by_product' => array(
					'title'       => esc_html__( 'Activations by product', 'licenses-manager-for-woocommerce' ),
					'description' => '',
					'callback'    => array( $this, 'activations_by_product' ),
				),
				/*
					'activations_by_category' => array(
						'title'       => esc_html__( 'Activations by category', 'licenses-manager-for-woocommerce' ),
						'description' => '',
						'callback'    => array( $this, 'activations_by_category' ),
					),
				*/
			),
		);

		return $reports;
	}

	public function __construct() {
		add_filter( 'woocommerce_admin_reports', array( $this, 'reports_tab' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
