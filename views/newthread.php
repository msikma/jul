<?php
require_once 'lib/actions/function.php';

$forum = get_forum_by_id($request['id']);
$windowtitle = "{$GLOBALS['jul_settings']['board_name']} -- $forum[title] -- New Thread";
$specialscheme  = $forum['specialscheme'];

// Stop this insanity.  Never index newthread.
$meta['noindex'] = true;

require_once 'lib/actions/layout.php';

$id = intval($request['id']);
$forum_link = route('@forum', $id);
$forumid = $id;
$forum = get_forum_by_id($id);
$fonline = fonlineusers($forumid);
$header = makeheader($header1,$headlinks,$header2 ."  $tblstart$tccell1s>$fonline$tblend");

print $header;

// FIXME
if ($poll) {
  $c=1;
  $d=0;
  while($chtext[$c+$d] || $c < $_POST['count']) {
    if ($remove[$c+$d]) {
      $d++;
    }
    else {
      $choices.="Choice $c: $inpt=chtext[$c] SIZE=30 MAXLENGTH=255 VALUE=\"".stripslashes(htmlspecialchars($chtext[$c+$d]))."\"> &nbsp Color: $inpt=chcolor[$c] SIZE=7 MAXLENGTH=25 VALUE=\"".stripslashes(htmlspecialchars($chcolor[$c+$d]))."\"> &nbsp <INPUT type=checkbox class=radio name=remove[$c] value=1> Remove<br>";
      $c++;
    }
  }
  $choices.="Choice $c: $inpt=chtext[$c] SIZE=30 MAXLENGTH=255> &nbsp Color: $inpt=chcolor[$c] SIZE=7 MAXLENGTH=25><br>$inps=paction VALUE=\"Submit changes\"> and show $inpt=count size=4 maxlength=2 VALUE=\"".stripslashes(htmlspecialchars(($_POST['count']) ? $_POST['count'] : $c))."\"> options";
  if ($mltvote) {
    $checked1='checked';
  }
  else {
    $checked0='checked';
  }
}

// TODO: move to render posticons function
$posticons = get_posticons(); // TODO
for($i=0;$posticons[$i];$i++) {
  if($iconid==$i) $checked='checked';
  $posticonlist.="$radio=iconid value=$i $checked>&nbsp<IMG SRC=$posticons[$i] HEIGHT=15 WIDTH=15>&nbsp; &nbsp;";
  $checked='';
  if(($i+1)%10==0) $posticonlist.='<br>';
}
if (!$iconid or $iconid==-1) {
  $checked='checked';
}
$posticonlist.="
  <br>$radio=iconid value=-1 $checked>&nbsp;None&nbsp; &nbsp; &nbsp;
  Custom: $inpt=custposticon SIZE=40 MAXLENGTH=100 VALUE=\"". stripslashes($custposticon) ."\">
";
// end posticons

// todo form generation
$subject_html = htmlspecialchars($subject);
$question_html = htmlspecialchars($question);

if ($nosmilies) $nosmilieschk = " checked";
if ($nohtml)  $nohtmlchk  = " checked";
if ($nolayout)  $nolayoutchk  = " checked";

$form = '';
if ($poll) {
  $form .= "
    <tr>$tccell1><b>Poll icon:</td> $tccell2l colspan=2>$posticonlist</td></tr>
    <tr>$tccell1><b>Poll title:</td>  $tccell2l colspan=2>$inpt=subject SIZE=40 MAXLENGTH=100 VALUE=\"". stripslashes($subject) ."\"></td></tr>
    <tr>$tccell1><b>Question:</td>  $tccell2l colspan=2>$inpt=question SIZE=60 MAXLENGTH=255 VALUE=\"". stripslashes($question) ."\"></td></tr>
    <tr>$tccell1><b>Briefing:</td>  $tccell2l colspan=2>$txta=briefing ROWS=2 COLS=$numcols style=\"resize:vertical;\">". stripslashes($briefing) ."</TEXTAREA></td></tr>
    <tr>$tccell1><b>Multi-voting:</td>$tccell2l colspan=2>$radio=mltvote value=0 $checked0> Disabled &nbsp $radio=mltvote value=1 $checked1> Enabled</td></tr>
    <tr>$tccell1><b>Choices:</td> $tccell2l colspan=2>$choices</td></tr>
  ";
}
else {
  $form .= "
    <tr>$tccell1><b>Thread icon:</td> $tccell2l colspan=2>$posticonlist</td></tr>
    <tr>$tccell1><b>Thread title:</td>$tccell2l colspan=2>$inpt=subject SIZE=40 MAXLENGTH=100 VALUE=\"". stripslashes($subject) ."\"></td></tr>
  ";
}
$form .= "
  <tr>$tccell1><b>Post:</td>$tccell2l width=800px valign=top>"."
  $txta=message ROWS=21 COLS=$numcols style=\"width: 100%; max-width: 800px; resize:vertical;\">". (htmlspecialchars($message)) ."</TEXTAREA></td>
  $tccell2l width=*>".emoticon_table()."</td></tr>

  <tr>
  $tccell1>&nbsp</td>$tccell2l colspan=2>
  $inph=action VALUE=postthread>
  $inph=id VALUE=$id>
  ".($poll ? "$inph=poll VALUE=1>" : "")."
  $inps=submit VALUE=\"".($poll ? "Submit poll" : "Submit thread")."\">
  $inps=preview VALUE=\"".($poll ? "Preview poll" : "Preview thread")."\"></td>
  <tr>
  <!-- </FORM> -->
