<?php

require_once 'lib/actions/colors.php';

//
if ($specialscheme) {
  include "schemes/spec-$specialscheme.php";
}

if (isset($bgimage) && '' != $bgimage) {
    $bgimage = " url('$bgimage')";
} else {
    $bgimage = '';
}
