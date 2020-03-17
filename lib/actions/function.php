<?php

// Note: this file contains by far the most garbage of all, because it contains most
// of the original Jul code that runs on every page.

use Coduo\PHPHumanizer\DateTimeHumanizer;

$theme_base = $GLOBALS['jul_base_dir']."/themes/default/";
require_once 'themes/default/settings.php';

$startingtime = microtime(true);

// Run a check on the database to see if the forum is operating normally.
// If there's something wrong with the setup, certain database actions
// will not be attempted, such as checking whether the user is banned or not,
// or updating the stats and views tables.
//
// If the database has a problem, the error handling will happen in route.php.
// There we'll decide whether to display an error or start the installer.
$GLOBALS['jul_db_status'] = check_db();
$GLOBALS['jul_installed'] = check_installed();
$GLOBALS['jul_valid_db'] = $GLOBALS['jul_db_status'][0] === true && $GLOBALS['jul_installed'] === true;

// Awful old legacy thing. Too much code relies on register globals,
// and doesn't distinguish between _GET and _POST, so we have to do it here. fun
$id = filter_int($_POST['id']);
if (null === $id) {
  $id = filter_int($_GET['id']);
}
if (null === $id) {
  $id = 0;
}

// This array will contain our post filters.
// We load every PHP file inside 'filters' and let them populate this.
$GLOBALS['jul_postfilters'] = array();
$path = $GLOBALS['jul_base_path'].'/lib/filters/';
$dir = new DirectoryIterator($path);
$filters = array();
foreach ($dir as $fileinfo) {
  if ($fileinfo->isDot()) {
  continue;
  }
  include_once($fileinfo->getPathname());
  // TODO: make some system for storing and loading settings in the database.
}

// This is the only filter we explicitly import.
require_once 'lib/filters/bbcode.php';
// This is a replacement for doreplace2().
function bbcode_format($msg, $options) {
  return jul_postfilter_bbcode($msg, $options);
}

if (filter_int($die) || filter_int($_GET['sec'])) {
  if ($die) {
    $sql->query("INSERT INTO `minilog` SET `ip` = '".$_SERVER['REMOTE_ADDR']."', `time` = '".ctime()."', `banflags` = '$banflags'");

    if ($_COOKIE['loguserid'] > 0) {
      $newid = 0;
    } elseif (!$_COOKIE['loguserid']) {
      $newid = 0 - ctime();
    }

    if ($newid) {
      setcookie('loguserid', $newid, 2147483647);
    }
  }
  suspicious_die();
}

try {
  if ($sql->resultq('SELECT `disable` FROM `misc` WHERE 1')) {
    downtime_die();
  }
}
catch (Exception $e) {
}

// Include the hex color JS file.
$js_include = array();
$js_include[] = $GLOBALS['jul_base_path'].'/static/js/hex.js';

$dateformat = $GLOBALS['jul_settings']['date_format_long'];
$dateshort = $GLOBALS['jul_settings']['date_format_short'];

// Get the current logged in user.
$loguser = get_logged_in_user();

$tzoff = 0;

if ($loguser) {
  $loguserid = $loguser['id'];
  $tzoff = $loguser['timezone'] * 3600;
  $scheme = $loguser['scheme'];
  if ($loguser['dateformat']) {
    $dateformat = $loguser['dateformat'];
  }
  if ($loguser['dateshort']) {
    $dateshort = $loguser['dateshort'];
  }

  $log = 1;

  if ($loguser['viewsig'] >= 3) {
    return header('Location: /?sec=1');
  }
  if ($loguser['powerlevel'] >= 1) {
    $GLOBALS['jul_settings']['board_title'] .= $submessage;
  }

  if (175 == $loguser['id'] && !$x_hacks['host']) {
    $loguser['powerlevel'] = max($loguser['powerlevel'], 3);
  }
} else {
  $loguserid = null;
  $loguser = array();
  $loguser['viewsig'] = 0;
  $loguser['powerlevel'] = 0;
  $loguser['signsep'] = 0;
  $loguser['id'] = null;
  $log = 0;
}

$scheme = filter_int($scheme);
if (isset($_GET['scheme']) && is_numeric($_GET['scheme'])) {
  $scheme = intval($_GET['scheme']);
} elseif (isset($_GET['scheme'])) {
  $scheme = 0;
}

// Load the default theme first. All themes inherit it and can override it.
$theme_base = $GLOBALS['jul_base_dir']."/themes/default/";
include "themes/default/settings.php";
$theme_base = $GLOBALS['jul_base_dir']."/themes/default/";
include "themes/default/layout.php";

// Load theme settings. 'Night' is the default theme.
try {
  $schemerow = $sql->fetchq("SELECT `name`, `file` FROM schemes WHERE id='$scheme'");
  $theme = $schemerow ? $schemerow['file'] : 'night';
}
catch (Exception $e) {
  $theme = 'night';
}

// Include the chosen theme settings, which sets up its variables/colors.
$theme_base = $GLOBALS['jul_base_dir']."/themes/$theme/";
if (file_exists($GLOBALS['jul_base_path']."/themes/$theme/settings.php")) {
  include "themes/$theme/settings.php";
}

// Load the theme's layout file, which determines how the posts get rendered.
if (file_exists($GLOBALS['jul_base_path']."/themes/$theme/layout.php")) {
  $theme_base = $GLOBALS['jul_base_dir']."/themes/$theme/";
  include "themes/$theme/layout.php";
}

