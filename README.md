# bigram 
![banner](assets/bigram-banner-772x250.jpg)
* Contributors: bobbingwide
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: specific, behaviour
* Requires at least: 4.5.2
* Tested up to: 6.0.1
* Stable tag: 0.5.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
When one word won't do.

The bigram WordPress plugin has "specific behaviour".

Its purpose is to make it easy to gather all instances of the bigrams
which are pairs of words where the first word starts with an 'S'
and the second word starts with a 'B'.

It's only needed on the website seriouslybonkers.com ( www.bigram.co.uk )

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
# 0.5.0 
Update for easier update of featured image and content for existing bigrams.

# 0.4.1 
Update for improved search banter for non-SB search.

# 0.4.0 
Update for the server rendered bigram/reactsb block.

# 0.3.0 
Added Seen before block for use with the SB theme.

# 0.2.1 
Upgrade for improved saving of drafts.

# 0.2.0 
Upgrade for sampled bigrams.

# 0.1.4 
For testing on a different site.

# 0.1.3 
Upgrade to set the bigram date from the original date time of the image

# 0.1.2 
Now sets featured image from the "Submit bigram" page... [bw_new] shortcode

# 0.1.1 
Latest version on bigram.co.uk ( end May 2016 )

# 0.1.0 
Now on GitHub.

# 0.1 
New plugin for the WordPress version of bigram.co.uk.
The original site was developed with Drupal.


## Changelog 
# 0.5.0 
* Changed: Build with latest scripts #23
* Changed: Update wp-scripts #23
* Added: Implement bw_new_pre_update_post filter function #28
* Changed: Update build files #23
* Changed: Don't sample posts which were created automatically; Seen before or Sampled from #4
* Changed: Only set metaFieldValue for bigram post type #23
* Changed: Remove seenBefore meta attribute - deprecated and not needed #23
* Changed: Remove apiVersion and attributes from Seen before block registration #23
* Changed: Improve logic in the 'request' filter hook #15
* Tested: With WordPress 6.0.1 and WordPress Multisite
* Tested: With PHP 8.0

# 0.4.1 
* Changed: Improve search banter results for non-SB search #24
* Changed: Reduce amount of stuff being traced
* Tested: With WordPress 5.9.3 and WordPress Multisite
* Tested: With PHP 8.0
* Tested: With Gutenberg 13.1.0

# 0.4.0 
* Added: Add bigram/reactsb block for the page-sb.html template #25
* Changed: Deliver seen-before and search-banter blocks individually #24
* Added: Add bigram/search-banter block for the search page #24

# 0.3.0 
* Added: Seen before block ( bigram/seen-before ) #23
* Fixed: prevent badly formed more links in the FSE post-content block #21
* Changed: Change _seen_before field to #theme true so that it can be used in a [bw_fields] shortcode #22
* Tested: With WordPress 5.9.3
* Tested: WIth Gutenberg 13.1.0
* Tested: With PHP 8.0

# 0.2.1 
* Changed: Attempt to avoid problems during heartbeat saving of drafts

# 0.2.0 
* Added: Add _seen_before post meta field to bigram post type.
* Added: Automatically create new SBs on save https://github.com/bobbingwide/bigram/issues/4
* Added: Batch routine to generate sampled bigrams https://github.com/bobbingwide/bigram/issues/4
* Added: Filter genesis_term_intro_text_output
* Added: Filter processing to create links for SB's in content https://github.com/bobbingwide/bigram/issues/4
* Added: Make it easier to find bigrams by filtering 'request' https://github.com/bobbingwide/bigram/issues/15
* Changed: Set default category to 'sampled-bigram' on existing posts
* Changed: Try to catch the place where attachments with funny IDs are created
* Tested: WordPress 4.9.7

# 0.1.4 
* Changed: Automaically sets the s-letter and b-letter tags https://github.com/bobbingwide/bigram/issues/10
* Added: Batch process to set the values for s-letter and b-letter
* Changed: Hardcode the registration of bigram post type and custom taxonomies
* Fixed: Correct value being stored in _wp_attached_file https://github.com/bobbingwide/bigram/issues/12
* Tested: Tested up to WordPress 4.7.3 2017/05/03

# 0.1.3 
* Changed: uses oik-media logic to determine the original date time of the image and set the published date of a bigram https://github.com/bobbingwide/bigram/issues/6

# 0.1.2 
* Changed: Set featured image when a new bigram is created using the [bw_new] form https://github.com/bobbingwide/bigram/issues/6

# 0.1.1 
* Added: Populate the database with the original bigrams from SB.txt https://github.com/bobbingwide/bigram/issues/1
* Added: Repopulate the database with existing images https://github.com/bobbingwide/bigram/issues/2
* Added: Latest attached image should be featured image https://github.com/bobbingwide/bigram/issues/2
* Added: Batch routine to add an image or bigram https://github.com/bobbingwide/issues/3
* Changed: Implement 'pre_get_posts' action for bigram
* Fixed: Rename custom taxonomies https://github.com/bobbingwide/bigram/issues/5
* Changed: Validate the [bw_new] form https://github.com/bobbingwide/bigram/issues/6

# 0.1.0 
* Tested: With WordPress 4.5.2

# 0.1 
* Added: New plugin for the WordPress version of bigram.co.uk
