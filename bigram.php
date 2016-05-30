<?php
/*
Plugin Name: bigram
Plugin URI: http://www.oik-plugins.com/oik-plugins/bigram
Description: Extra processing when creating a bigram post type
Version: 0.1.1
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
	add_filter( "oik_add_new_format_bigram", "bigram_oik_add_new_format_bigram", 10, 2 );
	add_action( "oik-media-create-attachment", "bigram_oik_media_create_attachment", 10, 4 );
	add_filter( "bw_field_validation_s-word", "bigram_validate_s_word", 10, 3 );
	add_filter( "bw_field_validation_b-word", "bigram_validate_b_word", 10, 3 );
	add_filter( "bw_field_validation_post_content", "bigram_validate_post_content", 10, 3 );
	add_filter( "oik_add_new_validate", "bigram_add_new_validate", 10, 4 );
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
	bw_backtrace();
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

/**
 * Define the [bw_new] format for bigrams
 *
 * We don't need the post_title field since we'll create this automatically from the s-word and b-word
 * ... but when
 * 
 * @param string $format 
 * @param string $post_type
 * @return string the required format
 *
 */
function bigram_oik_add_new_format_bigram( $format, $post_type ) {
	return( "I_C" );
}

/**
 * Validate and create the media attachment
 *
 * @param string $key - the field name used for the file
 * @param array $file - a PHP file structure: name, type, tmp_name, error, size
 * @param array $fields - the field names
 * @param array $validated - validation status
 * 
     [0] => file
    [1] => Array
        (
            [name] => IMG_0008.JPG
            [type] => image/jpeg
            [tmp_name] => C:\Windows\Temp\phpDAE3.tmp
            [error] => 0
            [size] => 796444
        )

    [2] => Array
        (
            [0] => s-word
            [1] => b-word
        )

    [3] => Array
        (
        )

 */ 
function bigram_oik_media_create_attachment( $key, $file, $fields, &$validated ) {
	bw_trace2();
	$s_word = bw_array_get( $_REQUEST, "s-word", null );
	$s_word = trim( $s_word );
	$s_word = strtolower( $s_word );
	$s_word = ucfirst( $s_word );
	$b_word = bw_array_get( $_REQUEST, "b-word", null );
	$b_word = trim( $b_word );
	$b_word = strtolower( $b_word );
	$b_word = ucfirst( $b_word );
	$post_title = "$s_word $b_word";
	$post_content = bw_array_get( $_REQUEST, "post_content", null );
	$file['name'] = "$post_title.jpg";
	require_once( ABSPATH . 'wp-admin/includes/admin.php' );
	$file_return = wp_handle_upload( $file, array('test_form' => false ) );
	bw_trace2( $file_return, "file_return", true, BW_TRACE_DEBUG );
  $filename = $file_return['file'];
	$attachment = array( 'post_mime_type' => $file_return['type']
										 , 'post_title' => $post_title
										 , 'post_content' => $post_content
										 , 'post_status' => 'inherit'
										 , 'guid' => $file_return['url']
										 );
	$attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
	wp_update_attachment_metadata( $attachment_id, $attachment_data );
	$file['id'] = $attachment_id;
	
	$validated['post_title'] = $post_title;
	$validated['post_content'] = $post_content;
	$validated['_thumbnail'] = $attachment_id;
}

/**
 * Validate the s-word
 * 
 * @param string $value trimmed and stripslashed value for the s-word
 * @param string $field field name
 * @param array $data field type definition
 */
function bigram_validate_s_word( $value, $field, $data ) { 
	$value = bigram_validate_a_word( $value, $field, $data, 'S' );
	return( $value );
}

/**
 * Validate the b-word
 * 
 * @param string $value trimmed and stripslashed value for the s-word
 * @param string $field field name
 * @param array $data field type definition
 */
function bigram_validate_b_word( $value, $field, $data ) { 
	$value = bigram_validate_a_word( $value, $field, $data, 'B' );
	return( $value );
}


/**
 * Validate the word starts with the given letter
 * 
 * @param string $value trimmed and stripslashed value for the s-word
 * @param string $field field name
 * @param array $data field type definition
 * @param string $required_first_letter
 * @return string validated field or null
 */
function bigram_validate_a_word( $value, $field, $data, $required_first_letter="S" ) { 
	bw_trace2();
	$value = strtolower( $value );
	$value = ucfirst( $value );
	$first_letter = substr( $value, 0, 1 ); 
	if ( $first_letter !== $required_first_letter ) {
		$value = null; 
		bw_issue_message( $field, "invalid", "Invalid value for {$data['#title']}. It must start with the letter $required_first_letter", 'error' ); 
	}
	return( $value );
}

/**
 * Implement "oik_add_new_validate" to validate the add new form for a bigram
 *
 */ 
function bigram_add_new_validate( $valid, $format, $fields, &$validated ) {
	bw_trace2();
	bw_backtrace();
	$post_title = bw_array_get( $validated, "post_title", null ); 
	if ( !$post_title ) {
		$s_word = bw_array_get( $validated, "s-word", null );
		$b_word = bw_array_get( $validated, "b-word", null );
		$post_title = "$s_word $b_word";
		$validated['post_title'] = $post_title;
	}
	return( $valid );
}
 


                                