<?php

/**
 * A bridge between MyBB and Twitter, featuring login, registration and more.
 *
 * @package MyTwitter Connect
 * @author  Shade <shad3-@outlook.com>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 3.0
 */

if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!defined("PLUGINLIBRARY")) {
	define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

if (!function_exists('verify_port_433')) {
	
	function verify_port_443()
	{
		global $mybb, $lang;
	
		if ($mybb->input['skip_port_check']) {
			return true;
		}
	
		// 3 seconds timeout to check for port 443 is enough
		$fp = @fsockopen('127.0.0.1', 443, $errno, $errstr, 3);
	
		// Port 443 is closed or blocked
		if (!$fp) {
	
			flash_message($lang->sprintf($lang->mytwconnect_error_port_443_not_open, $mybb->post_code), 'error');
			admin_redirect("index.php?module=config-plugins");
	
		}
	
		return true;
	
	}
	
}

function mytwconnect_info()
{
	return [
		'name' => 'MyTwitter Connect',
		'description' => 'Integrates MyBB with Twitter, featuring login and registration.',
		'website' => 'https://www.mybboost.com/forum-mytwitter-connect',
		'author' => 'Shade',
		'authorsite' => 'https://www.mybboost.com',
		'version' => '3.0',
		'compatibility' => '16*,18*',
	];
}

function mytwconnect_is_installed()
{
	global $cache;
	
	$info = mytwconnect_info();
	$installed = $cache->read("shade_plugins");
	if ($installed[$info['name']]) {
		return true;
	}
}

function mytwconnect_install()
{
	global $db, $PL, $lang, $mybb, $cache;
	
	if (!$lang->mytwconnect) {
		$lang->load('mytwconnect');
	}

	verify_port_443();
	
	if (!file_exists(PLUGINLIBRARY)) {
		flash_message($lang->mytwconnect_pluginlibrary_missing, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	$PL or require_once PLUGINLIBRARY;
	
	$PL->settings('mytwconnect', $lang->setting_group_mytwconnect, $lang->setting_group_mytwconnect_desc, [
		'enabled' => [
			'title' => $lang->setting_mytwconnect_enable,
			'description' => $lang->setting_mytwconnect_enable_desc,
			'value' => '1'
		],
		'conskey' => [
			'title' => $lang->setting_mytwconnect_conskey,
			'description' => $lang->setting_mytwconnect_conskey_desc,
			'value' => '',
			'optionscode' => 'text'
		],
		'conssecret' => [
			'title' => $lang->setting_mytwconnect_conssecret,
			'description' => $lang->setting_mytwconnect_conssecret_desc,
			'value' => '',
			'optionscode' => 'text'
		],
		'fastregistration' => [
			'title' => $lang->setting_mytwconnect_fastregistration,
			'description' => $lang->setting_mytwconnect_fastregistration_desc,
			'value' => '1'
		],
		'usergroup' => [
			'title' => $lang->setting_mytwconnect_usergroup,
			'description' => $lang->setting_mytwconnect_usergroup_desc,
			'value' => '2',
			'optionscode' => 'text'
		],
		'use_secondary' => [
			'title' => $lang->setting_mytwconnect_use_secondary,
			'description' => $lang->setting_mytwconnect_use_secondary_desc,
			'value' => '1'
		],
		'keeprunning' => [
			'title' => $lang->setting_mytwconnect_keeprunning,
			'description' => $lang->setting_mytwconnect_keeprunning_desc,
			'value' => '0'
		],
		
		// PM delivery
		'passwordpm' => [
			'title' => $lang->setting_mytwconnect_passwordpm,
			'description' => $lang->setting_mytwconnect_passwordpm_desc,
			'value' => '1'
		],
		'passwordpm_subject' => [
			'title' => $lang->setting_mytwconnect_passwordpm_subject,
			'description' => $lang->setting_mytwconnect_passwordpm_subject_desc,
			'optionscode' => 'text',
			'value' => $lang->mytwconnect_default_passwordpm_subject
		],
		'passwordpm_message' => [
			'title' => $lang->setting_mytwconnect_passwordpm_message,
			'description' => $lang->setting_mytwconnect_passwordpm_message_desc,
			'optionscode' => 'textarea',
			'value' => $lang->mytwconnect_default_passwordpm_message
		],
		'passwordpm_fromid' => [
			'title' => $lang->setting_mytwconnect_passwordpm_fromid,
			'description' => $lang->setting_mytwconnect_passwordpm_fromid_desc,
			'optionscode' => 'text',
			'value' => ''
		],
		
		// Avatar
		'twavatar' => [
			'title' => $lang->setting_mytwconnect_twavatar,
			'description' => $lang->setting_mytwconnect_twavatar_desc,
			'value' => '1'
		],
		
		// Location
		'twlocation' => [
			'title' => $lang->setting_mytwconnect_twlocation,
			'description' => $lang->setting_mytwconnect_twlocation_desc,
			'value' => '1'
		],
		'twlocationfield' => [
			'title' => $lang->setting_mytwconnect_twlocationfield,
			'description' => $lang->setting_mytwconnect_twlocationfield_desc,
			'optionscode' => 'text',
			'value' => '1'
		],
		
		// Bio
		'twbio' => [
			'title' => $lang->setting_mytwconnect_twbio,
			'description' => $lang->setting_mytwconnect_twbio_desc,
			'value' => '1'
		],
		'twbiofield' => [
			'title' => $lang->setting_mytwconnect_twbiofield,
			'description' => $lang->setting_mytwconnect_twbiofield_desc,
			'optionscode' => 'text',
			'value' => '2'
		],
		
		// Tweet on user's timeline
		'tweet' => [
			'title' => $lang->setting_mytwconnect_tweet,
			'description' => $lang->setting_mytwconnect_tweet_desc,
			'value' => '0'
		],
		'tweet_message' => [
			'title' => $lang->setting_mytwconnect_tweet_message,
			'description' => $lang->setting_mytwconnect_tweet_message_desc,
			'optionscode' => 'textarea',
			'value' => $lang->mytwconnect_default_tweet
		]
	]);

	$columns_to_check = ['twavatar', 'twbio', 'twlocation', 'VARCHAR(32) NOT NULL DEFAULT 0' => 'mytw_uid'];
	$columns_to_add = '';

	// Check if columns are already there (prevents duplicate installation errors)
	foreach ($columns_to_check as $type => $name) {

		if (!$db->field_exists($name, 'users')) {

			if (is_int($type)) {
				$type = 'int(1) NOT NULL DEFAULT 1';
			}

			$columns_to_add .= "`{$name}` $type,";

		}

	}

	$columns_to_add = rtrim($columns_to_add, ',');

	// Insert our Twitter columns into the database
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users ADD ({$columns_to_add})");
	
	// Insert our templates
	$dir = new DirectoryIterator(dirname(__FILE__) . '/MyTwitterConnect/templates');
	$templates = [];
	foreach ($dir as $file) {
		if (!$file->isDot() and !$file->isDir() and pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html') {
			$templates[$file->getBasename('.html')] = file_get_contents($file->getPathName());
		}
	}
	
	$PL->templates('mytwconnect', 'MyTwitter Connect', $templates);
	
	// Create cache
	$info = mytwconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	$shadePlugins[$info['name']] = [
		'title' => $info['name'],
		'version' => $info['version']
	];
	$cache->update('shade_plugins', $shadePlugins);
	
	// Add the login button variable to templates
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$lang->welcome_register}</a>') . '#i', '{$lang->welcome_register}</a>{$twitter_login}');
	
}

function mytwconnect_uninstall()
{
	global $db, $PL, $cache, $lang;
	
	if (!$lang->mytwconnect) {
		$lang->load('mytwconnect');
	}
	
	if (!file_exists(PLUGINLIBRARY)) {
		flash_message($lang->mytwconnect_pluginlibrary_missing, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	$PL or require_once PLUGINLIBRARY;
	
	// Drop settings
	$PL->settings_delete('mytwconnect');
	
	// Delete our columns
	$columns = [
		'twavatar',
		'twbio',
		'twlocation',
		'mytw_uid'
	];

	foreach ($columns as $field) {
		if ($db->field_exists($field, 'users')) {
			$db->drop_column('users', $field);
		}
	}
	
	// Delete the plugin from cache
	$info = mytwconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	unset($shadePlugins[$info['name']]);
	$cache->update('shade_plugins', $shadePlugins);
	
	$PL->templates_delete('mytwconnect');
	
	// Try to update templates
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$twitter_login}') . '#i', '');
	
}

global $mybb;

if ($mybb->settings['mytwconnect_enabled']) {
	
	// Global
	$plugins->add_hook('global_start', 'mytwconnect_global');
	
	// User CP
	$plugins->add_hook('usercp_menu', 'mytwconnect_usercp_menu', 40);
	$plugins->add_hook('usercp_start', 'mytwconnect_usercp');
	
	// Who's Online
	$plugins->add_hook("fetch_wol_activity_end", "mytwconnect_fetch_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "mytwconnect_build_wol_location");

	// Validation bypass
	$plugins->add_hook("datahandler_user_validate", "mytwconnect_user_validate");

	// Login button
	$plugins->add_hook("global_intermediate", "mytwconnect_load_login_button");
	
	// Logout
	$plugins->add_hook('member_logout_end', 'mytwconnect_logout');
	
	// Admin CP
	if (defined('IN_ADMINCP')) {

		// Update routines and settings
		$plugins->add_hook("admin_page_output_header", "mytwconnect_update");
		$plugins->add_hook("admin_page_output_footer", "mytwconnect_settings_footer");
		
		// Replace text inputs to select boxes dinamically
		$plugins->add_hook("admin_config_settings_change", "mytwconnect_settings_saver");
		$plugins->add_hook("admin_formcontainer_output_row", "mytwconnect_settings_replacer");
		
	}
	
}

function mytwconnect_global()
{
	
	global $mybb, $lang, $templatelist;
	
	if ($templatelist) {
		$templatelist = explode(',', $templatelist);
	}
	// Fixes common warnings (due to $templatelist being void)
	else {
		$templatelist = [];
	}
	
	if (THIS_SCRIPT == 'mytwconnect.php') {
	
		$templatelist[] = 'mytwconnect_register';
		$templatelist[] = 'mytwconnect_register_settings_setting';
		
	}
	
	if (THIS_SCRIPT == 'usercp.php') {
		$templatelist[] = 'mytwconnect_usercp_menu';
	}
	
	if (THIS_SCRIPT == 'usercp.php' and $mybb->input['action'] == 'mytwconnect') {
	
		$templatelist[] = 'mytwconnect_usercp_settings';
		$templatelist[] = 'mytwconnect_usercp_settings_linkprofile';
		$templatelist[] = 'mytwconnect_usercp_settings_setting';
		
	}
	
	$templatelist = implode(',', array_filter($templatelist));
	
	$lang->load('mytwconnect');
	
}

function mytwconnect_user_validate(&$data)
{
	// Bypass required profile fields during registration
	if (THIS_SCRIPT == 'mytwconnect.php') {

		unset ($data->errors['missing_required_profile_field'],
			   $data->errors['bad_profile_field_values'],
			   $data->errors['max_limit_reached']);

		return $data;

	}
}

function mytwconnect_load_login_button()
{
	global $twitter_login, $mybb, $templates, $lang;

	$lang->load('mytwconnect');

	eval("\$twitter_login = \"" . $templates->get('mytwconnect_login_button') . "\";");
}

function mytwconnect_logout()
{
	global $mybb;
	
	if (!session_id()) {
		session_start();
	}
	
	// Construct the security_key from scratch; requiring the Twitter API here doesn't make much sense
	if ($mybb->settings['mytwconnect_conskey'] and $mybb->settings['mytwconnect_conssecret']) {
		$security_key = md5($mybb->settings['mytwconnect_conskey'].$mybb->settings['mytwconnect_conssecret']);
	}
	
	// Destroy our token. This user must authenticate again the very next time (so if he logged out from Twitter he would be asked to log in again)
	if ($security_key and $_SESSION[$security_key]['access_token']) {
		unset($_SESSION[$security_key]['access_token']);
	}
}

function mytwconnect_usercp_menu()
{
	global $mybb, $templates, $theme, $usercpmenu, $lang, $collapsed, $collapsedimg;
	
	if (!$lang->mytwconnect) {
		$lang->load("mytwconnect");
	}
	
	eval("\$usercpmenu .= \"" . $templates->get('mytwconnect_usercp_menu') . "\";");
}

function mytwconnect_usercp()
{
	global $mybb, $lang;
	
	// Load API in certain areas
	if (in_array($mybb->input['action'], ['twlink', 'do_twlink']) or $_SESSION['twlogin'] or ($mybb->input['action'] == 'mytwconnect' and $mybb->request_method == 'post')) {
		
		require_once MYBB_ROOT . "inc/plugins/MyTwitterConnect/class_twitter.php";
		$TwitterConnect = new MyTwitter();
		
	}
	
	$settingsToCheck = [
		'twavatar',
		'twbio',
		'twlocation'
	];
	
	if (!$lang->mytwconnect) {
		$lang->load('mytwconnect');
	}
	
	// Authenticate
	if ($mybb->input['action'] == 'twlink') {
		
		$TwitterConnect->set_fallback('usercp.php?action=do_twlink');
		$TwitterConnect->authenticate();
		
	}
	
	// Link account to his Twitter's one
	if ($mybb->input['action'] == 'do_twlink') {
		
		$TwitterConnect->obtain_tokens();
		
		$user = $TwitterConnect->get_user();
		
		if ($user) {
			$TwitterConnect->link_user('', $user['id']);
		}
		else {			
			error($lang->mytwconnect_error_noauth);
		}
		
		$TwitterConnect->redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_account_linked_title, $lang->mytwconnect_success_account_linked);
		
	}
	
	// Settings page
	if ($mybb->input['action'] == 'mytwconnect') {
	
		global $db, $lang, $theme, $templates, $headerinclude, $header, $footer, $plugins, $usercpnav;
		
		add_breadcrumb($lang->nav_usercp, 'usercp.php');
		add_breadcrumb($lang->mytwconnect_page_title, 'usercp.php?action=mytwconnect');
				
		// The user is changing his settings
		if ($mybb->request_method == 'post' or $_SESSION['twlogin']) {
						
			if ($mybb->request_method == 'post') {
				verify_post_check($mybb->input['my_post_key']);
			}
			
			// He's unlinking his account
			if ($mybb->input['unlink']) {
			
				$TwitterConnect->unlink_user();
				$TwitterConnect->redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_account_unlinked_title, $lang->mytwconnect_success_account_unlinked);
				
			}
			// He's updating his settings
			else {
				
				$settings = [];
				
				foreach ($settingsToCheck as $setting) {
					
					$settings[$setting] = ($mybb->input[$setting] == 1) ? 1 : 0;
					
					// Build a list of parameters to include in the fallback URL
					$loginUrlExtra .= "&{$setting}=" . $settings[$setting];
					
				}
				
				// Process the tokens
				if ($_SESSION['twlogin']) {
					$TwitterConnect->obtain_tokens();
				}
				
				$user = $TwitterConnect->get_user();
				
				// This user is not logged in with Twitter
				if (!$user) {
					
					// Store a token in the session, we will check for it in the next call
					$_SESSION['twlogin'] = 1;
					
					$TwitterConnect->set_fallback("usercp.php?action=mytwconnect" . $loginUrlExtra);
					$TwitterConnect->authenticate();
					
					return;
					
				}
				
				$db->update_query('users', $settings, 'uid = ' . (int) $mybb->user['uid']);
					
				unset($_SESSION['twlogin']);
					
				$newUser = array_merge($mybb->user, $settings);
				$TwitterConnect->sync($newUser, $user);
				
				$TwitterConnect->redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_settings_updated_title, $lang->mytwconnect_success_settings_updated);

			}
		}
		
		$options = $unlink = $save = '';
		if ($mybb->user['mytw_uid']) {
			
			$userSettings = [];
		
			// Checking if admins and users want to sync that stuff
			foreach ($settingsToCheck as $setting) {
				
				$tempKey = 'mytwconnect_' . $setting;
				
				if (!$mybb->settings[$tempKey]) {
					continue;
				}
				
				$userSettings[$setting] = ($mybb->user[$setting]) ? 1 : 0;
				
			}
			
			$text = $lang->setting_mytwconnect_whattosync;
			$unlink = "<input type=\"submit\" class=\"button\" name=\"unlink\" value=\"{$lang->mytwconnect_settings_unlink}\" />";
			$save   = "<input type=\"submit\" class=\"button\" name=\"save\" value=\"{$lang->mytwconnect_settings_save}\" />";
			
			if ($userSettings) {
			
				foreach ($userSettings as $setting => $value) {
					
					$tempKey = 'mytwconnect_settings_' . $setting;
					
					$checked = '';
					
					if ($value) {
						$checked = " checked=\"checked\"";
					}
					
					$label = $lang->$tempKey;
					$altbg = alt_trow();
					
					eval("\$options .= \"" . $templates->get('mytwconnect_usercp_settings_setting') . "\";");
					
				}
				
			}
			else {
				$text = $lang->setting_mytwconnect_connected;
			}
			
		}
		else {
			
			$text = $lang->setting_mytwconnect_linkaccount;
			eval("\$options = \"" . $templates->get('mytwconnect_usercp_settings_linkprofile') . "\";");
			
		}
		
		eval("\$content = \"" . $templates->get('mytwconnect_usercp_settings') . "\";");
		output_page($content);
	}
}

function mytwconnect_update()
{
	global $mybb, $db, $cache, $lang;
	
	$file = MYBB_ROOT . "inc/plugins/MyTwitterConnect/class_update.php";
	
	if (file_exists($file)) {
		require_once $file;
	}
}

/**
 * Displays peekers in settings.
 **/

function mytwconnect_settings_footer()
{
	global $mybb, $db;
	
	if ($mybb->input["action"] == "change" and $mybb->request_method != "post") {
	
		$gid = mytwconnect_settings_gid();
		
		if ($mybb->input["gid"] == $gid or !$mybb->input['gid']) {
		
			// 1.8 has jQuery, not Prototype
			if ($mybb->version_code >= 1700) {
				echo '<script type="text/javascript">
	$(document).ready(function() {
		loadMyTWConnectPeekers();
		loadStars();
	});
	function loadMyTWConnectPeekers()
	{
		new Peeker($(".setting_mytwconnect_passwordpm"), $("#row_setting_mytwconnect_passwordpm_subject"), /1/, true);
		new Peeker($(".setting_mytwconnect_passwordpm"), $("#row_setting_mytwconnect_passwordpm_message"), /1/, true);
		new Peeker($(".setting_mytwconnect_passwordpm"), $("#row_setting_mytwconnect_passwordpm_fromid"), /1/, true);
		new Peeker($(".setting_mytwconnect_twbio"), $("#row_setting_mytwconnect_twbiofield"), /1/, true);
		new Peeker($(".setting_mytwconnect_twlocation"), $("#row_setting_mytwconnect_twlocationfield"), /1/, true);
		new Peeker($(".setting_mytwconnect_tweet"), $("#row_setting_mytwconnect_tweet_message"), /1/, true);
	}
	function loadStars()
	{
		add_star("row_setting_mytwconnect_appid");
		add_star("row_setting_mytwconnect_appsecret");
	}
	</script>';
			}
			else {
				echo '<script type="text/javascript">
	Event.observe(window, "load", function() {
		loadMyTWConnectPeekers();
		loadStars();
	});
	function loadMyTWConnectPeekers()
	{
		new Peeker($$(".setting_mytwconnect_passwordpm"), $("row_setting_mytwconnect_passwordpm_subject"), /1/, true);
		new Peeker($$(".setting_mytwconnect_passwordpm"), $("row_setting_mytwconnect_passwordpm_message"), /1/, true);
		new Peeker($$(".setting_mytwconnect_passwordpm"), $("row_setting_mytwconnect_passwordpm_fromid"), /1/, true);
		new Peeker($$(".setting_mytwconnect_twbio"), $("row_setting_mytwconnect_twbiofield"), /1/, true);
		new Peeker($$(".setting_mytwconnect_twlocation"), $("row_setting_mytwconnect_twlocationfield"), /1/, true);
		new Peeker($$(".setting_mytwconnect_tweet"), $("row_setting_mytwconnect_tweet_message"), /1/, true);
	}
	function loadStars()
	{
		add_star("row_setting_mytwconnect_conskey");
		add_star("row_setting_mytwconnect_conssecret");
	}
	</script>';
			}
		}
	}
}

/**
 * Gets the gid of MyTwitter Connect settings group.
 **/

function mytwconnect_settings_gid()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "gid", "name = 'mytwconnect'", [
		"limit" => 1
	]);
	$gid = $db->fetch_field($query, "gid");
	
	return intval($gid);
}

