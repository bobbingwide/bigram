<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

bigram_second_letters();

/**
 * Set the second letter terms for s-letter and b-letter
 */
function bigram_second_letters() {
	oik_require( "admin/oik-a2z-run.php", "oik-a2z" );
	bigram_populate_taxonomies();
}

/**
 * Populate the s-letter and b-letter taxonomies
 */
function bigram_populate_taxonomies() {
  oik_a2z_set_empty_terms( 's-letter' );
	oik_a2z_set_empty_terms( 'b-letter' );

	
	add_action( "oik_a2z_set_posts_terms_filters", "oik_a2z_set_posts_terms_filters", 10, 3 );
	
	$taxonomies = array( array( "post_type" => "bigram", "taxonomy" => "s-letter", "filter" => "bigram_s_letter" ) 
										 , array( "post_type" => "bigram", "taxonomy" => "b-letter", "filter" => "bigram_b_letter" )
										 );
										 
	//$taxonomies = array( array( "post_type" => "bigram", "taxonomy" => "b-letter", "filter" => "bigram_b_letter" )
	//									 );
	
	foreach ( $taxonomies as $post_type_taxonomy ) {
		$post_type = bw_array_get( $post_type_taxonomy, "post_type", null );
		$taxonomy = bw_array_get( $post_type_taxonomy, "taxonomy", null );
		$filter = bw_array_get( $post_type_taxonomy, "filter", null );
		if ( $post_type && $taxonomy ) {
			oik_a2z_set_posts_terms( $post_type, $taxonomy, $filter );
		}
	}

}


/**
 * Implement "oik_a2z_query_terms_post_filter" for bigram's s-letter
 * 
 * @param array $terms - current values - there may be more than one - can you think of a good reason?
 * @param object $post
 * @return array replaced by the new term
 */
function bigram_s_letter( $terms, $post ) {
	//print_r( $post );
	$new_term = bigram_map_second_letter( $post->post_title );
	echo "New term: $new_term" . PHP_EOL;
	if ( $new_term ) {
		$terms[0] = $new_term;
	}	
	//print_r( $terms );
	return( $terms );
}



/**
 * Implement "oik_a2z_query_terms_post_filter" for bigram's b-letter
 * 
 * @param array $terms - current values - there may be more than one - can you think of a good reason?
 * @param object $post
 * @return array replaced by the new term
 */
function bigram_b_letter( $terms, $post ) {
	//print_r( $post );
	$second_word = bigram_second_word( $post->post_title );
	$new_term = bigram_map_second_letter( $second_word );
	echo "New term: $new_term" ;
	if ( $new_term ) {
		$terms[0] = $new_term;
	}	
	//print_r( $terms );
	return( $terms );
}


function bigram_second_word( $string ) {
	$second_word = bigram_extract_words( $string, 2 ); 
	return( $second_word );
}


function bigram_extract_words( $string, $from, $length=null ) {
	$words = explode( " ",  $string	 );
	$start = $from - 1;
	$selected = array_slice( $words, $start, $length );
	$extract = implode( " ", $selected );
	return( $extract );
}
