<?php

/**
 * Default configuration values. Some of the settings here are for backwards compatibility.
 *
 * Please do not edit this file. Edit config.php instead.
 * (If it doesn't exist yet, make a copy of config.example.php and edit that.)
 */

$default_sql_settings = array(
	// MySQL connection options.
	'host' => 'localhost',
	'user' => '',
	'pass' => '',
	'name' => '', // Database name
);

$default_forum_settings = array(
	// Board settings.
	'board_name' => 'Board Name',
	'board_title' => 'Board Title',
	'site_url' => 'http://example.com',
	'site_name' => 'Site Name',

	// Default date/time formatting. See PHP's date() function for help.
	// <http://php.net/manual/en/function.date.php>
	'date_format_long' => 'm-d-y h:i:s A',
	'date_format_short' => 'm-d-y',

	// Various hardcoded settings, most of them just legacy.
	'irc_enable_notifications' => false, // Toggles the IRC bot callback code. See xk_ircsend() in lib/actions/function.php.
	'irc_header_title' => 'IRC Chat - BadnikZONE, #tcrf, #x',
	'irc_channels' => '#tcrf,#x',
	'irc_servers' => array('irc.badnik.zone', 'irc.rustedlogic.net', 'irc.tcrf.net'),

	// Primary admin, the person people are told to turn to if they are e.g. banned.
	'primary_admin_name' => 'Dada',
	'primary_admin_email' => 'tomato@tomatoland.org',

	// Ikachan! :D!
	'display_ikachan' => false,
	// Settings of the top menu. This is an array of routes: use only specified routes in lib/routing.php.
	// Custom routes can be set as well. The wiki is an example here.
	'top_menu_items' => array(
		array(
			array('@home', 'Main'),
			array('@memberlist', 'Memberlist'),
			array('@activeusers', 'Active users'),
			array('@calendar', 'Calendar'),
			array('http://tcrf.net', 'Wiki'),
			array('@irc', 'IRC Chat'),
			array('@online', 'Online users'),
		),
		array(
			array('@ranks', 'Ranks'),
			array('@faq', 'Rules/FAQ'),
			array('@stats', 'Stats'),
			array('@latestposts', 'Latest Posts'),
			array('@hex', 'Color Chart'),
			array('@smilies', 'Smilies'),
		),
	),
);
