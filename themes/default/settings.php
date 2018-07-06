<?php

// Default theme style settings.

// Some default name colors.
$nmcol[0] = array('-2' => '6a6a6a', '-1' => '888888', '97ACEF', 'D8E8FE', 'AFFABE', 'FFEA95');
$nmcol[1] = array('-2' => '767676', '-1' => '888888', 'F185C9', 'FFB3F3', 'C762F2', 'C53A9E');
$nmcol[2] = array('-2' => '767676', '-1' => '888888', '7C60B0', 'EEB9BA', '47B53C', 'F0C413');

// Which table layout to use by default.
$tlayout = 1;
$numcols = 100;

$linkcolor = 'FFD040';
$linkcolor2 = 'F0A020';
$linkcolor3 = 'FFEA00';
$linkcolor4 = 'FFFFFF';
$textcolor = 'E0E0E0';

$font = 'Verdana, sans-serif';
$font2 = 'Verdana, sans-serif';
$font3 = 'Tahoma, sans-serif';

// Images used in the theme.
$newpollpic = "<img class='ui-icon' src='{$theme_base}images/newpoll.png' alt='New poll' align='absmiddle'>";
$newreplypic = "<img class='ui-icon' src='{$theme_base}images/newreply.png' alt='New reply' align='absmiddle'>";
$newthreadpic = "<img class='ui-icon' src='{$theme_base}images/newthread.png' alt='New thread' align='absmiddle'>";
$closedpic = "<img class='ui-icon' src='{$theme_base}images/threadclosed.png' alt='Thread closed' align='absmiddle'>";
$numdir = "jul/";

$statusicons['new'] = "<img class='ui-icon' src={$theme_base}images/new.gif>";
$statusicons['newhot'] = "<img class='ui-icon' src={$theme_base}images/hotnew.gif>";
$statusicons['newoff'] = "<img class='ui-icon' src={$theme_base}images/off.gif>";
$statusicons['newhotoff'] = "<img class='ui-icon' src={$theme_base}images/hotoff.gif>";
$statusicons['hot'] = "<img class='ui-icon' src={theme_base}images/hot.gif>";
$statusicons['hotoff'] = "<img class='ui-icon' src={$theme_base}images/hotoff.gif>";
$statusicons['off'] = "<img class='ui-icon' src={theme_base}images/off.gif>";

$statusicons['getnew'] = '<img class="ui-icon" src='.$theme_base.'images/getnew.png title="Go to new posts" align="absmiddle">';
$statusicons['getlast'] = '<img class="ui-icon" src='.$theme_base.'images/getlast.png title="Go to last post" style="position:relative;top:1px;">';

$statusicons['sticky'] = 'Sticky:';
$statusicons['poll'] = 'Poll:';
$statusicons['stickypoll'] = 'Sticky poll:';

$ui_icons['no_more_polls'] = '<img src="images/nopolls.png" align="absmiddle" />';

$ui_images['minibar'] = $theme_base.'images/minibar.png';
$ui_images['coinyellow'] = $theme_base.'images/coinyellow.png';
$ui_images['coingreen'] = $theme_base.'images/coingreen.png';

$favicons = array(
  $theme_base.'images/favicons/favicon1.ico',
  $theme_base.'images/favicons/favicon2.ico',
  $theme_base.'images/favicons/favicon3.ico',
  $theme_base.'images/favicons/favicon4.ico',
  $theme_base.'images/favicons/favicon5.ico',
  $theme_base.'images/favicons/favicon6.ico',
  $theme_base.'images/favicons/favicon7.ico',
  $theme_base.'images/favicons/favicon8.ico',
);
$ikachan_source = array(
  array($theme_base.'images/ikachan/ikanormal.png', 98),
  array($theme_base.'images/ikachan/ikamad.png', 1),
  array($theme_base.'images/ikachan/ikaviking.png', 1),
);
