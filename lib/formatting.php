<?php

use Coduo\PHPHumanizer\DateTimeHumanizer;

date_default_timezone_set('UTC');

// Disables all relative dates for testing.
$_debug_no_rel_dates = false;

/**
 * Formats a timestamp and returns a string value.
 * 
 * By default, this will usually return both a relative value and an absolute value.
 * If the timestamp happened very shortly ago, only a relative value is shown.
 * If it happened long ago, only an absolute value is shown.
 */
function format_timestamp_html($timestamp, $include_rel = true, $include_abs = true, $include_prefix = false) {
  global $_debug_no_rel_dates;

  $esc_timestamp = intval($timestamp);
  $ts_obj = format_timestamp_obj($timestamp);

  $cls = implode(' ', array('formatted-timestamp', $ts_obj['abs_shown'] ? 'type-absolute' : 'type-relative'));
  $content = '';
  $attr = '';
  $add_prefix = null;

  if ($ts_obj['abs_only'] || !$include_rel || $_debug_no_rel_dates) {
    $content = $ts_obj['abs'];
    $add_prefix = true;
  }
  else if ($ts_obj['abs_shown']) {
    $content = $ts_obj['abs'].($include_rel ? " ({$ts_obj['rel']})" : '');
    $add_prefix = true;
  }
  else {
    $content = $ts_obj['rel'];
    $attr = " title='{$ts_obj['abs']}'";
    $add_prefix = false;
  }

  return (($include_prefix && $add_prefix) ? 'on ' : '')."<span class='{$cls}' data-ts='{$esc_timestamp}' {$attr}>{$content}</span>";
}

/**
 * Formats a timestamp using a standardized formulate.
 * 
 * Includes an absolute time and a relative time by default.
 */
function format_timestamp_obj($timestamp) {
  $timestamp = intval($timestamp);

  // Calculate the absolute timestamp.
  $abs_ts = _format_timestamp_abs($timestamp);

  // Whether we only see an absolute time and no relative time at all.
  $abs_only = null;
  // Whether the absolute time should be displayed as the primary time.
  $abs_shown = null;

  // If our date is within two days, we would like to specifically display
  // the amount of hours ago it was; rather than the regular time_ago function.
  // This is to avoid situations like "Yesterday (1 day ago)".
  // The standard relative time function is called if it's within one hour from now.
  $in_last_hour = in_last_hour($timestamp);
  $is_today = is_today($timestamp);
  $is_yday = is_yesterday($timestamp);
  $in_last_two_weeks = in_last_two_weeks($timestamp);
  $in_last_year = in_last_year($timestamp);

  $abs_shown = !($in_last_hour || $is_today || $is_yday || $in_last_two_weeks);
  $abs_only = !$in_last_year;

  // Calculate the relative timestamp.
  $rel_ts = _format_timestamp_rel($timestamp);

  return array(
    'abs' => $abs_ts,
    'rel' => $rel_ts,
    'abs_only' => $abs_only,
    'abs_shown' => $abs_shown,
  );
}

/**
 * Returns whether this timestamp is within the last hour.
 */
function in_last_hour($timestamp) {
  return is_within_seconds($timestamp, 3600);
}

/**
 * Returns whether this timestamp is within the last two weeks.
 */
function in_last_two_weeks($timestamp) {
  return is_within_seconds($timestamp, 604800 * 2);
}

/**
 * Returns whether this timestamp is within the last year.
 */
function in_last_year($timestamp) {
  return is_within_seconds($timestamp, 31556952);
}

/**
 * Returns whether this timestamp is from today.
 */
function is_today($timestamp) {
  return date('Y-m-d') === date('Y-m-d', $timestamp);
}

/**
 * Returns whether this timestamp is from yesterday.
 */
function is_yesterday($timestamp) {
  return date('Y-m-d', strtotime('yesterday')) === date('Y-m-d', $timestamp);
}

/**
 * Returns whether a timestamp is within a specific limit in seconds.
 */
function is_within_seconds($timestamp, $limit, $cmp_timestamp = null) {
  if (is_null($cmp_timestamp)) {
    $cmp_timestamp = time();
  }
  $cmp_timestamp = intval($cmp_timestamp);
  $timestamp = intval($timestamp);
  $limit = intval($limit);
  return $cmp_timestamp - $timestamp < $limit;
}

/**
 * Returns an absolute timestamp, e.g. ''.
 */
function _format_timestamp_abs($timestamp) {
  return strftime('%c %z', $timestamp);
}

/**
 * Returns a relative timestamp, e.g. '1 day ago', '15 minutes ago'.
 */
function _format_timestamp_rel($timestamp) {
  $now = new DateTime();
  $now->setTimestamp(time());
  $then = new DateTime();
  $then->setTimestamp($timestamp);

  return DateTimeHumanizer::difference($now, $then);
}