function mytwconnect_fetch_wol_activity(&$user_activity)
{
    global $user, $mybb;

    // get the base filename
    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location'])
    {
        $filename = '';
    }
    else
    {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }

    // get parameters of the URI
    if($split_loc[1])
    {
        $temp = explode("&amp;", my_substr($split_loc[1], 1));
        foreach($temp as $param)
        {
            $temp2 = explode("=", $param, 2);
            $temp2[0] = str_replace("amp;", '', $temp2[0]);
            $parameters[$temp2[0]] = $temp2[1];
        }
    }
    
	// if our plugin is found, store our custom vars in the main $user_activity array
    switch($filename)
    {
        case "mytwconnect":
            if($parameters['action'])
            {
				$user_activity['activity'] = $parameters['action'];
            }
			break;
    }
    
    return $user_activity;
}

function mytwconnect_build_wol_location(&$plugin_array)
{
    global $db, $lang, $mybb, $_SERVER;
    
    $lang->load('mytwconnect');
	
	// let's see what action we are watching
    switch($plugin_array['user_activity']['activity'])
    {
        case "login":
		case "do_login":
            $plugin_array['location_name'] = $lang->mytwconnect_viewing_loggingin;
			break;
		case "twregister":
            $plugin_array['location_name'] = $lang->mytwconnect_viewing_registering;
            break;
    }
    return $plugin_array;
}

