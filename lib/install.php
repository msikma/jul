<?php

function check_config() {
  // Check to see if the user has set up their settings.
  $config = $GLOBALS['jul_base_path'].'/lib/config.php';
  return is_file($config);
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
