/**
 * Scripts for the Records pages
 *
 * @package Fiscaat
 * @subpackage Administration
 */
jQuery(document).ready( function($) {

	/** Select Account ********************************************************/

	// Get the dropdowns: list table filters, new records accounts, and the Edit Record page
	var dropdowns = [ 
		// Ledger dropdowns
		$( 'select#fct_ledger_id_filter, select.fct_record_ledger_id, select#fct_record_account_ledger_id' ), 
		// Account dropdowns
		$( 'select#fct_account_id_filter, select.fct_record_account_id, select#parent_id' ) 
	];

	// Make dropdowns listen to their co-dropdown
	$.each( dropdowns, function( i ){
		// For each change in a dropdown of the one kind, change the matching dropdown of the other kind
		$.each( this, function( j ) {
			var $this = $(this),
			    other = false;

			$this.change( function(){
				// Find corresponding dropdown option
				if ( 0 === i ) {
					other = $( 'option', dropdowns[1][j] ).filter( function(){ return $this.val() == $(this).data('ledger_id'); } );
				} else {
					other = $( 'option', dropdowns[0][j] ).filter( function(){ return $this.find('option:selected').data('ledger_id') == this.value; } );
				}

				// Mark the matched option as selected
				if ( !! other.length ) {
					other.prop( 'selected', true );
				// Reset dropdown: select the first element
				} else {
					$( 'option', dropdowns[ 0 === i ? 1 : 0 ][j] ).first().prop( 'selected', true );
				}
			});
		});
	});

	/** Inserting Records *****************************************************/

	var $table = $( '.widefat.records' );
	    $debits = $table.find( '.debit_amount' ),
	    $credits = $table.find( '.credit_amount' ),
	    $sumRow = $( '#fct-total-records' ), // May not be in $table
	    $debitSum = $sumRow.find( '#fct_records_debit_total' ),
	    $creditSum = $sumRow.find( '#fct_records_credit_total' ),
	    debit = 'debit',
	    credit = 'credit',
	    debitSum = 0,
	    creditSum = 0;

	// Listen for changes on debit/credit amount input fields
	$.each( [ $debits, $credits ], function( i, list ) {
		list.each( function( j, input ) {
			$( input )

				// Store current value
				.on( 'focus', function() {
					$( this ).data( 'originalValue', this.value );
				})

				// Process input value
				.on( 'blur', function( e ) {
					var $this = $( this ),
					    which = list[0].classList.contains( 'debit_amount' ) ? debit : credit,
					    sanitized_value = formatNumber( this.value );

					// Display properly formatted input
					$this.val( sanitized_value );

					// Empty the adjacent input when entry is valid
					if ( isValidNumber( sanitized_value ) ) {
						var $adjacent = $this[ ( debit === which ) ? 'next' : 'prev' ](),
						    other_value = $adjacent.val();

						// Empty adjacent input
						$adjacent.removeAttr( 'value' );

						// Recalculate the other type if the value was valid
						if ( isValidNumber( other_value ) ) {
							updateSum( otherType( which ) );
						}
					}

					// When input is valid
					if ( isValidNumber( sanitized_value ) || 
						// When original input was valid but now it is not (i.e. input was emptied)
						isValidNumber( $this.data( 'originalValue' ) ) ) {

						// Recalculate this type
						updateSum( which );
					}
				});
		});
	});

	/**
	 * Return the other column type
	 * 
	 * @param {string} which The other's other column type
	 * @return {string} The other column type
	 */
	function otherType( which ) {
		return ( debit === which ) ? credit : debit;
	}

	/**
	 * Parse the argument as a formatted number for display
	 * 
	 * @param  {mixed} number Value to format
	 * @return {string} Formatted number with 2 digits or empty
	 */
	function formatNumber( number ) {
		var n = parseFloat( number );
		return ( ! isNaN( n ) ) ? n.toFixed(2).toString() : '';
	}

	/**
	 * Return whether the given value is a valid number
	 * 
	 * @param  {mixed} number Value to check
	 * @return {Boolean} Value is a valid number
	 */
	function isValidNumber( number ) {
		return ! isNaN( parseFloat( number ) );
	}

	/**
	 * Update the sum of the given column type
	 *
	 * Sets the value in the corresponding sum holder.
	 *
	 * @param {string} which The column type to sum. Defaults to both columns
	 */
	function updateSum( which ) {
		var v = 0;

		// Update both when no arg was passed
		if ( 'undefined' === typeof which ) {
			which = false;
		}

		// Sum debit values
		if ( false === which || debit === which ) {
			debitSum = 0;
			$debits.filter( function(){ return !! this.value; }).each( function( i, el ){
				v = parseFloat( el.value );
				debitSum += ( ! isNaN( v ) ) ? v : 0;
			});

			$debitSum.val( formatNumber( debitSum ) );
		}

		// Sum credit values
		if ( false === which || credit === which ) {
			creditSum = 0;
			$credits.filter( function(){ return !! this.value; }).each( function( i, el ){
				v = parseFloat( el.value );
				creditSum += ( ! isNaN( v ) ) ? v : 0;
			});

			$creditSum.val( formatNumber( creditSum ) );
		}

		// Handle sum inequality 
		if ( debitSum !== creditSum ) {
			// Add mismatch class for visual hint 
			$sumRow.addClass( 'mismatch' )
				// And toggle submit button disabler
				.find( 'input[name="submit-records"]' ).prop( 'disabled', true );
		} else if ( $sumRow.hasClass( 'mismatch' ) ) {
			// Remove mismatch class
			$sumRow.removeClass( 'mismatch' )
				// And toggle submit button disabler
				.find( 'input[name="submit-records"]' ).prop( 'disabled', false );
		}
	}

	// Calculate on page load for browsers that keep input values on page refresh
	updateSum();

});
