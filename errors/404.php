<?php
include('templates/start.php');
$error_content = $data['reason'] ? $data['reason'] : 'An error has occurred.';
render_box($error_content, $error_header);
include('templates/end.php');
