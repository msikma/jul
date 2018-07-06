<?php

/**
 * An old check to see if the user is using a proxy.
 */
function is_using_proxy() {
  $ch = curl_init();
  curl_setopt ($ch,CURLOPT_URL, "http://". $_SERVER['REMOTE_ADDR']);
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 3); // <---- HERE
  curl_setopt ($ch, CURLOPT_TIMEOUT, 5); // <---- HERE
  $file_contents = curl_exec($ch);
  curl_close($ch);

  if (stristr($file_contents, "proxy")
  	|| stristr($file_contents, "forbidden")
  	|| stristr($file_contents, "it works")
  	|| stristr($file_contents, "anonymous")
  	|| stristr($file_contents, "filter")
  	|| stristr($file_contents, "panel")) {
    return true;
  }
  return false;
}

/**
 * Registers the main admin account. Used only once during install.
 */
function register_admin_account($username, $password) {
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

/**
 * Bans a user.
 */
function ban_user($user_id, $reason, $ip_ban = false, $permanent = false) {
  global $sql;
  $esc_reason = mysql_real_escape_string($reason);
  $user_id = intval($user_id);
  $permanent = $permanent === true ? 1 : 0;
  $sql->query("
    update `users` set
    `banned` = '1',
    `banreason` = '{$esc_reason}',
    `banperm` = '{$permanent}',
    `bandatetime_utc` = utc_timestamp()
    where `id` = '{$user_id}';
  ");
  // IP ban the user too if desired.
  if ($ip_ban) {
    ban_user_ip($user_id, $reason, $permanent);
  }
}

/**
 * Bans a user's IP address by their user ID.
 */
function ban_user_ip($user_id, $reason, $permanent = false) {
  $ip = get_user_ip($user_id);
  ban_ip($ip, $reason, $permanent);
}

/**
 * Retrieves a user's IP by their user ID.
 */
function get_user_ip($user_id) {
  global $sql;
  $user_id = intval($user_id);
  $users = $sql->query("
    select `lastip`
    from `users`
    where `id` = '{$user_id}';
  ");
  $user = $sql->fetch($users);
  return $user['lastip'];
}

/**
 * Searches the user database for anyone who has a specific IP.
 * Anyone found has their user account banned.
 */
function ban_all_users_by_ip($ip_array, $reason, $permanent = false) {
  global $sql;
  // Wrap if we get a string.
  if (is_string($ip_array)) {
    $ip_array = array($ip_array);
  }
  $esc_reason = mysql_real_escape_string($reason);
  $ips = array();
  foreach ($ip_array as $ip) {
    $esc_ip = mysql_real_escape_string($ip);
    $ips[] = "'{$esc_ip}'";
  }
  $ips = implode(', ', $ips);
  $permanent = $permanent === true ? 1 : 0;
  $sql->query("
    update `users` set
    `banned` = '1',
    `banreason` = '{$esc_reason}',
    `banperm` = '{$permanent}',
    `bandatetime_utc` = utc_timestamp()
    where `lastip` in ({$ips});
  ");
}

/**
 * Bans an IP from accessing the forum.
 */
function ban_ip($ip, $reason, $permanent = false) {
  global $sql;
  $esc_ip = mysql_real_escape_string($ip);
  $esc_reason = mysql_real_escape_string($reason);
  $permanent = $permanent === true ? 1 : 0;
  $sql->query("
    insert into `ipbans` set
    `ip` = '{$esc_ip}',
    `perm` = '{$permanent}',
    `reason` = '{$esc_reason}',
    `datetime_utc` = utc_timestamp();
  ");
}
