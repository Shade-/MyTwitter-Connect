<?php

/**
 * MyTwitter Connect
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'mytwconnect.php');
define('ALLOWABLE_PAGE', 'login,register,do_login');

require_once "./global.php";

$lang->load('mytwconnect');

if (!$mybb->settings['mytwconnect_enabled']) {

	header("Location: index.php");
	exit;
	
}

// Registrations are disabled
if ($mybb->settings['disableregs'] == 1) {

	if (!$lang->registrations_disabled) {
		$lang->load("member");
	}
	
	error($lang->registrations_disabled);
	
}

// Load API
require_once MYBB_ROOT . "inc/plugins/MyTwitterConnect/class_twitter.php";
$TwitterConnect = new MyTwitter();

// If the user is watching another page, fallback to login
if (!in_array($mybb->input['action'], explode(',', ALLOWABLE_PAGE))) {
	$mybb->input['action'] = 'login';
}

// Begin the authenticating process
if ($mybb->input['action'] == 'login') {
	
	if ($mybb->user['uid']) {
		error($lang->mytwconnect_error_alreadyloggedin);
	}
	
	$TwitterConnect->authenticate();
	
}

// Receive the incoming data from Twitter and evaluate the user
if ($mybb->input['action'] == 'do_login') {
	
	// Already logged in? You should not use this
	if ($mybb->user['uid']) {
		error($lang->mytwconnect_error_alreadyloggedin);
	}
	
	$TwitterConnect->obtain_tokens();
	
	// Attempt to get an user if authenticated
	$user = $TwitterConnect->get_user();
	if ($user) {
	
		$process = $TwitterConnect->process($user);
		
		if ($process['error']) {
			$errors = $process['error'];
			$mybb->input['action'] = "twregister";
		}
	}
}

// Receive the incoming data from Twitter and evaluate the user
if ($mybb->input['action'] == 'register') {
	
	// Already logged in? You should not use this
	if ($mybb->user['uid']) {
		error($lang->mytwconnect_error_alreadyloggedin);
	}
	
	$user = $TwitterConnect->get_user();
	
	if (!$user) {
		$TwitterConnect->authenticate();
	}
		
	// Came from our reg page
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
		
			if ($mybb->input[$setting] == 1) {
				$settingsToAdd[$setting] = 1;
			}
			else {
				$settingsToAdd[$setting] = 0;
			}
			
		}
		
		// Register him
		$user = $TwitterConnect->register($newuser);
		
		// Insert options and extra data and login
		if (!$user['error']) {
		
			$db->update_query('users', $settingsToAdd, 'uid = ' . (int) $user['uid']);
			
			// Sync
			$newUser = array_merge($user, $settingsToAdd);
			$TwitterConnect->sync($newUser);
			
			// Login
			$TwitterConnect->login($user);
			
			// Redirect
			$TwitterConnect->redirect($mybb->input['redUrl'], $lang->sprintf($lang->mytwconnect_redirect_title, $user['username']), $lang->mytwconnect_redirect_registered);
		}
		else {
			$errors = inline_error($user['error']);
		}
	}
	
	$options = '';
	$settingsToBuild = array();
	
	// Checking if we want to sync that stuff (admin)
	$settingsToCheck = array(
		'twavatar',
		'twbio',
		'twlocation'
	);
	
	foreach ($settingsToCheck as $setting) {
	
		$tempKey = 'mytwconnect_' . $setting;
		
		if ($mybb->settings[$tempKey]) {
			$settingsToBuild[] = $setting;
		}
		
	}
	
	foreach ($settingsToBuild as $setting) {
	
		$tempKey = 'mytwconnect_settings_' . $setting;
		$checked = " checked=\"checked\"";
		
		$label = $lang->$tempKey;
		$altbg = alt_trow();
		eval("\$options .= \"" . $templates->get('mytwconnect_register_settings_setting') . "\";");
		
	}
	
	// If registration failed, we certainly have some custom inputs, so we have to display them instead of the Twitter ones
	if ($mybb->input['username']) {
		$user['name'] = htmlspecialchars_uni($mybb->input['username']);
	}
	
	$email = htmlspecialchars_uni($mybb->input['email']);
	
	$username = "<input type=\"text\" class=\"textbox\" name=\"username\" value=\"{$user['name']}\" placeholder=\"{$lang->mytwconnect_register_username_placeholder}\" />";
	$email = "<input type=\"text\" class=\"textbox\" name=\"email\" value=\"{$email}\" placeholder=\"{$lang->mytwconnect_register_email_placeholder}\" />";
	$redirectUrl = "<input type=\"hidden\" name=\"redUrl\" value=\"{$_SESSION['redirecturl']}\" />";
	
	// Output our page
	eval("\$twregister = \"" . $templates->get("mytwconnect_register") . "\";");
	output_page($twregister);
	
}