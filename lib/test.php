<?php

function dev_testing_tools_html() {
  if (!dev_testing_is_allowed()) {
    return '';
  }
  $base = base_dir();
  return "
    <tr width='100%' class='dev-testing-tools'>
      <td colspan='3' class='tbl tdbg2 center fonts'>
        Dev testing tools: 
        <a href='#'>Truncate DB</a>
      </td>
    </tr>
  ";
}

function dev_testing_is_allowed() {
  // Allow dev testing only if we're running locally.
  $is_local = $_SERVER['SERVER_ADDR'] === '::1' && $_SERVER['REMOTE_ADDR'] === '::1';
  return $is_local;
}
