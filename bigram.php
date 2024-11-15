<?php
/*
Plugin Name: bigram
Plugin URI: https://www.oik-plugins.com/oik-plugins/bigram
Description: Extra processing when creating a bigram post type
Version: 0.7.1
Author: bobbingwide
Author URI: https://bobbingwide.com/about-bobbing-wide
Text Domain: oik
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015-2024 Bobbing Wide (email : herb@bobbingwide.com )

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
	add_action( "wp_insert_post", "bigram_wp_insert_post_sample_bigrams", 10, 3 );
	add_action( "pre_get_posts", "bigram_pre_get_posts" );
	add_action( "run_bigram.php", "bigram_run_bigram" );
	add_filter( "oik_add_new_format_bigram", "bigram_oik_add_new_format_bigram", 10, 2 );
	add_action( "oik-media-create-attachment", "bigram_oik_media_create_attachment", 10, 4 );
	add_filter( "bw_field_validation_s-word", "bigram_validate_s_word", 10, 3 );
	add_filter( "bw_field_validation_b-word", "bigram_validate_b_word", 10, 3 );
	add_filter( "bw_field_validation_post_content", "bigram_validate_post_content", 10, 3 );
	add_filter( "oik_add_new_validate", "bigram_add_new_validate", 10, 4 );
	add_action( "oik_fields_loaded", "bigram_oik_fields_loaded" );
	//add_filter( "the_content", "bigram_the_content", 20 );
	add_filter( "genesis_term_intro_text_output", "bigram_the_content", 20 );
	add_filter( "request", "bigram_request" );
	add_action( 'init', 'bigram_block_block_init' );
	add_filter( 'bw_new_pre_update_post', "bigram_bw_new_pre_update_post", 10, 2 );
	remove_action( 'embed_head', 'wp_print_styles', 20 );
	add_filter( 'render_block_core/list-item', 'bigram_render_block_core_paragraph', 10, 3, );
	add_filter( 'render_block_core/paragraph', 'bigram_render_block_core_paragraph', 10, 3, );
	// This is for core/freeform
	add_filter( 'render_block_', 'bigram_render_block_core_paragraph', 10, 3, );
	add_filter( 'render_block_core/heading', 'bigram_render_block_core_paragraph', 10, 3, );
	add_filter( 'render_block_core/code', 'bigram_render_block_core_paragraph', 10, 3, );
	add_filter( 'render_block_core/verse', 'bigram_render_block_core_paragraph', 10, 3, );
	add_filter( 'render_block_core/table', 'bigram_render_block_core_paragraph', 10, 3, );
	add_filter( 'run_wptexturize', '__return_false');

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
 * Some of the fields are now in $_POST?
 *
 * @param ID $post_ID
 * @param object $post
 * @param bool $update
 */
function bigram_wp_insert_post( $post_ID, $post, $update ) {
  //bw_trace2();
	//bw_backtrace();
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

		$s_letter = bigram_map_second_letter( $sword );

    wp_set_post_terms( $post_ID, $s_letter, "s-letter" );
		$b_letter = bigram_map_second_letter( $bword );
    wp_set_post_terms( $post_ID, $b_letter, "b-letter" );

    update_post_meta( $post_ID, "_yoast_wpseo_metadesc", "$title - another SB bi-gram" );
    update_post_meta( $post_ID, "_yoast_wpseo_focuskw", "$title bigram" );
		bw_trace2( $_POST, "_POST from validated?", false );
		$thumbnail_id = bw_array_get( $_POST, "_thumbnail_id", null );
		if ( $thumbnail_id > 1 ) {
			update_post_meta( $post_ID, "_thumbnail_id", $thumbnail_id );
		}

		// @TODO Where should this go?
		unset( $_POST['_thumbnail_id' ] );
		unset( $_POST['_seen_before' ] );
  }
}

/**
 * Implements 'wp_insert_post' for bigrams to sample bigrams
 *
 * Avoids doing anything silly during autosaves being performed in AJAX requets.
 *
 * @param ID $post_ID
 * @param object $post
 * @param bool $update
 */
