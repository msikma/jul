<?php

require_once '../lib/function.php';
require_once '../lib/layout.php';
require_once '../lib/install.php';

$self = 'installer.php';

$config_ready = check_config();
$db_ready = check_db();
$already_installed = check_already_installed();
$installer_step = !isset($_POST['step']) ? 1 : intval($_POST['step']);

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
    <form action="install.php" method="post">
    <center>
      <?= $tblstart; ?>
      <tr>
        <td class='tbl tdbg1 center' colspan=3><a href="<?= $self; ?>">Jul - installer</a></td>
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
  In order to set up Jul, make sure you make a <a>lib/config.php</a> file with your own custom settings.
  ");
}
if ($config_ready && !$db_ready[0]) {
  render_box("
  Could not connect to the database. Make sure your <a>lib/config.php</a> file has the correct settings to connect to MySQL.<br /><br />

  {$db_ready[1]}
  ");
}
// TODO
if (false && $already_installed) {
  render_box("
  Jul is already installed.
  ");
}
else if ($config_ready && $db_ready[0] && $installer_step === 3) {
  render_box("
    We're all done installing! Now you can log in to your forum for the first time.
  ", 'Installation result');
  ob_start();
  // Huge hack here.
  $GLOBALS['skip_header'] = true;
  $GLOBALS['skip_footer'] = true;
  include('./login.php');
  $html = ob_get_clean();
  print($html);
}
else if ($config_ready && $db_ready[0] && $installer_step === 2) {
  $admin_username = $_POST['admin_username'];
  $admin_password = $_POST['admin_password'];
  $install_sql = get_installer_sql();
  $tables = get_sql_create_tables($install_sql);
  $tableamount = count($tables);
  print("
  <table class='table form-table table-margin' cellspacing='0'>
  <tr>
    <td class='tbl tdbgh font center'>&nbsp;</td>
    <td class='tbl tdbgh font center'>&nbsp;</td>
  </tr>
  <tr>
    <td class='tbl tdbg1 font center label'>Installing tables...</td>
    <td class='tbl tdbg2 font'>
      {$tableamount} tables will be inserted.
      <table class='table data-table sql-tables'>
  ");
  $n = 0;
  $cols = 7;
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
    <td class='tbl tdbg1 font center label'>Inserting admin user...</td>
    <td class='tbl tdbg2 font'>
      Done. Inserted admin user with username <strong><a>{$admin_username}</a></strong>.<br />Click 'Continue'.
    </td>
  </tr>
  <tr>
    <td class='tbl tdbgh font center'>&nbsp;</td>
    <td class='tbl tdbgh font center'>&nbsp;</td>
  </tr>
  <tr>
    <td class='tbl tdbg1 font center label'>&nbsp;</td>
    <td class='tbl tdbg2 font'>
      <input type='hidden' name='step' value='3' />
      <input type='submit' class='submit' value='Continue' />
    </td>
  </tr>
  </table>
  ");
}
else if ($config_ready && $db_ready[0] && $installer_step === 1) {
  $set = get_data_table($GLOBALS['jul_settings'], $GLOBALS['jul_common_settings']);
  $db = get_data_table($GLOBALS['jul_sql_settings']);
  render_box("
  Ready to begin installing Jul. Please check if the following settings are correct.<br />Edit your <a>lib/config.php</a> file to fix any problems.
  ");
  $content = array(
    array('---'),
    array('Forum settings', $set),
    array('---'),
    array('Database settings', $db),
    array('---'),
    array('Admin username', "<input type='text' name='admin_username' value='' class='right' />"),
    array('Admin password', "<input type='password' name='admin_password' value='' class='right' /><br />Choose a username and password for your new admin user.<br />Make sure it's a unique and strong password.<br />For complete certainty, <a href='#'>generate a strong password</a>."),
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
