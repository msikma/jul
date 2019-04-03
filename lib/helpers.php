<?php

function base_dir() {
  return $GLOBALS['jul_base_dir'];
}

function base_url() {
  return $GLOBALS['jul_base_url'];
}

function base_path() {
  return $GLOBALS['jul_base_path'];
}

/** Return the current person's IP address. */
function get_current_ip() {
  return $_SERVER['REMOTE_ADDR'];
}

/**
 * Removes the file extension from a filename or path.
 */
function remove_extension($fn) {
  $bits = explode('.', $fn);
  array_pop($bits);
  return implode('.', $bits);
}

/**
 * Used on $_GET, etc. arrays if we don't have the firewall installed.
 */
function addslashes_array($data) {
	if (is_array($data)){
		foreach ($data as $key => $value){
			$data[$key] = addslashes_array($value);
		}
		return $data;
	} else {
		return mysql_real_escape_string($data);
	}
}

/**
 * Retrieves information about image sets for emoticons or posticons.
 * These are directories containing an info.json file and sets of images.
 * Used in lib/emoticons.php and lib/posticons.php.
 */
function get_image_set($base, $local_dir) {
  $dir = new DirectoryIterator($base);
  $sets = array();
  foreach ($dir as $item) {
    if ($item->isDot()) {
      continue;
    }
    if (!$item->isDir()) {
      continue;
    }

    // Check whether an info.json is present in the directory.
    // If so it's a valid item.
    $name = $item->getFilename();
    $path = $local_dir.$name;
    $info = $base.$name.'/info.json';

    if (!is_file($info)) {
      continue;
    }

    // Extract info from the item.
    $data = json_decode(file_get_contents($info), true);
    if (empty($data) || !isset($data['name']) || empty($data['sets'])) {
      continue;
    }
    $data['slug'] = $name;
    $data['path'] = $path;
    $data['base'] = $GLOBALS['jul_base_dir'].'/static'.$local_dir.$name;
    $data['amount'] = 0;
    foreach ($data['sets'] as $set_name => $set) {
      foreach ($set['items'] as $n => $item) {
        $data['sets'][$set_name]['items'][$n]['slug'] = remove_extension($item['fn']);
      }
      $data['amount'] += count($set['items']);
    }
    $sets[$name] = $data;
  }

  return $sets;
}
