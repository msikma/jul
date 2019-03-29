<?php

// The base directory where the project is located, e.g. '/var/www/jul'.
$GLOBALS['jul_base_path'] = implode('/', array_splice(explode('/', __DIR__), 0, -1));
// The base path, e.g. '/jul' if the index is at '/jul/index.html'.
$GLOBALS['jul_base_dir'] = implode('/', array_splice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
// A link to home.
$GLOBALS['jul_home'] = $GLOBALS['jul_base_dir'].'/';

// Path where we can find the views, e.g. thread.php, forum.php.
$GLOBALS['jul_views_path'] = "{$GLOBALS['jul_base_dir']}";
// Path where we can find themes.
$GLOBALS['jul_themes_path'] = "{$GLOBALS['jul_base_dir']}/themes";
// Path to the static resources.
$GLOBALS['jul_static_path'] = "{$GLOBALS['jul_base_dir']}/static";

// Allow us to include from lib/ wherever we are.
set_include_path($GLOBALS['jul_base_path']);
