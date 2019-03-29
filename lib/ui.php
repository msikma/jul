<?php

/**
 * Displays the current Jul version in the footer.
 * Very legacy!
 */
function version_footer()
{
  // Take the first part of the name ('dada/jul') to put in front of the version.
  // So people can see it's not stock Jul.
  $name = explode('/', $GLOBALS['jul_version']['composer']['name']);
  $commit = $GLOBALS['jul_version']['commit']['hash']
      ? " - <a href='{$GLOBALS['jul_version']['commit']['url']}'>{$GLOBALS['jul_version']['commit']['string']}</a>"
      : '';

  return "
    <font class=\"fonts\">
      Jul v{$GLOBALS['jul_version']['version']}-{$name[0]} r{$GLOBALS['jul_version']['commit']['rev']} {$commit}
      <br>&copy;{$GLOBALS['jul_version']['copyright_start']}-{$GLOBALS['jul_version']['copyright_end']} {$GLOBALS['jul_version']['authors']}
    </font>
  ";
}

/**
 * Renders a simple table based on an array.
 */
function render_form_table($content) {
  $html = "
    <table class='table form-table table-margin' cellspacing='0'>
      <tbody>
  ";
  for ($a = 0; $a < count($content); ++$a) {
    $val = $content[$a];
    if ($val[0] === '---') {
      // Separator
      $html .= "
        <tr>
          <td class='tbl tdbgh font center'>&nbsp;</td>
          <td class='tbl tdbgh font center'>&nbsp;</td>
        </tr>
      ";
    }
    else {
      $label = $val[0];
      $val = $val[1];
      $html .= "
        <tr>
          <td class='tbl tdbg1 font center label'><b>{$label}</b></td>
          <td class='tbl tdbg2 font'>{$val}</td>
        </tr>
      ";
    }
  }
  $html .= "
      </tbody>
    </table>
  ";
  print($html);
}

/**
 * Renders a box with title and content.
 */
function render_box($content, $title='Notice') {
  $html = '
    <table class="table brightlinks table-margin" cellspacing="0">
      <tbody>
      '.($title ? ('
        <tr>
          <td class="tbl tdbgh font center">'.$title.'</td>
        </tr>
      ') : ('')).'
        <tr>
          <td class="tbl tdbg1 font center">'.$content.'</td>
        </tr>
      </tbody>
    </table>';
  print($html);
}