function bigram_wp_insert_post_sample_bigrams( $post_ID, $post, $update ) {
  //bw_trace2();
	//bw_backtrace();
	if ( wp_doing_ajax() ) {
		return;
	}

  $status = $post->post_status;
  $post_type = $post->post_type;
  if ( $status != "auto-draft" && $post_type == "bigram" ) {
		oik_require( "classes/class-sample-bigrams.php", "bigram" );
		$sample_bigrams = new sample_bigrams();
		$sample_bigrams->sample_post( $post_ID, $post, $update );
	}

}

/**
 * Implement "pre_get_posts" for bigram
 *
 * Updates the array of post types which can be displayed on the page that's showing the blog posts.
 * i.e. The home page, as opposed to the front page.
 *
 * Notes:
 *
 * Original notes pre WordPress 6.3
 * - You can't check for main query in "pre_get_posts"
 * - You can't use WP_Query::is_main_query() either
 * - You can't check is_home() in pre_get_posts for other reasons
 * - Assumes that the "post" post type, for blog posts, will always be included.
 * - Once we've run the main query we don't need this filter any more.
 *
 * Notes for WordPress 6.3 & WooCommerce 8.0.1
 * - we now use $query->is_category() to avoid getting a "Doing it wrong message" from is_category()
 * - this allows for plugins which run queries before the main query...
 * - which is what we're trying to intercept.
 *
 * @param WP_Query $query - the query object for the current query
 * @return WP_Query - the updated query object
 */
