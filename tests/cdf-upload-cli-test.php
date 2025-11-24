<?php
// Run with: wp eval-file tests/cdf-upload-cli-test.php

// Ensure WP is loaded by wp eval-file.
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '/dev/stderr' );

// 1. Locate the plugin class and ensure it’s active.
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! is_plugin_active( 'custom-document-folder/custom-document-folder.php' ) ) {
	WP_CLI::error( 'Custom Document Folder plugin is not active.' );
}

// 2. Prepare a temp PDF file to upload.
$filename = 'cdf-test-' . time() . '.pdf';
$tmp_file = wp_tempnam( $filename );
file_put_contents( $tmp_file, '%PDF-1.4 test pdf' ); // tiny dummy content

if ( ! file_exists( $tmp_file ) ) {
	WP_CLI::error( 'Temp file creation failed.' );
}// 3. Use wp_handle_upload to simulate a real upload.
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Simulate $_FILES array structure
$_FILES[ 'test_file' ] = array(
	'name'     => $filename,
	'tmp_name' => $tmp_file,
	'type'     => 'application/pdf',
	'error'    => 0,
	'size'     => filesize( $tmp_file ),
);

// Debug: Check if filter is registered
global $wp_filter;
if ( isset( $wp_filter[ 'wp_handle_upload_prefilter' ] ) ) {
	WP_CLI::log( 'Filter wp_handle_upload_prefilter is registered.' );
} else {
	WP_CLI::error( 'Filter wp_handle_upload_prefilter is NOT registered.' );
}

// Try wp_handle_sideload directly
$overrides = array( 'test_form' => false );
$movefile  = wp_handle_sideload( $_FILES[ 'test_file' ], $overrides );

if ( isset( $movefile[ 'error' ] ) ) {
	WP_CLI::error( 'wp_handle_sideload failed: ' . $movefile[ 'error' ] );
}

WP_CLI::log( 'wp_handle_sideload success. File moved to: ' . $movefile[ 'file' ] );

// Now create attachment manually to mimic media_handle_sideload
$attachment_id = wp_insert_attachment( array(
	'guid'           => $movefile[ 'url' ],
	'post_mime_type' => $movefile[ 'type' ],
	'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $movefile[ 'file' ] ) ),
	'post_content'   => '',
	'post_status'    => 'inherit',
), $movefile[ 'file' ] );

if ( is_wp_error( $attachment_id ) ) {
	WP_CLI::error( 'Media import failed: ' . $attachment_id->get_error_message() );
}

// 4. Inspect attachment file path.
$file = get_attached_file( $attachment_id );

WP_CLI::log( 'Attachment ID: ' . $attachment_id );
WP_CLI::log( 'Attachment file: ' . $file );

// Check if the path contains /pdf/
if ( strpos( $file, '/pdf/' ) !== false ) {
	WP_CLI::success( 'SUCCESS: File uploaded to correct folder (/pdf/).' );
} else {
	WP_CLI::warning( 'FAILURE: Upload path is NOT correct; file is at: ' . $file );
}

// Clean up
wp_delete_attachment( $attachment_id, true );
// Note: wp_handle_upload moves the file, so $tmp_file is gone.
if ( file_exists( $tmp_file ) ) {
	@unlink( $tmp_file );
}
