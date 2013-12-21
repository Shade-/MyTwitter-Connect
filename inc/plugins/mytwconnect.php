<?php
/**
 * MyTwitter Connect
 * 
 * Integrates MyBB with Twitter, featuring login and registration.
 *
 * @package MyTwitter Connect
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.0.2
 */

if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!defined("PLUGINLIBRARY")) {
	define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

global $mybb, $settings;

define("CONSUMER_KEY", $mybb->settings['mytwconnect_conskey']);
define("CONSUMER_SECRET", $mybb->settings['mytwconnect_conssecret']);
define("OAUTH_CALLBACK", $mybb->settings['bburl']."/mytwconnect.php?action=authenticate");

function mytwconnect_info()
{
	return array(
		'name' => 'MyTwitter Connect',
		'description' => 'Integrates MyBB with Twitter, featuring login and registration.',
		'website' => 'https://github.com/Shade-/MyTwitter-Connect',
		'author' => 'Shade',
		'authorsite' => 'http://www.idevicelab.net/forum',
		'version' => '1.0.2',
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
		// location
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
		// bio
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
		)
	));
	
	// insert our Twitter columns into the database
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users ADD (
		`twavatar` int(1) NOT NULL DEFAULT 1,
		`twbio` int(1) NOT NULL DEFAULT 1,
		`twlocation` int(1) NOT NULL DEFAULT 1,
		`mytw_uid` bigint(50) NOT NULL DEFAULT 0
		)");
	
	// Euantor's templating system	   
	$dir = new DirectoryIterator(dirname(__FILE__) . '/MyTwitterConnect/templates');
	$templates = array();
	foreach ($dir as $file) {
		if (!$file->isDot() AND !$file->isDir() AND pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html') {
			$templates[$file->getBasename('.html')] = file_get_contents($file->getPathName());
		}
	}
	
	$PL->templates('mytwconnect', 'MyTwitter Connect', $templates);
	
	// create cache
	$info = mytwconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	$shadePlugins[$info['name']] = array(
		'title' => $info['name'],
		'version' => $info['version']
	);
	$cache->update('shade_plugins', $shadePlugins);
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$lang->welcome_register}</a>') . '#i', '{$lang->welcome_register}</a> &mdash; <a href="{$mybb->settings[\'bburl\']}/mytwconnect.php?action=twlogin">{$lang->mytwconnect_login}</a>');
	
	rebuild_settings();
	
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
	
	$PL->settings_delete('mytwconnect');
	
	// delete our Twitter columns
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP `twavatar`, DROP `twbio`, DROP `twlocation`, DROP `mytw_uid`");
	
	$info = mytwconnect_info();
	// delete the plugin from cache
	$shadePlugins = $cache->read('shade_plugins');
	unset($shadePlugins[$info['name']]);
	$cache->update('shade_plugins', $shadePlugins);
	
	$PL->templates_delete('mytwconnect');
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('&mdash; <a href="{$mybb->settings[\'bburl\']}/mytwconnect.php?action=twlogin">{$lang->mytwconnect_login}</a>') . '#i', '');
	
	// rebuild settings
	rebuild_settings();
}

