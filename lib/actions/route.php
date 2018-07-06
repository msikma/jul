<?php

// We use this instead of a function, because we need to run routes
// in the global context.
$route = get_request_route();

// Run a standard route file.
if ($route['file']) {
  $file = $route['file'];
  include("views/{$file}.php");
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
