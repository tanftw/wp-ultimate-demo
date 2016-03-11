<?php
/*
Plugin Name: WP Ultimate Demo
Plugin URI: https://binaty.org/plugins/wp-ultimate-demo
Description: Seamless & Fast WP Demo Solution. Just click and run.
Version: 1.1
Author: Tan Nguyen <tan@binaty.org>
Text Domain: wud
License: GPL2+
*/

//Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

global $wpdb;

//----------------------------------------------------------
// Define plugin URL for loading static files or doing AJAX
//------------------------------------------------------------
if ( ! defined( 'WUD_URL' ) )
	define( 'WUD_URL', plugin_dir_url( __FILE__ ) );

define( 'WUD_JS_URL', trailingslashit( WUD_URL . 'assets/js' ) );
define( 'WUD_CSS_URL', trailingslashit( WUD_URL . 'assets/css' ) );

// ------------------------------------------------------------
// Plugin paths, for including files
// ------------------------------------------------------------
if ( ! defined( 'WUD_DIR' ) )
	define( 'WUD_DIR', plugin_dir_path( __FILE__ ) );

define( 'WUD_INC_DIR', trailingslashit( WUD_DIR . 'inc' ) );

// Get the current wp-content/uploads dir path. In case other plugins
// changed it
if ( defined( 'UPLOADS' ) )
	define( 'UPLOADS_ORIGINAL', UPLOADS );
else
	define( 'UPLOADS_ORIGINAL', 'wp-content/uploads' );

// Check wether is in demo mode or not. If so, set the db prefix and uploads dir
// to temporary db tables and directories
if ( file_exists( ABSPATH . '.demo' ) ) 
{
	$wpdb->set_prefix( 'wuddemo_' );

	define( 'UPLOADS', 'wp-content/uploads-demo' );
}

// Helper functions
include WUD_INC_DIR . 'helpers.php';

// Interact with File System
include WUD_INC_DIR . 'class-ultimate-demo-file-system.php';

// Setting pages and API
include WUD_INC_DIR . 'class-ultimate-demo-settings.php';

// Interact with all data
include WUD_INC_DIR . 'class-ultimate-demo-data.php';

// Main Class 
include WUD_INC_DIR . 'class-ultimate-demo.php';

register_activation_hook( __FILE__, array( 'Ultimate_Demo_Data', 'setup' ) );
register_deactivation_hook( __FILE__, array( 'Ultimate_Demo_Data', 'uninstall' ) );