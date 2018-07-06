<?php

$active_emoticon_set = '';

/**
 * Prints a table of emoticons.
 */
function emoticon_table() {
  return make_emoticon_table(false, 5);
}

/**
 * Returns the currently active emoticon set.
 */
function get_active_emoticon_set() {
  global $active_emoticon_set;
  if ($active_emoticon_set === '') {
    try {
      $set = forum_setting_get('emoticon_set');
      return $GLOBALS['jul_emoticon_sets'][$set];
    }
    catch (Exception $e) {
      // If we can't connect to the database (e.g. before install).
      return 'phpbb';
    }
  }
  else {
    return $active_emoticon_set;
  }
}

/**
 * Changes the currently active emoticon set.
 */
function set_active_emoticon_set($set) {
  forum_setting_set('emoticon_set', $set);
}

/**
 * Returns the emoticon image URL for a specific code, e.g. ':)'.
 */
function get_emoticon($code) {
  global $active_emoticon_set;
  $set = $active_emoticon_set;
  $img = $set['smilies'][$code];
  $url = $GLOBALS['jul_base_dir'].'/static/smilies/'.$set['slug'].'/'.$img;
  return $url;
}

/**
 * Returns a table of all emoticons for display purposes.
 */
function make_emoticon_table($display_only=true, $width=3) {
  global $active_emoticon_set;
  $set = $active_emoticon_set;
  $list = array_keys($set['smilies']);

  $t = '<table class="emoticon-table">';

  $a = 0;
  $z = count($set['smilies']);
  while (true) {
    $t .= '<tr>';
    for ($n = 0; $n < $width; ++$n) {
      if ($a === $z - 1) {
        break(2);
      }
      $code = $list[++$a];
      $img = get_emoticon($code);
      if ($display_only) {
        $t .= '<th>'.$code.'</th><td><img src="'.$img.'" /></td>';
      }
      else {
        $t .= '<td><a href="#" class="emoticon-add" data-emoticon="'.htmlspecialchars($code).'" title="'.htmlspecialchars($code).'"><img src="'.$img.'" /></a></td>';
      }
    }
    $t .= '</tr>';
  }
  $t .= '</table>';
  return $t;
}

/**
 * Retrieves emoticon sets from the static directory.
 */
function get_emoticon_data() {
  global $active_emoticon_set;
  $smdir = '/static/smilies/';
  $path = $GLOBALS['jul_base_path'].$smdir;
  $dir = new DirectoryIterator($path);
  $emoticon_sets = array();
  foreach ($dir as $fileinfo) {
    if ($fileinfo->isDot()) {
      continue;
    }
    if (!$fileinfo->isDir()) {
      continue;
    }
    $name = $fileinfo->getFilename();
    $setpath = $smdir.$name.'/';
    $info = $path.$name.'/info.json';
    if (!is_file($info)) {
      continue;
    }
    $info = json_decode(file_get_contents($info), true);
    if (empty($info) || !isset($info['name']) || empty($info['smilies'])) {
      continue;
    }
    $info['slug'] = $name;
    $info['path'] = $setpath;
    $info['amount'] = count($info['smilies']);
    $emoticon_sets[$name] = $info;
  }
  $GLOBALS['jul_emoticon_sets'] = $emoticon_sets;
  $active_emoticon_set = get_active_emoticon_set();
}

// Load emoticon data.
get_emoticon_data();
