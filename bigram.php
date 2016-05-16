<?php
/*
Plugin Name: bigram
Plugin URI: http://www.oik-plugins.com/oik-plugins/bigram
Description: Extra processing when creating a bigram post type
Version: 0.1.0
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: oik
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015,2016 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/
bigram_loaded();

/**
 * Function invoked when bigram is loaded 
 */													
function bigram_loaded() {
	add_action( "wp_insert_post", "bigram_wp_insert_post", 10, 3 ); 
	add_action( "pre_get_posts", "bigram_pre_get_posts" );
	add_action( "run_bigram.php", "bigram_run_bigram" );
}

/**
 * Set bigram fields when creating/updating a post
 * 
 * Title expected to be Sxxx Bxxxx
 * 
 * - first word (S-word) used for the S tag
 * - second word (B-word) used for the B tag
 * - WordPress SEO keyword: S-word B-word bigram
 * - Meta description set to: $title - another SB bi-gram 
 * - 
 */
function bigram_save_fields() {
  $args['tax_input'] = array( "s-word" => $sword 
                            , "b-word" => $bword 
                            );
  return( $post );                            
}

/**
 * Implement 'wp_insert_post' for bigram
 *
 * It's too late to manipulate the content when the post has been created.
 * 
 * @param ID $post_ID
 * @param object $post
 * @param bool $update 
 */
function bigram_wp_insert_post( $post_ID, $post, $update ) {
  bw_trace2(); 
  $status = $post->post_status;
  $post_type = $post->post_type; 
  if ( $status != "auto-draft" && $post_type == "bigram" ) {
    $title = $post->post_title;
    list( $sword, $bword ) = explode(" ", $title . " . . ");
    $sword = strtolower( $sword );
    $bword = strtolower( $bword );
    bw_trace2( $sword, "sword", false );
    bw_trace2( $bword, "bword", false );
    wp_set_post_terms( $post_ID, $sword, "s-word" );
    wp_set_post_terms( $post_ID, $bword, "b-word" );
    update_post_meta( $post_ID, "_yoast_wpseo_metadesc", "$title - another SB bi-gram" );
    update_post_meta( $post_ID, "_yoast_wpseo_focuskw", "$title bigram" );
  }
}



/**
 * Implement "pre_get_posts" for bigram
 *
 * Updates the array of post types which can be displayed on the page that's showing the blog posts.
 * i.e. The home page, as opposed to the front page.
 *
 * Notes: 
 * - You can't check for main query in "pre_get_posts" 
 * - You can't use WP_Query::is_main_query() either
 * - You can't check is_home() in pre_get_posts for other reasons
 * - Assumes that the "post" post type, for blog posts, will always be included.
 * - Once we've run the main query we don't need this filter any more.
 
 * 
 * @param WP_Query $query - the query object for the current query
 * @return WP_Query - the updated query object 
 */
function bigram_pre_get_posts( $query ) {
	bw_trace2();
	if ( is_category() && false == $query->get('suppress_filters') ) {
		$post_types = array( "post", "bigram" );
		/*
		global $wp_post_types;
		foreach ( $wp_post_types as $post_type => $data ) {
		
			//$supports = post_type_supports( $post_type, "home" );
			//if ( $supports ) {
				$post_types[] = $post_type;
				if ( $post_type == "attachment" ) {
					add_filter( "posts_where", "bigram_posts_where", 10, 2);
				}
			}
		}
		*/
		$query->set( 'post_type', $post_types );
		remove_action( "pre_get_posts", "bigram_pre_get_posts" );
	}
	return( $query );
}

/**
 * Implement "run_bigram.php" for bigram 
 */
function bigram_run_bigram() {
	oik_require( "admin/bigram-run-bigram.php", "bigram" );
	bigram_lazy_run_bigram();
	

}


                                