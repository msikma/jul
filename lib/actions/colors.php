<?php

$pwlnames = array('-2' => 'Permabanned', '-1' => 'Banned', 'Normal', 'Normal +', 'Moderator', 'Administrator', 'Sysadmin');
$nmcol[0] = array('-2' => '6a6a6a', '-1' => '888888', '97ACEF', 'D8E8FE', 'AFFABE', 'FFEA95');
$nmcol[1] = array('-2' => '767676', '-1' => '888888', 'F185C9', 'FFB3F3', 'C762F2', 'C53A9E');
$nmcol[2] = array('-2' => '767676', '-1' => '888888', '7C60B0', 'EEB9BA', '47B53C', 'F0C413');

$linkcolor = 'FFD040';
$linkcolor2 = 'F0A020';
$linkcolor3 = 'FFEA00';
$linkcolor4 = 'FFFFFF';
$textcolor = 'E0E0E0';

$font = 'Verdana, sans-serif';
$font2 = 'Verdana, sans-serif';
$font3 = 'Tahoma, sans-serif';

$newpollpic = '<img class="ui-icon" src="'.$GLOBALS['jul_base_dir'].'/images/newpoll.png" alt="New poll" align="absmiddle">';
$newreplypic = '<img class="ui-icon" src="'.$GLOBALS['jul_base_dir'].'/images/newreply.png" alt="New reply" align="absmiddle">';
$newthreadpic = '<img class="ui-icon" src="'.$GLOBALS['jul_base_dir'].'/images/newthread.png" alt="New thread" align="absmiddle">';
$closedpic = '<img class="ui-icon" src="'.$GLOBALS['jul_base_dir'].'/images/threadclosed.png" alt="Thread closed" align="absmiddle">';
$numdir = 'jul/';

$statusicons['new'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/new.gif>';
$statusicons['newhot'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/hotnew.gif>';
$statusicons['newoff'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/off.gif>';
$statusicons['newhotoff'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/hotoff.gif>';
$statusicons['hot'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/hot.gif>';
$statusicons['hotoff'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/hotoff.gif>';
$statusicons['off'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/off.gif>';

$statusicons['getnew'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/getnew.png title="Go to new posts" align="absmiddle">';
$statusicons['getlast'] = '<img class="ui-icon" src='.$GLOBALS['jul_base_dir'].'/images/getlast.png title="Go to last post" style="position:relative;top:1px;">';

$statusicons['sticky'] = 'Sticky:';
$statusicons['poll'] = 'Poll:';
$statusicons['stickypoll'] = 'Sticky poll:';

$schemetime = -1; // mktime(9, 0, 0) - time();

// $numfil='numnes';
$schemepre = false;

$scheme = filter_int($scheme);
if (isset($_GET['scheme']) && is_numeric($_GET['scheme'])) {
    $scheme = intval($_GET['scheme']);
    $schemepre = true;
} elseif (isset($_GET['scheme'])) {
    $scheme = 0;
}

// Force Xmas scheme (cue whining, as always)
if (false && !($log && 2100 == $loguserid)) { // ... just ... not now please.
    if (!$x_hacks['host']) {
        $scheme = 3;
    }
    $x_hacks['rainbownames'] = true;
}

$schemerow = $sql->fetchq("SELECT `name`, `file` FROM schemes WHERE id='$scheme'");

$filename = '';
if ($schemerow) {
    $filename = $schemerow['file'];
} else {
    $filename = 'night.php';
    $schemepre = false;
}

require_once "schemes/$filename";

if ($schemepre) {
    $GLOBALS['jul_settings']['board_title'] .= "</a><br><span class='font'>Previewing scheme \"<b>".$schemerow['name'].'</b>"</span>';
}

// hack for compat
if (!$inputborder) {
    $inputborder = $tableborder;
}

$newpic = $statusicons['new'];	// hack for compat

if ($loguser['powerlevel'] < 3) {
    $nmcol[0][1] = $nmcol[0][0];
    $nmcol[1][1] = $nmcol[1][0];
    $nmcol[2][1] = $nmcol[2][0];
}
