<?php

function check_config() {
  // Check to see if the user has set up their settings.
  $config = $GLOBALS['jul_base_path'].'/lib/config.php';
  return is_file($config);
}

function can_make_tables($connection) {
  // Create a table to see if we have the right to.
  $name = 'jul_installer_test_table';
  $sql = "
  create table `$name` (
    `string` varchar(255) collate utf8mb4_unicode_ci not null
  ) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;
  ";
  $result = mysql_query($sql);
  $row = mysql_fetch_assoc($result);
  $can_create_tables = $result;

  // Tear down the test table.
  $sql = "
  drop table `$name`;
  ";
  $result = mysql_query($sql);
  $row = mysql_fetch_assoc($result);
  $can_drop_tables = $result;

  return array('can_make' => $can_create_tables, 'can_drop' => $can_drop_tables);
}

function check_db() {
  // See if we can connect.
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
  $row = mysql_fetch_assoc($result);
  if (!isset($row['a']) || $row['a'] !== 'a') return array(false, mysql_error());

  // See if we have the right to make and drop tables.
  $rights = can_make_tables($c);
  if (!$rights['can_make'] && !$rights['can_drop']) return array(false, "Can't create or drop tables.");
  if (!$rights['can_make']) return array(false, "Can't create new tables.");
  if (!$rights['can_drop']) return array(false, "Can't drop tables.");

  return array(true);
}

function get_data_table($data, $items) {
  $html = "<table class='table data-table'>";
  foreach ($data as $k => $v) {
    if ((!empty($items) && in_array($k, $items)) || empty($items)) {
      $html .= "
        <tr>
          <th>$k</th>
          <td>$v</td>
        </tr>";
    }
  }
  $html .= '</table>';
  return $html;
}

// Checks whether Jul is already installed.
function check_already_installed() {
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
  $row = mysql_fetch_assoc($result);
  if ($row['id'] === '1') return true;

  return false;
}

// Returns the installer SQL dump.
function get_installer_sql() {
  $file = $GLOBALS['jul_base_path'].'/sql/jul.sql';
  return file_get_contents($file);
}

// Returns the tables created in an SQL string.
function get_sql_create_tables($sql) {
  preg_match_all('/create table `(.+?)`/i', $sql, $matches);
  if (!empty($matches)) {
    return $matches[1];
  }
  return null;
}
