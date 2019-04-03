<?php

function userfields(){
	return 'posts,sex,powerlevel,birthday,aka,picture,moodurl,title,useranks,location,lastposttime,lastactivity,imood,pronouns';
}

/**
 * Returns the contents of the post.
 *
 * The post is returned in an array of three items:
 * The HTML before the post (containing the username and avatar, etc.),
 * the HTML of the post itself, and the closing HTML.
 */
function postcode($post, $set){
	global $tzoff, $user, $smallfont, $ip, $quote, $edit, $dateshort, $dateformat, $tlayout, $textcolor, $numdir, $numfil, $tblstart, $hacks, $x_hacks, $loguser, $ui_images;

	$tblend		= "</table>";
	$exp		= calcexp($post['posts'],((ctime()-$post['regdate'])) / 86400);
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

		if ($barwidth > 0) $baron="<img src={$ui_images['bar_on']} width=$barwidth height=8>";

		if ($barwidth < $totalwidth) $baroff="<img src={$ui_images['bar_off']} width=".($totalwidth-$barwidth)." height=8>";
		$bar="<br><img src={$ui_images['bar_left']} height=8>$baron$baroff<img src={$ui_images['bar_right']} height=8>";

	} else {
		$level		= "<img src={$GLOBALS['jul_base_dir']}/images/$numdir"."level.gif width=36 height=8><img src={$GLOBALS['jul_views_path']}/ext/numgfx.png?n=$lvl&l=3&f=$numfil height=8>";
		$experience	= "<img src={$GLOBALS['jul_base_dir']}/images/$numdir"."exp.gif width=20 height=8><img src={$GLOBALS['jul_views_path']}/ext/numgfx.png?n=$exp&l=5&f=$numfil height=8><br><img src={$GLOBALS['jul_base_dir']}/images/$numdir"."fornext.gif width=44 height=8><img src={$GLOBALS['jul_views_path']}/ext/numgfx.png?n=$expleft&l=2&f=$numfil height=8>";
		$poststext	= "<img src={$GLOBALS['jul_base_dir']}/static/images/spacer.gif height=2><br><img src={$GLOBALS['jul_base_dir']}/images/$numdir"."posts.gif width=28 height=8>";
		$postnum	= "<img src={$GLOBALS['jul_views_path']}/ext/numgfx.png?n=$post[num]/&l=5&f=$numfil height=8>";
		$posttotal	= "<img src={$GLOBALS['jul_views_path']}/ext/numgfx.png?n=$post[posts]&f=$numfil".($post['num']?'':'&l=4')." height=8>";
		$totalwidth	= 56;
		$barwidth	= $totalwidth-round(@($expleft/totallvlexp($lvl))*$totalwidth);

		if($barwidth<1) $barwidth=0;

		if($barwidth>0) $baron="<img src={$ui_images['bar_on']} width=$barwidth height=8>";

		if($barwidth<$totalwidth) $baroff="<img src={$ui_images['bar_off']} width='.($totalwidth-$barwidth).' height=8>";
		$bar="<br><img src={$ui_images['bar_left']} width=2 height=8>$baron$baroff<img src={$ui_images['bar_right']} width=2 height=8>";
	}


	if(!$post['num']){
		$postnum	= '';

		if($postlayout==1) $posttotal="<img src={$GLOBALS['jul_views_path']}/ext/numgfx.png?n=$post[posts]&f=$numfil&l=4 height=8>";
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
		return array(
			// Before
			"<div style='position:relative'>
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
			  <br><img src={$GLOBALS['jul_base_dir']}/static/images/spacer.gif width=200 height=1>
			</td>
			$set[tdbg] height=1 width=100%>
			  <table cellspacing=0 cellpadding=2 width=100% class=fonts>
			    <td>Posted on $postdate$threadlink$post[edited]</td>
			    <td width=255><nobr>$quote$edit$ip
			  </table><tr>
			$set[tdbg] height=220 class='jul-post-content' id=\"post". $post['id'] ."\">",
			// Post
			"$post[headtext]$post[text]$post[signtext]",
			// After
			"</td>$tblend
			</div>"
		);
	}

  // Non-defined / Blank
  // (Adelheid uses this)
	else {
		return array(
			// Before
			"$tblstart
			$set[tdbg] rowspan=2>
			  $set[userlink]$smallfont<br>
			  $set[userrank]$reinf<br>
			  <br><img src={$GLOBALS['jul_base_dir']}/static/images/spacer.gif width=200 height=1>
			</td>
			$set[tdbg] height=1 width=100%>
			  <table cellspacing=0 cellpadding=2 width=100% class='fonts'>
			    <td>Posted on $postdate$threadlink$post[edited]</td>
			    <td width=255><nobr>$quote$edit$ip
			  </table><tr>
			$set[tdbg] height=220 class='jul-post-content' id=\"post". $post['id'] ."\">",
			// Post
			"$post[headtext]$post[text]$post[signtext]",
			// After
			"</td>
			$tblend"
		);
	}
}
