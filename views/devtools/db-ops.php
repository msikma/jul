<?php

require_once 'lib/actions/function.php';
$windowtitle="{$GLOBALS['jul_settings']['board_name']} -- Database operations";
require_once 'lib/actions/layout.php';

$index = route('@home');

if ($query['action'] === 'db-truncate') {
  // Truncate the database and redirect back to the index.
  __truncate_db();
  redirect_exit($index);
}
if ($query['action'] === 'reinstall') {
  // Return the database to the post-installation state.
  __truncate_db();

  // Quick install and redirect back to the index.
  run_installer_sql();
  _make_testing_forums();
  register_admin_account('root', 'root');
  check_login('root', 'root');
  set_user_login_cookies(1, '0');
  redirect_exit($index);
}

check_login('root', 'root');

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
