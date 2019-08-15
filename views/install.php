<?php

$GLOBALS['jul_installing'] = true;

require_once 'config.example.php';
require_once 'lib/helpers.php';
require_once 'lib/actions/function.php';
require_once 'lib/actions/layout.php';
require_once 'lib/models/check-install.php';
require_once 'lib/install.php';

$config_ready = does_config_exist();
$db_ready = check_db();
$already_installed = check_installed();
$installer_step = !isset($_POST['step']) ? 1 : intval($_POST['step']);

$first_forum = 'General chat';

$home = to_home();
$self = base_dir().'/install';

// When we're at step 2, we'll actually install the forum and add the db tables.
if ($config_ready && $db_ready[0] && $installer_step === 2) {
  $installer_success = run_installer_sql();
}

// Link the top header not to the installer script if installation is done.
$link_to_installer = !$installer_success && ($installer_step < 2);

?>
<html>
  <head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title><?= $GLOBALS['jul_settings']['board_name']; ?> -- Installer</title>
    <meta name="robots" content="noindex,follow" />
		<link rel='stylesheet' href='/jul/theme/style.css' type='text/css'>
		<link rel='stylesheet' href='/jul/static/css/base.css' type='text/css'>
		<link rel='stylesheet' href='/jul/static/css/install.css' type='text/css'>
    <?= $GLOBALS['jul_js_vars']; ?>
  </head>
  <body>
    <form action="<?= $link_to_installer ? $self : $GLOBALS['jul_base_dir'].'/login'; ?>" method="post">
    <center>
      <?= $tblstart; ?>
      <tr>
        <td class='tbl tdbg1 center' colspan=3><a href="<?= $link_to_installer ? $self : $GLOBALS['jul_base_dir'].'/'; ?>">Jul -- Installer</a></td>
      </tr>
      <tr>
        <td class='tbl tdbg2 center fonts'>
          Step <?= $installer_step; ?>
        </td>
      </tr>
      <?= $tblend; ?>
<?php
if (!$config_ready) {
  render_box("
  In order to set up Jul, make sure you make a <a>config.php</a> file with your own custom settings.
  ");
}
if ($config_ready && !$db_ready[0]) {
  render_box("
  Could not connect to the database. Make sure your <a>config.php</a> file has the correct settings to connect to MySQL.<br /><br />

  {$db_ready[1]}
  ");
}
// TODO
if ($already_installed && $installer_step !== 3) {
  render_box("
  <p>Jul is already installed.</p>
  <p><a href='{$home}'>Go to the forum homepage.</a></p>
  ");
}
else if ($config_ready && $db_ready[0] && $installer_step === 3) {
  $login_form = get_login_form(true);
  // ----- Step 3 ------
  render_box("
    <p>We're all done installing! Now you can log in to your forum for the first time.</p>
    <p><a href='{$home}'>Go to the forum homepage.</a></p>
  ", 'Installation result');
  print($login_form);
}
else if ($config_ready && $db_ready[0] && $installer_step === 2) {
  // ----- Step 2 ------
  if (!$installer_success) {
    print("
    <tr>
      <td class='tbl tdbg1 font center label'>
      <p>Something went wrong while installing.</p>
      <p>Check the SQL log for more information. <a href='{$self}'>Go back to step one.</a></p></td>
    </tr>
    </table>
    </center>
    </form>
    {$footer}
  </body>
</html>
    ");
    exit;
  }
  $admin_username = $_POST['admin_username'];
  $admin_password = $_POST['admin_password'];
  $made_admin = register_admin_account($admin_username, $admin_password);
  $install_sql = get_installer_sql();
  $tables = get_sql_create_tables($install_sql);
  $tableamount = count($tables);
  print("
  <table class='table form-table table-margin' cellspacing='0'>
  <tr>
    <td class='tbl tdbgh font center'>&nbsp;</td>
    <td class='tbl tdbgh font center' colspan='3'>&nbsp;</td>
  </tr>
  <tr>
    <td class='tbl tdbg1 font center installer-label'>Installing tables</td>
    <td class='tbl tdbg2 font' colspan='3'>
      {$tableamount} tables will be inserted:
      <table class='table data-table sql-tables'>
  ");
  $n = 0;
  $cols = 5;
  for ($a = 0; $a < floor(count($tables) / $cols) + 1; ++$a) {
    print("<tr>");
    for ($b = 0; $b < $cols; ++$b) {
      $tablename = $tables[$n];
      print("<td>$tablename</td>");
      $n += 1;
    }
    print("</tr>");
  }
  print("
      </table>
      Done.
    </td>
  </tr>
  <tr>
    <td class='tbl tdbg1 font center installer-label'>Making admin user</td>
    <td class='tbl tdbg2 font' colspan='3'>
      Done. Inserted admin user with username <strong><a>{$admin_username}</a></strong>.
    </td>
  </tr>");
  _make_testing_forums();
  // ---
  $login = get_login_form(true);
  $loginlink = $GLOBALS['jul_base_dir'].'/login';
  print("
  <tr>
    <td class='tbl tdbg1 font center installer-label'>Creating forum</td>
    <td class='tbl tdbg2 font' colspan='3'>
      Done. Created '<strong><a>{$first_forum}</a></strong>' forum.
    </td>
  </tr>
  <tr>
    <td class='tbl tdtransparent font center'>&nbsp;</td>
    <td class='tbl tdtransparent font center' colspan='3'>&nbsp;</td>
  </tr>
  <tr>
    <td class='tbl tdbgh font center'>&nbsp;</td>
    <td class='tbl tdbgh font center' colspan='3'>&nbsp;</td>
  </tr>
  <tr>
    <td class='tbl tdbg1 font center label'>&nbsp;</td>
    <td class='tbl tdbg2 font' colspan='3'>
    <a href='{$loginlink}'>We're all done installing! Now you can log in to your forum for the first time:</a>
    </td>
  </tr>
  {$login}
  </table>
  ");
}
else if ($config_ready && $db_ready[0] && $installer_step === 1) {
  // ----- Step 1 ------
  $set = get_data_table($GLOBALS['jul_settings'], $GLOBALS['jul_common_settings']);
  $db = get_data_table($GLOBALS['jul_sql_settings_safe']);
  render_box("
  Ready to begin installing Jul. Please check if the following settings are correct.<br />Edit your <a>config.php</a> file to fix any problems.
  ");
  $content = array(
    array('---'),
    array('Forum settings', $set),
    array('---'),
    array('Database settings', $db),
    array('---'),
    array('Admin username', "<input type='text' name='admin_username' value='' />"),
    array('Admin password', "<input type='password' name='admin_password' value='' /><br />Choose a username and password for your new admin user.<br />Make sure it's a unique and strong password.<br />For complete certainty, <a href='https://passwordsgenerator.net/' target='_blank'>generate a strong password</a>."),
    array('---'),
    array('', "<input type='hidden' name='step' value='2' /><input type='submit' class='submit' value='Confirm and install' />"),
  );
  render_form_table($content);
}
?>
    </center>
    </form>
    <?= $footer; ?>
  </body>
</html>
