<?php

/**
 * Fiscaat Records List Table class
 *
 * @package Fiscaat
 * @subpackage List_Table
 * @since 0.0.7
 * @access private
 */

class FCT_Records_List_Table extends FCT_Posts_List_Table {

	/**
	 * Holds the period ID of the queried records
	 *
	 * @since 0.0.9
	 * @var int|bool
	 * @access private
	 */
	private $period_id = false;

	/**
	 * Holds the account ID when querying the account's records
	 *
	 * @since 0.0.8
	 * @var int|bool
	 * @access private
	 */
	private $account_id = false;

	/**
	 * Holds the displayed debit and credit record amounts
	 *
	 * @since 0.0.8
	 * @var array
	 * @access private
	 */
	private $amounts = array();

	/**
	 * Constructs the posts list table
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array(
			'plural'   => 'records',
			'singular' => 'record',
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );

		// Set the period id
		if ( ! empty( $_REQUEST['period_id'] ) ) {
			$this->period_id = (int) $_REQUEST['period_id'];
		// Default to the current period
		} else {
			$this->period_id = fct_get_current_period_id();
		}

		// Set the account id when querying an account's records
		if ( ! empty( $_REQUEST['account_id'] ) ) {
			$this->account_id = (int) $_REQUEST['account_id'];
		}

		// Setup amounts counter
		$this->amounts = array(
			fct_get_debit_record_type_id()  => array(),
			fct_get_credit_record_type_id() => array()
		);

		// Single row data
		add_action( 'fct_admin_records_start_row', array( $this, '_start_or_end_row' ) );
		add_action( 'fct_admin_records_end_row',   array( $this, '_start_or_end_row' ) );
		add_action( 'fct_admin_records_total_row', array( $this, '_total_row'        ) );

		// Single post-new row data
		add_action( 'fct_admin_new_records_row',   array( $this, '_new_row'          ) );
	}

	/**
	 * Setup posts query and query vars
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_admin_is_new_records()
	 * @uses get_available_post_statuses()
	 */
	public function prepare_items() {

		// New records mode
		if ( fct_admin_is_new_records() ) {
			
			// Set list table globals
			global $avail_post_stati, $per_page;
			$avail_post_stati = get_available_post_statuses( $this->screen->post_type );
			$per_page = 0; // The amount of empty record rows
			$this->is_trash = false;
			$this->_pagination_args = array(); // No pagination

		// Default to parent behavior
		} else {
			parent::prepare_items();
		}
	}

	/**
	 * Return whether the current table has records
	 *
	 * @since 0.0.9
	 * 
	 * @uses fct_admin_is_new_records()
	 * @uses fct_period_has_records()
	 * @return boolean Table has records
	 */
	public function has_items() {

		// New records mode
		if ( fct_admin_is_new_records() ) {

			// Whether there are queryable records
			return fct_period_has_records();

		// Default to parent behavior
		} else {
			return parent::has_items();
		}
	}

