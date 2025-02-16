# translate-in-realtime
Translate Text in Realtime on frontend. Helpful if some of the words are not getting translated after changing the language.

Contributors: Unsia Syed
Donate link: http://ifinityhub.com
Tags: replace, search, find and replace, search and replace, on demand replace
Requires at least: 5.0
Tested up to: 6.5.2
License: GPLv2 or later

Set up find and replace rules that are executed AFTER a page is generated by WordPress, but BEFORE it is sent to a user's browser.

== Description ==

This plugin allows you to dynamically translate Text in Realtime on frontend. Helpful if some of the words are not getting translated after changing the language. 

Here are some common uses:

1. Want to selectively translate text that is being output by another plugin? You can do that.
2. Trying to tweak the text on a product or shopping cart page? No problem.
3. Hoping to remove footer text from a theme without modifying the theme? That's easy.


== Installation ==

1. Upload the translate-in-realtime folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The find and replace rules are in the Tools sidebar menu.
4. Click on the Add link on the Find and Replace admin page to add as many rules as you want.

== Frequently Asked Questions ==

= Where is data stored? =

In an array in the wp_options table. Just one record regardless of the number of find and replace rules.

= Will the find and replace slow my site? =

Unless you're using 50+ rules, you shouldn't notice any performance impact. And if you're using a caching plugin, even more rules won't slow your site.

= What does the regex checkbox do? =

You can do a straight up find and replace where the plugin will look for an exact match of what you specified. You can also used advanced pattern matching that is available through regular expressions by checking the regex checkbox.

= My web site is showing a blank page. What do I do? =

This will happen if you are using a rule with regex enabled and the rule has an error. Regex is tricky so I recommend starting with a simple rule and adding to it to perform the replace you want.


== Changelog ==


= 1.0 =
* Initial release.
