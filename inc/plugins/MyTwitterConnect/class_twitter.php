<?php

/**
 * A bridge between MyBB with Twitter, featuring login, registration and more.
 *
 * @package Main API class
 * @version 2.0
 */

class MyTwitter
{
	// The fallback URL where Twitter redirects users
	private $fallback = '';
	
	// The consumer key populated upon initialization
	private $key = '';
	
	// The consumer secret populated upon initialization
	private $secret = '';
	
	// md5 sumcheck of concatenated $key and $secret to enhance security across multiple boards (prevents logging in other boards provided access within another)
	private $security_key = '';
	
	// A boolean simple token to know when an user is authenticated or not. Can be used by third party plugins.
	public $authenticated = '';
	
	// The $twitter object populated upon initialization
	public $twitter;
	
	/**
	 * Contructor
	 */
	public function __construct()
	{
		global $mybb, $lang;
		
		$this->key = $mybb->settings['mytwconnect_conskey'];
		$this->secret = $mybb->settings['mytwconnect_conssecret'];
		
		if (!session_id()) {
			session_start();
		}
		
		if (!$lang->mytwconnect) {
			$lang->load('mytwconnect');
		}
		
		$this->security_key = md5($this->key.$this->secret);
		
		$this->load_api();
		$this->set_fallback();
	}
	
	/**
	 * Loads the necessary API classes
	 */
	private function load_api()
	{
		global $mybb, $lang;
		
		if ($this->twitter) {
			return false;
		}
		
		if (!$this->key or !$this->secret) {
			error($lang->mytwconnect_error_noconfigfound);
		}
		
		try {
			require_once MYBB_ROOT . "mytwconnect/src/twitteroauth.php";
		}
		catch (Exception $e) {
			error($lang->sprintf($lang->mytwconnect_error_report, $e->getMessage()));
		}
		
		// Create our application instance
		$this->load_object();
		
		return true;
	}
	
	/**
	 * Loads or reloads the main Twitter object. Placed here to modify it on the fly when authenticating
	 */
	public function load_object($type = '')
	{
		global $mybb;
		
		if ($type == 'authenticated' or $_SESSION[$this->security_key]['access_token']['oauth_token']) {
		
			$this->twitter = new TwitterOAuth($this->key, $this->secret, $_SESSION[$this->security_key]['access_token']['oauth_token'], $_SESSION[$this->security_key]['access_token']['oauth_token_secret']);
			$this->authenticated = true;
			
		}
		else if ($type == 'temporary' or $_SESSION[$this->security_key]['temporary']['oauth_token']) {
			$this->twitter = new TwitterOAuth($this->key, $this->secret, $_SESSION[$this->security_key]['temporary']['oauth_token'], $_SESSION[$this->security_key]['temporary']['oauth_token_secret']);			
		}
		else {
			$this->twitter = new TwitterOAuth($this->key, $this->secret);
		}
		
		return true;
	}
	
	/**
	 * Sets the fallback URL where the app should redirect to when finished authenticating
	 */
	public function set_fallback($url = '')
	{
		global $mybb;
		
		if (!$url) {
			$this->fallback = $mybb->settings['bburl'] . "/mytwconnect.php?action=do_login";
		}
		else {
			$this->fallback = $mybb->settings['bburl'] . "/" . $url;
		}
		
		return true;
	}
	
	/**
	 * Starts the login process, creating the authorize URL
	 */
	public function authenticate()
	{
		global $mybb, $lang;
		
		if ($this->authenticated) {
			
			header('Location: ' . $this->fallback);
			return true;
			
		}
		
		// Get a temporary pair of tokens
		$token = $this->twitter->getRequestToken($this->fallback);
		
		$_SESSION[$this->security_key]['temporary'] = array(
			'oauth_token' => $token['oauth_token'],
			'oauth_token_secret' => $token['oauth_token_secret']
		);
		
		// Something went wrong
		if ($this->twitter->http_code != 200) {
	   		error($lang->mytwconnect_error_cantconnect);
	   	}
	   	
	   	$_SESSION['orig_url'] = basename($_SERVER['HTTP_REFERER']);
	   	
	   	header('Location: ' . $this->twitter->getAuthorizeURL($token['oauth_token']));
		
		return true;
	}
	
