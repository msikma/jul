<?php

$composer = $GLOBALS['jul_base_path'].'/composer.json';
$composer = json_decode(file_get_contents($composer), true);

// Returns a formatted commit number from Git.
function get_commit() {
  return trim(shell_exec("git log --format='commit %h [%ad]' --date='short' -n 1"));
}

$GLOBALS['jul_version'] = array(
  'copyright_start' => '2000',
  'copyright_end' => date('Y'),
  'authors' => 'Acmlm, Xkeeper, Inuyasha, Dada, et al.',
  'commit' => get_commit(),
  'version' => $composer['version'],
  'name' => $composer['name'],
  'license' => $composer['license'],
  'repository' => $composer['repository'],
);
