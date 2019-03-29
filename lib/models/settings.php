<?php

function forum_setting_set($name, $value) {
  global $sql;

  if (!$name || !$value) return false;

  $esc_name = mysql_real_escape_string($name);
  $esc_value = mysql_real_escape_string($value);
  $sql->query("select name, value from `settings` where `name` = '{$esc_name}';");
  $sql->query("
    insert into `settings`
    (name, value)
    values
    ('{$esc_name}', '{$esc_value}')
    on duplicate key update
    name='{$esc_name}', value='{$esc_value}';
  ");
}

function forum_setting_get($name, $fallback = null) {
  global $sql;

  if (!$name) return false;

  try {
    $esc_name = mysql_real_escape_string($name);
    $query = $sql->query("select name, value from `settings` where `name` = '{$esc_name}';");
    $setting = $sql->fetch($query);
    return $setting !== false ? $setting['value'] : $fallback;
  }
  catch (Exception $e) {
    return $fallback;
  }
}
