<?php

$l['mytwconnect'] = "MyTwitter Connect";
$l['mytwconnect_login'] = "Login with Twitter";

// Redirects
$l['mytwconnect_redirect_loggedin'] = "You have successfully logged in with Twitter.";
$l['mytwconnect_redirect_registered'] = "You have successfully registered and logged in with Twitter.";
$l['mytwconnect_redirect_title'] = "Welcome, {1}!";

// Errors
$l['mytwconnect_error_noconfigfound'] = "You haven't configured MyTwitter Connect plugin yet: either your Twitter Application ID or your Twitter Application Secret are missing. If you are an administrator, please read the instructions provided in the documentation.";
$l['mytwconnect_error_noauth'] = "You didn't let us login with your Twitter account. Please authorize our application from your Twitter Application manager if you would like to login into our Forum.";
$l['mytwconnect_error_alreadyloggedin'] = "You are already logged into the board.";
$l['mytwconnect_error_report'] = "An unknown error with the remote Twitter API server occured. The output of the error is:

{1}

If you don't know what it means, report it to an administrator.";
$l['mytwconnect_error_unknown'] = "An unknown error occurred using MyTwitter Connect.";

// UserCP
$l['mytwconnect_settings_title'] = $l['mytwconnect_page_title'] = "Twitter integration";
$l['mytwconnect_settings_save'] = "Save";
$l['mytwconnect_settings_twavatar'] = "Avatar and cover";
$l['mytwconnect_settings_twbio'] = "Bio";
$l['mytwconnect_settings_twlocation'] = "Location";
$l['mytwconnect_link'] = "Click here to link your account with your Twitter's one";
$l['mytwconnect_settings_what_to_sync'] = "Select what info we should import from your Twitter. We'll immediately synchronize your desired data on-the-fly while updating the settings, adding what should be added (but not removing what should be removed - that's up to you!).";
$l['mytwconnect_settings_linkaccount'] = "Hit the button on your right to link your Twitter account with the one on this board.";
$l['mytwconnect_settings_unlink'] = "Unlink my account";
$l['mytwconnect_settings_connected'] = "Your Twitter account is currently linked to the account on this board. Click on the button below to unlink.";

// Registration
$l['mytwconnect_register_title'] = "Twitter registration";
$l['mytwconnect_register_basic_info'] = "Choose your basic infos on your right. They are already filled with your Twitter data, but if you want to change them you are free to do it. The account will be linked to your Twitter one immediately, automatically and regardless of your choices.";
$l['mytwconnect_register_what_to_sync'] = "Select what info we should import from your Twitter. We'll immediately synchronize your desired data making an exact copy of your Twitter account, dependently of your choices.";
$l['mytwconnect_register_username'] = "Username:";
$l['mytwconnect_register_email'] = "Email:";

// Success messages
$l['mytwconnect_success_account_linked_title'] = "Account linked";
$l['mytwconnect_success_account_linked'] = "Your MyBB account has been successfully linked to your Twitter one.";
$l['mytwconnect_success_settings_updated_title'] = "Settings updated";
$l['mytwconnect_success_settings_updated'] = "Your Twitter integration related settings have been updated correctly.";
$l['mytwconnect_success_account_unlinked_title'] = "Account unlinked";
$l['mytwconnect_success_account_unlinked'] = "Your Twitter account has been unlinked successfully from your MyBB's one.";

// Who's online
$l['mytwconnect_viewing_loggingin'] = "<a href='mytwconnect.php?action=login'>Logging in with Twitter</a>";
$l['mytwconnect_viewing_registering'] = "<a href='mytwconnect.php?action=register'>Registering with Twitter</a>";