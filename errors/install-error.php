<?php
include('templates/start.php');
$install_link = $GLOBALS['jul_base_dir']."/install.php";
$error_header = 'Installation error';
$error_content = "
  It does not seem like Jul has been installed yet.<br />
  To install, visit <a href='{$install_link}'>{$install_link}</a> and follow the steps.
  ";
render_error_header($data);
render_box($error_content, $error_header);
include('templates/end.php');
