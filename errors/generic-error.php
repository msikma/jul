<?php
include('templates/start.php');
$error_header = $data['header'];
$error_content = $data['reason'] ? $data['reason'] : 'An error has occurred.';
if ($data['no_top_bar'] !== true) {
  render_error_header($data);
}
render_box($error_content, $error_header);
include('templates/end.php');