	/**
	 * Checks the incoming request and exchanges temporary tokens with permanent auth tokens
	 */
	public function obtain_tokens()
	{
		global $mybb, $lang;
		
		if ($this->authenticated) {
			return false;
		}
		
		// Mitigate CSRF after authentication
		if ($_REQUEST['oauth_token'] and $_SESSION[$this->security_key]['temporary']['oauth_token'] !== $_REQUEST['oauth_token']) {
			error($lang->mytwconnect_error_noauth);
		}
		
		// Load the temporary Twitter object
		$this->load_object('temporary');
		
		$_SESSION[$this->security_key]['access_token'] = $this->twitter->getAccessToken($_REQUEST['oauth_verifier']);
		
		unset($_SESSION[$this->security_key]['temporary']);
		
		if ($this->twitter->http_code != 200) {
			error($lang->mytwconnect_error_noauth);
		}
		
		// Reload the Twitter object. The user should be authenticated at this point, and we don't need to redirect another time.
		$this->load_object('authenticated');
		
		return true;
	}
	
	/**
	 * Attempts to get the authenticated user's data
	 */
	public function get_user()
	{
		global $lang;
		
		$user = (array) $this->twitter->get('account/verify_credentials');
		
		// 200: ok
		if ($this->twitter->http_code == 200) {
			return $user;
		}
		// 429: rate limit exceeded
		else if ($this->twitter->http_code == 429) {
			error("Rate limit exceeded. Please retry later.");
		}
		
		// At this point we must unset the tokens, they are not useful anymore
		unset($_SESSION[$this->security_key]['access_token'], $this->authenticated);
		
		$this->load_object();
		
		return false;
	}
	
	/**
	 * Tweets something in the user's timeline. Assumes authentication.
	 */
	private function tweet($message)
	{
		global $mybb;
		
		if (!$message) {
			return false;
		}
		
		$thingsToReplace = array(
			"{bbname}" => $mybb->settings['bbname'],
			"{bburl}" => $mybb->settings['bburl']
		);
		
		// Replace what needs to be replaced
		foreach ($thingsToReplace as $find => $replace) {
			$message = str_replace($find, $replace, $message);
		}
		
		$this->twitter->post('statuses/update', array(
			'status' => $message
		));
		
		return true;
	}
	
	/**
	 * Logins an user by adding a cookie into his browser and updating his session
	 */
	public function login($user = '')
	{
		global $mybb, $session, $db;
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		if (!$user['uid'] or !$user['loginkey'] or !$session) {
			return false;
		}
		
		// Delete all the old sessions
		$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' and sid != '" . $session->sid . "'");
		
		// Create a new session
		$db->update_query("sessions", array(
			"uid" => $user['uid']
		), "sid='" . $session->sid . "'");
		
		// Set up the login cookies
		my_setcookie("mybbuser", $user['uid'] . "_" . $user['loginkey'], null, true);
		my_setcookie("sid", $session->sid, -1, true);
		
		return true;
	}
	
