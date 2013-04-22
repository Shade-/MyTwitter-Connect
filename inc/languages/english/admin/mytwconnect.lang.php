<?php
// installation
$l['mytwconnect'] = "MyTwitter Connect";
$l['mytwconnect_pluginlibrary_missing'] = "<a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> is missing. Please install it before doing anything else with mytwconnect.";

// settings
$l['mytwconnect_settings'] = "Twitter login and registration settings";
$l['mytwconnect_settings_desc'] = "Here you can manage Twitter login and registration on your board, changing API keys and options to enable or disable certain aspects of MyTwitter Connect plugin.";
$l['mytwconnect_settings_enable'] = "Master switch";
$l['mytwconnect_settings_enable_desc'] = "Do you want to let your users login and register with Twitter? If an user is already registered the account will be linked to its Twitter account.";
$l['mytwconnect_settings_conskey'] = "Consumer Key";
$l['mytwconnect_settings_conskey_desc'] = "Enter your Consumer Key token from Twitter Developers site. This will be used together with the Secret token to ask authorizations to your users through your app.";
$l['mytwconnect_settings_conssecret'] = "Consumer Secret";
$l['mytwconnect_settings_conssecret_desc'] = "Enter your Consumer Secret token from Twitter Developers site. This will be used together with the Key token to ask authorizations to your users through your app.";
$l['mytwconnect_settings_fastregistration'] = "One-click registration";
$l['mytwconnect_settings_fastregistration_desc'] = "If this option is disabled, when an user wants to register with Twitter he will be asked for permissions for your app if it's the first time he is loggin in, else he will be registered and logged in immediately without asking for username changes and what data to sync.";
$l['mytwconnect_settings_usergroup'] = "After registration usergroup";
$l['mytwconnect_settings_usergroup_desc'] = "Enter the usergroup ID you want the new users to be when they register with Twitter. By default this value is set to 2, which equals to Registered usergroup.";
$l['mytwconnect_settings_requestpublishingperms'] = "Request publishing permissions";
$l['mytwconnect_settings_requestpublishingperms_desc'] = "If this option is enabled, the user will be asked for extra publishing permissions for your application. <b>This option should be left disabled (as it won't do anything in particular at the moment). In the future it will be crucial to let you post something on the user's wall when he registers or logins to your board.";
$l['mytwconnect_settings_passwordpm'] = "Send PM upon registration";
$l['mytwconnect_settings_passwordpm_desc'] = "If this option is enabled, the user will be notified with a PM telling his randomly generated password upon his registration.";
$l['mytwconnect_settings_passwordpm_subject'] = "PM subject";
$l['mytwconnect_settings_passwordpm_subject_desc'] = "Choose a default subject to use in the generated PM.";
$l['mytwconnect_settings_passwordpm_message'] = "PM message";
  $l['mytwconnect_settings_passwordpm_message_desc'] = "Write down a default message which will be sent to the registered users when they register with Twitter. {user} and {password} are variables and refer to the username the former and the randomly generated password the latter: they should be there even if you modify the default message. HTML and BBCode are permitted here.";
$l['mytwconnect_settings_passwordpm_fromid'] = "PM sender";
$l['mytwconnect_settings_passwordpm_fromid_desc'] = "Insert the UID of the user who will be the sender of the PM. By default is set to 0 which is MyBB Engine, but you can change it to whatever you like.";
// custom fields support, yay!
$l['mytwconnect_settings_twlocation'] = "Sync location";
$l['mytwconnect_settings_twlocation_desc'] = "If you would like to import Location from Twitter (and let users decide to sync it) enable this option.";
$l['mytwconnect_settings_twlocationfield'] = "Location Custom Profile Field ID";
$l['mytwconnect_settings_twlocationfield_desc'] = "Insert the Custom Profile Field ID which corresponds to the Location field. Make sure it's the right ID while you fill it! Default to 1 (MyBB's default)";
$l['mytwconnect_settings_twbio'] = "Sync biography";
$l['mytwconnect_settings_twbio_desc'] = "If you would like to import Biography from Twitter (and let users decide to sync it) enable this option.";
$l['mytwconnect_settings_twbiofield'] = "Biography Custom Profile Field ID";
$l['mytwconnect_settings_twbiofield_desc'] = "Insert the Custom Profile Field ID which corresponds to the Biography field. Make sure it's the right ID while you fill it! Default to 2 (MyBB's default)";

// default pm text
$l['mytwconnect_default_passwordpm_subject'] = "New password";
$l['mytwconnect_default_passwordpm_message'] = "Welcome on our Forums, dear {user}!

We are pleased you are registering with Twitter. We have generated a random password for you which you should take note somewhere if you would like to change your personal infos. We require for security reasons that you specify your password when you change things such as the email, your username and the password itself, so keep it secret!

Your password is: [b]{password}[/b]

Also, due to the fact that we couldn't fetch your email during the Twitter login process, we have registered you with a fictional email. We strongly recommend you to change it with a real email address to be able to access certain services in the future, such as the password restore system.

With regards,
our Team";

// errors
$l['mytwconnect_error_needtoupdate'] = "You seem to have currently installed an outdated version of MyTwitter Connect. Please <a href=\"index.php?module=config-settings&upgrade=mytwconnect\">click here</a> to run the upgrade script.";
$l['mytwconnect_error_nothingtodohere'] = "Ooops, MyTwitter Connect is already up-to-date! Nothing to do here...";

// success
$l['mytwconnect_success_updated'] = "MyTwitter Connect has been updated correctly from version {1} to {2}. Good job!";