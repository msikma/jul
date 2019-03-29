<?php

$default_posticon_set = 'saltw';
$active_posticon_set = '';

function get_posticons() {
  return get_active_posticon_set();
}

function get_active_posticon_set() {
  $set = get_active_posticon_name();
  return $GLOBALS['jul_posticon_sets'][$set];
}

function get_active_posticon_name() {
  global $active_posticon_set, $default_posticon_set;
  if ($active_posticon_set === '' || $active_posticon_set === null) {
    return forum_setting_get('posticon_set', $default_posticon_set);
  }
  else {
    return $active_posticon_set;
  }
}

function set_active_posticon_set($set) {
  forum_setting_set('posticon_set', $set);
}

function get_posticon_data() {
  global $active_posticon_set;
  $GLOBALS['jul_posticon_sets'] = get_image_set($GLOBALS['jul_base_path'].'/static/posticons/', '/posticons/');;
  $active_posticon_set = get_active_posticon_name();
}

// Load initial data.
get_posticon_data();
