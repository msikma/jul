<?php

//
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
ini_set('default_charset', 'UTF-8');

// Set the include path - allows us to import from the perspective of the Git root.
// If coming from an external entry point this will already have been done.
if (!$GLOBALS['jul_external_entry_point']) set_include_path('./');
// Prevent entry without going through the system.
$GLOBALS['jul_system'] = true;

// These need to be included in order.
require_once 'lib/paths.php';
require_once 'lib/version.php';
require_once 'lib/ui.php';
require_once 'lib/error.php';
require_once 'lib/check-install.php';
require_once 'lib/defaults.php';
require_once 'lib/config.php';

// These can be included in any order. Add new libraries here.
require_once 'lib/helpers.php';
require_once 'lib/install.php';
require_once 'lib/management.php';
require_once 'lib/mysql.php';
require_once 'lib/registration.php';
require_once 'lib/routing.php';
require_once 'lib/rpg.php';
require_once 'lib/actions/connect.php';
require_once 'lib/settings.php';
require_once 'lib/emoticons.php';
require_once 'lib/posticons.php';
require_once 'lib/security.php';
require_once 'lib/threadpost.php';

// Note: lib/actions/function.php will automatically redirect to the error page
// if the database is not reachable or if the forum is not installed.
// Except if we're running the installer.
require_once 'lib/actions/function.php';
require_once 'lib/actions/layout.php';

// Determine and run the user's requested route.
include('lib/actions/route.php');
