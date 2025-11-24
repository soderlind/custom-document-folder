<?php
/**
 * Real upload test using media_handle_sideload
 * Run with: wp eval-file wp-content/plugins/custom-document-folder/tests/cdf-real-upload-test.php
 */

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

WP_CLI::log( '=== Real Upload Test ===' );
WP_CLI::log( '' );

// Get settings
$selected = get_option( 'cdf_selected_extensions', [ 'pdf' ] );
WP_CLI::log( 'Selected extensions: ' . implode( ', ', $selected ) );
WP_CLI::log( '' );

$tests = [
	[ 'name' => 'test-pdf-' . time() . '.pdf', 'type' => 'application/pdf', 'should_redirect' => in_array( 'pdf', $selected ) ],
	[ 'name' => 'test-png-' . time() . '.png', 'type' => 'image/png', 'should_redirect' => in_array( 'png', $selected ) ],
	[ 'name' => 'test-jpg-' . time() . '.jpg', 'type' => 'image/jpeg', 'should_redirect' => in_array( 'jpg', $selected ) ],
];

$results = [];

foreach ( $tests as $test ) {
	$ext = pathinfo( $test[ 'name' ], PATHINFO_EXTENSION );
	WP_CLI::log( "Testing: {$test[ 'name' ]}" );

	// Create temp file
	$tmp_file = wp_tempnam( $test[ 'name' ] );
	file_put_contents( $tmp_file, 'test content for ' . $test[ 'name' ] );

	// Use media_handle_sideload
	$file_array = [
		'name'     => $test[ 'name' ],
		'tmp_name' => $tmp_file,
		'type'     => $test[ 'type' ],
		'error'    => 0,
		'size'     => filesize( $tmp_file ),
	];

	$attachment_id = media_handle_sideload( $file_array, 0 );

	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::warning( '  Upload failed: ' . $attachment_id->get_error_message() );
		$results[] = [ 'file' => $test[ 'name' ], 'status' => 'failed', 'error' => $attachment_id->get_error_message() ];
		@unlink( $tmp_file );
		continue;
	}

	$file_path         = get_attached_file( $attachment_id );
	$has_custom_folder = strpos( $file_path, "/{$ext}/" ) !== false;

	$expected = $test[ 'should_redirect' ] ? "/{$ext}/" : '(standard uploads folder)';
	$actual   = $has_custom_folder ? "/{$ext}/" : '(standard uploads folder)';
	$passed   = ( $has_custom_folder === $test[ 'should_redirect' ] );

	WP_CLI::log( "  Expected: {$expected}" );
	WP_CLI::log( "  Actual: {$actual}" );
	WP_CLI::log( "  File: {$file_path}" );
	WP_CLI::log( "  Status: " . ( $passed ? '✓ PASS' : '✗ FAIL' ) );
	WP_CLI::log( '' );

	$results[] = [
		'file'   => $test[ 'name' ],
		'status' => $passed ? 'passed' : 'failed',
		'path'   => $file_path,
	];

	// Clean up
	wp_delete_attachment( $attachment_id, true );
}

// Summary
WP_CLI::log( '=== Summary ===' );
$passed = array_filter( $results, function ( $r ) {
	return $r[ 'status' ] === 'passed'; } );
$failed = array_filter( $results, function ( $r ) {
	return $r[ 'status' ] === 'failed'; } );

WP_CLI::log( 'Total tests: ' . count( $results ) );
WP_CLI::log( 'Passed: ' . count( $passed ) );
WP_CLI::log( 'Failed: ' . count( $failed ) );

if ( count( $failed ) === 0 ) {
	WP_CLI::success( 'All tests passed!' );
} else {
	WP_CLI::warning( 'Some tests failed.' );
}