// Special theme that activates when a user is viewing a specific forum.
if ($specialscheme) {
  $theme_base = $GLOBALS['jul_base_dir']."/themes/$specialscheme/";
  if (file_exists($GLOBALS['jul_base_path']."/themes/$specialscheme/settings.php")) {
  include "themes/$specialscheme/settings.php";
  }
  if (file_exists($GLOBALS['jul_base_path']."/themes/$specialscheme/layout.php")) {
  $theme_base = $GLOBALS['jul_base_dir']."/themes/$specialscheme/";
  include "themes/$specialscheme/layout.php";
  }
}


if ($x_hacks['superadmin']) {
  $loguser['powerlevel'] = 4;
}

$power = $loguser['powerlevel'];
$banned = ($power < 0);
$ismod = ($power >= 2);
$isadmin = ($power >= 3);
if ($banned) {
  $power = 0;
}

$specialscheme = '';

try {
  $x_hacks['rainbownames'] = ($sql->resultq('SELECT `date` FROM `posts` WHERE (`id` % 100000) = 0 ORDER BY `id` DESC LIMIT 1') > ctime() - 86400);
}
catch (Exception $e) {
  $x_hacks['rainbownames'] = false;
}

function filter_int(&$v)
{
  if (!isset($v)) {
    return null;
  } else {
    $v = intval($v);

    return $v;
  }
}

function filter_bool(&$v)
{
  if (!isset($v)) {
    return null;
  } else {
    $v = (bool) $v;

    return $v;
  }
}

function filter_string(&$v)
{
  if (!isset($v)) {
    return null;
  } else {
    $v = (string) $v;

    return $v;
  }
}

// Used by 'rendertime.png'.
function sinc($x) {
  $ret  = ($x ? sin($x*pi())/($x*pi()) : 1);
  return $ret;
}

function timeunits($sec)
{
  if ($sec < 60) {
    return "$sec sec.";
  }
  if ($sec < 3600) {
    return floor($sec / 60).' min.';
  }
  if ($sec < 7200) {
    return '1 hour';
  }
  if ($sec < 86400) {
    return floor($sec / 3600).' hours';
  }
  if ($sec < 172800) {
    return '1 day';
  }
  if ($sec < 31556926) {
    return floor($sec / 86400).' days';
  }

  return sprintf('%.1f years', floor($sec / 31556926));
}

function timeunits2($sec)
{
  $d = floor($sec / 86400);
  $h = floor($sec / 3600) % 24;
  $m = floor($sec / 60) % 60;
  $s = $sec % 60;
  $ds = (1 != $d ? 's' : '');
  $hs = (1 != $h ? 's' : '');
  $str = ($d ? "$d day$ds " : '').($h ? "$h hour$hs " : '').($m ? "$m min. " : '').($s ? "$s sec." : '');
  if (' ' == substr($str, -1)) {
    $str = substr_replace($str, '', -1);
  }

  return $str;
}

function calcexpgainpost($posts, $days)
{
  return @floor(1.5 * @pow($posts * $days, 0.5));
}
function calcexpgaintime($posts, $days)
{
  $val = (int)sprintf('%01d', 172800 * @(@pow(@($days / $posts), 0.5) / $posts));
  $str = DateTimeHumanizer::preciseDifference(date_create_from_format('U', time()), date_create_from_format('U', time() + $val));
  $str = str_replace('from now', '', $str);
  $str = preg_replace('/(, [0-9]+ seconds)/', '', $str);
  $str = explode(',', $str);
  return implode(',', array_slice($str, 0, -2)).', '.implode(' and ', array_slice($str, -2));
}

function calcexpleft($exp)
{
  return calclvlexp(calclvl($exp) + 1) - $exp;
}
function totallvlexp($lvl)
{
  return calclvlexp($lvl + 1) - calclvlexp($lvl);
}

function calclvlexp($lvl)
{
  if (1 == $lvl) {
    return 0;
  } else {
    return floor(pow(abs($lvl), 3.5)) * ($lvl > 0 ? 1 : -1);
  }
}
function calcexp($posts, $days)
{
  if (@($posts / $days) > 0) {
    return floor($posts * pow($posts * $days, 0.5));
  } elseif (0 == $posts) {
    return 0;
  } else {
    return 'NaN';
  }
}
function calclvl($exp)
{
  if ($exp >= 0) {
    $lvl = floor(@pow($exp, 2 / 7));
    if (calclvlexp($lvl + 1) == $exp) {
      $lvl++;
    }
    if (!$lvl) {
      $lvl = 1;
    }
  } else {
    $lvl = -floor(pow(-$exp, 2 / 7));
  }
  if (is_string($exp) && 'NaN' == $exp) {
    $lvl = 'NaN';
  }

  return $lvl;
}

function generatenumbergfx($num, $minlen = 0, $double = false)
{
  global $numdir;
  $nw = 8 * ($double ? 2 : 1);
  $num = strval($num);
  $gfxcode = '';
  $img_base = base_dir().'/';

  if ($minlen > 1 && strlen($num) < $minlen) {
    $gfxcode = "<img class='pointresize' src=\"{$img_base}static/images/spacer.gif\" width=".($nw * ($minlen - strlen($num))).' height='.$nw.'>';
  }

  for ($i = 0; $i < strlen($num); ++$i) {
    $code = $num[$i];
    switch ($code) {
      case '/':
        $code = 'slash';
        break;
    }
    if (' ' == $code) {
      $gfxcode .= "<img class='pointresize' src={$img_base}static/images/spacer.gif width=$nw height=$nw>";
    } else {
      $gfxcode .= "<img class='pointresize' src={$img_base}numgfx/$numdir$code.png width=$nw height=$nw>";
    }
  }

  return $gfxcode;
}

