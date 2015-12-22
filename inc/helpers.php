<?php
/**
 * Check if demo is active or not
 * 
 * @return bool
 */
function wud_is_demo_active()
{
	return file_exists( ABSPATH . '.demo' );
}

/**
 * Define all available settings. Format: setting_name => default
 * 
 * @var array
 */
function wud_default_settings()
{
	return array(
		'offline_mode' 			=> true, 
		'disable_file_editing'	=> true,
		'cleanup_offset'		=> 24,
		'show_countdown'		=> true, 
		'countdown_interval'	=> 300, // 300 seconds 
		'countdown_template'	=> 'Your session is going to expires in %time% seconds', 
		'auto_login'			=> 2, // Prefill
		'auto_login_as'			=> '',
		'user_login'			=> '',
		'user_pass'				=> '',
		'login_message'			=> '',
		'hide_from_anyone'		=> true,
		'only_show_for'			=> get_current_user_id()
	);
}

/**
 * Get Setting
 * 
 * @param  Mixed $field (Optional) Field Name, if null, return whole settings
 *  
 * @return Mixed
 */
function wud_setting( $field = null )
{
	$settings = get_option( 'ultimate_demo' );
	$defaults = wud_default_settings();

	if ( empty( $settings ) || $settings == false )
		$settings = $defaults;

	if ( is_null( $field ) )
		return $settings;

	if ( isset( $settings[$field] ) )
		return $settings[$field];

	if ( isset( $defaults[$field] ) )
		return $defaults[$field];

	return null;
}

/**
 * Get the last cleanup time
 * 
 * @return MySQL Timestamp
 */
function wud_get_last_cleanup_time()
{
	return get_option( 'wud_last_run' );
}

/**
 * Get the next cleanup time
 * 
 * @return MySQL Timestamp
 */
function wud_get_next_cleanup_time()
{
	$last_cleanup_time = wud_get_last_cleanup_time();

	$offset = wud_setting( 'cleanup_offset' );

	return date( "Y-m-d H:i:s", strtotime( $last_cleanup_time ) + $offset * 3600 );
}

/**
 * Remove directory with recursive
 * @param  String $dir Path
 * @return void
 */
if ( ! function_exists( 'rrmdir' ) ) 
{
	function rrmdir( $dir ) 
	{ 
	   	if ( is_dir( $dir ) ) 
	   	{ 
		    $objects = scandir( $dir );

		    foreach ( $objects as $object ) 
		    { 
		       	if ( $object != "." && $object != ".." ) 
		       	{ 
		         	if ( is_dir( $dir. "/" .$object ) )
		           		rrmdir( $dir . "/" . $object );
		         	else
		           		unlink( $dir."/".$object ); 
		       	} 
		    }
	     	rmdir($dir); 
	   	}
	}
}

/**
 * Copy directory and its content
 * 
 * @param String $src Source Directory
 * @param String $dst Destination
 */
if ( ! function_exists( 'recurse_copy' ) )
{
	function recurse_copy( $src, $dst ) 
	{ 
	    $dir = opendir($src); 
	    @mkdir($dst); 
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	}
}

function wud_user_uneditable()
{
	return wud_setting( 'only_show_for' ) != get_current_user_id() && wud_setting( 'hide_from_anyone' );
}

function wud_user_editable()
{
	return ! wud_user_uneditable();
}