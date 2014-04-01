<?php

/**
 * Fiscaat Common Template Tags
 *
 * Common template tags are ones that are used by more than one component, like
 * years, accounts, records, etc...
 *
 * @package Fiscaat
 * @subpackage TemplateTags
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** URLs **********************************************************************/

/**
 * Ouput the year URL
 * 
 * @uses fct_get_years_url() To get the years URL
 * @param string $path Additional path with leading slash
 */
function fct_years_url( $path = '/' ) {
	echo fct_get_years_url( $path );
}
	/**
	 * Return the year URL
	 * 
	 * @uses home_url() To get the home URL
	 * @uses fct_get_root_slug() To get the year root location
	 * @param string $path Additional path with leading slash
	 */
	function fct_get_years_url( $path = '/' ) {
		return home_url( fct_get_root_slug() . $path );
	}

/**
 * Ouput the year URL
 *
 * @uses fct_get_accounts_url() To get the accounts URL
 * @param string $path Additional path with leading slash
 */
function fct_accounts_url( $path = '/' ) {
	echo fct_get_accounts_url( $path );
}
	/**
	 * Return the year URL
	 *
	 * @uses home_url() To get the home URL
	 * @uses fct_get_account_archive_slug() To get the accounts archive location
	 * @param string $path Additional path with leading slash
	 * @return The URL to the accounts archive
	 */
	function fct_get_accounts_url( $path = '/' ) {
		return home_url( fct_get_account_archive_slug() . $path );
	}

/** Add-on Actions ************************************************************/

/**
 * Add our custom head action to wp_head
 *
 * @uses do_action() Calls 'fct_head'
*/
function fct_head() {
	do_action( 'fct_head' );
}

/**
 * Add our custom head action to wp_head
 *
 * @uses do_action() Calls 'fct_footer'
 */
function fct_footer() {
	do_action( 'fct_footer' );
}

/** is_ ***********************************************************************/

/**
 * Check if current page is a Fiscaat year
 *
 * @param int $post_id Possible post_id to check
 * @uses fct_get_year_post_type() To get the year post type
 * @return bool True if it's a year page, false if not
 */
function fct_is_year( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a year
	if ( ! empty( $post_id ) && ( fct_get_year_post_type() == get_post_type( $post_id ) ))
		$retval = true;

	return (bool) apply_filters( 'fct_is_year', $retval, $post_id );
}

/**
 * Check if we are viewing a year archive.
 *
 * @uses is_post_type_archive() To check if we are looking at the year archive
 * @uses fct_get_year_post_type() To get the year post type ID
 *
 * @return bool
 */
function fct_is_year_archive() {

	// Default to false
	$retval = false;

	// In year archive
	if ( is_post_type_archive( fct_get_year_post_type() ) || fct_is_query_name( 'fct_year_archive' ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_year_archive', $retval );
}

/**
 * Viewing a single year
 *
 * @uses is_single()
 * @uses fct_get_year_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function fct_is_single_year() {

	// Assume false
	$retval = false;

	// Edit is not a single year
	if ( fct_is_year_edit() )
		return false;

	// Single and a match
	if ( is_singular( fct_get_year_post_type() ) || fct_is_query_name( 'fct_single_year' ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_single_year', $retval );
}

/**
 * Check if current page is a year edit page
 *
 * @uses WP_Query Checks if WP_Query::fct_is_year_edit is true
 * @return bool True if it's the year edit page, false if not
 */
function fct_is_year_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->fct_is_year_edit ) && ( $wp_query->fct_is_year_edit == true ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' == $pagenow ) && ( get_post_type() == fct_get_year_post_type() ) && ( ! empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_year_edit', $retval );
}

/**
 * Check if current page is a Fiscaat account
 *
 * @param int $post_id Possible post_id to check
 * @uses fct_get_account_post_type() To get the account post type
 * @uses get_post_type() To get the post type of the post id
 * @return bool True if it's a account page, false if not
 */
function fct_is_account( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a account
	if ( ! empty( $post_id ) && ( fct_get_account_post_type() == get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_account', $retval, $post_id );
}

/**
 * Viewing a single account
 *
 * @uses is_single()
 * @uses fct_get_account_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function fct_is_single_account() {

	// Assume false
	$retval = false;

	// Edit is not a single account
	if ( fct_is_account_edit() )
		return false;

	// Single and a match
	if ( is_singular( fct_get_account_post_type() ) || fct_is_query_name( 'fct_single_account' ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_single_account', $retval );
}

/**
 * Check if we are viewing a account archive.
 *
 * @uses is_post_type_archive() To check if we are looking at the account archive
 * @uses fct_get_account_post_type() To get the account post type ID
 *
 * @return bool
 */
function fct_is_account_archive() {

	// Default to false
	$retval = false;

	// In account archive
	if ( is_post_type_archive( fct_get_account_post_type() ) || fct_is_query_name( 'fct_account_archive' ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_account_archive', $retval );
}

/**
 * Check if current page is a account edit page
 *
 * @uses WP_Query Checks if WP_Query::fct_is_account_edit is true
 * @return bool True if it's the account edit page, false if not
 */
function fct_is_account_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->fct_is_account_edit ) && ( $wp_query->fct_is_account_edit == true ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' == $pagenow ) && ( get_post_type() == fct_get_account_post_type() ) && ( ! empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_account_edit', $retval );
}

/**
 * Check if the current post type is one of Fiscaat's
 *
 * @param mixed $the_post Optional. Post object or post ID.
 * @uses get_post_type()
 * @uses fct_get_year_post_type()
 * @uses fct_get_account_post_type()
 * @uses fct_get_record_post_type()
 *
 * @return bool
 */
function fct_is_custom_post_type( $the_post = false ) {

	// Assume false
	$retval = false;

	// Viewing one of the Fiscaat post types
	if ( in_array( get_post_type( $the_post ), array(
		fct_get_year_post_type(),
		fct_get_account_post_type(),
		fct_get_record_post_type()
	) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_custom_post_type', $retval, $the_post );
}

/**
 * Check if current page is a Fiscaat record
 *
 * @param int $post_id Possible post_id to check
 * @uses fct_get_record_post_type() To get the record post type
 * @uses get_post_type() To get the post type of the post id
 * @return bool True if it's a record page, false if not
 */
function fct_is_record( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a record
	if ( ! empty( $post_id ) && ( fct_get_record_post_type() == get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_record', $retval, $post_id );
}

/**
 * Check if current page is a record edit page
 *
 * @uses WP_Query Checks if WP_Query::fct_is_record_edit is true
 * @return bool True if it's the record edit page, false if not
 */
function fct_is_record_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->fct_is_record_edit ) && ( true == $wp_query->fct_is_record_edit ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' == $pagenow ) && ( get_post_type() == fct_get_record_post_type() ) && ( ! empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_record_edit', $retval );
}

/**
 * Viewing a single record
 *
 * @uses is_single()
 * @uses fct_get_record_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function fct_is_single_record() {

	// Assume false
	$retval = false;

	// Edit is not a single record
	if ( fct_is_record_edit() )
		return false;

	// Single and a match
	if ( is_singular( fct_get_record_post_type() ) || ( fct_is_query_name( 'fct_single_record' ) ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_single_record', $retval );
}

/**
 * Check if current page is a view page
 *
 * @global WP_Query $wp_query To check if WP_Query::fct_is_view is true 
 * @uses fct_is_query_name() To get the query name
 * @return bool Is it a view page?
 */
function fct_is_single_view() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->fct_is_view ) && ( true == $wp_query->fct_is_view ) )
		$retval = true;

	// Check query name
	if ( empty( $retval ) && fct_is_query_name( 'fct_single_view' ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_single_view', $retval );
}

/**
 * Check if current page is an edit page
 *
 * @uses WP_Query Checks if WP_Query::fct_is_edit is true
 * @return bool True if it's the edit page, false if not
 */
function fct_is_edit() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->fct_is_edit ) && ( $wp_query->fct_is_edit == true ) )
		$retval = true;

	return (bool) apply_filters( 'fct_is_edit', $retval );
}