function doreplace($msg, $posts, $days, $username, &$tags = null)
{
  return $msg;
}

function escape_codeblock($text)
{
  $list = array('[code]', '[/code]', '<', '\\"', '\\\\', "\\'", '[', ':', ')', '_');
  $list2 = array('', '', '&lt;', '"', '\\', "\'", '&#91;', '&#58;', '&#41;', '&#95;');

  // @TODO why not just use htmlspecialchars() or htmlentities()
  return '[quote]<code>'.str_replace($list, $list2, $text[0]).'</code>[/quote]';
}

function doforumlist($id)
{
  global $fonttag,$loguser,$power,$sql;
  $forumlinks = "
  <table><td>$fonttag Forum jump: </td>
  <td><form><select onChange=parent.location=this.options[this.selectedIndex].value style=\"position:relative;top:8px;\">
  ";

  $cats = $sql->query("SELECT id,name,minpower FROM categories WHERE (minpower<=$power OR minpower<=0) ORDER BY id ASC");
  while ($cat = $sql->fetch($cats)) {
    $fjump[$cat['id']] = '<optgroup label="'.$cat['name'].'">';
  }

  $forum1 = $sql->query("SELECT id,title,catid FROM forums WHERE (minpower<=$power OR minpower<=0) AND `hidden` = '0' AND `id` != '0' OR `id` = '$id' ORDER BY forder") or print mysql_error();
  while ($forum = $sql->fetch($forum1)) {
    $forum_link = route('@forum', $forum['id']);
    $fjump[$forum['catid']] .= "<option value='{$forum_link}'".($forum['id'] == $id ? ' selected' : '')."'>$forum[title]</option>";
  }

  foreach ($fjump as $jtext) {
    $forumlinks .= $jtext.'</optgroup>';
  }
  $forumlinks .= '</select></table></form>';

  return $forumlinks;
}

function ctime()
{
  return time();
}
function cmicrotime()
{
  return microtime(true) + 3 * 3600;
}

function getrank($rankset, $title, $posts, $powl)
{
  global $hacks, $sql;
  $rank = '';
  if (255 == $rankset) {   //special code for dots
    if (!$hacks['noposts']) {
      $pr[5] = 5000;
      $pr[4] = 1000;
      $pr[3] = 250;
      $pr[2] = 50;
      $pr[1] = 10;

      if ($rank) {
        $rank .= '<br>';
      }
      $postsx = $posts;
      $dotnum[5] = floor($postsx / $pr[5]);
      $postsx = $postsx - $dotnum[5] * $pr[5];
      $dotnum[4] = floor($postsx / $pr[4]);
      $postsx = $postsx - $dotnum[4] * $pr[4];
      $dotnum[3] = floor($postsx / $pr[3]);
      $postsx = $postsx - $dotnum[3] * $pr[3];
      $dotnum[2] = floor($postsx / $pr[2]);
      $postsx = $postsx - $dotnum[2] * $pr[2];
      $dotnum[1] = floor($postsx / $pr[1]);

      foreach ($dotnum as $dot => $num) {
        for ($x = 0; $x < $num; ++$x) {
          $rank .= "<img src={$img_base}images/dot".$dot.'.gif align="absmiddle">';
        }
      }
      if ($posts >= 10) {
        $rank = floor($posts / 10) * 10 .' '.$rank;
      }
    }
  } elseif ($rankset) {
    $posts %= 10000;
    $rank = @$sql->resultq("SELECT text FROM ranks WHERE num<=$posts AND rset=$rankset ORDER BY num DESC LIMIT 1", 0, 0, true);
  }

  $powerranks = array(
    -1 => 'Banned',
    //1  => '<b>Staff</b>',
    2 => '<b>Moderator</b>',
    3 => '<b>Administrator</b>',
  );

  if ($rank && (in_array($powl, $powerranks) || $title)) {
    $rank .= '<br>';
  }

  if ($title) {
    $rank .= $title;
  } elseif (in_array($powl, $powerranks)) {
    $rank .= filter_string($powerranks[$powl]);
  }

  return $rank;
}

function updategb()
{
  global $sql;
  $hranks = $sql->query('SELECT posts FROM users WHERE posts>=1000 ORDER BY posts DESC');
  $c = mysql_num_rows($hranks);

  for ($i = 1; ($hrank = $sql->fetch($hranks)) && $i <= $c * 0.7; ++$i) {
    $n = $hrank[posts];
    if ($i == floor($c * 0.001)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=3%'");
    } elseif ($i == floor($c * 0.01)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=4%'");
    } elseif ($i == floor($c * 0.03)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=5%'");
    } elseif ($i == floor($c * 0.06)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=6%'");
    } elseif ($i == floor($c * 0.10)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=7%'");
    } elseif ($i == floor($c * 0.20)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=8%'");
    } elseif ($i == floor($c * 0.30)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=9%'");
    } elseif ($i == floor($c * 0.50)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=10%'");
    } elseif ($i == floor($c * 0.70)) {
      $sql->query("UPDATE ranks SET num=$n WHERE rset=3 AND text LIKE '%=11%'");
    }
  }
}

