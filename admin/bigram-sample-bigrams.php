<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2018, 2024
 *
 */


if ( PHP_SAPI !== "cli" ) {
	die();
}

/**
 * Extracts existing bigrams from content
 *
 * Implement issue #4 in batch
 *
 * 1. List all bigrams
 * 2. Create mapping
 * 3. For each post in the mapping
 * 4. sample_bigram - find sampled bigrams
 * 5. Create each new sampled bigram
 *
 */
function bigram_sample_bigrams() {
	oik_require( "classes/class-sample-bigrams.php", "bigram" );
	$sample_bigrams = new sample_bigrams();
	$sample_bigrams->set_echo( true );

	$sample_bigrams->get_default_category();
	$sample_bigrams->load();
	$sample_bigrams->map_posts();
	$sample_bigrams->process();
	$sample_bigrams->report();


}

function bigram_sample_post( $post_ID=6550 ) {
	oik_require( "classes/class-sample-bigrams.php", "bigram" );
	$sample_bigrams = new sample_bigrams();
	$sample_bigrams->set_echo();
	$post = get_post( $post_ID);
	//print_r( $post );
	if ( $post ) {
		$sample_bigrams->sample_post( $post_ID, $post, true );
	}
}



//bigram_sample_bigrams();
bigram_sample_post();



