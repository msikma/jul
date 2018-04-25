<?php

require_once 'themes/default/settings.php';

// Check for scheme settings. Initial value of $scheme comes from function.php.
$scheme = filter_int($scheme);
if (isset($_GET['scheme']) && is_numeric($_GET['scheme'])) {
    $scheme = intval($_GET['scheme']);
} elseif (isset($_GET['scheme'])) {
    $scheme = 0;
}

// Load theme settings.
$schemerow = $sql->fetchq("SELECT `name`, `file` FROM schemes WHERE id='$scheme'");
$theme = $schemerow ? $schemerow['file'] : 'night';

// Include the chosen theme settings, which sets up its variables/colors.
include "themes/$theme/settings.php";

// Special theme that activates when a user is viewing a specific forum.
if ($specialscheme) {
  include "themes/$specialscheme/settings.php";
}

$bgimage = isset($bgimage) && $bgimage ? " url('$bgimage')" : '';
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

// Now output the CSS.
header('Content-type: text/css');

// If the current theme wishes only to include its own CSS, skip the standard rules.
if (isset($schemetype) && $schemetype === 1) {
  $content = file_get_contents($GLOBALS['jul_base_dir']."/themes/$theme/style.css");
  print($content);
  exit;
}

// 10/18/08 - hydrapheetz: added a small hack for "extra" css goodies.
if (!isset($nullscheme) && !isset($schemetype) && isset($css_extra)) {
  print($css_extra);
}

print("
a { color: #$linkcolor; }
a:visited { color: #$linkcolor2; }
a:active { color: #$linkcolor3; }
a:hover { color: #$linkcolor4; }
body {
  scrollbar-face-color: #$scr3;
  scrollbar-track-color: #$scr7;
  scrollbar-arrow-color: #$scr6;
  scrollbar-highlight-color: #$scr2;
  scrollbar-3dlight-color: #$scr1;
  scrollbar-shadow-color: #$scr4;
  scrollbar-darkshadow-color: #$scr5;
  color: #$textcolor;
  font:13px $font;
  background: #$bgcolor$bgimage;
}
div.lastpost { font: 10px $font2 !important; white-space: nowrap; }
div.lastpost:first-line { font: 13px $font !important; }
.sparkline { display: none; }
.brightlinks a { color: #$brightlinkcolor; font-weight: normal; }
.brightlinks a:hover { font-weight: normal; }
.font {font:13px $font}
.fonth {font:13px $font;color:$tableheadtext}
.fonts {font:10px $font2}
.fontt {font:10px $font3}
.tdbg1 {background:#$tablebg1}
.tdbg2 {background:#$tablebg2}
.tdbgc {background:#$categorybg}
.tdbgh {background:#$tableheadbg; color:$tableheadtext}
.table {
empty-cells: show; width: $tablewidth;
border-top: #$tableborder 1px solid;
border-left: #$tableborder 1px solid;
}
td.tbl {
border-right: #$tableborder 1px solid;
border-bottom: #$tableborder 1px solid;
}
");

$numcols = (filter_int($numcols) ? $numcols : 60);

if ($formcss) {
    $numcols = 80;
    if (!isset($formtextcolor)) {
        $formtextcolor = $textcolor;
    }
    $css .= "
textarea,input,select{
  border:	#$inputborder solid 1px;
  background:#000000;
  color:	#$formtextcolor;
  font:	10pt $font;}
textarea:focus {
  border:	#$inputborder solid 1px;
  background:#000000;
  color:	#$formtextcolor;
  font:	10pt $font;}
.radio{
  border:	none;
  background:none;
  color:	#$formtextcolor;
  font:	10pt $font;}
.submit{
  border:	#$inputborder solid 2px;
  font:	10pt $font;}
";
}
