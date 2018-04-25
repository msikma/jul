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

$home = base_dir().'/';
$GLOBALS['jul_settings']['board_title'] = "<a href='{$home}'>{$GLOBALS['jul_settings']['board_title']}</a>";

$race = $loguserid ? postradar($loguserid) : '';

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

// TODO: make this show up for all admins? Test it.
if (in_array($loguserid, array(1, 5, 2100))) {
    $xminilog = $sql->fetchq('SELECT COUNT(*) as count, MAX(`time`) as time FROM `minilog`');
    if ($xminilog['count']) {
        $xminilogip = $sql->fetchq('SELECT `ip`, `banflags` FROM `minilog` ORDER BY `time` DESC LIMIT 1');
        $GLOBALS['jul_settings']['board_title'] .= "<br><a href='{$GLOBALS['jul_views_path']}/shitbugs.php'><span class=font style=\"color: #f00\"><b>".$xminilog['count'].'</b> suspicious request(s) logged, last at <b>'.date($dateformat, $xminilog['time'] + $tzoff).'</b> by <b>'.$xminilogip['ip'].' ('.$xminilogip['banflags'].')</b></span></a>';
    }
    $xminilog = $sql->fetchq('SELECT COUNT(*) as count, MAX(`time`) as time FROM `pendingusers`');
    if ($xminilog['count']) {
        $xminilogip = $sql->fetchq('SELECT `username`, `ip` FROM `pendingusers` ORDER BY `time` DESC LIMIT 1');
        $GLOBALS['jul_settings']['board_title'] .= "<br><span class='font' style=\"color: #ff0\"><b>".$xminilog['count']."</b> pending user(s), last <b>'".$xminilogip['username']."'</b> at <b>".date($dateformat, $xminilog['time'] + $tzoff).'</b> by <b>'.$xminilogip['ip'].'</b></span>';
    }
}
$headlinks2 = array();
foreach ($GLOBALS['jul_settings']['top_menu_items'] as $row) {
    $rowlinks = array();
    foreach ($row as $item) {
        $rowlinks[] = '<a href="'.to_route($item[0]).'">'.$item[1].'</a>';
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

if ($sql->resultq("SELECT count(*) FROM ipbans WHERE $checkips")) {
    $ipbanned = 1;
}
if ($sql->resultq("SELECT count(*) FROM `tor` WHERE `ip` = '".$_SERVER['REMOTE_ADDR']."' AND `allowed` = '0'")) {
    $torbanned = 1;
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

$views = $sql->resultq('SELECT views FROM misc') + 1;

if (!$ipbanned && !$torbanned && (!defined('IS_AJAX_REQUEST') || !IS_AJAX_REQUEST)) {
    // Don't increment the view counter for bots
    // Todo: Actually check for bots and disable it because hdurfs
    $sql->query("UPDATE misc SET views=$views");

    if ($views % 10000000 > 9999000 or $views % 10000000 < 1000) {
        $u = ($loguserid ? $loguserid : 0);
        $ct = ctime();
        $sql->query("INSERT INTO hits VALUES ({$views},{$u},'{$userip}',{$ct})");
    }
}

// Dailystats update in one query
$sql->query('INSERT INTO dailystats (date, users, threads, posts, views) '.
             "VALUES ('".date('m-d-y', ctime())."', (SELECT COUNT( * ) FROM users), (SELECT COUNT(*) FROM threads), (SELECT COUNT(*) FROM posts), $views) ".
             "ON DUPLICATE KEY UPDATE users=VALUES(users), threads=VALUES(threads), posts=VALUES(posts), views=$views");

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
  $ikachan = $GLOBALS['jul_themes_path'].'/default/images/squid.png';
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
  $yyy = "<img id='f_ikachan' src='$ikachan' style=\"z-index: 999999; position: fixed; left: ".mt_rand(0, 100).'%; top: '.mt_rand(0, 100)."%;\" title=\"$ikaquote\">";
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

$header1 = "<html><head><meta http-equiv='Content-type' content='text/html; charset=utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>$windowtitle</title>
{$GLOBALS['jul_js_vars']}
$metatag
<link rel=\"shortcut icon\" href=\"/images/favicons/favicon".(!$x_hacks['host'] ? rand(1, 8).'' : '').".ico\" type=\"image/x-icon\">
<link rel='stylesheet' href='{$GLOBALS['jul_base_dir']}/css/base.css' type='text/css'>
<link rel='stylesheet' href='{$GLOBALS['jul_views_path']}/theme/style.css' type='text/css'>
$css
</head>
$body
$yyy
<center>
$tblstart
<form action='{$GLOBALS['jul_views_path']}/login.php' method='post' name='logout'><input type='hidden' name='action' value='logout'></form>
<td class='tbl tdbg1 center' colspan=3>{$GLOBALS['jul_settings']['board_title']}";
$header2 = '
'.(!$x_hacks['smallbrowse'] ? "
</td><tr>
  <td width='120px' class='tbl tdbg2 center fonts'><nobr>Views: $dispviews<br><img src={$GLOBALS['jul_base_dir']}/images/_.gif width=120 height=1></td>
  <td width='100%' class='tbl tdbg2 center fonts'>$headlinks2</td>
  <td width='120px' class='tbl tdbg2 center fonts'><nobr>".date($dateformat, ctime() + $tzoff)."<br><img src={$GLOBALS['jul_base_dir']}/images/_.gif width=120 height=1><tr>"
    : "<br>$dispviews views, ".date($dateformat, ctime() + $tzoff)."
  </td><tr>
	<td width=100% class='tbl tdbg2 center fonts' colspan=3>$headlinks2</td><tr>")."
<td colspan=3 class='tbl tdbg1 center fonts'>$race
$privatebox
$tblend
</center>";

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

function version_footer()
{
    global $smallfont;
    // Take the first part of the name ('dada/jul') to put in front of the version.
    // So people can see it's not stock Jul.
    $name = explode('/', $GLOBALS['jul_version']['composer']['name']);
    $commit = $GLOBALS['jul_version']['commit']['hash']
        ? " - <a href='{$GLOBALS['jul_version']['commit']['url']}'>{$GLOBALS['jul_version']['commit']['string']}</a>"
        : '';

    return "
{$smallfont}
	Jul v{$GLOBALS['jul_version']['version']}-{$name[0]} r{$GLOBALS['jul_version']['commit']['rev']} {$commit}
	<br>&copy;{$GLOBALS['jul_version']['copyright_start']}-{$GLOBALS['jul_version']['copyright_end']} {$GLOBALS['jul_version']['authors']}
</font>";
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
    $sql->query('INSERT INTO referer (time,url,ref,ip) VALUES ('.ctime().", '".addslashes($url)."', '".addslashes($ref)."', '".$_SERVER['REMOTE_ADDR']."')");
}

$sql->query("DELETE FROM guests WHERE ip='$userip' OR date<".(ctime() - 300));

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

        $sql->query('UPDATE users SET lastactivity='.ctime().",lastip='$userip',lasturl='".addslashes($url)."',lastforum=0,`influence`='$influencelv' WHERE id=$loguserid");
    }
} else {
    $sql->query("INSERT INTO guests (ip,date,useragent,lasturl) VALUES ('$userip',".ctime().",'".addslashes($_SERVER['HTTP_USER_AGENT'])."','".addslashes($url)."')");
}

$header = makeheader($header1, $headlinks, $header2);

$footer = '	</textarea></form></embed></noembed></noscript></noembed></embed></table></table>
<br>'.($loguser['id'] && false === strpos($PHP_SELF, 'index.php') ? adbox().'<br>' : '')."
<center>

<!--
<img src='{$GLOBALS['jul_views_path']}/adnonsense.php?m=d' title='generous donations to the first national bank of bad jokes and other dumb crap people post' style='margin-left: 44px;'><br>
<img src='{$GLOBALS['jul_views_path']}/adnonsense.php' title='hotpod fund' style='margin: 0 22px;'><br>
<img src='{$GLOBALS['jul_views_path']}/adnonsense.php?m=v' title='VPS slushie fund' style='margin-right: 44px;'>
-->
<br>
$smallfont
<br><br><a href={$GLOBALS['jul_settings']['site_url']}>{$GLOBALS['jul_settings']['site_name']}</a>
<br>".filter_string($affiliatelinks)."
<br>
<table cellpadding=0 border=0 cellspacing=2><tr>
<td>
	<img class=\"pointresize\" src={$GLOBALS['jul_base_dir']}/images/poweredbyacmlm.gif>
</td>
<td>
	".version_footer().'
</td>
</tr></table>
'.($x_hacks['mmdeath'] >= 0 ? "<div style='position: absolute; top: -100px; left: -100px;'>Hidden preloader for doom numbers:
<img src='numgfx/death/0.png'> <img src='numgfx/death/1.png'> <img src='numgfx/death/2.png'> <img src='numgfx/death/3.png'> <img src='numgfx/death/4.png'> <img src='numgfx/death/5.png'> <img src='numgfx/death/6.png'> <img src='numgfx/death/7.png'> <img src='numgfx/death/8.png'> <img src='numgfx/death/9.png'>" : '').'


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