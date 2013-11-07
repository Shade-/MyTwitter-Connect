<?php

/**
 * MyTwitter Connect
 * 
 * Integrates MyBB with Twitter, featuring login and registration.
 *
 * @package MyTwitter Connect
 * @page Main
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.0.2
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'mytwconnect.php');
define('ALLOWABLE_PAGE', 'twlogin,twregister,do_twlogin,authenticate');

require_once "./global.php";

define("CONSUMER_KEY", $mybb->settings['mytwconnect_conskey']);
define("CONSUMER_SECRET", $mybb->settings['mytwconnect_conssecret']);
define("OAUTH_CALLBACK", $mybb->settings['bburl'] . "/mytwconnect.php?action=authenticate");

$lang->load('mytwconnect');

if (!$mybb->settings['mytwconnect_enabled']) {
	header("Location: index.php");
	exit;
}

session_start();

/* API LOAD */
try {
	include_once MYBB_ROOT . "mytwconnect/src/twitteroauth.php";
}
catch (Exception $e) {	
	error_log($e);
}

// empty configuration
if (CONSUMER_KEY === '' OR CONSUMER_SECRET === '') {
	error($lang->mytwconnect_error_noconfigfound);
}
/* END API LOAD */

// start all magic
if ($mybb->input['action'] == "twlogin") {
	
	if ($mybb->user['uid']) {
		error($lang->mytwconnect_error_alreadyloggedin);
	}
	
	$loginUrl = "/mytwconnect.php?action=do_twlogin";
	mytwconnect_login($loginUrl);
}

// don't stop the magic
if ($mybb->input['action'] == "do_twlogin") {
	
	// user detected, just tell him he his already logged in
	if ($mybb->user['uid']) {
		error($lang->mytwconnect_error_alreadyloggedin);
	}
	
	$access_token = $_SESSION['access_token'];
	
	$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	
	// get the user
	$userdata = (array) $Twitter->get('account/verify_credentials');
	// invalid/expired token
	if ($userdata['errors']) {
		error($lang->mytwconnect_error_oldsessionkey);
	}
	$magic = mytwconnect_run($userdata);
	if ($magic['error']) {
		$errors = $magic['data'];
		$mybb->input['action'] = "twregister";
	}
}

// authenticate callback
if ($mybb->input['action'] == "authenticate") {
	
	// auth token present, but invalid (prevents CSRF attacks)
	if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
		session_destroy();
		error($lang->mytwconnect_error_noauth);
	}
	
	$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
	$access_token = $Twitter->getAccessToken($_REQUEST['oauth_verifier']);
	$_SESSION['access_token'] = $access_token;
	
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);
	
	if ($Twitter->http_code == 200) {
		// The user has been verified
		header("Location: ".$_SESSION['redirecturl']);
		exit;
	} else {
		// No auth
		error($lang->mytwconnect_error_noauth);
	}
}

// don't stop the magic, again
if ($mybb->input['action'] == "twregister") {
	
	// user detected, just tell him he his already logged in
	if ($mybb->user['uid']) {
		error($lang->mytwconnect_error_alreadyloggedin);
	}
	
	// take the access token out of the session, just to beautify it a bit
	$access_token = $_SESSION['access_token'];
	
	// we have no auth here
	if(empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
		session_destroy();
		error($lang->mytwconnect_error_noauth);
	}
	
	$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	$userdata = (array) $Twitter->get('account/verify_credentials');
		
	// came from our reg page
	if ($mybb->request_method == "post") {
		$newuser = array();
		$newuser['name'] = $mybb->input['username'];
		$newuser['email'] = $mybb->input['email'];
		
		$settingsToAdd = array();
		$settingsToCheck = array(
			"twavatar",
			"twbio",
			"twlocation"
		);
		
		foreach ($settingsToCheck as $setting) {
			// variable variables. Yay!
			if ($mybb->input[$setting] == 1) {
				$settingsToAdd[$setting] = 1;
			} else {
				$settingsToAdd[$setting] = 0;
			}
		}
		
		// register it
		$newUserData = mytwconnect_register($newuser);
		
		// insert options and extra data
		if ($db->update_query('users', $settingsToAdd, 'uid = ' . (int) $newUserData['uid']) AND !($newUserData['error'])) {
			// update on-the-fly that array of data dude!
			$newUser = array_merge($newUserData, $settingsToAdd);
			// oh yeah, let's sync!
			mytwconnect_sync($newUser, $userdata);
			
			// login the user normally, and we have finished.	
			$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' AND sid != '" . $session->sid . "'");
			$newsession = array(
				"uid" => $newUserData['uid']
			);
			$db->update_query("sessions", $newsession, "sid='" . $session->sid . "'");
			
			// finally log the user in
			my_setcookie("mybbuser", $newUserData['uid'] . "_" . $newUserData['loginkey'], null, true);
			my_setcookie("sid", $session->sid, -1, true);
			// redirect the user to where he came from
			if ($mybb->input['redUrl'] AND strpos($mybb->input['redUrl'], "action=twlogin") === false AND strpos($mybb->input['redUrl'], "action=twregister") === false) {
				$redirect_url = htmlentities($mybb->input['redUrl']);
			} else {
				$redirect_url = "index.php";
			}
			redirect($redirect_url, $lang->mytwconnect_redirect_registered, $lang->sprintf($lang->mytwconnect_redirect_title, $newUserData['username']));
		} else {
			$errors = $newUserData['data'];
		}
	}
	
	if ($errors) {
		$errors = inline_error($errors);
	}
	
	$options = "";
	
	$settingsToBuild = array(
		"twavatar"
	);
	
	// checking if we want to sync that stuff (admin)
	$settingsToCheck = array(
		"twbio",
		"twlocation"
	);
	
	foreach ($settingsToCheck as $setting) {
		$tempKey = 'mytwconnect_' . $setting;
		if ($mybb->settings[$tempKey]) {
			$settingsToBuild[] = $setting;
		}
	}
	
	foreach ($settingsToBuild as $setting) {
		// variable variables. Yay!
		$tempKey = 'mytwconnect_settings_' . $setting;
		$checked = " checked=\"checked\"";
		$label = $lang->$tempKey;
		$altbg = alt_trow();
		eval("\$options .= \"" . $templates->get('mytwconnect_register_settings_setting') . "\";");
	}
	
	// if the registration failed we might have a custom username which should be displayed
	if(!empty($mybb->input['username'])) {
		$userdata['name'] = $mybb->input['username'];
	}
	
	$username = "<input type=\"text\" class=\"textbox\" name=\"username\" value=\"{$userdata['name']}\" placeholder=\"{$lang->mytwconnect_register_username_placeholder}\" />";
	$email = "<input type=\"text\" class=\"textbox\" name=\"email\" value=\"{$mybb->input['email']}\" placeholder=\"{$lang->mytwconnect_register_email_placeholder}\" />";
	$redirectUrl = "<input type=\"hidden\" name=\"redUrl\" value=\"{$_SESSION['redirecturl']}\" />";
	
	// output our page
	eval("\$twregister = \"" . $templates->get("mytwconnect_register") . "\";");
	output_page($twregister);
}

if (!$mybb->input['action']) {
	header("Location: index.php");
	exit;
}