function shenc($str)
{
  $l = strlen($str);
  for ($i = 0; $i < $l; ++$i) {
    $n = (308 - ord($str[$i])) % 256;
    $e[($i + 5983) % $l] += floor($n / 16);
    $e[($i + 5984) % $l] += ($n % 16) * 16;
  }
  for ($i = 0; $i < $l; ++$i) {
    $s .= chr($e[$i]);
  }

  return $s;
}
function shdec($str)
{
  $l = strlen($str);
  $o = 10000 - 10000 % $l;
  for ($i = 0; $i < $l; ++$i) {
    $n = ord($str[$i]);
    $e[($i + $o - 5984) % $l] += floor($n / 16);
    $e[($i + $o - 5983) % $l] += ($n % 16) * 16;
  }
  for ($i = 0; $i < $l; ++$i) {
    $e[$i] = (308 - $e[$i]) % 256;
    $s .= chr($e[$i]);
  }

  return $s;
}
function fadec($c1, $c2, $pct)
{
  $pct2 = 1 - $pct;
  $cx1[r] = hexdec(substr($c1, 0, 2));
  $cx1[g] = hexdec(substr($c1, 2, 2));
  $cx1[b] = hexdec(substr($c1, 4, 2));
  $cx2[r] = hexdec(substr($c2, 0, 2));
  $cx2[g] = hexdec(substr($c2, 2, 2));
  $cx2[b] = hexdec(substr($c2, 4, 2));
  $ret = floor($cx1[r] * $pct2 + $cx2[r] * $pct) * 65536 +
   floor($cx1[g] * $pct2 + $cx2[g] * $pct) * 256 +
   floor($cx1[b] * $pct2 + $cx2[b] * $pct);
  $ret = dechex($ret);

  return $ret;
}

function getuserlink(&$u, $substitutions = null, $urlclass = '')
{
  if (true === $substitutions) {
    global $herpderpwelp;
    if (!$herpderpwelp) {
      trigger_error('Deprecated: $substitutions passed true (old behavior)', E_USER_NOTICE);
    }
    $herpderpwelp = true;
  }

  // dumb hack for $substitutions
  $fn = array(
    'aka' => 'aka',
    'id' => 'id',
    'name' => 'name',
    'sex' => 'sex',
    'powerlevel' => 'powerlevel',
    'birthday' => 'birthday',
  );
  if ($substitutions) {
    $fn = array_merge($fn, $substitutions);
  }

  $akafield = htmlspecialchars($u[$fn['aka']], ENT_QUOTES);
  $alsoKnownAs = (($u[$fn['aka']] && $u[$fn['aka']] != $u[$fn['name']])
    ? " title='Also known as: {$akafield}'" : '');

  $u[$fn['name']] = htmlspecialchars($u[$fn['name']], ENT_QUOTES);

  global $tzoff;
  $birthday = (date('m-d', $u[$fn['birthday']]) == date('m-d', ctime() + $tzoff));
  $rsex = (($birthday) ? 255 : $u[$fn['sex']]);

  $namecolor = getnamecolor($rsex, $u[$fn['powerlevel']], false);

  if ($urlclass) {
    $class = " class='{$urlclass}'";
  } else {
    $class = '';
  }

  return "<a style='color:#{$namecolor};'{$class} href='{$GLOBALS['jul_views_path']}/profile.php?id="
    .$u[$fn['id']]."'{$alsoKnownAs}>".$u[$fn['name']].'</a>';
}

// eventually: change/remove prefix. ugh. it's there so nothing old breaks.
function getnamecolor($sex, $powl, $prefix = true)
{
  global $nmcol, $x_hacks;

  // don't let powerlevels above admin have a blank color
  $powl = min(3, $powl);

  $namecolor = (($prefix) ? 'color=' : '');

  if ($powl < 0) { // always dull drab banned gray.
    $namecolor .= $nmcol[0][$powl];
  }

  // RAINBOW MULTIPLIER
  elseif ($x_hacks['rainbownames'] || 255 == $sex) {
    $stime = gettimeofday();
    // slowed down 5x
    $h = (($stime['usec'] / 25) % 600);
    if ($h < 100) {
      $r = 255;
      $g = 155 + $h;
      $b = 155;
    } elseif ($h < 200) {
      $r = 255 - $h + 100;
      $g = 255;
      $b = 155;
    } elseif ($h < 300) {
      $r = 155;
      $g = 255;
      $b = 155 + $h - 200;
    } elseif ($h < 400) {
      $r = 155;
      $g = 255 - $h + 300;
      $b = 255;
    } elseif ($h < 500) {
      $r = 155 + $h - 400;
      $g = 155;
      $b = 255;
    } else {
      $r = 255;
      $g = 155;
      $b = 255 - $h + 500;
    }
    $namecolor .= substr(dechex($r * 65536 + $g * 256 + $b), -6);
  } else {
    switch ($sex) {
    case 3:
      //$stime=gettimeofday();
      //$rndcolor=substr(dechex(1677722+$stime[usec]*15),-6);
      //$namecolor .= $rndcolor;
      $nc = mt_rand(0, 0xffffff);
      $namecolor .= str_pad(dechex($nc), 6, '0', STR_PAD_LEFT);
      break;
    case 4:
      $namecolor .= 'ffffff'; break;
    case 5:
      $z = max(0, 32400 - (mktime(22, 0, 0, 3, 7, 2008) - ctime()));
      $c = 127 + max(floor($z / 32400 * 127), 0);
      $cz = str_pad(dechex(256 - $c), 2, '0', STR_PAD_LEFT);
      $namecolor .= str_pad(dechex($c), 2, '0', STR_PAD_LEFT).$cz.$cz;
      break;
    case 6:
      $namecolor .= '60c000'; break;
    case 7:
      $namecolor .= 'ff3333'; break;
    case 8:
      $namecolor .= '6688aa'; break;
    case 9:
      $namecolor .= 'cc99ff'; break;
    case 10:
      $namecolor .= 'ff0000'; break;
    case 11:
      $namecolor .= '6ddde7'; break;
    case 12:
      $namecolor .= 'e2d315'; break;
    case 13:
      $namecolor .= '94132e'; break;
    case 14:
      $namecolor .= 'ffffff'; break;
    case 21: // Sofi
      $namecolor .= 'DC143C'; break;
    case 22: // Nicole
      $namecolor .= 'FFB3F3'; break;
    case 23: // Rena
      $namecolor .= '77ECFF'; break;
    case 24: // Adelheid
      $namecolor .= 'D2A6E1'; break;
    case 41:
      $namecolor .= '8a5231'; break;
    case 42:
      $namecolor .= '20c020'; break;
    case 99:
      $namecolor .= 'EBA029'; break;
    case 98:
      $namecolor .= $nmcol[0][3]; break;
    case 97:
      $namecolor .= '6600DD'; break;
    default:
      $namecolor .= $nmcol[$sex][$powl];
      break;
  }
  }

  return $namecolor;
}

