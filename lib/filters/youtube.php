<?php

/**
 * Transforms 'youtube.com/asdf' into embedded videos.
 */
function jul_postfilter_youtube($post) {
  $post = preg_replace("'\[youtube\]([a-zA-Z0-9_-]{11})\[/youtube\]'si", '<iframe src="https://www.youtube.com/embed/\1" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"></iframe>', $post);

  return $post;
}
$defaults = array();

$GLOBALS['jul_postfilters'][] = array(
  'function' => 'jul_postfilter_youtube',
  'defaults' => $defaults
);
