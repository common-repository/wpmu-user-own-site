=== Plugin Name ===
Contributors: gavrilov.web
Donate link: https://load.payoneer.com/?email=gavrilov.web@gmail.com
Tags: WPMU, Multisite, Multiuser, Widget, Shortcode, registration
Requires at least: 4.3.1 
Tested up to: 4.3.1 
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin allows to create site on user registration in WPMU environment.

== Description ==

Use this plugin if you want allow users create their own sites based on your.

== Installation ==

1. Upload 'wp-auto-mu' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the settings page (/wp-admin/options-general.php?page=mygewebmu) and use 'Help' tab to choose the variant of integration.

== Screenshots ==

1. Use Recaptcha to save your site from spam sites.
2. Example of frontend view.

== Changelog ==

= 1.0.0 =
Release date: November 27th, 2015

* First version release

== Arbitrary section ==

You can create your own HTML code of the form.
Create file in your theme, put code from plugins/wp-auto-mu/geweb-mu/register.php to your new file and add_filter geweb-mu-register-path to rewrite file paht for the form.