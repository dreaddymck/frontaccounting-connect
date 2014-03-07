=== Frontaccounting connect ===
Contributors: dreaddymck
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=6NA7CLSRP6FMW&lc=US&item_name=Mckenzie%27s%20Touch&item_number=FAC&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: frontaccounting, erp, plugin, widget
Requires at least: 3.0.1
Tested up to: 3.8.1
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import active Frontaccounting items into wordpress as posts.

== Description ==

A simple plugin that can be used to import Frontaccounting product items into wordpress posts. 
Support widget to display imported items in sidebar
Shortcode support.

Shortcode: [ fac-display-items stock_id = "value" ]

Note:
Widgets, and shortcode html can be overridden by common templates located in /plugins/frontaccounting_connect/templates/ directory
Templates copied to theme root folder will override default html used for shortcodes, widget and append to content.
FaC has only been tested in a localhost environment. Remote DB connections not tested.
------------------------
import option
-------------------------
Import/sync product information into a wordpress posts.

NOTE:
imported items initial status is "draft".
Imported data will be located in "post_meta". 
Customize templates if needed.


== Installation ==

1. Upload `frontaccounting_connect` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Fill out and update the settings section. Import frontaccounting data

== Frequently Asked Questions ==

= Does this plugin support real-time pricing and quantities =

Not at the moment. Manual updates required if FrontAccounting items change
 
= Where does this plugin store imported Frontaccount items data =

The item short description will be stored as post content. Everything else is stored in post meta data

== Screenshots ==

na

== Changelog ==

=1.0.1.1=
fixed - pass by reference error

=1.0.1=
fixed - image import error
fixed - associating image with post
added - jQuery UI progress bar


=1.0=
no change - initial product release

== Upgrade Notice ==
na

== Arbitrary section ==