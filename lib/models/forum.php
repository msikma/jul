<?php

/** Returns a forum's information by ID. */
function get_forum_by_id($id) {
  global $sql;
  $esc_id = intval($id);
  $forum = $sql->fetchq("SELECT * FROM forums WHERE id=$esc_id limit 1;");
  return $forum;
}

/** Returns the hot topics in a forum. */
function get_hot_count() {
  global $sql;
  $hotcount = $sql->resultq('SELECT hotcount FROM misc',0,0);
  return $hotcount;
}

/**
 * Returns an array with forum ID for keys, and last read date for values.
 * E.g. array('1' => '1554424019').
 */
function get_forum_read_date($userid)
{
  global $sql;
  if (!$userid) {
    return array();
  }
  $esc_userid = intval($userid);
  return $sql->getresultsbykey("SELECT forum,readdate FROM forumread WHERE user=$esc_userid", 'forum', 'readdate');
}
/**
 * Returns a string of attributes to be used in an edit/create forum SQL call.
 * The attributes will already be escaped, so the string can be included directly.
 */
function _forum_attributes($attributes) {
  // All accepted attributes.
  $valid_attributes = array('title', 'description', 'catid', 'minpower', 'minpowerthread',
    'minpowerreply', 'numthreads', 'numposts', 'forder', 'specialscheme', 'hidden', 'pollstyle');

  $attr_arr = array();
  foreach ($attributes as $k => $v) {
    if (!in_array($k, $valid_attributes) || trim($v) === '') {
      continue;
    }
    $esc_v = mysql_real_escape_string($v);
    $attr_arr[] = "`{$k}` = '{$esc_v}'";
  }
  return implode(",\n", $attr_arr);
}

function _update_forum_after_post($user_id, $post_id, $forum_id) {
  $esc_user_id = intval($user_id);
  $esc_post_id = intval($post_id);
  $esc_forum_id = intval($forum_id);
  run_query("
    update `forums`
    set `numthreads` = `numthreads` + 1,
    `numposts` = `numposts` + 1,
    `lastpostdate` = unix_timestamp(),
    `lastpostuser` = {$esc_user_id},
    `lastpostid` = {$esc_post_id}
    where `id` = {$esc_forum_id};
  ");
}

/**
 * Deletes a forum. Requires a 'mergeid', an ID of another forum to move its posts to.
 * TODO
 */
function delete_forum($id, $mergeid) {
  global $sql;

  $esc_id = intval($id);
  $esc_mergeid = intval($mergeid);

  if ($esc_id === 0) die('Attempted to delete forum ID '.$esc_id);
  if ($esc_mergeid === 0) die('Attempted to delete forum ID '.$esc_id.' with merge ID '.$esc_mergeid);

  $counts = $sql->fetchq("SELECT `numthreads`, `numposts` FROM `forums` WHERE `id`='$id'");
	$sql->query("UPDATE `threads` SET `forum`='$mergeid' WHERE `forum`='$id'");
	$sql->query("UPDATE `announcements` SET `forum`='$mergeid' WHERE `forum`='$id'");
	$sql->query("DELETE FROM `forummods` WHERE `forum`='$id'");
	$sql->query("DELETE FROM `forums` WHERE `id`='$id'");

	$lastthread = $sql->fetchq("SELECT * FROM `threads` WHERE `forum`='$mergeid' ORDER BY `lastpostdate` DESC LIMIT 1");
	$sql->query("UPDATE `forums` SET
		`numthreads`=`numthreads`+'{$counts['numthreads']}',
		`numposts`=`numposts`+'{$counts['numposts']}',
		`lastpostdate`='{$lastthread['lastpostdate']}',
		`lastpostuser`='{$lastthread['lastposter']}',
		`lastpostid`='{$lastthread['id']}'
	WHERE `id`='$mergeid'");
}

/**
 * Edits an existing forum.
 */
function edit_forum($id, $attributes) {
  global $sql;

  $values = _forum_attributes($attributes);
  $esc_id = intval($id);
  $sql->query("update `forums` set $values where `id` = '{$esc_id}';");
  return $id;
}

/**
 * Creates a new forum.
 */
function create_forum($attributes) {
  global $sql;

  $values = _forum_attributes($attributes);
  $sql->query("insert into `forums` set $values, `lastpostid` = '0';");
  $id	= mysql_insert_id();
  return $id;
}

/**
 * Edits a category.
 */
function edit_category($id, $name) {
  global $sql;

  $esc_id = intval($id);
  $esc_name = mysql_real_escape_string($name);
  $sql->query("update `categories` set `name` = '{$esc_name}' where `id` = '{$esc_id}';");
  return $id;
}

/**
 * Creates a new category.
 */
function create_category($name) {
  global $sql;

  $esc_name = mysql_real_escape_string($name);
  $sql->query("insert into `categories` (`name`) values ('{$esc_name}');");
  $id	= mysql_insert_id();
  return $id;
}