	/**
	 * Display the search box.
	 *
	 * @since 0.0.9
	 * @access public
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />'; ?>

		<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
		<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php echo $text; ?>" />

		<?php
	}

	/**
	 * Return post status views
	 *
	 * Use {@link fct_count_posts()} when displaying account's records
	 * for it enables counting posts by parent. Additionally append the
	 * account id query arg to the views's urls.
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_count_posts()
	 * @return array Views
	 */
	public function get_views() {
		global $locked_post_status, $avail_post_stati;

		$post_type = $this->screen->post_type;

		if ( ! empty( $locked_post_status ) )
			return array();

		// Account's record count
		if ( $this->account_id ) {
			$num_posts = fct_count_posts( array(
				'type'        => $post_type,
				'perm'        => 'readable',
				'post_parent' => $this->account_id,
			) );
			$parent = '&account_id=' . $this->account_id;

		// Period's record count. Not querying all records
		} elseif ( $this->period_id ) {
			$num_posts = fct_count_posts( array(
				'type'      => $post_type,
				'perm'      => 'readable',
				'period_id' => $this->period_id,
			) );

		// Total records count. Never getting here since period is always set
		} else {
			$num_posts = wp_count_posts( $post_type, 'readable' );
		}

		$status_links  = array();
		$class         = '';
		$parent        = isset( $parent ) ? $parent : '';
		$total_posts   = array_sum( (array) $num_posts );

		// Prepend a link for the period's records when viewing a single account
		if ( $this->account_id ) {
			$status_links['period'] = "<a href=\"admin.php?page=fct-records&amp;period_id={$this->period_id}\"$class>" . sprintf( _x( 'Period <span class="count">(%s)</span>', 'records', 'fiscaat' ), fct_get_period_record_count( $this->period_id ) ) . '</a>';
		}

		// Subtract post stati that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		$class = empty( $class ) && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';
		$status_links['all'] = "<a href=\"admin.php?page=fct-records{$parent}\"$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati ) )
				continue;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( ! empty( $_REQUEST['post_status'] ) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			$status_links[$status_name] = "<a href=\"admin.php?page=fct-records&amp;post_status=$status_name{$parent}\"$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return apply_filters( "fct_admin_get_records_views", $status_links );
	}

	/**
	 * Return dedicated bulk actions
	 *
	 * @since 0.0.8
	 *
	 * @return array Bulk actions
	 */
	public function _get_bulk_actions() {
		return array();
	}

	/**
	 * Return table classes. Mode aware
	 *
	 * @since 0.0.8
	 *
	 * @return array Classes
	 */
	public function get_table_classes() {
		$classes = array( 'widefat', 'fixed', 'posts', 'records' );
		return $classes;
	}

	/**
	 * Return dedicated record columns
	 *
	 * @since 0.0.8
	 *
	 * @return array Columns
	 */
	public function _get_columns() {
		$columns = array(
			'cb'                           => '<input type="checkbox" />',
			'fct_record_post_date'         => _x( 'Inserted', 'column name', 'fiscaat' ),
			'author'                       => __( 'Author' ),
			'fct_record_period'            => _x( 'Period',   'column name', 'fiscaat' ),
			'fct_record_account_ledger_id' => _x( 'No.',      'column name', 'fiscaat' ),
			'fct_record_account'           => __( 'Account',                 'fiscaat' ),
			'fct_record_description'       => __( 'Description',             'fiscaat' ),
			'fct_record_date'              => __( 'Date' ),
			'fct_record_offset_account'    => __( 'Offset Account',          'fiscaat' ),
			'fct_record_amount'            => _x( 'Amount', 'column name',   'fiscaat' ),
		);

		// Remove rows in new/edit mode
		if ( fct_admin_is_new_records() || fct_admin_is_edit_records() ) {
			unset(
				$columns['fct_record_post_date'],
				$columns['author'],
				$columns['fct_record_period']
			);

			// Display single account column for new mode
			if ( fct_admin_is_new_records() ) {
				unset( $columns['fct_record_account_ledger_id'] );
			}
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
	public function _get_sortable_columns() {

		// Do not sort in new mode
		if ( fct_admin_is_new_records() ) {
			$columns = array();

		} else {
			$columns = array(
				'fct_record_post_date'         => array( 'date',        true ),
				'fct_record_date'              => array( 'record_date', true ),

				// @todo Fix sorting by account ledger id.
				// @see Fiscaat_Records_Admin::filter_post_rows()
				// 'fct_record_account_ledger_id' => 'ledger_id',

				'fct_record_account'           => 'parent',
				'fct_record_offset_account'    => 'offset_account',
				'fct_record_amount'            => 'amount',
			);
		}

		return $columns;
	}

	/**
	 * Return columns that are hidden by default
	 *
	 * @since 0.0.8
	 *
	 * @return array Hidden columns
	 */
	public function _get_hidden_columns( $columns ) {

		// Hide columns on view page to keep it clean
		if ( fct_admin_is_view_records() ) {
			$columns[] = 'author';
			$columns[] = 'fct_record_period';
		}

		return $columns;
	}

	/** Display Rows ******************************************************/

	/**
	 * Generate the table navigation above or below the table
	 *
	 * When editing or creating records enclose the list table in
	 * a <form> element with method=post to enable proper submitting.
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_admin_is_view_records()
	 * @uses do_action() Calls 'fct_admin_posts_insert_form_bottom'
	 * @uses do_action() Calls 'fct_admin_posts_insert_form_top'
	 */
	public function display_tablenav( $which ) {
		if ( 'top' == $which ) {
			wp_nonce_field( 'bulk-records' );
		}

		// Close #posts-insert form and start bottom tablenav
		if ( 'bottom' == $which && ( fct_admin_is_new_records() || fct_admin_is_edit_records() ) ) : ?>

				<?php do_action( 'fct_admin_posts_insert_form_bottom' ); ?>

			</form><!-- #posts-insert -->
			<form id="posts-filter2" action="" method="get">

				<input type="hidden" name="page" class="post_page" value="<?php echo ! empty($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : 'fct-records'; ?>" />
				<input type="hidden" name="post_status" class="post_status_page" value="<?php echo ! empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : ''; ?>" />
				<?php wp_nonce_field( 'bulk-records' ); ?>

		<?php endif; ?>

			<div class="tablenav <?php echo esc_attr( $which ); ?>">

				<?php if ( $this->has_bulk_actions() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions(); ?>
				</div>
				<?php endif;

				$this->extra_tablenav( $which );
				$this->pagination( $which );

				?>

				<br class="clear" />
			</div>
		
		<?php // Close top tablenav and start #posts-insert form
		if ( 'top' == $which && ( fct_admin_is_new_records() || fct_admin_is_edit_records() ) ) : ?>

			</form><!-- #posts-filter -->
			<form id="posts-insert" action="" method="post">

				<?php do_action( 'fct_admin_posts_insert_form_top' ); ?> 

		<?php endif;
	}

	/**
	 * Generate the <tbody> part of the table
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_admin_is_view_records()
	 * @uses fct_admin_is_edit_records()
	 * @uses fct_admin_is_new_records()
	 * @uses FCT_Records_List_Table::display_rows()
	 * @uses FCT_Records_List_Table::display_edit_rows()
	 * @uses FCT_Records_List_Table::display_new_rows()
	 */
	public function display_rows_or_placeholder() {

		// Display edit mode, not when displaying account
		if ( fct_admin_is_edit_records() && $this->has_items() && ! $this->account_id ) {
			$this->display_edit_rows();

		// Display post-new mode
		} elseif ( fct_admin_is_new_records() ) {
			$this->display_new_rows();

		// Display rows when present or displaying account
		} elseif ( fct_admin_is_view_records() && ( $this->has_items() || $this->account_id ) ) {
			$this->display_rows();

		// Placeholder
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/** Edit Rows *********************************************************/

	/**
	 * Display post-new rows
	 *
	 * @since 0.0.8
	 *
	 * @uses FCT_Records_List_Table::_display_rows()
	 * @uses FCT_Records_List_Table::display_helper_row()
	 * @param array $posts Posts
	 * @param integer $level Depth
	 */
	public function display_edit_rows( $posts = array(), $level = 0 ) {
		global $wp_query;

		if ( empty( $posts ) ) {
			$posts = $wp_query->posts;
		}

		add_filter( 'the_title', 'esc_html' );

		$this->_display_rows( $posts, $level );

		// Total sum row
		$this->display_helper_row( 'total' );
	}

	/** New Rows **********************************************************/

	/**
	 * Display post-new rows
	 *
	 * @since 0.0.8
	 *
	 * @uses FCT_Records_List_Table::single_new_row()
	 * @uses FCT_Records_List_Table::display_helper_row()
	 */
	public function display_new_rows() {
		global $post;

		// Setup $post global to have all record meta keys
		$post = new WP_Post( (object) array_map( '__return_empty_string', fct_get_record_default_meta() ) );

		// Define local variable(s)
		$new_rows = 25; // get_option() ?

		// Manage unprocessed data
		if ( ! empty( $_REQUEST['records'] ) ) {

			// Keep copy of $post global
			$_post = $post;

			// Get unprocessed data
			$posts    = (array) $_REQUEST['records'];
			$new_rows = max( 0, $new_rows - count( $posts ) );

			// Display unprocessed data
			foreach ( $posts as $data ) {
				$post = new WP_Post( (object) $data );
				$this->single_new_row();
			}

			// Reset $post global
			$post = $_post;
		}

		// Display remaining empty rows
		for ( $i = 0; $i < $new_rows; $i++ ) {
			$this->single_new_row();
		}

		// Total sum row
		$this->display_helper_row( 'total' );
	}

	/**
	 * Display single post-new row
	 *
	 * @since 0.0.8
	 *
	 * @uses do_action() Calls 'fct_admin_new_records_row' with the column name
	 */
	public function single_new_row() {
		$alternate =& $this->alternate;
		$alternate = 'alternate' == $alternate ? '' : 'alternate';
		$classes = $alternate . ' iedit record';

		list( $columns, $hidden ) = $this->get_column_info(); ?>
		<tr class="<?php echo $classes; ?>" valign="top">

			<?php foreach ( $columns as $column_name => $column_display_name ) :
				$class = " class=\"$column_name column-$column_name\"";
				$style = '';

				if ( in_array( $column_name, $hidden ) )
					$style = ' style="display:none;"';

				$attributes = "$class$style";

				$el1 = 'cb' == $column_name ? 'th scope="row" class="check-column"' : "td $attributes";
				$el2 = 'cb' == $column_name ? 'th' : 'td';

				echo "<$el1>";
				do_action( "fct_admin_new_records_row", $column_name );
				echo "</$el2>";
			endforeach; ?>

		</tr>
		<?php
	}

	/**
	 * Display dedicated post-new column content
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_account_ledger_dropdown()
	 * @uses fct_account_dropdown()
	 * @param string $column_name Column name
	 */
	public function _new_row( $column_name ) {
		global $post;

		// Check column name
		switch ( $column_name ) {

			// Record account ledger id
			case 'fct_record_account_ledger_id' :
				fct_ledger_dropdown( array(
					'select_name'    => 'records[ledger_account_id][]',
					'class'          => 'fct_record_ledger_id',
					'show_none'      => '&mdash;',
					'disable_closed' => true,
					'selected'       => fct_get_account_ledger_id( $post->account_id ),
				) );
				break;

			// Record account
			case 'fct_record_account' :

				// Prepend ledger dropdown in new mode
				if ( fct_admin_is_new_records() && ! in_array( 'fct_record_account_ledger_id', array_keys( $this->get_columns() ) ) ) {
					fct_ledger_dropdown( array(
						'select_name'    => 'records[ledger_account_id][]',
						'class'          => 'fct_record_ledger_id',
						'show_none'      => '&mdash;',
						'disable_closed' => true,
						'selected'       => fct_get_account_ledger_id( $post->account_id ),
					) );
				}

				fct_account_dropdown( array(
					'select_name'    => 'records[account_id][]',
					'class'          => 'fct_record_account_id',
					'show_none'      => __( '&mdash; No Account &mdash;', 'fiscaat' ),
					'disable_closed' => true,
					'selected'       => $post->account_id,
				) );
				break;

			// Record content
			case 'fct_record_description' : ?>

				<textarea name="records[description][]" class="fct_record_description" rows="1"><?php echo esc_textarea( $post->post_content ); ?></textarea>

				<?php
				break;

			// Record date
			case 'fct_record_date': 
				$today = mysql2date( _x( 'd-m-Y', 'date input field format', 'fiscaat' ), fct_current_time() ); ?>

				<input name="records[record_date][]" type="text" class="fct_record_date medium-text datepicker" value="<?php echo esc_attr( $post->record_date ); ?>" placeholder="<?php echo $today; ?>" />

				<?php
				break;

			// Record offset account
			case 'fct_record_offset_account' : ?>

				<input name="records[offset_account][]" type="text" class="fct_record_offset_account" value="<?php echo esc_attr( $post->offset_account ); ?>" />

				<?php
				break;

			// Record amount
			case 'fct_record_amount' : ?>

				<input name="records[amount][<?php echo fct_get_debit_record_type_id(); ?>][]"  class="debit_amount small-text"  type="text" value="<?php if ( fct_get_debit_record_type_id()  == $post->record_type ) { echo esc_attr( $post->amount ); } ?>" />
				<input name="records[amount][<?php echo fct_get_credit_record_type_id(); ?>][]" class="credit_amount small-text" type="text" value="<?php if ( fct_get_credit_record_type_id() == $post->record_type ) { echo esc_attr( $post->amount ); } ?>" />

				<?php
				break;
		}
	}

	/** View Rows *********************************************************/

	/**
	 * Display post rows
	 *
	 * When there are items, show account (start, end, total) rows
	 *
	 * @since 0.0.8
	 *
	 * @uses FCT_Records_List_Table::display_helper_row()
	 * @param array $posts Posts
	 * @param integer $level Depth
	 */
	public function display_rows( $posts = array(), $level = 0 ) {
		global $wp_query;

		if ( empty( $posts ) ) {
			$posts = $wp_query->posts;
		}

		add_filter( 'the_title', 'esc_html' );

		// Start account row. Revenue accounts have no starting value
		if ( $this->account_id && ! fct_is_revenue_account( $this->account_id ) ) {
			$this->display_helper_row( 'start' );
		}

		$this->_display_rows( $posts, $level );

		// End account row
		if ( $this->account_id ) {
			$this->display_helper_row( 'end' );
		}

		// Total sum row
		$this->display_helper_row( 'total' );
	}

	/**
	 * Display dedicated column content
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_get_record_account_id()
	 * @uses get_the_date()
	 * @uses fct_account_ledger_id()
	 * @uses fct_get_account_title()
	 * @uses fct_record_excerpt()
	 * @uses fct_record_offset_account()
	 * @uses fct_get_record_amount()
	 * @uses fct_get_record_type()
	 * @uses fct_get_debit_record_type()
	 * @uses fct_get_credit_record_type()
	 * @uses fct_currency_format()
	 * @uses fct_get_record_period_id()
	 * @uses fct_get_account_period_id()
	 * @uses fct_get_period_title()
	 * @param string $column_name Column name
	 * @param int $record_id Record ID
	 */
	public function _column_content( $column_name, $record_id ) {
		$account_id = fct_get_record_account_id( $record_id );
		$period_id  = fct_get_record_period_id(  $record_id );

		// Check column name
		switch ( $column_name ) {

			// Record post date
			case 'fct_record_post_date':
				$date = get_post_time( 'Y-m-d H:i:s', false, $record_id );
				echo '<abbr title="' . mysql2date( __( 'Y/m/d g:i:s A' ), $date ) . '">' . mysql2date( _x( 'd/m/Y', 'Record date display format', 'fiscaat' ), $date ) . '</abbr>';
				break;

			// Record account ledger id
			case 'fct_record_account_ledger_id' :
				if ( ! empty( $account_id ) ) {
					$ledger_id = fct_get_account_ledger_id( $account_id );
					printf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'fct-records', 'ledger_id' => $ledger_id, 'period_id' => $period_id ), admin_url( 'admin.php' ) ), $ledger_id );
				}
				break;

			// Record account
			case 'fct_record_account' :
				if ( ! empty( $account_id ) ) {
					$account_title = fct_get_account_title( $account_id );
					if ( empty( $account_title ) ) {
						$account_title = __( 'No Account', 'fiscaat' );
					} else {
						$account_title = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'fct-records', 'account_id' => $account_id, 'period_id' => $period_id ), admin_url( 'admin.php' ) ), $account_title );
					}
					echo $account_title;

				} else {
					_e( 'No Account', 'fiscaat' );
				}
				break;

			// Record content
			case 'fct_record_description' :
				fct_record_excerpt( $record_id );
				break;

			// Record date
			case 'fct_record_date':
				$date = fct_get_record_date( $record_id );
				echo '<abbr title="' . mysql2date( __( 'Y/m/d g:i:s A' ), $date ) . '">' . mysql2date( _x( 'd/m/Y', 'Record date display format', 'fiscaat' ), $date ) . '</abbr>';
				break;

			// Record offset account
			case 'fct_record_offset_account' :
				fct_record_offset_account( $record_id );
				break;

			// Record amount
			case 'fct_record_amount' :
				$value = fct_get_record_amount( $record_id ); // Always float
				$this->amounts[ fct_get_record_type( $record_id ) ][] = $value; ?>

				<input id="fct_record_<?php echo $record_id; ?>_debit_amount"  class="debit_amount small-text"  type="text" value="<?php if ( fct_is_debit_record( $record_id )  ){ fct_currency_format( $value ); } ?>" readonly />
				<input id="fct_record_<?php echo $record_id; ?>_credit_amount" class="credit_amount small-text" type="text" value="<?php if ( fct_is_credit_record( $record_id ) ){ fct_currency_format( $value ); } ?>" readonly />

				<?php
				break;

			// Record period
			case 'fct_record_period' :
				$account_period_id = fct_get_account_period_id( $account_id );

				if ( ! empty( $period_id ) ) {
					$period_title = fct_get_period_title( $period_id );
					if ( empty( $period_title ) ) {
						$period_title = __( 'No Period', 'fiscaat' );
					}

					// Alert capable users of record period mismatch
					if ( $period_id != $account_period_id ) {
						if ( current_user_can( 'edit_others_records' ) ) {
							$period_title .= ' <div class="attention">' . __( '(Mismatch)', 'fiscaat' ) . '</div>';
						}
					}
					echo $period_title;

				} else {
					_e( 'No Period', 'fiscaat' );
				}
				break;
		}
	}

