<?php

# This is not where you input your database configuration.
# See config.php in the Git root instead.

if (!is_file("{$GLOBALS['jul_base_path']}/config.php")) {
	early_html_die('Could not find a configuration file. Make sure you have a <tt>lib/config.php</tt> file and it contains <tt>$sql_settings</tt> and <tt>$forum_settings</tt>. See <tt>lib/config.example.php</tt>.');
}

include("{$GLOBALS['jul_base_path']}/config.php");

// Merge defaults and user supplied config.
if (!isset($sql_settings) || !isset($forum_settings)) {
	early_html_die('Your forum settings are not correct. Make sure you have a <tt>lib/config.php</tt> file and it contains <tt>$sql_settings</tt> and <tt>$forum_settings</tt>. See <tt>lib/config.example.php</tt>.');
}
$GLOBALS['jul_sql_settings'] = array_merge($default_sql_settings, $sql_settings);
$GLOBALS['jul_sql_settings_safe'] = array_merge($sql_settings, array('pass' => '(hidden)'));
$GLOBALS['jul_settings'] = array_merge($default_forum_settings, $forum_settings);
// A list of all the 'regular' settings that users would want to customize.
// Doesn't include the very old legacy items. Used by the installer.
$GLOBALS['jul_common_settings'] = array('board_name', 'board_title', 'site_url', 'site_name');
