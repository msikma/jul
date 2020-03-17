<?php

// If, during initialization, we found some kind of error (the database doesn't exist, or is invalid,
// or the forum isn't installed yet, etc.) we will end the

// We use this instead of a function, because we need to run routes in the global context.
$route = get_request_route();
console_log($route);

// There are two special routes: the installer, and the theme CSS generator.
// They both circumvent the regular error handling that occurs when the database
// is not in a valid state.
if ($route['file'] !== 'install' && $route['file'] !== 'theme/style.css' && !$route['error']) {
  error_on_bad_db();
  error_on_bad_install();
}

// Run a standard route file.
if ($route['file']) {
  $file = $route['file'];
  $request = $route['request']['data'];
  $query = $route['request']['query'];

  // Check for admin credentials on secured pages.
  if ($route['admin'] && !is_admin_user()) {
    error_page('Uh oh, you are not the admin go away!', 'Return to the homepage', route('@home'));
    exit;
  }

  include("views/{$file}.php");

  // If developing, print messages to be passed on to the console.
  print(console_exec());
  exit;
}

// Run a redirect.
if ($route['redirect']) {
  header("Location: {$GLOBALS['jul_base_dir']}{$route['redirect']}");
  exit;
}

// Display an error page.
if ($route['error']) {
  error_code($route['error']);
  exit;
}