	/** Helper Rows *******************************************************/

	/**
	 * Display records's helper row
	 *
	 * @since 0.0.8
	 *
	 * @uses do_action() Calls 'fct_admin_records_{$which}_row'
	 * @param string $which The row name. Can be either 'start', 'end' or 'total'
	 */
	public function display_helper_row( $which = '' ) {

		// No helper rows for search results
		if ( is_search() && ! empty( $_REQUEST['s'] ) )
			return;

		// Bail if no row name given
		$which = esc_attr( esc_html( $which ) );
		if ( empty( $which ) )
			return;

		$alternate =& $this->alternate;
		$alternate = 'alternate' == $alternate ? '' : 'alternate';
		$classes   = "{$alternate} iedit {$which}-records";

		// Append sum mismatch class for total row
		if ( 'total' == $which && $this->sum_mismatch() ) {
			$classes .= ' mismatch';
		}

		// Define table-scroll bottom row
		if ( 'end' == $which || ( 'total' == $which && fct_admin_is_new_records() ) ) {
			$classes .= ' scroll-bottom-row';
		}

		list( $columns, $hidden ) = $this->get_column_info(); ?>
		<tr id="fct-<?php echo $which; ?>-records" class="<?php echo $classes; ?>" valign="top">

			<?php foreach ( $columns as $column_name => $column_display_name ) :
				$class = " class=\"$column_name column-$column_name\"";
				$style = '';

				if ( in_array( $column_name, $hidden ) )
					$style = ' style="display:none;"';

				$attributes = "$class$style";

				$el1 = 'cb' == $column_name ? 'th scope="row" class="check-column"' : "td $attributes";
				$el2 = 'cb' == $column_name ? 'th' : 'td';

				echo "<$el1>";
				do_action( "fct_admin_records_{$which}_row", $column_name );
				echo "</$el2>";
			endforeach; ?>

		</tr>
		<?php
	}

