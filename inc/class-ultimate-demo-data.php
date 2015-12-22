<?php

class Ultimate_Demo_Data
{
	/**
	 * Get all db tables (without temporary tables)
	 * 
	 * @return Array Table list
	 */
	protected static function get_tables()
	{
		global $wpdb;

		$tables = $wpdb->get_results('SHOW TABLES');

		// DROP Demo table if exists
		foreach ( $tables as $row )
		{
			foreach ($row as $property => $table_name )
			{
				$table_name = str_replace( $wpdb->prefix, '', $table_name );

				$table_list[$table_name] = $table_name;
			}
		}

		return $table_list;
	}

	/**
	 * Setup sync_demo_data procedure
	 * 
	 * @return void
	 */
	public static function setup_procedure()
	{
		global $wpdb;

		$tables = self::get_tables();

		$procedures = $wpdb->get_results( 'SHOW PROCEDURE STATUS' );

		foreach ( $procedures as $procedure )
		{
			if ( $procedure->Name === 'sync_demo_data' )
				return;
		}

		$query = "
			CREATE PROCEDURE sync_demo_data()
			BEGIN
		";

		foreach ( $tables as $table )
		{
			$query .= " DROP TABLE IF EXISTS wuddemo_{$table};";
			$query .= " CREATE TABLE IF NOT EXISTS wuddemo_{$table} LIKE " . $wpdb->prefix . $table . ';';
			$query .= " INSERT INTO wuddemo_{$table} SELECT * FROM " . $wpdb->prefix . $table . ';';
		}

		$query .= " UPDATE wuddemo_usermeta SET meta_key = REPLACE(meta_key, '{$wpdb->prefix}', 'wuddemo_');";
		$query .= " UPDATE wuddemo_options SET option_name = REPLACE(option_name, '{$wpdb->prefix}', 'wuddemo_');";
		$query .= " REPLACE INTO wuddemo_options(option_name, option_value) VALUES('udm_last_run', NOW());";

		$query .= " END;";

		$wpdb->query( $query );
	}


	/**
	 * Setup SQL Event which run each X hour(s)
	 * 
	 * @return void
	 */
	public static function setup_event( $offset = null )
	{
		global $wpdb;

		$offset = ( ! is_numeric( $offset ) ) ? wud_setting( 'cleanup_offset' ) : $offset;

		// Check if 'auto_cleanup' event exists or not.
		// If exists: Alter that event.
		$create_or_alter = 'CREATE';

		$events = $wpdb->get_results( "SHOW EVENTS" );

		foreach ( $events as $event )
		{
			if ( $event->Name === 'auto_cleanup' )
				$create_or_alter = 'ALTER';
		}

		$query = "	
				{$create_or_alter} EVENT `auto_cleanup` 
				ON SCHEDULE EVERY {$offset} HOUR STARTS '2015-01-01 00:00:00' 
				DO BEGIN CALL sync_demo_data;
			END;
		";

		$wpdb->query( $query );
	} 

	/**
	 * Cleanup data, sync files and database
	 * 
	 * @return void
	 */
	public static function cleanup()
	{
		global $wpdb;

		$wpdb->query( 'CALL sync_demo_data' );

		Ultimate_Demo_File_System::sync_uploads_demo_dir();
	}

	/**
	 * Turn on Event Scheduler
	 * 
	 * @return void
	 */
	public static function turn_on_event_scheduler()
	{
		global $wpdb;
		
		$wpdb->query( 'SET GLOBAL event_scheduler="ON"' );
	}

	/**
	 * Setup temporary demo tables and directories
	 * 
	 * @return void
	 */
	public static function setup()
	{
		global $wpdb;

		// $wpdb->show_errors();

		self::setup_procedure();

		self::setup_event();
		
		self::turn_on_event_scheduler();

		self::cleanup();
	}

	/**
	 * Uninstall Plugin
	 * 
	 * @return void
	 */
	public static function uninstall()
	{
		global $wpdb;

		$tables = self::get_tables();

		// Remove .demo and .demo_disabled files
		@unlink( ABSPATH . '.demo' );
		@unlink( ABSPATH . '.demo_disabled' );

		// Drop procedure and events
		$wpdb->query( 'DROP PROCEDURE IF EXISTS `sync_demo_data`' );
		$wpdb->query( 'DROP EVENT IF EXISTS `auto_cleanup`' );

		// Drop temporary tables
		foreach ( $tables as $table )
		{
			$wpdb->query("DROP TABLE IF EXISTS wuddemo_{$table}");
		}

		// Delete temporary uploads dir
		Ultimate_Demo_File_System::delete_uploads_demo_dir();
		
		// Delete plugin settings
		delete_option( 'ultimate_demo' );
	}
}