<?php

function userfields(){
	return 'posts,sex,powerlevel,birthday,aka,picture,moodurl,title,useranks,location,lastposttime,lastactivity,imood,pronouns';
}


function postcode($post,$set){
	global $tzoff, $smallfont, $ip, $quote, $edit, $dateshort, $dateformat, $tlayout, $textcolor, $numdir, $numfil, $tblstart, $hacks, $x_hacks, $loguser;

	$tblend		= "</table>";
	$exp		= calcexp($post['posts'],(ctime()-$post['regdate']) / 86400);
	$lvl		= calclvl($exp);
	$expleft	= calcexpleft($exp);

	if ($tlayout == 1) {
		$level		= "Level: $lvl";
		$poststext	= "Posts: ";
		$postnum	= "$post[num]/";
		$posttotal	= $post['posts'];
		$experience	= "EXP: $exp<br>For next: $expleft";
		$totalwidth	= 96;
		$barwidth	= $totalwidth-round(@($expleft/totallvlexp($lvl))*$totalwidth);

		if ($barwidth < 1) $barwidth=0;

		if ($barwidth > 0) $baron="<img src={$GLOBALS['jul_base_dir']}/images/$numdir"."bar-on.gif width=$barwidth height=8>";

		if ($barwidth < $totalwidth) $baroff="<img src={$GLOBALS['jul_base_dir']}/images/$numdir".'bar-off.gif width='.($totalwidth-$barwidth).' height=8>';
		$bar="<br><img src={$GLOBALS['jul_base_dir']}/images/$numdir"."barleft.gif height=8>$baron$baroff<img src={$GLOBALS['jul_base_dir']}/images/$numdir".'barright.gif height=8>';

	} else {
		$level		= "<img src={$GLOBALS['jul_base_dir']}/images/$numdir"."level.gif width=36 height=8><img src={$GLOBALS['jul_views_path']}/numgfx.php?n=$lvl&l=3&f=$numfil height=8>";
		$experience	= "<img src={$GLOBALS['jul_base_dir']}/images/$numdir"."exp.gif width=20 height=8><img src={$GLOBALS['jul_views_path']}/numgfx.php?n=$exp&l=5&f=$numfil height=8><br><img src={$GLOBALS['jul_base_dir']}/images/$numdir"."fornext.gif width=44 height=8><img src={$GLOBALS['jul_views_path']}/numgfx.php?n=$expleft&l=2&f=$numfil height=8>";
		$poststext	= "<img src={$GLOBALS['jul_base_dir']}/images/_.gif height=2><br><img src={$GLOBALS['jul_base_dir']}/images/$numdir"."posts.gif width=28 height=8>";
		$postnum	= "<img src={$GLOBALS['jul_views_path']}/numgfx.php?n=$post[num]/&l=5&f=$numfil height=8>";
		$posttotal	= "<img src={$GLOBALS['jul_views_path']}/numgfx.php?n=$post[posts]&f=$numfil".($post['num']?'':'&l=4')." height=8>";
		$totalwidth	= 56;
		$barwidth	= $totalwidth-round(@($expleft/totallvlexp($lvl))*$totalwidth);

		if($barwidth<1) $barwidth=0;

		if($barwidth>0) $baron="<img src={$GLOBALS['jul_base_dir']}/images/$numdir"."bar-on.gif width=$barwidth height=8>";

		if($barwidth<$totalwidth) $baroff="<img src={$GLOBALS['jul_base_dir']}/images/$numdir".'bar-off.gif width='.($totalwidth-$barwidth).' height=8>';
		$bar="<br><img src={$GLOBALS['jul_base_dir']}/images/$numdir"."barleft.gif width=2 height=8>$baron$baroff<img src={$GLOBALS['jul_base_dir']}/images/$numdir".'barright.gif width=2 height=8>';
	}


	if(!$post['num']){
		$postnum	= '';

		if($postlayout==1) $posttotal="<img src={$GLOBALS['jul_views_path']}/numgfx.php?n=$post[posts]&f=$numfil&l=4 height=8>";
	}


	$reinf=syndrome(filter_int($post['act']));

	if ($post['lastposttime']) {
		$sincelastpost	= 'Since last post: '.timeunits(ctime()-$post['lastposttime']);
	}
	$lastactivity	= 'Last activity: '.timeunits(ctime()-$post['lastactivity']);
	$since			= 'Since: '.@date($dateshort,$post['regdate']+$tzoff);
	$postdate		= date($dateformat,$post['date']+$tzoff);

	$threadlink		= "";
	if (filter_string($set['threadlink'])) {
		$threadlink	= ", in $set[threadlink]";
	}

	$post['edited']	= filter_string($post['edited']);
	if ($post['edited']) {
		//		.="<hr>$smallfont$post[edited]";
	}

  // Default layout
	if ($loguser['viewsig'] != 0) {
		return "
		<div style='position:relative'>
		$tblstart
		$set[tdbg] rowspan=2>
		  $set[userlink]$smallfont<br>
		  $set[userrank]$reinf<br>
	        $level$bar<br>
		  $set[userpic]<br>
		  ". (filter_bool($hacks['noposts']) ? "" : "$poststext$postnum$posttotal<br>") ."
		  $experience<br><br>
		  $since<br>
		  ". (isset($set['pronouns']) ? "<br>".$set['pronouns'] : "")."
		  ". (isset($set['location']) ? "<br>".$set['location'] : "")."
		  <br>
		  <br>
		  $sincelastpost<br>$lastactivity<br>
		  </font>
		  <br><img src={$GLOBALS['jul_base_dir']}/images/_.gif width=200 height=1>
		</td>
		$set[tdbg] height=1 width=100%>
		  <table cellspacing=0 cellpadding=2 width=100% class=fonts>
		    <td>Posted on $postdate$threadlink$post[edited]</td>
		    <td width=255><nobr>$quote$edit$ip
		  </table><tr>
		$set[tdbg] height=220 id=\"post". $post['id'] ."\">$post[headtext]$post[text]$post[signtext]</td>
		$tblend
		</div>";
	}

  // Non-defined / Blank
  // (Adelheid uses this)
	else {
		return "
		$tblstart
		$set[tdbg] rowspan=2>
		  $set[userlink]$smallfont<br>
		  $set[userrank]$reinf<br>
		  <br><img src={$GLOBALS['jul_base_dir']}/images/_.gif width=200 height=1>
		</td>
		$set[tdbg] height=1 width=100%>
		  <table cellspacing=0 cellpadding=2 width=100% class=fonts>
		    <td>Posted on $postdate$threadlink$post[edited]</td>
		    <td width=255><nobr>$quote$edit$ip
		  </table><tr>
		$set[tdbg] height=220 id=\"post". $post['id'] ."\">$post[headtext]$post[text]$post[signtext]</td>
		$tblend";
	}
}
