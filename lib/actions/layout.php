<?php

// UTF-8 time?
header("Content-type: text/html; charset=utf-8');");

// cache bad
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Pragma: no-cache');

$userip = $_SERVER['REMOTE_ADDR'];

if (!($clientip = filter_var(getenv('HTTP_CLIENT_IP'), FILTER_VALIDATE_IP))) {
  $clientip = 'XXXXXXXXXXXXXXXXX';
}
if (!($forwardedip = filter_var(getenv('HTTP_X_FORWARDED_FOR'), FILTER_VALIDATE_IP))) {
  $forwardedip = 'XXXXXXXXXXXXXXXXX';
}
if (!isset($windowtitle)) {
  $windowtitle = $GLOBALS['jul_settings']['board_name'];
}

$home = base_url().'/';
$GLOBALS['jul_settings']['board_title'] = "<a href='{$home}'>{$GLOBALS['jul_settings']['board_title']}</a>";

try {
    $race = $loguserid ? postradar($loguserid) : '';
}
catch (Exception $e) {
    $race = '';
}

$tablewidth = '100%';
$fonttag = '<font class="font">';
$fonthead = '<font class="fonth">';
$smallfont = '<font class="fonts">';
$tinyfont = '<font class="fontt">';

foreach (array('1', '2', 'c', 'h') as $celltype) {
  $cell = "<td class='tbl tdbg$celltype font";
  $celln = "tccell$celltype";
  $$celln = $cell." center'";
  ${$celln.'s'} = $cell."s center'";
  ${$celln.'t'} = $cell."t center'";
  ${$celln.'l'} = $cell."'";
  ${$celln.'r'} = $cell." right'";
  ${$celln.'ls'} = $cell."s'";
  ${$celln.'lt'} = $cell."t'";
  ${$celln.'rs'} = $cell."s right'";
  ${$celln.'rt'} = $cell."t right'";
}

$inpt = '<input type="text" name';
$inpp = '<input type="password" name';
$inph = '<input type="hidden" name';
$inps = '<input type="submit" class=submit name';
$inpc = '<input type=checkbox name';
$radio = '<input type=radio class=radio name';
$txta = '<textarea wrap=virtual name';
$tblstart = '<table class="table" cellspacing=0>';
$tblend = '</table>';
$sepn = array('Dashes', 'Line', 'Full horizontal line', 'None');
$sep = array('<br><br>--------------------<br>',
     '<br><br>____________________<br>',
     '<br><br><hr>',
     '<br><br>', );
$br = "\n";

$headlinks = '';
if ($loguserid) {
    if ($isadmin) {
        $headlinks .= "<a href=\"{$GLOBALS['jul_views_path']}/admin.php\" style=\"font-style:italic;\">Admin</a> - ";
    }

    if ($power >= 1) {
        $headlinks .= "<a href='{$GLOBALS['jul_views_path']}/shoped.php' style=\"font-style:italic;\">Shop Editor</a> - ";
    }

    $headlinks .= "
<a href=\"javascript:document.logout.submit()\">Logout</a>
- <a href=\"{$GLOBALS['jul_views_path']}/editprofile.php\">Edit profile</a>
- <a href=\"{$GLOBALS['jul_views_path']}/postradar.php\">Post radar</a>
- <a href=\"{$GLOBALS['jul_views_path']}/shop.php\">Item shop</a>
- <a href=\"{$GLOBALS['jul_views_path']}/forum.php?fav=1\">Favorites</a>";
} else {
    $headlinks .= "
  <a href=\"{$GLOBALS['jul_views_path']}/register.php\">Register</a>
- <a href=\"{$GLOBALS['jul_views_path']}/login.php\">Login</a>";
}

$headlinks2 = array();
foreach ($GLOBALS['jul_settings']['top_menu_items'] as $row) {
    $rowlinks = array();
    foreach ($row as $item) {
        $rowlinks[] = '<a href="'.route($item[0]).'" '.route_params($item[0]).'>'.$item[1].'</a>';
    }
    $headlinks2[] = implode(' - ', $rowlinks);
}
$headlinks2 = implode('<br>', $headlinks2);

