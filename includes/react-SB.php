<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2022
 */


/**
* Displays the react-SB interface for viewing SB's
 *
 * Copied from genesis-sb's page-sb.php
 *
* All we want to do here is to deliver the react-SB JavaScript
* and a div with id='root' into which the JavaScript is run
* and style it a bit.
 *
 * This is an alternative to using viewScript in the block.json file
 * which also requires an asset.php file with the same name.
 *
*/
function bigram_render_react_sb( $attributes ) {
	//wp_enqueue_script( 'bigram_reactsb_view_script');
	$st = genesis_SB_react_update();
	//echo WP_PLUGIN_URL;
	$asset_url = WP_PLUGIN_URL . '/bigram/includes';
	//echo $asset_url;
	// I couldn't get the code to work when enqueuing the JavaScript by this method.
	// But it does work with inline JavaScript below.

	wp_register_script( "react-SB", $asset_url . "/js/react-SB.js", array(), $st );
	//wp_enqueue_script( "react-SB" );
	wp_enqueue_style( "react-SB", $asset_url . "/css/react-SB.css", array(), $st );

	// This is the div that the reactSB JavaScript uses.
	$html = '<div id="root"></div>';
	$bundle_url = $asset_url . '/js/react-SB.js';
	$html .= '<script src="'. $bundle_url .'"></script>';
	return $html;
}

/**
 * Wraps the reactSB code in a WordPress/Gutenberg block.
 *
 */
function bigram_react_sb( $attributes ) {
	$html = bigram_render_react_sb( $attributes );
	$align_class_name = empty($attributes['textAlign']) ? '' : "has-text-align-{$attributes['textAlign']}";
	$extra_attributes = ['class' => $align_class_name];
	$wrapper_attributes = get_block_wrapper_attributes($extra_attributes);
	$html = sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$html
	);
	return $html;
}

/**
 * Update our files from the react-SB repository
 *
 * If the react-SB/public directory exists
 * /apache/htdocs/react-SB/public
 */
function genesis_SB_react_update() {
	//echo ABSPATH . PHP_EOL;
	$upabit = dirname( ABSPATH );
	$react_SB_public = $upabit . '/react-SB/public';
	//echo $react_SB_public . PHP_EOL;
	if ( is_dir( $react_SB_public ) ) {
		$st1 = genesis_SB_react_update_maybe_copy( $react_SB_public, __DIR__, "/bundle.js", "/js/react-SB.js" );
		$st2 = genesis_SB_react_update_maybe_copy( $react_SB_public, __DIR__, "/css/react-SB.css", "/css/react-SB.css" );
	}	else {
		$st1 = filemtime( __DIR__ . "/js/react-SB.js" );
		$st2 = filemtime( __DIR__ . "/css/react-SB.css" );
	}
	$st = max( $st1, $st2 );
	return( $st );
}

/**
 * Copy a file if necessary
 *
 * Copy the source file to the target file if newer
 * returning the timestamp of the most recent file
 *
 * We always expect both files to be present, so we should be happy with warning.
 *
 */
function genesis_SB_react_update_maybe_copy( $source_dir, $target_dir, $source_file, $target_file ) {
	$source_time = filemtime( $source_dir . $source_file );
	$target_time = filemtime( $target_dir . $target_file );
	if ( $source_time > $target_time ) {
		copy( $source_dir . $source_file, $target_dir . $target_file );
		p( "File refreshed from source" );
		p( "$source_dir $source_file $source_time $target_time" );
	}
	return( $source_time );
}

