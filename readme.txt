=== bigram ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: specific, behaviour
Requires at least: 4.5.2
Tested up to: 6.5.4
Stable tag: 0.7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
When one word won't do.

The bigram WordPress plugin has "specific behaviour".

Its purpose is to make it easy to gather all instances of the bigrams
which are pairs of words where the first word starts with an 'S'
and the second word starts with a 'B'.

It's only needed on the website seriouslybonkers.com 

But the code itself could be quite educational since it tries to do things in WordPress
that you might not normally attempt. 

This plugin delivers blocks for use in the SB ( Second Byte ) theme:

- bigram/seen-before
- bigram/search-banter
- bigram/reactsb


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
- synthesised-by - the name of the image generation routine 

The above taxonomies, as well as the default Category, are attached to both bigrams and attachments.

Other custom taxonomies for the bigram CPT are: 

- s-letter for the second letter of the S-word
- b-letter for the second letter of the B-word
- supplied-by - the name of the person who supplied the SB

We don't really use posts.

= What themes are supported? =
I'm developing an FSE theme called SB ( Second Byte ) to replace the genesis-SB theme ( Specially Built )
that was used in 2018. 

The site was originally put together with TwentyFourteen.
 
It then suffered briefly with an Artisteer theme ( sb0515 ); used for styling basics. 

And some bits were cribbed from Pictorico... but they didn't work well on tablets or smart phones.


== Screenshots ==
1. None yet

== Upgrade Notice ==
= 0.7.0 = 
Update for improved sampling and display of SB links and improved form for Submit bigram.

== Changelog ==
= 0.7.0 =
* Changed: Update wp-scripts to v27.4.0 #40
* Changed: Generate Seen before posts using blocks #4
* Changed: SB link creation: Replace logic filtering `the_content` to filter the output for specific block rendering #4
* Changed: Update bigram sampling logic #4
* Changed: Display taxonomies as drop down select lists #43
* Tested: With WordPress 6.5.4
* Tested: PHP 8.3
* Tested: With PHPUnit 9.6