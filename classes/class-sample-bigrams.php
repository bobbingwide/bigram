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
	public $echo;
	
	
	/**
	 * 
	 */
	function __construct() {
		$this->mapping = array();
		$this->already_mapped = 0;
		$this->posts = null;
		$this->echo = false;
		
	}
	
	function reset_sampled() {
		$this->sampled = array();
	}
	
	/**
	 * Sets echoing for batch
	 *
	 * @param bool Set to true when echoing is required
	 */
	function set_echo( $echo=true ) {
		$this->echo = $echo;
	}
	
	function echo( $string ) {
		if ( $this->echo ) {
			echo $string;
			echo PHP_EOL; 
		}
	}
		
	
	/**
	 * Load all the bigram posts
	 * 
	 * There are thousands. How long does this take? 
	 */
	function load() {
		oik_require( "includes/bw_posts.php" );
		$args = array( "post_type" => "bigram" 
								 , "numberposts" => -1
								 , "orderby" => "date"
								 , "order" => "asc"
								 );
		$posts = bw_get_posts( $args );
		$this->echo( count( $posts ) );
		$this->posts = $posts;
	}
	
	function get_default_category() {
		//$this->default_category = 2147;
		$this->default_category = get_term_by( "slug", "sampled-bigram", "category" );
		//print_r( $this->default_category );
		
	}
	
	/**
	 * Map post titles to ID
	 * 
	 *  
	 */ 
	function map_posts() {
		foreach ( $this->posts as $post ) {	 
			$this->map( $post->post_name, $post->ID );
		}
		$this->echo( count( $this->mapping ) );
		$this->echo( $this->already_mapped );
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
	 * Gets the key for the post_name
	 */
	function get_key( $sword, $bword ) {
		$key = "$sword-$bword";
		$key = sanitize_title( $key );
		return $key;
	}
	
	/**
	 * Gets the mapped ID
	 */
	function get_mapping( $sword, $bword ) {
		$key = $this->get_key( $sword, $bword );
		$ID = bw_array_get( $this->mapping, $key, null );
		return $ID;
	}
	
	/**
	 * Process the array of posts
	 */
	function process() {
		$this->reset_sampled();
		foreach ( $this->posts as $post ) {	
			$this->sampled = array(); 
			$this->sample( $post );
			if ( count( $this->sampled ) ) {
				$this->echo( print_r( $this->sampled, true ) );
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
		bw_trace2();
		$this->echo( "Processing {$post->ID} {$post->post_title}" );
		$content = $post->post_content;
		$content = $this->process_contents( $content );
		$this->update( $post, $content );
	}
	
	/**
	 * Processes content looking for SB pairs
	 * 
	 * @TODO Needs to ignore HTML attributes.
	 * 
	 * @param string $content
	 * @return string processed contents
	 */
	function process_contents( $content ) { 
		$contents = explode( " ", $content );
		//bw_trace2( $contents, "contents", false);
		$sword_index = null;
		$waitforgt = false;
		foreach ( $contents as $index => $word ) {
			$char = strtolower( substr( $word, 0, 1 ) );
			switch ( $char ) {
				case '<':
					$waitforgt = true;
					$sword_index = null;
				break;

				case 'b':
					if ( $sword_index !== null ) {
						//bw_trace2( $sword_index, "!$word $sword_index^", false );
						$this->make_link( $contents, $sword_index );
					}
					$sword_index = null;
				break;

				case 's':
					if ( !$waitforgt ) {
						//bw_trace2( $index, "index of s", false );
						$sword_index = $index;
					}
				break;
						
				default:
					$sword_index=null;

			}
			if ( $waitforgt ) {
				$pos=strpos( $word, '>' );
				if ( false !== $pos && false === strpos( $word, '<' ) ) {
					$waitforgt = false;
				}
			}
		}

		$content = implode( " ", $contents );
		$content = str_replace( "</a> ", "</a>", $content );
		$content = str_replace( " ,", ",", $content );
		$content = str_replace( " .", ".", $content );
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
		//bw_trace2( $sbwordystuff, "sbwordystuff", false );
		$bwords = preg_match( "/[sSbB]([a-zA-Z'])*/", $sbwordystuff, $words );
		//if ( count( $words )) {
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
		//$url = "https://bigram.co.uk/bigram/$sword-$bword" ;
		$link = retlink( null, $url, $link_text );
		
		//$link = '<em>' . $link_text . '</em>';
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
	 * Also:
	 * - correct _thumbnail_id if < 1
	 * - correct title - if null
	 * - correct _wp_attached_file
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
			$this->echo( "Sampled Bigram: " . $post->post_title );
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
			$existing_post = $this->maybe_retrieve_post( $post, $sword, $bword );
			if ( !$existing_post ) { 
				$new_post = $this->create_sampled_bigram( $post, $sword, $bword );
			}
			$this->map( $post->post_name, $post->ID );
		}
	}
	
	/**
	 * Attempts to retrieve the post by post_name
	 *
	 */
	function maybe_retrieve_post( $post, $sword, $bword ) {
	
		oik_require( "includes/bw_posts.php" );
		$post_name = $this->get_key( $sword, $bword );
		$args = array( "post_type" => "bigram"
								 , "name" => $post_name
                 , "numberposts" => 1
								 , "orderby" => "date"
								 , "order" => "asc"
								 );
		$posts = bw_get_posts( $args );
		if ( $posts ) {
			$existing_post = bw_array_get( $posts, 0, null );
		} else {
			$existing_post = null;
		}
		return $existing_post;
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
		$this->echo( "Creating sampled bigram: $sword $bword" );
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
			$this->set_default_category( $sampled_post );
		} else {
			$this->echo( "Failed to create the sampled post" );
			bw_trace2( "gob()" );
			
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
	
	/**
	 * Samples a post after insert / update
	 *
	 * - If it's an insert then we might not want to sample. @TODO Check this.
	 * - If it's already a sample then we don't want to sample it again.
	 * - This tries to avoid multiple inserts.
	 * - Think about attachment IDs - should they be populated to mapped posts.
	 * 
	 * 
	 * @param integer $post_ID ID of the post
	 * @param object $post
	 * @param bool $update
   */
	function sample_post( $post_ID, $post, $update ) {
		bw_trace2();
		if ( $update ) {
			if ( false === strpos( $post->post_content, "<!--more-->Sampled from" ) ) {
				$this->posts = array( $post );
				$this->map_posts();
				$this->process();
			}
		}
		
	}



}
