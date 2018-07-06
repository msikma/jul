<?php

/**
 * Renders a generic header with a link back to home.
 */
function render_error_header($data) {
?>
<table class="table" cellspacing="0">
  <tr>
    <td class="tbl tdbg1 center"><a href="<?= $GLOBALS['jul_home']; ?>">Jul - <?= $data['error_header'] ? $data['error_header'] : 'An error has occurred'; ?></a></td>
  </tr>
</table>
<?php
}

?>
<html>
  <head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Jul -- <?= $data['error_header'] ? $data['error_header'] : 'An error has occurred'; ?></title>
    <meta name="robots" content="noindex,follow" />
		<link rel='stylesheet' href='<?= $GLOBALS['jul_base_dir'] ?>/static/css/base.css' type='text/css'>
		<link rel='stylesheet' href='<?= $GLOBALS['jul_base_dir'] ?>/views/theme/style.css' type='text/css'>
<?php
if ($data['static_css']) {
  include('css.php');
}
?>
  </head>
  <body>
    <center>
