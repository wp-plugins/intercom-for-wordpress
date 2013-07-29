=== Intercom for WordPress ===
Contributors: lumpysimon
Donate link: http://lumpylemon.co.uk/donate
Tags: intercom, intercom.io, crm, messaging, contact form, support, email, feedback, customer relationship management
Requires at least: 3.3
Tested up to: 3.5.2
Stable tag: trunk

Easy integration of the Intercom CRM and messaging app into your WordPress website.

== Description ==

[Intercom](http://intercom.io) is a customer relationship management (CRM) and messaging tool for web app owners. WordPress is being widely used as a web app nowadays, so Intercom is an ideal companion app to find out more about your users, contact them, get their instant feedback, and track your relationship with them over time so you can spot those who need attention.

This plugin generates the Javascript install code to integrate all of this functionality into your WordPress-powered web app.

You can also optionally send extra custom data about your users.

= Important! =

Intercom have now made "secure mode" mandatory, so as of version 0.6 this plugin will only output the install code if you have entered both your app ID and your secret key. If you are upgrading from an earlier version and you are not using secure mode, please make sure you enter your secret key otherwise your users will not be tracked.

== Frequently Asked Questions ==

= What on earth is Intercom and what is a CRM? =

Take a look at http://intercom.io, they explain it better than I can!

= Does this plugin track all visitors to my site? =

No, it only tracks logged-in users. The administrator is not tracked.

= Are Intercom and this plugin secure? =

Intercom's "secure mode" is now mandatory. This plugin uses your Intercom secret key to generate a 'hash' with every request - this prevents users maliciously sending messages as another user. If you do not enter your secret key in the settings screen, this plugin will do nothing.

= Can I choose the format of the username sent to Intercom? =

Yes, you can choose between "Firstname Lastname" or the user's displayname.

= Can I send other custom data? =

Yes, if you wish you can send the user's role, ID and website URL.

= Does this plugin work on older versions of WordPress or PHP? =

Possibly, but I've not tried. I can only provide support if you're using the latest version of this plugin together with WordPress 3.5 or newer and PHP 5.2.4 or newer.

== Installation ==

1. Upload the intercom-for-wordpress folder to your wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to the settings page.
4. Enter your Intercom App ID.
5. Enable secure mode in Intercom and enter your secret key in the settings screen (it's a combination of letters and numbers, similar to your App ID).
6. Choose your preferred username format, optional custom data and whether to track admin pages.

== Changelog ==

= 0.6 =
* Make the secret key field mandatory and do not output the install code if it is not set
* Remove redundant code that was generating a PHP notice

= 0.5 =
* Add option to allow tracking of admin pages (off by default)
* Update install code to load JavaScript from CDN

= 0.4 =
* Use latest version of the install code
* Add filter (ll_intercom_custom_data) so plugins/themes can add their own custom data

= 0.3.1 =
* Fix Multisite network-activated options saving bug
* Remove default options

= 0.3 =
* Fix kses missing arguments bug
* Multisite-compatible options page
* Use latest version of the install code
* Remove the label option (no longer supported by Intercom)
* Various code improvements and DocBlock comments

= 0.2 =
* Corrected user capability check when displaying reminder notice
* Added description and code comments
* Added 'Like this Plugin?' section to settings screen
* Code tidy-up

= 0.1 =
* Initial release
