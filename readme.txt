=== Plugin Name ===
Contributors: michelem
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=michele%40befree%2eit&item_name=nofollow-free&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=IT&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: comments, nofollow, links, author, text, post, posts, link, dofollow, blacklist, spam, exclude, users, registered, options, custom, multilanguage, free, locales
Requires at least: 2.x
Tested up to: 2.9 - 2.8 - 2.7 - 2.6 - 2.5 - 2.3 - 2.2.3 - 2.2.2
Stable tag: 1.6.3

NoFollow Free removes the "nofollow" tag from your Wordpress blog's comments with a lot of custom options and provides the option to insert a "NoFollow Free" band at the top of your blog.

== Description ==

This Wordpress plugin remove the "nofollow" attribute from your wordpress blog's comments (precisely from the author's links) and/or from the comments text links and it inserts (if you want) an image band at the top of your pages with the phrase: "NOFOLLOW FRE" to encourage your users to submit comments.
The last release includes new options to replace the nofollow only when the author posted X comments before and put back the nofollow when some blacklisted words are matched. The replacement of the nofollow is also based on the users type (registered and visitor users). Every option cna be customized by the options page "NOFF".

NoFollow Free now is multilingual, it supports English, Spanish, Italian, German, Turkish, Swedish, French, Portuguese, Romanian, Russian, Danish, Arabic, Croatian, Norwegian, Indonesian, Dutch, Hungarian, Chinese, Japanese, Polish amd Finnish languages.

Now it comes with a nice sidebar Widget support to show the top ten charts for the top commenters.

== Installation ==

- Download the plugin nofollow-free
- uncompress it with your prefered unzip/untar program or use the command line: tar xzvf nofollow-free.tar.gz
- copy the entire directory noff in your plugins directory at your wordpress blog (/wp-content/plugins)
- activate the nofollow-free Wordpress plugin at your Plugins admin page

== Frequently Asked Questions ==

Coming soon

== Screenshots ==

You could find some screenshot at http://www.michelem.org/wordpress-plugin-nofollow-free/

== Usage ==

When you activate the plugin it just works removing the "nofollow" attribute from your comments authors link, but if you would like have the image band at the top of your pages too, you MUST put the php function <?php noff(); ?> into your template file just below the <body> attribute like this:

    <body>
    <?php if (function_exists(noff())) noff(); ?>
    ...

Remember to set the plugin option by clicking wp-admin -> options -> NOFF

== Infos ==

You could find every informations here and much more at http://www.michelem.org/wordpress-plugin-nofollow-free/
