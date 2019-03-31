<?php

// Names of the user groups.
$GLOBALS['jul_user_groups'] = array(
  '-2' => 'Permabanned',
  '-1' => 'Banned',
  'Normal',
  'Normal +',
  'Moderator',
  'Administrator',
  'Sysadmin'
);

/** Returns the user data of a logged in user. */
function get_logged_in_user() {
  $c = get_user_cookies();
  $loguser = get_user_by_id($c['loguserid']);
  $verifyid = intval(substr($c['logverify'], 0, 1));
  $verifyhash = create_verification_hash($verifyid, $loguser['password']);

  // Compare what we just created with what the cookie says, assume something is wrong if it doesn't match.
  if ($verifyhash !== $c['logverify']) {
    $loguser = null;
  }

  return $loguser;
}

function is_admin_user() {
  return false;
}

/** Logs in a user. Returns false if login failed. */
function check_login($name, $pass) {
  global $sql;

  $esc_name = mysql_real_escape_string($name);
  $user = $sql->fetchq("
    select `id`, `password`
    from `users`
    where `name` = '{$esc_name}';
  ");

  if (!$user) {
    return -1;
  }
  if (!password_verify("{$pass}{$user['id']}", $user['password'])) {
    return -1;
  }

  return $user['id'];
}

function update_user_post_count($user) {
  if (!$user || $user['id']) return false;
  $count = intval($user['posts']);
  $new_count = $count + 1;
  $esc_id = intval($user['id']);
  $user = fetch_query("
    update `users`
    set `posts` = {$new_count}, `lastposttime` = unix_timestamp()
    where `id` = '{$esc_id}';
  ");
  return $user;
}

/** Check if a user's posting too fast. Returns true if the user has broken the limit. */
function check_user_posting_limit($user) {
  if (!$user) $user = get_logged_in_user();
  return $user['lastposttime'] > (ctime() - 30);
}

/** Check if a user's authorized to post in a specific forum. */
function check_user_forum_authority($user, $forum) {
  if (!$user) $user = get_logged_in_user();
  return $user['powerlevel'] >= $forum['minpowerthread'];
}

/** Return whether a user is permitted to post a certain message. */
function can_user_post($user, $forum, $subject, $message) {
  $limit_hit = check_user_posting_limit($user);
  $authorized = check_user_forum_authority($user, $forum);
  $is_banned = $user['powerlevel'] < 0;

  return ($user['id'] && !$is_banned && $subject && $message && $forum['title'] && $authorized && !$limit_hit);
}

/** Retrieves a user's data by their ID. */
function get_user_by_id($user_id) {
  $user_id = intval($user_id);
  $user = fetch_query("
    select *
    from `users`
    where `id` = '{$user_id}';
  ");
  return $user;
}

/** Retrieves a user's data by their username. */
function get_user_by_name($username) {
  $esc_username = mysql_real_escape_string($username);
  $user = fetch_query("
    select *
    from `users`
    where `name` = '{$esc_username}';
  ");
  return $user;
}

/** Retrieves a user's IP by their user ID. */
function get_user_ip($user_id) {
  $user = get_user_by_id($user_id);
  return $user['lastip'];
}

/** Returns cookies that are present if the user is logged in. */
function get_user_cookies() {
  return array(
    'loguserid' => $_COOKIE['loguserid'] ? intval($_COOKIE['loguserid']) : null,
    'logverify' => $_COOKIE['logverify'] ? strval($_COOKIE['logverify']) : null
  );
}

/** Password hashing function. */
function get_password_hash($name, $id) {
  return password_hash("{$name}{$id}", PASSWORD_DEFAULT);
}

/** Used in checking user login. TODO: document and check. */
function create_verification_hash($n, $pw) {
  $ipaddr = explode('.', $_SERVER['REMOTE_ADDR']);
  $vstring = 'verification IP: ';

  $tvid = $n;
  while ($tvid--) {
    $vstring .= array_shift($ipaddr).'|';
  }

  // don't base64 encode like I do on my fork, waste of time (honestly)
  return $n.sha1($pw.$vstring, false);
}