/**
 * Use the above is_() functions to output a body class for each scenario
 *
 * @since Fiscaat (r2926)
 *
 * @param array $wp_classes
 * @param array $custom_classes
 * @uses fct_is_single_year()
 * @uses fct_is_single_account()
 * @uses fct_is_single_record()
 * @uses fct_is_record_edit()
 * @uses fct_is_single_view()
 * @uses fct_is_year_archive()
 * @uses fct_is_account_archive()
 * @return array Body Classes
 */
function fct_body_class( $wp_classes, $custom_classes = false ) {

	$fct_classes = array();

	/** Archives **************************************************************/

	if ( fct_is_year_archive() )
		$fct_classes[] = fct_get_year_post_type() . '-archive';

	if ( fct_is_account_archive() )
		$fct_classes[] = fct_get_account_post_type() . '-archive';

	/** Components ************************************************************/

	if ( fct_is_single_year() )
		$fct_classes[] = fct_get_year_post_type();

	if ( fct_is_single_account() )
		$fct_classes[] = fct_get_account_post_type();

	if ( fct_is_single_record() )
		$fct_classes[] = fct_get_record_post_type();

	if ( fct_is_account_edit() )
		$fct_classes[] = fct_get_account_post_type() . '-edit';

	if ( fct_is_record_edit() )
		$fct_classes[] = fct_get_record_post_type() . '-edit';

	if ( fct_is_single_view() )
		$fct_classes[] = 'fiscaat-view';

	/** Clean up **************************************************************/

	// Add Fiscaat class if we are within a Fiscaat page
	if ( ! empty( $fct_classes ) )
		$fct_classes[] = 'Fiscaat';

	// Merge WP classes with Fiscaat classes and remove any duplicates
	$classes = array_unique( array_merge( (array) $fct_classes, (array) $wp_classes ) );

	return apply_filters( 'fct_get_the_body_class', $classes, $fct_classes, $wp_classes, $custom_classes );
}

/**
 * Use the above is_() functions to return if in any Fiscaat page
 *
 * @uses fct_is_single_year()
 * @uses fct_is_single_account()
 * @uses fct_is_single_record()
 * @uses fct_is_record_edit()
 * @uses fct_is_record_edit()
 * @uses fct_is_single_view()
 * @return bool In a Fiscaat page
 */
function is_fiscaat() {

	// Defalt to false
	$retval = false;

	/** Archives **************************************************************/

	if ( fct_is_year_archive() )
		$retval = true;

	elseif ( fct_is_account_archive() )
		$retval = true;

	/** Components ************************************************************/

	elseif ( fct_is_single_year() )
		$retval = true;

	elseif ( fct_is_single_account() )
		$retval = true;

	elseif ( fct_is_single_record() )
		$retval = true;

	elseif ( fct_is_account_edit() )
		$retval = true;

	elseif ( fct_is_record_edit() )
		$retval = true;

	elseif ( fct_is_single_view() )
		$retval = true;

	/** Done ******************************************************************/

	return (bool) apply_filters( 'is_fiscaat', $retval );
}

/** Listeners *****************************************************************/

/**
 * Check if it's a year or a account or record of a year and if
 * the user can't view it, then sets a 404
 *
 * @uses current_user_can() To check if the current user can fiscaat
 * @uses is_singular() To check if it's a singular page
 * @uses fct_get_year_post_type() To get the year post type
 * @uses fct_get_account_post_type() To get the account post type
 * @uses fct_get_record_post_type() TO get the record post type
 * @uses fct_get_account_year_id() To get the account year id
 * @uses fct_get_record_year_id() To get the record year id
 * @uses fct_user_can_spectate() To check if the year is closed or not
 * @uses fct_set_404() To set a 404 status
 */