$ipbanned = $torbanned = 0;

$checkips = "INSTR('$userip',ip)=1";
if ('XXXXXXXXXXXXXXXXX' !== $forwardedip) {
    $checkips .= " OR INSTR('$forwardedip',ip)=1";
}
if ('XXXXXXXXXXXXXXXXX' !== $clientip) {
    $checkips .= " OR INSTR('$clientip',ip)=1";
}
if ($GLOBALS['jul_valid_db']) {
    if ($sql->resultq("SELECT count(*) FROM ipbans WHERE $checkips")) {
        $ipbanned = 1;
    }
    if ($sql->resultq("SELECT count(*) FROM `tor` WHERE `ip` = '".$_SERVER['REMOTE_ADDR']."' AND `allowed` = '0'")) {
        $torbanned = 1;
    }
}
else {
    $ipbanned = 0;
    $torbanned = 0;
}

if ($ipbanned || $torbanned) {
    $windowtitle = $GLOBALS['jul_settings']['board_name'];
}

if ($ipbanned) {
    $url .= ' (IP banned)';
}

if ($torbanned) {
    $url .= ' (Tor proxy)';
    $sql->query("UPDATE `tor` SET `hits` = `hits` + 1 WHERE `ip` = '".$_SERVER['REMOTE_ADDR']."'");
}

if ($GLOBALS['jul_valid_db']) {
    $views = $sql->resultq('SELECT views FROM misc') + 1;

    // Dailystats update in one query
    $sql->query('INSERT INTO dailystats (date, users, threads, posts, views) '.
    "VALUES ('".date('m-d-y', ctime())."', (SELECT COUNT( * ) FROM users), (SELECT COUNT(*) FROM threads), (SELECT COUNT(*) FROM posts), $views) ".
    "ON DUPLICATE KEY UPDATE users=VALUES(users), threads=VALUES(threads), posts=VALUES(posts), views=$views");
}
else {
    $views = 0;
}

$new = '&nbsp;';
$privatebox = '';
// Note that we ignore this in private.php (obviously) and the index page (it handles PMs itself)
// This box only shows up when a new PM is found, so it's optimized for that
if ($log && false == strpos($PHP_SELF, 'private.php') && 0 == strpos($PHP_SELF, 'index.php')) {
    $newmsgquery = $sql->query("SELECT date,u.id uid,name,sex,powerlevel,aka FROM pmsgs p LEFT JOIN users u ON u.id=p.userfrom WHERE userto=$loguserid AND msgread=0 ORDER BY p.id DESC");
    if ($pmsg = $sql->fetch($newmsgquery)) {
        $namelink = getuserlink($pmsg, array('id' => 'uid'));
        $lastmsg = "Last unread message from $namelink on ".date($dateformat, $pmsg['date'] + $tzoff);

        $numnew = mysql_num_rows($newmsgquery);
        if ($numnew > 1) {
            $ssss = 's';
        }

        $privatebox = "<tr><td colspan=3 class='tbl tdbg2 center fonts'>$newpic <a href={$GLOBALS['jul_views_path']}/private.php>You have $numnew new private message$ssss</a> -- $lastmsg</td></tr>";
    }
}

// Pass on some PHP variables to JS.
$base_json = json_encode($GLOBALS['jul_base_dir']);
$views_json = json_encode($GLOBALS['jul_views_path']);
$settings_json = json_encode($GLOBALS['jul_settings']);
$GLOBALS['jul_js_vars'] = "
<script>
window.jul_base_dir = {$base_json};
window.jul_views_path = {$views_json};
window.jul_settings = {$settings_json};
</script>
";