	/**
	 * Display contents of either an account's start or end row
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_get_account_id()
	 * @uses fct_get_account_type()
	 * @uses fct_get_account_start_value()
	 * @uses fct_get_account_end_value()
	 * @uses fct_get_debit_record_type_id()
	 * @uses fct_get_credit_record_type_id()
	 * @uses fct_currency_format()
	 * @param string $column Column name
	 */
	public function _start_or_end_row( $column ) {

		// Bail if no valid parent account id
		if ( ! $account_id = fct_get_account_id( $this->account_id ) )
			return;

		// Is this the start row?
		$start = false !== strpos( current_filter(), 'start' );

		// Check column name
		switch ( $column ) {

			// Row title
			case 'fct_record_description' :
				if ( fct_is_capital_account( $account_id ) ) {
					if ( $start ) {
						_e( 'Beginning Balance', 'fiscaat' );
					} else {
						_e( 'Ending Balance',    'fiscaat' );
					}
				} elseif ( fct_is_revenue_account( $account_id ) ) {
					// Revenue accounts have no starting value
					if ( ! $start ) {
						_e( 'To Income Statement', 'fiscaat' );
					}
				}
				break;

			// Row account amount
			case 'fct_record_amount' :
				$row   = $start ? 'start' : 'end';
				$value = call_user_func_array( "fct_get_account_{$row}_value", array( 'account_id' => $account_id ) );

				// Update skewed end value on the fly
				if ( 'end' == $row && $this->get_sum_diff() != $value ) {
					// $value = $this->get_sum_diff();
					// fct_update_account_end_value( $account_id, $value );
				}

				$this->amounts[ $value > 0 ? fct_get_debit_record_type_id() : fct_get_credit_record_type_id() ][] = abs( $value ); ?>

				<input id="fct_account_debit_<?php echo $row; ?>"  class="debit_amount small-text"  type="text" value="<?php if ( $value > 0 ) { fct_currency_format( abs( $value ) ); } ?>" readonly />
				<input id="fct_account_credit_<?php echo $row; ?>" class="credit_amount small-text" type="text" value="<?php if ( $value < 0 ) { fct_currency_format( abs( $value ) ); } ?>" readonly />

				<?php
				break;
		}
	}

