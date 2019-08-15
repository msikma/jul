<?php
include('templates/start.php');
$install_link = $GLOBALS['jul_base_dir']."/install";
$error_header = 'Installation notice';
$error_content = "
  <b>Welcome to Jul, the new old forum software.</b><p>
  Jul has not been installed yet.<br />
  To install, visit <a href='{$install_link}'>{$install_link}</a> and follow the steps.
  ";
render_error_header($data);
render_box($error_content, $error_header);
include('templates/end.php');
