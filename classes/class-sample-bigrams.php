<?php 

/**
 * @copyright (C) Copyright Bobbing Wide 2018
 * @package bigram
 * 
 */
class sample_bigrams {

	/**
	 * All bigrams 
	 */
	public $posts;
	
	/**
	 * Mapping of bigrams to IDs
	 * first unique key found comes first
	 * ... is that a good idea or not?
	 
	 * $mapping[ $post_name ] => ID  ?
	 */
	public $mapping;
	public $already_mapped;
	public $sampled;
	public $default_category;
	
	
	/**
	 * 
	 */
	function __construct() {
		$this->mapping = array();
		$this->already_mapped = 0;
		$this->posts = null;
		
	}
	
	function reset_sampled() {
		$this->sampled = array();
	}
		
	
	/**
	 * Load all the bigram posts
	 * 
	 * There are thousands
	 */
	function load() {
		oik_require( "includes/bw_posts.php" );
		$args = array( "post_type" => "bigram" 
								 , "numberposts" => -1
								 , "orderby" => "date"
								 , "order" => "asc"
								 );
		$posts = bw_get_posts( $args );
		echo count( $posts ) . PHP_EOL;
		$this->posts = $posts;
	}
	
	function get_default_category() {
		//$this->default_category = 2147;
		$this->default_category = get_term_by( "slug", "sampled-bigram", "category" );
		//print_r( $this->default_category );
		
	}
	
	/**
	 * Map post titles to ID 
	 */ 
	function map_posts() {
		foreach ( $this->posts as $post ) {	 
			$this->map( $post->post_name, $post->ID );
		}
		echo count( $this->mapping ) . PHP_EOL;
		echo $this->already_mapped . PHP_EOL;
	}
	
	
	/**
	 * Maps the post_name to ID
	 * 
	 * Enables a quick lookup of the post name which is expected to be of the form `sword-bword`
	 * when there's more than one then a suffix gets added 
	 * but that's not expected to be an issue
	 *
	 WP_Post Object
(
    [ID] => 212
    [post_author] => 1
    [post_date] => 1991-07-30 00:00:00
    [post_date_gmt] => 1991-07-30 00:00:00
    [post_content] => By Jon<!--more--><br />From the original SB.txt
    [post_title] => Business FactorTable
    [post_excerpt] =>
    [post_status] => publish
    [comment_status] => closed
    [ping_status] => closed
    [post_password] =>
    [post_name] => business-factortable
    [to_ping] =>
    [pinged] =>
    [post_modified] => 2016-05-09 08:26:05
    [post_modified_gmt] => 2016-05-09 08:26:05
    [post_content_filtered] =>
    [post_parent] => 0
    [guid] => http://qw/bigram/bigram/business-factortable/
    [menu_order] => 0
    [post_type] => bigram
    [post_mime_type] =>
    [comment_count] => 0
    [filter] => raw
)
	 */
		
	function map( $post_name, $ID ) {
		if ( !isset( $this->mapping[ $post_name ] ) ) {
			$this->mapping[ $post_name ] = $ID;
		}	else {
			$this->already_mapped++;
		}
	}
	
	/**
	 * Gets the mapped ID
	 */
	function get_mapping( $sword, $bword ) {
		$key = "$sword-$bword";
		$key = sanitize_title( $key );
		$ID = bw_array_get( $this->mapping, $key, null );
		return $ID;
	}
	
	function process() {
		$this->reset_sampled();
		foreach ( $this->posts as $post ) {	
			$this->sampled = array(); 
			$this->sample( $post );
			if ( count( $this->sampled ) ) {
				print_r( $this->sampled );
			}
			
			foreach ( $this->sampled as $key => $sample ) {
				$sword = $sample[ 'sword' ];
				$bword = $sample[ 'bword' ];
				$this->maybe_create_sampled_bigram( $post, $sword, $bword );
			}
		}
	}
	
	/**
	 * Converts SB's to links
	 *
	 * Creates a list of sampled bigrams to be checked
	 *
	 * If the SB is already in a link then we shouldn't need to worry about it.
	 */
	function sample( $post ) {
		echo "Processing {$post->ID} {$post->post_title}" . PHP_EOL;
		$content = $post->post_content;
		$content = $this->process_contents( $content );
		$this->update( $post, $content );
	}
	
	function process_contents( $content ) { 
		$contents = explode( " ", $content );
		//print_r( $contents );
		$sword_index = null;
		foreach ( $contents as $index => $word ) {
			$char = strtolower( substr( $word, 0, 1 ) );
			switch ( $char ) {
				case 'b':
					if ( $sword_index !== null ) {
						$this->make_link( $contents, $sword_index );
					}
					$sword_index = null;
				break;
				case 's':
					$sword_index = $index;
				break;
						
				default:
					$sword_index = null;
			}
		}
		$content = implode( " ", $contents );
		$content = str_replace( "</a> ", "</a>", $content );
		return $content;
	}
	
	/** 
	 * Implements 'the_content' filter 
	 * 
	 * Converts SB's to links
	 * 
	 * @param string $content
	 * @return string converted content
	 */
	function the_content( $content ) {
		$this->reset_sampled();
		$content = $this->process_contents( $content ); 
		return $content;
	}
	
	function report() {
	
	}
	
