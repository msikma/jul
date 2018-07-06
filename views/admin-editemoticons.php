<?php

require_once 'lib/actions/function.php';
$windowtitle = "{$GLOBALS['jul_settings']['board_name']} - Emoticons";
require_once 'lib/actions/layout.php';

print $header."<br>";

$active_emoticon_set = get_active_emoticon_set();
admincheck();
print(adminlinkbar("{$GLOBALS['jul_views_path']}/admin-editemoticons.php"));

$donotprint = false;
if ($action) {
	$donotprint = true;
	switch($action) {
    case "set":
      $emoticonset = $_POST['setemoticons'];
      if (!$emoticonset) {
        print("$tblstart$tccell1> Invalid emoticon set selected. No changes made.<br><a href=\"{$GLOBALS['jul_views_path']}/admin-editemoticons.php\">Go back to Edit Emoticons</a>");
        break;
      }
      set_active_emoticon_set($emoticonset);
      print("$tblstart$tccell1> You successfully changed the emoticon set to $emoticonset.<br>".redirect("{$GLOBALS['jul_views_path']}/admin-editemoticons.php",'Edit Emoticons',0));
		  break;
		default:
			print "Naw.";
  }
}

if (!$donotprint) {
	$forumselect="<option value=\"0\">Select an emoticon set...</option>\r\n";
  foreach ($GLOBALS['jul_emoticon_sets'] as $k => $v) {
    if ($k === $active_emoticon_set['slug']) {
      $forumselect .= "<option value='{$k}' selected='selected'>{$v['name']} ({$v['amount']})</option>";
    }
    else {
      $forumselect .= "<option value='{$k}'>{$v['name']} ({$v['amount']})</option>";
    }
  }

print "

<form action=\"{$GLOBALS['jul_views_path']}/admin-editemoticons.php\" method=\"POST\">$inph=\"action\" value=\"set\">$tblstart".
"<tr>$tccellh colspan=\"2\">Set Emoticons:</td></tr>
<tr>$tccell1 width=15%>Set:</td>$tccell2l width=85%><select name=\"setemoticons\" size=\"1\">$forumselect</select></td></tr>
<tr>$tccell1 width=15%>&nbsp;</td>$tccell2l width=85%>$inps=\"setemoticonssubmit\" value=\"Set Emoticons\"></td></tr>$tblend</form>";

$images = make_emoticon_table();

?>
<?= $tblstart; ?>
<tr><?= $tccellh; ?> colspan="2">Active set:</td></tr>
<tr><?= $tccell1; ?> width=15%>Name:</td><?= $tccell2l ?> width=85%><?= $active_emoticon_set['name']; ?></td></tr>
<tr><?= $tccell1; ?> width=15%>Slug:</td><?= $tccell2l ?> width=85%><pre><?= $active_emoticon_set['slug']; ?></pre></td></tr>
<tr><?= $tccell1; ?> width=15%>Smilies:</td><?= $tccell2l ?> width=85%><?= $active_emoticon_set['amount']; ?></td></tr>
<tr><?= $tccell1; ?> width=15%>Path:</td><?= $tccell2l ?> width=85%><pre><?= $active_emoticon_set['path']; ?></pre></td></tr>
<tr><?= $tccell1; ?> width=15%>Website:</td><?= $tccell2l ?> width=85%><a href="<?= $active_emoticon_set['website'] ?>" target="_blank"><?= $active_emoticon_set['website']; ?></a></td></tr>
<tr><?= $tccell1; ?> width=15%>Images:</td><?= $tccell2l ?> width=85%><?= $images; ?></td></tr>
<?= $tblend; ?>
<?php

}

print $footer;
printtimedif($startingtime);
