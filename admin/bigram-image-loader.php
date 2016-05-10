<?php // (C) Copyright Bobbing Wide 2016


if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Load SB images as bigrams and attachments
 *
 * For each image with S and B words in the file name:
 * 
 * - create a bigram, if necessary 
 * - load the image as an attachment
 * 
 * 
 */
 
bigram_image_loader_loaded(); 

/**
 * WordPress admin pre-req
 */
function bigram_wp_admin_prereq() {
	/** WordPress Administration File API */
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	
	/** WordPress Image Administration API */
	require_once(ABSPATH . 'wp-admin/includes/image.php');
}	
 
/**
 * Function to invoke when bigram-image-loader is loaded	 
 */ 															 
function bigram_image_loader_loaded() {
	oik_require( "classes/class-sb.php", "bigram" );
	bigram_wp_admin_prereq();
	$directory = "c:/apache/htdocs/bigram/images";
	//bigram_image_loader_process_directory();
	bigram_image_loader_process_directory( "c:/apache/htdocs/bigram/images/done", "SB-images-done.txt" );
	
}
																						
function bigram_image_loader_process_directory( $directory="c:/apache/htdocs/bigram/images", $file="SB-images.txt" ) {

	$sbs = load_sb_images( $file );
	
	foreach ( $sbs as $sb ) {
		$sb_obj = new SB( $sb, $directory );
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
		$sb_obj->attach_image( $id, "$directory/$sb" );
		oikb_get_response();
	}
}
	

/**
 * Load SB images
 *
 * We create a list of SB images with a format similar to the original SB.txt file
 * 
 * The images are sourced from: 
 * - c:\apache\htdocs\bigram\images - some of these may already have been processed
 * - c:\
 * - what about c:\apache\htdocs\S*B*.[jpg|png]
 *
 * For the time being we'll create a file.
 * SB-images.txt where the file name comes first followed by the SB stuff
 *
 * @return array image names in SB format
 */
function load_sb_images( $file="SB-images.txt" ) {
	$files = file( $file );
	$sbs = array();
	foreach ( $files as $image_file ) {
		$sb = $image_file;
		$sbs[] = $sb;
	}
	return( $sbs );
}
