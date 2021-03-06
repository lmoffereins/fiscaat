<?php

/**
 * Fiscaat Accounts Admin Class
 *
 * @package Fiscaat
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Fiscaat_Accounts_Admin' ) ) :
/**
 * Loads Fiscaat accounts admin area
 *
 * @package Fiscaat
 * @subpackage Administration
 */
class Fiscaat_Accounts_Admin {

	/** Variables *************************************************************/

	/**
	 * @var The post type of this admin component
	 */
	private $post_type = '';

	/** Functions *************************************************************/

	/**
	 * The main Fiscaat accounts admin loader
	 *
	 * @uses Fiscaat_Accounts_Admin::setup_globals() Setup the globals needed
	 * @uses Fiscaat_Accounts_Admin::setup_actions() Setup the hooks and actions
	 * @uses Fiscaat_Accounts_Admin::setup_help() Setup the help text
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
	 * @uses fct_get_period_post_type() To get the period post type
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses fct_get_record_post_type() To get the record post type
	 */
	private function setup_actions() {

		/** Actions ***********************************************************/

		// Add some general styling to the admin area
		add_action( 'fct_admin_head', array( $this, 'admin_head' ) );

		// Account metabox actions
		add_action( 'add_meta_boxes', array( $this, 'attributes_metabox'      ) );
		add_action( 'save_post',      array( $this, 'attributes_metabox_save' ) );

		// Check if there are any fct_toggle_account_* requests on admin_init, also have a message displayed
		add_action( 'fct_admin_load_accounts', array( $this, 'toggle_account'        ) );
		add_action( 'fct_admin_notices',       array( $this, 'toggle_account_notice' ) );

		// Contextual Help
		add_action( 'fct_admin_load_accounts',     array( $this, 'edit_help' ) );
		add_action( 'fct_admin_load_post_account', array( $this, 'new_help'  ) );

		// Check if there is a missing open period on account add/edit, also have a message displayed
		add_action( 'fct_admin_load_post_account', array( $this, 'missing_redirect' ) );
		add_action( 'fct_admin_notices',           array( $this, 'missing_notices'  ) );
		
		// Page title
		add_action( 'fct_admin_accounts_page_title', array( $this, 'accounts_page_title' ) );
		add_action( 'fct_admin_accounts_page_title', array( $this, 'post_new_link'       ) );

		/** Ajax **************************************************************/
		
		// Check ledger id
		add_action( 'wp_ajax_fct_check_ledger_id', array( $this, 'check_ledger_id' ) );

		/** Filters ***********************************************************/

		// Add ability to filter accounts and records per period
		add_action( 'restrict_manage_posts', array( $this, 'filter_dropdown'   )        );
		add_filter( 'fct_request',           array( $this, 'filter_post_rows'  )        );
		add_filter( 'posts_clauses',         array( $this, 'filter_post_order' ), 10, 2 );

		// Account columns (in post row)
		add_filter( 'fct_admin_accounts_get_columns', array( $this, 'accounts_column_headers' )        );
		add_filter( 'display_post_states',            array( $this, 'account_post_states'     ), 10, 2 );
		add_filter( 'post_row_actions',               array( $this, 'account_row_actions'     ), 10, 2 );

		// Bulk actions
		add_filter( 'fct_admin_accounts_get_bulk_actions',  array( $this, 'accounts_bulk_actions'  ), 10, 2 );
		add_filter( 'fct_admin_accounts_bulk_action_close', array( $this, 'bulk_action_close'      ), 10, 2 );
		add_filter( 'fct_admin_accounts_bulk_action_open',  array( $this, 'bulk_action_open'       ), 10, 2 );
		add_filter( 'fct_admin_remove_bulk_query_args',     array( $this, 'bulk_remove_query_args' )        );
		
		// Messages
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
	}

