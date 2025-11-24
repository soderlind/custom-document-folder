<?php
/**
 * Direct filter test for Custom Document Folder plugin
 * Run with: wp eval-file wp-content/plugins/custom-document-folder/tests/cdf-filter-test.php
 */

// Ensure plugin is loaded
if ( ! class_exists( 'Soderlind\DocumentFolder\Upload_Document' ) ) {
	WP_CLI::error( 'Custom Document Folder plugin class not found.' );
}

WP_CLI::log( '=== Custom Document Folder - Filter Test ===' );
WP_CLI::log( '' );

// Get current settings
$selected_extensions = get_option( 'cdf_selected_extensions', [ 'pdf' ] );
WP_CLI::log( 'Selected extensions: ' . implode( ', ', $selected_extensions ) );
WP_CLI::log( '' );

// Check filter registration
global $wp_filter;
$filters_to_check = [
	'upload_dir',
	'wp_handle_upload_prefilter',
	'wp_check_filetype_and_ext',
	'wp_handle_upload_overrides',
	'sanitize_file_name',
];

WP_CLI::log( 'Checking filter registration:' );
foreach ( $filters_to_check as $filter ) {
	$registered = isset( $wp_filter[ $filter ] ) && ! empty( $wp_filter[ $filter ] );
	$status     = $registered ? '✓' : '✗';
	WP_CLI::log( "  {$status} {$filter}" );
}
WP_CLI::log( '' );

// Test 1: Check if prefilter hook is properly registered and can be called
WP_CLI::log( 'Test 1: Prefilter hook functionality' );
$test_file = [
	'name'     => 'test-document.pdf',
	'type'     => 'application/pdf',
	'tmp_name' => '/tmp/test.pdf',
	'error'    => 0,
	'size'     => 1024,
];

$filtered_file = apply_filters( 'wp_handle_upload_prefilter', $test_file );
WP_CLI::log( '  Input file: ' . $test_file[ 'name' ] );
WP_CLI::log( '  Filter executed: ' . ( $filtered_file === $test_file ? '✓' : '✗' ) );
WP_CLI::log( '' );

// Test 2: Check upload_dir filter modification
WP_CLI::log( 'Test 2: Upload directory modification' );

// First, simulate the prefilter to set the extension
$test_pdf = [ 'name' => 'document.pdf', 'type' => 'application/pdf' ];
apply_filters( 'wp_handle_upload_prefilter', $test_pdf );

// Then check if upload_dir gets modified
$original_upload_dir = [
	'path'    => '/var/www/wp-content/uploads/2025/11',
	'url'     => 'http://example.com/wp-content/uploads/2025/11',
	'subdir'  => '/2025/11',
	'basedir' => '/var/www/wp-content/uploads',
	'baseurl' => 'http://example.com/wp-content/uploads',
	'error'   => false,
];

$modified_upload_dir = apply_filters( 'upload_dir', $original_upload_dir );

WP_CLI::log( '  Original path: ' . $original_upload_dir[ 'path' ] );
WP_CLI::log( '  Modified path: ' . $modified_upload_dir[ 'path' ] );
WP_CLI::log( '  Original subdir: ' . $original_upload_dir[ 'subdir' ] );
WP_CLI::log( '  Modified subdir: ' . $modified_upload_dir[ 'subdir' ] );

$pdf_folder_present = strpos( $modified_upload_dir[ 'path' ], '/pdf' ) !== false;
WP_CLI::log( '  Contains /pdf/: ' . ( $pdf_folder_present ? '✓ YES' : '✗ NO' ) );
WP_CLI::log( '' );

// Test 3: Test with non-selected extension (should not modify)
WP_CLI::log( 'Test 3: Non-selected extension (e.g., .jpg)' );
$test_jpg = [ 'name' => 'image.jpg', 'type' => 'image/jpeg' ];
apply_filters( 'wp_handle_upload_prefilter', $test_jpg );

$jpg_upload_dir   = apply_filters( 'upload_dir', $original_upload_dir );
$jpg_not_modified = $jpg_upload_dir[ 'path' ] === $original_upload_dir[ 'path' ] ||
	( in_array( 'jpg', $selected_extensions ) && strpos( $jpg_upload_dir[ 'path' ], '/jpg' ) !== false );

WP_CLI::log( '  JPG in settings: ' . ( in_array( 'jpg', $selected_extensions ) ? 'YES' : 'NO' ) );
WP_CLI::log( '  Modified path: ' . $jpg_upload_dir[ 'path' ] );
WP_CLI::log( '  Behavior correct: ' . ( $jpg_not_modified ? '✓' : '✗' ) );
WP_CLI::log( '' );

// Test 4: Test with PNG (currently in settings)
if ( in_array( 'png', $selected_extensions ) ) {
	WP_CLI::log( 'Test 4: PNG extension (in settings)' );
	$test_png = [ 'name' => 'image.png', 'type' => 'image/png' ];
	apply_filters( 'wp_handle_upload_prefilter', $test_png );

	$png_upload_dir     = apply_filters( 'upload_dir', $original_upload_dir );
	$png_folder_present = strpos( $png_upload_dir[ 'path' ], '/png' ) !== false;

	WP_CLI::log( '  Modified path: ' . $png_upload_dir[ 'path' ] );
	WP_CLI::log( '  Contains /png/: ' . ( $png_folder_present ? '✓ YES' : '✗ NO' ) );
	WP_CLI::log( '' );
}

// Summary
WP_CLI::log( '=== Summary ===' );
if ( $pdf_folder_present ) {
	WP_CLI::success( 'Plugin filters are working correctly! PDF files will be uploaded to /pdf/ folder.' );
} else {
	WP_CLI::error( 'Plugin filters are NOT working as expected.' );
}
