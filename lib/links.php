<?php

function forum_link($id, $title) {
  global $sql, $power;
  $forumquery = $sql->query("SELECT f.id,f.title FROM forums f WHERE (!minpower OR minpower<=$power) AND f.hidden = '0';");
  $slug = slugify($title);
  return route('@forum', array('id' => $id, 'slug' => $slug));
}