function fonlineusers($id)
{
  global $userip,$loguser,$sql;

  if ($loguser['id']) {
    $sql->query("UPDATE users SET lastforum=$id WHERE id=".$loguser['id']);
  } else {
    $sql->query("UPDATE guests SET lastforum=$id WHERE ip='$userip'");
  }

  $forumname = @$sql->resultq("SELECT title FROM forums WHERE id=$id", 0, 0);
  $onlinetime = ctime() - 300;
  $onusers = $sql->query("SELECT id,name,lastactivity,minipic,lasturl,aka,sex,powerlevel,birthday FROM users WHERE lastactivity>$onlinetime AND lastforum=$id ORDER BY name");

  $onlineusers = '';

  for ($numon = 0; $onuser = $sql->fetch($onusers); ++$numon) {
    if ($numon) {
      $onlineusers .= ', ';
    }

    /* if ((!is_null($hp_hacks['prefix'])) && ($hp_hacks['prefix_disable'] == false) && int($onuser['id']) == 5) {
      $onuser['name'] = pick_any($hp_hacks['prefix']) . " " . $onuser['name'];
    } */

    $namelink = getuserlink($onuser);
    $onlineusers .= '<nobr>';
    $onuser['minipic'] = str_replace('>', '&gt;', $onuser['minipic']);
    if ($onuser['minipic']) {
      $onlineusers .= "<img width=16 height=16 src=$onuser[minipic] align=top> ";
    }
    if ($onuser['lastactivity'] <= $onlinetime) {
      $namelink = "($namelink)";
    }
    $onlineusers .= "$namelink</nobr>";
  }
  $p = ($numon ? ':' : '.');
  $s = (1 != $numon ? 's' : '');
  $numguests = $sql->resultq("SELECT count(*) AS n FROM guests WHERE date>$onlinetime AND lastforum=$id", 0, 0);
  if ($numguests) {
    $guests = "| $numguests guest".($numguests > 1 ? 's' : '');
  }

  return "$numon user$s currently in $forumname$p $onlineusers $guests";
}

/* WIP
$jspcount = 0;
function jspageexpand($start, $end) {
  global $jspcount;

  if (!$jspcount) {
    echo '
      <script type="text/javascript">
        function pageexpand(uid,st,en)
        {
          var elem = document.getElementById(uid);
          var res = "";
        }
      </script>
    ';
  }

  $entityid = "expand" . ++$jspcount;

  $js = "#todo";
  return $js;
}
*/

function redirect($url, $msg, $delay = 0)
{
  return $delay ? "You will now be redirected to <a href=$url>$msg</a>...<META HTTP-EQUIV=REFRESH CONTENT=$delay;URL=$url>" : "Return to <a href=$url>$msg</a>.";
}

function postradar($userid)
{
  global $sql, $loguser, $loguser;
  if (!$userid) {
    return '';
  }

  //$postradar = $sql->query("SELECT posts,id,name,aka,sex,powerlevel,birthday FROM users u RIGHT JOIN postradar p ON u.id=p.comp WHERE p.user={$userid} ORDER BY posts DESC", MYSQL_ASSOC);
  $postradar = $sql->query("SELECT posts,id,name,aka,sex,powerlevel,birthday FROM users,postradar WHERE postradar.user={$userid} AND users.id=postradar.comp ORDER BY posts DESC", MYSQL_ASSOC);
  if (@mysql_num_rows($postradar) > 0) {
    $race = 'You are ';

    function cu($a, $b)
    {
      global $hacks;

      $dif = $a - $b['posts'];
      if ($dif < 0) {
        $t = (!$hacks['noposts'] ? -$dif : '').' behind';
      } elseif ($dif > 0) {
        $t = (!$hacks['noposts'] ? $dif : '').' ahead of';
      } else {
        $t = ' tied with';
      }

      $namelink = getuserlink($b);
      $t .= " {$namelink}".(!$hacks['noposts'] ? " ($b[posts])" : '');

      return "<nobr>{$t}</nobr>";
    }

    // Save ourselves a query if we're viewing our own post radar
    // since we already fetch all user fields for $loguser
    if ($userid == $loguser['id']) {
      $myposts = $loguser['posts'];
    } else {
      $myposts = $sql->resultq("SELECT posts FROM users WHERE id=$userid");
    }

    for ($i = 0; $user2 = $sql->fetch($postradar); ++$i) {
      if ($i) {
        $race .= ', ';
      }
      if ($i && $i == mysql_num_rows($postradar) - 1) {
        $race .= 'and ';
      }
      $race .= cu($myposts, $user2);
    }
  }

  return $race;
}

