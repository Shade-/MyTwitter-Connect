<?php

/**
 * A bridge between MyBB and Twitter, featuring login, registration and more.
 *
 * @package MyTwitter Connect
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 2.0
 */

if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!defined("PLUGINLIBRARY")) {
	define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

function mytwconnect_info()
{
	return array(
		'name' => 'MyTwitter Connect',
		'description' => 'Integrates MyBB with Twitter, featuring login and registration.',
		'website' => 'https://github.com/Shade-/MyTwitter-Connect',
		'author' => 'Shade',
		'authorsite' => '',
		'version' => '2.0',
		'compatibility' => '16*',
		'guid' => '4b4ec3336f071cf86b9ec92df02250eb'
	);
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
	
	if (!file_exists(PLUGINLIBRARY)) {
		flash_message($lang->mytwconnect_pluginlibrary_missing, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	$PL or require_once PLUGINLIBRARY;
	
	$PL->settings('mytwconnect', $lang->mytwconnect_settings, $lang->mytwconnect_settings_desc, array(
		'enabled' => array(
			'title' => $lang->mytwconnect_settings_enable,
			'description' => $lang->mytwconnect_settings_enable_desc,
			'value' => '1'
		),
		'conskey' => array(
			'title' => $lang->mytwconnect_settings_conskey,
			'description' => $lang->mytwconnect_settings_conskey_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'conssecret' => array(
			'title' => $lang->mytwconnect_settings_conssecret,
			'description' => $lang->mytwconnect_settings_conssecret_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'fastregistration' => array(
			'title' => $lang->mytwconnect_settings_fastregistration,
			'description' => $lang->mytwconnect_settings_fastregistration_desc,
			'value' => '1'
		),
		'usergroup' => array(
			'title' => $lang->mytwconnect_settings_usergroup,
			'description' => $lang->mytwconnect_settings_usergroup_desc,
			'value' => '2',
			'optionscode' => 'text'
		),
		
		// PM delivery
		'passwordpm' => array(
			'title' => $lang->mytwconnect_settings_passwordpm,
			'description' => $lang->mytwconnect_settings_passwordpm_desc,
			'value' => '1'
		),
		'passwordpm_subject' => array(
			'title' => $lang->mytwconnect_settings_passwordpm_subject,
			'description' => $lang->mytwconnect_settings_passwordpm_subject_desc,
			'optionscode' => 'text',
			'value' => $lang->mytwconnect_default_passwordpm_subject
		),
		'passwordpm_message' => array(
			'title' => $lang->mytwconnect_settings_passwordpm_message,
			'description' => $lang->mytwconnect_settings_passwordpm_message_desc,
			'optionscode' => 'textarea',
			'value' => $lang->mytwconnect_default_passwordpm_message
		),
		'passwordpm_fromid' => array(
			'title' => $lang->mytwconnect_settings_passwordpm_fromid,
			'description' => $lang->mytwconnect_settings_passwordpm_fromid_desc,
			'optionscode' => 'text',
			'value' => ''
		),
		
		// Avatar
		'twavatar' => array(
			'title' => $lang->mytwconnect_settings_twavatar,
			'description' => $lang->mytwconnect_settings_twavatar_desc,
			'value' => '1'
		),
		
		// Location
		'twlocation' => array(
			'title' => $lang->mytwconnect_settings_twlocation,
			'description' => $lang->mytwconnect_settings_twlocation_desc,
			'value' => '1'
		),
		'twlocationfield' => array(
			'title' => $lang->mytwconnect_settings_twlocationfield,
			'description' => $lang->mytwconnect_settings_twlocationfield_desc,
			'optionscode' => 'text',
			'value' => '1'
		),
		
		// Bio
		'twbio' => array(
			'title' => $lang->mytwconnect_settings_twbio,
			'description' => $lang->mytwconnect_settings_twbio_desc,
			'value' => '1'
		),
		'twbiofield' => array(
			'title' => $lang->mytwconnect_settings_twbiofield,
			'description' => $lang->mytwconnect_settings_twbiofield_desc,
			'optionscode' => 'text',
			'value' => '2'
		),
		
		// Tweet on user's timeline
		'tweet' => array(
			'title' => $lang->mytwconnect_settings_tweet,
			'description' => $lang->mytwconnect_settings_tweet_desc,
			'value' => '0'
		),
		'tweet_message' => array(
			'title' => $lang->mytwconnect_settings_tweet_message,
			'description' => $lang->mytwconnect_settings_tweet_message_desc,
			'optionscode' => 'textarea',
			'value' => $lang->mytwconnect_default_tweet
		)
	));
	
	// Insert our Twitter columns into the database
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users ADD (
		`twavatar` int(1) NOT NULL DEFAULT 1,
		`twbio` int(1) NOT NULL DEFAULT 1,
		`twlocation` int(1) NOT NULL DEFAULT 1,
		`mytw_uid` bigint(50) NOT NULL DEFAULT 0
		)");
	
	// Insert our templates	   
	$dir = new DirectoryIterator(dirname(__FILE__) . '/MyTwitterConnect/templates');
	$templates = array();
	foreach ($dir as $file) {
		if (!$file->isDot() and !$file->isDir() and pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html') {
			$templates[$file->getBasename('.html')] = file_get_contents($file->getPathName());
		}
	}
	
	$PL->templates('mytwconnect', 'MyTwitter Connect', $templates);
	
	// Create cache
	$info = mytwconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	$shadePlugins[$info['name']] = array(
		'title' => $info['name'],
		'version' => $info['version']
	);
	$cache->update('shade_plugins', $shadePlugins);
	
	// Try to update templates
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$lang->welcome_register}</a>') . '#i', '{$lang->welcome_register}</a> &mdash; <a href="{$mybb->settings[\'bburl\']}/mytwconnect.php?action=twlogin">{$lang->mytwconnect_login}</a>');
	
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
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP `twavatar`, DROP `twbio`, DROP `twlocation`, DROP `mytw_uid`");
	
	// Delete the plugin from cache
	$info = mytwconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	unset($shadePlugins[$info['name']]);
	$cache->update('shade_plugins', $shadePlugins);
	
	$PL->templates_delete('mytwconnect');
	
	// Try to update templates
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('&mdash; <a href="{$mybb->settings[\'bburl\']}/mytwconnect.php?action=twlogin">{$lang->mytwconnect_login}</a>') . '#i', '');
	
}

if ($settings['mytwconnect_enabled']) {
	
	// Global
	$plugins->add_hook('global_start', 'mytwconnect_global');
	
	// Logout
	$plugins->add_hook('member_logout_end', 'mytwconnect_logout');
	
	// User CP
	$plugins->add_hook('usercp_menu', 'mytwconnect_usercp_menu', 40);
	$plugins->add_hook('usercp_start', 'mytwconnect_usercp');
	
	// Who's Online
	$plugins->add_hook("fetch_wol_activity_end", "mytwconnect_fetch_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "mytwconnect_build_wol_location");
	
	// Admin CP
	if (defined('IN_ADMINCP')) {
		$plugins->add_hook("admin_page_output_header", "mytwconnect_update");
		$plugins->add_hook("admin_page_output_footer", "mytwconnect_settings_footer");
		
		// Custom module
        $plugins->add_hook("admin_config_menu", "mytwconnect_admin_config_menu");
        $plugins->add_hook("admin_config_action_handler", "mytwconnect_admin_config_action_handler");
		
		// Replace text inputs to select boxes dinamically
		$plugins->add_hook("admin_config_settings_change", "mytwconnect_settings_saver");
		$plugins->add_hook("admin_formcontainer_output_row", "mytwconnect_settings_replacer");
	}
	
}

function mytwconnect_global()
{
	
	global $mybb, $templatelist;
	
	if ($templatelist) {
		$templatelist = explode(',', $templatelist);
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
	
}

function mytwconnect_logout()
{
	if (!session_id()) {
		session_start();
	}
	
	if ($_SESSION['access_token']) {
		unset($_SESSION['access_token']);
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
	global $mybb, $lang, $inlinesuccess;
	
	// Load API in certain areas
	if (in_array($mybb->input['action'], array('twlink','do_twlink')) or $_SESSION['twlogin'] or ($mybb->input['action'] == 'mytwconnect' and $mybb->request_method == 'post')) {
		
		require_once MYBB_ROOT . "inc/plugins/MyTwitterConnect/class_twitter.php";
		$TwitterConnect = new MyTwitter();
		
	}
	
	$settingsToCheck = array(
		'twavatar',
		'twbio',
		'twlocation'
	);
	
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
		
		$TwitterConnect->redirect('usercp.php?action=mytwconnect', '', $lang->mytwconnect_success_linked);
		
	}
	
	// Settings page
	if ($mybb->input['action'] == 'mytwconnect') {
	
		global $db, $lang, $theme, $templates, $headerinclude, $header, $footer, $plugins, $usercpnav;
		
		add_breadcrumb($lang->nav_usercp, 'usercp.php');
		add_breadcrumb($lang->mytwconnect_page_title, 'usercp.php?action=mytwconnect');
				
		// The user is changing his settings
		if ($mybb->request_method == 'post' or $_SESSION['twlogin']) {
						
			if($mybb->request_method == 'post') {
				verify_post_check($mybb->input['my_post_key']);
			}
			
			// He's unlinking his account
			if ($mybb->input['unlink']) {
			
				$TwitterConnect->unlink_user();
				redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_accunlinked, $lang->mytwconnect_success_accunlinked_title);
				
			}
			// He's updating his settings
			else {
				
				$settings = array();
				
				foreach ($settingsToCheck as $setting) {
					
					$settings[$setting] = 0;
					
					if ($mybb->input[$setting] == 1) {
						$settings[$setting] = 1;
					}
					
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
				
				if ($db->update_query('users', $settings, 'uid = ' . (int) $mybb->user['uid'])) {
					
					unset($_SESSION['twlogin']);
					
					$newUser = array_merge($mybb->user, $settings);
					$TwitterConnect->sync($newUser, $user);
					
					redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_settingsupdated, $lang->mytwconnect_success_settingsupdated_title);
					
				}
			}
		}
		
		$options = '';
		if ($mybb->user['mytw_uid']) {
		
			// Checking if admins and users want to sync that stuff
			foreach ($settingsToCheck as $setting) {
				
				$tempKey = 'mytwconnect_' . $setting;
				
				if (!$mybb->settings[$tempKey]) {
					continue;
				}
				
				$userSettings[$setting] = 0;
				
				if ($mybb->user[$setting]) {
					$userSettings[$setting] = 1;
				}
				
			}
			
			$text = $lang->mytwconnect_settings_whattosync;
			$unlink = "<input type=\"submit\" class=\"button\" name=\"unlink\" value=\"{$lang->mytwconnect_settings_unlink}\" />";
			
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
				$text = $lang->mytwconnect_settings_connected;
			}
			
		}
		else {
			
			$text = $lang->mytwconnect_settings_linkaccount;
			eval("\$options = \"" . $templates->get('mytwconnect_usercp_settings_linkprofile') . "\";");
			
		}
		
		eval("\$content = \"" . $templates->get('mytwconnect_usercp_settings') . "\";");
		output_page($content);
	}
}

function mytwconnect_update()
{
	global $mybb, $db, $cache, $lang;
	
	require_once MYBB_ROOT . "inc/plugins/MyTwitterConnect/class_update.php";
}

/**
 * Displays peekers in settings.
 * 
 * @return boolean True if successful, false either.
 **/

function mytwconnect_settings_footer()
{
	global $mybb, $db;
	if ($mybb->input["action"] == "change" and $mybb->request_method != "post") {
		$gid = mytwconnect_settings_gid();
		if ($mybb->input["gid"] == $gid or !$mybb->input['gid']) {
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

/**
 * Gets the gid of MyTwitter Connect settings group.
 * 
 * @return mixed The gid.
 **/

function mytwconnect_settings_gid()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "gid", "name = 'mytwconnect'", array(
		"limit" => 1
	));
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

$GLOBALS['replace_custom_fields'] = array('twlocationfield', 'twbiofield');

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
        
	$query = $db->simple_select('profilefields', 'name, fid');
	
	$profilefields = array('' => '');
	
	while ($field = $db->fetch_array($query)) {
		$profilefields[$field['fid']] = $field['name'];
	}
	$db->free_result($query);
	
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
			
		// Replace the textarea with a cool selectbox
		$args['content'] = $form->generate_group_select($tempKey."_select", array($mybb->settings[$tempKey]));
			
	}
}

function mytwconnect_admin_config_menu($sub_menu)
{
        global $lang;

        $lang->load("mytwconnect");

        $sub_menu[] = array("id" => "mytwconnect", "title" => $lang->mytwconnect, "link" => "index.php?module=config-mytwconnect");

        return $sub_menu;
}

function mytwconnect_admin_config_action_handler($actions)
{
        $actions['mytwconnect'] = array(
                "active" => "mytwconnect",
                "file" => "mytwconnect.php"
        );

        return $actions;
}