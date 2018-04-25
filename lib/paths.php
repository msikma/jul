<?php

// The base path we're currently in. If the forum is located at http://example.com/jul/, this will be '/jul'.
// Take the current directory and pop off the last two items to get the containing dir.
// This removes /lib and /jul (or whatever the containing directory is named, e.g. 'www').
$containing_dir = implode('/', array_splice(explode('/', __DIR__), 0, -2));
// The base directory contains the project root.
$base_dir = implode('/', array_splice(explode('/', __DIR__), 0, -1));

$GLOBALS['jul_base_path'] = $base_dir;
$GLOBALS['jul_base_dir'] = str_replace($containing_dir, '', $base_dir);
$GLOBALS['jul_home'] = $GLOBALS['jul_base_dir'].'/';
// Path where we can find the views, e.g. thread.php, forum.php.
$GLOBALS['jul_views_path'] = "{$GLOBALS['jul_base_dir']}/views";
// Path where we can find themes.
$GLOBALS['jul_themes_path'] = "{$GLOBALS['jul_base_dir']}/themes";

// Allow us to include from lib/ wherever we are.
set_include_path($GLOBALS['jul_base_path']);