$jscripts = '';
if ($GLOBALS['jul_settings']['display_ikachan']) { // Ikachan! :D!
  // Display ikachan based on chance from second number.
  // e.g. 99 = 99% chance, 1 = 1% chance
  $rand = mt_rand(1, 100);
  $n = 0;
  foreach ($ikachan_source as $ika) {
    $n += $ika[1];
    if ($rand <= $n) {
      $ikasrc = $ika[0];
      break;
    }
  }
  if (!isset($ikasrc)) {
    // oops
    var_dump('err');
    $ikasrc = $ikachan_source[0][0];
  }
  $ikaquotes = array(
    'Capturing turf before it was cool',
    'Someone stole my hat!',
    'If you don\'t like Christmas music, well... it\'s time to break out the earplugs.',
    'This viking helmet is stuck on my head!',
    'Searching for hats to wear!  If you find any, please let me know...',
    'What idiot thought celebrating a holiday five months late was a good idea?',
    'I just want to let you know that you are getting coal this year. You deserve it.'
  );
  $ikaquote = $ikaquotes[array_rand($ikaquotes)];
  $yyy = "<img id='f_ikachan' src='$ikasrc' style=\"z-index: 999999; position: fixed; left: ".mt_rand(0, 100).'%; top: '.mt_rand(0, 100)."%;\" title=\"$ikaquote\">";
}

$dispviews = $views;
$body = '<body>';
if (!isset($meta)) {
    $meta = array();
}
$metatag = '';

if (filter_bool($meta['noindex'])) {
    $metatag .= '<meta name="robots" content="noindex,follow" />';
}

if (isset($meta['description'])) {
    $metatag .= "<meta name=\"description\" content=\"{$meta['description']}\" />";
}

if (isset($meta['canonical'])) {
    $metatag .= "<link rel='canonical' href='{$meta['canonical']}'>";
}
$favicon = $favicons[array_rand($favicons)];
$jsfiles = '';
foreach ($js_include as $js_file_include) {
  $ct = file_get_contents($js_file_include);
  $jsfiles .= "
  <script type='text/javascript'>
  $ct
  </script>";
}

$header1 = "<html><head><meta http-equiv='Content-type' content='text/html; charset=utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>$windowtitle</title>
{$GLOBALS['jul_js_vars']}
$metatag
<link rel=\"shortcut icon\" href=\"{$favicon}\" type=\"image/x-icon\">
<link rel='stylesheet' href='{$GLOBALS['jul_base_dir']}/static/css/base.css' type='text/css'>
<link rel='stylesheet' href='{$GLOBALS['jul_views_path']}/theme/style.css' type='text/css'>
$css
$jsfiles
</head>
$body
$yyy
<center>
$tblstart
<form action='{$GLOBALS['jul_views_path']}/login.php' method='post' name='logout'><input type='hidden' name='action' value='logout'></form>
<td class='tbl tdbg1 center' colspan=3>{$GLOBALS['jul_settings']['board_title']}";

$header2 = '
'."
</td><tr>
  <td width='120px' class='tbl tdbg2 center fonts'><nobr>Views: $dispviews<br><img src={$GLOBALS['jul_base_dir']}/static/images/spacer.gif width=120 height=1></td>
  <td width='100%' class='tbl tdbg2 center fonts'>$headlinks2</td>
  <td width='120px' class='tbl tdbg2 center fonts'><nobr>".date($dateformat, ctime() + $tzoff)."<br><img src={$GLOBALS['jul_base_dir']}/static/images/spacer.gif width=120 height=1></table>";

$headlinks = "$smallfont<br>$headlinks";

function makeheader($header1, $headlinks, $header2)
{
    global $loguser, $PHP_SELF;
    $header = $header1.$headlinks.$header2;
    if (!$loguser['id'] && false === strpos($PHP_SELF, 'index.php')) {
        $header .= adbox().'<br>';
    }

    return $header;
}

if (isset($_GET['scheme']) && is_numeric($_GET['scheme'])) {
    $GLOBALS['jul_settings']['board_title'] .= "</a><br><span class='font'>Previewing scheme \"<b>".$schemerow['name'].'</b>"</span>';
}

$ref = filter_string($_SERVER['HTTP_REFERER']);
$url = getenv('SCRIPT_URL');

if (!$url) {
    $url = str_replace('/etc/board', '', getenv('SCRIPT_NAME'));
}
$q = getenv('QUERY_STRING');

