<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/admin
 */


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/admin
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmark_Admin {

	/**
	 * The plugin basename of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_basename The plugin basename of the plugin.
	 */
	protected $plugin_basename;
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The settings api of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $settings_api settings api of this plugin.
	 */
	private $settings_api;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $cbxwpbookmark The ID of this plugin.
	 */
	private $cbxwpbookmark;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param  string  $plugin_name  The name of this plugin.
	 * @param  string  $version  The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->cbxwpbookmark = $plugin_name;
		$this->plugin_name   = $plugin_name;
		$this->version       = $version;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}

		$this->plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->cbxwpbookmark . '.php' );

		$this->settings_api = new CBXWPBookmark_Settings_API();
	}//end constructor

	public function setting_init() {
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}//end setting_init

	/**
	 * Tab Defination
	 *
	 * @return array
	 */
	public function get_settings_sections() {
		return CBXWPBookmarkHelper::cbxwpbookmark_setting_sections();
	}//end get_settings_sections


	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	public function get_settings_fields() {
		return CBXWPBookmarkHelper::cbxwpbookmark_setting_fields();
	}//end get_settings_fields

	/**
	 * Returns post types as array
	 *
	 * @return array
	 */
	public function post_types() {
		return CBXWPBookmarkHelper::post_types();
	}//end enqueue_scripts

	/**
	 * Adds hook for post delete - delete bookmark for those post
	 */
	public function on_bookmarkpost_delete() {
		add_action( 'delete_post', [ $this, 'on_delete_object_delete_bookmarks' ], 10, 2 );
	}//end get_settings_sections

	/**
	 * On any object delete delete bookmarks
	 *
	 * @param int $object_id
	 * @param object $post
	 *
	 * @return void
	 */
	public function on_delete_object_delete_bookmarks($object_id, $post) {
		$object_type = $post->post_type;

		cbxwpbookmarks_delete_bookmarks($object_id, $object_type);
	}//end method on_delete_object_delete_bookmarks

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		global $post_type, $post;

		$version = $this->version;
		$page    = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended


		$css_url_part     = CBXWPBOOKMARK_ROOT_URL . 'assets/css/';
		$js_url_part      = CBXWPBOOKMARK_ROOT_URL . 'assets/js/';
		$vendors_url_part = CBXWPBOOKMARK_ROOT_URL . 'assets/vendors/';

		$css_path_part     = CBXWPBOOKMARK_ROOT_PATH . 'assets/css/';
		$js_path_part      = CBXWPBOOKMARK_ROOT_PATH . 'assets/js/';
		$vendors_path_part = CBXWPBOOKMARK_ROOT_PATH . 'assets/vendors/';


		$admin_pages = CBXWPBookmarkHelper::admin_page_slugs();
		$all_pages   = array_merge( $admin_pages, [ 'cbxwpbookmark_settings' ] );


		//register common css for all bookmark dashboard pages
		if ( in_array( $page, $all_pages ) ) {
			wp_register_style( 'pickr', $vendors_url_part . 'pickr/classic.min.css', [], $version );
			wp_register_style( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/style.css', [], $version );

			wp_register_style( 'cbxwpbookmark-admin', $css_url_part . 'cbxwpbookmark-admin.css', [], $version );
		}


		//other pages except setting page
		if ( in_array( $page, $admin_pages ) ) {
			wp_register_style( 'cbxwpbookmark-manage', $css_url_part . 'cbxwpbookmark-manage.css',
				[ 'pickr', 'awesome-notifications', 'cbxwpbookmark-admin' ], $version );


			wp_enqueue_style( 'pickr' );
			wp_enqueue_style( 'awesome-notifications' );

			wp_enqueue_style( 'cbxwpbookmark-admin' );
			wp_enqueue_style( 'cbxwpbookmark-manage' );
		}

		//only for setting page
		if ( $page == 'cbxwpbookmark_settings' ) {
			wp_register_style( 'select2', $vendors_url_part . 'select2/select2.min.css', [], $version );

			wp_register_style( 'cbxwpbookmark-setting', $css_url_part . 'cbxwpbookmark-setting.css',
				[ 'select2', 'pickr', 'awesome-notifications', 'cbxwpbookmark-admin' ], $version );

			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'pickr' );
			wp_enqueue_style( 'awesome-notifications' );

			wp_enqueue_style( 'cbxwpbookmark-admin' );//common admin styles
			wp_enqueue_style( 'cbxwpbookmark-setting' );
		}
	}//end enqueue_styles

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		global $post_type, $post;

		$version = $this->version;
		$page    = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$admin_pages = CBXWPBookmarkHelper::admin_page_slugs();
		$all_pages   = array_merge( $admin_pages, [ 'cbxwpbookmark_settings' ] );


		$css_url_part     = CBXWPBOOKMARK_ROOT_URL . 'assets/css/';
		$js_url_part      = CBXWPBOOKMARK_ROOT_URL . 'assets/js/';
		$vendors_url_part = CBXWPBOOKMARK_ROOT_URL . 'assets/vendors/';

		$css_path_part     = CBXWPBOOKMARK_ROOT_PATH . 'assets/css/';
		$js_path_part      = CBXWPBOOKMARK_ROOT_PATH . 'assets/js/';
		$vendors_path_part = CBXWPBOOKMARK_ROOT_PATH . 'assets/vendors/';

		$import_modal_html = '<div id="cbxwpbookmark_import_modal_wrap" class="cbx-chota">';
		$import_modal_html .= '<h2>' . esc_html__( 'Import Bookmark Setting: Json file', 'cbxwpbookmark' ) . '</h2>';
		$import_modal_html .= '<form method="post" id="cbxwpbookmark_import_form">';
		$import_modal_html .= '<input type="file" name="file" id="cbxwpbookmark_import_file" />';
		$import_modal_html .= '</form>';
		$import_modal_html .= '</div>';

		$translation_placeholder =
			[
				'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
				'ajax_fail'                => esc_html__( 'Request failed, please reload the page.', 'cbxwpbookmark' ),
				'nonce'                    => wp_create_nonce( "settingsnonce" ),
				'editnonce'                => wp_create_nonce( "cbxwpbookmarknonce" ),
				'is_user_logged_in'        => is_user_logged_in() ? 1 : 0,
				'please_select'            => esc_html__( 'Please Select', 'cbxwpbookmark' ),
				'upload_title'             => esc_html__( 'Window Title', 'cbxwpbookmark' ),
				'search_placeholder'       => esc_html__( 'Search here', 'cbxwpbookmark' ),
				'teeny_setting'            => [
					'teeny'         => true,
					'media_buttons' => true,
					'editor_class'  => '',
					'textarea_rows' => 5,
					'quicktags'     => false,
					'menubar'       => false,
				],
				'copycmds'                 => [
					'copy'       => esc_html__( 'Copy', 'cbxwpbookmark' ),
					'copied'     => esc_html__( 'Copied', 'cbxwpbookmark' ),
					'copy_tip'   => esc_html__( 'Click to copy', 'cbxwpbookmark' ),
					'copied_tip' => esc_html__( 'Copied to clipboard', 'cbxwpbookmark' ),
				],
				'confirm_msg'              => esc_html__( 'Are you sure to remove this step?', 'cbxwpbookmark' ),
				'confirm_msg_all'          => esc_html__( 'Are you sure to remove all steps?', 'cbxwpbookmark' ),
				'confirm_yes'              => esc_html__( 'Yes', 'cbxwpbookmark' ),
				'confirm_no'               => esc_html__( 'No', 'cbxwpbookmark' ),
				'are_you_sure_global'      => esc_html__( 'Are you sure?', 'cbxwpbookmark' ),
				'are_you_sure_delete_desc' => esc_html__( 'Once you delete, it\'s gone forever. You can not revert it back.', 'cbxwpbookmark' ),
				'pickr_i18n'               => [
					// Strings visible in the UI
					'ui:dialog'       => esc_html__( 'color picker dialog', 'cbxwpbookmark' ),
					'btn:toggle'      => esc_html__( 'toggle color picker dialog', 'cbxwpbookmark' ),
					'btn:swatch'      => esc_html__( 'color swatch', 'cbxwpbookmark' ),
					'btn:last-color'  => esc_html__( 'use previous color', 'cbxwpbookmark' ),
					'btn:save'        => esc_html__( 'Save', 'cbxwpbookmark' ),
					'btn:cancel'      => esc_html__( 'Cancel', 'cbxwpbookmark' ),
					'btn:clear'       => esc_html__( 'Clear', 'cbxwpbookmark' ),

					// Strings used for aria-labels
					'aria:btn:save'   => esc_html__( 'save and close', 'cbxwpbookmark' ),
					'aria:btn:cancel' => esc_html__( 'cancel and close', 'cbxwpbookmark' ),
					'aria:btn:clear'  => esc_html__( 'clear and close', 'cbxwpbookmark' ),
					'aria:input'      => esc_html__( 'color input field', 'cbxwpbookmark' ),
					'aria:palette'    => esc_html__( 'color selection area', 'cbxwpbookmark' ),
					'aria:hue'        => esc_html__( 'hue selection slider', 'cbxwpbookmark' ),
					'aria:opacity'    => esc_html__( 'selection slider', 'cbxwpbookmark' ),
				],
				'awn_options'              => [
					'tip'           => esc_html__( 'Tip', 'cbxwpbookmark' ),
					'info'          => esc_html__( 'Info', 'cbxwpbookmark' ),
					'success'       => esc_html__( 'Success', 'cbxwpbookmark' ),
					'warning'       => esc_html__( 'Attention', 'cbxwpbookmark' ),
					'alert'         => esc_html__( 'Error', 'cbxwpbookmark' ),
					'async'         => esc_html__( 'Loading', 'cbxwpbookmark' ),
					'confirm'       => esc_html__( 'Confirmation', 'cbxwpbookmark' ),
					'confirmOk'     => esc_html__( 'OK', 'cbxwpbookmark' ),
					'confirmCancel' => esc_html__( 'Cancel', 'cbxwpbookmark' )
				],
				'validation'               => [
					'required'    => esc_html__( 'This field is required.', 'cbxwpbookmark' ),
					'remote'      => esc_html__( 'Please fix this field.', 'cbxwpbookmark' ),
					'email'       => esc_html__( 'Please enter a valid email address.', 'cbxwpbookmark' ),
					'url'         => esc_html__( 'Please enter a valid URL.', 'cbxwpbookmark' ),
					'date'        => esc_html__( 'Please enter a valid date.', 'cbxwpbookmark' ),
					'dateISO'     => esc_html__( 'Please enter a valid date ( ISO ).', 'cbxwpbookmark' ),
					'number'      => esc_html__( 'Please enter a valid number.', 'cbxwpbookmark' ),
					'digits'      => esc_html__( 'Please enter only digits.', 'cbxwpbookmark' ),
					'equalTo'     => esc_html__( 'Please enter the same value again.', 'cbxwpbookmark' ),
					'maxlength'   => esc_html__( 'Please enter no more than {0} characters.', 'cbxwpbookmark' ),
					'minlength'   => esc_html__( 'Please enter at least {0} characters.', 'cbxwpbookmark' ),
					'rangelength' => esc_html__( 'Please enter a value between {0} and {1} characters long.', 'cbxwpbookmark' ),
					'range'       => esc_html__( 'Please enter a value between {0} and {1}.', 'cbxwpbookmark' ),
					'max'         => esc_html__( 'Please enter a value less than or equal to {0}.', 'cbxwpbookmark' ),
					'min'         => esc_html__( 'Please enter a value greater than or equal to {0}.', 'cbxwpbookmark' ),
					'recaptcha'   => esc_html__( 'Please check the captcha.', 'cbxwpbookmark' ),
				],
				'global_setting_link_html' => '<a href="' . admin_url( 'admin.php?page=cbxwpbookmark-settings' ) . '"  class="button outline primary pull-right">' . esc_html__( 'Global Settings', 'cbxwpbookmark' ) . '</a>',
				'lang'                     => get_user_locale()
			];


		if ( $page == 'cbxwpbookmark_settings' ) {


			wp_register_script( 'awesome-notifications', $vendors_url_part . 'awesome-notifications/script.js', [], $version, true );
			wp_register_script( 'pickr', $vendors_url_part . 'pickr/pickr.min.js', [], $version, true );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_media();

			wp_register_script( 'select2', $vendors_url_part . 'select2/select2.min.js', [ 'jquery' ], $version, true );
			wp_register_script( 'cbxwpbookmark-setting', $js_url_part . 'cbxwpbookmark-setting.js',
				[
					'jquery',
					'select2',
					'pickr',
					'awesome-notifications'
				],
				$version, true );


			wp_localize_script( 'cbxwpbookmark-setting', 'cbxwpbookmark_setting', apply_filters( 'cbxwpbookmark_setting_vars', $translation_placeholder ) );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'pickr' );
			wp_enqueue_script( 'awesome-notifications' );

			wp_enqueue_script( 'cbxwpbookmark-setting' );
		}

	}//end enqueue_scripts

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function admin_pages() {
		global $submenu;

		//review listing page
		$bookmark_list_page_hook = add_menu_page( esc_html__( 'CBX WP Bookmark Dashboard', 'cbxwpbookmark' ),
			esc_html__( 'Bookmarks', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmarkdash',
			[ $this, 'display_admin_bookmark_dash_page' ],
			CBXWPBOOKMARK_ROOT_URL . 'assets/img/menu_icon_24.png' );

		//review listing page
		$bookmark_list_page_hook = add_submenu_page( 'cbxwpbookmarkdash', esc_html__( 'CBX WP Bookmark Listing', 'cbxwpbookmark' ),
			esc_html__( 'User Bookmarks', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmark',
			[ $this, 'display_admin_bookmark_list_page' ] );


		//add screen save option for bookmark listing
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'cbxwpbookmark' && ! isset( $_GET['view'] ) ) {
			add_action( "load-$bookmark_list_page_hook", [ $this, 'cbxwpbookmark_bookmark_list_screen' ] );
		}

		//Add menu for bookmark category listing
		$bookmark_category_page_hook = add_submenu_page( 'cbxwpbookmarkdash', esc_html__( 'CBX WP Bookmark Category Listing', 'cbxwpbookmark' ),
			esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmarkcats',
			[ $this, 'display_admin_bookmark_category_page' ]
		);

		//add screen save option for bookmark category listing
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'cbxwpbookmarkcats' && ! isset( $_GET['view'] ) ) {
			add_action( "load-$bookmark_category_page_hook",
				[
					$this,
					'cbxwpbookmark_bookmark_category_screen',
				] );
		}


		//add settings for this plugin
		$setting_page_hook = add_submenu_page(
			'cbxwpbookmarkdash',
			esc_html__( 'CBX WP Bookmark Setting', 'cbxwpbookmark' ),
			esc_html__( 'Setting', 'cbxwpbookmark' ),
			'manage_options',
			'cbxwpbookmark_settings',
			[ $this, 'display_plugin_admin_settings' ]
		);


		if ( isset( $submenu['cbxwpbookmarkdash'][0][0] ) ) {
			$submenu['cbxwpbookmarkdash'][0][0] = esc_html__( 'Bookmarks Dashboard', 'cbxwpbookmark' );
		}
	}//end add_plugin_admin_menu

	/**
	 * Admin dashboard view
	 */
	public function display_admin_bookmark_dash_page() {
		echo cbxwpbookmark_get_template_html( 'admin/dashboard.php', [] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end display_admin_bookmark_dash_page

	/**
	 * Admin review listing view
	 */
	public function display_admin_bookmark_list_page() {
		echo cbxwpbookmark_get_template_html( 'admin/bookmark_list_display.php', [] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}//end display_admin_bookmark_listing_page

	/**
	 * Set options for bookmark listing result
	 *
	 * @param $new_status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function cbxwpbookmark_bookmark_list_per_page( $new_status, $option, $value ) {
		if ( 'cbxwpbookmark_list_per_page' == $option ) {
			return $value;
		}

		return $new_status;
	}//end cbxwpbookmark_bookmark_list_per_page

	/**
	 * Add screen option for bookmark listing
	 */
	public function cbxwpbookmark_bookmark_list_screen() {

		$option = 'per_page';
		$args   = [
			'label'   => esc_html__( 'Number of items per page', 'cbxwpbookmark' ),
			'default' => 50,
			'option'  => 'cbxwpbookmark_list_per_page',
		];
		add_screen_option( $option, $args );

	}//end cbxwpbookmark_bookmark_list_screen

	/**
	 * Admin review listing view
	 */
	public function display_admin_bookmark_category_page() {
		$settings = $this->settings_api;

		global $wpdb;

		//$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . $this->plugin_basename );

		$view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $view == 'edit' ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo cbxwpbookmark_get_template_html( 'admin/bookmark_category_edit.php', [
				'settings' => $settings
			] );

		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo cbxwpbookmark_get_template_html( 'admin/bookmark_category_list.php', [
				'settings' => $settings
			] );

		}


	}//end display_admin_bookmark_listing_page


	/**
	 * Set options for bookmark category listing result
	 *
	 * @param $new_status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function cbxwpbookmark_bookmark_category_per_page( $new_status, $option, $value ) {
		if ( 'cbxwpbookmark_category_per_page' == $option ) {
			return $value;
		}

		return $new_status;
	}//end cbxwpbookmark_bookmark_category_per_page

	/**
	 * Add screen option for bookmark listing
	 */
	public function cbxwpbookmark_bookmark_category_screen() {

		$option = 'per_page';
		$args   = [
			'label'   => esc_html__( 'Number of items per page', 'cbxwpbookmark' ),
			'default' => 50,
			'option'  => 'cbxwpbookmark_category_per_page',
		];
		add_screen_option( $option, $args );

	}//end cbxwpbookmark_bookmark_category_screen

	/**
	 * Admin page for settings of this plugin
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_settings() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo cbxwpbookmark_get_template_html( 'admin/setting_display.php', [
			'ref'      => $this,
			'settings' => $this->settings_api
		] );
	}//end display_plugin_admin_settings

	/**
	 * Add/Edit bookmark Category
	 */
	public function add_edit_category() {
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['cbxwpbookmark_cat_addedit'] ) && intval( $_POST['cbxwpbookmark_cat_addedit'] ) == 1 ) {
			global $wpdb;
			$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';

			$redirect_url        = 'admin.php?page=cbxwpbookmarkcats&view=edit';
			$form_validated      = true;
			$validation['error'] = false;
			$validation['field'] = [];

			//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$submit_data = isset( $_POST['cbxwpbookmark_form'] ) ? $_POST['cbxwpbookmark_form'] : [];
			$isAjax      = isset( $submit_data['ajax'] ) ? intval( $submit_data['ajax'] ) : 0;

			//verify nonce field
			if ( wp_verify_nonce( $_POST['cbxwpbookmark_cat_nonce'], 'cbxwpbookmark_cat_addedit' ) ) {

				$log_id   = isset( $submit_data['id'] ) ? absint( $submit_data['id'] ) : 0;
				$privacy  = isset( $submit_data['privacy'] ) ? absint( $submit_data['privacy'] ) : 0;
				$cat_name = isset( $submit_data['cat_name'] ) ? sanitize_text_field( $submit_data['cat_name'] ) : '';

				$title_len = mb_strlen( $cat_name );

				$col_data = [
					'cat_name' => $cat_name,
					'privacy'  => $privacy,
				];


				//check category title length is not less than 5 or more than 200 char
				if ( $title_len < 3 || $title_len > 250 ) {
					$form_validated        = false;
					$validation['error']   = true;
					$validation['field'][] = 'title';
					$validation['msg']     = esc_html__( 'The title field character limit must be between 3 to 250.', 'cbxwpsimpleaccounting' );
				}


				//check form passes all validation rules
				if ( $form_validated ) {
					//edit mode
					if ( $log_id > 0 ) {


						$col_data['modyfied_date'] = current_time( 'mysql' );

						//cat_name, privacy, modyfied_date
						$col_data_format = [ '%s', '%d', '%s' ];

						$where = [
							'id' => $log_id,
						];

						$where_format = [ '%d' ];

						//matching update function return is false, then update failed.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						if ( $wpdb->update( $category_table, $col_data, $where, $col_data_format, $where_format ) === false ) {
							//update failed
							$validation['msg'] = esc_html__( 'Sorry! category update failed or database error', 'cbxwpbookmark' );
						} else {
							$category_info = CBXWPBookmarkHelper::singleCategory( $log_id );

							do_action( 'cbxbookmark_category_edit', $log_id, $category_info['user_id'], $cat_name );

							//update successful
							$msg = esc_html__( 'Category updated successfully.', 'cbxwpbookmark' );


							$validation['error']            = false;
							$validation['msg']              = $msg;
							$validation['data']['id']       = $log_id;
							$validation['data']['cat_name'] = stripslashes( $cat_name );
							$validation['data']['privacy']  = $privacy;
							$validation['data']['status']   = 'updated';


						}

					} else { //if category is new then go here

						$col_data['user_id']      = $user_id = intval( get_current_user_id() );
						$col_data['created_date'] = current_time( 'mysql' );

						///cat_name, privacy, user_id, created_date
						$col_data_format = [ '%s', '%d', '%d', '%s' ];
						//insert new category
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						if ( $wpdb->insert( $category_table, $col_data, $col_data_format ) ) {
							//new category inserted successfully

							$log_id = $wpdb->insert_id;

							do_action( 'cbxbookmark_category_added', $log_id, $user_id, $cat_name );

							$msg = esc_html__( 'Category created successfully.', 'cbxwpsimpleaccounting' );
//							$msg .= ' <a  href="' . admin_url( $redirect_url . '&id=' . $log_id ) . '" class="button">';
//							$msg .= esc_html__( 'Edit', 'cbxwpbookmark' );
//							$msg .= '</a>';

							$validation['error']            = false;
							$validation['msg']              = $msg;
							$validation['data']['id']       = $log_id;
							$validation['data']['cat_name'] = stripslashes( $cat_name );
							$validation['data']['privacy']  = $privacy;
							$validation['data']['status']   = 'new';
						} else { //new category insertion failed
							$validation['error'] = true;
							$validation['msg']   = esc_html__( 'Error creating category', 'cbxwpbookmark' );
						}
					}
				}
			} else { //if wp_nonce not verified then entry here
				$validation['error']   = true;
				$validation['field'][] = 'wp_nonce';
				$validation['msg']     = esc_html__( 'Hacking attempt ?', 'cbxwpbookmark' );
			}


			if ( $isAjax ) {
				echo wp_json_encode( $validation );
				wp_die();
			} else {
				set_transient( 'cbxwpbookmark_cat_addedit_error', $validation );

				if ( $log_id > 0 ) {
					$redirect_url .= '&id=' . $log_id;
				}

				wp_safe_redirect( admin_url( $redirect_url ) );
				exit;
			}
		}//if cbxwpbookmark_cat_addedit(category edit submited)  submit

	}//end add_edit_category

	/**
	 * Automatically create pages using ajax
	 */
	public function cbxwpbookmark_autocreate_page() {
		check_ajax_referer( 'cbxbookmarknonce', 'security' );

		if ( ! class_exists( 'CBXWPBookmark_Activator' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxwpbookmark-activator.php';
		}

		//create pages
		CBXWPBookmarkHelper::cbxbookmark_create_pages(); //create the shortcode page

		$message        = [];
		$message['msg'] = esc_html__( 'Automatic page creation done. This message doesn\'t confirm success or failed', 'cbxwpbookmark' );

		echo wp_json_encode( $message );
		wp_die();
	}//end cbxwpbookmark_autocreate_page

	/**
	 * Post installation hook
	 *
	 * @param $response
	 * @param  array  $hook_extra
	 * @param  array  $result
	 */
	public function upgrader_post_install( $response, $hook_extra = [], $result = [] ) {
		if ( $response && isset( $hook_extra['type'] ) && $hook_extra['type'] == 'plugin' ) {
			if ( isset( $result['destination_name'] ) && $result['destination_name'] == 'cbxwpbookmark' ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				CBXWPBookmarkHelper::create_tables();
				CBXWPBookmarkHelper::customizer_default_adjust( true );
				set_transient( 'cbxwpbookmark_upgraded_notice', 1 );
			}
		}
	}


	/**
	 * If we need to do something in upgrader process is completed
	 *
	 * @param $upgrader_object
	 * @param $options
	 */
	public function plugin_upgrader_process_complete( $upgrader_object, $options ) {
		if ( isset( $options['plugins'] ) && $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) && sizeof( $options['plugins'] ) > 0 ) {
				foreach ( $options['plugins'] as $each_plugin ) {
					if ( $each_plugin == CBXWPBOOKMARK_BASE_NAME ) {
						CBXWPBookmarkHelper::create_tables();
						CBXWPBookmarkHelper::customizer_default_adjust( true );
						set_transient( 'cbxwpbookmark_upgraded_notice', 1 );
						break;
					}
				}
			}
		}

	}//end plugin_upgrader_process_complete

	/**
	 * Show a notice to anyone who has just installed the plugin for the first time
	 * This notice shouldn't display to anyone who has just updated this plugin
	 */
	public function plugin_activate_upgrade_notices() {
		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxwpbookmark_activated_notice' ) ) {
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible">';
			/* translators: %s: bookmark core plugin version */
			echo '<p>' . sprintf( wp_kses( __( 'Thanks for installing/deactivating <strong>CBX Bookmark</strong> V%s - Codeboxr Team', 'cbxwpbookmark' ), [ 'strong' => [] ] ), esc_attr( CBXWPBOOKMARK_PLUGIN_VERSION ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			/* translators: 1. Plugin setting url 2. Documentation link */
			echo '<p>' . sprintf( wp_kses( __( 'Check <a style="color:#005ae0 !important; font-weight: bold;" href="%1$s">Plugin Setting</a> | <a style="color:#005ae0 !important; font-weight: bold;" href="%2$s" target="_blank">Documentation</a>', 'cbxwpbookmark' ), [ 'a' => [ 'href' => [], 'style' => [], 'target' => [] ] ] ), esc_url(admin_url( 'admin.php?page=cbxwpbookmark_settings' )),
					'https://codeboxr.com/product/cbx-wordpress-bookmark/' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxwpbookmark_activated_notice' );

			$this->pro_addon_compatibility_campaign();
		}

		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxwpbookmark_upgraded_notice' ) ) {
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible">';
			/* translators: %s: bookmark core plugin version */
			echo '<p>' . sprintf( wp_kses( __( 'Thanks for upgrading <strong>CBX Bookmark</strong> V%s , enjoy the new features and bug fixes - Codeboxr Team', 'cbxwpbookmark' ), [ 'strong' => [] ] ), esc_attr( CBXWPBOOKMARK_PLUGIN_VERSION ) ) . '</p>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			/* translators: 1. Plugin setting url 2. Documentation link */
			echo '<p>' . sprintf( wp_kses( __( 'Check <a style="color:#005ae0 !important; font-weight: bold;" href="%1$s">Plugin Setting</a> | <a style="color:#005ae0 !important; font-weight: bold;" href="%2$s" target="_blank">Documentation</a>', 'cbxwpbookmark' ), [ 'a' => [ 'href' => [], 'style' => [], 'target' => [] ] ] ), esc_url( admin_url( 'admin.php?page=cbxwpbookmark_settings' ) ), 'https://codeboxr.com/product/cbx-wordpress-bookmark/' ) . '</p>';
			echo '</div>';
			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxwpbookmark_upgraded_notice' );

			$this->pro_addon_compatibility_campaign();
		}
	}//end plugin_activate_upgrade_notices

	/**
	 * Check plugin compatibility and pro addon install campaign
	 */
	public function pro_addon_compatibility_campaign() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		//if the pro addon is active or installed
		if ( in_array( 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXWPBOOKMARKADDON_PLUGIN_NAME' ) ) {
			//plugin is activated

			$pro_plugin_version = CBXWPBOOKMARKADDON_PLUGIN_VERSION;
			//$core_plugin_version = CBXWPBOOKMARK_PLUGIN_VERSION;

			if ( version_compare( $pro_plugin_version, '1.1.10', '<' ) ) {
				/* translators: %s: bookmark pro plugin version */
				echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'CBX Bookmark Pro Addon V%s or any previous version is not 100%% compatible with CBX Bookmark Core V1.5.3 or later. Please update CBX Bookmark Pro Addon to version 1.1.10 or latest. - Codeboxr Team', 'cbxmcratingreview' ), $pro_plugin_version ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}


		} else {
			/* translators: %s: bookmark product description url */
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible"><p>' . sprintf( wp_kses( __( '<a target="_blank" href="%s">CBX Bookmark Pro Addon</a> has extended features, settings, widgets and shortcodes. try it  - Codeboxr Team', 'cbxwpbookmark' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ), 'https://codeboxr.com/product/cbx-wordpress-bookmark/' ) . '</p></div>';
		}


		//if the mycred addon is active or installed
		if ( in_array( 'cbxwpbookmarkmycred/cbxwpbookmarkmycred.php.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXWPBOOKMARKMYCRED_PLUGIN_NAME' ) ) {
			//plugin is activated

			//$plugin_version = CBXWPBOOKMARKMYCRED_PLUGIN_VERSION;
		} else {
			/* translators: %s: bookmark mycred plugin description url */
			echo '<div style="border-left-color: #005ae0;" class="notice notice-success is-dismissible"><p>' . sprintf( wp_kses( __( '<a target="_blank" href="%s">CBX Bookmark myCred Addon</a> has myCred integration. try it  - Codeboxr Team', 'cbxwpbookmark' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ), 'https://codeboxr.com/product/cbx-bookmark-mycred-addon/' ) . '</p></div>';
		}

	}//end pro_addon_compatibility_campaign


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param  mixed  $links  Plugin Action links.
	 *
	 * @return  array
	 */
	public function plugin_action_links( $links ) {
		$action_links = [
			'settings' => '<a style="color:#005ae0 !important; font-weight: bold;" href="' . admin_url( 'admin.php?page=cbxwpbookmark_settings' ) . '" aria-label="' . esc_attr__( 'View settings', 'cbxwpbookmark' ) . '">' . esc_html__( 'Settings', 'cbxwpbookmark' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}//end plugin_action_links

	/**
	 * Filters the array of row meta for each/specific plugin in the Plugins list table.
	 * Appends additional links below each/specific plugin on the plugins page.
	 *
	 * @access  public
	 *
	 * @param  array  $links_array  An array of the plugin's metadata
	 * @param  string  $plugin_file_name  Path to the plugin file
	 * @param  array  $plugin_data  An array of plugin data
	 * @param  string  $status  Status of the plugin
	 *
	 * @return  array       $links_array
	 */
	public function plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {
		if ( strpos( $plugin_file_name, CBXWPBOOKMARK_BASE_NAME ) !== false ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://wordpress.org/support/plugin/cbxwpbookmark/" aria-label="' . esc_attr__( 'Free Support', 'cbxwpbookmark' ) . '">' . esc_html__( 'Free Support', 'cbxwpbookmark' ) . '</a>';

			$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://wordpress.org/plugins/cbxwpbookmark/#reviews" aria-label="' . esc_attr__( 'Reviews', 'cbxwpbookmark' ) . '">' . esc_html__( 'Reviews', 'cbxwpbookmark' ) . '</a>';

			$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://codeboxr.com/doc/cbxwpbookmark-doc/" aria-label="' . esc_attr__( 'Documentation', 'cbxwpbookmark' ) . '">' . esc_html__( 'Documentation', 'cbxwpbookmark' ) . '</a>';


			if ( in_array( 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXWPBOOKMARKADDON_PLUGIN_NAME' ) ) {

			} else {
				$links_array[] = '<a target="_blank" style="color:#005ae0 !important; font-weight: bold;" href="https://codeboxr.com/product/cbx-wordpress-bookmark/" aria-label="' . esc_attr__( 'Try Pro Addon', 'cbxwpbookmark' ) . '">' . esc_html__( 'Try Pro Addon', 'cbxwpbookmark' ) . '</a>';
			}
		}

		return $links_array;
	}//end plugin_row_meta

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function pre_set_site_transient_update_plugins_pro_addon( $transient ) {
		// Extra check for 3rd plugins
		if ( isset( $transient->response['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_info = [];
		$all_plugins = get_plugins();
		if ( ! isset( $all_plugins['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'] ) ) {
			return $transient;
		} else {
			$plugin_info = $all_plugins['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'];
		}

		$remote_version = '1.4.3'; //current pro addon version

		if ( version_compare( $plugin_info['Version'], $remote_version, '<' ) ) {
			$obj                                                              = new stdClass();
			$obj->slug                                                        = 'cbxwpbookmarkaddon';
			$obj->new_version                                                 = $remote_version;
			$obj->plugin                                                      = 'cbxwpbookmarkaddon/cbxwpbookmarkaddon.php';
			$obj->url                                                         = '';
			$obj->package                                                     = false;
			$obj->name                                                        = 'CBX Bookmark & Favorite Pro Addon';
			$transient->response['cbxwpbookmarkaddon/cbxwpbookmarkaddon.php'] = $obj;
		}

		return $transient;
	}//end pre_set_site_transient_update_plugins_pro_addons

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $transient
	 */
	public function pre_set_site_transient_update_plugins_mycred_addon( $transient ) {
		// Extra check for 3rd plugins
		if ( isset( $transient->response['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_info = [];
		$all_plugins = get_plugins();
		if ( ! isset( $all_plugins['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'] ) ) {
			return $transient;
		} else {
			$plugin_info = $all_plugins['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'];
		}


		$remote_version = '1.0.4';

		if ( version_compare( $plugin_info['Version'], $remote_version, '<' ) ) {
			$obj                                                                = new stdClass();
			$obj->slug                                                          = 'cbxwpbookmarkmycred';
			$obj->new_version                                                   = $remote_version;
			$obj->plugin                                                        = 'cbxwpbookmarkmycred/cbxwpbookmarkmycred.php';
			$obj->url                                                           = '';
			$obj->package                                                       = false;
			$obj->name                                                          = 'CBX Bookmark & Favorite myCred Addon';
			$transient->response['cbxwpbookmarkmycred/cbxwpbookmarkmycred.php'] = $obj;
		}

		return $transient;
	}//end pre_set_site_transient_update_plugins_pro_addons

	/**
	 * Pro Addon update message
	 */
	public function plugin_update_message_pro_addons() {
		/* translators: 1. Link to codeboxr.com site 2. Link to codeboxr.com site */
		echo ' ' . sprintf( wp_kses( __( 'Check how to <a style="color:#005ae0 !important; font-weight: bold;" href="%1$s"><strong>Update manually</strong></a> , download latest version from <a style="color:#005ae0 !important; font-weight: bold;" href="%2$s"><strong>My Account</strong></a> section of Codeboxr.com', 'cbxwpbookmark' ), [ 'a' => [ 'href' => [], 'style' => [], 'target' => [] ] ] ), 'https://codeboxr.com/manual-update-pro-addon/', 'https://codeboxr.com/my-account/' );
	}//end plugin_update_message_pro_addons

	/**
	 * User's bookmarks listing screen option columns
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function log_listing_screen_cols( $columns ) {
		$columns = [
			'id'           => esc_html__( 'ID', 'cbxwpbookmark' ),
			'object_id'    => esc_html__( 'Post', 'cbxwpbookmark' ),
			'object_type'  => esc_html__( 'Post Type', 'cbxwpbookmark' ),
			'user_id'      => esc_html__( 'User', 'cbxwpbookmark' ),
			'cat_id'       => esc_html__( 'Category', 'cbxwpbookmark' ),
			'created_date' => esc_html__( 'Created', 'cbxwpbookmark' ),
		];

		return apply_filters( 'cbxwpbookmark_bookmarks_listing_screen_option_columns', $columns );
	}//end log_listing_screen_cols

	/**
	 * User's bookmarks listing screen option columns
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function category_listing_screen_cols( $columns ) {
		$columns = [
			'id'            => esc_html__( 'ID', 'cbxwpbookmark' ),
			'cat_name'      => esc_html__( 'Title', 'cbxwpbookmark' ),
			'user_id'       => esc_html__( 'User', 'cbxwpbookmark' ),
			'privacy'       => esc_html__( 'Privacy', 'cbxwpbookmark' ),
			'created_date'  => esc_html__( 'Created', 'cbxwpbookmark' ),
			'modyfied_date' => esc_html__( 'Modified', 'cbxwpbookmark' )
		];

		return apply_filters( 'cbxwpbookmark_category_listing_screen_option_columns', $columns );
	}//end category_listing_screen_cols

	/**
	 * Load setting html
	 *
	 * @return void
	 * @since 1.7.14
	 */
	public function settings_reset_load() {
		//security check
		check_ajax_referer( 'settingsnonce', 'security' );

		$msg            = [];
		$msg['html']    = '';
		$msg['message'] = esc_html__( 'Bookmark reset setting html loaded successfully', 'cbxwpbookmark' );
		$msg['success'] = 1;

		if ( ! current_user_can( 'manage_options' ) ) {
			$msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxwpbookmark' );
			$msg['success'] = 0;
			wp_send_json( $msg );
		}

		$msg['html'] = CBXWPBookmarkHelper::setting_reset_html_table();

		wp_send_json( $msg );
	}//end method settings_reset_load

	/**
	 * Reset plugin data
	 */
	public function plugin_reset() {
		//security check
		check_ajax_referer( 'settingsnonce', 'security' );

		$url = admin_url( 'admin.php?page=cbxwpbookmark_settings' );

		$msg            = [];
		$msg['message'] = esc_html__( 'Bookmark setting reset successfully', 'cbxwpbookmark' );
		$msg['success'] = 1;
		$msg['url']     = $url;

		if ( ! current_user_can( 'manage_options' ) ) {
			$msg['message'] = esc_html__( 'Sorry, you don\'t have enough permission', 'cbxwpbookmark' );
			$msg['success'] = 0;
			wp_send_json( $msg );
		}


		//before hook
		do_action( 'cbxwpbookmark_plugin_reset_before' );

		$plugin_resets = wp_unslash( $_POST );

		//delete options
		do_action( 'cbxwpbookmark_plugin_options_deleted_before' );

		$reset_options = isset( $plugin_resets['reset_options'] ) ? $plugin_resets['reset_options'] : [];
		$option_values = ( is_array( $reset_options ) && sizeof( $reset_options ) > 0 ) ? array_values( $reset_options ) : array_values( CBXWPBookmarkHelper::getAllOptionNamesValues() );

		foreach ( $option_values as $key => $option ) {
			do_action( 'cbxwpbookmark_plugin_option_delete_before', $option );
			delete_option( $option );
			do_action( 'cbxwpbookmark_plugin_option_delete_after', $option );
		}

		do_action( 'cbxwpbookmark_plugin_options_deleted_after' );
		do_action( 'cbxwpbookmark_plugin_options_deleted' );
		//end delete options


		//delete tables
		$reset_tables = isset( $plugin_resets['reset_tables'] ) ? $plugin_resets['reset_tables'] : [];
		$table_names  = ( is_array( $reset_tables ) && sizeof( $reset_tables ) > 0 ) ? array_values( $reset_tables ) : array_values( CBXWPBookmarkHelper::getAllDBTablesList() );


		if ( is_array( $table_names ) && count( $table_names )) {
			do_action( 'cbxwpbookmark_plugin_tables_delete_before', $table_names );

			global $wpdb;

			foreach ($table_names as $table_name){
				//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$query_result = $wpdb->query(  "DROP TABLE IF EXISTS {$table_name}");
			}

			do_action( 'cbxwpbookmark_plugin_tables_deleted_after', $table_names );
			do_action( 'cbxwpbookmark_plugin_tables_deleted' );
		}


		//after hook
		do_action( 'cbxwpbookmark_plugin_reset_after' );

		//general hook
		do_action( 'cbxwpbookmark_plugin_reset' );//hooked in core 'plugin_reset_extend'

		wp_send_json( $msg );
	}//end method plugin_reset

	/**
	 * Create the tables and pages after plugin reset
	 *
	 * @return void
	 */
	public function plugin_reset_extend() {
		//need to create the tables again
		CBXWPBookmarkHelper::create_tables();
		//create pages
		CBXWPBookmarkHelper::cbxbookmark_create_pages(); //create the shortcode page
	}//end method plugin_reset_extend
}//end class CBXWPBookmark_Admin