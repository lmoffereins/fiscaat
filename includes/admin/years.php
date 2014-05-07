<?php

/**
 * Fiscaat Year Admin Class
 *
 * @package Fiscaat
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Fiscaat_Years_Admin' ) ) :
/**
 * Loads Fiscaat years admin area
 *
 * @package Fiscaat
 * @subpackage Administration
 */
class Fiscaat_Years_Admin {

	/** Variables *************************************************************/

	/**
	 * @var The post type of this admin component
	 */
	private $post_type = '';

	/** Functions *************************************************************/

	/**
	 * The main Fiscaat years admin loader
	 *
	 * @uses Fiscaat_Years_Admin::setup_globals() Setup the globals needed
	 * @uses Fiscaat_Years_Admin::setup_actions() Setup the hooks and actions
	 * @uses Fiscaat_Years_Admin::setup_help() Setup the help text
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses add_filter() To add various filters
	 * @uses fct_get_year_post_type() To get the year post type
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses fct_get_record_post_type() To get the record post type
	 */
	private function setup_actions() {

		/** Actions ***********************************************************/

		// Add some general styling to the admin area
		add_action( 'fct_admin_head', array( $this, 'admin_head' ) );

		// Metabox actions
		add_action( 'add_meta_boxes', array( $this, 'attributes_metabox'      ) );
		add_action( 'save_post',      array( $this, 'attributes_metabox_save' ) );

		// Contextual Help
		add_action( 'fct_admin_load_edit_years',  array( $this, 'edit_help' ) );
		add_action( 'fct_admin_load_new_years',   array( $this, 'new_help'  ) );

		// Page title
		add_action( 'fct_admin_years_page_title', array( $this, 'post_new_link' ) );

		/** Filters ***********************************************************/

		// Filter years
		add_filter( 'fct_request',           array( $this, 'filter_post_rows' ) );

		// Messages
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

		// Columns (in post row)
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );
	}

	/**
	 * Setup default admin class globals
	 *
	 * @access private
	 */
	private function setup_globals() {
		$this->post_type = fct_get_year_post_type();
	}

	/**
	 * Should we bail out of this method?
	 *
	 * @return boolean
	 */
	private function bail() {
		if ( ! isset( get_current_screen()->post_type ) || ( $this->post_type != get_current_screen()->post_type ) )
			return true;

		return false;
	}

	/** Contextual Help *******************************************************/

	/**
	 * Contextual help for Fiscaat year edit page
	 *
	 * @uses get_current_screen()
	 */
	public function edit_help() {
		if ( $this->bail() ) 
			return;

		// Overview
		get_current_screen()->add_help_tab( array(
			'id'		=> 'overview',
			'title'		=> __( 'Overview', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'This screen displays the individual years on your site. You can customize the display of this screen to suit your workflow.', 'fiscaat' ) . '</p>'
		) );

		// Screen Content
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __( 'Screen Content', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'fiscaat' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'You can hide/display columns based on your needs and decide how many years to list per screen using the Screen Options tab.',                                                                                                                                'fiscaat' ) . '</li>' .
					'<li>' . __( 'You can filter the list of years by year status using the text links in the upper left to show All, Published, or Trashed years. The default view is to show all years.',                                                                                 'fiscaat' ) . '</li>' .
					'<li>' . __( 'You can refine the list to show only years from a specific month by using the dropdown menus above the years list. Click the Filter button after making your selection. You also can refine the list by clicking on the year creator in the years list.', 'fiscaat' ) . '</li>' .
				'</ul>'
		) );

		// Available Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'action-links',
			'title'		=> __( 'Available Actions', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'Hovering over a row in the years list will display action links that allow you to manage your year. You can perform the following actions:', 'fiscaat' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that year. You can also reach that screen by clicking on the year title.',                                                                              'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Trash</strong> removes your year from this list and places it in the trash, from which you can permanently delete it.',                                                                                    'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>View</strong> will show you what your draft year will look like if you publish it. View will take you to your live site to view the year. Which link is available depends on your year&#8217;s status.', 'fiscaat' ) . '</li>' .
				'</ul>'
		) );

		// Bulk Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'bulk-actions',
			'title'		=> __( 'Bulk Actions', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'You can also edit or move multiple years to the trash at once. Select the years you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.',           'fiscaat' ) . '</p>' .
				'<p>' . __( 'When using Bulk Edit, you can change the metadata (categories, author, etc.) for all selected years at once. To remove a year from the grouping, just click the x next to its name in the Bulk Edit area that appears.', 'fiscaat' ) . '</p>'
		) );

		// Help Sidebar
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'fiscaat' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.fiscaat.org" target="_blank">Fiscaat Documentation</a>',    'fiscaat' ) . '</p>' .
			'<p>' . __( '<a href="http://fiscaat.org/years/" target="_blank">Fiscaat Support Years</a>', 'fiscaat' ) . '</p>'
		);
	}

	/**
	 * Contextual help for Fiscaat year edit page
	 *
	 * @since Fiscaat (r3119)
	 * @uses get_current_screen()
	 */
	public function new_help() {
		if ( $this->bail() ) 
			return;

		$customize_display = '<p>' . __( 'The title field and the big year editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.', 'fiscaat' ) . '</p>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'customize-display',
			'title'   => __( 'Customizing This Display', 'fiscaat' ),
			'content' => $customize_display,
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'title-year-editor',
			'title'   => __( 'Title and Year Editor', 'fiscaat' ),
			'content' =>
				'<p>' . __( '<strong>Title</strong> - Enter a title for your year. After you enter a title, you&#8217;ll see the permalink below, which you can edit.', 'fiscaat' ) . '</p>' .
				'<p>' . __( '<strong>Year Editor</strong> - Enter the text for your year. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your year text. You can insert media files by clicking the icons above the year editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in HTML mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular year editor.', 'fiscaat' ) . '</p>'
		) );

		$publish_box = '<p>' . __( '<strong>Publish</strong> - You can set the terms of publishing your year in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a year or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a year to be published in the future or backdate a year.', 'fiscaat' ) . '</p>';

		if ( current_theme_supports( 'year-formats' ) && year_type_supports( 'year', 'year-formats' ) ) {
			$publish_box .= '<p>' . __( '<strong>year Format</strong> - This designates how your theme will display a specific year. For example, you could have a <em>standard</em> blog year with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each year format</a>. Your theme could enable all or some of 10 possible formats.', 'fiscaat' ) . '</p>';
		}

		if ( current_theme_supports( 'year-thumbnails' ) && year_type_supports( 'year', 'thumbnail' ) ) {
			$publish_box .= '<p>' . __( '<strong>Featured Image</strong> - This allows you to associate an image with your year without inserting it. This is usually useful only if your theme makes use of the featured image as a year thumbnail on the home page, a custom header, etc.', 'fiscaat' ) . '</p>';
		}

		get_current_screen()->add_help_tab( array(
			'id'      => 'year-attributes',
			'title'   => __( 'Year Attributes', 'fiscaat' ),
			'content' =>
				'<p>' . __( 'Select the attributes that your year should have:', 'fiscaat' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Type</strong> indicates if the year is a category or year. Categories generally contain other years.',                                                                                'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Status</strong> allows you to close a year to new accounts and years.',                                                                                                                  'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Visibility</strong> lets you pick the scope of each year and what users are allowed to access it.',                                                                                     'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Parent</strong> dropdown determines the parent year. Select the year or category from the dropdown, or leave the default (No Parent) to create the year at the root of your years.', 'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Order</strong> allows you to order your years numerically.',                                                                                                                            'fiscaat' ) . '</li>' .
				'</ul>'
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'publish-box',
			'title'   => __( 'Publish Box', 'fiscaat' ),
			'content' => $publish_box,
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'discussion-settings',
			'title'   => __( 'Discussion Settings', 'fiscaat' ),
			'content' =>
				'<p>' . __( '<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.', 'fiscaat' ) . '</p>' .
				'<p>' . __( '<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the year, you can see them here and moderate them.', 'fiscaat' ) . '</p>'
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'fiscaat' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.fiscaat.org" target="_blank">Fiscaat Documentation</a>',    'fiscaat' ) . '</p>' .
			'<p>' . __( '<a href="http://fiscaat.org/years/" target="_blank">Fiscaat Support Years</a>', 'fiscaat' ) . '</p>'
		);
	}

	/**
	 * Add the year attributes metabox
	 *
	 * @uses fct_get_year_post_type() To get the year post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'fct_year_attributes_metabox'
	 */
	public function attributes_metabox() {
		if ( $this->bail() ) 
			return;

		add_meta_box (
			'fct_year_attributes',
			__( 'Year Attributes', 'fiscaat' ),
			'fct_year_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'fct_year_attributes_metabox' );
	}

	/**
	 * Pass the year attributes for processing
	 *
	 * @param int $year_id Year id
	 * @uses current_user_can() To check if the current user is capable of
	 *                           editing the year
	 * @uses do_action() Calls 'fct_year_attributes_metabox_save' with the
	 *                    year id
	 * @return int Year id
	 */
	public function attributes_metabox_save( $year_id ) {
		if ( $this->bail() ) 
			return $year_id;

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $year_id;

		// Bail if not a post request
		if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
			return $year_id;

		// Nonce check
		if ( empty( $_POST['fct_year_metabox'] ) || ! wp_verify_nonce( $_POST['fct_year_metabox'], 'fct_year_metabox_save' ) )
			return $year_id;

		// Only save for year post-types
		if ( ! fct_is_year( $year_id ) )
			return $year_id;

		// Bail if current user cannot edit this year
		if ( ! current_user_can( 'edit_year', $year_id ) )
			return $year_id;

		// Update the year meta bidness
		fct_update_year( array( 'year_id' => $year_id ) );

		do_action( 'fct_year_attributes_metabox_save', $year_id );

		return $year_id;
	}

	/**
	 * Add some general styling to the admin area
	 *
	 * @uses fct_get_year_post_type() To get the year post type
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses fct_get_record_post_type() To get the record post type
	 * @uses sanitize_html_class() To sanitize the classes
	 * @uses do_action() Calls 'fct_admin_head'
	 */
	public function admin_head() {
		if ( $this->bail() ) 
			return; ?>

		<style type="text/css" media="screen">
		/*<![CDATA[*/

			#misc-publishing-actions,
			#save-post {
				display: none;
			}

			strong.label {
				display: inline-block;
				width: 60px;
			}

			#fct_year_attributes hr {
				border-style: solid;
				border-width: 1px;
				border-color: #ccc #fff #fff #ccc;
			}

			.column-fct_year_account_count,
			.column-fct_year_record_count,
			.column-fct_account_record_count,
			.column-fct_account_end_value {
				width: 10% !important;
			}

			.column-author,
			.column-fct_record_author,
			.column-fct_account_author {
				width: 10% !important;
			}

			.column-fct_account_year,
			.column-fct_record_year,
			.column-fct_record_account {
				width: 10% !important;
			}

			.column-fct_year_started,
			.column-fct_year_closed,
			.column-fct_record_created {
				width: 15% !important;
			}

			.status-closed {
				background-color: #eaeaea;
			}

			.status-declined {
				background-color: #faeaea;
			}

			.status-unapproved {
				background-color: #eafeaf;
			}

		/*]]>*/
		</style>

		<?php
	}

	/**
	 * Year Row actions
	 *
	 * Add the view/accounts/records/close/open/trash/delete action links 
	 * under the year title
	 *
	 * @param array $actions Actions
	 * @param array $year Year object
	 * @uses the_content() To output year description
	 * @return array $actions Actions
	 */
	public function row_actions( $actions, $year ) {
		if ( $this->bail() ) 
			return $actions;

		// Show view link if it's not set, the year is trashed and the user can view trashed accounts
		if ( empty( $actions['view'] ) && ( fct_get_trash_status_id() != $year->post_status ) ) {
			$actions['view'] = '<a href="' . fct_get_account_permalink( $year->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'fiscaat' ), fct_get_year_title( $year->ID ) ) ) . '" rel="permalink">' . __( 'View', 'fiscaat' ) . '</a>';
		}

		// Show accounts and records link
		$actions['accounts'] = '<a href="' . add_query_arg( array( 'page' => 'fct-accounts', 'fct_year_id' => $year->ID ), admin_url( 'admin.php' ) ) .'" title="' . esc_attr( sprintf( __( 'Show all accounts of &#8220;%s&#8221;', 'fiscaat' ), fct_get_year_title( $year->ID ) ) ) . '">' . __( 'Accounts', 'fiscaat' ) . '</a>';
		$actions['records']  = '<a href="' . add_query_arg( array( 'page' => 'fct-records',  'fct_year_id' => $year->ID ), admin_url( 'admin.php' ) ) .'" title="' . esc_attr( sprintf( __( 'Show all records of &#8220;%s&#8221;',  'fiscaat' ), fct_get_year_title( $year->ID ) ) ) . '">' . __( 'Records',  'fiscaat' ) . '</a>';

		// Show the close and open link
		if ( current_user_can( 'edit_year', $year->ID ) ) {
			$toggle_uri = esc_url( wp_nonce_url( add_query_arg( array( 'year_id' => $year->ID, 'action' => 'fct_toggle_year_close' ), remove_query_arg( array( 'fct_year_toggle_notice', 'account_id', 'failed', 'super' ) ) ), 'close-year_' . $year->ID ) );
			if ( fct_is_year_open( $year->ID ) ) {

				// Show close link if the year has no open accounts
				if ( ! fct_has_open_account() ) {
					$actions['closed'] = '<a href="' . $toggle_uri . '" title="' . esc_attr__( 'Close this year', 'fiscaat' ) . '">' . _x( 'Close', 'Close the year', 'fiscaat' ) . '</a>';
				}
			} else {
				$actions['open'] = '<a href="' . $toggle_uri . '" title="' . esc_attr__( 'Open this year',  'fiscaat' ) . '">' . _x( 'Open',  'Open the year',  'fiscaat' ) . '</a>';
			}
		}

		// Only show delete links for closed or empty years
		if ( current_user_can( 'delete_year', $year->ID ) && ( fct_is_year_closed() || ! fct_year_has_records() ) ) {
			if ( fct_get_trash_status_id() == $year->post_status ) {
				$post_type_object = get_post_type_object( fct_get_year_post_type() );
				$actions['untrash'] = "<a title='" . esc_attr__( 'Restore this item from the Trash', 'fiscaat' ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => fct_get_year_post_type() ), admin_url( 'edit.php' ) ) ), wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $year->ID ) ), 'untrash-' . $year->post_type . '_' . $year->ID ) ) . "'>" . __( 'Restore', 'fiscaat' ) . "</a>";
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Move this item to the Trash', 'fiscaat' ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => fct_get_year_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $year->ID ) ) . "'>" . __( 'Trash', 'fiscaat' ) . "</a>";
			}

			if ( fct_get_trash_status_id() == $year->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Delete this item permanently', 'fiscaat' ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => fct_get_year_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $year->ID, '', true ) ) . "'>" . __( 'Delete Permanently', 'fiscaat' ) . "</a>";
			}
		}

		return $actions;
	}

	/**
	 * Adjust the request query
	 *
	 * @param array $query_vars Query variables from {@link WP_Query}
	 * @return array Processed Query Vars
	 */
	function filter_post_rows( $query_vars ) {
		if ( $this->bail() ) 
			return $query_vars;

		// Handle sorting
		if ( isset( $_REQUEST['orderby'] ) ) {

			// Check order type
			switch ( $_REQUEST['orderby'] ) {

				// Year closed date. Reverse order
				case 'year_closed' :
					$query_vars['meta_key'] = '_fct_closed';
					$query_vars['orderby']  = 'meta_value';
					$query_vars['order']   = isset( $_REQUEST['order'] ) && 'DESC' == strtoupper( $_REQUEST['order'] ) ? 'ASC' : 'DESC';
					break;

				// Year account count
				case 'year_account_count' :
					$query_vars['meta_key'] = '_fct_account_type';
					$query_vars['orderby']  = 'meta_value';
					break;

				// Year record count
				case 'year_record_count' :
					$query_vars['meta_key'] = '_fct_record_count';
					$query_vars['orderby']  = 'meta_value_num';
					break;

				// Year end value
				case 'year_end_value' :
					$query_vars['meta_key'] = '_fct_end_value';
					$query_vars['orderby']  = 'meta_value_num';
					break;
			}

			// Default sorting order
			if ( ! isset( $query_vars['order'] ) ) {
				$query_vars['order']    = isset( $_REQEUEST['order'] ) ? strtoupper( $_REQEUEST['order'] ) : 'ASC';
			}
		}

		// Return manipulated query_vars
		return $query_vars;
	}

	/**
	 * Custom user feedback messages for year post type
	 *
	 * @global int $post_ID
	 * @uses fct_get_year_permalink()
	 * @uses wp_post_revision_title()
	 * @uses esc_url()
	 * @uses add_query_arg()
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	public function updated_messages( $messages ) {
		global $post_ID;

		if ( $this->bail() ) 
			return $messages;

		// URL for the current year
		$year_url = fct_get_year_permalink( $post_ID );

		// Current year's post_date
		$post_date = fct_get_global_post_field( 'post_date', 'raw' );

		// Messages array
		$messages[$this->post_type] = array(
			0 =>  '', // Left empty on purpose

			// Updated
			1 =>  sprintf( __( 'Year updated. <a href="%s">View year</a>', 'fiscaat' ), $year_url ),

			// Custom field updated
			2 => __( 'Custom field updated.', 'fiscaat' ),

			// Custom field deleted
			3 => __( 'Custom field deleted.', 'fiscaat' ),

			// Year updated
			4 => __( 'Year updated.', 'fiscaat' ),

			// Restored from revision
			// translators: %s: date and time of the revision
			5 => isset( $_GET['revision'] )
					? sprintf( __( 'Year restored to revision from %s', 'fiscaat' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
					: false,

			// Year created
			6 => sprintf( __( 'Year created. <a href="%s">View year</a>', 'fiscaat' ), $year_url ),

			// Year saved
			7 => __( 'Year saved.', 'fiscaat' ),

			// Year submitted
			8 => sprintf( __( 'Year submitted. <a target="_blank" href="%s">Preview year</a>', 'fiscaat' ), esc_url( add_query_arg( 'preview', 'true', $year_url ) ) ),

			// Year scheduled
			9 => sprintf( __( 'Year scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview year</a>', 'fiscaat' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'fiscaat' ),
					strtotime( $post_date ) ),
					$year_url ),

			// Year draft updated
			10 => sprintf( __( 'Year draft updated. <a target="_blank" href="%s">Preview year</a>', 'fiscaat' ), esc_url( add_query_arg( 'preview', 'true', $year_url ) ) ),

		);

		return $messages;
	}


	/**
	 * Append post-new link to page title
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_has_open_year()
	 * @uses fct_admin_page_title_add_new()
	 * @param string $title Page title
	 * @return string Page title
	 */
	public function post_new_link( $title ) {

		// When there's no open year
		if ( ! fct_has_open_year() ) {
			$title = fct_admin_page_title_add_new( $title );
		}

		return $title;
	}
}

endif; // class_exists check

/**
 * Setup Fiscaat Years Admin
 *
 * This is currently here to make hooking and unhooking of the admin UI easy.
 * It could use dependency injection in the future, but for now this is easier.
 *
 * @uses Fiscaat_Years_Admin
 */
function fct_admin_years() {
	fiscaat()->admin->years = new Fiscaat_Years_Admin();
}
