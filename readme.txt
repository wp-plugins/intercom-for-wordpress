=== Intercom for WordPress ===
Contributors: lumpysimon
Donate link: http://lumpylemon.co.uk/donate
Tags: intercom, intercom.io, crm, messaging, contact form, support, email, feedback, customer relationship management
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: trunk

Easy integration of the Intercom CRM and messaging app into your WordPress website.

== Description ==

[Intercom](http://intercom.io) is a customer relationship management (CRM) and messaging tool for web app owners. WordPress is being widely used as a web app nowadays, so Intercom is an ideal companion app to find out more about your users, contact them, get their instant feedback, and track your relationship with them over time so you can spot those who need attention.

This plugin generates the Javascript snippet to integrate all of this functionality into your WordPress-powered web app.

It allows you to securely connect to Intercom using secure key authentication mode, and you can optionally send extra custom data about your users.

== Frequently Asked Questions ==

= What on earth is Intercom and what is a CRM? =

Take a look at http://intercom.io, they explain it better than I can!

= Does this plugin track all visitors to my site? =

No, it only tracks logged-in users. The administrator is also not tracked.

= Can I use private key authentication (user hash)? =

Absolutely! In fact this is highly recommended for security reasons.

= Can I customise the inbox link? =

Yes, you can choose the label text.

= Can I choose the format of the username sent to Intercom? =

Yes, you can choose between "Firstname Lastname" or the user's displayname.

= Can I send other custom data? =

Yes, if you wish you can send the user's role, ID and website URL.

= Does this plugin work on older versions of WordPress or PHP? =

Possibly, but I've not tried. I can't provide support if you're using WordPress 3.2.1 or older, or PHP older than 5.2.4.

== Installation ==

1. Unzip intercom-for-wordpress.zip and upload the folder and its files to your wp-content/plugins directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to the settings page.
4. Enter your Intercom App ID.
5. Highly recommended! Enable secure mode in Intercom and enter your security key in the settings screen (it's a combination of letters and numbers, similar to your App ID).
6. Choose the username format and optional custom data.

== Changelog ==

= 0.1 =
* Initial release