function fct_enforce_404() {

	// Bail if not viewing a single item or if user has caps
	if ( !is_singular() || current_user_can( 'fiscaat' ) || current_user_can( 'control' ) ) // || is_super_admin() ?
		return;

	global $wp_query;

	// Define local variables
	$year_id = 0;

	// Check post type
	switch ( $wp_query->get( 'post_type' ) ) {

		// Year
		case fct_get_year_post_type() :
			$year_id = fct_get_year_id( $wp_query->post->ID );
			break;

		// Topic
		case fct_get_account_post_type() :
			$year_id = fct_get_account_year_id( $wp_query->post->ID );
			break;

		// Reply
		case fct_get_record_post_type() :
			$year_id = fct_get_record_year_id( $wp_query->post->ID );
			break;
	}

	// If page is Fiscaat and user is not capable, set 404
	if ( ! empty( $year_id ) && ! fct_user_can_spectate( $wp_query->post->ID ) )
		fct_set_404();
}

/** Forms *********************************************************************/

/**
 * Output hidden request URI field for user forms.
 *
 * The referer link is the current Request URI from the server super global. The
 * input name is '_wp_http_referer', in case you wanted to check manually.
 *
 * @param string $url Pass a URL to redirect to
 * @uses wp_get_referer() To get the referer
 * @uses esc_attr() To escape the url
 * @uses apply_filters() Calls 'fct_redirect_to_field' with the referer field
 *                        and url
 */
function fct_redirect_to_field( $redirect_to = '' ) {

	// Rejig the $redirect_to
	if ( !isset( $_SERVER['REDIRECT_URL'] ) || ( !$redirect_to == home_url( $_SERVER['REDIRECT_URL'] ) ) )
		$redirect_to = wp_get_referer();

	// Make sure we are directing somewhere
	if ( empty( $redirect_to ) )
		$redirect_to = home_url( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );

	// Remove loggedout query arg if it's there
	$redirect_to    = (string) esc_attr( remove_query_arg( 'loggedout', $redirect_to ) );
	$redirect_field = '<input type="hidden" id="fct_redirect_to" name="redirect_to" value="' . $redirect_to . '" />';

	echo apply_filters( 'fct_redirect_to_field', $redirect_field, $redirect_to );
}

/**
 * Echo sanitized $_REQUEST value.
 *
 * Use the $input_type parameter to properly process the value. This
 * ensures correct sanitization of the value for the receiving input.
 *
 * @param string $request Name of $_REQUEST to look for
 * @param string $input_type Type of input. Default: text. Accepts:
 *                            textarea|password|select|radio|checkbox
 * @uses fct_get_sanitize_val() To sanitize the value.
 */
function fct_sanitize_val( $request = '', $input_type = 'text' ) {
	echo fct_get_sanitize_val( $request, $input_type );
}
	/**
	 * Return sanitized $_REQUEST value.
	 *
	 * Use the $input_type parameter to properly process the value. This
	 * ensures correct sanitization of the value for the receiving input.
	 *
	 * @param string $request Name of $_REQUEST to look for
	 * @param string $input_type Type of input. Default: text. Accepts:
	 *                            textarea|password|select|radio|checkbox
	 * @uses esc_attr() To escape the string
	 * @uses apply_filters() Calls 'fct_get_sanitize_val' with the sanitized
	 *                        value, request and input type
	 * @return string Sanitized value ready for screen display
	 */
	function fct_get_sanitize_val( $request = '', $input_type = 'text' ) {

		// Check that requested
		if ( empty( $_REQUEST[$request] ) )
			return false;

		// Set request varaible
		$pre_ret_val = $_REQUEST[$request];

		// Treat different kinds of fields in different ways
		switch ( $input_type ) {
			case 'text'     :
			case 'textarea' :
				$retval = esc_attr( stripslashes( $pre_ret_val ) );
				break;

			case 'password' :
			case 'select'   :
			case 'radio'    :
			case 'checkbox' :
			default :
				$retval = esc_attr( $pre_ret_val );
				break;
		}

		return apply_filters( 'fct_get_sanitize_val', $retval, $request, $input_type );
	}

/**
 * Output the current tab index of a given form
 *
 * Use this function to handle the tab indexing of user facing forms within a
 * template file. Calling this function will automatically increment the global
 * tab index by default.
 *
 * @param int $auto_increment Optional. Default true. Set to false to prevent
 *                             increment
 */
function fct_tab_index( $auto_increment = true ) {
	echo fct_get_tab_index( $auto_increment );
}

	/**
	 * Output the current tab index of a given form
	 *
	 * Use this function to handle the tab indexing of user facing forms
	 * within a template file. Calling this function will automatically
	 * increment the global tab index by default.
	 *
	 * @uses apply_filters Allows return value to be filtered
	 * @param int $auto_increment Optional. Default true. Set to false to
	 *                             prevent the increment
	 * @return int $fiscaat->tab_index The global tab index
	 */
	function fct_get_tab_index( $auto_increment = true ) {
		$fiscaat = fiscaat();

		if ( true === $auto_increment )
			++$fiscaat->tab_index;

		return apply_filters( 'fct_get_tab_index', (int) $fiscaat->tab_index );
	}

/**
 * Output a select box allowing to pick which year/account a new account/record
 * belongs in.
 *
 * Can be used for any post type, but is mostly used for accounts and years.
 *
 * @param mixed $args See {@link fct_get_dropdown()} for arguments
 */
