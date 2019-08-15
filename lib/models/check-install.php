<?php

// See if we can connect.
function check_db() {
  $host = $GLOBALS['jul_sql_settings']['host'];
  $user = $GLOBALS['jul_sql_settings']['user'];
  $pass = $GLOBALS['jul_sql_settings']['pass'];
  $name = $GLOBALS['jul_sql_settings']['name'];

  $c = mysql_connect($host, $user, $pass);
  if (!$c) return array(false, 'Invalid username/password or host.');
  $n = mysql_select_db($name);
  if (!$n) return array(false, mysql_error());

  // Test to see if we can make any queries at all.
  $sql = "select 'a';";
  $result = mysql_query($sql);
  if ($result === false) {
    return array(false, mysql_error());
  }
  $row = mysql_fetch_assoc($result);
  if (!isset($row['a']) || $row['a'] !== 'a') return array(false, mysql_error());

  // See if we have the right to make and drop tables.
  $rights = can_make_tables($c);
  if (!$rights['can_make'] && !$rights['can_drop']) return array(false, "Can't create or drop tables.");
  if (!$rights['can_make']) return array(false, "Can't create new tables.");
  if (!$rights['can_drop']) return array(false, "Can't drop tables.");

  // Check if the configuration is correct.
  $config_details = has_good_config($c);
  if ($config_details['strict_tt']) return array(false, "Disable STRICT_TRANS_TABLES in the MySQL config.");

  return array(true);
}

// Checks whether Jul is already installed.
function check_installed() {
  $host = $GLOBALS['jul_sql_settings']['host'];
  $user = $GLOBALS['jul_sql_settings']['user'];
  $pass = $GLOBALS['jul_sql_settings']['pass'];
  $name = $GLOBALS['jul_sql_settings']['name'];

  $c = mysql_connect($host, $user, $pass);
  if (!$c) return false;
  $n = mysql_select_db($name);
  if (!$n) return false;

  // Check whether we're installed by seeing whether the admin user exists.
  $sql = "select * from `users` where `id` = 1;";
  $result = mysql_query($sql);
  if (!$result) return false;
  $row = mysql_fetch_assoc($result);
  if ($row['id'] === '1') return true;

  return false;
}

// Create a table to see if we have the right to.
function can_make_tables($connection) {
  $name = 'jul_installer_test_table';
  $sql = "
  create table `$name` (
    `string` varchar(255) collate utf8mb4_unicode_ci not null
  ) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;
  ";
  $result = mysql_query($sql);
  $can_create_tables = $result;

  // Tear down the test table.
  $sql = "
  drop table `$name`;
  ";
  $result = mysql_query($sql);
  $can_drop_tables = $result;

  return array('can_make' => $can_create_tables, 'can_drop' => $can_drop_tables);
}

// Checks if we can reach the database.
// If not, display the database error.
function error_on_bad_db() {
  $msg = check_db();
  if ($msg[0] === false) {
    include_error('database-error', true, array('db_error' => $msg[1]));
    exit;
  }
}

// Checks if the forum is installed.
// If not, display a link to the installer.
function error_on_bad_install() {
  if (check_installed() === false) {
    include_error('install-error', true, array('static_css' => true, 'error_header' => 'Installer'));
    exit;
  }
}

// Checks whether the config is set properly.
function has_good_config($connection) {
  // Only thing we need to check for now.
  return array(
    'strict_tt' => has_strict_trans_tables($connection) === true
  );
}

// Checks whether STRICT_TRANS_TABLES is enabled (it has to be disabled).
function has_strict_trans_tables($connection) {
  $sql = "select @@sql_mode as mode;";
  $row = mysql_fetch_assoc(mysql_query($sql));
  return strpos($row['mode'], 'STRICT_TRANS_TABLES') !== false;
}
