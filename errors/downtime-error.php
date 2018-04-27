<?php
include('templates/start.php');
$error_header = 'The forum is down';
$error_content = $data['reason'] ? $data['reason'] : 'An error has occurred.';
render_error_header($data);
render_box($error_content, $error_header);
include('templates/end.php');
