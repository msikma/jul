<?php

/**
 * Returns the first post ID in a thread after a specific timestamp.
 */
function get_thread_post_id_after_ts($thread_id, $unix_timestamp) {
  $esc_thread_id = intval($thread_id);
  $esc_unix_timestamp = intval($unix_timestamp);
  $result = fetch_query_single("
    select min(`id`) as id
    from `posts`
    where `thread` = {$esc_thread_id}
    and `date` >= {$esc_unix_timestamp}
  ");
  return $result['id'];
}

/**
 * Returns a direct link to a post.
 */
function get_post_direct_link($thread_id, $post_id) {
  $thread = get_thread($thread_id);
  $url = route('@thread_slug_post', array('id' => $thread_id, 'slug' => slugify($thread['title']), 'pid' => $post_id));
  return $url.'#post'.$post_id;
}

/**
 * Returns the last post ID of a thread.
 */
function get_thread_last_post_id($thread_id) {
  $esc_thread_id = intval($thread_id);
  $result = fetch_query_single("
    select max(`id`) as id
    from `posts`
    where `thread` = {$esc_thread_id}
  ");
  return $result['id'];
}

/**
 * Returns a thread by ID.
 */
function get_thread($thread_id) {
  $esc_thread_id = intval($thread_id);
  $result = fetch_query_single("
    select *
    from `threads`
    where `id` = {$esc_thread_id}
  ");
  return $result;
}

function make_new_thread($forum, $user, $subject, $posticon) {
  $esc_forum_id = intval($forum['id']);
  $esc_user_id = intval($user['id']);
  $esc_subject = mysql_real_escape_string($subject);
  $esc_posticon = mysql_real_escape_string($posticon);
  $new_id = insert_query("
    insert into `threads` (
      `forum`, `user`, `views`, `closed`,
      `title`, `icon`, `replies`, `firstpostdate`,
      `lastpostdate`, `lastposter`
    ) values (
      $esc_forum_id, $esc_user_id, 0, 0,
      '$esc_subject', '$esc_posticon', 0, unix_timestamp(),
      unix_timestamp(), $esc_user_id
    );
  ");
  return $new_id;
}

function make_new_post($thread_id, $forum_id, $user, $message) {
  $esc_thread_id = intval($thread_id);
  $esc_user_id = intval($user['id']);
  $esc_ip = mysql_real_escape_string(get_current_ip());
  $esc_post_num = intval($user['posts']) + 1;
  $esc_header_id = 0;//$user['signature']
  $esc_signature_id = 0;//$user['signature']
  $post_id = insert_query("
    insert into `posts` (
      `thread`, `user`, `date`, `ip`, `num`, `headid`, `signid`
    ) values (
      {$esc_thread_id}, {$esc_user_id}, unix_timestamp(), '{$esc_ip}', {$esc_post_num}, {$esc_header_id}, {$esc_signature_id}
    );
  ");
  if (!$post_id) return false;

  $esc_message = mysql_real_escape_string($message);
  insert_query("
    insert into `posts_text` (
      `pid`, `text`, `tagval`, `options`
    ) values (
      {$post_id}, '{$esc_message}', '', ''
    );
  ");
  // Update the forum to increase the post count and last poster.
  _update_forum_after_post($esc_user_id, $post_id, $forum_id);
}

function _update_thread_after_poll($thread_id, $poll_id) {
  $esc_thread_id = intval($thread_id);
  $esc_poll_id = intval($poll_id);
  run_query("update `threads` set `poll` = {$esc_poll_id} where `id` = {$esc_thread_id};");
}

function make_new_poll($question, $briefing, $multiple_vote, $choices, $colors) {
  $esc_question = mysql_real_escape_string($question);
  $esc_briefing = mysql_real_escape_string($briefing);
  $esc_multiple_vote = intval($multiple_vote);
  $poll_id = insert_query("
    insert into `poll` (
      `question`, `briefing`, `closed`, `doublevote`
    ) values (
      '{$esc_question}', '{$esc_briefing}', 0, {$esc_multiple_vote}
    );
  ");
  if (!$poll_id) return false;

  $esc_poll_id = intval($poll_id);
  $c = 1;
  while ($chtext[$c]) {
    $esc_choice = mysql_real_escape_string($chtext[$c]);
    $esc_color = mysql_real_escape_string($chcolor[$c]);
    insert_query("
      insert into `poll_choices` (
        `poll`, `choice`, `color`
      ) values (
        {$esc_poll_id}, '{$esc_choice}', '{$esc_color}'
      );
    ");
    $c++;
  }
  return $poll_id;
}
