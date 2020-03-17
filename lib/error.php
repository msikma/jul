<?php

/**
 * Loads a file from the errors directory and prints its output, then exits.
 * If $print is false, the content is returned instead, and the program does not exit.
 */
function include_error($name, $print=true, $data=array()) {
  ob_start();
  include('errors/'.$name.'.php');
  $output = ob_get_clean();

  if ($print) {
    print($output);
    // Only prints if developing.
    print(console_exec());
    exit;
  }
}

// Error page that links back to home.
function error_page_common($text) {
  return error_page($text, 'Return to the homepage', route('@home'));
}

// Generic error page.
function error_page($text, $redir = '', $redirurl = '') {
  global $header, $tblstart, $tccell1, $tblend, $footer, $startingtime;

  // Legacy: some calls to error_page() have 'return to' in the $redir string.
  // This is now provided outside of the link. So we remove it from $redir.
  // We also remove a period at the end if it's there.
  $redir = trim(preg_replace('/^return to/i', '', trim($redir, '.')));

  print("{$header}<br>{$tblstart}{$tccell1}>{$text}");
  if ($redir) {
    print('<br>');
    print('Return to <a href="'.$redirurl.'">'.$redir.'</a>.');
  }
  print("{$tblend}{$footer}");
  printtimedif($startingtime);
  // Only prints if developing.
  print(console_exec());
  die();
}

function error_code($code) {
  $fn = "error_{$code}";
  if (function_exists($fn)) call_user_func($fn);
  // Apparently we don't have a function for this error code.
  // Call a generic one.
  else error_500();
}

function error_500() {
  header('HTTP/1.1 500 Internal Server Error', true, 500);
  include_error('generic-error', true, array(
    'reason' => "
    <a href=\"{$GLOBALS['jul_home']}\"><br><img src='{$GLOBALS['jul_base_dir']}/static/errors/500.png' /><br><br></a>
    ",
    'header' => '500 - Internal Server Error'
  ));
}

function error_404() {
  header('HTTP/1.1 404 Not Found');
  include_error('generic-error', true, array(
    'reason' => "
    <a href=\"{$GLOBALS['jul_home']}\"><br><img src='{$GLOBALS['jul_base_dir']}/static/errors/404.png' /><br><br></a>
    ",
    'no_top_bar' => true,
    'header' => null
  ));
}

// Used in early errors, such as SQL connection problems.
function early_html_die($reason, $use_mysql_error=false, $external_entry_point=false) {
  if ($external_entry_point) {
    // Don't die if we're not on the main entry point.
    return false;
  }
  $sql_error = $use_mysql_error ? "<br><font style=\"color: #f55;\">". mysql_error() ."</font>" : '';
  include_error('generic-error', true, array(
    'reason' => $reason,
    'static_css' => true,
    'sql_error' => $sql_error
  ));
}

function suspicious_die() {
  header('HTTP/1.1 403 Forbidden', true, 403);
  include_error('generic-error', true, array(
    'reason' => 'Suspicious request detected (e.g. bot or malicious tool).',
    'header' => '403 - Forbidden'
  ));
}

function downtime_die() {
  header('HTTP/1.1 503 Service Unavailable', true, 503);
  include_error('generic-error', true, array(
    'reason' => "
    <div>
      The board has been taken offline for a while.
      <br>
      This is probably because:
      <ul class='center'>
        <li>we're trying to prevent something from going wrong,</li>
        <li>abuse of the forum was taking place and needs to be stopped,</li>
        <li>some idiot thought it'd be fun to disable the board</li>
      </ul>
      The forum should be back up within a short time. Until then, please do not panic;
      <br>if something bad actually happened, we take backups often.
    </div>
    ",
    'header' => '503 - Service Unavailable'
  ));
}
