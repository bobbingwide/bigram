# bigram 
![banner](assets/bigram-banner-772x250.jpg)
* Contributors: bobbingwide
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: specific, behaviour
* Requires at least: 4.5.2
* Tested up to: 6.4.2
* Stable tag: 0.6.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
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


## Installation 
1. Upload the contents of the bigram plugin to the `/wp-content/plugins/bigram' directory
1. Activate the bigram plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions 
# What's a bigram? 

A bigram or digram is a sequence of two adjacent elements from a string of tokens, which are typically letters, syllables, or words.
A bigram is an n-gram for n=2.

* See [Wikipedia: Bigram](https://en.wikipedia.org/wiki/Bigram)

# Is 'skipping bigrams' in the list? 
Yes, it is now.

# Why SB? 
See the website.

# How are the CPT's created? 
The custom post type used is 'bigram'.
The attachments post type has also been customised.

The custom taxonomies are:
- s-word for the word beginning with S
- b-word for the word beginning with B

The above two taxonomies, as well as the default Category, are attached to both bigrams and attachments.

We don't really use posts.

# What themes are supported? 
I'm developing an FSE theme called SB ( Second Byte ) to replace the genesis-SB theme ( Specially Built )
that was used in 2018.

The site was originally put together with TwentyFourteen.

It then suffered briefly with an Artisteer theme ( sb0515 ); used for styling basics.

And some bits were cribbed from Pictorico... but they didn't work well on tablets or smart phones.


## Screenshots 
1. None yet

## Upgrade Notice 
# 0.6.0 
Update for support for PHP 8.1, PHP 8.2 and PHP 8.3

## Changelog 
# 0.6.0 
* Changed: Add PHPUnit tests for PHP 8.1 and PHP 8.2 #39
* Changed: Update wp-scripts #40
* Tested: With WordPress 6.4.2
* Tested: With PHP 8.1, PHP 8.2 and PHP 8.3
* Tested: With PHPUnit 9.6
