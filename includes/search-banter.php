<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2022
 */

function bigram_search_banter( $attributes ) {
	if ( !is_search() ) {
		return null;
	}
	$html = bigram_search_title();
	ob_start();
	bigram_search_banter_details();
	$html .= ob_get_clean();
	return $html;
}


/**
 * Return the title with the search term.
 */
function bigram_search_title() {
	$html = '<div class="archive-description"><h1 class="archive-title">';
	$html .= 'Search results for: ';
	$html .= get_search_query();
	$html .= '</h1></div>';
	return $html;
}

/**
 * Analyses the search for SBs that may have been seen before with some search banter
 *
 * Processing depends on the search and the results
 *
 * search | results | processing
 * ------ | ------- | ------------------
 * SB     | 0       | Create a new post in category "Seen before?"
 * SB     | 1       | Is it a match?
 * SB     | > 1     | Is it a match?
 * not SB | ?       | Why not search for words starting with S or B ?
 *
 */
function bigram_search_banter_details() {

	echo '<div class="search-banter">';

	$terms = bigram_search_banter_get_terms();
	$swords = get_words_starting( $terms, "s", "s-word" );
	$bwords = get_words_starting( $terms, "b", "b-word" );
	$is_sb_query = is_sb_query( $terms, $swords, $bwords );
	if ( $is_sb_query ) {
		global $wp_query;
		if ( $wp_query->post_count > 0 ) {
			// something found
			bigram_check_first_post( $swords, $bwords );
		} else {
			// Nothing found
			// Look for combinations
			bigram_consider_terms( $swords, $bwords );
		}

	} else {
		// We don't really care if it's not an SB query
		// Produce the same message as for a 404 page.
		bigram_sorry_but(' Sorry but, no posts surfaced before I gave up looking. Sobeit.');
	}
	echo '</div>';

}

/**
 * Gets search terms
 *
 * @return array of the escaped words in the search
 */
function bigram_search_banter_get_terms() {
	$terms = get_search_query();
	//echo $terms;

	$terms = trim( $terms );
	$terms = strtolower( $terms );
	$array = explode( " ", $terms );
	return $array;
}

/**
 *
 */
function bigram_consider_terms( $swords, $bwords ) {
	bw_trace2();
	$sword = current( $swords );
	$bword = current( $bwords );
	printf( '<br />Considering terms: %1$s %2$s', $sword, $bword );

	$sterm = bigram_get_term( "S-word", $sword, "s-word" );
	$bterm = bigram_get_term( "B-word", $bword, "b-word" );

	if ( $sterm && $bterm && $sterm->count && $bterm->count ) {
		//print_r( $sterm );
		if ( is_user_logged_in() ) {
			bigram_create_seen_before( $sword, $bword, $sterm, $bterm );
		} else {
			bigram_sorry_but( "You need to be logged in to automatically create searched bigrams" );
		}
	} else {
		bigram_sorry_but( "This doesn't qualify for automatic creation of an SB." );
	}
}

/**
 * Extracts terms starting with the given letter
 *
 * @param array $terms - array of lower case terms
 * @param string $letter - the lower case first letter
 * @param string $taxonomy - the s-word or b-word taxonomy name
 */
function get_words_starting( $terms, $letter, $taxonomy ) {
	$words = array();
	foreach ( $terms as $word ) {
		if ( substr( $word, 0, 1 ) == $letter ) {
			$words[ $word ] = $word;
		}
	}
	return $words;
}

/**
 * Determines if it's an SB query
 *
 * Echoes a message indicating its opinion on the search
 *
 * @param array $terms complete array of terms ( escaped )
 * @param array $swords array of S-words
 * @param array $bwords array of B-words
 * @return bool True if the number of terms is two, one's an S-word and the other's a B-word. False otherwise.
 */
function is_sb_query( $terms, $swords, $bwords ) {
	$is_sb_query = false;
	$lookup = count( $terms );
	$lookup .= count( $swords );
	$lookup .= count( $bwords );
	$messages = array();
	$messages[ "211" ] = "Searching Beautifully...";
	$messages[ "110" ] = "How about a B word too?";
	$messages[ "101" ] = "How about searching for an S word as well?";
	// $messages[ "100" ] =
	// $messages[ "200" ] =
	$message = bw_array_get( $messages, $lookup, "Try to search for an S-word a B-word or both" );
	//echo "<br />";
	echo $message;
	if ( $lookup == "211" ) {
		$is_sb_query = true;
	}
	return $is_sb_query;
}


/**
 * Get the term for the given taxonomy
 *
 * @param string $label Label for the term
 * @param string $word the lower case term word
 * @param string $taxonomy taxonomy name
 * @return term|null the term object found
 */
function bigram_get_term( $label, $word, $taxonomy ) {

	$term = get_term_by( "slug", $word, $taxonomy );
	if ( $term ) {
		echo '<br />';
		$times = _n( 'Found %1$s %2$s once', 'Found %1$s %2$s %3$s times', $term->count, "genesis-SB" );
		$link = retlink( null, get_term_link( $term ), $word );
		printf( $times, $label, $link, $term->count );
	}
	return $term;
}

/**
 * Sets the $sorry_but prefix
 */
function bigram_sorry_but( $text ) {
	global $sorry_but;
	$sorry_but = $text;
	echo '<br />' . $text;
}

/**
 * See if we've got a perfect match
 * then perhaps report on when it was last searched for
 * and how many times displayed.
 */
function bigram_check_first_post( $swords, $bwords ) {
	$sword = current( $swords );
	$bword = current( $bwords );
	$post = get_post();
	echo "<br />Checking first post: " . $post->post_title;
	//print_r( $post );
	if ( $post->post_title == bigram_title_text( $sword, $bword ) ) {
		echo "<br />Satisfied by...";
	}
}

function bigram_create_post_content( $sword, $bword, $sterm, $bterm ) {
	$content = sprintf( '<!--more-->Seen before as %1$s and %2$s.', $sword, $bword );
	return $content;
}

function bigram_title_text( $sword, $bword ) {
	$title_text = ucfirst( $sword );
	$title_text .= " ";
	$title_text .= ucfirst( $bword );
	return $title_text;
}

/**
 * Create a new bigram where the terms have been seen before
 *
 * Category: Seen before?
 */
function bigram_create_seen_before( $sword, $bword, $sterm, $bterm ) {
	$title_text = bigram_title_text( $sword, $bword );
	$post_content = bigram_create_post_content( $sword, $bword, $sterm, $bterm );
	$post = array( "post_type" => "bigram"
	, "post_title" => $title_text
	, "post_name" => $title_text
	, "post_status" => "publish"
	, "post_content" => $post_content
	, "post_author" => 1
	);
	$id = wp_insert_post( $post, true );
	wp_add_object_terms( $id, "seen-before", "category" );
	$metadesc = "{$title_text} bigram";
	update_post_meta( $id, "_yoast_wpseo_metadesc", $metadesc );
	update_post_meta( $id, "_yoast_wpseo_focuskw", $metadesc );

	//echo "Created $id for {$this->title_text}" . PHP_EOL;

	return $id;

}
