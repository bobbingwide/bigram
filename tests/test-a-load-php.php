<?php

/**
 * @package bigram
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the PHP files for PHP 8.2
 */
class Tests_load_php extends BW_UnitTestCase
{

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp(): void
	{
		parent::setUp();

	}

	/** Don't load the admin files - they're only used in batch */
	function dont_test_load_admin_php() {

		$files = glob( 'admin/*.php');
		//print_r( $files );

		foreach ( $files as $file ) {
			switch ( $file ) {
				default:
					oik_require( $file, 'bigram');
			}

		}
		$this->assertTrue( true );


	}
	function test_load_includes_php() {
		$files = glob( 'includes/*.php');
		//print_r( $files );
		foreach ( $files as $file ) {
			switch ( $file ) {
				default:
					oik_require( $file, 'bigram');
			}

		}
		$this->assertTrue( true );

	}

	function test_load_classes_php() {
		$files = glob( 'classes/*.php');
		//print_r( $files );

		foreach ( $files as $file ) {
			switch ( $file ) {

				case '':

					break;


				default:
					oik_require( $file, 'bigram');
			}

		}
		$this->assertTrue( true );

	}

	function test_load_plugin() {
		oik_require( 'bigram.php', 'bigram' );
		$this->assertTrue( true );
	}

}
