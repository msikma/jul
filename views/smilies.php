<?php
require_once 'lib/actions/function.php';
require_once 'lib/actions/layout.php';

 $base = base_dir().'/';

print "
$body
<title>Smilies</title>
$css
<center>
 <table height=100% valign=middle><td>
  $tblstart";

	foreach($s as $i => $v) {
		if (!($i % 4)) print "<tr>";

		if ($v) print "$tccell1><img src=\"{$base}". $v[1] ."\"></td>$tccell2>". $v[0] ."</td>";
	}
 print "$tblend
 </td></table>
";
