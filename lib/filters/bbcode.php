<?php

/**
 * Converts BBcode to HTML.
 * This used to be called doreplace2().
 */
function jul_postfilter_bbcode($msg, $options='0|0') {
  // options will contain smiliesoff|htmloff
  $options = explode('|', $options);
  $smiliesoff = $options[0];
  $htmloff = $options[1];

  $list = array('<', '\\"', '\\\\', "\\'", '[', ':', ')', '_');
  $list2 = array('&lt;', '"', '\\', "\'", '&#91;', '&#58;', '&#41;', '&#95;');
  $msg = preg_replace_callback("'\[code\](.*?)\[/code\]'si", 'escape_codeblock', $msg);

  if ($htmloff) {
      $msg = str_replace('<', '&lt;', $msg);
      $msg = str_replace('>', '&gt;', $msg);
  }

  if (!$smiliesoff) {
      global $smilies;
      if (!$smilies) {
          $smilies = readsmilies();
      }
      for ($s = 0; $smilies[$s][0]; ++$s) {
          $smilie = $smilies[$s];
          $msg = str_replace($smilie[0], "<img src=$smilie[1] align=absmiddle>", $msg);
      }
  }

  $msg = str_replace('[red]', '<font color=FFC0C0>', $msg);
  $msg = str_replace('[green]', '<font color=C0FFC0>', $msg);
  $msg = str_replace('[blue]', '<font color=C0C0FF>', $msg);
  $msg = str_replace('[orange]', '<font color=FFC080>', $msg);
  $msg = str_replace('[yellow]', '<font color=FFEE20>', $msg);
  $msg = str_replace('[pink]', '<font color=FFC0FF>', $msg);
  $msg = str_replace('[white]', '<font color=white>', $msg);
  $msg = str_replace('[black]', '<font color=0>', $msg);
  $msg = str_replace('[/color]', '</font>', $msg);
  $msg = preg_replace("'\[quote=(.*?)\]'si", '<blockquote><font class=fonts><i>Originally posted by \\1</i></font><hr>', $msg);
  $msg = str_replace('[quote]', '<blockquote><hr>', $msg);
  $msg = str_replace('[/quote]', '<hr></blockquote>', $msg);
  $msg = preg_replace("'\[sp=(.*?)\](.*?)\[/sp\]'si", '<span style="border-bottom: 1px dotted #f00;" title="did you mean: \\1">\\2</span>', $msg);
  $msg = preg_replace("'\[abbr=(.*?)\](.*?)\[/abbr\]'si", '<span style="border-bottom: 1px dotted;" title="\\1">\\2</span>', $msg);
  $msg = str_replace('[spoiler]', '<div class="fonts pstspl2"><b>Spoiler:</b><div class="pstspl1">', $msg);
  $msg = str_replace('[/spoiler]', '</div></div>', $msg);
  $msg = preg_replace("'\[(b|i|u|s)\]'si", '<\\1>', $msg);
  $msg = preg_replace("'\[/(b|i|u|s)\]'si", '</\\1>', $msg);
  $msg = preg_replace("'\[img\](.*?)\[/img\]'si", '<img src=\\1>', $msg);
  $msg = preg_replace("'\[url\](.*?)\[/url\]'si", '<a href=\\1>\\1</a>', $msg);
  $msg = preg_replace("'\[url=(.*?)\](.*?)\[/url\]'si", '<a href=\\1>\\2</a>', $msg);
  $msg = str_replace('http://nightkev.110mb.com/justus_layout.css', 'about:blank', $msg);

  do {
      $msg = preg_replace("/<(\/?)t(able|h|r|d)(.*?)>(\s+?)<(\/?)t(able|h|r|d)(.*?)>/si",
              '<\\1t\\2\\3><\\5t\\6\\7>', $msg, -1, $replaced);
  } while ($replaced >= 1);

  // TODO: shouldn't we just do this here?
  sbr(0, $msg);

  return $msg;
}
$defaults = array();

$GLOBALS['jul_postfilters'][] = array(
  'function' => 'jul_postfilter_bbcode',
  'defaults' => $defaults
);