function loaduser($id, $type)
{
  global $sql;
  if (1 == $type) {
    $fields = 'id,name,sex,powerlevel,posts';
  }

  return @$sql->fetchq("SELECT $fields FROM users WHERE id=$id");
}

function getpostlayoutid($text)
{
  global $sql;
  $id = @$sql->resultq("SELECT id FROM postlayouts WHERE text='".mysql_real_escape_string($text)."' LIMIT 1", 0, 0);
  if (!$id) {
    $sql->query("INSERT INTO postlayouts (text) VALUES ('".mysql_real_escape_string($text)."')");
    $id = mysql_insert_id();
  }

  return $id;
}

function squot($t, &$src)
{
  switch ($t) {
    case 0: $src = htmlspecialchars($src); break;
    case 1: $src = urlencode($src); break;
    case 2: $src = str_replace('&quot;', '"', $src); break;
    case 3: $src = urldecode('%22', '"', $src); break;
  }
  /*  switch($t){
    case 0: $src=str_replace('"','&#34;',$src); break;
    case 1: $src=str_replace('"','%22',$src); break;
    case 2: $src=str_replace('&#34;','"',$src); break;
    case 3: $src=str_replace('%22','"',$src); break;
    }*/
}
function sbr($t, &$src)
{
  global $br;
  switch ($t) {
    case 0: $src = str_replace($br, '<br>', $src); break;
    case 1: $src = str_replace('<br>', $br, $src); break;
  }
}
function mysql_get($query)
{
  global $sql;

  return $sql->fetchq($query);
}

function admincheck()
{
  global $tblstart, $tccell1, $tblend, $footer, $isadmin;
  if (!$isadmin) {
    $home = base_dir().'/';
    echo "
      $tblstart
        $tccell1>This feature is restricted to administrators.<br>You aren't one, so go away.<br>
    ".redirect("{$home}index.php", 'return to the board', 0)."
    </td>
      $tblend

    $footer
    ";
    die();
  }
}

function adminlinkbar($sel = 'admin.php')
{
  global $tblstart, $tblend, $tccell1, $tccellh, $tccellc, $isadmin;

  if (!$isadmin) {
    return;
  }

  $links = array(
    array(
      "{$GLOBALS['jul_views_path']}/admin.php" => 'Admin Control Panel',
    ),
    array(
//      'admin-todo.php'     => "To-do list",
      "{$GLOBALS['jul_views_path']}/announcement.php" => 'Go to Announcements',
      "{$GLOBALS['jul_views_path']}/admin-editforums.php" => 'Edit Forum List',
      "{$GLOBALS['jul_views_path']}/admin-editemoticons.php" => 'Edit Emoticons',
      "{$GLOBALS['jul_views_path']}/admin-editmods.php" => 'Edit Forum Moderators',
      "{$GLOBALS['jul_views_path']}/ipsearch.php" => 'IP Search',
      "{$GLOBALS['jul_views_path']}/admin-threads.php" => 'ThreadFix',
      "{$GLOBALS['jul_views_path']}/admin-threads2.php" => 'ThreadFix 2',
      "{$GLOBALS['jul_views_path']}/del.php" => 'Delete User',
    ),
  );

  $r = "<div style='padding:0px;margins:0px;'>
    $tblstart<tr>$tccellh><b>Admin Functions</b></td></tr>$tblend";

  foreach ($links as $linkrow) {
    $c = count($linkrow);
    $w = floor(1 / $c * 100);

    $r .= "$tblstart<tr>";

    foreach ($linkrow as $link => $name) {
      $cell = $tccell1;
      if (false !== strpos($link, $sel)) {
        $cell = $tccellc;
      }
      $r .= "$cell width=\"$w%\"><a href=\"$link\">$name</a></td>";
    }

    $r .= "</tr>$tblend";
  }
  $r .= '</div><br>';

  return $r;
}

function include_js($fn, $as_tag = false)
{
  // HANDY JAVASCRIPT INCLUSION FUNCTION
  if ($as_tag) {
    // include as a <script src="..."></script> tag
    return "<script src='$fn' type='text/javascript'></script>";
  } else {
    $f = fopen("../js/$fn", 'r');
    $c = fread($f, filesize($fn));
    fclose($f);

    return '<script type="text/javascript">'.$c.'</script>';
  }
}

function xk_notify($type, $data) {
  // Only continue if Discord notifications are enabled.
  if (!$GLOBALS['jul_settings']['discord_enable_notifications']) {
    return;
  }

  // TODO
}


function xk_ircsend($str)
{
  // Disabled pending removal.
  return;
  // Only continue if IRC notifications are enabled.
  if (!$GLOBALS['jul_settings']['irc_enable_notifications']) {
    return;
  }

  $str = str_replace(array('%10', '%13'), array('', ''), rawurlencode($str));

  $str = html_entity_decode($str);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://treeki.rustedlogic.net:5000/reporting.php?t=$str");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // <---- HERE
  curl_setopt($ch, CURLOPT_TIMEOUT, 5); // <---- HERE
  $file_contents = curl_exec($ch);
  curl_close($ch);

  return true;
}

function xk($n = -1)
{
  if ($n == -1) {
    $k = '';
  } else {
    $k = str_pad($n, 2, 0, STR_PAD_LEFT);
  }

  return "\x03".$k;
}

function formatting_trope($input)
{
  $in = '/[A-Z][^A-Z]/';
  $out = ' \\0';
  $output = preg_replace($in, $out, $input);

  return trim($output);
}

