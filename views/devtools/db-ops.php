<?php

require_once 'lib/actions/function.php';
$windowtitle="{$GLOBALS['jul_settings']['board_name']} -- Database operations";
require_once 'lib/actions/layout.php';

$index = route('@home');

if ($query['action'] === 'db-truncate') {
  // Truncate the database and redirect back to the index.
  redirect_exit($index);
}
if ($query['action'] === 'reinstall') {
  // Return the database to the post-installation state and redirect back to the index.
  redirect_exit($index);
}

?>
<?= $header ?>
<?= render_box('
<div>
  <ul>
    <li><a href="'.route('@_db_ops', null, array('action' => 'db-truncate')).'">Truncate database</a></li>
    <li><a href="'.route('@_db_ops', null, array('action' => 'reinstall')).'">Quick reinstall</a></li>
  </ul>
</div>
', 'Database operations'); ?>

<?= $footer ?>
