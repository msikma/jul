<?php

/**
 * Forum configuration. Values entered here will override those from 'lib/defaults.php'.
 */

$sql_settings = array(
  // MySQL connection options.
  'host' => 'localhost',
  'user' => '',
  'pass' => '',
  'name' => '', // Database name
);

$forum_settings = array(
  // Board settings.
  'board_name' => 'Board Name',
  'board_title' => 'Board Title',
  'site_url' => 'http://example.com',
  'site_name' => 'Site Name',
  // Base URL used for all links, e.g. 'http://myforum.com' (no trailing slash).
  // Will use links like '/new-thread/1' if set to an empty string
  // (while respecting any base directory that might be set).
  'base_url' => '',
);
