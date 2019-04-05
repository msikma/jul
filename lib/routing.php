<?php

/**
 * Searches one of the views directories and returns the filenames of everything there.
 * All of these files can be used directly as route.
 */
function find_route_dir($no_index=true, $subdir='') {
  $path = $GLOBALS['jul_base_path'].'/views/'.$subdir;
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

  // Remove any remaining slashes to be sure, unless it's index.
  $path = trim($path, '/');
  $path = $path === '' ? '/' : $path;

  // Remove the query string and store it separately.
  $info = parse_url($path);
  $path = trim($info['path']);
  parse_str($info['query'], $query);

  // Finally, remove .php.
  $path = preg_replace('/\.php$/', '', $path);

  // Now we should have a path that we can relate back to a route.
  // For example, if the path is 'forum/1' then we can see that this is
  // the @forum path with ID '1'.
  // Attempt to extract a valid route with data.
  $route_data = extract_route($path, $query);

  // If a valid route was found, return that.
  if ($route_data) {
    return $route_data;
  }

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

// Returns a simple route back home.
function to_home() {
  return base_dir();
}

/**
 * Returns a route string for use in links.
 * Routes start with a @ character. Anything other than a route is returned verbatim.
 * This means you can pass either a @route and get the proper route URL, or just pass a full URL by itself.
 *
 * To generate a route with e.g. an ID in it, pass data in the second array;
 * in most cases this will just be an ID, so this function can be called in one of two ways:
 *
 *    route('@routename', 5);
 *    route('@routename', array('id' => 5, 'any_other' => 'data'));
 *
 * Any query string data will be tacked on the end from the third argument.
 */
function route($route, $data = 0, $query = array(), $base = null) {
  if ($route[0] !== '@') return $route;
  // Note: apply the base URL as defined in <paths.php> by default.
  if (!$base) $base = $GLOBALS['jul_base_url'];
  $route_data = $GLOBALS['jul_routes'][$route];

  // Return the data for a 404 if this route isn't found.
  if (!$route_data) return route('@error', 404);

  // Decorate the route with data, e.g. the ID of a topic or message.
  // This function can be run either as
  $route_path = decorate_route($route_data, $data && is_numeric($data) ? array('id' => $data) : $data);

  // Add on query data.
  $query_str = !empty($query) ? '?'.http_build_query($query) : '';

  return "{$base}{$route_path}{$query_str}";
}

/**
 * Redirects the user to a specified URL and exits.
 */
function redirect_exit($url) {
  header('Location: '.$url);
  exit;
}

/**
 * Essentially, this does the opposite of decorate_route().
 * It takes a path string and returns a matching route with data.
 * If no valid route is found, false is returned.
 *
 * Note: the path will be given as e.g. 'forum/1', without leading slash.
 */
function extract_route($path, $query) {
  foreach ($GLOBALS['jul_routes'] as $name => $route) {
    $match_re = $route['match'] ? $route['match'] : '/'.preg_quote(trim($route['path'], '/'), '/').'/';
    $match_segments = $route['match_segments'];
    if (!$match_re) continue;

    $data = array();
    $valid = preg_match_all($match_re, $path, $matches);
    if (!$valid) continue;
    if ($matches[1]) {
      foreach ($matches[1] as $n => $match) {
        $data[$match_segments[$n]] = $match;
      }
    }
    return array_merge($route, array('request' => array('path' => $path, 'data' => $data, 'query' => $query)));
  }
  return false;
}

/**
 * Adds data to a route.
 */
function decorate_route($route, $data = array()) {
  if (!$data) {
    // Return 'clean' variant of the path if no data is passed.
    return $route['path_clean'] ? $route['path_clean'] : $route['path'];
  }

  // Replace named variables with our data.
  $decorated = preg_replace_callback(
    '/\{(.*)\}/',
    function ($matches) use (&$data) {
      $match = $data[$matches[1]];
      return $match ? $match : '';
    },
    $route['path']
  );

  return $decorated;
}

/**
 * Returns route parameters, if any.
 */
function route_params($route) {
  if ($route[0] !== '@') return $route;
  return isset($GLOBALS['jul_routes'][$route][1]) ? $GLOBALS['jul_routes'][$route][1] : '';
}

// Runs some simple preprocessing on our routes.
function preprocess_routes($routes) {
  $processed_routes = array();
  foreach ($routes as $k => $v) {
    // Retrieve named path segments. E.g. '/forum/{id}/topic/{topic_id}' yields ['id', 'topic_id'].
    preg_match_all('/\{(.+?)\}/', $v['path'], $matches);
    $segments = array();
    foreach ($matches[1] as $match) {
      $segments[] = $match;
    }
    // Add the path segments and the target filename to the route info.
    $route_info = array('match_segments' => $segments, 'file' => $v['file'] ? $v['file'] : ltrim($k, '@'));
    // Ensure all other values (noindex, admin, etc.) are present and cast to the right type.
    $route_defaults = array('noindex' => !!$v['noindex'], 'admin' => !!$v['admin'], 'path_clean' => $v['path_clean'] ?? null, 'match' => $v['match'] ?? null);
    $processed_routes[$k] = array_merge($v, $route_info, $route_defaults);
  }
  return $processed_routes;
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

// Full list of routes available on the forum.
$GLOBALS['jul_routes'] = preprocess_routes(array(
  '@home' => array('path' => '/', 'match' => '/^\/$/', 'file' => 'index'),
  '@forum' => array('path' => '/forum/{id}', 'match' => '/^forum\/([0-9]+)/'),
  '@thread' => array('path' => '/thread/{id}', 'match' => '/^thread\/([0-9]+)/'),

  // User management
  '@register' => array('path' => '/register'),
  '@edit_profile' => array('path' => '/editprofile', 'noindex' => true),
  '@login' => array('path' => '/login'),

  // Messages
  '@new_thread' => array('path' => '/new-thread/{id}', 'path_clean' => '/new-thread/', 'match' => '/^new-thread\/?([0-9]+)?/', 'file' => 'newthread'),

  // Etc.
  '@error' => array('path' => '/$d'), // TODO: replace?
  '@post_radar' => array('path' => '/postradar'),
  '@shop' => array('path' => '/shop'),
  '@shop_editor' => array('path' => '/shopeditor'),

  // Admin routes
  '@admin' => array('path' => '/admin', 'admin' => true),

  // Dev routes
  '@_converter' => array('path' => '/dev/converter', 'admin' => true, 'file' => 'devtools/converter'),
  '@_db_ops' => array('path' => '/dev/db-ops', 'admin' => false, 'file' => 'devtools/db-ops'),

  // Replace these.
  /*
  '@memberlist' => array("{$GLOBALS['jul_views_path']}/memberlist.php"),
  '@activeusers' => array("{$GLOBALS['jul_views_path']}/activeusers.php"),
  '@calendar' => array("{$GLOBALS['jul_views_path']}/calendar.php"),
  '@irc' => array("{$GLOBALS['jul_views_path']}/irc.php"),
  '@online' => array("{$GLOBALS['jul_views_path']}/online.php"),
  '@ranks' => array("{$GLOBALS['jul_views_path']}/ranks.php"),
  '@faq' => array("{$GLOBALS['jul_views_path']}/faq.php"),
  '@stats' => array("{$GLOBALS['jul_views_path']}/stats.php"),
  '@latestposts' => array("{$GLOBALS['jul_views_path']}/latestposts.php"),
  '@hex' => array("javascript:void(0);", "onclick=\"hexidecimalchart()\""),
  '@smilies' => array("{$GLOBALS['jul_views_path']}/smilies.php"),
  */
));