	/**
	 * Registers an user with Twitter data
	 */
	public function register($user)
	{
		if (!$user) {
			return false;
		}
		
		global $mybb, $session, $plugins, $lang;
		
		require_once MYBB_ROOT . "inc/datahandlers/user.php";
		$userhandler = new UserDataHandler("insert");
		
		$plength = 8;
		if ($mybb->settings['minpasswordlength']) {
			$plength = (int) $mybb->settings['minpasswordlength'];
		}
		
		// No email? Create a fictional one
		if (!$user['email']) {
			$email = $user['id'] . '@' . str_replace(' ', '', strtolower($mybb->settings['bbname'])) . '.com';
		}
		else {
			$email = $user['email'];
		}
		
		$password = random_str($plength);
		
		$new_user = array(
			"username" => $user['name'],
			"password" => $password,
			"password2" => $password,
			"email" => $user['email'],
			"email2" => $user['email'],
			"usergroup" => $mybb->settings['mytwconnect_usergroup'],
			"displaygroup" => $mybb->settings['mytwconnect_usergroup'],
			"regip" => $session->ipaddress,
			"longregip" => my_ip2long($session->ipaddress),
			"options" => array(
				"hideemail" => 1
			)
		);
		
		/* Registration might fail for custom profile fields required at registration... workaround = IN_ADMINCP defined.
		Placed straight before the registration process to avoid conflicts with third party plugins messying around with
		templates (I'm looking at you, PHPTPL) */
		define("IN_ADMINCP", 1);
		
		$userhandler->set_data($new_user);
		if ($userhandler->validate_user()) {
			
			$user = $userhandler->insert_user();
			
			$plugins->run_hooks("member_do_register_end");
			
			// Deliver a welcome PM
			if ($mybb->settings['mytwconnect_passwordpm']) {
				
				require_once MYBB_ROOT . "inc/datahandlers/pm.php";
				$pmhandler                 = new PMDataHandler();
				$pmhandler->admin_override = true;
				
				// Make sure admins haven't done something bad
				$fromid = (int) $mybb->settings['mytwconnect_passwordpm_fromid'];
				if (!$mybb->settings['mytwconnect_passwordpm_fromid'] or !user_exists($mybb->settings['mytwconnect_passwordpm_fromid'])) {
					$fromid = 0;
				}
				
				$message = $mybb->settings['mytwconnect_passwordpm_message'];
				$subject = $mybb->settings['mytwconnect_passwordpm_subject'];
				
				$thingsToReplace = array(
					"{user}" => $user['username'],
					"{password}" => $password
				);
				
				// Replace what needs to be replaced
				foreach ($thingsToReplace as $find => $replace) {
					$message = str_replace($find, $replace, $message);
				}
				
				$pm = array(
					"subject" => $subject,
					"message" => $message,
					"fromid" => $fromid,
					"toid" => array(
						$user['uid']
					)
				);
				
				// Some defaults :)
				$pm['options'] = array(
					"signature" => 1
				);
				
				$pmhandler->set_data($pm);
				
				// Now let the PM handler do all the hard work
				if ($pmhandler->validate_pm()) {
					$pmhandler->insert_pm();
				}
				else {
					error($lang->sprintf($lang->mytwconnect_error_report, $pmhandler->get_friendly_errors()));
				}
			}
			
			// Post a message on the user's wall
			if ($mybb->settings['mytwconnect_tweet']) {
				$this->tweet($mybb->settings['mytwconnect_tweet_message']);
			}
			
			// Finally return our new user data
			return $user;
			
		}
		else {
			return array(
				'error' => $userhandler->get_friendly_errors()
			);
		}
		
		return true;
	}
	
	/**
	 * Links an user with Twitter
	 */
	public function link_user($user = '', $id)
	{
		global $mybb, $db;
		
		if (!$id) {
			return false;
		}
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		// Still no user?
		if (!$user) {
			return false;
		}
		
		$update = array(
			"mytw_uid" => (int) $id
		);
		
		$db->update_query("users", $update, "uid = {$user['uid']}");
		
		// Add to the usergroup
		if ($mybb->settings['mytwconnect_usergroup']) {
			$this->join_usergroup($user, $mybb->settings['mytwconnect_usergroup']);
		}
		
		// Post a message on the user's wall
		if ($mybb->settings['mytwconnect_tweet']) {
			$this->tweet($mybb->settings['mytwconnect_tweet_message']);
		}
		
		return true;
	}
	
	/**
	 * Unlinks an user from Twitter
	 */
	public function unlink_user($user = '')
	{
		global $mybb, $db;
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		// Still no user?
		if (!$user) {
			return false;
		}
		
		$update = array(
			"mytw_uid" => 0
		);
		
		$db->update_query("users", $update, "uid = {$user['uid']}");
		
		// Remove from the usergroup
		if ($mybb->settings['mytwconnect_usergroup']) {
			$this->leave_usergroup($user, $mybb->settings['mytwconnect_usergroup']);
		}
		
		// Unset the tokens to ask for login again (multiple account support)
		unset($_SESSION[$this->security_key]['access_token']);
		
		return true;
	}
	
