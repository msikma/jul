<?php

// TODO: move?
function check_config() {
  // Check to see if the user has set up their settings.
  $config = $GLOBALS['jul_base_path'].'/lib/config.php';
  return is_file($config);
}

// TODO: move?
function get_data_table($data, $items = array()) {
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

// Actually runs the installer SQL to install the database.
function run_installer_sql() {
  global $sql;

  $all_ok = true;

  // Split the installer SQL by statements.
  $installer_sql = get_installer_sql();
  $installer_lines = explode(';', $installer_sql);
  foreach ($installer_lines as $line) {
    $line = trim($line);
    if (substr($line, 0, 2) === '--' || substr($line, 0, 3) === '/*!' || $line === '') {
      continue;
    }
    // Execute one statement.
    $query = $sql->query($line);
    $all_ok = $all_ok === false ? false : $query === true;
  }
  return $all_ok;
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
