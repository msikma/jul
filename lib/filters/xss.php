<?php

$anti_xss = new voku\helper\AntiXSS();

/**
 * Runs an XSS cleaning filter over the post.
 */
function jul_postfilter_xss($post) {
  global $anti_xss;
  return $anti_xss->xss_clean($post);
}
$defaults = array();

$GLOBALS['jul_postfilters'][] = array(
  'function' => 'jul_postfilter_xss',
  'defaults' => $defaults
);
