=== Intercom for WordPress ===
Contributors: lumpysimon
Donate link: http://lumpylemon.co.uk/donate
Tags: intercom, intercom.io, crm, messaging, contact form, support, email, feedback, customer relationship management
Requires at least: 3.3
Tested up to: 3.5
Stable tag: trunk

Easy integration of the Intercom CRM and messaging app into your WordPress website.

== Description ==

[Intercom](http://intercom.io) is a customer relationship management (CRM) and messaging tool for web app owners. WordPress is being widely used as a web app nowadays, so Intercom is an ideal companion app to find out more about your users, contact them, get their instant feedback, and track your relationship with them over time so you can spot those who need attention.

This plugin generates the Javascript install code to integrate all of this functionality into your WordPress-powered web app.

It allows you to securely connect to Intercom using secure key authentication mode, and you can optionally send extra custom data about your users.

== Frequently Asked Questions ==

= What on earth is Intercom and what is a CRM? =

Take a look at http://intercom.io, they explain it better than I can!

= Does this plugin track all visitors to my site? =

No, it only tracks logged-in users. The administrator is not tracked.

= Can I use private key authentication (user hash)? =

Absolutely! In fact this is highly recommended for security reasons.

= Can I choose the format of the username sent to Intercom? =

Yes, you can choose between "Firstname Lastname" or the user's displayname.

= Can I send other custom data? =

Yes, if you wish you can send the user's role, ID and website URL.

= Does this plugin work on older versions of WordPress or PHP? =

Possibly, but I've not tried. I can only provide support if you're using WordPress 3.5 or newer and PHP 5.2.4 or newer.

== Installation ==

1. Upload the intercom-for-wordpress folder to your wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to the settings page.
4. Enter your Intercom App ID.
5. Highly recommended! Enable secure mode in Intercom and enter your security key in the settings screen (it's a combination of letters and numbers, similar to your App ID).
6. Choose your preferred username format and optional custom data.

== Changelog ==

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