if ($q) {
    $url .= "?$q";
}
if ($ref && 'jul.rus' != substr($ref, 7, 7)) {
    try {
        $sql->query('INSERT INTO referer (time,url,ref,ip) VALUES ('.ctime().", '".mysql_real_escape_string($url)."', '".mysql_real_escape_string($ref)."', '".$_SERVER['REMOTE_ADDR']."')");
    }
    catch (Exception $e) {
    }
}

if ($GLOBALS['jul_valid_db']) {
    $sql->query("DELETE FROM guests WHERE ip='$userip' OR date<".(ctime() - 300));
}

if ($log) {
    if (($loguser['powerlevel'] <= 5) && (!defined('IS_AJAX_REQUEST') || !IS_AJAX_REQUEST)) {
        $influencelv = calclvl(calcexp($loguser['posts'], (ctime() - $loguser['regdate']) / 86400));

        // Alart #defcon?
        if ($loguser['lastip'] != $_SERVER['REMOTE_ADDR']) {
            $ip1 = explode('.', $loguser['lastip']);
            $ip2 = explode('.', $_SERVER['REMOTE_ADDR']);
            for ($diff = 0; $diff < 3; ++$diff) {
                if ($ip1[$diff] != $ip2[$diff]) {
                    break;
                }
            }
            if (0 == $diff) {
                $color = xk(4);
            } else {
                $color = xk(8);
            }
            $diff = '/'.($diff + 1) * 8;

            report_notice("User $loguser[name] (id $loguserid) changed from IP {$loguser['lastip']} to {$_SERVER['REMOTE_ADDR']}.");
        }

        $sql->query('UPDATE users SET lastactivity='.ctime().",lastip='$userip',lasturl='".mysql_real_escape_string($url)."',lastforum=0,`influence`='$influencelv' WHERE id=$loguserid");
    }
} else if ($GLOBALS['jul_valid_db']) {
    $sql->query("INSERT INTO guests (ip,date,useragent,lasturl) VALUES ('$userip',".ctime().",'".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."','".mysql_real_escape_string($url)."')");
}

$header = makeheader($header1, $headlinks, $header2);

$footer = '	</textarea></form></embed></noembed></noscript></noembed></embed></table></table>
<br>'.($loguser['id'] && false === strpos($PHP_SELF, 'index.php') ? adbox().'<br>' : '')."
<center>
<br>
$smallfont
<br><br><a href={$GLOBALS['jul_settings']['site_url']}>{$GLOBALS['jul_settings']['site_name']}</a>
<br>".filter_string($affiliatelinks)."
<br>
<table cellpadding=0 border=0 cellspacing=2><tr>
<td>
	<img class=\"pointresize\" src={$GLOBALS['jul_base_dir']}/static/images/poweredbyacmlm.gif>
</td>
<td>
	".version_footer().'
</td>
</tr></table>



</body></html>
';
if ($ipbanned) {
    if ('Banned; account hijacked. Contact admin via PM to change it.' == $loguser['title']) {
        $reason = 'Your account was hijacked; please contact '.$GLOBALS['jul_settings']['primary_admin_name'].' to reset your password and unban your account.';
    } elseif ($loguser['title']) {
        $reason = 'Ban reason: '.$loguser['title'].'<br>If you think have been banned in error, please contact '.$GLOBALS['jul_settings']['primary_admin_name'].'.';
    } else {
        $reason = $sql->resultq("SELECT `reason` FROM ipbans WHERE $checkips", 0, 0);
        $reason = ($reason ? "Reason: $reason" : '<i>(No reason given)</i>');
    }
    die("$header<br>$tblstart$tccell1>
You are banned from this board.
<br>".$reason."
<br>
<br>If you think you have been banned in error, please contact the administrator:
<br>E-mail: ".$GLOBALS['jul_settings']['primary_admin_email']."
$tblend$footer");
}
if ($torbanned) {
    die("$header<br>$tblstart$tccell1>
You appear to be using a Tor proxy. Due to abuse, Tor usage is forbidden.
<br>If you have been banned in error, please contact ".$GLOBALS['jul_settings']['primary_admin_name'].".
<br>
<br>E-mail: ".$GLOBALS['jul_settings']['primary_admin_email']."
$tblend$footer");
}
