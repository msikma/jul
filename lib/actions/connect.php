<?php

$is_external = is_external_entry_point();
$sql = new mysql();

$sql->connect($GLOBALS['jul_sql_settings']['host'], $GLOBALS['jul_sql_settings']['user'], $GLOBALS['jul_sql_settings']['pass']) or
    early_html_die('The MySQL server has exploded.', true, $is_external);

// Check if the database exists.
$selected_db = $sql->selectdb($sql_settings['name']);
if (!$selected_db) {
    $sql_name = mysql_real_escape_string($sql_settings['name']);
    $check = $sql->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$sql_name}';");
    $install_link = base_dir().'/install';
    if ($check->num_rows === 0) {
        early_html_die("
            <p>Connected to MySQL, but couldn't find the configured database.</p>
            <p>Make sure a database with the name <tt>{$sql_name}</tt> exists.</p>
        ", true, $is_external);
    }
    else {
        early_html_die('Another stupid MySQL error happened, panic', true, $is_external);
    }
}
