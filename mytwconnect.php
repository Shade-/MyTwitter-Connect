<?php

/**
 * MyTwitter Connect
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'mytwconnect.php');
define('ALLOWABLE_PAGE', 'login,do_login,register');

require_once "./global.php";

$lang->load('mytwconnect');

if (!$mybb->settings['mytwconnect_enabled']) {

	header("Location: index.php");
	exit;

}

// Registrations are disabled
if ($mybb->settings['disableregs'] == 1 and !$mybb->settings['mytwconnect_keeprunning']) {

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

	// Already logged in? Redirect to the homepage
	if ($mybb->user['uid']) {
		header('Location: index.php');
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
			$mybb->input['action'] = 'register';

		}
	}
}

// Receive the incoming data from Twitter and evaluate the user
if ($mybb->input['action'] == 'register') {

	// Already logged in? Redirect to the homepage
	if ($mybb->user['uid']) {
		header('Location: index.php');
	}

	$user = $TwitterConnect->get_user();

	if (!$user) {
		$TwitterConnect->authenticate();
	}

	$settingsToAdd = [];
	$settingsToCheck = [
		"twavatar",
		"twbio",
		"twlocation"
	];

	// Came from our reg page
	if ($mybb->request_method == "post") {

		$newuser = [
			'name' => $mybb->input['username'],
			'email' => $mybb->input['email']
		];

		foreach ($settingsToCheck as $setting) {
			$settingsToAdd[$setting] = ($mybb->input[$setting] == 1) ? 1 : 0;
		}

		// Register him
		$user = $TwitterConnect->register($newuser);

		// Insert options and extra data and login
		if (!$user['error']) {

			$db->update_query('users', $settingsToAdd, 'uid = ' . (int) $user['uid']);

			// Sync
			$TwitterConnect->sync(array_merge($user, $settingsToAdd));

			// Login
			$TwitterConnect->login($user);

			// Redirect
			$TwitterConnect->redirect($mybb->input['redirect_url'], $lang->sprintf($lang->mytwconnect_redirect_title, $user['username']), $lang->mytwconnect_redirect_registered);

		}
		else {
			$errors = inline_error($user['error']);
		}
	}

	$options = '';
	$settingsToBuild = [];

	foreach ($settingsToCheck as $setting) {

		$tempKey = 'mytwconnect_' . $setting;

		if ($mybb->settings[$tempKey]) {
			$settingsToBuild[] = $setting;
		}

	}

	$checked = " checked=\"checked\"";

	foreach ($settingsToBuild as $setting) {

		$tempKey = 'mytwconnect_settings_' . $setting;

		$label = $lang->$tempKey;
		$altbg = alt_trow();
		eval("\$options .= \"" . $templates->get('mytwconnect_register_settings_setting') . "\";");

	}

	// If registration failed, we certainly have some custom inputs, so we have to display them instead of the Twitter ones
	if ($mybb->input['username']) {
		$user['name'] = htmlspecialchars_uni($mybb->input['username']);
	}

	if ($mybb->input['email']) {
		$user['email'] = htmlspecialchars_uni($mybb->input['email']);
	}

	$lang->mytwconnect_register_basic_info = $lang->sprintf($lang->mytwconnect_register_basic_info, $user['id']);

	$redirect_url = $_SERVER['HTTP_REFERER'];

	// Output our page
	eval("\$register = \"" . $templates->get("mytwconnect_register") . "\";");
	output_page($register);

}
