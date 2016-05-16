=== bigram ===
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: specific, behaviour
Requires at least: 4.5.2
Tested up to: 4.5.2
Stable tag: 0.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
When one word won't do.

The bigram WordPress plugin has "specific behaviour".

Its purpose is to make it easy to gather all instances of the bigrams
which are pairs of words where the first word starts with an 'S'
and the second word starts with a 'B'.

It's only needed on the website www.bigram.co.uk

But the code itself could be quite educational since it tries to do things in WordPress
that you might not normally attempt. 



== Installation ==
1. Upload the contents of the bigram plugin to the `/wp-content/plugins/bigram' directory
1. Activate the bigram plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= What's a bigram? =

A bigram or digram is a sequence of two adjacent elements from a string of tokens, which are typically letters, syllables, or words. 
A bigram is an n-gram for n=2.

See [Wikipedia: Bigram](https://en.wikipedia.org/wiki/Bigram)

= Is 'skipping bigrams' in the list? =
Yes, it is now. 

= Why SB? = 
See the website.

= How are the CPT's created? =
The custom post type used is 'bigram'.
The attachments post type has also been customised.

The custom taxonomies are:
- s-word for the word beginning with S
- b-word for the word beginning with B

The above two taxonomies as well as the default Category are attached to both bigrams and attachments.

We don't really use posts.

= What themes are supported? = 
The website was built to work with a theme called genesis-SB - Specially Built.

It was originally put together with TwentyFourteen.
 
It then suffered briefly with an Artisteer theme ( sb0515 ); used for styling basics. 

And some bits were cribbed from Pictorico.



== Screenshots ==
1. None yet

== Upgrade Notice ==
= 0.1.1 =
Latest version on bigram.co.uk

= 0.1.0 =
Now on GitHub.

= 0.1 =
New plugin for the WordPress version of bigram.co.uk.
The original site was developed with Drupal.


== Changelog ==
= 0.1.1 = 
* Added: Populate the database with the original bigrams from SB.txt [github bobbingwide bigram issue 1] 
* Added: Repopulate the database with existing images [github bobbingwide bigram issue 2]
* Added: Latest attached image should be featured image [github bobbingwide bigram issue 2]
* Added: Batch routine to add an image or bigram [github bobbingwide issue 3]
* Changed: Implement 'pre_get_posts' action for bigram
* Fixed: Rename custom taxonomies [github bobbingwide bigram issue 5]

= 0.1.0 =
* Tested: With WordPress 4.5.2
 
= 0.1 =
* Added: New plugin for the WordPress version of bigram.co.uk



