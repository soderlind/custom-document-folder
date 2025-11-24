<?php
/**
 * PHPUnit bootstrap file for Custom Document Folder plugin tests
 */

// Composer autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Set the plugin directory
define( 'CDF_PLUGIN_DIR', dirname( __DIR__ ) );
define( 'ABSPATH', '/tmp/wordpress/' );

// Load Brain Monkey
require_once dirname( __DIR__ ) . '/vendor/brain/monkey/inc/patchwork-loader.php';
