<?php // (C) Copyright Bobbing Wide 2016

/**
 * Class SB - Implements the SB bigrams
 *
 * This might have been better implemented as multiple classes.
 
 * But since this is just a batch process and it's under development
 * it doesn't matter at the moment.
 
 * In the version that will load a single image to the website
 * we need an easier way of identifying the S and B words
 * Given that smart phones and tablets aren't that smart we need to be able to 
 * do it NOT based on the file name of the image.
 * 
 * So a third parse_sb() routine will be needed to create the appropriate image file
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
	
	public $image_dir;
	public $post_mime_type;
	
	/**
	 *
	 * //"c:/apache/htdocs/bigram/images";
	 * 
	 */
	function __construct( $sb, $directory=null ) {
		$this->image_dir = $directory; 
		
		$this->sb = trim( $sb );
		if ( $directory ) {
			$this->parse_sb_image();
		} else {
			$this->parse_sb();
		}
	}
	
	/**
	 * Parse SB image
	 *
	 * Attempt to cater for file names with hyphens instead of spaces and no description.
	 *
	 * e.g. all of these
	 * `
	 
sb-in-a-captcha.jpg
sb.jpg
Scotch Beef b The Scotch Beef Club.jpg
Scotch Box.jpg
Scottish Blossom b Honey from Heather Hills Farms.jpg
Scrabby-Buttock.jpg
Sealed-Bid.jpg
Secret board.jpg
	 * `
	 * 
	 */
	function parse_sb_image() {
		$sbstring = $this->sb;
		$sbstring = str_replace( "-", " ", $sbstring );
		$sbstring = str_replace( ".", " ", $sbstring );
		$words = explode( " ", $sbstring );
		$category = "i";
		//$mapped_category = "Seen By"; 
		switch ( count( $words ) ) {
			case 0:
			case 1:
				gob();
				
			case 2:
				$bpos =strpos( $words[0], "b" );
				$sword = substr( $words[0], 0, $bpos );
				$bword = substr( $words[0], $bpos );
				
				break;
				
			case 3: 
			default:
				$sword = ucfirst( $words[0] );
				$bword = ucfirst( $words[1] );
				break;
			 
		}
		$body_text = $this->sb;		
		
		$mapped_category = $this->map_category( $category );
		echo "$sword,$bword,$category,$mapped_category,$body_text" . PHP_EOL;
		$this->title_text = "$sword $bword";
		$this->sword = strtolower( $sword );
		$this->bword = strtolower( $bword );
		$this->body_text = $body_text;
		$this->get_date_from_body();
		$this->category = $mapped_category;
		
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
		//$sbstring = trim( $sbstring );
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
	 * Set date from image
	 * 
	 * "Y-m-d h:m:s"
	 */
	function set_date_from_image() {
		$image_file = $this->get_image_file_name();
		if ( file_exists( $image_file ) ) {
      $filemtime = filemtime( $image_file );
			$this->post_date = date( "Y-m-d h:m:s", $filemtime );
			echo "$image_file {$this->post_date} " . PHP_EOL;
		} else {
			echo "Image file missing: $image_file" . PHP_EOL;
		}
	}
	
	/** 
	 * Set the post_mime_type
	 */
	function set_post_mime_type( $ext ) {
		$post_mime_types = array( ".jpg" => "image/jpeg" 
														, ".png" => "image/png"
														, ".gif" => "image/gif"
														);
		$this->post_mime_type = bw_array_get( $post_mime_types, $ext, "image" );
	}													
	
	/**
	 * Return the fully qualified image file name
	 *
	 * If you want the basename() just use $this->sb;
	 *
	 * @return string fully qualified image file name
	 */
	function get_image_file_name() {
		$image_file = $this->image_dir;
		$image_file .= '/';
		$image_file .= $this->sb;
		//$image_file = trim( $image_file );
		return( $image_file );
	}
		
	
	/**
	 * Return the post content
	 *
	 * This depends on whether or not it's an image we're attaching
	 * If it is then we adjust the post date to the date of the image file.
	 *
	 * @return string the post content
	 */
	function get_post_content() {
		$post_content = $this->body_text;
		//echo $post_content . "?";
		$ext = substr( $post_content, -4 );		
		$ext = strtolower( $ext );																	
		switch ( $ext ) {
			case ".jpg":
			case ".png":
			case ".gif":
				$post_content = substr( $post_content, 0, -4 );
				$this->set_date_from_image();
				$this->set_post_mime_type( $ext );
				break;
			
			default:
				$post_content .= "<!--more--><br />From the original SB.txt";
		}
		return( $post_content );
	}

	/**
	 * Insert a bigram
	 */
	function insert_bigram( ) {
		$post_content = $this->get_post_content();
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
	
	/**
	 * Set taxonomy values
	 * 
	 * @param ID $id the post to update
	 */
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
	 *  i | image | Seen By	 - photo
	 *  j | jpeg | Surf Bite - screen capture
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
										, "i" => "Seen By"
										, "j" => "Surf Bite"
										);
		$mapped_category = bw_array_get( $mapping, $category, null );
		if ( !$mapped_category ) {
			echo "No mapping for: $category" . PHP_EOL;
		}
		return( $mapped_category );
		
	}
	
	/**
	 * Get the category ID
	 *
	 * This is inefficient for batch processing; the data could be cached
	 *
	 * @param string $slug
	 * @return ID the object's term_id
	 */
	function get_category_id( $slug ) {
    $object = get_category_by_slug( $slug );
		//print_r( $object );
		return( $object->term_id );
	}
	
	/**
	 * Attach the image file to the selected post
	 *
	 * We have to create an attachment as a child of the selected post
	 * and associate the media file to the attachment post.
	 */
	function attach_image( $parent_id ) { 
		$image_file = $this->get_image_file_name();
		$contents = file_get_contents( $image_file );
		$tmp_name = $this->write_tmp_file( $image_file, $contents );
		$attachment_id = $this->create_attachment( $parent_id );
		$this->set_tags( $attachment_id );
		$_REQUEST['post_id'] = $attachment_id;
		$media_file = $this->write_media_file( $this->sb, $this->post_mime_type, $tmp_name, $this->post_date );
		print_r( $media_file );
		$this->update_attachment_metadata( $attachment_id, $media_file['file'] );
	}
	
	/**
	 * Write a temporary file
	 *
	 * WordPress likes to work off temporary files rather than the originals
	 *
	 * @param string $name the full file name from which to create the tmp file name
	 * @param string $contents the contents of the file
	 * @return string the temporary file name
	 */
	function write_tmp_file( $name, $contents ) { 
		$tmp_name = wp_tempnam( $name );
		file_put_contents( $tmp_name, $contents );
		return( $tmp_name );
	}
	
	/**
	 * Insert an attachment
	 *
	 * Note: We have to set the post_mime_type
	 */
	function create_attachment( $parent_id ) {
		$post_content = $this->get_post_content();
		$post = array( "post_type" => "attachment"
								 , "post_title" => $this->title_text
								 , "post_name" => $this->title_text
								 , "post_status" => "inherit"
								 , "post_parent" => $parent_id
								 , "post_content" => $post_content
								 , "post_date" => $this->post_date
								 , "post_modified_date" => $this->post_date
								 , "post_author" => 1
								 , "post_mime_type" => $this->post_mime_type
								 );
		$id = wp_insert_post( $post, true );
		$metadesc = "{$this->title_text} attachment";
		update_post_meta( $id, "_yoast_wpseo_metadesc", $metadesc );
		update_post_meta( $id, "_yoast_wpseo_focuskw", $metadesc );
		echo "Created attachment $id for {$this->title_text} {$this->post_date}" . PHP_EOL;
		return( $id );
	}
	
	/**
	 * Write the media file
	 *
	 * 
	 */
	function write_media_file( $name, $type, $tmp_file, $time ) {
		bw_trace2();
		echo " $name $type $tmp_file $time " . PHP_EOL;
		$file = array();
		$file['name'] = $name;
		$file['type'] = $type;
		$file['tmp_name'] = $tmp_file;
		$overrides = array( "test_form" => false, "test_size" => false );                                    
		$media_file = wp_handle_sideload( $file, $overrides, $time );
		bw_trace2( $media_file, "media_file" );
		//print_r( $media_file );
		return( $media_file ); 
	}
	
	/**
	 * Update attachment metadata
	 *
	 * @param ID $target_id ID of the attachment post 
	 * @param string $media_file full file name of the media file
	 */
	function update_attachment_metadata( $target_id, $media_file ) {
		$metadata = wp_generate_attachment_metadata( $target_id, $media_file );
		//print_r( $metadata );
		bw_trace2( $metadata, "attachment_metadata" );
		wp_update_attachment_metadata( $target_id, $metadata );
		$attached_file = bw_array_get( $metadata, 'file', null );
		echo "attached_file: $attached_file" . PHP_EOL;
		if ( $attached_file ) {
			update_post_meta( $target_id, "_wp_attached_file" , $attached_file );
		}
	}  

}			
