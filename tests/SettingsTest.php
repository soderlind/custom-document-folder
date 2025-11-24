<?php
/**
 * Test settings sanitization and validation
 *
 * @package CustomDocumentFolder
 */

namespace CustomDocumentFolder\Tests;

/**
 * Test settings functionality
 */
class SettingsTest extends TestCase {

	/**
	 * Test valid settings are saved correctly
	 */
	public function test_valid_settings_are_saved() {
		$input = array( 'pdf', 'docx' );

		$sanitized = $this->plugin->sanitize_extensions( $input );

		$this->assertIsArray( $sanitized );
		$this->assertCount( 2, $sanitized );
		$this->assertEquals( 'pdf', $sanitized[ 0 ] );
		$this->assertEquals( 'docx', $sanitized[ 1 ] );
	}

	/**
	 * Test invalid input returns default
	 */
	public function test_invalid_input_returns_default() {
		$input = 'not-an-array';

		$sanitized = $this->plugin->sanitize_extensions( $input );

		$this->assertIsArray( $sanitized );
		$this->assertCount( 1, $sanitized );
		$this->assertEquals( 'pdf', $sanitized[ 0 ] );
	}

	/**
	 * Test extensions are sanitized
	 */
	public function test_extensions_are_sanitized() {
		$input = array( 'pdf', '<script>alert("xss")</script>', 'docx' );

		$sanitized = $this->plugin->sanitize_extensions( $input );

		$this->assertIsArray( $sanitized );
		$this->assertCount( 3, $sanitized );
		$this->assertEquals( 'pdf', $sanitized[ 0 ] );
		// strip_tags removes tags but keeps content
		$this->assertEquals( 'alert("xss")', $sanitized[ 1 ] );
		$this->assertEquals( 'docx', $sanitized[ 2 ] );
	}
}
