<?php

function base_dir() {
  return $GLOBALS['jul_base_dir'];
}

function base_path() {
  return $GLOBALS['jul_base_path'];
}

function to_home() {
  return base_dir();
}

/**
 * Returns the destination of a route string, as defined in lib/routing.php.
 * Routes start with a @ character. Anything other than a route is returned verbatim.
 * This means you can pass either a @route and get the proper route URL, or just pass a full URL by itself.
 */
function to_route($route) {
  if ($route[0] !== '@') return $route;
  return $GLOBALS['jul_routes'][$route][0];
}

/**
 * Returns route parameters, if any.
 */
function route_params($route) {
  if ($route[0] !== '@') return $route;
  return isset($GLOBALS['jul_routes'][$route][1]) ? $GLOBALS['jul_routes'][$route][1] : '';
}

/**
 * Returns an absolute link to an image file.
 */
function dir_images($file='') {
  return "{base_dir()}/images/{$file}";
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
 * Renders a simple table based on an array.
 */
function render_form_table($content) {
  $html = "
    <table class='table form-table table-margin' cellspacing='0'>
      <tbody>
  ";
  for ($a = 0; $a < count($content); ++$a) {
    $val = $content[$a];
    if ($val[0] === '---') {
      // Separator
      $html .= "
        <tr>
          <td class='tbl tdbgh font center'>&nbsp;</td>
          <td class='tbl tdbgh font center'>&nbsp;</td>
        </tr>
      ";
    }
    else {
      $label = $val[0];
      $val = $val[1];
      $html .= "
        <tr>
          <td class='tbl tdbg1 font center label'><b>{$label}</b></td>
          <td class='tbl tdbg2 font'>{$val}</td>
        </tr>
      ";
    }
  }
  $html .= "
      </tbody>
    </table>
  ";
  print($html);
}

/**
 * Renders a box with title and content.
 */
function render_box($content, $title='Notice') {
  $html = '
    <table class="table brightlinks table-margin" cellspacing="0">
      <tbody>
      '.($title ? ('
        <tr>
          <td class="tbl tdbgh font center">'.$title.'</td>
        </tr>
      ') : ('')).'
        <tr>
          <td class="tbl tdbg1 font center">'.$content.'</td>
        </tr>
      </tbody>
    </table>';
  print($html);
}
