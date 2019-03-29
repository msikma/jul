<?php

/** Retrieves a user ID from a username. */
function username_to_user_id($name)
{
  global $sql;
  $u = $sql->resultq("SELECT id FROM users WHERE name='".mysql_real_escape_string($name)."'");
  if ($u < 1) {
      $u = -1;
  }

  return $u;
}

/** Logs in a user. Returns false if login failed. */
function check_login($name, $pass)
{
    global $sql;

    $esc_name = mysql_real_escape_string($name);
    $user = $sql->fetchq("SELECT id,password FROM users WHERE name='{$esc_name}'");

    if (!$user) {
        return -1;
    }
    if (!password_verify("{$pass}{$user['id']}", $user['password'])) {
        return -1;
    }

    return $user['id'];
}

/** Retrieves a user's IP by their user ID. */
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
