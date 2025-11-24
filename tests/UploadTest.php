<?php
/**
 * Test file upload directory modification
 *
 * @package CustomDocumentFolder
 */

namespace CustomDocumentFolder\Tests;

/**
 * Test upload directory modification
 */
class UploadTest extends TestCase {

	/**
	 * Test PDF file is redirected to /pdf folder
	 */
	public function test_pdf_file_redirected_to_pdf_folder() {
		// Set PDF as selected extension
		$this->set_plugin_settings( array( 'pdf' ) );

		// Create mock file
		$file = $this->create_mock_file( 'test-document.pdf', 'application/pdf' );

		// Simulate prefilter hook
		$filtered_file = $this->plugin->wp_handle_upload_prefilter( $file );

		// Get modified upload directory
		$upload_dir = $this->create_mock_upload_dir();
		$result     = $this->plugin->upload_dir( $upload_dir );

		// Assert path was modified
		$this->assertStringContainsString( '/pdf', $result[ 'path' ] );
		$this->assertStringContainsString( '/pdf', $result[ 'url' ] );
		$this->assertEquals( '/pdf', $result[ 'subdir' ] );
	}

	/**
	 * Test non-document file is not redirected
	 */
	public function test_non_document_file_not_redirected() {
		// Set PDF as selected extension (jpg not selected)
		$this->set_plugin_settings( array( 'pdf' ) );

		// Create mock JPG file
		$file = $this->create_mock_file( 'test-image.jpg', 'image/jpeg' );

		// Simulate prefilter hook
		$this->plugin->wp_handle_upload_prefilter( $file );

		// Get upload directory
		$upload_dir = $this->create_mock_upload_dir();
		$result     = $this->plugin->upload_dir( $upload_dir );

		// Assert path was NOT modified
		$this->assertEquals( $upload_dir[ 'path' ], $result[ 'path' ] );
		$this->assertEquals( $upload_dir[ 'subdir' ], $result[ 'subdir' ] );
	}

	/**
	 * Test multiple document types configuration
	 */
	public function test_multiple_document_types() {
		// Set multiple extensions
		$this->set_plugin_settings( array( 'pdf', 'docx' ) );

		// Test PDF
		$pdf_file = $this->create_mock_file( 'document.pdf', 'application/pdf' );
		$this->plugin->wp_handle_upload_prefilter( $pdf_file );
		$result = $this->plugin->upload_dir( $this->create_mock_upload_dir() );
		$this->assertEquals( '/pdf', $result[ 'subdir' ] );

		// Test DOCX
		$docx_file = $this->create_mock_file( 'document.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' );
		$this->plugin->wp_handle_upload_prefilter( $docx_file );
		$result = $this->plugin->upload_dir( $this->create_mock_upload_dir() );
		$this->assertEquals( '/docx', $result[ 'subdir' ] );
	}

	/**
	 * Test wp_check_filetype_and_ext method
	 */
	public function test_filetype_check() {
		// Set PDF as selected extension
		$this->set_plugin_settings( array( 'pdf' ) );

		// Simulate prefilter first
		$file = $this->create_mock_file( 'document.pdf', 'application/pdf' );
		$this->plugin->wp_handle_upload_prefilter( $file );

		// Test filetype check
		$data   = array( 'ext' => false, 'type' => false, 'proper_filename' => false );
		$result = $this->plugin->wp_check_filetype_and_ext( $data, '/tmp/test.pdf', 'document.pdf', null );

		$this->assertEquals( 'pdf', $result[ 'ext' ] );
		$this->assertEquals( 'application/pdf', $result[ 'type' ] );
	}
}