if ($settings['mytwconnect_enabled']) {
	$plugins->add_hook('global_start', 'mytwconnect_global');
	$plugins->add_hook('usercp_menu', 'mytwconnect_usercp_menu', 40);
	$plugins->add_hook('usercp_start', 'mytwconnect_usercp');
	$plugins->add_hook("admin_page_output_footer", "mytwconnect_settings_footer");
	$plugins->add_hook("fetch_wol_activity_end", "mytwconnect_fetch_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "mytwconnect_build_wol_location");
}

function mytwconnect_global()
{
	
	global $mybb, $lang, $templatelist;
	
	if (!$lang->mytwconnect) {
		$lang->load("mytwconnect");
	}
	
	if (isset($templatelist)) {
		$templatelist .= ',';
	}
	
	if (THIS_SCRIPT == "mytwconnect.php") {
		$templatelist .= 'mytwconnect_register';
	}
	
	if (THIS_SCRIPT == "usercp.php") {
		$templatelist .= 'mytwconnect_usercp_menu';
	}
	
	if (THIS_SCRIPT == "usercp.php" AND $mybb->input['action'] == "mytwconnect") {
		$templatelist .= ',mytwconnect_usercp_settings,mytwconnect_usercp_settings_linkprofile,mytwconnect_usercp_showsettings,mytwconnect_usercp_settings_setting';
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
	
	if (!$lang->mytwconnect) {
		$lang->load('mytwconnect');
	}
	
	if(!session_id()) {
		session_start();
	}
	
	if ($mybb->input['action'] == "do_twlink" OR ($mybb->input['action'] == "mytwconnect" AND $mybb->request_method == "post")) {
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
			
		$access_token = $_SESSION['access_token'];
		
		// if do_twlink, we are certainly sure that the user has an active access token, so just mytwconnect needs it
		if($mybb->input['action'] == "mytwconnect") {
			// if it's empty, we have got to log the user in, but mantaining the settings update process active
			if(empty($access_token)) {
				
				$settings = array();
				$settingsToCheck = array(
					"twavatar",
					"twbio",
					"twlocation"
				);
				
				// having some fun with variable variables
				foreach ($settingsToCheck as $setting) {
					if ($mybb->input[$setting] == 1) {
						$settings[$setting] = 1;
					} else {
						$settings[$setting] = 0;
					}
					// building the extra data passed to the redirect url of the login function
					$loginUrlExtra .= "&{$setting}=" . $settings[$setting];
				}
				
				$url = "/usercp.php?action=mytwconnect".$loginUrlExtra;
				// used for maintaining the settings update process active
				$_SESSION['tw_isloggingin'] = true;
				// log the user in
				mytwconnect_login($url);
			}
		}
		
		// Create our application instance
		$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		/* END API LOAD */
	}
	
	// linking accounts
	if ($mybb->input['action'] == "twlink") {
		$loginUrl = "/usercp.php?action=do_twlink";
		mytwconnect_login($loginUrl);
	}
	
	// truly link accounts
	if ($mybb->input['action'] == "do_twlink") {
		// get the user
		$user = (array) $Twitter->get('account/verify_credentials');
		
		// if any kind of error occur, let the user be informed about it (Rate limit exceeded probably)
		if($user['errors']) {
			error($lang->sprintf($lang->mytwconnect_error_report, $user['errors']['message']));
		}
		
		$userdata['id'] = $user['id'];
		// true means only link
		mytwconnect_run($userdata, true);
		redirect("usercp.php?action=mytwconnect", $lang->mytwconnect_success_linked);
	}
	
	// settings page
	if ($mybb->input['action'] == 'mytwconnect') {
		global $db, $lang, $theme, $templates, $headerinclude, $header, $footer, $plugins, $usercpnav;
		
		add_breadcrumb($lang->nav_usercp, 'usercp.php');
		add_breadcrumb($lang->mytwconnect_page_title, 'usercp.php?action=mytwconnect');
				
		// 2 situations provided: the user is logged in with Twitter, two user isn't logged in with Twitter but it's loggin in.
		if ($mybb->request_method == 'post' OR $_SESSION['tw_isloggingin']) {
						
			if($mybb->request_method == 'post') {
				verify_post_check($mybb->input['my_post_key']);
			}
			
			// unlinking his TW account... what a pity! :(
			if ($mybb->input['unlink']) {
				mytwconnect_unlink();
				redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_accunlinked, $lang->mytwconnect_success_accunlinked_title);
			} else {			
				$settings = array();
				$settingsToCheck = array(
					"twavatar",
					"twbio",
					"twlocation"
				);
				
				// having some fun with variable variables
				foreach ($settingsToCheck as $setting) {
					if ($mybb->input[$setting] == 1) {
						$settings[$setting] = 1;
					} else {
						$settings[$setting] = 0;
					}
				}
				
				if ($db->update_query('users', $settings, 'uid = ' . (int) $mybb->user['uid'])) {
					// update on-the-fly that array of data dude!
					$newUser = array_merge($mybb->user, $settings);
					// oh yeah, let's sync!
					mytwconnect_sync($newUser);
					
					// unset tw_isloggingin, we don't need it anymore
					if(!session_id()) {
						session_start();
					}
					unset($_SESSION['tw_isloggingin']);
					redirect('usercp.php?action=mytwconnect', $lang->mytwconnect_success_settingsupdated, $lang->mytwconnect_success_settingsupdated_title);
				}
			}
		}
		
		$query = $db->simple_select("users", "mytw_uid", "uid = " . $mybb->user['uid']);
		$alreadyThere = $db->fetch_field($query, "mytw_uid");
		
		$options = "";
		
		if ($alreadyThere) {
			
			$text = $lang->mytwconnect_settings_whattosync;
			$unlink = "<input type=\"submit\" class=\"button\" name=\"unlink\" value=\"{$lang->mytwconnect_settings_unlink}\" />";
			// checking if we want to sync that stuff
			$settingsToCheck = array(
				"twbio",
				"twlocation"
			);
			
			foreach ($settingsToCheck as $setting) {
				$tempKey = 'mytwconnect_' . $setting;
				if ($mybb->settings[$tempKey]) {
					$settingsToSelect[] = $setting;
				}
			}
			
			// join pieces into a string
			if (!empty($settingsToSelect)) {
				$settingsToSelect = "," . implode(",", $settingsToSelect);
			}
			
			$query = $db->simple_select("users", "twavatar" . $settingsToSelect, "uid = " . $mybb->user['uid']);
			$userSettings = $db->fetch_array($query);
			$settings = "";
			foreach ($userSettings as $setting => $value) {
				// variable variables. Yay!
				$tempKey = 'mytwconnect_settings_' . $setting;
				if ($value == 1) {
					$checked = " checked=\"checked\"";
				} else {
					$checked = "";
				}
				$label = $lang->$tempKey;
				$altbg = alt_trow();
				eval("\$options .= \"" . $templates->get('mytwconnect_usercp_settings_setting') . "\";");
			}
		} else {
			$text = $lang->mytwconnect_settings_linkaccount;
			eval("\$options = \"" . $templates->get('mytwconnect_usercp_settings_linkprofile') . "\";");
		}
		
		eval("\$content = \"" . $templates->get('mytwconnect_usercp_settings') . "\";");
		output_page($content);
	}
}

/**
 * Main function which logins or registers any kind of Twitter user, provided a valid ID.
 * 
 * @param array The user data containing all the information which are parsed and inserted into the database.
 * @param boolean (optional) Whether to simply link the profile to TW or not. Default to false.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mytwconnect_run($userdata, $justlink = false)
{
	
	global $mybb, $db, $session, $lang;
	
	$user = (array) $userdata;
	
	// See if this user is already present in our database
	if (!$justlink) {
		$query = $db->simple_select("users", "*", "mytw_uid = {$user['id']}");
		$registered = $db->fetch_array($query);
	}
	
	if($registered OR $justlink) {
		// add the user to the twitter group, if any is provided
		if($mybb->settings['mytwconnect_usergroup']) {
			$groups = explode(",", $mybb->user['additionalgroups']);
			$toadd = (int) $mybb->settings['mytwconnect_usergroup'];
			if(!in_array($toadd, $groups)) {
				$groups[] = $toadd;
				$param = array(
					"additionalgroups" => implode(",", array_filter($groups))
				);
				$db->update_query("users", $param, "uid = {$mybb->user['uid']}");
			}
		}
	}
	
	// this user hasn't a linked-to-Twitter account yet
	if (!$registered OR $justlink) {
		
		if ($justlink) {
			$db->update_query("users", array(
				"mytw_uid" => $user['id']
			), "uid = {$mybb->user['uid']}");
			return;
		}
		// this user isn't registered with us, so we have to register it
		// if we want to let the user choose some infos, then pass the ball to our custom page			
		if (!$mybb->settings['mytwconnect_fastregistration']) {
			header("Location: mytwconnect.php?action=twregister");
			return;
		}
		// register the user
		$newUserData = mytwconnect_register($user);
		// oops, errors
		if ($newUserData['error']) {
			return $newUserData;
		} else {
			// no errors, link the account and log the user in
			$db->update_query("users", array(
				"mytw_uid" => $user['id']
			), "uid = '{$newUserData['uid']}'");
			// enable all options and sync
			$newUserDataSettings = array(
				"twavatar" => 1,
				"twbio" => 1,
				"twlocation" => 1
			);
			$newUserData = array_merge($newUserData, $newUserDataSettings);
			mytwconnect_sync($newUserData, $user);
			// after registration we have to log this new user in
			my_setcookie("mybbuser", $newUserData['uid'] . "_" . $newUserData['loginkey'], null, true);
			
			if ($_SERVER['HTTP_REFERER'] AND strpos($_SERVER['HTTP_REFERER'], "action=twlogin") === false AND strpos($_SERVER['HTTP_REFERER'], "action=do_twlogin") === false) {
				$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
			} else {
				$redirect_url = "index.php";
			}
			
			redirect($redirect_url, $lang->mytwconnect_redirect_registered, $lang->sprintf($lang->mytwconnect_redirect_title, $newUserData['username']));
		}
	}
	// this user has already a linked-to-Twitter account, just log him in and update session
	else {
		$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' AND sid != '" . $session->sid . "'");
		$newsession = array(
			"uid" => $registered['uid']
		);
		$db->update_query("sessions", $newsession, "sid='" . $session->sid . "'");
		
		// eventually sync data
		mytwconnect_sync($registered, $user);
		
		// finally log the user in
		my_setcookie("mybbuser", $registered['uid'] . "_" . $registered['loginkey'], null, true);
		my_setcookie("sid", $session->sid, -1, true);
		// redirect the user to where he came from
		if ($_SERVER['HTTP_REFERER'] AND strpos($_SERVER['HTTP_REFERER'], "action=twlogin") === false) {
			$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
		} else {
			$redirect_url = "index.php";
		}
		redirect($redirect_url, $lang->mytwconnect_redirect_loggedin, $lang->sprintf($lang->mytwconnect_redirect_title, $registered['username']));
	}
	
}

/**
 * Unlink any Twitter account from the corresponding MyBB account.
 * 
 * @param int The UID of the user you want to unlink.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mytwconnect_unlink()
{
	
	global $db, $mybb;
	
	$reset = array(
		"mytw_uid" => 0
	);
	
	// unlink the account
	$db->update_query("users", $reset, "uid = {$mybb->user['uid']}");
	// remove the additional group
	
	$groups = explode(",", $mybb->user['additionalgroups']);
	// we should not rely on the admin's input
	$todelete = (int) $mybb->settings['mytwconnect_usergroup'];
	if(in_array($todelete, $groups)) {
		$groups = array_flip($groups);
		unset($groups[$todelete]);
		$groups = array_filter(array_flip($groups));
		$reset = array(
			"additionalgroups" => implode(",", $groups)
		);
		$db->update_query("users", $reset, "uid = {$mybb->user['uid']}");
	}
	
}

/**
 * Logins any Twitter user, prompting a permission page and redirecting to the URL they came from.
 * 
 * @param mixed The URL to redirect at the end of the process. Relative URL.
 * @return redirect Redirects with an header() call to the specified URL.
 **/

function mytwconnect_login($url)
{
	global $mybb, $lang;
	
	// include our API
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
	
	if(!session_id()) {
		session_start();
	}
	
	$access_token = $_SESSION['access_token'];
	
	// access token found - the user have already authenticated our app, but we aren't sure it's still valid
	if(!empty($access_token)) {
		// build our Twitter instance
		$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		// check for infos
		$user = (array) $Twitter->get('account/verify_credentials');
		// errors found, token was invalid (most probably the user has denied permissions)
		if($user['errors']) {
			unset($access_token);
		} else {
			// skip slow auth if it's valid
			header("Location: ".$mybb->settings['bburl'].$url);
			exit;
		}
	} else {
		// the user isn't authenticated yet, build the app instance
		$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	}
	
	// get temporary credentials
	$request_token = $Twitter->getRequestToken(OAUTH_CALLBACK);
	
	// save some defaults into the current user's session
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	$_SESSION['redirecturl'] = $mybb->settings['bburl'].$url;
	
	switch ($Twitter->http_code) {
  		case 200:
    		// Build authorize URL and redirect user to Twitter.
    		$url = $Twitter->getAuthorizeURL($token);
    		header('Location: ' . $url);
    		break;
  		default:
    		// Show notification if something went wrong
   			error($lang->mytwconnect_error_cantconnect);
			break;
	}
}

/**
 * Registers an user, provided an array with valid data.
 * 
 * @param array The data of the user to register. name key must be present.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mytwconnect_register($user = array())
{
	
	global $mybb, $session, $plugins, $lang;
	
	require_once MYBB_ROOT . "inc/datahandlers/user.php";
	$userhandler = new UserDataHandler("insert");
	
	$plength = !empty($mybb->settings['minpasswordlength']) ? $mybb->settings['minpasswordlength'] : 8;
	
	$password = random_str($plength);
	// if the email isn't set (coming from One-click registration), create one
	if(empty($user['email'])) {
		$email = $user['id']."@".strtolower($mybb->settings['bbname']).".com";
		$email = preg_replace('/\s+/', '', $email);
	} else {
		$email = $user['email'];
	}
	
	$newUser = array(
		"username" => $user['name'],
		"password" => $password,
		"password2" => $password,
		"email" => $email,
		"email2" => $email,
		"usergroup" => $mybb->settings['mytwconnect_usergroup'],
		"displaygroup" => $mybb->settings['mytwconnect_usergroup'],
		"regip" => $session->ipaddress,
		"longregip" => my_ip2long($session->ipaddress)
	);
	
	/* Registration might fail for custom profile fields required at registration... workaround = IN_ADMINCP defined.
	 Placed straight before the registration process to avoid conflicts with third party plugins messying around with
	 templates (I'm looking at you, PHPTPL) */
	define("IN_ADMINCP", 1);
	
	$userhandler->set_data($newUser);
	if ($userhandler->validate_user()) {
		$newUserData = $userhandler->insert_user();
		
		if ($mybb->settings['mytwconnect_passwordpm']) {
			require_once MYBB_ROOT . "inc/datahandlers/pm.php";
			$pmhandler = new PMDataHandler();
			$pmhandler->admin_override = true;
			
			// just make sure the admins didn't make something wrong in configuration
			if (empty($mybb->settings['mytwconnect_passwordpm_fromid']) OR !user_exists($mybb->settings['mytwconnect_passwordpm_fromid'])) {
				$fromid = 0;
			} else {
				$fromid = (int) $mybb->settings['mytwconnect_passwordpm_fromid'];
			}
			
			$message = $mybb->settings['mytwconnect_passwordpm_message'];
			$subject = $mybb->settings['mytwconnect_passwordpm_subject'];
			
			$thingsToReplace = array(
				"{user}" => $newUserData['username'],
				"{password}" => $password
			);
			
			// replace what needs to be replaced
			foreach ($thingsToReplace as $find => $replace) {
				$message = str_replace($find, $replace, $message);
			}
			
			$pm = array(
				"subject" => $subject,
				"message" => $message,
				"fromid" => $fromid,
				"toid" => array(
					$newUserData['uid']
				)
			);
			
			// some defaults :)
			$pm['options'] = array(
				"signature" => 1,
				"disablesmilies" => 0,
				"savecopy" => 0,
				"readreceipt" => 0
			);
			
			$pmhandler->set_data($pm);
			
			// Now let the pm handler do all the hard work
			if ($pmhandler->validate_pm()) {
				$pmhandler->insert_pm();
			} else {
				error($lang->sprintf($lang->mytwconnect_error_report, $pmhandler->get_errors()));
			}
		}
		// return our newly registered user data
		return $newUserData;
	} else {
		$errors['error'] = true;
		$errors['data'] = $userhandler->get_friendly_errors();
		return $errors;
	}
}

