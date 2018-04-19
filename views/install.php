<?php

require_once '../lib/function.php';
require_once '../lib/layout.php';

$self = 'installer.php';

?>
<html>
  <head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title><?= $GLOBALS['jul_settings']['board_name']; ?> -- Installer</title>
    <meta name="robots" content="noindex,follow" />
    <?= $GLOBALS['jul_js_vars']; ?>
    <?= $css; ?>
  </head>
  <body>
    <center>
      <?= $tblstart; ?>
      <tr>
        <td class='tbl tdbg1 center' colspan=3><a href="<?= $self; ?>">Jul - installer</a></td>
      </tr>
      <?= $tblend; ?>
      <?= $footer; ?>
  </body>
</html>