function fct_dropdown( $args = '' ) {
	echo fct_get_dropdown( $args );
}
	/**
	 * Output a select box allowing to pick which year/account a new
	 * account/record belongs in.
	 *
	 * @param mixed $args The function supports these args:
	 *  - post_type: Post type, defaults to fct_get_year_post_type() (fct_year)
	 *  - selected: Selected ID, to not have any value as selected, pass
	 *               anything smaller than 0 (due to the nature of select
	 *               box, the first value would of course be selected -
	 *               though you can have that as none (pass 'show_none' arg))
	 *  - sort_column: Sort by? Defaults to 'menu_order, post_title'
	 *  - child_of: Child of. Defaults to 0
	 *  - post_status: Which all post_statuses to find in? Can be an array
	 *                  or CSV of publish, category, closed, private, spam,
	 *                  trash (based on post type) - if not set, these are
	 *                  automatically determined based on the post_type
	 *  - posts_per_page: Retrieve all years/accounts. Defaults to -1 to get
	 *                     all posts
	 *  - walker: Which walker to use? Defaults to {@link Fiscaat_Walker_Dropdown}
	 *  - select_id: ID of the select box. Defaults to 'fct_year_id'
	 *  - tab: Tabindex value. False or integer
	 *  - options_only: Show only <options>? No <select>?
	 *  - show_none: False or something like __( '(No Year)', 'fiscaat' ),
	 *                will have value=""
	 *  - none_found: False or something like
	 *                 __( 'No years to post to!', 'fiscaat' )
	 *  - disable_closed: Disable closed years? Defaults to true. Only for years.
	 * @uses Fiscaat_Walker_Dropdown() As the default walker to generate the
	 *                              dropdown
	 * @uses current_user_can() To check if the current user can read
	 *                           private years
	 * @uses fct_get_year_post_type() To get the year post type
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses walk_page_dropdown_tree() To generate the dropdown using the
	 *                                  walker
	 * @uses apply_filters() Calls 'fct_get_dropdown' with the dropdown
	 *                        and args
	 * @return string The dropdown
	 */
	function fct_get_dropdown( $args = '' ) {

		/** Arguments *********************************************************/

		$defaults = array (
			'post_type'          => fct_get_year_post_type(),
			'selected'           => 0,
			'sort_column'        => 'menu_order',
			'child_of'           => '0',
			'numberposts'        => -1,
			'orderby'            => 'menu_order',
			'order'              => 'ASC',
			'walker'             => '',

			// Output-related
			'select_id'          => 'fct_year_id',
			'select_name'        => false, // Custom
			'class'              => false, // Custom
			'tab'                => fct_get_tab_index(),
			'options_only'       => false,
			'show_none'          => false,
			'none_found'         => false,
			'disable_closed'     => true,
			'disabled'           => ''
		);
		$r = fct_parse_args( $args, $defaults, 'get_dropdown' );

		if ( empty( $r['walker'] ) ) {
			$r['walker']            = new Fiscaat_Walker_Dropdown();
			$r['walker']->tree_type = $r['post_type'];
		}

		// Force 0
		if ( is_numeric( $r['selected'] ) && $r['selected'] < 0 )
			$r['selected'] = 0;

		extract( $r );

		// Unset the args not needed for WP_Query to avoid any possible conflicts.
		// Note: walker and disable_categories are not unset
		unset( $r['select_id'], $r['tab'], $r['options_only'], $r['show_none'], $r['none_found'] );

		/** Post Status *******************************************************/

		// Define local variable(s)
		$post_stati = array();

		// Public
		$post_stati[] = fct_get_public_status_id();

		// Closed
		if ( ! $r['disable_closed'] )
			$post_stati[] = fct_get_closed_status_id();

		// Setup the post statuses
		$r['post_status'] = implode( ',', $post_stati );

		/** Setup variables ***************************************************/

		$name      = ! empty( $select_name ) ? esc_attr( $select_name ) : esc_attr( $select_id );
		$select_id = ! empty( $select_id   ) ? esc_attr( $select_id   ) : $name;
		$class     = ! empty( $class       ) ? ' class="'. $class .'"'  : '';
		$tab       = (int) $tab;
		$retval    = '';
		$posts     = get_posts( $r );
		// $disabled  = disabled( isset( fiscaat()->options[$disabled] ), true, false );
		$disabled  = disabled( $disabled, true, false );

		/** Drop Down *********************************************************/

		// Items found
		if ( ! empty( $posts ) ) {
			if ( empty( $options_only ) ) {
				$tab     = ! empty( $tab ) ? ' tabindex="' . $tab . '"' : '';
				$retval .= '<select name="' . $name . '" id="' . $select_id . '"' . $tab  . $class . $disabled . '>' . "\n";
			}

			$retval .= ! empty( $show_none ) ? "\t<option value=\"\" class=\"level-0\">" . $show_none . '</option>' : '';
			$retval .= walk_page_dropdown_tree( $posts, 0, $r );

			if ( empty( $options_only ) )
				$retval .= '</select>';

		// No items found - Display feedback if no custom message was passed
		} elseif ( empty( $none_found ) ) {

			// Setup empty select
			$retval = '<select name="'. $name .'" id="'. $select_id .'"><option value"">';

			// Switch the response based on post type
			switch ( $post_type ) {

				// Accounts
				case fct_get_account_post_type() :
					$retval .= __( '&mdash; No accounts &mdash;', 'fiscaat' );
					break;

				// Years
				case fct_get_year_post_type() :
					$retval .=  __('&mdash; No years &mdash;', 'fiscaat');
					break;

				// Any other
				default :
					$retval .= __( '&mdash; None &mdash;', 'fiscaat' );
					break;
			}

			$retval .= '</option></select>';
		}

		return apply_filters( 'fct_get_dropdown', $retval, $args );
	}

/**
 * Output the required hidden fields when creating/editing a year
 *
 * @since Fiscaat (r3553)
 *
 * @uses fct_is_year_edit() To check if it's the year edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses fct_year_id() To output the year id
 * @uses fct_is_single_year() To check if it's a year page
 * @uses fct_year_id() To output the year id
 */
function fct_year_form_fields() {

	if ( fct_is_year_edit() ) : ?>

		<input type="hidden" name="action"          id="fct_post_action" value="fiscaat-edit-year" />
		<input type="hidden" name="fct_year_id" id="fct_year_id"     value="<?php fct_year_id(); ?>" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'fiscaat-unfiltered-html-year_' . fct_get_year_id(), '_fct_unfiltered_html_year', false );

		?>

		<?php wp_nonce_field( 'fiscaat-edit-year_' . fct_get_year_id() );

	else :

		if ( fct_is_single_year() ) : ?>

			<input type="hidden" name="fct_year_parent_id" id="fct_year_parent_id" value="<?php fct_year_parent_id(); ?>" />

		<?php endif; ?>

			<input type="hidden" name="action" id="fct_post_action" value="fiscaat-new-year" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'fiscaat-unfiltered-html-year_new', '_fct_unfiltered_html_year', false );

		?>

		<?php wp_nonce_field( 'fiscaat-new-year' );

	endif;
}