// I'm picky about this sorta thing
function getblankdate()
{
  global $dateformat;

  // We only need to do the replacing one time
  static $bl;
  if ($bl) {
    return $bl;
  }

  $bl = $dateformat;
  $bl = preg_replace('/[jNwzWnLgGI]/', '-', $bl);
  $bl = preg_replace('/[dSmtyaAhHis]/', '--', $bl);
  $bl = preg_replace('/[DFMBe]/', '---', $bl);
  $bl = preg_replace('/[oY]/', '----', $bl);
  $bl = preg_replace('/[lu]/', '------', $bl);
  $bl = preg_replace('/[c]/', '----------T--:--:--+00:00', $bl);
  $bl = preg_replace('/[r]/', '---, -- --- ---- --:--:-- +0000', $bl);

  return $bl;
}

function cleanurl($url)
{
  $pos1 = $pos = strrpos($url, '/');
  $pos2 = $pos = strrpos($url, '\\');
  if (false === $pos1 && false === $pos2) {
    return $url;
  }

  $spos = max($pos1, $pos2);

  return substr($url, $spos + 1);
}

/* extra fun functions! */
function pick_any($array)
{
  if (is_array($array)) {
    return $array[array_rand($array)];
  } elseif (is_string($array)) {
    return $array;
  }
}

function numrange($n, $lo, $hi)
{
  return max(min($hi, $n), $lo);
}

function marqueeshit($str)
{
  return "<marquee scrollamount='".mt_rand(1, 50)."' scrolldelay='".mt_rand(1, 50)."' direction='".pick_any(array('left', 'right'))."'>$str</marquee>";
}

function unescape($in)
{
  $out = urldecode($in);
  while ($out != $in) {
    $in = $out;
    $out = urldecode($in);
  }

  return $out;
}

function adbox()
{
  // no longer needed. RIP
  return '';

  global $loguser, $bgcolor, $linkcolor;

  /*
    $tagline  = array();
    $tagline[]  = "Viewing this ad requires<br>ZSNES 1.42 or older!";
    $tagline[]  = "Celebrating 5 years of<br>ripping off SMAS!";
    $tagline[]  = "Now with 100% more<br>buggy custom sprites!";
    $tagline[]  = "Try using AddMusic to give your hack<br>that 1999 homepage feel!";
    $tagline[]  = "Pipe cutoff? In my SMW hack?<br>It's more likely than you think!";
    $tagline[]  = "Just keep giving us your money!";
    $tagline[]  = "Now with 97% more floating munchers!";
    $tagline[]  = "Tip: If you can beat your level without<br>savestates, it's too easy!";
    $tagline[]  = "Tip: Leave exits to level 0 for<br>easy access to that fun bonus game!";
    $tagline[]  = "Now with 100% more Touhou fads!<br>It's like Jul, but three years behind!";
    $tagline[]  = "Isn't as cool as this<br>witty subtitle!";
    $tagline[]  = "Finally beta!";
    $tagline[]  = "If this is blocking other text<br>try disabling AdBlock next time!";
    $tagline[]  = "bsnes sucks!";
    $tagline[]  = "Now in raspberry, papaya,<br>and roast beef flavors!";
    $tagline[]  = "We &lt;3 terrible Japanese hacks!";
    $tagline[]  = "573 crappy joke hacks and counting!";
    $tagline[]  = "Don't forget your RATS tag!";
    $tagline[]  = "Now with exclusive support for<br>127&frac12;Mbit SuperUltraFastHiDereROM!";
    $tagline[]  = "More SMW sequels than you can<br>shake a dead horse at!";
    $tagline[]  = "xkas v0.06 or bust!";
    $tagline[]  = "SMWC is calling for your blood!";
    $tagline[]  = "You can run,<br>but you can't hide!";
    $tagline[]  = "Now with 157% more CSS3!";
    $tagline[]  = "Stickers and cake don't mix!";
    $tagline[]  = "Better than a 4-star crap cake<br>with garlic topping!";
    $tagline[]  = "We need some IRC COPS!";

    if (isset($_GET['lolol'])) {
      $taglinec = $_GET['lolol'] % count($tagline);
      $taglinec = $tagline[$taglinec];
    }
    else
      $taglinec = pick_any($tagline);
  */

  return "
<center>
<!-- Beginning of Project Wonderful ad code: -->
<!-- Ad box ID: 48901 -->
<script type=\"text/javascript\">
<!--
var pw_d=document;
pw_d.projectwonderful_adbox_id = \"48901\";
pw_d.projectwonderful_adbox_type = \"5\";
pw_d.projectwonderful_foreground_color = \"#$linkcolor\";
pw_d.projectwonderful_background_color = \"#$bgcolor\";
//-->
</script>
<script type=\"text/javascript\" src=\"http://www.projectwonderful.com/ad_display.js\"></script>
<noscript><map name=\"admap48901\" id=\"admap48901\"><area href=\"http://www.projectwonderful.com/out_nojs.php?r=0&amp;c=0&amp;id=48901&amp;type=5\" shape=\"rect\" coords=\"0,0,728,90\" title=\"\" alt=\"\" target=\"_blank\" /></map>
<table cellpadding=\"0\" border=\"0\" cellspacing=\"0\" width=\"728\" bgcolor=\"#$bgcolor\"><tr><td><img src=\"http://www.projectwonderful.com/nojs.php?id=48901&amp;type=5\" width=\"728\" height=\"90\" usemap=\"#admap48901\" border=\"0\" alt=\"\" /></td></tr><tr><td bgcolor=\"\" colspan=\"1\"><center><a style=\"font-size:10px;color:#$linkcolor;text-decoration:none;line-height:1.2;font-weight:bold;font-family:Tahoma, verdana,arial,helvetica,sans-serif;text-transform: none;letter-spacing:normal;text-shadow:none;white-space:normal;word-spacing:normal;\" href=\"http://www.projectwonderful.com/advertisehere.php?id=48901&amp;type=5\" target=\"_blank\">Ads by Project Wonderful! Your ad could be right here, right now.</a></center></td></tr></table>
</noscript>
<!-- End of Project Wonderful ad code. -->
</center>";
}

