<?php
wc_get_template(
	'templates/myaccount/licenses.php',
	array(
		'licenses' => $licenses,
	),
	'',
	QLWLM_PLUGIN_DIR
);
