<?php

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

/**
 * Deletes a forum. Requires a 'mergeid', an ID of another forum to move its posts to.
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
