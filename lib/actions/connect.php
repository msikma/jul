<?php

$sql = new mysql();

$sql->connect($GLOBALS['jul_sql_settings']['host'], $GLOBALS['jul_sql_settings']['user'], $GLOBALS['jul_sql_settings']['pass']) or
    early_html_die('The MySQL server has exploded.', true);
$sql->selectdb($sql_settings['name']) or early_html_die('Another stupid MySQL error happened, panic', true);
