<?php
	require_once 'lib/actions/function.php';
	require_once 'lib/actions/layout.php';

	// Bots don't need to be on this page
	$meta['noindex'] = true;

	$username = $_POST['username'];
	$password = $_POST['userpass'];
	$verifyid = $_POST['verify'];

	if ($GLOBALS['skip_header']) {
		// Skip the regular header.
		$txt = "$tblstart";
	}
	else {
		$txt = "$header<br>$tblstart";
	}

	if($_POST['action']=='login') {
		if (!$username)
			$msg = "Couldn't login.  You didn't input a username.";
		else {
			$username = trim($username);
			$userid = check_login($username,$password);

			if($userid!=-1) {
				set_user_login_cookies($userid, $verifyid);
				$msg = "You are now logged in as $username.";
			}
			else {
				$sql->query("INSERT INTO `failedlogins` SET `time` = '". ctime() ."', `username` = '". $username ."', `password` = '". $password ."', `ip` = '". $_SERVER['REMOTE_ADDR'] ."'");
				$fails = $sql->resultq("SELECT COUNT(`id`) FROM `failedlogins` WHERE `ip` = '". $_SERVER['REMOTE_ADDR'] ."' AND `time` > '". (ctime() - 1800) ."'");

				// Keep in mind, it's now not possible to trigger this if you're IP banned
				// when you could previously, making extra checks to stop botspam not matter

				//if ($fails > 1)
				@xk_ircsend("102|". xk(14) ."Failed attempt". xk(8) ." #$fails ". xk(14) ."to log in as ". xk(8) . $username . xk(14) ." by IP ". xk(8) . $_SERVER['REMOTE_ADDR'] . xk(14) .".");

				if ($fails >= 5) {
					$sql->query("INSERT INTO `ipbans` SET `ip` = '". $_SERVER['REMOTE_ADDR'] ."', `reason` = 'Send e-mail for password recovery'");
					@xk_ircsend("102|". xk(7) ."Auto-IP banned ". xk(8) . $_SERVER['REMOTE_ADDR'] . xk(7) ." for this.");
					@xk_ircsend("1|". xk(7) ."Auto-IP banned ". xk(8) . $_SERVER['REMOTE_ADDR'] . xk(7) ." for repeated failed logins.");
				}

				$msg = "Couldn't login.  Either you didn't enter an existing username, or you haven't entered the right password for the username.";
			}
		}
		$txt.="$tccell1>$msg<br>".redirect("{$GLOBALS['jul_base_dir']}/",'the forum index',0);
	}
	elseif ($_POST['action']=='logout') {
		setcookie('loguserid','', time()-3600, "/", $_SERVER['SERVER_NAME'], false, true);
		setcookie('logverify','', time()-3600, "/", $_SERVER['SERVER_NAME'], false, true);

		// May as well unset this as well
		setcookie('logpassword','', time()-3600, "/", $_SERVER['SERVER_NAME'], false, true);
		$txt.="$tccell1> You are now logged out.<br>".redirect("{$GLOBALS['jul_base_dir']}/",'the index',0);
	}
	elseif (!$_POST['action']) {
		$ipaddr = explode('.', $_SERVER['REMOTE_ADDR']);
		for ($i = 4; $i > 0; --$i) {
			$verifyoptext[$i] = "(".implode('.', $ipaddr).")";
			$ipaddr[$i-1]       = 'xxx';
		}
		$txt .= "<body onload=window.document.replier.username.focus()>";
		$txt .= get_login_form();
	}
	else { // Just what do you think you're doing
		$sql->query("INSERT INTO `ipbans` SET `ip` = '". $_SERVER['REMOTE_ADDR'] ."', `reason` = 'Generic internet exploit searcher'");
		if (!mysql_error())
			xk_ircsend("1|". xk(7) ."Auto-banned asshole trying to be clever with the login form (action: ".xk(8).$_POST['action'].xk(7).") with IP ". xk(8) . $_SERVER['REMOTE_ADDR'] . xk(7) .".");
	}

	if ($GLOBALS['skip_footer']) {
		// Skip the footer.
		print($txt);
	}
	else {
		print $txt.$tblend.$footer;
		printtimedif($startingtime);
	}
