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
    <table class="table brightlinks table-margin table-box" cellspacing="0">
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

/**
 * Renders a table based on an assoc. Returns the value.
 */
function get_data_table($data, $items = array()) {
  $html = "<table class='table data-table'>";
  foreach ($data as $k => $v) {
    if ((!empty($items) && in_array($k, $items)) || empty($items)) {
      $html .= "
        <tr>
          <th>$k</th>
          <td>$v</td>
        </tr>";
    }
  }
  $html .= '</table>';
  return $html;
}

function get_login_form() {
  global $tccellh, $tccell1, $tccell2l, $inpp, $inpt, $inph, $inps, $verifyoptext;
  $fstart = "<FORM ACTION='{$GLOBALS['jul_base_dir']}/login' NAME=replier METHOD=POST><tr>";
  $fend = "</FORM>";
  if ($no_form) {
    $fstart = '';
    $fend = '';
  }
  return "
    $fstart
		$tccellh width=150>&nbsp;</td>$tccellh width=40%>&nbsp</td>$tccellh width=150>&nbsp;</td>$tccellh width=40%>&nbsp;</td></tr><tr>
		$tccell1><b>Username:</b></td>       $tccell2l>$inpt=username tabindex='10' MAXLENGTH=25 style='width:280px;'></td>
		$tccell1 rowspan=2><b>IP Verification:</b></td> $tccell2l rowspan=2>
			<select name=verify tabindex='30'>
				<option selected value=0>Don't use</option>
				<option value=1> /8 $verifyoptext[1]</option>
				<option value=2>/16 $verifyoptext[2]</option>
				<option value=3>/24 $verifyoptext[3]</option>
				<option value=4>/32 $verifyoptext[4]</option>
			</select><br><small>You can require your IP address to match your current IP (to an extent) to remain logged in.</small>
		</tr><tr>
		$tccell1><b>Password:</b></td>        $tccell2l>$inpp=userpass tabindex='20' MAXLENGTH=64 style='width:180px;'></td>
		</tr><tr>
		$tccell1>&nbsp;</td>$tccell2l colspan=3>
		$inph=action VALUE=login>
		$inps=submit tabindex='40' VALUE=Login></td></tr>
    $fend
  ";
}
