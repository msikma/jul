<?php

/**
 * Loads a file from the errors directory and prints its output, then exits.
 * If $print is false, the content is returned instead, and the program does not exit.
 */
function include_error($name, $print=true, $data=array()) {
  ob_start();
  include($GLOBALS['jul_base_path'].'/errors/'.$name.'.php');
  $output = ob_get_clean();

  if ($print) {
    print($output);
    exit;
  }
}

// Generic error page.
function error_page($text, $redir = '', $redirurl = '') {
  global $header, $tblstart, $tccell1, $tblend, $footer, $startingtime;

  print("{$header}<br>{$tblstart}{$tccell1}>{$text}");
  if ($redir) {
    print('<br>'.redirect($redirurl, $redir, 0));
  }
  print("{$tblend}{$footer}");
  printtimedif($startingtime);
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
    <br><img src='errors/500.png' /><br><br>
    ",
    'header' => '500 - Internal Server Error'
  ));
}

function error_404() {
  header('HTTP/1.1 404 Not Found');
  include_error('generic-error', true, array(
    'reason' => "
    <br><img src='errors/404.png' /><br><br>
    ",
    'no_top_bar' => true,
    'header' => null
  ));
}

// Used in early errors, such as SQL connection problems.
function early_html_die($reason, $use_mysql_error=false) {
  $sql_error = $use_mysql_error ? "<br><font style=\"color: #f55;\">". mysql_error() ."</font>" : '';
  include_error('generic-error', true, array(
    'reason' => $reason,
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