	/**
	 * Creates a link to the SB bigram
	 * 
	 * Strips the bword from the second word and replaces the sword with the link
	 * <a href="https://qw/bigram/bigram/sword-bword>sword bword</a> 
	 * 
	 */
	function make_link( &$contents, $sword_index ) {
		$sword = $this->get_sbword( $contents[ $sword_index ] );
		$bword = $this->get_sbword( $contents[ $sword_index+1 ] );
		$this->add_sampled( $sword, $bword );
		$this->create_sword_link( $contents, $sword_index, $sword, $bword );
		$this->remove_bword( $contents, $sword_index, $sword, $bword );
	}
	
	/**
	 * Adds a sampled bigram to the sampled array
	 */
	function add_sampled( $sword, $bword ) {
		$this->sampled["$sword $bword"] = array( 'sword' => $sword, 'bword' => $bword );
	}
	
	/**
	 * Gets an S or B word
	 * 
	 * @TODO Check it works for single letter words.
	 * Check if any words contain numbers, hyphens or other strange things
	 * 
	 * @param string $sbwordystuff 
	 * @return string the S or B word
	 */
	function get_sbword( $sbwordystuff ) {
		$bwords = preg_match( "/[sSbB]([a-zA-Z'])+/", $sbwordystuff, $words );
		$sbword = strtolower( $words[0] );
		return $sbword;
	}
	
	/**
	 * Creates the sword link 
	 * 
	 * Replaces the existing sword with a link
	 * 
	 * @param array $contents the contents array
	 * @param integer $sword_index 
	 * @param string $sword the sanitized sword	- lower case
	 * @param string $bword the sanitized bword - lower case ( but may contain apostrophes ? )
	 */
	function create_sword_link( &$contents, $sword_index, $sword, $bword ) {
		$link_text = $contents[ $sword_index ];
		$link_text .= " ";
		$link_text .= substr( $contents[ $sword_index+1 ], 0, strlen( $bword ) );
		$url = site_url( "/bigram/$sword-$bword", "https" );
		$link = retlink( null, $url, $link_text );
		//echo $link . PHP_EOL;
		$contents[ $sword_index ] = $link;  
	}
	
	/**
	 * Removes the bword from contents
	 * 
	 * bwords may be followed by HTML ( e.g. Silver Bullet<!--more--> )
	 * we need to remove the bword - it's already part of the sword link
	 * 
	 * @param array $contents array of words
	 * @param string $sword_index
	 * @param string $sword
	 * @param string $bword
	 */
	function remove_bword( &$contents, $sword_index, $sword, $bword ) {
		$bword_index = $sword_index + 1;
		$new_bword = $contents[ $bword_index ] ;
		$new_bword = substr( $new_bword, strlen( $bword ) );
		$contents[ $bword_index ] = $new_bword;
		//echo $new_bword;
		bw_trace2( $new_bword, "new_bword", false );
	}
	
	/**
	 * Updates the post
	 * 
	 * We pass the new contents, in case it might be useful.
	 * But the current logic is that we don't create links since this can be done dynamically in the front end.
	 * What we do need to do is to set the category for "Sampled Bigram".
	 * We also have to ensure that we don't intercept save_post and end up in a loop.
	 *
	 * @param object $post 
	 * @param string $contents
	 */
	function update( $post, $contents ) {
		$this->set_default_category( $post );
	
	
	}
	
	/**
	 * Sets the default category to "Sampled Bigram"
	 */
	function set_default_category( $post ) {
		$terms = wp_get_object_terms( $post->ID, "category" );
		if ( 0 == count( $terms ) ) {
			wp_add_object_terms( $post->ID, "sampled-bigram", "category" );
			echo "Sampled Bigram: " . $post->post_title . PHP_EOL;
		} else {
			//print_r( $terms );
		}
			
	
	}
	
	/**
	 * Create sampled bigram 
	 *
	 * @param object $post
	 * @param string $sword
	 * @param string $bword
	 */
	function maybe_create_sampled_bigram( $post, $sword, $bword ) {
		$mapped = $this->get_mapping( $sword, $bword );
		if ( !$mapped ) { 
			$post = $this->create_sampled_bigram( $post, $sword, $bword );
			$this->map( $post->post_name, $post->ID );
		}
	}
	
	/**
	 * Creates a sampled bigram
	 *
	 * Damn! It failed to set the Category taxonomy
	 * 
	 * @param object $post the original post object
	 * @param string $sword 
	 * @param string $bword
	 * @return object the sampled post 
	 */
	function create_sampled_bigram( $post, $sword, $bword ) {
		echo "Creating sampled bigram: $sword $bword" . PHP_EOL;
		$content = $this->post_content( $post ); 		
		$title = $this->post_title( $sword, $bword );
		$sampled = array( "post_author" => $post->post_author
										, "post_date" => $post->post_date
										, "post_modified" => $post->post_modified
										, "post_content" => $content
										, "post_title" => $title
										, "post_type" => $post->post_type
										, "post_status" => "publish"
										, "comment_status" => "closed"
										, "ping_status" => "closed"
										);
		$ID = wp_insert_post( $sampled );
		$sampled_post = null;
		if ( $ID ) {
			$sampled_post = get_post( $ID );
		} else {
			echo "Failed to create the sampled post" . PHP_EOL;
			gob();
		}
		return $sampled_post;
	}
	
	function post_content( $post ) {
		$post_content = "<!--more-->Sampled from {$post->post_title}.";
		return $post_content;
	}

	function post_title( $sword, $bword ) {
		$post_title = ucfirst( $sword );
		$post_title .= " ";
		$post_title .= ucfirst( $bword );
		return $post_title;
	}



}
