<?php

/**
 * Fiscaat Control Template Tags
 *
 * @package Fiscaat
 * @subpackage TemplateTags
 *
 * @todo fct_get_record_admin_links()
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Years *********************************************************************/

/**
 * Output total declined record count of a year 
 *
 * @param int $year_id Optional. Account id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses fct_get_year_record_count_declined() To get the year declined record count
 */
function fct_year_record_count_declined( $year_id = 0, $integer = false ) {
	echo fct_get_year_record_count_declined( $year_id, $integer );
}
	/**
	 * Return total declined record count of a year 
	 *
	 * @param int $year_id Optional. Account id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses fct_get_year_id() To get the year id
	 * @uses fct_get_year_meta() To get the declined record count
	 * @uses apply_filters() Calls 'fct_get_year_record_count_declined' with
	 *                        the declined record count and year id
	 * @return int Account declined record count
	 */
	function fct_get_year_record_count_declined( $year_id = 0, $integer = false ) {
		$year_id = fct_get_year_id( $year_id );
		$records = (int) fct_get_year_meta( $year_id, 'record_count_declined' );
		$filter  = ( true === $integer ) ? 'fct_get_year_record_count_declined_int' : 'fct_get_year_record_count_declined';

		return apply_filters( $filter, $records, $year_id );
	}

/**
 * Output total unapproved record count of a year 
 *
 * @param int $year_id Optional. Account id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses fct_get_year_record_count_unapproved() To get the year unapproved record count
 */
function fct_year_record_count_unapproved( $year_id = 0, $integer = false ) {
	echo fct_get_year_record_count_unapproved( $year_id, $integer );
}
	/**
	 * Return total unapproved record count of a year 
	 *
	 * @param int $year_id Optional. Account id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses fct_get_year_id() To get the year id
	 * @uses fct_get_year_meta() To get the unapproved record count
	 * @uses apply_filters() Calls 'fct_get_year_record_count_unapproved' with
	 *                        the unapproved record count and year id
	 * @return int Account unapproved record count
	 */
	function fct_get_year_record_count_unapproved( $year_id = 0, $integer = false ) {
		$year_id = fct_get_year_id( $year_id );
		$records = (int) fct_get_year_meta( $year_id, 'record_count_unapproved' );
		$filter  = ( true === $integer ) ? 'fct_get_year_record_count_unapproved_int' : 'fct_get_year_record_count_unapproved';

		return apply_filters( $filter, $records, $year_id );
	}

/** Accounts ******************************************************************/

/**
 * Output total declined record count of an account 
 *
 * @param int $account_id Optional. Account id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses fct_get_account_record_count_declined() To get the account declined record count
 */
function fct_account_record_count_declined( $account_id = 0, $integer = false ) {
	echo fct_get_account_record_count_declined( $account_id, $integer );
}
	/**
	 * Return total declined record count of an account 
	 *
	 * @param int $account_id Optional. Account id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses fct_get_account_id() To get the account id
	 * @uses fct_get_account_meta() To get the declined record count
	 * @uses apply_filters() Calls 'fct_get_account_record_count_declined' with
	 *                        the declined record count and account id
	 * @return int Account declined record count
	 */
	function fct_get_account_record_count_declined( $account_id = 0, $integer = false ) {
		$account_id = fct_get_account_id( $account_id );
		$records    = (int) fct_get_account_meta( $account_id, 'record_count_declined' );
		$filter     = ( true === $integer ) ? 'fct_get_account_record_count_declined_int' : 'fct_get_account_record_count_declined';

		return apply_filters( $filter, $records, $account_id );
	}

/**
 * Output total unapproved record count of an account 
 *
 * @param int $account_id Optional. Account id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses fct_get_account_record_count_unapproved() To get the account unapproved record count
 */
function fct_account_record_count_unapproved( $account_id = 0, $integer = false ) {
	echo fct_get_account_record_count_unapproved( $account_id, $integer );
}
	/**
	 * Return total unapproved record count of an account 
	 *
	 * @param int $account_id Optional. Account id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses fct_get_account_id() To get the account id
	 * @uses fct_get_account_meta() To get the unapproved record count
	 * @uses apply_filters() Calls 'fct_get_account_record_count_unapproved' with
	 *                        the unapproved record count and account id
	 * @return int Account unapproved record count
	 */
	function fct_get_account_record_count_unapproved( $account_id = 0, $integer = false ) {
		$account_id = fct_get_account_id( $account_id );
		$records    = (int) fct_get_account_meta( $account_id, 'record_count_unapproved' );
		$filter     = ( true === $integer ) ? 'fct_get_account_record_count_unapproved_int' : 'fct_get_account_record_count_unapproved';

		return apply_filters( $filter, $records, $account_id );
	}