	/**
	 * Processes an user
	 */
	public function process($user)
	{
		global $mybb, $db, $session, $lang;
		
		if (!$user['id']) {
			error($lang->mytwconnect_error_noidprovided);
		}
		
		// Let's see if you are already with us
		$query   = $db->simple_select("users", "*", "mytw_uid = {$user['id']}", array(
			"limit" => 1
		));
		$account = $db->fetch_array($query);
		$db->free_result($query);
		
		$message = $lang->mytwconnect_redirect_loggedin;
		
		// Register
		if (!$account) {
			
			if (!$mybb->settings['mytwconnect_fastregistration']) {
			
				header("Location: mytwconnect.php?action=register");
				return false;
				
			}
			
			global $plugins;
			$account = $this->register($user);
			
			if ($account['error']) {
				return $account;
			}
			else {
			
				// Set some defaults
				$toCheck = array('twavatar', 'twbio', 'twlocation');
				foreach ($toCheck as $setting) {
				
					$tempKey = 'mytwconnect_' . $setting;
					$new_settings[$setting] = $mybb->settings[$tempKey];
					
				}
				
				$account = array_merge($account, $new_settings);
				
			}
			
			$message = $lang->mytwconnect_redirect_registered;
			
		}
		
		// Login
		$this->login($account);
		
		// Sync
		$this->sync($account, $user);
		
		$title = $lang->sprintf($lang->mytwconnect_redirect_title, $account['username']);
		
		// Redirect
		$this->redirect('', $title, $message);
		
		return true;
	}
	
	/**
	 * Synchronizes Twitter's data with MyBB's data
	 */
	public function sync($user, $data)
	{
		if (!$user['uid']) {
			return false;
		}
		
		global $mybb, $db, $session, $lang;
		
		$update         = array();
		$userfield = array();
		
		$locationid = "fid" . (int) $mybb->settings['mytwconnect_twlocationfield'];
		$bioid      = "fid" . (int) $mybb->settings['mytwconnect_twbiofield'];
		
		// No data available? Let's get some
		if (!$data) {
			$data = $this->get_user();
		}
		
		$query      = $db->simple_select("userfields", "ufid", "ufid = {$user['uid']}");
		$check = $db->fetch_field($query, "ufid");
		$db->free_result($query);
		
		if (!$check) {
			$userfield['ufid'] = $user['uid'];
		}
		
		// No Twitter ID? Sync it too!
		if (!$user['mytw_uid'] and $data['id']) {
			$update['mytw_uid'] = $data['id'];
		}
		
		// Avatar
		if ($user['twavatar'] and $data['profile_image_url_https'] and $mybb->settings['mytwconnect_twavatar']) {
			
			list($maxwidth, $maxheight) = explode('x', my_strtolower($mybb->settings['maxavatardims']));
			
			$update["avatar"]     = $db->escape_string(str_replace('_normal.', '.', $data['profile_image_url_https']));
			$update["avatartype"] = "remote";
			
			// Copy the avatar to the local server (work around remote URL access disabled for getimagesize)
			$file     = fetch_remote_file($update["avatar"]);
			$tmp_name = $mybb->settings['avataruploadpath'] . "/remote_" . md5(random_str());
			$fp       = @fopen($tmp_name, "wb");
			
			if ($fp) {
				
				fwrite($fp, $file);
				fclose($fp);
				list($width, $height, $type) = @getimagesize($tmp_name);
				@unlink($tmp_name);
				
				if (!$type) {
					$avatar_error = true;
				}
				
			}
			
			if (!$avatar_error) {
				
				if ($width and $height and $mybb->settings['maxavatardims'] != "") {
					
					if (($maxwidth and $width > $maxwidth) or ($maxheight and $height > $maxheight)) {
						$avatardims = $maxheight . "|" . $maxwidth;
					}
					
				}
				
				if ($width > 0 and $height > 0 and !$avatardims) {
					$avatardims = $width . "|" . $height;
				}
				
				$update["avatardimensions"] = $avatardims;
				
			}
			else {
				$update["avatardimensions"] = $maxheight . "|" . $maxwidth;
			}
		}
		
		// Bio
		if ($user['twbio'] and $data['description'] and $mybb->settings['mytwconnect_twbio']) {
			
			if ($db->field_exists($bioid, "userfields")) {
				$userfield[$bioid] = $db->escape_string(htmlspecialchars_decode(my_substr($data['description'], 0, 400, true)));
			}
			
		}
		
		// Location
		if ($user['twlocation'] and $data['location'] and $mybb->settings['mytwconnect_twlocation']) {
			
			if ($db->field_exists($locationid, "userfields")) {
				$userfield[$locationid] = $db->escape_string($data['location']);
			}
			
		}
		
		if ($update) {			
			$query = $db->update_query("users", $update, "uid = {$user['uid']}");
		}
		
		// Make sure we can do it
		if ($userfield) {
			
			if ($userfield['ufid']) {
				$query = $db->insert_query("userfields", $userfield);
			}
			else {
				$query = $db->update_query("userfields", $userfield, "ufid = {$user['uid']}");
			}
			
		}
		
		return true;
	}
	
