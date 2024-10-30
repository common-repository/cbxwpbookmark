<?php

/**
 * Fired during plugin uninstall/delete.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      1.0.0
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Uninstall {
	/**
	 * Uninstall plugin functionality
	 *
	 *
	 * @since    1.1.3
	 */
	public static function uninstall() {
		// For the regular site.
		if ( ! is_multisite() ) {
			self::uninstall_tasks();
		} else {
			//for multi site
			global $wpdb;


			$blog_ids         = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM %s", $wpdb->blogs ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				self::uninstall_tasks();
			}

			switch_to_blog( $original_blog_id );
		}
	}//end method uninstall

	/**
	 * Do the necessary uninstall tasks
	 *
	 * @return void
	 */
	public static function uninstall_tasks() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}


		$settings             = new CBXWPBookmark_Settings_API();
		$delete_global_config = $settings->get_option( 'delete_global_config', 'cbxwpbookmark_tools', 'no' );

		if ( $delete_global_config == 'yes' ) {
			//before hook
			do_action( 'cbxwpbookmark_plugin_uninstall_before' );

			//delete options
			$option_values = CBXWPBookmarkHelper::getAllOptionNames();

			do_action( 'cbxwpbookmark_plugin_options_deleted_before' );

			foreach ( $option_values as $key => $option_value ) {
				$option = $option_value['option_name'];

				do_action( 'cbxwpbookmark_plugin_option_delete_before', $option );
				delete_option( $option );
				do_action( 'cbxwpbookmark_plugin_option_delete_after', $option );
			}

			do_action( 'cbxwpbookmark_plugin_options_deleted_after' );
			do_action( 'cbxwpbookmark_plugin_options_deleted' );
			//end delete options

			//delete tables
			$table_names = CBXWPBookmarkHelper::getAllDBTablesList();

			if ( is_array( $table_names ) && sizeof( $table_names ) > 0 ) {
				do_action( 'cbxwpbookmark_plugin_tables_deleted_before', $table_names );

				global $wpdb;

				foreach ($table_names as $table_name){
					//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$query_result = $wpdb->query(  "DROP TABLE IF EXISTS {$table_name}");
				}

				do_action( 'cbxwpbookmark_plugin_tables_deleted_after', $table_names );
				do_action( 'cbxwpbookmark_plugin_tables_deleted' );
			}
			//end delete tables

			//after hook
			do_action( 'cbxwpbookmark_plugin_uninstall_after' );

			//general hook
			do_action( 'cbxwpbookmark_plugin_uninstall' );
		}
	}//end method uninstall
}//end class CBXWPBookmark_Uninstall
