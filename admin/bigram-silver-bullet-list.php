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

/**
 * Class SB - Implements the SB bigrams
 * 
 */

class SB {
	
	public $sword;
	public $bword;
	public $sb;
	public $IDs;
	public $title_text;
	public $body_text;
	public $post_date;
	public $attributed_to;
	public $category;
	
	/**
	 *
	 */
	function __construct( $sb ) {
		$this->sb = $sb;
		$this->parse_sb();
	}

	/**
	 * Parse the SB record into the new fields
	 *
	 * `
Banana Splits           d By Herb
Business FactorTable    d By Jon
S B                     c Son of a Bitch (or is it SOB)
S- bend                  - a plumbing term
S- box                   b on soap dispenser in Royal Oak, Langstone
Sabena Bus              b   found at Brussels Airport
	 * `
	 * 
	 *
	 * Field       | Purpose
	 * -------     | -------
	 * $sword      | The S word and tag
	 * $bword      | The B word and tag
	 * $title_text | The bigram
	 * $body text  | The original description, if given
	 * $categories | The mapped category
	 *
   */
	function parse_sb() {
		$sbstring = $this->sb;
		$sbstring = trim( $sbstring );
		$sbstring = $sbstring . " -";
		while  ( false !== strpos( $sbstring, "  " ) ) {
			$sbstring = str_replace( "  ", " ", $sbstring );
		}
		$words = explode( " ", $sbstring );
		$sword = array_shift( $words );
		$bword = array_shift( $words );
		$category = array_shift( $words );
		$body_text = implode( " ", $words );
		$mapped_category = $this->map_category( $category );
		if ( !$mapped_category ) {
			$body_text = $category . " " . $body_text;
			$mapped_category = "SB";
		}
		
		
		$body_text = rtrim( $body_text, " -" );
		
		echo "$sword,$bword,$category,$mapped_category,$body_text" . PHP_EOL;
		$this->title_text = "$sword $bword";
		$this->sword = strtolower( $sword );
		$this->bword = strtolower( $bword );
		$this->body_text = $body_text;
		$this->get_date_from_body();
		$this->category = $mapped_category;

	}
	
	function get_date_from_body() {
		$this->post_date = "1991-07-30";
		if ( $this->body_text ) {
			$dates = explode( " ", $this->body_text );
			foreach ( $dates as $date ) {
				//echo $date . strlen( $date );
				if ( $date[0] == "1" || $date[0] == "2" ) { 
					$adate = strtotime( $date );
					//echo "$date  $adate" . "?" .strftime( $adate ) . "?";
					if ( $adate ) {
						
						//gob();
						$this->post_date = $date;
					}
				}	
			}
		}	
	}
	
	/**
	 * Load any bigrams with this title
	 */
	function get_bigrams() {
		oik_require( "includes/bw_posts.inc" );
		$tax_query = array( 'relation' => 'AND'
											, array( 'taxonomy' => 's', 'field' => 'slug', 'terms' => array( $this->sword ) )
											, array( 'taxonomy' => 'b', 'field' => 'slug', 'terms' => array( $this->bword ) )
											);
		$atts = array( "post_type" => "bigram" 
								, "post_status" => "any" 
								, "numberposts" => -1
								, "tax_query" => $tax_query
								);
		$posts = bw_get_posts( $atts );
		return( $posts );
	}





	/**
	 * Insert a bigram
	 */
	function insert_bigram( ) {
		$post_content = $this->body_text;
		$post_content .= "<!--more--><br />From the original SB.txt";
		$post = array( "post_type" => "bigram"
								 , "post_title" => $this->title_text
								 , "post_name" => $this->title_text
								 , "post_status" => "publish"
								 , "post_content" => $post_content
								 , "post_date" => $this->post_date
								 , "post_modified_date" => $this->post_date
								 , "post_author" => 1
							 );
		$id = wp_insert_post( $post, true );
		$metadesc = "{$this->title_text} bigram";
		update_post_meta( $id, "_yoast_wpseo_metadesc", $metadesc );
		update_post_meta( $id, "_yoast_wpseo_focuskw", $metadesc );
		
		echo "Created $id for {$this->title_text}" . PHP_EOL;
		
		return( $id );
		
	}
	
	function set_tags( $id ) {
	
		wp_set_post_terms( $id, $this->sword, "s" );
		wp_set_post_terms( $id, $this->bword, "b" );
		
		$category_id = $this->get_category_id( $this->category );
		wp_set_post_terms( $id, $category_id, "category" );
	}
	
	
	/**
	 * Map the original classification to the new Category
	 *
	 * 
	 * From | Meaning | To
	 * ---- | ------- | ---------------- 
	 *  b | brand name | Some Business
	 * 	c | cheat | Skipping Bigrams
   * 	d | disastrous failure | Seriously Bad
	 *	f | foreign | Said By
	 *	n | name | Some Body
	 *	p | place | Site Base
	 *	q | questionable | Skipping Bigrams
	 *	s | sick or stupid | Silly Blogger
	 *	x | x-rated | Shocking Behaviour
	 */
	function map_category( $category ) {
		$mapping = array( "-" => "SB"
										, "b" => "Some Business"
										, "c" => "Skipping Bigrams"
										, "d" => "Seriously Bad"
										, "f" => "Said By"
										, "n" => "Some Body"
										, "p" => "Site Base"
										, "q" => "Skipping Bigrams"
										, "s" => "Silly Blogger"
										, "x" => "Shocking Behaviour"
										);
		$mapped_category = bw_array_get( $mapping, $category, null );
		if ( !$mapped_category ) {
			echo "No mapping for: $category" . PHP_EOL;
		}
		return( $mapped_category );
		
	}
	
	function get_category_id( $slug ) {
    $object = get_category_by_slug( $slug );
		//print_r( $object );
		return( $object->term_id );
	}

}			
