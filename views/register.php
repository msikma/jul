<?php

require_once 'lib/actions/function.php';
require_once 'lib/actions/layout.php';

print $header;
$is_viewing_form = $_POST['action'] !== 'Register';
$is_registering = $_POST['action'] === 'Register';
$is_start = !$_POST['action'];

if (!is_registration_enabled()) {
	error_page_common('Registration is disabled. Please contact an admin if you have any questions.');
}

if ($is_start){
	// When loading the page without posting any data.
  $descbr="</b>$smallfont<br></center>&nbsp";
  print "
<body onload=window.document.replier.username.focus()>
<form ACTION={$GLOBALS['jul_views_path']}/register.php NAME=replier METHOD=POST>
<br>$tblstart

$tccellh colspan=2>Login information</td><tr>
$tccell1><b>User name:</b>$descbr The name you want to use on the board.</td>
$tccell2l width=50%>$inpt=name SIZE=25 MAXLENGTH=25><tr>
$tccell1><b>Password:</b>$descbr Enter any password up to 32 characters in length. It can later be changed by editing your profile.<br><br>Warning: Do <b>not</b> use unsecure passwords such as '123456', 'qwerty', or 'pokemon'. It'll result in an instant IP ban.</td>
$tccell2l width=50%>$inpp=pass SIZE=13 MAXLENGTH=64><tr>
$tccellh>&nbsp</td>$tccellh>&nbsp<tr>
$tccell1>&nbsp</td>$tccell2l>
$inph=action VALUE=\"Register\">
$inps=submit VALUE=\"Register account\"></td>
</table>
	<div style='visibility: hidden;'><b>Homepage:</b><small> DO NOT FILL IN THIS FIELD. DOING SO WILL RESULT IN INSTANT IP-BAN.</small> - $inpt=homepage SIZE=25 MAXLENGTH=255></div>

	</form>

  ";
}

if ($is_viewing_form) {
  print($footer);
  printtimedif($startingtime);
  exit;
}

// Check if the user is using a proxy. Ban if this is the case.
if (is_using_proxy()) {
	ban_ip($ip, "Auto IP ban due to proxy (tried to register using '{$name}')", false);
	print("$tccell1>Thank you, $name, for registering your account.<br>".redirect("{$GLOBALS['jul_base_dir']}/index.php",'the board',0).$footer);
	exit;
}

if (!$is_registering) {
	exit;
}

// User information from registration post.
$ip = $_SERVER['REMOTE_ADDR'];
$username = $_POST['name'];
$password = $_POST['pass'];

// Check if the username/ip already exists.
$duplicate_info = check_registration($name, $ip);

$reason = false;
if ($duplicate_info['ip_exists']) {
	// IP already exists and we don't allow multiple/sockpuppet accounts.
	$reason = "You have already registered! (<a href={$GLOBALS['jul_views_path']}/profile.php?id={$duplicate_info['ip_id']}>here</a>)";
}
else if ($duplicate_info['username_exists']) {
	// Username already exists.
	$reason = "That username is already in use.";
}
else if (!$username || !$password) {
	$reason = "You haven't entered a username or password.";
}

if ($reason) {
	// If something went wrong, report an error.
	print("
   $tccell1>Couldn't register the account. {$reason}
   <br>".redirect("{$GLOBALS['jul_base_dir']}/index.php","the board",0)
	);
}
if ($reason === false) {
	// All good. Register the account.
	$success = register_account($username, $password, $ip);
	if ($success[0]) {
		// All done.
		print(
			"$tccell1>Thank you, $username, for registering your account.<br>".redirect("{$GLOBALS['jul_base_dir']}/index.php",'the board',0)
		);
	}
	else {
		// Something went wrong while registering. Post the error.
		$reason = $success[1];
		print("
	   $tccell1>Couldn't register the account. {$reason}
	   <br>".redirect("{$GLOBALS['jul_base_dir']}/index.php","the board",0)
		);
	}
}

print($tblend);
print($footer);
printtimedif($startingtime);
