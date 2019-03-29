<?php

/**
 * Registers the main admin account. Used only once during install.
 */
function register_admin_account($username, $password) {
  // TODO: add check for installer here.
  return register_account($username, $password, $_SERVER['REMOTE_ADDR'], 3);
}

/**
 * Registers a new user.
 */
function register_account($username, $password, $ip, $powerlevel = 0) {
  global $sql;

  $esc_username = mysql_real_escape_string($username);
  $esc_ip = mysql_real_escape_string($ip);
  $esc_powerlevel = intval($powerlevel);
  $reason = '';

  $q = $sql->query("
    insert into `users` set
    `name` = '{$esc_username}',
    `password` = '',
    `powerlevel` = '{$esc_powerlevel}',
    `postsperpage` = '20',
    `threadsperpage` = '50',
    `lastip` = '{$esc_ip}',
    `scheme` = '0',
    `lastactivity` = utc_timestamp(),
    `regdate` = utc_timestamp()
  ");
  if (!$q) $reason = mysql_error();

  // Fetch the next ID so we can generate the password hash.
  $id = mysql_insert_id();
  $esc_password_hashed = getpwhash($password, $id);

  // Set the password.
  $q = $sql->query("
    update `users` set
    `password` = '{$esc_password_hashed}'
    where `id` = '{$id}'
  ");
  if (!$q) $reason = mysql_error();

  // Add the user's RPG minigame information.
  $q = $sql->query("
    insert into `users_rpg` (`uid`)
    values ('{$id}')
  ");
  if (!$q) $reason = mysql_error();

  return array($reason === '', $reason);
}

/**
 * Checks a user's registration details for duplicate name or IP.
 * Returns an array containing booleans 'username_exists' and 'ip_exists'.
 */
function check_registration($name, $ip) {
  global $sql;
  $esc_name = mysql_real_escape_string($name);
  $esc_ip = mysql_real_escape_string($ip);

  // Check by name.
  $users = $sql->query("select * from `users` where `name` = '{$esc_name}';");
  $user_row = $sql->fetch($users);
  $username_exists = $user_row !== false;
  $username_id = $username_exists ? (int)$user_row['id'] : null;

  // Check by IP.
  $users = $sql->query("select * from `users` where `lastip` = '{$esc_ip}';");
  $user_row = $sql->fetch($users);
  $ip_exists = $user_row !== false;
  $ip_id = $username_exists ? (int)$user_row['id'] : null;

  return array(
    'username_exists' => $username_exists,
    'username_id' => $username_id,
    'ip_exists' => $username_exists,
    'ip_id' => $ip_id,
  );
}
