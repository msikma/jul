<?php

require_once 'lib/actions/function.php';
$windowtitle="{$GLOBALS['jul_settings']['board_name']} -- Database converter";
require_once 'lib/actions/layout.php';

if ($query['action'] === 'convert') {
  print('convert');
  exit;
}

ob_start();
?>

<p>hello world</p>

<?php
$content = ob_get_clean();
?>
<?= $header ?>
<?= render_box($content, 'Database converter'); ?>
<?= $footer ?>
