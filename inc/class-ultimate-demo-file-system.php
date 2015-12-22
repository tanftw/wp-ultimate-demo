<?php
/**
 * This class holds all methods to interact with File System
 * 
 * @author Tan Nguyen <tan@fitwp.com>
 */
class Ultimate_Demo_File_System
{
	protected static $demo 			= ABSPATH . '.demo';

	protected static $demo_disabled = ABSPATH . '.demo_disabled';

	/**
	 * Delete temporary uploads directory
	 * 
	 * @return void
	 */
	public static function delete_uploads_demo_dir()
	{
		rrmdir( ABSPATH . 'wp-content/uploads-demo' );
	}

	/**
	 * Sync temporary upload directory with original content
	 * 
	 * @return void
	 */
	public static function sync_uploads_demo_dir()
	{
		self::delete_uploads_demo_dir();

		recurse_copy( ABSPATH . UPLOADS_ORIGINAL, ABSPATH . 'wp-content/uploads-demo' );
	}
	
	public static function toggle_demo()
	{
		if ( wud_is_demo_active() === ! isset( $_POST['offline_mode'] ) )
			return;

		if ( isset( $_POST['offline_mode'] ) && $_POST['offline_mode'] == 1 )
			self::disable_demo();

		if ( ! isset( $_POST['offline_mode'] ) )
			self::enable_demo();

		wp_redirect( $_POST['_wp_http_referer'] . '&saved=true' );
		exit;
	}

	/**
	 * Enable demo mode
	 * 
	 * @return bool
	 */
	public static function enable_demo()
	{
		@unlink( self::$demo_disabled );

		if ( ! file_exists( self::$demo ) )
			file_put_contents( self::$demo, '' );
	}

	/**
	 * Disable demo mode
	 * 
	 * @return bool
	 */
	public static function disable_demo()
	{
		@unlink( self::$demo );

		if ( ! file_exists( self::$demo_disabled ) )
			file_put_contents( self::$demo_disabled, '' );
	}
}