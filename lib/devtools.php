<?php

$GLOBALS['jul_devtools_logs'] = array();

/** Logs a variable to the JS console, if development mode is enabled. */
function console_log($var) {
  $GLOBALS['jul_devtools_logs'][] = $var;
}

/** Generates the <script> tag needed to log our variables. */
function console_exec() {
  if (!_dev_testing_is_allowed()) {
    return '';
  }
  return _console_exec_content($GLOBALS['jul_devtools_logs']);
}

/** Prints testing tools. */
function dev_testing_tools_html() {
  if (!_dev_testing_is_allowed()) {
    return '';
  }
  return "
    <tr width='100%' class='dev-testing-tools'>
      <td colspan='3' class='tbl tdbg2 center fonts'>
        Dev testing tools:
        <a href='".(route('@_db_ops', null, array('a' => 'b')))."'>Truncate DB</a>
      </td>
    </tr>
  ";
}

function _dev_testing_is_allowed() {
  // Allow dev testing only if we're running locally.
  $is_local_ipv6 = $_SERVER['SERVER_ADDR'] === '::1' && $_SERVER['REMOTE_ADDR'] === '::1';
  $is_local_ipv4 = $_SERVER['SERVER_ADDR'] === '127.0.0.1' && $_SERVER['REMOTE_ADDR'] === '127.0.0.1';
  return $is_local_ipv6 || $is_local_ipv4;
}

// Encodes a string for use in JSON.parse() inside a backtick-quoted string.
function _encode_for_json_parse($log) {
  $encoded = json_encode($log, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS);
  return str_replace(array('\\', '`'), array('\\\\', '\`'), $encoded);
}

/** Generates a <script> tag that logs our stored messages.  */
function _console_exec_content($logs) {
  $log_lines = array();
  foreach ($logs as $log) {
    $log_lines[] = 'console.log(JSON.parse(`'._encode_for_json_parse($log).'`));';
  }
  return '
    <script type="text/javascript" jul-devtools>
    '.implode("\n", $log_lines).'
    </script>
  ';
}
