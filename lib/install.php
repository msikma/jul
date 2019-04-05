<?php

// TODO: move?
function check_config() {
  // Check to see if the user has set up their settings.
  $config = $GLOBALS['jul_base_path'].'/config.php';
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

// Generates a set of testing forums.
function _make_testing_forums() {
  $cat_id = create_category('Discussion');
  $id = create_forum(array(
    'title' => 'General chat',
    'description' => 'This is the ジェネラル chat.',
    'catid' => $cat_id,
  ));
  $id = create_forum(array(
    'title' => 'Entertainment and Media',
    'description' => 'Talk about music, literature, art, movies, games and every other form of entertainment.',
    'catid' => $cat_id,
  ));
  $id = create_forum(array(
    'title' => 'Technology and Programming',
    'description' => 'Computers, applications and technology.',
    'catid' => $cat_id,
  ));
  // ---
  $cat_id = create_category('Creativity');
  $id = create_forum(array(
    'title' => 'Game Design & Demos',
    'description' => 'Discuss the process of making games, show off your work and get feedback.',
    'catid' => $cat_id,
  ));
  $id = create_forum(array(
    'title' => 'Artistry',
    'description' => 'Discuss the creation of music, graphic art, literature and other endeavors.',
    'catid' => $cat_id,
  ));
  $cat_id = create_category('Projects');
  $id = create_forum(array(
    'title' => 'Salt Dev',
    'description' => 'THIS IS SO MUCH MORE FUN THAN MY LAST GULAG',
    'catid' => $cat_id,
  ));
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
