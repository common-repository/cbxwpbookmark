<?php

/**
 * Fired during plugin activation
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		CBXWPBookmarkHelper::create_tables();

		set_transient( 'cbxwpbookmark_activated_notice', 1 );
	}//end method activate

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 */
	public static function cbxbookmark_create_pages() {
		CBXWPBookmarkHelper::cbxbookmark_create_pages();
	}//end cbxbookmark_create_pages

	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param  string  $key
	 * @param  string  $slug
	 * @param  string  $page_title
	 * @param  string  $page_content
	 *
	 * @return int|string|WP_Error|null
	 */
	public static function cbxbookmark_create_page( $key = '', $slug = '', $page_title = '', $page_content = '' ) {
		return CBXWPBookmarkHelper::cbxbookmark_create_page($key, $slug, $page_title, $page_content);
	}//end cbxbookmark_create_page
}//end class CBXWPBookmark_Activator