/**
 * Output the required hidden fields when creating/editing a account
 *
 * @since Fiscaat (r2753)
 *
 * @uses fct_is_account_edit() To check if it's the account edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses fct_account_id() To output the account id
 * @uses fct_is_single_year() To check if it's a year page
 * @uses fct_year_id() To output the year id
 */
function fct_account_form_fields() {

	if ( fct_is_account_edit() ) : ?>

		<input type="hidden" name="action"       id="fct_post_action" value="fiscaat-edit-account" />
		<input type="hidden" name="fct_account_id" id="fct_account_id"    value="<?php fct_account_id(); ?>" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'fiscaat-unfiltered-html-account_' . fct_get_account_id(), '_fct_unfiltered_html_account', false );

		?>

		<?php wp_nonce_field( 'fiscaat-edit-account_' . fct_get_account_id() );

	else :

		if ( fct_is_single_year() ) : ?>

			<input type="hidden" name="fct_year_id" id="fct_year_id" value="<?php fct_year_id(); ?>" />

		<?php endif; ?>

			<input type="hidden" name="action" id="fct_post_action" value="fiscaat-new-account" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'fiscaat-unfiltered-html-account_new', '_fct_unfiltered_html_account', false ); ?>

		<?php wp_nonce_field( 'fiscaat-new-account' );

	endif;
}

/**
 * Output the required hidden fields when creating/editing a record
 *
 * @since Fiscaat (r2753)
 *
 * @uses fct_is_record_edit() To check if it's the record edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses fct_record_id() To output the record id
 * @uses fct_account_id() To output the account id
 * @uses fct_year_id() To output the year id
 */
function fct_record_form_fields() {

	if ( fct_is_record_edit() ) : ?>

		<input type="hidden" name="fct_record_title" id="fct_record_title" value="<?php printf( __( 'Record To: %s', 'fiscaat' ), fct_get_account_title() ); ?>" />
		<input type="hidden" name="fct_record_id"    id="fct_record_id"    value="<?php fct_record_id(); ?>" />
		<input type="hidden" name="action"          id="fct_post_action" value="fiscaat-edit-record" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'fiscaat-unfiltered-html-record_' . fct_get_record_id(), '_fct_unfiltered_html_record', false ); ?>

		<?php wp_nonce_field( 'fiscaat-edit-record_' . fct_get_record_id() );

	else : ?>

		<input type="hidden" name="fct_record_title" id="fct_record_title" value="<?php printf( __( 'Record To: %s', 'fiscaat' ), fct_get_account_title() ); ?>" />
		<input type="hidden" name="fct_account_id"    id="fct_account_id"    value="<?php fct_account_id(); ?>" />
		<input type="hidden" name="action"          id="fct_post_action" value="fiscaat-new-record" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'fiscaat-unfiltered-html-record_' . fct_get_account_id(), '_fct_unfiltered_html_record', false ); ?>

		<?php wp_nonce_field( 'fiscaat-new-record' );

		// Show redirect field if not viewing a specific account
		if ( fct_is_query_name( 'fct_single_account' ) ) :
			fct_redirect_to_field( get_permalink() );

		endif;
	endif;
}

/**
 * Output a textarea or TinyMCE if enabled
 *
 * @param array $args
 * @uses fct_get_the_content() To return the content to output
 */
function fct_the_content( $args = array() ) {
	echo fct_get_the_content( $args );
}
	/**
	 * Return a textarea or TinyMCE if enabled
	 *
	 * @param array $args
	 *
	 * @uses apply_filter() To filter args and output
	 * @uses wp_parse_pargs() To compare args
	 * @uses fct_use_wp_editor() To see if WP editor is in use
	 * @uses fct_is_edit() To see if we are editing something
	 * @uses wp_editor() To output the WordPress editor
	 *
	 * @return string HTML from output buffer 
	 */
	function fct_get_the_content( $args = array() ) {

		// Default arguments
		$defaults = array(
			'context'       => 'account',
			'before'        => '<div class="fiscaat-the-content-wrapper">',
			'after'         => '</div>',
			'wpautop'       => true,
			'media_buttons' => false,
			'textarea_rows' => '12',
			'tabindex'      => fct_get_tab_index(),
			'editor_class'  => 'fiscaat-the-content',
			'tinymce'       => true,
			'teeny'         => true,
			'quicktags'     => true
		);
		$r = fct_parse_args( $args, $defaults, 'get_the_content' );
		extract( $r );

		// Assume we are not editing
		$post_content = '';

		// Start an output buffor
		ob_start();

		// Output something before the editor
		if ( ! empty( $before ) )
			echo $before;

		// Get sanitized content
		if ( fct_is_edit() )
			$post_content = call_user_func( 'fct_get_form_' . $context . '_content' );

		// Use TinyMCE if available
		if ( fct_use_wp_editor() ) :
			wp_editor( htmlspecialchars_decode( $post_content, ENT_QUOTES ), 'fct_' . $context . '_content', array(
				'wpautop'       => $wpautop,
				'media_buttons' => $media_buttons,
				'textarea_rows' => $textarea_rows,
				'tabindex'      => $tabindex,
				'editor_class'  => $editor_class,
				'tinymce'       => $tinymce,
				'teeny'         => $teeny,
				'quicktags'     => $quicktags
			) );

		/**
		 * Fallback to normal textarea.
		 *
		 * Note that we do not use esc_textarea() here to prevent double
		 * escaping the editable output, mucking up existing content.
		 */
		else : ?>

			<textarea id="fct_<?php echo esc_attr( $context ); ?>_content" class="<?php echo esc_attr( $editor_class ); ?>" name="fct_<?php echo esc_attr( $context ); ?>_content" cols="60" rows="<?php echo esc_attr( $textarea_rows ); ?>" tabindex="<?php echo esc_attr( $tabindex ); ?>"><?php echo $post_content; ?></textarea>

		<?php endif;

		// Output something after the editor
		if ( ! empty( $after ) )
			echo $after;

		// Put the output into a usable variable
		$output = ob_get_contents();

		// Flush the output buffer
		ob_end_clean();

		return apply_filters( 'fct_get_the_content', $output, $args, $post_content );
	}

