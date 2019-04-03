<?php

$default_posticon_set = 'saltw';
$active_posticon_set = '';

function get_posticons($checked_slug = '', $add_null_icon = false) {
  $icons = get_active_posticon_set();
  return get_posticons_list($icons, $checked_slug, $add_null_icon);
}

function get_posticons_list($icons, $checked_slug = '', $add_null_icon = false) {
  $list_initial = array();
  $list_more = array();
  $base = $icons['base'];

  // Adds an initial 'none' item.
  if ($add_null_icon) {
    $list_initial[] = '
      <label for="null-posticon" class="radio-icon null">
        <input type="radio" name="posticon" id="null-posticon" value="" '.(!$checked_slug ? 'checked' : '').' />
        (none)
      </label>
    ';
  }

  // Adds all applicable posticons.
  foreach ($icons['sets'] as $set) {
    foreach ($set['items'] as $item) {
      if ($item['legacy']) continue;
      $alt = $item['name'];
      $size = $item['size'];
      $width = $size[0];
      $height = $size[1];
      $src = $base.'/'.$set['name'].'/'.$item['fn'];
      $slug = $item['slug'];
      $checked = $item['slug'] === $checked_slug ? 'checked' : '';
      $html = '
        <label for="posticon-'.$slug.'" class="radio-icon">
          <input type="radio" name="posticon" id="posticon-'.$slug.'" value="'.$slug.'" '.$checked.'>
          <img src="'.$src.'" height="'.$height.'" width="'.$width.'" alt="'.$alt.'">
        </label>
      ';
      if ($set['initial']) $list_initial[] = $html;
      else $list_more[] = $html;
    }
  }

  return '
    <div class="radio-icons">
      <div class="initially-visible">'.implode('', $list_initial).'</div>
      <div class="initially-hidden">'.implode('', $list_more).'</div>
    </div>
  ';
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
  $GLOBALS['jul_posticon_sets'] = get_image_set($GLOBALS['jul_base_path'].'/static/posticons/', '/posticons/');
  $active_posticon_set = get_active_posticon_name();
}

// Load initial data.
get_posticon_data();