/**
 * Syncronizes any Twitter account with any MyBB account, importing all the infos.
 * 
 * @param array The existing user data. UID is required.
 * @param array The Twitter user data to sync.
 * @param int Whether to bypass any existing user settings or not. Disabled by default.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mytwconnect_sync($user, $twdata = array(), $bypass = false)
{
	
	global $mybb, $db, $session, $lang, $plugins;
	
	if(!session_id()) {
		session_start();
	}
	
	$userData = array();
	$userfieldsData = array();
	
	$locationid = "fid" . $mybb->settings['mytwconnect_twlocationfield'];
	$bioid = "fid" . $mybb->settings['mytwconnect_twbiofield'];
	
	// ouch! empty Twitter data, we need to help this poor guy!
	if (empty($twdata)) {
		
		// include our API
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
		
		// take the access token out of the session, just to beautify it a bit
		$access_token = $_SESSION['access_token'];
			
		// we have no auth here
		if(empty($access_token) || empty($access_token['oauth_token']) || empty($access_token['oauth_token_secret'])) {
			session_destroy();
			error($lang->mytwconnect_error_noauth);
		}
		
		$Twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$twdata = (array) $Twitter->get('account/verify_credentials');
	}
	
	$query = $db->simple_select("userfields", "*", "ufid = {$user['uid']}");
	$userfields = $db->fetch_array($query);
	if (empty($userfields)) {
		$userfieldsData['ufid'] = $user['uid'];
	}
	
	// Twitter id, if empty we need to sync it
	if (empty($user["mytw_uid"])) {
		$userData["mytw_uid"] = $twdata["id"];
	}
	
	// begin our checkes comparing mybb with Twitter stuff, syntax:
	// (USER SETTINGS AND !empty(Twitter VALUE)) OR $bypass (eventually ADMIN SETTINGS)
	
	// avatar
	if (($user['twavatar'] AND !empty($twdata['id'])) OR $bypass) {
		$userData["avatar"] = $twdata['profile_image_url_https'];
		// small to big avatar
		$userData["avatar"] = str_replace('_normal.', '.', $userData['avatar']);
		$userData["avatartype"] = "remote";
		
		// Copy the avatar to the local server (work around remote URL access disabled for getimagesize)
		$file = fetch_remote_file($userData["avatar"]);
		$tmp_name = $mybb->settings['avataruploadpath'] . "/remote_" . md5(random_str());
		$fp = @fopen($tmp_name, "wb");
		if ($fp) {
			fwrite($fp, $file);
			fclose($fp);
			list($width, $height, $type) = @getimagesize($tmp_name);
			@unlink($tmp_name);
			if (!$type) {
				$avatar_error = true;
			}
		}
		
		list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['maxavatardims']));
		
		if (empty($avatar_error)) {
			if ($width AND $height AND $mybb->settings['maxavatardims'] != "") {
				if (($maxwidth AND $width > $maxwidth) OR ($maxheight AND $height > $maxheight)) {
					$avatardims = $maxheight . "|" . $maxwidth;
				}
			}
			if ($width > 0 AND $height > 0 AND !$avatardims) {
				$avatardims = $width . "|" . $height;
			}
			$userData["avatardimensions"] = $avatardims;
		} else {
			$userData["avatardimensions"] = $maxheight . "|" . $maxwidth;
		}
	}
	// cover, if Profile Picture plugin is installed
	if ((($user['twavatar'] AND !empty($twdata['cover']['source'])) OR $bypass) AND $db->field_exists("profilepic", "users")) {
		$cover = $twdata['cover']['source'];
		$userData["profilepic"] = str_replace('/s720x720/', '/p851x315/', $cover);
		$userData["profilepictype"] = "remote";
		if ($mybb->usergroup['profilepicmaxdimensions']) {
			list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->usergroup['profilepicmaxdimensions']));
			$userData["profilepicdimensions"] = $maxwidth . "|" . $maxheight;
		} else {
			$userData["profilepicdimensions"] = "851|315";
		}
	}
	// bio
	if ((($user['twbio'] AND !empty($twdata['description'])) OR $bypass) AND $mybb->settings['mytwconnect_twbio']) {
		if ($db->field_exists($bioid, "userfields")) {
			$userfieldsData[$bioid] = $db->escape_string(htmlspecialchars_decode(my_substr($twdata['description'], 0, 400, true)));
		}
	}
	// location
	if ((($user['twlocation'] AND !empty($twdata['location'])) OR $bypass) AND $mybb->settings['mytwconnect_twlocation']) {
		if ($db->field_exists($locationid, "userfields")) {
			$userfieldsData[$locationid] = $db->escape_string($twdata['location']);
		}
	}
	
	$plugins->run_hooks("mytwconnect_sync_end", $userData);
	
	// let's do it!
	if (!empty($userData) AND !empty($user['uid'])) {
		$db->update_query("users", $userData, "uid = {$user['uid']}");
	}
	// make sure we can do it
	if (!empty($userfieldsData) AND !empty($user['uid'])) {
		if (isset($userfieldsData['ufid'])) {
			$db->insert_query("userfields", $userfieldsData);
		} else {
			$db->update_query("userfields", $userfieldsData, "ufid = {$user['uid']}");
		}
	}
	
	return true;
	
}

/**
 * Displays peekers in settings. Technique ripped from MySupport, please don't blame on me :(
 * 
 * @return boolean True if successful, false either.
 **/