/** Query *********************************************************************/

/**
 * Check the passed parameter against the current _fct_query_name
 *
 * @uses fct_get_query_name() Get the query var '_fct_query_name'
 * @return bool True if match, false if not
 */
function fct_is_query_name( $name = '' )  {
	return (bool) ( fct_get_query_name() == $name );
}

/**
 * Get the '_fct_query_name' setting
 *
 * @uses get_query_var() To get the query var '_fct_query_name'
 * @return string To return the query var value
 */
function fct_get_query_name()  {
	return get_query_var( '_fct_query_name' );
}

/**
 * Set the '_fct_query_name' setting to $name
 *
 * @param string $name What to set the query var to
 * @uses set_query_var() To set the query var '_fct_query_name'
 */
function fct_set_query_name( $name = '' )  {
	set_query_var( '_fct_query_name', $name );
}

/**
 * Used to clear the '_fct_query_name' setting
 *
 * @uses fct_set_query_name() To set the query var '_fct_query_name' value to ''
 */
function fct_reset_query_name() {
	fct_set_query_name();
}

/** Breadcrumbs ***************************************************************/

/**
 * Output the page title as a breadcrumb
 *
 * @param string $sep Separator. Defaults to '&larr;'
 * @param bool $current_page Include the current item
 * @param bool $root Include the root page if one exists
 * @uses fct_get_breadcrumb() To get the breadcrumb
 */
function fct_title_breadcrumb( $args = array() ) {
	echo fct_get_breadcrumb( $args );
}

/**
 * Output a breadcrumb
 *
 * @param string $sep Separator. Defaults to '&larr;'
 * @param bool $current_page Include the current item
 * @param bool $root Include the root page if one exists
 * @uses fct_get_breadcrumb() To get the breadcrumb
 */
function fct_breadcrumb( $args = array() ) {
	echo fct_get_breadcrumb( $args );
}
	/**
	 * Return a breadcrumb ( year -> account -> record )
	 *
	 * @since Fiscaat (r2589)
	 *
	 * @param string $sep Separator. Defaults to '&larr;'
	 * @param bool $current_page Include the current item
	 * @param bool $root Include the root page if one exists
	 *
	 * @uses get_post() To get the post
	 * @uses fct_get_year_permalink() To get the year link
	 * @uses fct_get_account_permalink() To get the account link
	 * @uses fct_get_record_permalink() To get the record link
	 * @uses get_permalink() To get the permalink
	 * @uses fct_get_year_post_type() To get the year post type
	 * @uses fct_get_account_post_type() To get the account post type
	 * @uses fct_get_record_post_type() To get the record post type
	 * @uses fct_get_year_title() To get the year title
	 * @uses fct_get_account_title() To get the account title
	 * @uses fct_get_record_title() To get the record title
	 * @uses get_the_title() To get the title
	 * @uses apply_filters() Calls 'fct_get_breadcrumb' with the crumbs
	 * @return string Breadcrumbs
	 */
	function fct_get_breadcrumb( $args = array() ) {

		// Turn off breadcrumbs
		if ( apply_filters( 'fct_no_breadcrumb', is_front_page() ) )
			return;

		// Define variables
		$front_id         = $root_id                                 = 0;
		$ancestors        = $crumbs           = $tag_data            = array();
		$pre_root_text    = $pre_front_text   = $pre_current_text    = '';
		$pre_include_root = $pre_include_home = $pre_include_current = true;

		/** Home Text *********************************************************/

		// No custom home text
		if ( empty( $args['home_text'] ) ) {

			// Set home text to page title
			$front_id = get_option( 'page_on_front' );
			if ( ! empty( $front_id ) ) {
				$pre_front_text = get_the_title( $front_id );

			// Default to 'Home'
			} else {
				$pre_front_text = __( 'Home', 'fiscaat' );
			}
		}

		/** Root Text *********************************************************/

		// No custom root text
		if ( empty( $args['root_text'] ) ) {
			$page = fct_get_page_by_path( fct_get_root_slug() );
			if ( ! empty( $page ) ) {
				$root_id = $page->ID;
			}
			$pre_root_text = fct_get_year_archive_title();
		}

		/** Includes **********************************************************/

		// Root slug is also the front page
		if ( ! empty( $front_id ) && ( $front_id == $root_id ) )
			$pre_include_root = false;

		// Don't show root if viewing year archive
		if ( fct_is_year_archive() )
			$pre_include_root = false;

		// Don't show root if viewing page in place of year archive
		if ( ! empty( $root_id ) && ( ( is_single() || is_page() ) && ( $root_id == get_the_ID() ) ) )
			$pre_include_root = false;

		/** Current Text ******************************************************/

		// Year archive
		if ( fct_is_year_archive() ) {
			$pre_current_text = fct_get_year_archive_title();

		// Account archive
		} elseif ( fct_is_account_archive() ) {
			$pre_current_text = fct_get_account_archive_title();

		// Single Year
		} elseif ( fct_is_single_year() ) {
			$pre_current_text = fct_get_year_title();

		// Single Account
		} elseif ( fct_is_single_account() ) {
			$pre_current_text = fct_get_account_title();

		// Single Account
		} elseif ( fct_is_single_record() ) {
			$pre_current_text = fct_get_record_title();

		// Single
		} else {
			$pre_current_text = get_the_title();
		}

		/** Parse Args ********************************************************/

		// Parse args
		$defaults = array(

			// HTML
			'before'          => '<div class="fiscaat-breadcrumb"><p>',
			'after'           => '</p></div>',
			
			// Separator
			'sep'             => __( '&rsaquo;', 'fiscaat' ),
			'pad_sep'         => 1,
			'sep_before'      => '<span class="fiscaat-breadcrumb-sep">',
			'sep_after'       => '</span>',
			
			// Crumbs
			'crumb_before'    => '',
			'crumb_after'     => '',

			// Home
			'include_home'    => $pre_include_home,
			'home_text'       => $pre_front_text,

			// Year root
			'include_root'    => $pre_include_root,
			'root_text'       => $pre_root_text,

			// Current
			'include_current' => $pre_include_current,
			'current_text'    => $pre_current_text,
			'current_before'  => '<span class="fiscaat-breadcrumb-current">',
			'current_after'   => '</span>',
		);
		$r = fct_parse_args( $args, $defaults, 'get_breadcrumb' );
		extract( $r );

		/** Ancestors *********************************************************/

		// Get post ancestors
		if ( is_page() || is_single() || fct_is_year_edit() || fct_is_account_edit() || fct_is_record_edit() )
			$ancestors = array_reverse( (array) get_post_ancestors( get_the_ID() ) );

		// Do we want to include a link to home?
		if ( ! empty( $include_home ) || empty( $home_text ) )
			$crumbs[] = '<a href="' . trailingslashit( home_url() ) . '" class="fiscaat-breadcrumb-home">' . $home_text . '</a>';

		// Do we want to include a link to the year root?
		if ( ! empty( $include_root ) || empty( $root_text ) ) {

			// Page exists at root slug path, so use its permalink
			$page = fct_get_page_by_path( fct_get_root_slug() );
			if ( ! empty( $page ) ) {
				$root_url = get_permalink( $page->ID );

			// Use the root slug
			} else {
				$root_url = get_post_type_archive_link( fct_get_year_post_type() );
			}

			// Add the breadcrumb
			$crumbs[] = '<a href="' . $root_url . '" class="fiscaat-breadcrumb-root">' . $root_text . '</a>';
		}

		// Ancestors exist
		if ( ! empty( $ancestors ) ) {

			// Loop through parents
			foreach( (array) $ancestors as $parent_id ) {

				// Parents
				$parent = get_post( $parent_id );

				// Switch through post_type to ensure correct filters are applied
				switch ( $parent->post_type ) {

					// Year
					case fct_get_year_post_type() :
						$crumbs[] = '<a href="' . fct_get_year_permalink( $parent->ID ) . '" class="fiscaat-breadcrumb-year">' . fct_get_year_title( $parent->ID ) . '</a>';
						break;

					// Account
					case fct_get_account_post_type() :
						$crumbs[] = '<a href="' . fct_get_account_permalink( $parent->ID ) . '" class="fiscaat-breadcrumb-account">' . fct_get_account_title( $parent->ID ) . '</a>';
						break;

					// Record (Note: not in most themes)
					case fct_get_record_post_type() :
						$crumbs[] = '<a href="' . fct_get_record_permalink( $parent->ID ) . '" class="fiscaat-breadcrumb-record">' . fct_get_record_title( $parent->ID ) . '</a>';
						break;

					// WordPress Post/Page/Other
					default :
						$crumbs[] = '<a href="' . get_permalink( $parent->ID ) . '" class="fiscaat-breadcrumb-item">' . get_the_title( $parent->ID ) . '</a>';
						break;
				}
			}
		}

		/** Current ***********************************************************/

		// Add current page to breadcrumb
		if ( ! empty( $include_current ) || empty( $pre_current_text ) )
			$crumbs[] = $current_before . $current_text . $current_after;

		/** Separator *********************************************************/

		// Wrap the separator in before/after before padding and filter
		if ( ! empty( $sep ) )
			$sep = $sep_before . $sep . $sep_after;

		// Pad the separator
		if ( ! empty( $pad_sep ) )
			$sep = str_pad( $sep, strlen( $sep ) + ( (int) $pad_sep * 2 ), ' ', STR_PAD_BOTH );

		/** Finish Up *********************************************************/

		// Filter the separator and breadcrumb
		$sep    = apply_filters( 'fct_breadcrumb_separator', $sep    );
		$crumbs = apply_filters( 'fct_breadcrumbs',          $crumbs );

		// Build the trail
		$trail = ! empty( $crumbs ) ? ( $before . $crumb_before . implode( $sep . $crumb_after . $crumb_before , $crumbs ) . $crumb_after . $after ) : '';

		return apply_filters( 'fct_get_breadcrumb', $trail, $crumbs, $r );
	}

