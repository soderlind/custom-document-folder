<?php
/**
 * Debug information for Custom Document Folder plugin
 * 
 * Add this code to your theme's functions.php temporarily to debug upload paths:
 * 
 * add_action('admin_notices', function() {
 *     if (isset($_GET['cdf_debug'])) {
 *         $settings = get_option('cdf_settings', array());
 *         echo '<div class="notice notice-info"><pre>';
 *         echo "Current Settings:\n";
 *         print_r($settings);
 *         echo "\nUpload Directory:\n";
 *         print_r(wp_upload_dir());
 *         echo '</pre></div>';
 *     }
 * });
 * 
 * Then visit: wp-admin/upload.php?cdf_debug=1
 * 
 * 
 * To test upload filter directly, add this to functions.php:
 * 
 * add_filter('upload_dir', function($upload) {
 *     error_log('Upload dir called: ' . print_r($upload, true));
 *     return $upload;
 * }, 999);
 * 
 * add_filter('wp_handle_upload_prefilter', function($file) {
 *     error_log('File being uploaded: ' . print_r($file, true));
 *     return $file;
 * });
 * 
 * Check your WordPress debug.log file for the output.
 */
