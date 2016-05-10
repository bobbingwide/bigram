<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Populate the bigrams from the original Silver Bullet list
 *
 * Syntax: oikwp bigram-silver-bullet-list.php
 * when in this directory as the current directory
 * 
 */
bigram_silver_bullet_list_loaded();

/**
 * Function to invoke when loaded
 *
 * Populate the database with the original bigrams from SB.txt
 * 
 * - The original file contained 1425 entries.
 * - This sanitized version contains slightly fewer - duplicates remove.
 * - We need to create a 'bigram' post type for each of these entries.
 * - Some of these may get attachments added in the future.
 * - Each bigram can have many attachments with the same name.
 * - It shouldn't be necessary to create a bigram if we only have a picture.
 * 
 * See p.44 of the Small Black book for the design of the UI
 *
 * 
 */ 
function bigram_silver_bullet_list_loaded() {
	oik_require( "classes/class-sb.php", "bigram" );
	$file = "SB.txt";

	$sbs = file( $file );
	echo count( $sbs ); 
	$count=0;
	foreach ( $sbs as $sb ) {
		echo $count;
		$count++;
		//echo $sb;
		echo PHP_EOL;
		
		$sb_obj = new SB( $sb );
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

}