	/**
	 * Setup default admin class globals
	 *
	 * @access private
	 */
	private function setup_globals() {
		$this->post_type = fct_get_account_post_type();
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
	 * Contextual help for Fiscaat account edit page
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
				'<p>' . __( 'This screen displays the individual accounts on your site. You can customize the display of this screen to suit your workflow.', 'fiscaat' ) . '</p>'
		) );

		// Screen Content
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __( 'Screen Content', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'fiscaat' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'You can hide/display columns based on your needs and decide how many accounts to list per screen using the Screen Options tab.',                                                                                                                                'fiscaat' ) . '</li>' .
					'<li>' . __( 'You can filter the list of accounts by account status using the text links in the upper left to show All, Published, or Trashed accounts. The default view is to show all accounts.',                                                                                 'fiscaat' ) . '</li>' .
					'<li>' . __( 'You can refine the list to show only accounts from a specific month by using the dropdown menus above the accounts list. Click the Filter button after making your selection. You also can refine the list by clicking on the account creator in the accounts list.', 'fiscaat' ) . '</li>' .
				'</ul>'
		) );

		// Available Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'action-links',
			'title'		=> __( 'Available Actions', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'Hovering over a row in the accounts list will display action links that allow you to manage your account. You can perform the following actions:', 'fiscaat' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that account. You can also reach that screen by clicking on the account title.',                                                                                 'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Trash</strong> removes your account from this list and places it in the trash, from which you can permanently delete it.',                                                                                       'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Spam</strong> removes your account from this list and places it in the spam queue, from which you can permanently delete it.',                                                                                   'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Preview</strong> will show you what your draft account will look like if you publish it. View will take you to your live site to view the account. Which link is available depends on your account&#8217;s status.', 'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Close</strong> will mark the selected account as &#8217;closed&#8217; and disable the option to post new records to the account.',                                                                                 'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Stick</strong> will keep the selected account &#8217;pinned&#8217; to the top the parent period account list.',                                                                                                     'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Stick <em>(to front)</em></strong> will keep the selected account &#8217;pinned&#8217; to the top of ALL periods and be visable in any periods accounts list.',                                                      'fiscaat' ) . '</li>' .
				'</ul>'
		) );

		// Bulk Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'bulk-actions',
			'title'		=> __( 'Bulk Actions', 'fiscaat' ),
			'content'	=>
				'<p>' . __( 'You can also edit or move multiple accounts to the trash at once. Select the accounts you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.',           'fiscaat' ) . '</p>' .
				'<p>' . __( 'When using Bulk Edit, you can change the metadata (categories, author, etc.) for all selected accounts at once. To remove a account from the grouping, just click the x next to its name in the Bulk Edit area that appears.', 'fiscaat' ) . '</p>'
		) );

		// Help Sidebar
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'fiscaat' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.fiscaat.org" target="_blank">Fiscaat Documentation</a>',     'fiscaat' ) . '</p>' .
			'<p>' . __( '<a href="http://fiscaat.org/periods/" target="_blank">Fiscaat Support Periods</a>',  'fiscaat' ) . '</p>'
		);
	}

	/**
	 * Contextual help for Fiscaat account edit page
	 *
	 * @uses get_current_screen()
	 */
	public function new_help() {
		if ( $this->bail() ) 
			return;

		$customize_display = '<p>' . __( 'The title field and the big account editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.', 'fiscaat' ) . '</p>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'customize-display',
			'title'   => __( 'Customizing This Display', 'fiscaat' ),
			'content' => $customize_display,
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'title-account-editor',
			'title'   => __( 'Title and Account Editor', 'fiscaat' ),
			'content' =>
				'<p>' . __( '<strong>Title</strong> - Enter a title for your account. After you enter a title, you&#8217;ll see the permalink below, which you can edit.', 'fiscaat' ) . '</p>' .
				'<p>' . __( '<strong>Account Editor</strong> - Enter the text for your account. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your account text. You can insert media files by clicking the icons above the account editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in HTML mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular account editor.', 'fiscaat' ) . '</p>'
		) );

		$publish_box = '<p>' . __( '<strong>Publish</strong> - You can set the terms of publishing your account in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a account or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a account to be published in the future or backdate a account.', 'fiscaat' ) . '</p>';

		if ( current_theme_supports( 'account-formats' ) && account_type_supports( 'account', 'account-formats' ) ) {
			$publish_box .= '<p>' . __( '<strong>account Format</strong> - This designates how your theme will display a specific account. For example, you could have a <em>standard</em> blog account with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each account format</a>. Your theme could enable all or some of 10 possible formats.', 'fiscaat' ) . '</p>';
		}

		if ( current_theme_supports( 'account-thumbnails' ) && account_type_supports( 'account', 'thumbnail' ) ) {
			$publish_box .= '<p>' . __( '<strong>Featured Image</strong> - This allows you to associate an image with your account without inserting it. This is usually useful only if your theme makes use of the featured image as a account thumbnail on the home page, a custom header, etc.', 'fiscaat' ) . '</p>';
		}

		get_current_screen()->add_help_tab( array(
			'id'      => 'account-attributes',
			'title'   => __( 'Account Attributes', 'fiscaat' ),
			'content' =>
				'<p>' . __( 'Select the attributes that your account should have:', 'fiscaat' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Period</strong> dropdown determines the parent period that the account belongs to. Select the period or category from the dropdown, or leave the default (No Period) to post the account without an assigned period.', 'fiscaat' ) . '</li>' .
					'<li>' . __( '<strong>Account Type</strong> dropdown indicates the sticky status of the account. Selecting the super sticky option would stick the account to the front of your periods, i.e. the account index, sticky option would stick the account to its respective period. Selecting normal would not stick the account anywhere.', 'fiscaat' ) . '</li>' .
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
				'<p>' . __( '<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the account, you can see them here and moderate them.', 'fiscaat' ) . '</p>'
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'fiscaat' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.fiscaat.org" target="_blank">Fiscaat Documentation</a>',    'fiscaat' ) . '</p>' .
			'<p>' . __( '<a href="http://fiscaat.org/periods/" target="_blank">Fiscaat Support Periods</a>', 'fiscaat' ) . '</p>'
		);
	}

	/** Account Meta **********************************************************/

	/**
	 * Add the account attributes metabox
	 *
	 * @since 0.0.1
	 *
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'fct_account_attributes_metabox'
	 */
	public function attributes_metabox() {
		if ( $this->bail() ) 
			return;

		// Title and description
		add_action( 'edit_form_after_title', 'fct_post_name_metabox' );

		// No slug
		remove_meta_box( 'slugdiv', null, 'normal' );

		// Attributes
		add_meta_box (
			'fct_account_attributes',
			__( 'Account Attributes', 'fiscaat' ),
			'fct_account_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'fct_account_attributes_metabox' );
	}

	/**
	 * Pass the account attributes for processing
	 *
	 * @param int $account_id Account id
	 * @uses current_user_can() To check if the current user is capable of
	 *                           editing the account
	 * @uses do_action() Calls 'fct_account_attributes_metabox_save' with the
	 *                    account id and parent id
	 * @return int Parent id
	 */
	public function attributes_metabox_save( $account_id ) {
		if ( $this->bail() ) 
			return $account_id;

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $account_id;

		// Bail if not a post request
		if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
			return $account_id;

		// Nonce check
		if ( empty( $_POST['fct_account_metabox'] ) || ! wp_verify_nonce( $_POST['fct_account_metabox'], 'fct_account_metabox_save' ) )
			return $account_id;

		// Bail if current user cannot edit this account
		if ( ! current_user_can( 'edit_account', $account_id ) )
			return $account_id;

		// Get the period ID
		$period_id   = ! empty( $_POST['parent_id'] ) ? (int) $_POST['parent_id'] : fct_get_current_period_id();

		// Get the ledger ID
		$ledger_id = ! empty( $_POST['fct_account_ledger_id'] ) ? (int) $_POST['fct_account_ledger_id'] : 0;

		// Check for ledger id conflict
		fct_check_ledger_id( $account_id, $ledger_id );

		// Formally update the account
		fct_update_account( array( 
			'account_id'   => $account_id, 
			'period_id'    => $period_id,
			'ledger_id'    => $ledger_id,
			'account_type' => ! empty( $_POST['fct_account_type'] ) ? $_POST['fct_account_type'] : '',
			'is_edit'      => (bool) isset( $_POST['save'] ),
		) );

		// Allow other fun things to happen
		do_action( 'fct_account_attributes_metabox_save', $account_id, $period_id );

		return $account_id;
	}

	/** Misc ******************************************************************/

	/**
	 * Add some general styling to the admin area
	 *
	 * @uses fct_get_period_post_type() To get the period post type
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

			strong.label {
				display: inline-block;
				width: 60px;
			}

			.column-fct_period_account_count,
			.column-fct_period_record_count,
			.column-fct_account_ledger_id,
			.column-fct_account_type,
			.column-fct_account_record_count,
			.column-fct_account_end_value {
				width: 10%;
			}

			.column-author,
			.column-fct_record_author,
			.column-fct_account_author {
				width: 10%;
			}

			.column-fct_account_value,
			.column-fct_account_period,
			.column-fct_record_period,
			.column-fct_record_account {
				width: 10%;
			}

			#fct_account_attributes p span {
				vertical-align: middle;
			}

		/*]]>*/
		</style>

		<?php // On post.php and post-new.php pages ?>
		<?php if ( isset( get_current_screen()->base ) && 'post' == get_current_screen()->base ) : ?>

		<script type="text/javascript">

			/**
			 * Make an AJAX request to check if a ledger id already exists
			 *
			 * @since 0.0.1
			 */
			jQuery(document).ready( function($) {
				var $ledger_id = $('input#fct_account_ledger_id'),
				    orig_val   = $ledger_id.attr('value');
				
				// On input change (blur)
				$ledger_id.change( function() {
					var new_value = this.value;

					if ( new_value ) {
						$loader = $ledger_id.siblings('.spinner').css('display', 'inline-block');

						$.post( 
							ajaxurl, 
							{
								action: 'fct_check_ledger_id',
								account_id: <?php the_ID(); ?>,
								ledger_id: new_value
							}, 
							function ( response ) {
								var resp    = response.success ? 'success' : 'error',
								    account = response.success ? '' : response.data.post.post_title;

								// Show response icon
								$('<span class="dashicons fct-badge-' + resp + '" title="' + account + '"></span>')
									.appendTo( $loader.hide().parent() ).delay(1500).fadeOut( function() {
										$(this).remove(); // Remove element
									});

								// Ledger id exists. Reset original value
								if ( ! response.success ) {
									$ledger_id.attr('value', orig_val);

								// Overwrite new accepted value
								} else {
									orig_val = new_value;
								}
							}
						);
					}
				});
			});
		</script>

		<?php endif;
	}

	/** Ajax ******************************************************************/

	/**
	 * Ajax action for facilitating the ledger id check
	 *
	 * @uses get_posts()
	 * @uses fct_get_account_period_id()
	 * @uses fct_get_period_id()
	 * @uses fct_get_account_post_type()
	 * @uses wp_send_json_error() To return that an account was found
	 * @uses wp_send_json_success() To return that no account was found
	 */
	public function check_ledger_id() {
		global $wpdb;

		$period_id = fct_get_account_period_id( (int) $_REQUEST['account_id'] );
		$period_id = fct_get_period_id( $period_id );

		// Find any matching ledger id in the account's period
		$query = get_posts( array(
			'post_type'    => fct_get_account_post_type(),
			'post_parent'  => $period_id,
			'meta_key'     => '_fct_ledger_id',
			'meta_value'   => (int) $wpdb->esc_like( $_REQUEST['ledger_id'] ),
			'post__not_in' => array( (int) $_REQUEST['account_id'] ),
			'numberposts'  => 1
		) );

		// If we found an account, report to user
		if ( ! empty( $query ) ) {
			wp_send_json_error( array( 'post' => $query[0] ) );

		// Report no match
		} else {
			wp_send_json_success();
		}
	}

	/** List Table ************************************************************/

	/**
	 * Manage the column headers for the accounts page
	 *
	 * @param array $columns The columns
	 * @return array $columns Fiscaat account columns
	 */
	public function accounts_column_headers( $columns ) {
		if ( $this->bail() ) 
			return $columns;

		// Hide period column if showing period accounts. When there
		// is no period selection, current period accounts are showed.
		if ( ! isset( $_REQUEST['period_id'] ) || ! empty( $_REQUEST['period_id'] ) ) {

			// But not when querying drafts or trash
			if ( ! isset( $_REQUEST['post_status'] ) || ! in_array( $_REQUEST['post_status'], array( 'draft', fct_get_trash_status_id() ) ) ) {
				unset( $columns['fct_account_period'] );
			}
		}

		return $columns;
	}

	/**
	 * Add period dropdown to account and record list table filters
	 *
	 * @uses fct_ledger_dropdown() To generate a ledger id dropdown
	 * @uses fct_period_dropdown() To generate a period dropdown
	 * @uses fct_get_current_period_id() To get the current period's id
	 * @return bool False. If post type is not account or record
	 */
	public function filter_dropdown() {
		if ( $this->bail() ) 
			return;

		// Show the number dropdown
		fct_ledger_dropdown( array(
			'select_id'   => 'fct_ledger_id_filter',
			'select_name' => 'ledger_id',
			'selected'    => ! empty( $_REQUEST['ledger_id'] ) ? $_REQUEST['ledger_id'] : '',
		) );

		// Show the periods dropdown. Default to current period
		fct_period_dropdown( array(
			'select_id'   => 'fct_period_id_filter',
			'select_name' => 'period_id',
			'selected'    => ! empty( $_REQUEST['period_id'] ) ? $_REQUEST['period_id'] : ( isset( $_REQUEST['period_id'] ) ? false : fct_get_current_period_id() ),
		) );
	}

	/**
	 * Adjust the request query and include the parent period id
	 *
	 * @param array $query_vars Query variables from {@link WP_Query}
	 * @return array Processed Query Vars
	 */
	public function filter_post_rows( $query_vars ) {
		if ( $this->bail() ) 
			return $query_vars;

		// Setup meta query
		$meta_query = isset( $query_vars['meta_query'] ) ? $query_vars['meta_query'] : array();

		/** Period **************************************************************/

		// Set the parent from period id
		if ( isset( $_REQUEST['period_id'] ) ) {

			// Use only when not empty
			if ( ! empty( $_REQUEST['period_id'] ) ) {
				$query_vars['post_parent'] = (int) $_REQUEST['period_id'];
			}

		// Default to current period...
		// ... but not when querying drafts or trash
		} elseif ( ! isset( $_REQUEST['post_status'] ) || ! in_array( $_REQUEST['post_status'], array( 'draft', fct_get_trash_status_id() ) ) ) {
			$query_vars['post_parent'] = fct_get_current_period_id();
		}

		/** Post Status *******************************************************/

		// Query only public post statuses (no draft) by default
		if ( ! isset( $_REQUEST['post_status'] ) || empty( $_REQUEST['post_status'] ) ) {
			$query_vars['post_status'] = implode( ',', fct_get_post_stati( fct_get_account_post_type() ) );
		}

		/** Ledger ************************************************************/

		// Query by ledger id
		if ( ! empty( $_REQUEST['ledger_id'] ) ) {
			$meta_query[] = array(
				'key'   => '_fct_ledger_id',
				'value' => (int) $_REQUEST['ledger_id'],
			);
		}

		/** Sorting ***********************************************************/

		// Handle sorting
		$orderby = ! empty( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : '';

		// Check order type
		switch ( $orderby ) {

			// Account type
			case 'account_type' :
				$query_vars['meta_key'] = '_fct_account_type';
				$query_vars['orderby']  = 'meta_value'; // What about second orderby (ledger id)?
				break;

			// Account record count
			case 'account_record_count' :
				$query_vars['meta_key'] = '_fct_record_count';
				$query_vars['orderby']  = 'meta_value_num';
				break;

			// Account end value
			case 'account_end_value' :
				$query_vars['meta_key'] = '_fct_end_value';
				$query_vars['orderby']  = 'meta_value_num';
				break;

			// Account ledger id. Default order when none requested
			case 'account_ledger_id' :
			case '':
				$query_vars['meta_key'] = '_fct_ledger_id';
				$query_vars['orderby']  = 'meta_value_num';
				break;
		}

		// Default sorting order
		if ( ! isset( $query_vars['order'] ) ) {
			$query_vars['order'] = ! empty( $_REQUEST['order'] ) ? strtoupper( $_REQUEST['order'] ) : 'ASC';
		}

		// Set meta query
		$query_vars['meta_query'] = $meta_query;

		// Return manipulated query_vars
		return apply_filters( 'fct_admin_accounts_request', $query_vars );
	}

	/**
	 * Filter accounts query for secondary ordering
	 *
	 * Always order accounts at second hand by post date
	 * to prevent counter-intuitive listing with accounts 
	 * that have the same title or ledger id.
	 *
	 * NOTE: this may conflict with imported closed accounts.
	 *
	 * @since 0.0.9
	 * 
	 * @param array $clauses Query clauses
	 * @param WP_Query $query
	 * @return array Clauses
	 */
	public function filter_post_order( $clauses, $query ) {
		global $wpdb;

		if ( $this->bail() )
			return $clauses;

		// Unless post date is the primary order, add second order by post date
		if ( isset( $query->query_vars['orderby'] ) && 'date' != $query->query_vars['orderby'] ) {

			// Be sure ORDER BY clause isn't emptied
			$sep = ! empty( $clauses['orderby'] ) ? ',' : '';

			// Append to clause
			$clauses['orderby'] .= $sep . " $wpdb->posts.post_date DESC";
		}

		return $clauses;
	}

	/**
	 * Define post states that are appended to the post title
	 *
	 * @since 0.0.9
	 *
	 * @uses fct_is_account_closed()
	 * @uses fct_get_closed_status_id()
	 * 
	 * @param array $post_states Post states
	 * @param object $account Account post data
	 * @return array Post states
	 */
	public function account_post_states( $post_states, $account ) {
		if ( $this->bail() )
			return $post_states;

		// Closed post state
		if ( fct_is_account_closed( $account->ID ) ) {
			$post_states[ fct_get_closed_status_id() ] = __( 'Closed', 'fiscaat' );
		}

		return $post_states;
	}

	/** Bulk Actions **********************************************************/

	/**
	 * Add accounts bulk actions
	 *
	 * @since 0.0.9
	 *
	 * @uses fct_get_trash_status_id()
	 * @uses fct_get_closed_status_id()
	 * 
	 * @param array $actions Bulk actions
	 * @return array Bulk actions
	 */
	public function accounts_bulk_actions( $actions ) {

		// Setup local vars
		$_actions    = array();
		$post_status = isset( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : '';

		// Not on trash or draft pages
		if ( ! in_array( $post_status, array( 'draft', fct_get_trash_status_id() ) ) && current_user_can( 'close_accounts' ) ) {

			// Close
			if ( fct_get_closed_status_id() != $post_status ) {
				$_actions['close'] = _x( 'Close', 'bulk action', 'fiscaat' );
			}

			// Open
			$_actions['open'] = _x( 'Open', 'bulk action', 'fiscaat' );
		}

		// Prepend close/open actions
		$actions = array_merge( $_actions, $actions );
		return $actions;
	}

	/**
	 * Process accounts close bulk action
	 *
	 * @since 0.0.9
	 *
	 * @uses fct_is_account_closed()
	 * @uses fct_close_account()
	 * 
	 * @param string $sendback Redirect url
	 * @param string $doaction Bulk action
	 * @param array $post_ids Post ids
	 * @return string Redirect url
	 */
	public function bulk_action_close( $sendback, $post_ids ) {

		// Setup local var
		$closed = 0;

		foreach ( $post_ids as $post_id ) {
			if ( ! current_user_can( 'close_account', $post_id ) )
				wp_die( __( 'You are not allowed to close this item.', 'fiscaat' ) );

			if ( fct_is_account_closed( $post_id ) )
				continue;

			if ( ! fct_close_account( $post_id ) )
				wp_die( __( 'Error in closing.', 'fiscaat' ) );

			$closed++;
		}

		return add_query_arg( 'closed', $closed, $sendback );
	}

	/**
	 * Process accounts open bulk action
	 *
	 * @since 0.0.9
	 *
	 * @uses fct_is_account_open()
	 * @uses fct_open_account()
	 * 
	 * @param string $sendback Redirect url
	 * @param string $doaction Bulk action
	 * @param array $post_ids Post ids
	 * @return string Redirect url
	 */
	public function bulk_action_open( $sendback, $post_ids ) {

		// Setup local var
		$opened = 0;

		foreach ( $post_ids as $post_id ) {
			if ( ! current_user_can( 'close_account', $post_id ) )
				wp_die( __( 'You are not allowed to open this item.', 'fiscaat' ) );

			if ( fct_is_account_open( $post_id ) )
				continue;

			if ( ! fct_open_account( $post_id ) )
				wp_die( __( 'Error in opening.', 'fiscaat' ) );

			$opened++;
		}

		return add_query_arg( 'opened', $opened, $sendback );
	}

	/**
	 * Add to the args to remove from the bulk query
	 *
	 * @since 0.0.9
	 * 
	 * @param array $args Query args
	 * @return array Query args
	 */
	public function bulk_remove_query_args( $args ) {
		if ( $this->bail() )
			return $args;

		// Add query args
		$args[] = 'closed';
		$args[] = 'opened';

		return $args;
	}

	/** Post Actions **********************************************************/

	/**
	 * Account Row actions
	 *
	 * Add the view/records/close/open/delete action links under the account title
	 *
	 * @since 0.0.1
	 *
	 * @param array $actions Actions
	 * @param array $account Account object
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses fct_get_account_permalink() To get the account link
	 * @uses fct_get_account_title() To get the account title
	 * @uses current_user_can() To check if the current user can edit or
	 *                           delete the account
	 * @uses fct_is_account_open() To check if the account is open
	 * @uses add_query_arg() To add custom args to the url
	 * @uses remove_query_arg() To remove custom args from the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses get_delete_post_link() To get the delete post link of the account
	 * @return array $actions Actions
	 */
	public function account_row_actions( $actions, $account ) {
		if ( $this->bail() ) 
			return $actions;

		// Only when account is published
		if ( ! in_array( $account->post_status, array( 'draft', fct_get_trash_status_id() ) ) ) {

			// Show records link
			if ( current_user_can( 'read_account', $account->ID ) ) {
				$actions['records'] = '<a href="' . add_query_arg( array( 'page' => 'fct-records', 'account_id' => $account->ID ), admin_url( 'admin.php' ) ) .'" title="' . esc_attr( sprintf( __( 'Show all records of "%s"',  'fiscaat' ), fct_get_account_title( $account->ID ) ) ) . '">' . __( 'Records', 'fiscaat' ) . '</a>';
			}

			// Show the close and open link
			if ( current_user_can( 'close_account', $account->ID ) ) {
				$close_uri = esc_url( wp_nonce_url( add_query_arg( array( 'account_id' => $account->ID, 'action' => 'fct_toggle_account_close' ), remove_query_arg( array( 'fct_account_toggle_notice', 'account_id', 'failed', 'super' ) ) ), 'close-account_' . $account->ID ) );
				if ( fct_is_account_open( $account->ID ) ) {
					$actions['close'] = '<a href="' . $close_uri . '" title="' . esc_attr__( 'Close this account', 'fiscaat' ) . '">' . _x( 'Close', 'Close the account', 'fiscaat' ) . '</a>';
				} else {
					$actions['open']  = '<a href="' . $close_uri . '" title="' . esc_attr__( 'Open this account',  'fiscaat' ) . '">' . _x( 'Open',  'Open the account',  'fiscaat' ) . '</a>';
				}
			}
		}

		// Only show delete links for empty accounts
		if ( current_user_can( 'delete_account', $account->ID ) && ! fct_account_has_records() ) {
			if ( fct_get_trash_status_id() == $account->post_status ) {
				$post_type_object = get_post_type_object( fct_get_account_post_type() );
				$actions['untrash'] = "<a title='" . esc_attr__( 'Restore this item from the Trash', 'fiscaat' ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'page' => 'fct-accounts' ), admin_url( 'admin.php' ) ) ), wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $account->ID ) ), 'untrash-post_' . $account->ID ) ) . "'>" . __( 'Restore', 'fiscaat' ) . "</a>";
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Move this item to the Trash', 'fiscaat' ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'page' => 'fct-accounts' ), admin_url( 'admin.php' ) ) ), get_delete_post_link( $account->ID ) ) . "'>" . __( 'Trash', 'fiscaat' ) . "</a>";
			}

			if ( fct_get_trash_status_id() == $account->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Delete this item permanently', 'fiscaat' ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'page' => 'fct-accounts' ), admin_url( 'admin.php' ) ) ), get_delete_post_link( $account->ID, '', true ) ) . "'>" . __( 'Delete', 'fiscaat' ) . "</a>";
			}
		}

		// Remove manipulative actions when parent period is closed
		if ( fct_is_period_closed( fct_get_account_period_id( $account->ID ) ) ) {
			foreach ( array( 'edit', 'close', 'open', 'untrash', 'trash', 'delete' ) as $action ) {
				unset( $actions[ $action ] );
			}
		}

		return $actions;
	}

	/**
	 * Toggle account
	 *
	 * Handles the admin-side opening/closing of accounts
	 *
	 * @uses fct_get_account() To get the account
	 * @uses current_user_can() To check if the user is capable of editing
	 *                           the account
	 * @uses wp_die() To die if the user isn't capable or the post wasn't
	 *                 found
	 * @uses check_admin_referer() To verify the nonce and check referer
	 * @uses fct_is_account_open() To check if the account is open
	 * @uses fct_close_account() To close the account
	 * @uses fct_open_account() To open the account
	 * @uses do_action() Calls 'fct_toggle_account_admin' with success, post
	 *                    data, action and message
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_safe_redirect() Redirect the page to custom url
	 */
	public function toggle_account() {
		if ( $this->bail() ) 
			return;

		// Only proceed if GET is an account toggle action
		if ( 'GET' == $_SERVER['REQUEST_METHOD'] && ! empty( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'fct_toggle_account_close' ) ) && ! empty( $_REQUEST['account_id'] ) ) {
			$action     = $_REQUEST['action'];                // What action is taking place?
			$account_id = (int) $_REQUEST['account_id'];      // What's the account id?
			$success    = false;                          // Flag
			$post_data  = array( 'ID' => $account_id );   // Prelim array
			$account    = fct_get_account( $account_id );

			// Bail if account is missing
			if ( empty( $account ) )
				wp_die( __( 'The account was not found!', 'fiscaat' ) );

			switch ( $action ) {
				case 'fct_toggle_account_close' :
					check_admin_referer( 'close-account_' . $account_id );

					if ( ! current_user_can( 'close_account', $account->ID ) ) // What is the user doing here?
						wp_die( __( 'You do not have the permission to do that!', 'fiscaat' ) );

					$is_open = fct_is_account_open( $account_id );
					$message = true == $is_open ? 'closed' : 'opened';
					$success = true == $is_open ? fct_close_account( $account_id ) : fct_open_account( $account_id );

					break;
			}

			$message = array( 'fct_account_toggle_notice' => $message, 'account_id' => $account->ID );

			if ( false == $success || is_wp_error( $success ) )
				$message['failed'] = '1';

			// Do additional account toggle actions (admin side)
			do_action( 'fct_toggle_account_admin', $success, $post_data, $action, $message );

			// Redirect back to the account
			$redirect = add_query_arg( $message, remove_query_arg( array( 'action', 'account_id', '_wpnonce' ) ) );
			wp_safe_redirect( $redirect );

			// For good measure
			exit();
		}
	}

	/**
	 * Toggle account notices
	 *
	 * Display the success/error notices from
	 * {@link Fiscaat_Accounts_Admin::toggle_account()}
	 *
	 * @since 0.0.1
	 *
	 * @uses fct_get_account() To get the account
	 * @uses fct_get_account_title() To get the account title of the account
	 * @uses esc_html() To sanitize the account title
	 * @uses apply_filters() Calls 'fct_toggle_account_notice_admin' with
	 *                        message, account id, notice and is it a failure
	 */
	public function toggle_account_notice() {
		if ( $this->bail() ) 
			return;

		// Only proceed if GET is a account toggle action
		if ( 'GET' == $_SERVER['REQUEST_METHOD'] && ! empty( $_REQUEST['fct_account_toggle_notice'] ) && in_array( $_REQUEST['fct_account_toggle_notice'], array( 'opened', 'closed' ) ) && !empty( $_REQUEST['account_id'] ) ) {
			$notice     = $_REQUEST['fct_account_toggle_notice'];       // Which notice?
			$account_id = (int) $_REQUEST['account_id'];                // What's the account id?
			$is_failure = !empty( $_REQUEST['failed'] ) ? true : false; // Was that a failure?

			// Bais if no account_id or notice
			if ( empty( $notice ) || empty( $account_id ) )
				return;

			// Bail if account is missing
			$account = fct_get_account( $account_id );
			if ( empty( $account ) )
				return;

			$account_title = esc_html( fct_get_account_title( $account->ID ) );
			$period_title  = esc_html( fct_get_period_title( fct_get_account_period_id( $account->ID ) ) );

			switch ( $notice ) {
				case 'opened' :
					/* translators: 1: account title, 2: account's period title */
					$message = $is_failure == true ? sprintf( __( 'There was a problem opening the account "%1$s" in "%2$s".', 'fiscaat' ), $account_title, $period_title ) : sprintf( __( 'Account "%1$s" in "%2$s" successfully opened.', 'fiscaat' ), $account_title, $period_title );
					break;

				case 'closed' :
					/* translators: 1: account title, 2: account's period title */
					$message = $is_failure == true ? sprintf( __( 'There was a problem closing the account "%1$s" in "%2$s".', 'fiscaat' ), $account_title, $period_title ) : sprintf( __( 'Account "%1$s" in "%2$s" successfully closed.', 'fiscaat' ), $account_title, $period_title );
					break;
			}

			// Do additional account toggle notice filters (admin side)
			$message = apply_filters( 'fct_toggle_account_notice_admin', $message, $account->ID, $notice, $is_failure );

			?>

			<div id="message" class="<?php echo $is_failure == true ? 'error' : 'updated'; ?> fade">
				<p style="line-height: 150%"><?php echo $message; ?></p>
			</div>

			<?php
		}
	}

	/** Missing ***************************************************************/

	/**
	 * Redirect user to accounts page when missing an open period
	 *
	 * @uses fct_has_open_period()
	 * @uses add_query_arg()
	 * @uses wp_safe_redirect()
	 */
	public function missing_redirect() {
		if ( $this->bail() ) 
			return;

		// Fiscaat has no open period
		if ( ! fct_has_open_period() ) {

			// Redirect to accounts page
			wp_safe_redirect( add_query_arg( array( 'page' => 'fct-accounts' ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	/**
	 * Display missing notice
	 *
	 * @since 0.0.9
	 *
	 * @uses fct_has_open_period()
	 * @uses current_user_can()
	 * @uses add_query_arg()
	 * @uses fct_get_period_post_type()
	 */
	public function missing_notices() {
		if ( $this->bail() ) 
			return;

		// Fiscaat has no open period
		if ( ! fct_has_open_period() && current_user_can( 'create_periods' ) ) : ?>

			<div id="message" class="error">
				<p style="line-height: 150%"><?php printf( __( 'There is currently no open period to manage accounts in. <a href="%s">Create a new period</a>.', 'fiscaat' ), add_query_arg( 'post_type', fct_get_period_post_type(), admin_url( 'post-new.php' ) ) ); ?></p>
			</div>

		<?php endif;
	}

	/** Messages **************************************************************/

	/**
	 * Custom user feedback messages for account post type
	 *
	 * @global int $post_ID
	 * @uses fct_get_account_permalink()
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

		// URL for the current account
		$account_url = fct_get_account_permalink( $post_ID );

		// Current account's post_date
		$post_date = fct_get_global_post_field( 'post_date', 'raw' );

		// Messages array
		$messages[$this->post_type] = array(
			0 =>  '', // Left empty on purpose

			// Updated
			1 =>  sprintf( __( 'Account updated. <a href="%s">View account</a>', 'fiscaat' ), $account_url ),

			// Custom field updated
			2 => __( 'Custom field updated.', 'fiscaat' ),

			// Custom field deleted
			3 => __( 'Custom field deleted.', 'fiscaat' ),

			// Account updated
			4 => __( 'Account updated.', 'fiscaat' ),

			// Restored from revision
			// translators: %s: date and time of the revision
			5 => isset( $_REQUEST['revision'] )
					? sprintf( __( 'Account restored to revision from %s', 'fiscaat' ), wp_post_revision_title( (int) $_REQUEST['revision'], false ) )
					: false,

			// Account created
			6 => sprintf( __( 'Account created. <a href="%s">View account</a>', 'fiscaat' ), $account_url ),

			// Account saved
			7 => __( 'Account saved.', 'fiscaat' ),

			// Account submitted
			8 => sprintf( __( 'Account submitted. <a target="_blank" href="%s">Preview account</a>', 'fiscaat' ), esc_url( add_query_arg( 'preview', 'true', $account_url ) ) ),

			// Account scheduled
			9 => sprintf( __( 'Account scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview account</a>', 'fiscaat' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'fiscaat' ),
					strtotime( $post_date ) ),
					$account_url ),

			// Account draft updated
			10 => sprintf( __( 'Account draft updated. <a target="_blank" href="%s">Preview account</a>', 'fiscaat' ), esc_url( add_query_arg( 'preview', 'true', $account_url ) ) ),

			// Require a period
			11 => sprintf( __( 'Using Fiscaat requires an open period to register accounts in. <a href="%s">Create a period first</a>.', 'fiscaat' ), esc_url( add_query_arg( 'post_type', fct_get_period_post_type(), admin_url( 'post-new.php' ) ) ) ),

			// Account number already taken
			12 => isset( $_REQUEST['ledger_id'] )
					? sprintf( __( 'The account number <strong>%d</strong> is already taken by <a href="%s">%s</a>. Use another number!', 'fiscaat' ), (int) $_REQUEST['ledger_id'], esc_url( add_query_arg( array( 'post' => fct_get_account_id_by_ledger_id( (int) $_REQUEST['ledger_id'] ), 'action' => 'edit' ), admin_url( 'post.php' ) ) ), fct_get_account_title( fct_get_account_id_by_ledger_id( (int) $_REQUEST['ledger_id'] ) ) )
					: false,

			// Account number required
			13 => __( 'No account number submitted. Please assign a unique number to this account.', 'fiscaat' ),
		);

		return $messages;
	}

	/** Page Title ************************************************************/

	/**
	 * Manipulate the accounts posts page title
	 * 
	 * @uses fct_get_period_title() To get the period title
	 * @return array Modified arguments
	 */
	public function accounts_page_title( $title ) {

		// Period accounts
		if ( ! isset( $_REQUEST['period_id'] ) || ! empty( $_REQUEST['period_id'] ) ) {

			// Check period id
			$selected  = isset( $_REQUEST['period_id'] ) ? $_REQUEST['period_id'] : fct_get_current_period_id();
			$period_id = fct_get_period_id( $selected );

			if ( ! empty( $period_id ) ) {
				// Format: {title} -- {period title}
				$title .= ' &mdash; '. fct_get_period_title( $period_id );
			}
		}

		return $title;
	}

	/**
	 * Append post-new link to page title
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_has_open_period()
	 * @uses fct_admin_page_title_get_add_new_link()
	 * @param string $title Page title
	 * @return string Page title
	 */
	public function post_new_link( $title ) {

		// Require open period
		if ( fct_has_open_period() ) {
			$title .= fct_admin_page_title_get_add_new_link();
		}

		return $title;
	}
}

endif; // class_exists check

/**
 * Setup Fiscaat Accounts Admin
 *
 * This is currently here to make hooking and unhooking of the admin UI easy.
 * It could use dependency injection in the future, but for now this is easier.
 *
 * @uses Fiscaat_Accounts_Admin
 */
function fct_admin_accounts() {
	fiscaat()->admin->accounts = new Fiscaat_Accounts_Admin();
}