/** Records *******************************************************************/

/**
 * Is the record declined?
 *
 * @param int $record_id Optional. Record id
 * @uses fct_get_record_id() To get the record id
 * @uses fct_get_record_status() To get the record status
 * @return bool True if declined, false if not.
 */
function fct_is_record_declined( $record_id = 0 ) {
	$record_id     = fct_get_record_id( $record_id );
	$record_status = fct_get_record_status( $record_id ) == fct_get_declined_status_id();
	
	return (bool) apply_filters( 'fct_is_record_declined', (bool) $record_status, $record_id );
}

/**
 * Is the record approved?
 *
 * @param int $record_id Optional. Account id
 * @uses fct_get_record_id() To get the record id
 * @uses fct_get_record_status() To get the record status
 * @return bool True if approved, false if not.
 */
function fct_is_record_approved( $record_id = 0 ) {
	$record_id     = fct_get_record_id( $record_id );
	$record_status = fct_get_record_status( $record_id ) == fct_get_approved_status_id();
	
	return (bool) apply_filters( 'fct_is_record_approved', (bool) $record_status, $record_id );
}

/**
 * Output the approve link of the record
 *
 * @param mixed $args See {@link fct_get_record_approve_link()}
 * @uses fct_get_record_approve_link() To get the record approve link
 */
function fct_record_approve_link( $args = '' ) {
	echo fct_get_record_approve_link( $args );
}

	/**
	 * Return the approve link of the record
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Record id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - approve_text: Approve text
	 * @uses fct_get_record_id() To get the record id
	 * @uses fct_get_record() To get the record
	 * @uses current_user_can() To check if the current user can edit the
	 *                           record
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses esc_url() To escape the url
	 * @uses fct_get_record_edit_url() To get the record edit url
	 * @uses apply_filters() Calls 'fct_get_record_approve_link' with the record
	 *                        approve link and args
	 * @return string Record approve link
	 */
	function fct_get_record_approve_link( $args = '' ) {
		$defaults = array (
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'approve_text' => __( 'Approve', 'fiscaat' )
		);
		$r = fct_parse_args( $args, $defaults, 'get_record_approve_link' );
		extract( $r );

		$record = fct_get_record( fct_get_record_id( (int) $id ) );

		if ( empty( $record ) || ! current_user_can( 'control', $record->ID ) )
			return;

		$uri      = add_query_arg( array( 'action' => 'fct_toggle_record_approval', 'record_id' => $record->ID ) );
		$uri      = esc_url( wp_nonce_url( $uri, 'approval-record_' . $record->ID ) );
		$retval   = $link_before . '<a href="' . $uri . '">' . $approve_text . '</a>' . $link_after;

		return apply_filters( 'fct_get_record_approve_link', $retval, $args );
	}

/**
 * Output the decline link of the record
 *
 * @uses fct_get_record_decline_link() To get the record decline link
 * @param mixed $args See {@link fct_get_record_decline_link()}
 */
function fct_record_decline_link( $args = '' ) {
	echo fct_get_record_decline_link( $args );
}

	/**
	 * Return the decline link of the record
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Record id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - decline_text: Suspense text
	 * @uses fct_get_record_id() To get the record id
	 * @uses fct_get_record() To get the record
	 * @uses current_user_can() To check if the current user can edit the
	 *                           record
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses esc_url() To escape the url
	 * @uses fct_get_record_edit_url() To get the record edit url
	 * @uses apply_filters() Calls 'fct_get_record_decline_link' with the record
	 *                        decline link and args
	 * @return string Record decline link
	 */
	function fct_get_record_decline_link( $args = '' ) {
		$defaults = array (
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'decline_text' => __( 'Decline', 'fiscaat' )
		);
		$r = fct_parse_args( $args, $defaults, 'get_record_decline_link' );
		extract( $r );

		$record = fct_get_record( fct_get_record_id( (int) $id ) );

		if ( empty( $record ) || ! current_user_can( 'control', $record->ID ) )
			return;

		$uri      = add_query_arg( array( 'action' => 'fct_set_record_decline', 'record_id' => $record->ID ) );
		$uri      = esc_url( wp_nonce_url( $uri, 'decline-record_' . $record->ID ) );
		$retval   = $link_before . '<a href="' . $uri . '">' . $decline_text . '</a>' . $link_after;

		return apply_filters( 'fct_get_record_decline_link', $retval, $args );
	}

