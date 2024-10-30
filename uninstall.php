<?php

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use QuadLayers\WLM\Models\License\Setup as Model_License_Setup;
use QuadLayers\WLM\Models\Activation\Setup as Model_Activation_Setup;

if ( 'yes' === get_option( 'qlwlm_tools_data_delete_licenses', 'no' ) ) {
	Model_License_Setup::delete_table();
	Model_Activation_Setup::delete_table();
}