$GLOBALS['replace_custom_fields'] = ['twlocationfield', 'twbiofield'];

function mytwconnect_settings_saver()
{
	global $mybb, $page, $replace_custom_fields;

	if ($mybb->request_method == "post" and $mybb->input['upsetting'] and $page->active_action == "settings" and $mybb->input['gid'] == mytwconnect_settings_gid()) {
	
		foreach ($replace_custom_fields as $setting) {
		
			$parentfield = str_replace('field', '', $setting);
			
			$mybb->input['upsetting']['mytwconnect_'.$setting] = $mybb->input['mytwconnect_'.$setting.'_select'];
			
			// Reset parent field if empty
			if (!$mybb->input['upsetting']['mytwconnect_'.$setting]) {
				$mybb->input['upsetting']['mytwconnect_'.$parentfield] = 0;
			}
		}
		
		$mybb->input['upsetting']['mytwconnect_usergroup'] = $mybb->input['mytwconnect_usergroup_select'];
			
	}
}

function mytwconnect_settings_replacer($args)
{
	global $db, $lang, $form, $mybb, $page, $replace_custom_fields;

	if ($page->active_action != "settings" and $mybb->input['action'] != "change" and $mybb->input['gid'] != mytwconnect_settings_gid()) {
		return false;
	}
	
	// Fields
	$profilefields = ['' => ''];
	$query = $db->simple_select('profilefields', 'name, fid');
	while ($field = $db->fetch_array($query)) {
		$profilefields[$field['fid']] = $field['name'];
	}
	
	foreach ($replace_custom_fields as $setting) {
	
		if ($args['row_options']['id'] == "row_setting_mytwconnect_".$setting) {
	
			if (!$profilefields) {
				
				$args['content'] = $lang->mytwconnect_select_nofieldsavailable;
				
				continue;
				
			}
			
			$tempKey = 'mytwconnect_'.$setting;
			
			// Replace the textarea with a cool selectbox
			$args['content'] = $form->generate_select_box($tempKey."_select", $profilefields, $mybb->settings[$tempKey]);
			
		}
		
	}
		
	if ($args['row_options']['id'] == "row_setting_mytwconnect_usergroup") {
			
		$tempKey = 'mytwconnect_usergroup';
		$args['content'] = $form->generate_group_select($tempKey."_select", [$mybb->settings[$tempKey]]);
			
	}
}
