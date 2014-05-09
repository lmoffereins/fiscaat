<?php

/**
 * Fiscaat Accounts List Table class
 *
 * @package Fiscaat
 * @subpackage List_Table
 * @since 0.0.7
 * @access private
 */

class FCT_Accounts_List_Table extends FCT_Posts_List_Table {

	/**
	 * Constructs the posts list table
	 * 
	 * @param array $args
	 */
	function __construct( $args = array() ) {
		parent::__construct( array(
			'plural'   => 'accounts',
			'singular' => 'account',
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	/**
	 * Return dedicated bulk actions
	 *
	 * @since 0.0.8
	 * 
	 * @return array Bulk actions
	 */
	function _get_bulk_actions() {
		$actions = array();

		if ( current_user_can( 'edit_accounts' ) ) {
			$actions['close'] = __( 'Close', 'fiscaat' );
		}

		// Accounts are not trashed, only deleted
		if ( current_user_can( 'delete_accounts' ) ) {
			$actions['delete'] = __( 'Delete', 'fiscaat' );
		}

		return $actions;
	}

	/**
	 * Return dedicated account columns
	 *
	 * @since 0.0.8
	 * 
	 * @return array Columns
	 */
	function _get_columns() {
		$columns = array(
			'cb'                       => '<input type="checkbox" />',
			'fct_account_period'       => __( 'Period',             'fiscaat' ),
			'fct_account_ledger_id'    => _x( 'No.', 'column name', 'fiscaat' ),
			'title'                    => __( 'Account',            'fiscaat' ),
			'fct_account_type'         => __( 'Type',               'fiscaat' ),
			'fct_account_record_count' => __( 'Records',            'fiscaat' ),
			'fct_account_end_value'    => __( 'Value',              'fiscaat' ),
			'author'                   => __( 'Author' ),
		);

		if ( ! current_user_can( 'edit_accounts' ) ) {
			unset( $columns['author'] );
		}

		return $columns;
	}

	/**
	 * Return which columns are sortable
	 * 
	 * @since 0.0.8
	 *
	 * @return array Sortable columns as array( column => sort key )
	 */
	function _get_sortable_columns() {
		return array(
			'fct_account_period'       => 'parent',
			'fct_account_ledger_id'    => 'account_ledger_id',
			'title'                    => 'title',
			'fct_account_type'         => 'account_type',
			'fct_account_record_count' => array( 'account_record_count', true ),
			'fct_account_end_value'    => array( 'account_end_value',    true ),
		);
	}

	/**
	 * Return columns that are hidden by default
	 *
	 * @since 0.0.8
	 * 
	 * @return array Hidden columns
	 */
	function _get_hidden_columns( $columns ) {
		$columns[] = 'author';

		return $columns;
	}

	/**
	 * Display dedicated column content
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_get_account_period_id()
	 * @uses fct_get_period_title()
	 * @uses fct_account_ledger_id()
	 * @uses fct_get_account_type()
	 * @uses fct_get_revenue_account_type_id()
	 * @uses fct_get_capital_account_type_id()
	 * @uses fct_account_record_count()
	 * @uses fct_currency_format()
	 * @uses fct_get_account_end_value()
	 * @param string $column_name Column name
	 * @param int $account_id Account ID
	 */
	function _column_content( $column_name, $account_id ) {

		// Check column name
		switch ( $column_name ) {

			// Account period
			case 'fct_account_period' :
				$period_id = fct_get_account_period_id( $account_id );
				
				if ( ! empty( $period_id ) ) {
					$period_title = fct_get_period_title( $period_id );
					if ( empty( $period_title ) ) {
						$period_title = __( 'No Period', 'fiscaat' );
					}
					echo $period_title;

				} else {
					_e( '(No Period)', 'fiscaat' );
				}
				break;

			// Account ledger id
			case 'fct_account_ledger_id' :
				$ledger_id = fct_get_account_ledger_id( $account_id );

				if ( ! empty( $ledger_id ) ) {
					echo $ledger_id;
				} else {
					echo '&mdash;';
				}
				break;

			// Account type
			case 'fct_account_type' :
				$account_type = fct_get_account_type( $account_id );

				// Capital
				if ( fct_get_capital_account_type_id() == $account_type ) {
					_ex( 'C', 'Capital account type', 'fiscaat' );

				// Revenue
				} elseif ( fct_get_revenue_account_type_id() == $account_type ) {
					_ex( 'R', 'Revenue account type', 'fiscaat' );
				}
				break;

			// Account record count
			case 'fct_account_record_count' :
				fct_account_record_count( $account_id );
				break;

			// Account end value
			case 'fct_account_end_value' :
				fct_currency_format( fct_get_account_end_value( $account_id ), true );
				break;
		}
	}
}
