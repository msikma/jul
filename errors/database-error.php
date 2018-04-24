<?php
include('templates/start.php');
$last_error = mysql_error();
$check_error = $data['db_error'];
$error_header = 'Database connection error';
$error_content = "
  Can't connect to the database.<br />
  Check your <a>lib/config.php</a> file to fix any problems.<br /><br />
  {$check_error}<br />
  {$last_error}"
render_error_header($data);
render_box($error_content, $error_header);
include('templates/end.php');
