<?php

function post_content_to_html($post) {
  $settings = post_lang_settings();

  $post = jul_postfilter_xss($post);
  $post = jul_postfilter_youtube($post);

  if ($settings['lang'] === 'bbcode') {
    $post = jul_postfilter_bbcode($post);
  }
  // TODO: add Markdown.
  return $post;
}

/** Hack to support the old postcode() function. */
function post_table_convert($pc_arr) {
  return $pc_arr[0].post_content_to_html($pc_arr[1]).$pc_arr[2];
}

function post_lang_settings() {
  return array('lang' => 'bbcode');
}