// for you-know-who's bullshit
function gethttpheaders()
{
  $ret = '';
  foreach ($_SERVER as $name => $value) {
    if ('HTTP_' == substr($name, 0, 5)) {
      $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
      if ('User-Agent' == $name || 'Cookie' == $name || 'Referer' == $name || 'Connection' == $name) {
        continue;
      } // we track the first three already, the last will always be "close"

      $ret .= "$name: $value\r\n";
    }
  }

  return $ret;
}

function printtimedif($timestart)
{
  global $x_hacks, $sql, $sqldebuggers, $smallfont, $route;

  $exectime = microtime(true) - $timestart;

  $qseconds = sprintf('%01.6f', mysql::$time);
  $sseconds = sprintf('%01.6f', $exectime - mysql::$time);
  $tseconds = sprintf('%01.6f', $exectime);

  $queries = mysql::$queries;
  $cache = mysql::$cachehits;

  // Old text
  //print "<br>{$smallfont} Page rendered in {$tseconds} seconds.</font><br>";

  echo "<br>
    {$smallfont}{$queries} database queries".(($cache > 0) ? ", {$cache} query cache hits" : '').".</font>
    <table cellpadding=0 border=0 cellspacing=0 class='fonts'>
      <tr><td align=right>Query execution time:&nbsp;</td><td>{$qseconds} seconds</td></tr>
      <tr><td align=right>Script execution time:&nbsp;</td><td>{$sseconds} seconds</td></tr>
      <tr><td align=right>Total render time:&nbsp;</td><td>{$tseconds} seconds</td></tr>
    </table>";

  // Save the render time to the database.
  $pages = array('index', 'thread', 'forum');
  $page = $route['file'];
  if (in_array($page, $pages)) {
    $sql->query("INSERT INTO `rendertimes` SET `page` = '".mysql_real_escape_string($page)."', `time` = '".ctime()."', `rendertime`  = '".$exectime."'");
    // Delete render times from the last 14 days.
    $sql->query("DELETE FROM `rendertimes` WHERE `time` < '".(ctime() - 86400 * 14)."'");
  }
}

function ircerrors($type, $msg, $file, $line, $context)
{
  global $loguser;

  // They want us to shut up? (@ error control operator) Shut the fuck up then!
  if (!error_reporting()) {
    return true;
  }

  switch ($type) {
    case E_USER_ERROR:    $typetext = xk(4).'- Error'; break;
    case E_USER_WARNING:  $typetext = xk(7).'- Warning'; break;
    case E_USER_NOTICE:   $typetext = xk(8).'- Notice'; break;
    default: return false;
  }

  // Get the ACTUAL location of error for mysql queries
  if (E_USER_ERROR == $type && 'mysql.php' === substr($file, -9)) {
    $backtrace = debug_backtrace();
    for ($i = 1; isset($backtrace[$i]); ++$i) {
      if ('mysql.php' !== substr($backtrace[$i]['file'], -9)) {
        $file = $backtrace[$i]['file'];
        $line = $backtrace[$i]['line'];
        break;
      }
    }
  }
  // Get the location of error for deprecation
  elseif (E_USER_NOTICE == $type && 'Deprecated' === substr($msg, 0, 10)) {
    $backtrace = debug_backtrace();
    $file = $backtrace[2]['file'];
    $line = $backtrace[2]['line'];
  }

  $errorlocation = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file)." #$line";

  xk_ircsend('102|'.($loguser['id'] ? xk(11).$loguser['name'].' ('.xk(10).$_SERVER['REMOTE_ADDR'].xk(11).')' : xk(10).$_SERVER['REMOTE_ADDR']).
         " $typetext: ".xk()."($errorlocation) $msg");

  return true;
}

/**
 * Cleanup functions that were only in the index.php file.
 * Thus this is only run from the views/index.php file right now.
 */
function run_cleanup_actions()
{
  if ('markforumread' == filter_string($_GET['action']) and $log) {
    $sql->query("DELETE FROM forumread WHERE user='".$loguser['id']."' AND forum='$forumid'");
    $sql->query("DELETE FROM `threadsread` WHERE `uid` = '".$loguser['id']."' AND `tid` IN (SELECT `id` FROM `threads` WHERE `forum` = '$forumid')");
    $ct = ctime();
    $sql->query("INSERT INTO forumread (user,forum,readdate) VALUES (".$loguser['id'].",$forumid,{$ct})");

    return header('Location: index.php');
  }

  if ('markallforumsread' == filter_string($_GET['action']) and $log) {
    $sql->query("DELETE FROM forumread WHERE user='".$loguser['id']."'");
    $sql->query("DELETE FROM `threadsread` WHERE `uid` = '".$loguser['id']."'");
    $sql->query("INSERT INTO forumread (user,forum,readdate) SELECT '".$loguser['id']."',`id`,".ctime().' FROM forums');

    return header('Location: index.php');
  }
}