/** Errors & Messages *********************************************************/

/**
 * Display possible errors & messages inside a template file
 *
 * @uses WP_Error Fiscaat::errors::get_error_codes() To get the error codes
 * @uses WP_Error Fiscaat::errors::get_error_data() To get the error data
 * @uses WP_Error Fiscaat::errors::get_error_messages() To get the error
 *                                                       messages
 * @uses is_wp_error() To check if it's a {@link WP_Error}
 */
function fct_template_notices() {

	// Bail if no notices or errors
	if ( !fct_has_errors() )
		return;

	// Define local variable(s)
	$errors = $messages = array();

	// Get Fiscaat
	$fiscaat = fiscaat();

	// Loop through notices
	foreach ( $fiscaat->errors->get_error_codes() as $code ) {

		// Get notice severity
		$severity = $fiscaat->errors->get_error_data( $code );

		// Loop through notices and separate errors from messages
		foreach ( $fiscaat->errors->get_error_messages( $code ) as $error ) {
			if ( 'message' == $severity ) {
				$messages[] = $error;
			} else {
				$errors[]   = $error;
			}
		}
	}

	// Display errors first...
	if ( ! empty( $errors ) ) : ?>

		<div class="fiscaat-template-notice error">
			<p>
				<?php echo implode( "</p>\n<p>", $errors ); ?>
			</p>
		</div>

	<?php endif;

	// ...and messages last
	if ( ! empty( $messages ) ) : ?>

		<div class="fiscaat-template-notice">
			<p>
				<?php echo implode( "</p>\n<p>", $messages ); ?>
			</p>
		</div>

	<?php endif;
}

/** Title *********************************************************************/

