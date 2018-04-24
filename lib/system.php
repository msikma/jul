<?php

// Set the include path - allows us to import from the perspective of the Git root.
set_include_path('./');

ini_set('default_charset', 'UTF-8');

require_once 'lib/paths.php';
require_once 'lib/error.php';
require_once 'lib/check-install.php';
require_once 'lib/defaults.php';
require_once 'lib/config.php';
require_once 'lib/helpers.php';
require_once 'lib/install.php';
require_once 'lib/mysql.php';
require_once 'lib/routing.php';
require_once 'lib/rpg.php';
require_once 'lib/security.php';
require_once 'lib/threadpost.php';
require_once 'lib/version.php';
require_once 'lib/xss.php';

// Note: lib/actions/function.php will automatically redirect to the error page
// if the database is not reachable or if the forum is not installed.
// Except if we're running the installer.
require_once 'lib/actions/function.php';
require_once 'lib/actions/layout.php';

// Determine and run the user's requested route.
include('lib/actions/route.php');
