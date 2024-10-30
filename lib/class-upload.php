<?php

namespace QuadLayers\WLM;

class Upload {

	protected static $instance;

	public function __construct() {
		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 99 );
	}

	function upload_mimes( $mimes = array() ) {

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$mimes['zip'] = 'application/zip';
			$mimes['gz']  = 'application/x-gzip';
		}
		return $mimes;
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