function bigram_pre_get_posts( $query ) {
	if ( $query->is_category() && false == $query->get('suppress_filters') ) {

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
	//bw_trace2( $query, "Query", false);
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
 * `
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
 * `
 *
 * @param string $key - the field name used for the file
 * @param array $file - a PHP file structure: name, type, tmp_name, error, size
 * @param array $fields - the field names
 * @param array $validated - validation status

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

	$time = bigram_oik_media_get_file_date( $file );

	require_once( ABSPATH . 'wp-admin/includes/admin.php' );
	$file_return = wp_handle_upload( $file, array('test_form' => false ), $time );
	bw_trace2( $file_return, "file_return", true, BW_TRACE_DEBUG );
  $filename = $file_return['file'];
	$attachment = array( 'post_mime_type' => $file_return['type']
										 , 'post_title' => $post_title
										 , 'post_content' => $post_content
										 , 'post_status' => 'inherit'
										 , 'guid' => $file_return['url']
										 , 'post_date' => $time
										 );
	$attachment_file = bigram_oik_media_attachment_file( $file_return, $time );
	$attachment_id = wp_insert_attachment( $attachment, $attachment_file );
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
	wp_update_attachment_metadata( $attachment_id, $attachment_data );
	$file['id'] = $attachment_id;

	$validated['post_title'] = $post_title;
	$validated['post_content'] = $post_content;
	$validated['post_date'] = $time;
	$validated['_thumbnail_id'] = $attachment_id;
}

function bigram_oik_media_attachment_file( $file_return, $time ) {
	$upload_dir = wp_upload_dir( $time );
	bw_trace2( $upload_dir, "upload_dir", true, BW_TRACE_DEBUG );
	$attachment_file = str_replace( $upload_dir['basedir'] . '/', "", $file_return['file'] );
	return $attachment_file;
}

/**
 * Gets the original image file date
 *
 * @param $file
 * @return string image date
 */
function bigram_oik_media_get_file_date( $file ) {
	//print_r( $file );
	oik_require( "includes/oik-media-date.php", "oik-media" );
  $date = oik_media_get_file_date( $file['tmp_name'] );
	return( $date );
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

/**
 * Map the second letter to a term
 *
 * - Choose the second non blank value from the field
 * - Simplify accented characters to A..Z
 * - Handle other special characters as we see fit.
 *
 * Letter        | Term | Comments
 * -------       | ---- | -------------
 * A..Z          | same | uppercased first character passed through remove_accents()
 * 0..9          | same |
 * _             | _    | @TODO to be completed
 * [             | [    |
 * anything else | ?    |
 */
function bigram_map_second_letter( $field ) {
	$string = trim( $field );
	$new_term = ucfirst( substr( $string, 1, 1 ) );
	$new_term = remove_accents( $new_term );
	return( $new_term );
}


/**
 * Implement "oik_fields_loaded" for bigram
 *
 * Register the CPTs, Taxonomies and relationships that were previously defined using oik-types
 * but which we now want to associate to the REST API
 *
 */
function bigram_oik_fields_loaded() {
	bigram_register_taxonomies();
	bigram_register_bigram();
	bigram_register_seen_before();

	bigram_check_post_type_object( "bigram" );
	register_taxonomy_for_object_type( 'synthesised-by', 'attachment');

}

/**
 * Register the custom taxonomies used by bigram
 *
 */
function bigram_register_taxonomies() {
	$tags = array( "s-word", "b-word", "s-letter", "b-letter" );
	foreach ( $tags as $tag ) {
		$args = array( "show_in_rest" => true
								 , "rest_base"  => $tag
								 , "rest_controller_class" => 'WP_REST_Terms_Controller'
								 , "label" => $tag
								 );
		bw_register_custom_tags( $tag, null, $args );

		bigram_check_tag_object( $tag );
	}
	// These are categories
	$tags = array( 'supplied-by', 'synthesised-by' );
	foreach ( $tags as $tag ) {
		$args = array( "show_in_rest" => true
		, "rest_base"  => $tag
		, "rest_controller_class" => 'WP_REST_Terms_Controller'
		, "label" => $tag
		);
		bw_register_custom_category( $tag, null, $args );
		bigram_check_tag_object( $tag );
	}
}

/**
 * Register the bigram post type
 *
 */
function bigram_register_bigram() {
  $post_type = 'bigram';
  $post_type_args = array();
  $post_type_args['label'] = 'bigrams';
  $post_type_args['description'] = 'Bi-gram - a pair of consecutive written units such as letters, syllables, or words.';
  $post_type_args['supports'] = array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'publicize', 'home', 'custom-fields' );
  $post_type_args['taxonomies'] = array( "category", "s-word", "b-word", "s-letter", "b-letter",'supplied-by', 'synthesised-by' );
  $post_type_args['has_archive'] = true;
	$post_type_args['show_in_rest'] = true;
	$post_type_args['rest_base'] = 'bigram';
	$post_type_args['rest_controller_class'] = 'WP_REST_Posts_Controller';
  //$post_type_args['menu_icon'] = 'dashicons-admin-';
  bw_register_post_type( $post_type, $post_type_args );
}

function bigram_register_seen_before() {
  bw_register_field( "_seen_before", "numeric", "Seen before", array( '#theme' => true, '#form' => false ) );
	bw_register_field_for_object_type("_seen_before", "bigram" );
	bigram_register_post_meta( '_seen_before', 'bigram', __( 'Seen before', 'bigram') );
	bigram_register_post_meta( '_seen_before', 'post', __( 'Seen before', 'bigram') );
}

function bigram_auth_callback() {
    return current_user_can( 'edit_posts');
}

/**
 * Registers the post meta to the REST API.
 *
 * register_post_meta() calls register_meta().
 *
 * @param $field
 * @param $post_type
 */
function bigram_register_post_meta( $field, $post_type, $description ) {
    global $wp_meta_keys;
    bw_trace2( $wp_meta_keys, 'wp_meta_keys before', true, BW_TRACE_DEBUG );
    $registered =  register_post_meta( $post_type, $field,
        array('show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => 'bigram_auth_callback',
            'description' => $description
        )
    );
    bw_trace2( $registered, 'registered?', false, BW_TRACE_VERBOSE );
    bw_trace2( $wp_meta_keys, 'wp_meta_keys after', false, BW_TRACE_VERBOSE );
}

function bigram_check_post_type_object( $post_type ) {
	$post_type_object = get_post_type_object( $post_type );
	bw_trace2( $post_type_object, "post_type_object", true );
}

function bigram_check_tag_object( $tag ) {
	$tag_object = get_taxonomy( $tag );
	bw_trace2( $tag_object, "tag_object", true, BW_TRACE_VERBOSE );
}

/**
 * Implements 'the_content' filter
 *
 * Converts SB's to links
 * @param string $content
 * @return string updated post content
 */
function bigram_the_content( $content ) {
	//bw_trace2(
	static $sample_bigrams = null;
	if ( !$sample_bigrams ) {
		oik_require( "classes/class-sample-bigrams.php", "bigram" );
		$sample_bigrams = new sample_bigrams();

	}
	if ( $sample_bigrams ) {
		$content = $sample_bigrams->the_content( $content );
	}
	//bw_trace2( $content, "after BTC", false);

	return $content;
}

/**
 * Filters the request to attempt to reduce 404s
 *
 * When the URL entered doesn't contain the bigram post type
 * and contains spaces rather than hyphens then we attempt
 * to change the request to load a particular bigram.
 *
 * When the URL contains bigram then the request is like this:
 * URL: bigram/shark%20bloke
 *
 * [page] => (string) ""
 * [bigram] => (string) "shark%20bloke"
 * [post_type] => (string) "bigram"
 * [name] => (string) "shark%20bloke"
 *
 * Otherwise, it's like this:
 * URL: shark%20bloke
 *
 * [page] => (string) ""
 * [pagename] => (string) "shark%20bloke"
 *
 * or this:
 * URL: six%20bells
 *
 * [page] => (string) ""
 * [name] => (string) "six%20bells"
 *
 * In both of these cases we need to make it look like the bigram request.
 *
 * @param arary $request
 * @return array modified request
 */
function bigram_request( $request ) {
	//bw_trace2();
	$post_type = bw_array_get( $request, "post_type", null );

	if ( $post_type == 'bigram' ) {
		$name = bw_array_get( $request, "bigram", '' );
		$name = str_replace( "%20", "-", $name );
		$request[ 'bigram' ] = $name;
		$request[ 'name' ] = $name;
	}

	if ( !$post_type ) {
		$name = bw_array_get( $request, "pagename", null );
		if ( null === $name) {
			$name = bw_array_get( $request, "name", null );
		}
		if ( $name && ( false !== strpos( $name, '%20'))) {
			$name = str_replace( "%20", "-", $name );
			$name = strtolower( $name );
			$words = explode( "-", $name );
			if ( 2 == count( $words ) ) {

				$sword = $words[0][0] == 's';
				$bword = $words[1][0] == 'b';
				if ( $sword && $bword ) {
					unset( $request['pagename']);
					//$request['pagename'] = $name;
					$request['post_type'] = 'bigram';
					$request['bigram'] = $name;
					$request['name'] = $name;
				}
			}
		}
	}
	bw_trace2( $request, 'request', false, BW_TRACE_VERBOSE );
	return $request;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 */
function bigram_block_block_init() {
    $args = [ 'render_callback' => 'bigram_block_dynamic_block'];
    $registered = register_block_type_from_metadata( __DIR__ . '/build/seen-before', $args );

	$args = [ 'render_callback' => 'bigram_block_dynamic_block_search_banter'];
	$registered = register_block_type_from_metadata( __DIR__ . '/build/search-banter', $args );

	$args = [ 'render_callback' => 'bigram_block_dynamic_block_react_sb'];

	$registered = register_block_type_from_metadata( __DIR__ . '/build/reactsb', $args );
	//$registered = register_block_type_from_metadata( __DIR__ .'/src/oik-address', $args );
	//bw_trace2( $registered, "registered?", false );
}


function bigram_block_dynamic_block( $attributes ) {
    $seen_before = bigram_block_increment( '_seen_before' );
    $html = '<div class="seen-before">';
    $html .= '<span>';
    $html .= __( 'Seen before:', 'bigram' );
    $html .= '</span>';
    $html .= '<span class="seen-before-value">';
    $times = _n( '%1$s time', '%1$s times', $seen_before, "bigram" );
    $html .= sprintf( $times, number_format_i18n( $seen_before ) );
	$html .= '</span>';
    $html .= '</div>';

    return $html;
}
/**
 * Increments post meta value
 * @param string $meta_key Meta key
 * @return integer Incremented value
 */
function bigram_block_increment( $meta_key ) {
    //bw_trace2();
    //bw_backtrace();
    $value = 0;
    $post = get_post();
    if (!$post) {
        return 0;
    }
    $value = get_post_meta($post->ID, $meta_key, true);
    if (false === $value || '' === $value) {
        $value = 0;
    }

    // only increment in the front end
    if ( !bigram_is_rest() && !is_admin() ) {

        $value = $value + 1;
        update_post_meta($post->ID, $meta_key, $value);
    }
    return $value;
}

function bigram_is_rest() {
    $is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;
    return $is_rest;
}

function bigram_block_dynamic_block_search_banter( $attributes ) {
	if ( !is_search() ) {
		return null;
	}
	$html = null;
	if (function_exists('\oik\oik_blocks\oik_blocks_check_server_func')) {

		$html = \oik\oik_blocks\oik_blocks_check_server_func('includes/search-banter.php', 'bigram', 'bigram_search_banter');
		if ( ! $html ) {
			$html = bigram_search_banter($attributes);
		}
	}
	return $html;
}

function bigram_block_dynamic_block_react_sb( $attributes ) {

	if (function_exists('\oik\oik_blocks\oik_blocks_check_server_func')) {

		$html = \oik\oik_blocks\oik_blocks_check_server_func('includes/react-SB.php', 'bigram', 'bigram_react_sb');
		if ( ! $html ) {
			$html = bigram_react_sb($attributes);
		}
	}
	return $html;
}

/**
 * Filters the post to apply newly validated fields on an update.
 *
 * For bigrams I want to be able to update existing entries which may have been
 * created without a featured image and only contains content such as:
 * - Seen before as S-word and B-word
 * - Sampled from S-word B-word
 * - <br/>From the original SB.txt
 *
 *
 *
 * @param $post
 * @param $validated
 * @return mixed
 */
function bigram_bw_new_pre_update_post ( $post, $validated ) {
	bw_trace2();
	// Adjust the original content to append to the new content.
	// The content will still be a Classic block, but it will convert better.
	$append = $post['post_content'];
	$append = str_replace( '<br />From the original SB.txt', '<p>From the original SB.txt</p>', $append );

	// "<!--more-->Sampled from {$post->post_title}.";
	if ( 0 === strpos( $append, '<!--more-->Sampled')) {
		$append = str_replace( '>S', '><p>Originally S', $append);
		$append = str_replace( '.', '.</p>', $append);
	}

	// <!--more-->Seen before as S-word and B-word.
	if ( 0 === strpos( $append, '<!--more-->Seen before')) {
		$append = str_replace( '>S', '><p>Originally S', $append);
		$append = str_replace( '.', '.</p>', $append);
	}

	$post['post_content'] = $validated['post_content'] . $append;
	return $post;
}

function bw_form_field_supplied_by( $name, $type, $title, $value, $args) {
	bw_form_field_category( $name, $type, $title, $value, $args );
}

function bw_form_field_synthesised_by( $name, $type, $title, $value, $args) {
	bw_form_field_category( $name, $type, $title, $value, $args );
}

/**
 * Filters the rendered paragraph block.
 *
 * @param $content
 * @param $parsed_block
 * @param $block
 * @return mixed|string
 */
function bigram_render_block_core_paragraph( $content, $parsed_block, $block ) {
	bw_trace2( $content, "content", false);
	$sample_bigrams = bigram_load_sample_bigrams();
	$content = $sample_bigrams->convert_bigrams_to_links( $content, $parsed_block, $block );
	//$content = do_shortcode( $content );
	return $content;
}

function bigram_load_sample_bigrams() {
	static $sample_bigrams = null;
	if ( !$sample_bigrams ) {
		oik_require( "classes/class-sample-bigrams.php", "bigram" );
		$sample_bigrams = new sample_bigrams();
	}
	return $sample_bigrams;
}

