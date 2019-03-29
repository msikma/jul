<?php

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
