<?php

/**
 * Upgrading routines
 */

class MyTwitter_Update
{

	private $version;

	private $old_version;

	private $plugins;

	private $info;

	public function __construct()
	{

		global $mybb, $db, $cache, $lang;

		if (!$lang->mytwconnect) {
			$lang->load("mytwconnect");
		}

		$this->load_version();

		$check = $this->check_update();

		if ($mybb->input['update'] == 'mytwconnect' and $check) {
			$this->update();
		}

	}

	private function load_version()
	{
		global $cache;

		$this->info        = mytwconnect_info();
		$this->plugins     = $cache->read('shade_plugins');
		$this->old_version = $this->plugins[$this->info['name']]['version'];
		$this->version     = $this->info['version'];

	}

	private function check_update()
	{
		global $lang, $mybb;

		if (version_compare($this->old_version, $this->version, "<")) {

			if ($mybb->input['update']) {
				return true;
			} else {
				flash_message($lang->mytwconnect_error_needtoupdate, "error");
			}

		}

		return false;

	}

	private function update()
	{
		global $db, $mybb, $cache, $lang;

		$new_settings = $drop_settings = [];
		$updateTemplates = 0;

		// Get the gid
		$query = $db->simple_select("settinggroups", "gid", "name='mytwconnect'");
		$gid   = (int) $db->fetch_field($query, "gid");

		// 1.0.2
		if (version_compare($this->old_version, '1.0.1', "<")) {

			require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
			find_replace_templatesets('mytwconnect_usercp_settings', '#' . preg_quote('<input type="submit" value="{$lang->mytwconnect_settings_save}" />') . '#i', '<input type="submit" class=\"button\" value="{$lang->mytwconnect_settings_save}" />{$unlink}');

		}

		// 2.0
		if (version_compare($this->old_version, '2.0', "<")) {

			$new_settings[] = [
				"name" => "mytwconnect_twavatar",
				"title" => $db->escape_string($lang->setting_mytwconnect_twavatar),
				"description" => $db->escape_string($lang->setting_mytwconnect_twavatar_desc),
				"optionscode" => "yesno",
				"value" => 1,
				"disporder" => 30,
				"gid" => $gid
			];

			$new_settings[] = [
				"name" => "mytwconnect_tweet",
				"title" => $db->escape_string($lang->setting_mytwconnect_tweet),
				"description" => $db->escape_string($lang->setting_mytwconnect_tweet_desc),
				"optionscode" => "yesno",
				"value" => 0,
				"disporder" => 31,
				"gid" => $gid
			];

			$new_settings[] = [
				"name" => "mytwconnect_tweet_message",
				"title" => $db->escape_string($lang->setting_mytwconnect_tweet_message),
				"description" => $db->escape_string($lang->setting_mytwconnect_tweet_message_desc),
				"optionscode" => "textarea",
				"value" => $lang->mytwconnect_default_tweet,
				"disporder" => 32,
				"gid" => $gid
			];

		}

		// 3.0
		if (version_compare($this->old_version, '3.0', "<")) {

			$new_settings[] = [
				"name" => "mytwconnect_use_secondary",
				"title" => $db->escape_string($lang->setting_mytwconnect_use_secondary),
				"description" => $db->escape_string($lang->setting_mytwconnect_use_secondary_desc),
				"optionscode" => "yesno",
				"value" => "1",
				"disporder" => 6,
				"gid" => $gid
			];

			$new_settings[] = [
				"name" => "mytwconnect_keeprunning",
				"title" => $db->escape_string($lang->setting_mytwconnect_keeprunning),
				"description" => $db->escape_string($lang->setting_mytwconnect_keeprunning_desc),
				"optionscode" => "yesno",
				"value" => 0,
				"disporder" => 7,
				"gid" => $gid
			];

			$updateTemplates = 1;

			// New column definition to standardize and anonymize identifiers
			$db->modify_column('users', 'mytw_uid', 'VARCHAR(32) NOT NULL DEFAULT 0');

		}

		if ($new_settings) {
			$db->insert_query_multiple('settings', $new_settings);
		}

		if ($drop_settings) {
			$db->delete_query('settings', "name IN ('mytwconnect_". implode("','mytwconnect_", $drop_settings) ."')");
		}

		rebuild_settings();

		if ($updateTemplates) {

			$PL or require_once PLUGINLIBRARY;

			// Update templates
			$dir       = new DirectoryIterator(dirname(__FILE__) . '/templates');
			$templates = [];
			foreach ($dir as $file) {
				if (!$file->isDot() and !$file->isDir() and pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html') {
					$templates[$file->getBasename('.html')] = file_get_contents($file->getPathName());
				}
			}

			$PL->templates('mytwconnect', 'MyTwitter Connect', $templates);

		}

		// Update the current version number and redirect
		$this->plugins[$this->info['name']] = [
			'title' => $this->info['name'],
			'version' => $this->version
		];

		$cache->update('shade_plugins', $this->plugins);

		flash_message($lang->sprintf($lang->mytwconnect_success_updated, $this->old_version, $this->version), "success");
		admin_redirect('index.php');

	}

}

// Direct init on call
$TwitterConnectUpdate = new MyTwitter_Update();