/**
 * Custom page title for Fiscaat pages
 *
 * @since Fiscaat (r2788)
 *
 * @param string $title Optional. The title (not used).
 * @param string $sep Optional, default is '&raquo;'. How to separate the
 *                     various items within the page title.
 * @param string $seplocation Optional. Direction to display title, 'right'.
 * @uses fct_is_single_user() To check if it's a user profile page
 * @uses fct_is_single_user_edit() To check if it's a user profile edit page
 * @uses fct_is_user_home() To check if the profile page is of the current user
 * @uses get_query_var() To get the user id
 * @uses get_userdata() To get the user data
 * @uses fct_is_single_year() To check if it's a year
 * @uses fct_get_year_title() To get the year title
 * @uses fct_is_single_account() To check if it's a account
 * @uses fct_get_account_title() To get the account title
 * @uses fct_is_single_record() To check if it's a record
 * @uses fct_get_record_title() To get the record title
 * @uses is_tax() To check if it's the tag page
 * @uses get_queried_object() To get the queried object
 * @uses fct_is_single_view() To check if it's a view
 * @uses fct_get_view_title() To get the view title
 * @uses apply_filters() Calls 'fct_raw_title' with the title
 * @uses apply_filters() Calls 'fct_profile_page_wp_title' with the title,
 *                        separator and separator location
 * @return string The tite
 */
function fct_title( $title = '', $sep = '&raquo;', $seplocation = '' ) {

	// Store original title to compare
	$_title = $title;

	/** Archives **************************************************************/

	// Year Archive
	if ( fct_is_year_archive() ) {
		$title = fct_get_year_archive_title();

	// Account Archive
	} elseif ( fct_is_account_archive() ) {
		$title = fct_get_account_archive_title();

	/** Singles ***************************************************************/

	// Year page
	} elseif ( fct_is_single_year() ) {
		$title = sprintf( __( 'Year: %s', 'fiscaat' ), fct_get_year_title() );

	// Account page
	} elseif ( fct_is_single_account() ) {
		$title = sprintf( __( 'Account: %s', 'fiscaat' ), fct_get_account_title() );

	// Records
	} elseif ( fct_is_single_record() ) {
		$title = fct_get_record_title();
	}

	// Filter the raw title
	$title = apply_filters( 'fct_raw_title', $title, $sep, $seplocation );

	// Compare new title with original title
	if ( $title == $_title )
		return $title;

	// Temporary separator, for accurate flipping, if necessary
	$t_sep  = '%WP_TITILE_SEP%';
	$prefix = '';

	if ( ! empty( $title ) )
		$prefix = " $sep ";

	// sep on right, so reverse the order
	if ( 'right' == $seplocation ) {
		$title_array = array_reverse( explode( $t_sep, $title ) );
		$title       = implode( " $sep ", $title_array ) . $prefix;

	// sep on left, do not reverse
	} else {
		$title_array = explode( $t_sep, $title );
		$title       = $prefix . implode( " $sep ", $title_array );
	}

	// Filter and return
	return apply_filters( 'fct_title', $title, $sep, $seplocation );
}

/** Currency ******************************************************************/

/**
 * Output the stored currency attribute
 * 
 * @param string $arg Optional. Currency attribute
 * @uses fct_get_currency() To get the currency
 */
function fct_currency( $arg = '' ){
	echo fct_get_currency( $arg );
}
	/**
	 * Return the stored currency (attribute)
	 * 
	 * @param string $arg Optional. Currency attribute
	 * @uses fct_get_currencies() To get the currencies list
	 * @return string The currency
	 */
	function fct_get_currency( $attr = '' ){
		$fiscaat = fiscaat();

		// Load once. Default to 'USD'
		if ( empty( $fiscaat->currency ) ) {
			$c = get_option( '_fct_currency', false );
			$fiscaat->currency = ! empty( $c ) ? $c : 'USD';
		}

		// Attribute requested
		if ( ! empty( $attr ) ) {
			$currencies = fct_get_currencies();

			if ( isset( $currencies[$fiscaat->currency] ) && isset( $currencies[$fiscaat->currency][$attr] ) )
				$currency = $currencies[$fiscaat->currency][$attr];			
			else
				$currency = $fiscaat->currency;

		// Default to currency iso code
		} else {
			$currency = $fiscaat->currency;
		}

		return apply_filters( 'fct_get_currency', $currency, $fiscaat->currency, $attr );
	}

/**
 * Output a select box allowing to pick a currency.
 *
 * @param mixed $args See {@link fct_get_dropdown()} for arguments
 */

function fct_currency_dropdown( $args = '' ) {
	echo fct_get_currency_dropdown( $args );
}
	/**
	 * Return a select box allowing to pick a currency.
	 * 
	 * @param mixed $args See {@link fct_get_dropdown()} for arguments
	 * @return string The dropdown
	 */
	function fct_get_currency_dropdown( $args = '' ) {

		/** Arguments *********************************************************/

		$defaults = array(
			'selected'           => 0,

			// Output-related
			'select_id'          => '_fct_currency',
			'tab'                => fct_get_tab_index(),
			'options_only'       => false,
			'none_found'         => false,
			'disabled'           => ''
		);

		$r = fct_parse_args( $args, $defaults, 'get_currency_dropdown' );
		extract( $r );

		/** Setup variables ***************************************************/

		$name      = esc_attr( $select_id );
		$select_id = $name;
		$tab       = (int) $tab;
		$retval    = '';
		$items     = fct_get_currencies();
		$disabled  = disabled( isset( fiscaat()->options[$disabled] ), true, false );

		/** Drop Down *********************************************************/

		// Items found
		if ( ! empty( $items ) ) {
			if ( empty( $options_only ) ) {
				$tab     = ! empty( $tab ) ? ' tabindex="' . $tab . '"' : '';
				$retval .= '<select name="' . $name . '" id="' . $select_id . '"' . $tab  . $disabled . '>' . "\n";
			}

			foreach ( $items as $iso => $att )
				$retval .= "\t<option value=\"$iso\" class=\"level-0\"". selected( $selected, $iso, false ) . ">" . $att['desc'] ."</option>\n";

			if ( empty( $options_only ) )
				$retval .= '</select>';

		// No items found - Display feedback if no custom message was passed
		} elseif ( empty( $none_found ) ) {
			$retval = __( 'No currencies available', 'fiscaat' );
		}

		return apply_filters( 'fct_get_currency_dropdown', $retval, $args );
	}