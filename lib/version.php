<?php

function get_composer() {
  $composer = $GLOBALS['jul_base_path'].'/composer.json';
  $composer = json_decode(file_get_contents($composer), true);
  return $composer;
}

// Returns a formatted commit number from Git.
function get_commit($composer) {
  $data = explode('$$', shell_exec("git log --format='%h$$%ad$$%H' --date='short' -n 1"));
  $rev = trim(shell_exec("git rev-list HEAD --count"));
  // If Git is not available somehow, $data will probably be [''].
  if (!$data || $data[0] === '') $data = null;
  return array(
    'hash' => $data[0],
    'full_hash' => $data[2],
    'last_commit' => $data[1],
    'rev' => $rev,
    'url' => $composer['repository'].'/commit/'.$data[2],
    'string' => 'commit '.$data[0].' ['.$data[1].']'
  );
}

$composer = get_composer();
$GLOBALS['jul_version'] = array(
  'copyright_start' => '2000',
  'copyright_end' => date('Y'),
  'authors' => 'Acmlm, Xkeeper, Inuyasha, Dada, et al.',
  'commit' => get_commit($composer),
  'composer' => $composer,
  'version' => $composer['version'],
  'name' => $composer['name'],
  'license' => $composer['license'],
  'repository' => $composer['repository'],
);