";
// end $form

// either regularly viewing the new thread page, or when previewing
$not_submitting = !$_POST['action'];
$previewing = !!$_POST['paction'];

if ($not_submitting || $previewing) {
  print "
  $fonttag<a href={$GLOBALS['jul_base_dir']}/index.php>{$GLOBALS['jul_settings']['board_name']}</a> - <a href='{$forum_link}'>".$forum['title']."</a>
  <form action='".route('@new_thread', $id)."' name=replier method=post autocomplete=\"off\">
  $tblstart
  ";
  // Not allowed to post in this forum from lack of power.
  if ($log && $forums[$id]['minpowerthread']>$power) {
    print "$tccell1>Sorry, but you are not allowed to post";
    if ($banned) {
      print ", because you are banned from this board.<br>".redirect("{$forum_link}",'return to the forum',0);
    }
    else {
      print ' in this restricted forum.<br>'.redirect("{$GLOBALS['jul_base_dir']}/index.php",'return to the board',0);
    }
  }
  // Allowed to post.
  else {
    if ($log) {
      $username=$loguser['name'];
      $passhint = 'Alternate Login:';
      $altloginjs = "<a href=\"#\" onclick=\"document.getElementById('altlogin').style.cssText=''; this.style.cssText='display:none'\">Use an alternate login</a>
      <span id=\"altlogin\" style=\"display:none\">";
    }
    else {
      $username = '';
      $passhint = 'Login Info:';
      $altloginjs = "<span>";
    }

    print "
      <body onload=window.document.replier.subject.focus()>
      $tccellh width=150>&nbsp</td>$tccellh colspan=2>&nbsp<tr>
      $tccell1><b>{$passhint}</td> $tccell2l colspan=2>
      {$altloginjs}
      <b>Username:</b> $inpt=username VALUE=\"".htmlspecialchars($username)."\" SIZE=250 MAXLENGTH=250 autocomplete=\"off\">

      <!-- Hack around autocomplete, fake inputs (don't use these in the file) -->
      <input style=\"display:none;\" type=\"text\"   name=\"__f__usernm__\">
      <input style=\"display:none;\" type=\"password\" name=\"__f__passwd__\">

      <b>Password:</b> $inpp=password SIZE=13 MAXLENGTH=250 autocomplete=\"off\">
      </span><tr>";
    print $form;
  }
  print "
  </table>
  </table>
  </form>
  $fonttag<a href={$GLOBALS['jul_base_dir']}/index.php>{$GLOBALS['jul_settings']['board_name']}</a> - <a href='{$forum_link}'>".$forum['title']."</a>
  ";
}

// when posting and not previewing
if ($_POST['action']=='postthread' & !$previewing) {
  print "<br>$tblstart";
  if ($log && !$password) {
    $userid = $loguserid;
  } else {
    $userid = check_login($username,$password);
  }
  $user = get_user_by_id($userid);

  // Users <0 are banned or something.
  if($user['powerlevel'] < 0) $userid=-1;

  // can't be posting too fast now
  $limithit = check_user_posting_limit($user);
  // can they post in this forum?
  $authorized = check_user_forum_authority($user, $forum);

  //
  // If the user is permitted to post.
  //
  $can_post = can_user_post($user, $forum, $subject, $message);
  if ($can_post) {
    $msg = $message;
    $sign = $user['signature'];
    $head = $user['postheader'];

    // improved post backgrounds
    // TODO: remove
    if ($user['postbg']) {
      $head = "<table width=100% height=100% border=0 cellpadding=0 cellspacing=0><td valign=top background=\"$user[postbg]\">$head";
      $sign = "$sign</td></table>";
    }

    $numposts = $user['posts'] + 1;
    $numdays = (ctime()-$user['regdate'])/86400;
    $tags = array();
    $msg = doreplace($msg, $numposts, $numdays, $username, $tags);
    $rsign = doreplace($sign, $numposts, $numdays, $username);
    $rhead = doreplace($head, $numposts, $numdays, $username);
    $tagval = $sql->escape(json_encode($tags));
    $posticons = get_posticons(); // TODO
    $posticon = $posticons[$iconid];
    $currenttime = ctime();
    $postnum = $numposts;
    if($iconid == -1) $posticon='';
    if($custposticon) $posticon = $custposticon;

    if($submit) {
      update_user_post_count($user);
      $new_thread_id = make_new_thread($forum, $user, $subject, $posticon);
      $new_post_id = make_new_post($new_thread_id, $forum['id'], $user, $message);
      $new_thread_link = route('@thread', $new_thread_id);

      if(!$poll) {
        print "
          $tccell1>Thread posted successfully!
          <br>".redirect("{$new_thread_link}", stripslashes($subject), 0).$tblend;
        xk_notify('NEW_THREAD_POSTED', array('has_poll' => false, 'user' => $user));
      }
      else {
        // If we posted a poll, insert it now.
        $new_poll_id = make_new_poll($question, $briefing, $mltvote ? 1 : 0, $chtext, $chcolor);
        _update_thread_after_poll($new_poll_id, $new_thread_id);

        print "
          $tccell1>Poll created successfully!
          <br>".redirect("{$new_thread_link}", stripslashes($subject), 0).$tblend;
        xk_notify('NEW_THREAD_POSTED', array('has_poll' => true, 'user' => $user));
      }
    }
    else {
      if($posticon) $posticon1="<img src='". stripslashes($posticon) ."' height=15 align=absmiddle>";

      if($poll) {
        for($c=1;$chtext[$c];$c++) {
          $chtext[$c]=stripslashes($chtext[$c]);
          $chcolor[$c]=stripslashes($chcolor[$c]);
          $hchoices.="$inph=chtext[$c] VALUE=\"".htmlspecialchars($chtext[$c])."\">$inph=chcolor[$c] VALUE=\"".htmlspecialchars($chcolor[$c]).'">';
          $pchoices.="
          $tccell1l width=20%>$chtext[$c]</td>
          $tccell2l width=60%><table cellpadding=0 cellspacing=0 width=50% bgcolor='$chcolor[$c]'><td>&nbsp</table></td>
          $tccell1 width=20%>$fonttag ? votes, ??.?%<tr>
          ";
        }
        $mlt=($mltvote?'enabled':'disabled');
        $pollpreview="
          <td colspan=3 class='tbl tdbgc center font'><b>$question<tr>
          $tccell2ls colspan=3>$briefing<tr>
          $pchoices
          $tccell2ls colspan=3>Multi-voting is $mlt.
          $tblend<br>$tblstart
        ";
        $subject = htmlspecialchars(stripslashes($subject));
        $question = htmlspecialchars(stripslashes($question));
        $briefing = htmlspecialchars(stripslashes($briefing));
      }
      $ppost=$user;
      $ppost['uid']=$userid;
      $ppost['num']=$postnum;
      $ppost['posts']++;
      $ppost['lastposttime']=$currenttime;
      $ppost['date']=$currenttime;
      //$ppost['headtext']=$rhead;
      //$ppost['signtext']=$rsign;
      $ppost['text']=stripslashes($message);
      $ppost['options'] = $_POST['nosmilies'] . "|" . $_POST['nohtml'];
      if($isadmin) $ip=$userip;
      $threadtype=($poll?'poll':'thread');
      $form_action = route('@new_thread', $id);
      print "
      <body onload=window.document.replier.message.focus()>
      $tccellh>".($poll?'Poll':'Thread')." preview
      $tblend$tblstart
      $pollpreview
      $tccell2l>$posticon1 <b>". stripslashes($subject) ."</b>
      $tblend$tblstart
      ".threadpost($ppost,1)."
      $tblend<br>$tblstart
      <FORM ACTION='{$form_action}' NAME=REPLIER METHOD=POST>
      $tccellh width=150>&nbsp</td>$tccellh colspan=2>&nbsp<tr>
      $inph=username VALUE=\"".htmlspecialchars($username)."\">
      $inph=password VALUE=\"".htmlspecialchars($password)."\">
      $form
      </td></FORM>
      $tblend
      ";
    }
  }
  //
  // If the user for some reason isn't able to post.
  //
  else {
    $reason = "You haven't entered your username and password correctly.";
    if ($limithit) $reason = "You are trying to post too rapidly.";
    if (!$message) $reason = "You haven't entered a message.";
    if (!$subject) $reason = "You haven't entered a subject.";
    if (!$authorized) $reason = "You aren't allowed to post in this forum.";
    print "
      $tccell1>Couldn't post the thread. $reason
      <br>".redirect("{$forum_link}", $forum['title'], 0).$tblend;
  }
}

print $footer;
printtimedif($startingtime);