function mytwconnect_settings_footer()
{
	global $mybb, $db;
	if ($mybb->input["action"] == "change" && $mybb->request_method != "post") {
		$gid = mytwconnect_settings_gid();
		if ($mybb->input["gid"] == $gid || !$mybb->input['gid']) {
			echo '<script type="text/javascript">
	Event.observe(window, "load", function() {
	loadMytwConnectPeekers();
});
function loadMytwConnectPeekers()
{
	new Peeker($$(".setting_mytwconnect_passwordpm"), $("row_setting_mytwconnect_passwordpm_subject"), /1/, true);
	new Peeker($$(".setting_mytwconnect_passwordpm"), $("row_setting_mytwconnect_passwordpm_message"), /1/, true);
	new Peeker($$(".setting_mytwconnect_passwordpm"), $("row_setting_mytwconnect_passwordpm_fromid"), /1/, true);
	new Peeker($$(".setting_mytwconnect_twbio"), $("row_setting_mytwconnect_twbiofield"), /1/, true);
	new Peeker($$(".setting_mytwconnect_twlocation"), $("row_setting_mytwconnect_twlocationfield"), /1/, true);
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
        case "twlogin":
		case "do_twlogin":
            $plugin_array['location_name'] = $lang->mytwconnect_viewing_loggingin;
			break;
		case "twregister":
            $plugin_array['location_name'] = $lang->mytwconnect_viewing_registering;
            break;
    }
    return $plugin_array;
} 

/**
 * Debugs any type of data.
 * 
 * @param mixed The data to debug.
 * @return mixed The debugged data.
 **/

function mytwconnect_debug($data)
{
	echo "<pre>";
	echo var_dump($data);
	echo "</pre>";
	exit;
}

/********************************************************************************************************
 *
 * ON-THE-FLY UPGRADING SYSTEM: used to upgrade from any older version to any newer version of the plugin
 *
 ********************************************************************************************************/

if ($mybb->settings['mytwconnect_enabled']) {
	$plugins->add_hook("admin_page_output_header", "mytwconnect_upgrader");
}

function mytwconnect_upgrader()
{
	
	global $db, $mybb, $cache, $lang;
	
	if (!$lang->mytwconnect) {
		$lang->load("mytwconnect");
	}
	
	// let's see what version of MyTwitter Connect is currently installed on this board
	$info = mytwconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	$oldversion = $shadePlugins[$info['name']]['version'];
	$currentversion = $info['version'];
	
	// you need to update buddy!
	if (version_compare($oldversion, $currentversion, "<")) {
		flash_message($lang->mytwconnect_error_needtoupdate, "error");
	}
	
	// you are updating, that's nice!
	if ($mybb->input['upgrade'] == "mytwconnect") {
		// but let's check if you should upgrade first
		if (version_compare($oldversion, $currentversion, "<")) {
			// yeah you should
			// to 1.0.2
			if (version_compare($oldversion, "1.0.2", "<")) {
				require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
				find_replace_templatesets('myfbconnect_usercp_settings', '#' . preg_quote('<input type="submit" value="{$lang->mytwconnect_settings_save}" />') . '#i', '<input type="submit" class=\"button\" value="{$lang->mytwconnect_settings_save}" />{$unlink}');
			}
			// update version n° and return a success message
			$shadePlugins[$info['name']] = array(
				'title' => $info['name'],
				'version' => $currentversion
			);
			$cache->update('shade_plugins', $shadePlugins);
			flash_message($lang->sprintf($lang->mytwconnect_success_updated, $oldversion, $currentversion), "success");
			admin_redirect($_SERVER['HTTP_REFERER']);
		} else {
			// you shouldn't
			flash_message($lang->mytwconnect_error_nothingtodohere, "error");
			admin_redirect("index.php");
		}
	}
}