	/**
	 * Display contents of the records's total row
	 *
	 * @since 0.0.8
	 *
	 * @uses fct_get_debit_record_type_id()
	 * @uses fct_get_credit_record_type_id()
	 * @uses fct_currency_format()
	 * @param string $column Column name
	 */
	public function _total_row( $column ) {

		// Check column name
		switch ( $column ) {

			// Row title
			case 'fct_record_description' :
				$total_title = _x( 'Total', 'Sum of all records', 'fiscaat' );

				// Alert for sum mismatch
				if ( $this->sum_mismatch() ) {
					$total_title .= ' <span class="attention">' . __( '(Mismatch)', 'fiscaat' ) . '</span>';
				}

				echo $total_title;
				break;

			// Submit button
			case 'fct_record_offset_account' :

				// THE records submit button
				if ( fct_admin_is_new_records() ) {
					wp_nonce_field( 'bulk-insert-records' );

					// Submit button
					submit_button( __( 'Submit', 'fiscaat' ), 'primary', 'submit-records', false );

					// Clear button
					echo '<input type="button" class="button button-secondary hide-if-no-js" onclick="clearInputs(this.form)" value="' . _x( 'Clear', 'Clear form button label', 'fiscaat' ) . '" />';
				}

				break;

			// Total amount
			case 'fct_record_amount' : 
				$placeholder = fct_get_currency_format( 0.00 ); ?>

				<input id="fct_records_debit_total"  class="debit_amount fct_record_total small-text"  type="text" value="<?php fct_currency_format( $this->get_debit_sum()  ); ?>" placeholder="<?php echo $placeholder; ?>" readonly />
				<input id="fct_records_credit_total" class="credit_amount fct_record_total small-text" type="text" value="<?php fct_currency_format( $this->get_credit_sum() ); ?>" placeholder="<?php echo $placeholder; ?>" readonly />

				<?php
				break;
		}
	}

