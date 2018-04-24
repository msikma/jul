<?php

if (file_exists('lib/firewall.php') && !filter_bool($disable_firewall)) {
	require_once 'lib/firewall.php';
} else {
	// Bad Design Decisions 2001.
	// :(
	if (!get_magic_quotes_gpc()) {
		$_GET = addslashes_array($_GET);
		$_POST = addslashes_array($_POST);
		$_COOKIE = addslashes_array($_COOKIE);
	}
	if (!ini_get('register_globals')) {
		$supers=array('_ENV', '_SERVER', '_GET', '_POST', '_COOKIE');
		foreach($supers as $__s) if (is_array($$__s)) extract($$__s, EXTR_SKIP);
		unset($supers);
	}
}
