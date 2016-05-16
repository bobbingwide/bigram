<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Define the bigram identified by this file or set of words
 * 
 */
function bigram_lazy_run_bigram() {
	oik_require( "classes/class-sb.php", "bigram" );
	print_r( $_SERVER['argv' ] );
	$file_or_bigram = oik_batch_query_value_from_argv( 1, null );
	$dir = oik_batch_query_value_from_argv( "dir", null );
	
	
	echo "Processing $file_or_bigram. If file then it's in $dir" . PHP_EOL;
	$additional_body_text = oik_batch_query_value_from_argv( 2, null );
	
	$ext = substr( $file_or_bigram, -4 );
	switch ( strtolower( $ext ) ) {
		case ".jpg":
		case ".png":
		case ".gif":
			bigram_run_bigram_image( $file_or_bigram, $dir, $additional_body_text );
			break;
			
		default:
			bigram_run_bigram_text( $file_or_bigram, $additional_body_text );
	}
	
}


/**
 * WordPress admin pre-req
 */
function bigram_wp_admin_prereq() {
	/** WordPress Administration File API */
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	
	/** WordPress Image Administration API */
	require_once(ABSPATH . 'wp-admin/includes/image.php');
}	

 
function bigram_run_bigram_text( $sb, $additional_body_text ) {
	//echo $sb;
	echo PHP_EOL;
	
	$sb_obj = new SB( $sb );
	$sb_obj->set_additional_body_text( $additional_body_text );
	$sb_posts = $sb_obj->get_bigrams();
	 
	if ( $sb_posts ) {
		echo "$sb already defined" . PHP_EOL; 
		$post = $sb_posts[0]; 
		$id = $post->ID; 
	} else {
		echo "$sb not defined" . PHP_EOL;
		$id = $sb_obj->insert_bigram(); 
	
	}
	$sb_obj->set_tags( $id );
}

/**
 * Load a bigram and attachment
 *
 * @param string $sb - the image file name - which may contain some SB classification stuff
 * @param string $directory - the source directory of the image
 * @param string $additional_body_text - additional text to include in the body of the bigram and attachment
 */
function bigram_run_bigram_image( $sb, $directory, $additional_body_text ) {
	bigram_wp_admin_prereq();
	
	$sb_obj = new SB( $sb, $directory );
	$sb_obj->set_additional_body_text( $additional_body_text );
	//$sb_obj->set_directory( $directory );
	$sb_posts = $sb_obj->get_bigrams();
	if ( $sb_posts ) {
		echo "$sb already defined" . PHP_EOL; 
		$post = $sb_posts[0]; 
		$id = $post->ID; 
	} else {
		echo "$sb not defined" . PHP_EOL;
		$id = $sb_obj->insert_bigram(); 
	}
	$sb_obj->set_tags( $id );
	$attachment_id = $sb_obj->attach_image( $id, "$directory/$sb" );
	$sb_obj->set_featured_image( $id, $attachment_id );

}
