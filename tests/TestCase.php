<?php
/**
 * Base test case for Custom Document Folder plugin
 *
 * @package CustomDocumentFolder
 */

namespace CustomDocumentFolder\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Base test case class
 */
abstract class TestCase extends PHPUnitTestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Plugin instance
	 *
	 * @var \Soderlind\DocumentFolder\Upload_Document
	 */
	protected $plugin;

	/**
	 * WordPress options storage
	 */
	protected $options = array();

	/**
	 * Setup test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Reset options storage
		$this->options = array();

		// Mock WordPress functions
		$this->mockWordPressFunctions();

		// Load plugin
		$this->plugin = $this->loadPlugin();
	}

	/**
	 * Teardown test environment
	 */
	protected function tearDown(): void {
		Monkey\tearDown();

		// Reset global variables
		$_FILES = array();

		parent::tearDown();
	}

	/**
	 * Mock common WordPress functions
	 */
	protected function mockWordPressFunctions() {
		// Mock get_option
		Monkey\Functions\when( 'get_option' )->alias( function ( $option, $default = false ) {
			return $this->options[ $option ] ?? $default;
		} );

		// Mock update_option
		Monkey\Functions\when( 'update_option' )->alias( function ( $option, $value ) {
			$this->options[ $option ] = $value;
			return true;
		} );

		// Mock add_option
		Monkey\Functions\when( 'add_option' )->alias( function ( $option, $value ) {
			if ( ! isset( $this->options[ $option ] ) ) {
				$this->options[ $option ] = $value;
				return true;
			}
			return false;
		} );

		// Mock delete_option
		Monkey\Functions\when( 'delete_option' )->alias( function ( $option ) {
			unset( $this->options[ $option ] );
			return true;
		} );

		// Mock wp_upload_dir
		Monkey\Functions\when( 'wp_upload_dir' )->justReturn( array(
			'path'    => '/tmp/wp-content/uploads/2025/11',
			'url'     => 'http://example.com/wp-content/uploads/2025/11',
			'subdir'  => '/2025/11',
			'basedir' => '/tmp/wp-content/uploads',
			'baseurl' => 'http://example.com/wp-content/uploads',
			'error'   => false,
		) );

		// Mock wp_check_filetype
		Monkey\Functions\when( 'wp_check_filetype' )->alias( function ( $filename ) {
			$ext        = pathinfo( $filename, PATHINFO_EXTENSION );
			$mime_types = array(
				'pdf'  => 'application/pdf',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'doc'  => 'application/msword',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'xls'  => 'application/vnd.ms-excel',
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'ppt'  => 'application/vnd.ms-powerpoint',
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
			);
			return array(
				'ext'  => $ext,
				'type' => $mime_types[ $ext ] ?? false,
			);
		} );

		// Mock get_allowed_mime_types
		Monkey\Functions\when( 'get_allowed_mime_types' )->justReturn( array(
			'pdf'      => 'application/pdf',
			'doc'      => 'application/msword',
			'docx'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls'      => 'application/vnd.ms-excel',
			'xlsx'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'ppt'      => 'application/vnd.ms-powerpoint',
			'pptx'     => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
		) );

		// Mock sanitize_text_field
		Monkey\Functions\when( 'sanitize_text_field' )->alias( function ( $str ) {
			return strip_tags( trim( $str ) );
		} );

		// Mock sanitize_file_name
		Monkey\Functions\when( 'sanitize_file_name' )->alias( function ( $filename ) {
			return preg_replace( '/[^a-zA-Z0-9._-]/', '', $filename );
		} );

		// Mock wp_mkdir_p
		Monkey\Functions\when( 'wp_mkdir_p' )->justReturn( true );

		// Mock WordPress actions/filters
		Monkey\Functions\when( 'add_action' )->justReturn( true );
		Monkey\Functions\when( 'add_filter' )->justReturn( true );
		Monkey\Functions\when( 'has_filter' )->justReturn( 20 );
		Monkey\Functions\when( 'has_action' )->justReturn( 10 );
		Monkey\Functions\when( 'is_admin' )->justReturn( true );
		Monkey\Functions\when( 'register_setting' )->justReturn( true );
		Monkey\Functions\when( 'add_settings_section' )->justReturn( true );
		Monkey\Functions\when( 'add_settings_field' )->justReturn( true );
		Monkey\Functions\when( 'add_options_page' )->justReturn( true );
		Monkey\Functions\when( '__' )->returnArg();
		Monkey\Functions\when( 'esc_html__' )->returnArg();
		Monkey\Functions\when( 'esc_html_e' )->alias( function ( $text ) {
			echo $text; } );
		Monkey\Functions\when( 'esc_html' )->returnArg();
		Monkey\Functions\when( 'esc_attr' )->returnArg();
		Monkey\Functions\when( 'esc_js' )->returnArg();
		Monkey\Functions\when( 'esc_url' )->returnArg();
		Monkey\Functions\when( 'register_activation_hook' )->justReturn( true );
		Monkey\Functions\when( 'register_deactivation_hook' )->justReturn( true );
	}

	/**
	 * Load plugin class
	 */
	protected function loadPlugin() {
		if ( ! class_exists( 'Soderlind\DocumentFolder\Upload_Document' ) ) {
			require_once CDF_PLUGIN_DIR . '/custom-document-folder.php';
		}

		// Create new instance (plugin doesn't use singleton pattern)
		return new \Soderlind\DocumentFolder\Upload_Document();
	}

	/**
	 * Helper method to set plugin settings
	 *
	 * @param array $extensions Array of extensions
	 */
	protected function set_plugin_settings( $extensions ) {
		$this->options[ 'cdf_selected_extensions' ] = $extensions;
		// Reload plugin with new settings
		$this->plugin = $this->loadPlugin();
	}

	/**
	 * Helper method to get plugin settings
	 *
	 * @return array Settings array
	 */
	protected function get_plugin_settings() {
		return $this->options[ 'cdf_selected_extensions' ] ?? array( 'pdf' );
	}

	/**
	 * Helper method to create a mock file array
	 *
	 * @param string $filename Filename with extension
	 * @param string $mime_type MIME type
	 * @return array Mock file array
	 */
	protected function create_mock_file( $filename, $mime_type = '' ) {
		return array(
			'name'     => $filename,
			'type'     => $mime_type,
			'tmp_name' => '/tmp/' . $filename,
			'error'    => 0,
			'size'     => 1024,
		);
	}

	/**
	 * Helper method to create a mock upload directory array
	 *
	 * @return array Mock upload directory
	 */
	protected function create_mock_upload_dir() {
		return array(
			'path'    => '/tmp/wp-content/uploads/2025/11',
			'url'     => 'http://example.com/wp-content/uploads/2025/11',
			'subdir'  => '/2025/11',
			'basedir' => '/tmp/wp-content/uploads',
			'baseurl' => 'http://example.com/wp-content/uploads',
			'error'   => false,
		);
	}
}
