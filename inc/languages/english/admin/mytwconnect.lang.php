<?php
// Installation
$l['mytwconnect'] = "MyTwitter Connect";
$l['mytwconnect_pluginlibrary_missing'] = "<a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> is missing. Please install it before doing anything else with mytwconnect.";

// Settings
$l['setting_group_mytwconnect'] = "Twitter login and registration settings";
$l['setting_group_mytwconnect_desc'] = "Here you can manage Twitter login and registration on your board, changing API keys and options to enable or disable certain aspects of MyTwitter Connect plugin.";
$l['setting_mytwconnect_enable'] = "Master switch";
$l['setting_mytwconnect_enable_desc'] = "Do you want to let your users login and register with Twitter? If an user is already registered the account will be linked to its Twitter account.";
$l['setting_mytwconnect_conskey'] = "Consumer Key";
$l['setting_mytwconnect_conskey_desc'] = "Enter your Consumer Key token from Twitter Developers site. This will be used together with the Secret token to ask authorizations to your users through your app.";
$l['setting_mytwconnect_conssecret'] = "Consumer Secret";
$l['setting_mytwconnect_conssecret_desc'] = "Enter your Consumer Secret token from Twitter Developers site. This will be used together with the Key token to ask authorizations to your users through your app.";
$l['setting_mytwconnect_fastregistration'] = "One-click registration";
$l['setting_mytwconnect_fastregistration_desc'] = "If this option is disabled, when an user wants to register with Twitter he will be asked for permissions for your app if it's the first time he is loggin in, else he will be registered and logged in immediately without asking for username changes and what data to sync.";
$l['setting_mytwconnect_usergroup'] = "After registration usergroup";
$l['setting_mytwconnect_usergroup_desc'] = "Select the after-registration usergroup. The user will be inserted directly into this usergroup upon registering. Also, if an existing user links his account to Twitter, this usergroup will be added to his additional groups list.";
$l['setting_mytwconnect_use_secondary'] = "Apply usergroup to existing users' secondary list";
$l['setting_mytwconnect_use_secondary_desc'] = "If this option is enabled, existing users who link their account to Twitter will be granted the above usergroup within their secondary usergroups list. Otherwise, they will not be getting any usergroup upgrade.";
$l['setting_mytwconnect_keeprunning'] = "Force operational status";
$l['setting_mytwconnect_keeprunning_desc'] = "Enable this option to let MyTwitter Connect run even if registrations are disabled. This is particularly useful if you want to allow new registrations only with Twitter.";
$l['setting_mytwconnect_passwordpm'] = "Send PM upon registration";
$l['setting_mytwconnect_passwordpm_desc'] = "If this option is enabled, the user will be notified with a PM telling his randomly generated password upon his registration.";
$l['setting_mytwconnect_passwordpm_subject'] = "PM subject";
$l['setting_mytwconnect_passwordpm_subject_desc'] = "Choose a default subject to use in the generated PM.";
$l['setting_mytwconnect_passwordpm_message'] = "PM message";
  $l['setting_mytwconnect_passwordpm_message_desc'] = "Write down a default message which will be sent to the registered users when they register with Twitter. {user} and {password} are variables and refer to the username the former and the randomly generated password the latter: they should be there even if you modify the default message. HTML and BBCode are permitted here.";
$l['setting_mytwconnect_passwordpm_fromid'] = "PM sender";
$l['setting_mytwconnect_passwordpm_fromid_desc'] = "Insert the UID of the user who will be the sender of the PM. By default is set to 0 which is MyBB Engine, but you can change it to whatever you like.";
$l['setting_mytwconnect_tweet'] = "Tweet on user's timeline";
$l['setting_mytwconnect_tweet_desc'] = "Enable this option to post a tweet on the user's timeline when he registers or links his account to your board. Your application must have read and write access for this to work, otherwise it will silently fail (no error message will be presented to the user). When this is active, users might wait a bit more when registering for the first time due to data being transferred to Twitter.";
$l['setting_mytwconnect_tweet_message'] = "Custom tweet message";
$l['setting_mytwconnect_tweet_message_desc'] = "Enter a custom tweet which will be posted to the user's timeline. You can use {bbname} and {bburl} to refer to your board's name and your board's URL. Hashtags and mentions will work. This must be 140 chars or less.";

// Custom fields
$l['setting_mytwconnect_twavatar'] = "Sync avatar";
$l['setting_mytwconnect_twavatar_desc'] = "If you would like to import the avatar from Twitter (and let users decide to sync it) enable this option.";
$l['setting_mytwconnect_twlocation'] = "Sync location";
$l['setting_mytwconnect_twlocation_desc'] = "If you would like to import Location from Twitter (and let users decide to sync it) enable this option.";
$l['setting_mytwconnect_twlocationfield'] = "Location Custom Profile Field";
$l['setting_mytwconnect_twlocationfield_desc'] = "Select the Custom Profile Field that will be filled with Twitter's location.";
$l['setting_mytwconnect_twbio'] = "Sync biography";
$l['setting_mytwconnect_twbio_desc'] = "If you would like to import Biography from Twitter (and let users decide to sync it) enable this option.";
$l['setting_mytwconnect_twbiofield'] = "Biography Custom Profile Field";
$l['setting_mytwconnect_twbiofield_desc'] = "Select the Custom Profile Field that will be filled with Twitter's biography.";

// Default pm text
$l['mytwconnect_default_passwordpm_subject'] = "New password";
$l['mytwconnect_default_passwordpm_message'] = "Welcome on our Forums, dear {user}!

We appreciate that you have registered with Twitter. We have generated a random password for you which you should take note somewhere if you would like to change your personal infos. We require for security reasons that you specify your password when you change things such as the email, your username and the password itself, so keep it secret!

Your password is: [b]{password}[/b]

With regards,
our Team";
$l['mytwconnect_default_tweet'] = "I have just registered on @{bbname}! Join me now registering with Twitter at {bburl}!";

// Errors
$l['mytwconnect_error_needtoupdate'] = "You seem to have currently installed an outdated version of MyTwitter Connect. Please <a href=\"index.php?module=config-settings&update=mytwconnect\">click here</a> to run the upgrade script.";
$l['mytwconnect_error_nothingtodohere'] = "Ooops, MyTwitter Connect is already up-to-date! Nothing to do here...";

// Success
$l['mytwconnect_success_updated'] = "MyTwitter Connect has been updated correctly from version {1} to {2}. Good job!";