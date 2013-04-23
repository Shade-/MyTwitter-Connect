MyTwitter Connect
===============================

> **Current version** beta 3  
> **Dependencies** [PluginLibrary][1]  
> **Author** Shade  

General
-------

MyTwitter Connect is meant to be the missing bridge between Twitter and MyBB. It lets your users login with their Twitter account, registering if they don't have an account on your board, and linking their Twitter account to their account on your board if they have one already.

It has been built from [MyFacebook Connect][2] code and it's considered its twin plugin.

**MyTwitter Connect is currently in beta testing and shouldn't be installed on live boards, although it's fairly stable.**

The plugin adds 13 settings into your Admin Control Panel which let you specify the Twitter Consumer Key, Twitter Cosnumer Secret, the post-registration usergroup the user will be inserted when registering through Twitter, whether to use fast one-click registrations and other minor settings.

MyTwitter Connect currently comes with the following feature list:

* Connect any user to your MyBB installation with Twitter
* One-click login
* One-click registration if setting "Fast registration" is enabled, else the user will be asked for a new username, a new email and data syncing permissions
* Automatically synchronizes Twitter account data with MyBB account, including avatar, cover (if Profile Pictures plugin is installed), location and biography
* Already-registered users can link to their Twitter account manually from within their User Control Panel
* Twitter-linked users can choose what data to import from their Twitter from within their User Control Panel
* Works for all MyBB 1.6 installations and web servers thanks to the TwitterOAuth library provided
* You can set a post-registration usergroup to insert the Twitter-registered users, meaning a smoother user experience
* You can notify a newly registered user with a PM containing his randomly generated password. You have full control on the subject, the sender and the message of the PM that you can edit from your Admin Control Panel
* You have full control over synchronized data. You can choose what data to let your users sync with their Twitter accounts by simply enabling the settings into the Admin Control Panel
* Redirects logged in/registered users to the same page they came from
* *It works*
* *It's free*

Future updates
-------------

Would you like to see a feature developed for MyTwitter Connect? No problem, just open a new Issue here on GitHub and I'll do my best to accomplish your request!

It is based upon TwitterOAuth Library with Twitter REST API 1.1. It is free as in freedom.

[1]: http://mods.mybb.com/view/PluginLibrary
[2]: http://github.com/Shade-/MyFacebook-Connect