	/**
	 * Return the current debit amount sum
	 * 
	 * @since 0.0.9
	 * 
	 * @uses fct_get_debit_record_type_id()
	 * @return float Debit amount sum
	 */
	public function get_debit_sum() {
		return (float) array_sum( $this->amounts[ fct_get_debit_record_type_id() ] );
	}

	/**
	 * Return the current credit amount sum
	 * 
	 * @since 0.0.9
	 * 
	 * @uses fct_get_credit_record_type_id()
	 * @return float Credit amount sum
	 */
	public function get_credit_sum() {
		return (float) array_sum( $this->amounts[ fct_get_credit_record_type_id() ] );
	}

	/**
	 * Return the amount sum difference: credit minus debit
	 *
	 * @since 0.0.9
	 *
	 * @uses FCT_Records_List_Table::get_debit_sum()
	 * @uses FCT_Records_List_Table::get_credit_sum()
	 * @return float Sum difference
	 */
	public function get_sum_diff() {
		return (float) ( $this->get_credit_sum() - $this->get_debit_sum() );
	}

	/**
	 * Return whether the amount sums do not match
	 *
	 * @since 0.0.9
	 *
	 * @uses FCT_Records_List_Table::get_debit_sum()
	 * @uses FCT_Records_List_Table::get_credit_sum()
	 * @return bool Sums do not match
	 */
	public function sum_mismatch() {
		return $this->get_debit_sum() != $this->get_credit_sum();
	}
}
