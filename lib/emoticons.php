<?php

$default_emoticon_set = 'saltw';
$active_emoticon_set = '';

/**
 * Prints a table of emoticons.
 */
function emoticon_table() {
  return make_emoticon_selector(false);
}

/**
 * Returns the actual emoticon data from the set that is currently active.
 */
function get_active_emoticon_set() {
  $set = get_active_emoticon_name();
  return $GLOBALS['jul_emoticon_sets'][$set];
}

/**
 * Returns the currently active emoticon set name.
 */
function get_active_emoticon_name() {
  global $active_emoticon_set, $default_emoticon_set;
  if ($active_emoticon_set === '' || $active_emoticon_set === null) {
    return forum_setting_get('emoticon_set', $default_emoticon_set);
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
  $img = $set['emoticons'][$code];
  $url = $GLOBALS['jul_base_dir'].'/static/emoticons/'.$set['slug'].'/'.$img;
  return $url;
}

/**
 * Returns an emoticon selector that doesn't use a <table> tag.
 * Emoticons have different sizes, so this is the best way to display them.
 */
function make_emoticon_selector($display_only=true) {
  $full_set = get_active_emoticon_set();
  $selector_html = '<div class="emoticon-selector hide-extra">'."\n";

  foreach ($full_set['sets'] as $set) {
    $display_set = false;
    
    $html = '<div class="emoticon-set '.($set['initial'] ? 'initially-visible' : 'initially-hidden').'" data-emoticon-set="'.htmlspecialchars($set['name']).'">'."\n";
    $path = $GLOBALS['jul_static_path'].$full_set['path'].'/'.$set['name'];
    foreach ($set['items'] as $item) {
      // Icons can be 'legacy' if we want to display them in existing posts
      // but not allow them to be used in new posts.
      if ($item['legacy']) continue;
      else $display_set = true;

      // $item is e.g. array('fn' => 'woop.gif', 'code' => ':woop:', 'size' => array(31, 24), 'legacy' => false)
      $src = $path.'/'.$item['fn'];
      $code = $item['code'];
      $width = $item['size'][0];
      $height = $item['size'][1];

      $img = '<img src="'.$src.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'" />';
      $link = '<a href="#" class="emoticon-item">'.$img.'</a>'."\n";

      $html .= $link;
    }
    $html .= '</div>'."\n";

    if ($display_set) {
      $selector_html .= $html;
    }
  }

  $selector_html .= '</div>';

  return $selector_html;
}

/**
 * Returns a table of all emoticons for display purposes.
 * Legacy, since emoticons have wildly varying sizes so a table doesn't work that well.
 * 
 * TODO: remove.
 */
function make_emoticon_table($display_only=true, $width=3) {
  $set = get_active_emoticon_set();
  $t = '<table class="emoticon-table">';

  $sets = $set['sets'];

  $img_prefix = $GLOBALS['jul_static_path'].$set['path'];

  foreach ($sets as $set) {
    $items = $set['items'];

    $t .= "\n<tbody data-emoticon-set=\"".htmlspecialchars($set['name'])."\">\n";

    $z = count($items);
    $a = 0;
    while (true) {
      $t .= "<tr>\n";
      for ($b = 0; $b < $width; ++$b) {
        // e.g. array('fn' => 'woop.gif', 'code' => ':woop:', 'size' => array(31, 24), 'legacy' => false)
        $img = $items[$a];
        // Don't display this emoticon in the table if it's 'legacy' (not for new posts).
        if (!$img || $img['legacy']) {
          $a += 1;
          $t .= "<td></td>\n";
          continue;
        }
        $fn_alt = reset(explode('.', $img['fn']));
        $alt = htmlspecialchars($img['alt'] ? $img['alt'] : $fn_alt);
        $code = $img['code'];
        $size = $img['size'];
        $img = '<img src="'.$img_prefix.'/'.$set['name'].'/'.$img['fn'].'" width="'.$size[0].'" height="'.$size[1].'" alt="'.$alt.'" />';
        $link = '<a href="#" class="emoticon-add" data-emoticon="'.$code.'" title="'.$alt.'">'.$img.'</a>';
        $t .= '<td>'.$link."</td>\n";
        $a += 1;
      }
      $t .= '</tr>';
      
      if ($a >= $z) {
        break;
      }
    }

    $t .= '</tbody>';
  }
  $t .= '</table>';

  return $t;
}

/**
 * Retrieves emoticon sets from the static directory.
 */
function get_emoticon_data() {
  global $active_emoticon_set;
  $GLOBALS['jul_emoticon_sets'] = get_image_set($GLOBALS['jul_base_path'].'/static/emoticons/', '/emoticons/');
  $active_emoticon_set = get_active_emoticon_name();
}

// Load initial data.
get_emoticon_data();
