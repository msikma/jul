<?php

/**
 * Searches one of the views directories and returns the filenames of everything there.
 * All of these files can be used directly as route.
 */
function find_route_dir($no_index=true, $subdir='') {
  $path = $GLOBALS['base_dir'].'/views/'.$subdir;
  $dir = new DirectoryIterator($path);
  $routes = array();
  foreach ($dir as $fileinfo) {
    if ($fileinfo->isDot()) {
      continue;
    }
    $route_name = trim(remove_extension($fileinfo->getFilename()));
    if ($route_name === '') {
      continue;
    }
    // Ignore the index route by default, since we bind that one manually.
    if ($route_name === 'index' && $no_index) {
      continue;
    }
    $routes[] = array($subdir.$route_name);
  }
  sort($routes);
  return $routes;
}

/**
 * Returns all files in the /views/ directory and in several subdirectories.
 */
function find_route_files() {
  $root = find_route_dir(true, '');
  $ext = find_route_dir(true, 'ext/');
  $theme = find_route_dir(true, 'theme/');
  return array_merge($root, $ext, $theme);
}

function route_matches($route, $path) {
  if ($route === $path) return true;
  return false;
}

/**
 * Returns information about the user's requested route.
 */
function get_request_route() {
  // Retrieve the user's requested path, minus the base dir.
  $base = str_replace('/', '\/', preg_quote($GLOBALS['jul_base_dir']));
  $path = preg_replace("/^{$base}/", '', $_SERVER['REQUEST_URI']);

  // Remove the 'views' segment if it's present.
  $path = preg_replace('/^\/views\//', '', $path);

  // Remove any remaining slashes to be sure, unless it's index.
  $path = trim($path, '/');
  $path = $path === '' ? '/' : $path;

  // Remove the query string and store it separately.
  $info = parse_url($path);
  $path = trim($info['path']);
  parse_str($info['query'], $query);

  // Finally, remove .php.
  $path = preg_replace('/\.php$/', '', $path);

  // Find which route belongs to this path.
  foreach ($GLOBALS['jul_views'] as $view) {
    if ($view[0] === $path) {
      return array(
        'file' => $view[1] ? $view[1] : $view[0],
        'path' => $path,
        'query' => $query
      );
    }
  }

  // If no route was found, see if there's a redirect.
  foreach ($GLOBALS['jul_redirects'] as $redir) {
    if ($redir[0] === $path) {
      return array(
        'redirect' => $redir[1],
        'path' => $path,
        'query' => $query
      );
    }
  }

  // If no route was found, reach the 404.
  return array(
    'error' => '404'
  );
}

// Locate all filenames in the /views/ directory, and add them as routes.
// All these files are added to the views array like this: array('routename')
// In doing so, we will simply load them when there is an exact match.
$file_routes = find_route_files();

// Other routes, including the index.
$other_routes = array(
  array('/', 'index')
);
$GLOBALS['jul_views'] = array_merge($file_routes, $other_routes);

// Redirects.
$GLOBALS['jul_redirects'] = array(
  array('index', '/')
);

// I don't know. Maybe someday these can be nice URLs.
$GLOBALS['jul_routes'] = array(
  '@home' => "{$GLOBALS['jul_base_dir']}/",
  '@memberlist' => "{$GLOBALS['jul_views_path']}/memberlist.php",
  '@activeusers' => "{$GLOBALS['jul_views_path']}/activeusers.php",
  '@calendar' => "{$GLOBALS['jul_views_path']}/calendar.php",
  '@irc' => "{$GLOBALS['jul_views_path']}/irc.php",
  '@online' => "{$GLOBALS['jul_views_path']}/online.php",
  '@ranks' => "{$GLOBALS['jul_views_path']}/ranks.php",
  '@faq' => "{$GLOBALS['jul_views_path']}/faq.php",
  '@stats' => "{$GLOBALS['jul_views_path']}/stats.php",
  '@latestposts' => "{$GLOBALS['jul_views_path']}/latestposts.php",
  '@hex' => "{$GLOBALS['jul_views_path']}/hex.php",
  '@smilies' => "{$GLOBALS['jul_views_path']}/smilies.php",
);
