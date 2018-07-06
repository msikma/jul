<?php

// TODO: this is meant for reporting potential problems to e.g. Discord.
// This should be fixed at some point.
function report_notice($str) {
  // Just go to the error log for now.
  error_log('jul-report-notice: '.$str);
}
