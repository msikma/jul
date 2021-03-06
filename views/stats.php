<?php
require_once 'lib/actions/function.php';
require_once 'lib/actions/layout.php';

$misc   = $sql->fetchq('SELECT * FROM misc');
$tstats = $sql->query('SHOW TABLE STATUS');
while ($t = $sql->fetch($tstats)) $tbl[$t['Name']]=$t;

print "
$header
<br>$tblstart
<tr>$tccellh>Interesting statistics</td></tr>
<tr>$tccell1l>
	<img src='{$GLOBALS['jul_base_dir']}/ext/ppdgauge.png' width='256' height='256' alt='Posts in last 24 hours' title='Posts in last 24 hours' style='display: block; float: right;'>
	<ul>
		<li><a href='{$GLOBALS['jul_views_path']}/activeusers.php'>Recently active posters</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/milestones.php'>Post milestones</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/sigsize.php'>Biggest posters</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/sigsize.php'>Largest post layouts</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/sigsize.php?bio=1'>Largest bios</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/ext/activity.png?u=". ($loguserid ? $loguserid : 1) ."'>Graph of your posting history</a> (change the ID in the URL to see others)</li>
		<li><a href='{$GLOBALS['jul_views_path']}/ext/activity2.png'>Graph of the top 10 posters</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/ext/activity3.png'>Graph of total post count and posts per day</a></li>
		<li><a href='{$GLOBALS['jul_views_path']}/ext/activity3u.png'>Graph of active users per day</a></li>
	</ul>
</td>
</tr>
$tblend
<br>$tblstart
$tccellh width='200'>Records$tccellh>&nbsp<tr>
$tccell1s><b>Most posts within 24 hours:</td>
$tccell2ls>".($misc['maxpostsdaydate'] ? "$misc[maxpostsday], on ".date($dateformat,$misc['maxpostsdaydate']) : 'Unknown')."<tr>
$tccell1s><b>Most posts within 1 hour:</td>
$tccell2ls>".($misc['maxpostshourdate'] ? "$misc[maxpostshour], on ".date($dateformat,$misc['maxpostshourdate']) : 'Unknown')."<tr>
$tccell1s><b>Most users online:</td>
$tccell2ls>".($misc['maxusersdate'] ? "$misc[maxusers], on ".date($dateformat,$misc['maxusersdate']) : 'Unknown')."$misc[maxuserstext]
$tblend<br>".
/*
// This is kind of in Edit Profile already.
"$tblstart<tr>$tccellh colspan='2'>Scheme Usage Breakdown</td></tr>
<tr>$tccellh>Scheme Name</td>$tccellh>Users</td></tr>
$sch_info
$tblend<br>".
*/
"$tblstart<tr>
$tccellh>Table name</td>
$tccellh>Rows</td>
$tccellh>Avg. data/row</td>
$tccellh>Data size</td>
$tccellh>Index size</td>
$tccellh>Overhead</td>
$tccellh>Total size</td></tr>"
.tblinfo('posts_text')
.tblinfo('posts')
.tblinfo('pmsgs_text')
.tblinfo('pmsgs')
.tblinfo('postlayouts')
.tblinfo('threads')
.tblinfo('users')
.tblinfo('forumread')
.tblinfo('threadsread')
.tblinfo('postradar')
.tblinfo('ipbans')
.tblinfo('defines')
.tblinfo('dailystats')
.tblinfo('rendertimes')
."$tblend
<br>
$tblstart<tr>
$tccellhs colspan=9>Daily stats<tr>
$tccellcs>Date</td>
$tccellcs>Total users</td>
$tccellcs>Total posts</td>
$tccellcs>Total threads</td>
$tccellcs>Total views</td>
$tccellcs>New users</td>
$tccellcs>New posts</td>
$tccellcs>New threads</td>
$tccellcs>New views</td></tr>
";
$users=0;
$posts=0;
$threads=0;
$views=0;
$stats=$sql->query("SELECT * FROM dailystats");
while($day=$sql->fetch($stats)){
	print "<tr>
	$tccell1s>$day[date]</td>
	$tccell2s>$day[users]</td>
	$tccell2s>$day[posts]</td>
	$tccell2s>$day[threads]</td>
	$tccell2s>$day[views]</td>
	$tccell2s>".($day['users']-$users)."</td>
	$tccell2s>".($day['posts']-$posts)."</td>
	$tccell2s>".($day['threads']-$threads)."</td>
	$tccell2s>".($day['views']-$views)."</td></tr>
	";
	$users=$day['users'];
	$posts=$day['posts'];
	$threads=$day['threads'];
	$views=$day['views'];
}
print $tblend.$footer;
printtimedif($startingtime);



function sp($sz) {
	$b=number_format($sz,0,'.',',');
	return $b;
}

function tblinfo($n) {
	global $tbl,$tccell2,$tccell2l;
	$t=$tbl[$n];
	return "
	<tr align=right>
	$tccell2>$t[Name]</td>
	$tccell2l>".sp($t['Rows']) ."</td>
	$tccell2l>".sp($t['Avg_row_length'])."</td>
	$tccell2l>".sp($t['Data_length'])."</td>
	$tccell2l>".sp($t['Index_length'])."</td>
	$tccell2l>".sp($t['Data_free'])."</td>
	$tccell2l>".sp($t['Data_length']+$t['Index_length'])."</td></tr>";
}
