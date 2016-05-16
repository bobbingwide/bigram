<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

bigram_set_featured_images();

/**
 * Set the featured image for each attachment
 *
 */
function bigram_set_featured_images() {
	oik_require( "includes/bw_posts.inc" );
	$atts = array( "post_type" => "attachment" 
							 , "post_status" => "inherit"
							 , "numberposts" => -1
							 , "post_parent" => "any"
							 );
	$posts = bw_get_posts( $atts );
	echo "Attachments: " . count( $posts ) . PHP_EOL;
	foreach ( $posts as $post ) {
		
		$parent = $post->post_parent;
		$attachment = $post->ID;
		echo "set featured image $parent $attachment " . PHP_EOL;
		if ( $parent != 0 ) {
			set_post_thumbnail( $parent, $attachment );
		}
	}
}