	/**
	 * Adds the logged in user to an additional group without losing the existing values
	 */
	public function join_usergroup($user, $gid)
	{
		global $mybb, $db;
		
		if (!$gid) {
			return false;
		}
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		if (!$user) {
			return false;
		}
		
		$gid = (int) $gid;
		
		// Is this user already in that group?
		if ($user['usergroup'] == $gid) {
			return false;
		}
		
		$groups = explode(",", $user['additionalgroups']);
		
		if (!in_array($gid, $groups)) {
			
			$groups[] = $gid;
			$update   = array(
				"additionalgroups" => implode(",", array_filter($groups))
			);
			$db->update_query("users", $update, "uid = {$user['uid']}");
			
		}
		
		return true;
	}
	
	/**
	 * Removes the logged in user from an additional group without losing the existing values
	 */
	public function leave_usergroup($user, $gid)
	{
		global $mybb, $db;
		
		if (!$gid) {
			return false;
		}
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		if (!$user) {
			return false;
		}
		
		$gid = (int) $gid;
		
		// Is this user already in that group?
		if ($user['usergroup'] == $gid) {
			return false;
		}
		
		$groups = explode(",", $user['additionalgroups']);
		
		if (in_array($gid, $groups)) {
			
			// Flip the array so we have gid => keys
			$groups = array_flip($groups);
			unset($groups[$gid]);
			
			// Restore the array flipping it again (and filtering it)
			$groups = array_filter(array_flip($groups));
			
			$update = array(
				"additionalgroups" => implode(",", $groups)
			);
			$db->update_query("users", $update, "uid = {$user['uid']}");
			
		}
		
		return true;
	}
	
	/**
	 * Redirects the user to the page he came from
	 */
	public function redirect($url = '', $title = '', $message = '')
	{
		if (!$url) {
		
			$url = $_SESSION['orig_url'];
			unset($_SESSION['orig_url']);
			
		}
		
		if (!strpos($url, "mytwconnect.php")) {
			$url = htmlspecialchars_uni($url);
		}
		else {
			$url = "index.php";
		}
		
		redirect($url, $message, $title);
		
		return true;
	}
	
	/**
	 * Debugs any type of data, printing out an array and immediately killing the execution of the currently running script
	 */
	public function debug($data)
	{
		// Fallback for arrays
		/*if (is_array($data)) {
			$data = array_map('htmlspecialchars_uni', $data);
		}*/
		// Fallback for strings
		/*else */if (is_string($data)) {
			$data = htmlspecialchars_uni($data);
		}
		
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		
		exit;
	}
}