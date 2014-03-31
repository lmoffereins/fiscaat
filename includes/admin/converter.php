<?php

/**
 * Fiscaat Converter
 *
 * Based on the hard work of Adam Ellis at http://bbconverter.com
 *
 * @package Fiscaat
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Fiscaat_Converter Class
 */
class Fiscaat_Converter {

	/**
	 * The main Fiscaat Converter loader
	 *
	 * @since Fiscaat (r3813)
	 * @uses Fiscaat_Converter::includes() Include the required files
	 * @uses Fiscaat_Converter::setup_actions() Setup the actions
	 */
	public function __construct() {

		// Bail if request is not correct
		switch ( strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {

			// Converter is converting
			case 'POST' :
				if ( ( empty( $_POST['action'] ) || ( 'bbconverter_process' !=  $_POST['action'] ) ) )
					return;

				break;

			// Some other admin page
			case 'GET'  :
				if ( ( empty( $_GET['page'] ) || ( 'fiscaat-converter' !=  $_GET['page'] ) ) )
					return;

				break;
		}

		// Proceed with the actions
		$this->setup_actions();
	}

	/**
	 * Setup the default actions
	 *
	 * @since Fiscaat (r3813)
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		// Attach to the admin head with our ajax requests cycle and css
		add_action( 'fiscaat_admin_head',              array( $this, 'admin_head'              ) );

		// Attach the bbConverter admin settings action to the WordPress admin init action.
		add_action( 'fiscaat_register_admin_settings', array( $this, 'register_admin_settings' ) );

		// Attach to the admin ajax request to process cycles
		add_action( 'wp_ajax_bbconverter_process', array( $this, 'process_callback'        ) );
	}

	/**
	 * Register the settings
	 *
	 * @since Fiscaat (r3813)
	 * @uses add_settings_section() To add our own settings section
	 * @uses add_settings_field() To add various settings fields
	 * @uses register_setting() To register various settings
	 */
	public function register_admin_settings() {

		// Add the main section
		add_settings_section( 'fiscaat_converter_main',     __( 'Database Settings', 'fiscaat' ),  'fiscaat_converter_setting_callback_main_section', 'fiscaat_converter' );

		// System Select
		add_settings_field( '_fiscaat_converter_platform',      __( 'Select Platform',   'fiscaat' ),  'fiscaat_converter_setting_callback_platform', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_platform',           'sanitize_title' );

		// Database Server
		add_settings_field( '_fiscaat_converter_db_server',     __( 'Database Server',   'fiscaat' ),  'fiscaat_converter_setting_callback_dbserver', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_db_server',          'sanitize_title' );

		// Database Server Port
		add_settings_field( '_fiscaat_converter_db_port',       __( 'Database Port',     'fiscaat' ),  'fiscaat_converter_setting_callback_dbport', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_db_port',            'sanitize_title' );

		// Database Name
		add_settings_field( '_fiscaat_converter_db_name',       __( 'Database Name',     'fiscaat' ),  'fiscaat_converter_setting_callback_dbname', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_db_name',            'sanitize_title' );

		// Database User
		add_settings_field( '_fiscaat_converter_db_user',       __( 'Database User',     'fiscaat' ),  'fiscaat_converter_setting_callback_dbuser', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_db_user',            'sanitize_title' );

		// Database Pass
		add_settings_field( '_fiscaat_converter_db_pass',       __( 'Database Password', 'fiscaat' ),  'fiscaat_converter_setting_callback_dbpass', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_db_pass',            'sanitize_title' );

		// Database Prefix
		add_settings_field( '_fiscaat_converter_db_prefix',     __( 'Table Prefix',      'fiscaat' ),  'fiscaat_converter_setting_callback_dbprefix', 'fiscaat_converter', 'fiscaat_converter_main' );
		register_setting  ( 'fiscaat_converter_main',       '_fiscaat_converter_db_prefix',          'sanitize_title' );

		// Add the options section
		add_settings_section( 'fiscaat_converter_opt',      __( 'Options',           'fiscaat' ),  'fiscaat_converter_setting_callback_options_section', 'fiscaat_converter' );

		// Rows Limit
		add_settings_field( '_fiscaat_converter_rows',          __( 'Rows Limit',        'fiscaat' ),  'fiscaat_converter_setting_callback_rows', 'fiscaat_converter', 'fiscaat_converter_opt' );
		register_setting  ( 'fiscaat_converter_opt',        '_fiscaat_converter_rows',               'intval' );

		// Delay Time
		add_settings_field( '_fiscaat_converter_delay_time',    __( 'Delay Time',        'fiscaat' ), 'fiscaat_converter_setting_callback_delay_time', 'fiscaat_converter', 'fiscaat_converter_opt' );
		register_setting  ( 'fiscaat_converter_opt',        '_fiscaat_converter_delay_time',        'intval' );

		// Convert Users ?
		add_settings_field( '_fiscaat_converter_convert_users', __( 'Convert Users',     'fiscaat' ), 'fiscaat_converter_setting_callback_convert_users', 'fiscaat_converter', 'fiscaat_converter_opt' );
		register_setting  ( 'fiscaat_converter_opt',        '_fiscaat_converter_convert_users',     'intval' );

		// Restart
		add_settings_field( '_fiscaat_converter_restart',       __( 'Start Over',        'fiscaat' ), 'fiscaat_converter_setting_callback_restart', 'fiscaat_converter', 'fiscaat_converter_opt' );
		register_setting  ( 'fiscaat_converter_opt',        '_fiscaat_converter_restart',           'intval' );

		// Clean
		add_settings_field( '_fiscaat_converter_clean',         __( 'Purge Previous Import', 'fiscaat' ), 'fiscaat_converter_setting_callback_clean', 'fiscaat_converter', 'fiscaat_converter_opt' );
		register_setting  ( 'fiscaat_converter_opt',        '_fiscaat_converter_clean',             'intval' );
	}

	/**
	 * Admin scripts
	 *
	 * @since Fiscaat (r3813)
	 */
	public function admin_head() { ?>

		<style type="text/css" media="screen">
			/*<![CDATA[*/

			div.fiscaat-converter-updated,
			div.fiscaat-converter-warning {
				border-radius: 3px 3px 3px 3px;
				border-style: solid;
				border-width: 1px;
				padding: 5px 5px 5px 5px;
			}

			div.fiscaat-converter-updated {
				height: 300px;
				overflow: auto;
				display: none;
				background-color: #FFFFE0;
				border-color: #E6DB55;
				font-family: monospace;
				font-weight: bold;
			}

			div.fiscaat-converter-updated p {
				margin: 0.5em 0;
				padding: 2px;
				float: left;
				clear: left;
			}

			div.fiscaat-converter-updated p.loading {
				padding: 2px 20px 2px 2px;
				background-image: url('<?php echo admin_url(); ?>images/wpspin_light.gif');
				background-repeat: no-repeat;
				background-position: center right;
			}

			#fiscaat-converter-stop {
				display:none;
			}

			#fiscaat-converter-progress {
				display:none;
			}

			/*]]>*/
		</style>

		<script language="javascript">

			var bbconverter_is_running = false;
			var bbconverter_run_timer;
			var bbconverter_delay_time = 0;

			function bbconverter_grab_data() {
				var values = {};
				jQuery.each(jQuery('#fiscaat-converter-settings').serializeArray(), function(i, field) {
					values[field.name] = field.value;
				});

				if( values['_fiscaat_converter_restart'] ) {
					jQuery('#_fiscaat_converter_restart').removeAttr("checked");
				}

				if( values['_fiscaat_converter_delay_time'] ) {
					bbconverter_delay_time = values['_fiscaat_converter_delay_time'] * 1000;
				}

				values['action'] = 'bbconverter_process';
				values['_ajax_nonce'] = '<?php echo  wp_create_nonce( 'fiscaat_converter_process' ); ?>';

				return values;
			}

			function bbconverter_start() {
				if( false == bbconverter_is_running ) {
					bbconverter_is_running = true;
					jQuery('#fiscaat-converter-start').hide();
					jQuery('#fiscaat-converter-stop').show();
					jQuery('#fiscaat-converter-progress').show();
					bbconverter_log( '<p class="loading"><?php _e( 'Starting Conversion', 'fiscaat' ); ?></p>' );
					bbconverter_run();
				}
			}

			function bbconverter_run() {
				jQuery.post(ajaxurl, bbconverter_grab_data(), function(response) {
					var response_length = response.length - 1;
					response = response.substring(0,response_length);
					bbconverter_success(response);
				});
			}

			function bbconverter_stop() {
				jQuery('#fiscaat-converter-start').show();
				jQuery('#fiscaat-converter-stop').hide();
				jQuery('#fiscaat-converter-progress').hide();
				jQuery('#fiscaat-converter-message p').removeClass( 'loading' );
				bbconverter_is_running = false;
				clearTimeout( bbconverter_run_timer );
			}

			function bbconverter_success(response) {
				bbconverter_log(response);

				if ( response == '<p class="loading"><?php _e( 'Conversion Complete', 'fiscaat' ); ?></p>' || response.indexOf('error') > -1 ) {
					bbconverter_log('<p>Repair any missing information: <a href="<?php echo admin_url(); ?>tools.php?page=fiscaat-repair">Continue</a></p>');
					bbconverter_stop();
				} else if( bbconverter_is_running ) { // keep going
					jQuery('#fiscaat-converter-progress').show();
					clearTimeout( bbconverter_run_timer );
					bbconverter_run_timer = setTimeout( 'bbconverter_run()', bbconverter_delay_time );
				} else {
					bbconverter_stop();
				}
			}

			function bbconverter_log(text) {
				if ( jQuery('#fiscaat-converter-message').css('display') == 'none' ) {
					jQuery('#fiscaat-converter-message').show();
				}
				if ( text ) {
					jQuery('#fiscaat-converter-message p').removeClass( 'loading' );
					jQuery('#fiscaat-converter-message').prepend( text );
				}
			}

		</script>

		<?php
	}

	/**
	 * Wrap the converter output in paragraph tags, so styling can be applied
	 *
	 * @since Fiscaat (r4052)
	 *
	 * @param string $output
	 */
	private static function converter_output( $output = '' ) {

		// Get the last query
		$before = '<p class="loading">';
		$after  = '</p>';
		$query  = get_option( '_fiscaat_converter_query' );

		if ( ! empty( $query ) )
			$before = '<p class="loading" title="' . esc_attr( $query ) . '">';

		echo $before . $output . $after;
	}

	/**
	 * Callback processor
	 *
	 * @since Fiscaat (r3813)
	 */
	public function process_callback() {

		// Verify intent
		check_ajax_referer( 'fiscaat_converter_process' );

		if ( ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
			ini_set( 'memory_limit',   '256M' );
			ini_set( 'implicit_flush', '1'    );
			ignore_user_abort( true );
		}

		// Save step and count so that it can be restarted.
		if ( ! get_option( '_fiscaat_converter_step' ) || ( ! empty( $_POST['_fiscaat_converter_restart'] ) ) ) {
			update_option( '_fiscaat_converter_step',  1 );
			update_option( '_fiscaat_converter_start', 0 );
		}

		$step  = (int) get_option( '_fiscaat_converter_step',  1 );
		$min   = (int) get_option( '_fiscaat_converter_start', 0 );
		$count = (int) ! empty( $_POST['_fiscaat_converter_rows'] ) ? $_POST['_fiscaat_converter_rows'] : 100;
		$max   = ( $min + $count ) - 1;
		$start = $min;

		// Bail if platform did not get saved
		$platform = ! empty( $_POST['_fiscaat_converter_platform' ] ) ? $_POST['_fiscaat_converter_platform' ] : get_option( '_fiscaat_converter_platform' );
		if ( empty( $platform ) )
			return;

		// Include the appropriate converter.
		$converter = fiscaat_new_converter( $platform );

		switch ( $step ) {

			// STEP 1. Clean all tables.
			case 1 :
				if ( ! empty( $_POST['_fiscaat_converter_clean'] ) ) {
					if ( $converter->clean( $start ) ) {
						update_option( '_fiscaat_converter_step',  $step + 1 );
						update_option( '_fiscaat_converter_start', 0         );
						$this->sync_table( true );
						if ( empty( $start ) ) {
							$this->converter_output( __( 'No data to clean', 'fiscaat' ) );
						}
					} else {
						update_option( '_fiscaat_converter_start', $max + 1 );
						$this->converter_output( sprintf( __( 'Deleting previously converted data (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
					}
				} else {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
				}

				break;

			// STEP 2. Convert users.
			case 2 :
				if ( ! empty( $_POST['_fiscaat_converter_convert_users'] ) ) {
					if ( $converter->convert_users( $start ) ) {
						update_option( '_fiscaat_converter_step',  $step + 1 );
						update_option( '_fiscaat_converter_start', 0         );
						if ( empty( $start ) ) {
							$this->converter_output( __( 'No users to convert', 'fiscaat' ) );
						}
					} else {
						update_option( '_fiscaat_converter_start', $max + 1 );
						$this->converter_output( sprintf(  __( 'Converting users (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
					}
				} else {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
				}

				break;

			// STEP 3. Clean passwords.
			case 3 :
				if ( ! empty( $_POST['_fiscaat_converter_convert_users'] ) ) {
					if ( $converter->clean_passwords( $start ) ) {
						update_option( '_fiscaat_converter_step',  $step + 1 );
						update_option( '_fiscaat_converter_start', 0         );
						if ( empty( $start ) ) {
							$this->converter_output( __( 'No passwords to clear', 'fiscaat' ) );
						}
					} else {
						update_option( '_fiscaat_converter_start', $max + 1 );
						$this->converter_output( sprintf( __( 'Delete users wordpress default passwords (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
					}
				} else {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
				}

				break;

			// STEP 4. Convert years.
			case 4 :
				if ( $converter->convert_years( $start ) ) {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
					if ( empty( $start ) ) {
						$this->converter_output( __( 'No years to convert', 'fiscaat' ) );
					}
				} else {
					update_option( '_fiscaat_converter_start', $max + 1 );
					$this->converter_output( sprintf( __( 'Converting years (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
				}

				break;

			// STEP 5. Convert year parents.
			case 5 :
				if ( $converter->convert_year_parents( $start ) ) {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
					if ( empty( $start ) ) {
						$this->converter_output( __( 'No year parents to convert', 'fiscaat' ) );
					}
				} else {
					update_option( '_fiscaat_converter_start', $max + 1 );
					$this->converter_output( sprintf( __( 'Calculating year hierarchy (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
				}

				break;

			// STEP 6. Convert accounts.
			case 6 :
				if ( $converter->convert_accounts( $start ) ) {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
					if ( empty( $start ) ) {
						$this->converter_output( __( 'No accounts to convert', 'fiscaat' ) );
					}
				} else {
					update_option( '_fiscaat_converter_start', $max + 1 );
					$this->converter_output( sprintf( __( 'Converting accounts (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
				}

				break;

			// STEP 7. Convert tags.
			case 7 :
				if ( $converter->convert_tags( $start ) ) {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
					if ( empty( $start ) ) {
						$this->converter_output( __( 'No tags to convert', 'fiscaat' ) );
					}
				} else {
					update_option( '_fiscaat_converter_start', $max + 1 );
					$this->converter_output( sprintf( __( 'Converting account tags (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
				}

				break;

			// STEP 8. Convert records.
			case 8 :
				if ( $converter->convert_records( $start ) ) {
					update_option( '_fiscaat_converter_step',  $step + 1 );
					update_option( '_fiscaat_converter_start', 0         );
					if ( empty( $start ) ) {
						$this->converter_output( __( 'No records to convert', 'fiscaat' ) );
					}
				} else {
					update_option( '_fiscaat_converter_start', $max + 1 );
					$this->converter_output( sprintf( __( 'Converting records (%1$s - %2$s)', 'fiscaat' ), $min, $max ) );
				}

				break;

			default :
				delete_option( '_fiscaat_converter_step'  );
				delete_option( '_fiscaat_converter_start' );
				delete_option( '_fiscaat_converter_query' );

				$this->converter_output( __( 'Conversion Complete', 'fiscaat' ) );

				break;
		}
	}

	/**
	 * Create Tables for fast syncing
	 *
	 * @since Fiscaat (r3813)
	 */
	public function sync_table( $drop = false ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'fiscaat_converter_translator';
		if ( ! empty( $drop ) && $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) == $table_name )
			$wpdb->query( "DROP TABLE {$table_name}" );

		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		/** Translator ****************************************************/

		$sql = "CREATE TABLE {$table_name} (
					meta_id mediumint(8) unsigned not null auto_increment,
					value_type varchar(25) null,
					value_id bigint(20) unsigned not null default '0',
					meta_key varchar(25) null,
					meta_value varchar(25) null,
				PRIMARY KEY  (meta_id),
					KEY value_id (value_id),
					KEY meta_join (meta_key, meta_value) ) {$charset_collate};";

		dbDelta( $sql );
	}
}

/**
 * Base class to be extended by specific individual importers
 *
 * @since Fiscaat (r3813)
 */
abstract class Fiscaat_Converter_Base {

	/**
	 * @var array() This is the field mapping array to process.
	 */
	protected $field_map = array();

	/**
	 * @var object This is the connection to the wordpress datbase.
	 */
	protected $wpdb;

	/**
	 * @var object This is the connection to the other platforms database.
	 */
	protected $opdb;

	/**
	 * @var int This is the max rows to process at a time.
	 */
	public $max_rows;

	/**
	 * @var array() Map of account to year.  It is for optimization.
	 */
	private $map_accountid_to_yearid = array();

	/**
	 * @var array() Map of from old year ids to new year ids.  It is for optimization.
	 */
	private $map_yearid = array();

	/**
	 * @var array() Map of from old account ids to new account ids.  It is for optimization.
	 */
	private $map_accountid = array();

	/**
	 * @var array() Map of from old user ids to new user ids.  It is for optimization.
	 */
	private $map_userid = array();

	/**
	 * @var str This is the charset for your wp database.
	 */
	public $charset;

	/**
	 * @var boolean Sync table available.
	 */
	public $sync_table = false;

	/**
	 * @var str Sync table name.
	 */
	public $sync_table_name;

	/** Methods ***************************************************************/

	/**
	 * This is the constructor and it connects to the platform databases.
	 */
	public function __construct() {
		$this->setup_globals();
	}

	private function setup_globals() {
		global $wpdb;

		/** Get database connections ******************************************/

		$this->wpdb         = $wpdb;
		$this->max_rows     = (int) $_POST['_fiscaat_converter_rows'];
		$this->opdb         = new wpdb( $_POST['_fiscaat_converter_db_user'], $_POST['_fiscaat_converter_db_pass'], $_POST['_fiscaat_converter_db_name'], $_POST['_fiscaat_converter_db_server'] );
		$this->opdb->prefix = $_POST['_fiscaat_converter_db_prefix'];

		/**
		 * Error Reporting
		 */
		$this->wpdb->show_errors();
		$this->opdb->show_errors();

		/**
		 * Syncing
		 */
		$this->sync_table_name = $this->wpdb->prefix . 'fiscaat_converter_translator';
		if ( $this->wpdb->get_var( "SHOW TABLES LIKE '" . $this->sync_table_name . "'" ) == $this->sync_table_name ) {
			$this->sync_table = true;
		} else {
			$this->sync_table = false;
		}

		/**
		 * Charset
		 */
		if ( empty( $this->wpdb->charset ) ) {
			$this->charset = 'UTF8';
		} else {
			$this->charset = $this->wpdb->charset;
		}

		/**
		 * Default mapping.
		 */

		/** Year Section *****************************************************/

		$this->field_map[] = array(
			'to_type'      => 'year',
			'to_fieldname' => 'post_status',
			'default'      => 'publish'
		);
		$this->field_map[] = array(
			'to_type'      => 'year',
			'to_fieldname' => 'comment_status',
			'default'      => 'closed'
		);
		$this->field_map[] = array(
			'to_type'      => 'year',
			'to_fieldname' => 'ping_status',
			'default'      => 'closed'
		);
		$this->field_map[] = array(
			'to_type'      => 'year',
			'to_fieldname' => 'post_type',
			'default'      => 'year'
		);

		/** Account Section *****************************************************/

		$this->field_map[] = array(
			'to_type'      => 'account',
			'to_fieldname' => 'post_status',
			'default'      => 'publish'
		);
		$this->field_map[] = array(
			'to_type'      => 'account',
			'to_fieldname' => 'comment_status',
			'default'      => 'closed'
		);
		$this->field_map[] = array(
			'to_type'      => 'account',
			'to_fieldname' => 'ping_status',
			'default'      => 'closed'
		);
		$this->field_map[] = array(
			'to_type'      => 'account',
			'to_fieldname' => 'post_type',
			'default'      => 'account'
		);

		/** Post Section ******************************************************/

		$this->field_map[] = array(
			'to_type'      => 'record',
			'to_fieldname' => 'post_status',
			'default'      => 'publish'
		);
		$this->field_map[] = array(
			'to_type'      => 'record',
			'to_fieldname' => 'comment_status',
			'default'      => 'closed'
		);
		$this->field_map[] = array(
			'to_type'      => 'record',
			'to_fieldname' => 'ping_status',
			'default'      => 'closed'
		);
		$this->field_map[] = array(
			'to_type'      => 'record',
			'to_fieldname' => 'post_type',
			'default'      => 'record'
		);

		/** User Section ******************************************************/

		$this->field_map[] = array(
			'to_type'      => 'user',
			'to_fieldname' => 'role',
			'default'      => get_option( 'default_role' )
		);
	}

	/**
	 * Convert Years
	 */
	public function convert_years( $start = 1 ) {
		return $this->convert_table( 'year', $start );
	}

	/**
	 * Convert Accounts / Threads
	 */
	public function convert_accounts( $start = 1 ) {
		return $this->convert_table( 'account', $start );
	}

	/**
	 * Convert Posts
	 */
	public function convert_records( $start = 1 ) {
		return $this->convert_table( 'record', $start );
	}

	/**
	 * Convert Users
	 */
	public function convert_users( $start = 1 ) {
		return $this->convert_table( 'user', $start );
	}

	/**
	 * Convert Tags
	 */
	public function convert_tags( $start = 1 ) {
		return $this->convert_table( 'tags', $start );
	}

	/**
	 * Convert Table
	 *
	 * @param string to type
	 * @param int Start row
	 */
	public function convert_table( $to_type, $start ) {

		// Are we usig a sync table, or postmeta?
		if ( $this->wpdb->get_var( "SHOW TABLES LIKE '" . $this->sync_table_name . "'" ) == $this->sync_table_name ) {
			$this->sync_table = true;
		} else {
			$this->sync_table = false;
		}

		// Set some defaults
		$has_insert     = false;
		$from_tablename = '';
		$field_list     = $from_tables = $tablefield_array = array();

		// Toggle Table Name based on $to_type (destination)
		switch ( $to_type ) {
			case 'user' :
				$tablename = $this->wpdb->users;
				break;

			case 'tags' :
				$tablename = '';
				break;

			default :
				$tablename = $this->wpdb->posts;
		}

		// Get the fields from the destination table
		if ( ! empty( $tablename ) ) {
			$tablefield_array = $this->get_fields( $tablename );
		}

		/** Step 1 ************************************************************/

		// Loop through the field maps, and look for to_type matches
		foreach ( $this->field_map as $item ) {

			// Yay a match, and we have a from table, too
			if ( ( $item['to_type'] == $to_type ) && ! empty( $item['from_tablename'] ) ) {

				// $from_tablename was set from a previous loop iteration
				if ( ! empty( $from_tablename ) ) {

					// Doing some joining
					if ( !in_array( $item['from_tablename'], $from_tables ) && in_array( $item['join_tablename'], $from_tables ) ) {
						$from_tablename .= ' ' . $item['join_type'] . ' JOIN ' . $this->opdb->prefix . $item['from_tablename'] . ' AS ' . $item['from_tablename'] . ' ' . $item['join_expression'];
					}

				// $from_tablename needs to be set
				} else {
					$from_tablename = $item['from_tablename'] . ' AS ' . $item['from_tablename'];
				}

				// Specific FROM expression data used
				if ( ! empty( $item['from_expression'] ) ) {

					// No 'WHERE' in expression
					if ( stripos( $from_tablename, "WHERE" ) === false ) {
						$from_tablename .= ' ' . $item['from_expression'];

					// 'WHERE' in expression, so replace with 'AND'
					} else {
						$from_tablename .= ' ' . str_replace( "WHERE", "AND", $item['from_expression'] );
					}
				}

				// Add tablename and fieldname to arrays, formatted for querying
				$from_tables[] = $item['from_tablename'];
				$field_list[]  = 'convert(' . $item['from_tablename'] . '.' . $item['from_fieldname'] . ' USING "' . $this->charset . '") AS ' . $item['from_fieldname'];
			}
		}

		/** Step 2 ************************************************************/

		// We have a $from_tablename, so we want to get some data to convert
		if ( ! empty( $from_tablename ) ) {

			// Get some data from the old years
			$field_list  = array_unique( $field_list );
			$year_query = 'SELECT ' . implode( ',', $field_list ) . ' FROM ' . $this->opdb->prefix . $from_tablename . ' LIMIT ' . $start . ', ' . $this->max_rows;
			$year_array = $this->opdb->get_results( $year_query, ARRAY_A );

			// Set this query as the last one ran
			update_option( '_fiscaat_converter_query', $year_query );

			// Query returned some results
			if ( ! empty( $year_array ) ) {

				// Loop through results
				foreach ( (array) $year_array as $year ) {

					// Reset some defaults
					$insert_post = $insert_postmeta = $insert_data = array();

					// Loop through field map, again...
					foreach ( $this->field_map as $row ) {

						// Types matchand to_fieldname is present. This means
						// we have some work to do here.
						if ( ( $row['to_type'] == $to_type ) && ! is_null( $row['to_fieldname'] ) ) {

							// This row has a destination that matches one of the
							// columns in this table.
							if ( in_array( $row['to_fieldname'], $tablefield_array ) ) {

								// Allows us to set default fields.
								if ( isset( $row['default'] ) ) {
									$insert_post[$row['to_fieldname']] = $row['default'];

								// Translates a field from the old year.
								} elseif ( isset( $row['callback_method'] ) ) {
									if ( ( 'callback_userid' == $row['callback_method'] ) && empty( $_POST['_fiscaat_converter_convert_users'] ) ) {
										$insert_post[$row['to_fieldname']] = $year[$row['from_fieldname']];
									} else {
										$insert_post[$row['to_fieldname']] = call_user_func_array( array( $this, $row['callback_method'] ), array( $year[$row['from_fieldname']], $year ) );
									}

								// Maps the field from the old year.
								} else {
									$insert_post[$row['to_fieldname']] = $year[$row['from_fieldname']];
								}

							// Destination field is not empty, so we might need
							// to do some extra work or set a default.
							} elseif ( ! empty( $row['to_fieldname'] ) ) {

								// Allows us to set default fields.
								if ( isset( $row['default'] ) ) {
									$insert_postmeta[$row['to_fieldname']] = $row['default'];

								// Translates a field from the old year.
								} elseif ( isset( $row['callback_method'] ) ) {
									if ( ( $row['callback_method'] == 'callback_userid' ) && ( 0 == $_POST['_fiscaat_converter_convert_users'] ) ) {
										$insert_postmeta[$row['to_fieldname']] = $year[$row['from_fieldname']];
									} else {
										$insert_postmeta[$row['to_fieldname']] = call_user_func_array( array( $this, $row['callback_method'] ), array( $year[$row['from_fieldname']], $year ) );
									}

								// Maps the field from the old year.
								} else {
									$insert_postmeta[$row['to_fieldname']] = $year[$row['from_fieldname']];
								}
							}
						}
					}

					/** Step 3 ************************************************/

					// Something to insert into the destination field
					if ( count( $insert_post ) > 0 || ( $to_type == 'tags' && count( $insert_postmeta ) > 0 ) ) {

						switch ( $to_type ) {

							/** New user **************************************/

							case 'user':
								if ( username_exists( $insert_post['user_login'] ) ) {
									$insert_post['user_login'] = 'imported_' . $insert_post['user_login'];
								}

								if ( email_exists( $insert_post['user_email'] ) ) {
									$insert_post['user_email'] = 'imported_' . $insert_post['user_email'];
								}

								$post_id = wp_insert_user( $insert_post );

								if ( is_numeric( $post_id ) ) {

									foreach ( $insert_postmeta as $key => $value ) {

										add_user_meta( $post_id, $key, $value, true );

										if ( '_id' == substr( $key, -3 ) && ( true === $this->sync_table ) ) {
											$this->wpdb->insert( $this->sync_table_name, array( 'value_type' => 'user', 'value_id' => $post_id, 'meta_key' => $key, 'meta_value' => $value ) );
										}
									}
								}
								break;

							/** New Account-Tag *********************************/

							case 'tags':
								$post_id = wp_set_object_terms( $insert_postmeta['objectid'], $insert_postmeta['name'], 'account-tag', true );
								break;

							/** Year, Account, Record ***************************/

							default:
								$post_id = wp_insert_post( $insert_post );

								if ( is_numeric( $post_id ) ) {

									foreach ( $insert_postmeta as $key => $value ) {

										add_post_meta( $post_id, $key, $value, true );

										// Years need to save their old ID for group year association
										if ( ( 'year' == $to_type ) && ( '_fiscaat_year_id' == $key ) )
											add_post_meta( $post_id, '_fiscaat_old_year_id', $value );

										// Accounts need an extra bit of metadata
										// to be keyed to the new post_id
										if ( ( 'account' == $to_type ) && ( '_fiscaat_account_id' == $key ) ) {

											// Update the live account ID
											update_post_meta( $post_id, $key, $post_id );

											// Save the old account ID
											add_post_meta( $post_id, '_fiscaat_old_account_id', $value );
											if ( '_id' == substr( $key, -3 ) && ( true === $this->sync_table ) ) {
												$this->wpdb->insert( $this->sync_table_name, array( 'value_type' => 'post', 'value_id' => $post_id, 'meta_key' => '_fiscaat_account_id',     'meta_value' => $post_id ) );
												$this->wpdb->insert( $this->sync_table_name, array( 'value_type' => 'post', 'value_id' => $post_id, 'meta_key' => '_fiscaat_old_account_id', 'meta_value' => $value   ) );
											}

										} elseif ( '_id' == substr( $key, -3 ) && ( true === $this->sync_table ) ) {
											$this->wpdb->insert( $this->sync_table_name, array( 'value_type' => 'post', 'value_id' => $post_id, 'meta_key' => $key, 'meta_value' => $value ) );
										}
									}
								}
								break;
						}
						$has_insert = true;
					}
				}
			}
		}

		return ! $has_insert;
	}

	public function convert_year_parents( $start ) {

		$has_update = false;

		if ( ! empty( $this->sync_table ) )
			$query = 'SELECT value_id, meta_value FROM ' . $this->sync_table_name . ' WHERE meta_key = "_fiscaat_year_parent_id" AND meta_value > 0 LIMIT ' . $start . ', ' . $this->max_rows;
		else
			$query = 'SELECT post_id AS value_id, meta_value FROM ' . $this->wpdb->postmeta . ' WHERE meta_key = "_fiscaat_year_parent_id" AND meta_value > 0 LIMIT ' . $start . ', ' . $this->max_rows;

		update_option( '_fiscaat_converter_query', $query );

		$year_array = $this->wpdb->get_results( $query );

		foreach ( (array) $year_array as $row ) {
			$parent_id = $this->callback_yearid( $row->meta_value );
			$this->wpdb->query( 'UPDATE ' . $this->wpdb->posts . ' SET post_parent = "' . $parent_id . '" WHERE ID = "' . $row->value_id . '" LIMIT 1' );
			$has_update = true;
		}

		return ! $has_update;
	}

	/**
	 * This method deletes data from the wp database.
	 */
	public function clean( $start ) {

		$start      = 0;
		$has_delete = false;

		/** Delete bbconverter accounts/years/posts ****************************/

		if ( true === $this->sync_table )
			$query = 'SELECT value_id FROM ' . $this->sync_table_name . ' INNER JOIN ' . $this->wpdb->posts . ' ON(value_id = ID) WHERE meta_key LIKE "_fiscaat_%" AND value_type = "post" GROUP BY value_id ORDER BY value_id DESC LIMIT ' . $this->max_rows;
		else
			$query = 'SELECT post_id AS value_id FROM ' . $this->wpdb->postmeta . ' WHERE meta_key LIKE "_fiscaat_%" GROUP BY post_id ORDER BY post_id DESC LIMIT ' . $this->max_rows;

		update_option( '_fiscaat_converter_query', $query );

		$posts = $this->wpdb->get_results( $query, ARRAY_A );

		if ( isset( $posts[0] ) && ! empty( $posts[0]['value_id'] ) ) {
			foreach ( (array) $posts as $value ) {
				wp_delete_post( $value['value_id'], true );
			}
			$has_delete = true;
		}

		/** Delete bbconverter users ******************************************/

		if ( true === $this->sync_table )
			$query = 'SELECT value_id FROM ' . $this->sync_table_name . ' INNER JOIN ' . $this->wpdb->users . ' ON(value_id = ID) WHERE meta_key = "_fiscaat_user_id" AND value_type = "user" LIMIT ' . $this->max_rows;
		else
			$query = 'SELECT user_id AS value_id FROM ' . $this->wpdb->usermeta . ' WHERE meta_key = "_fiscaat_user_id" LIMIT ' . $this->max_rows;

		update_option( '_fiscaat_converter_query', $query );

		$users = $this->wpdb->get_results( $query, ARRAY_A );

		if ( ! empty( $users ) ) {
			foreach ( $users as $value ) {
				wp_delete_user( $value['value_id'] );
			}
			$has_delete = true;
		}

		unset( $posts );
		unset( $users );

		return ! $has_delete;
	}

	/**
	 * This method deletes passwords from the wp database.
	 *
	 * @param int Start row
	 */
	public function clean_passwords( $start ) {

		$has_delete = false;

		/** Delete bbconverter passwords **************************************/

		$query       = 'SELECT user_id, meta_value FROM ' . $this->wpdb->usermeta . ' WHERE meta_key = "_fiscaat_password" LIMIT ' . $start . ', ' . $this->max_rows;
		update_option( '_fiscaat_converter_query', $query );

		$bbconverter = $this->wpdb->get_results( $query, ARRAY_A );

		if ( ! empty( $bbconverter ) ) {

			foreach ( $bbconverter as $value ) {
				if ( is_serialized( $value['meta_value'] ) ) {
					$this->wpdb->query( 'UPDATE ' . $this->wpdb->users . ' ' . 'SET user_pass = "" ' . 'WHERE ID = "' . $value['user_id'] . '"' );
				} else {
					$this->wpdb->query( 'UPDATE ' . $this->wpdb->users . ' ' . 'SET user_pass = "' . $value['meta_value'] . '" ' . 'WHERE ID = "' . $value['user_id'] . '"' );
					$this->wpdb->query( 'DELETE FROM ' . $this->wpdb->usermeta . ' WHERE meta_key = "_fiscaat_password" AND user_id = "' . $value['user_id'] . '"' );
				}
			}
			$has_delete = true;
		}

		return ! $has_delete;
	}

	/**
	 * This method implements the authentication for the different years.
	 *
	 * @param string Unencoded password.
	 */
	abstract protected function authenticate_pass( $password, $hash );

	/**
	 * Info
	 */
	abstract protected function info();

	/**
	 * This method grabs appropriate fields from the table specified
	 *
	 * @param string The table name to grab fields from
	 */
	private function get_fields( $tablename ) {
		$rval        = array();
		$field_array = $this->wpdb->get_results( 'DESCRIBE ' . $tablename, ARRAY_A );

		foreach ( $field_array as $field ) {
			$rval[] = $field['Field'];
		}

		if ( $tablename == $this->wpdb->users ) {
			$rval[] = 'role';
			$rval[] = 'yim';
			$rval[] = 'aim';
			$rval[] = 'jabber';
		}
		return $rval;
	}

	/** Callbacks *************************************************************/

	/**
	 * Run password through wp_hash_password()
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function callback_pass( $username, $password ) {
		$user = $this->wpdb->get_row( 'SELECT * FROM ' . $this->wpdb->users . ' WHERE user_login = "' . $username . '" AND user_pass = "" LIMIT 1' );
		if ( ! empty( $user ) ) {
			$usermeta = $this->wpdb->get_row( 'SELECT * FROM ' . $this->wpdb->usermeta . ' WHERE meta_key = "_fiscaat_password" AND user_id = "' . $user->ID . '" LIMIT 1' );

			if ( ! empty( $usermeta ) ) {
				if ( $this->authenticate_pass( $password, $usermeta->meta_value ) ) {
					$this->wpdb->query( 'UPDATE ' . $this->wpdb->users . ' ' . 'SET user_pass = "' . wp_hash_password( $password ) . '" ' . 'WHERE ID = "' . $user->ID . '"' );
					$this->wpdb->query( 'DELETE FROM ' . $this->wpdb->usermeta . ' WHERE meta_key = "_fiscaat_password" AND user_id = "' . $user->ID . '"' );
				}
			}
		}
	}

	/**
	 * A mini cache system to reduce database calls to year ID's
	 *
	 * @param string $field
	 * @return string
	 */
	private function callback_yearid( $field ) {
		if ( !isset( $this->map_yearid[$field] ) ) {
			if ( ! empty( $this->sync_table ) ) {
				$row = $this->wpdb->get_row( 'SELECT value_id, meta_value FROM ' . $this->sync_table_name . ' WHERE meta_key = "_fiscaat_year_id" AND meta_value = "' . $field . '" LIMIT 1' );
			} else {
				$row = $this->wpdb->get_row( 'SELECT post_id AS value_id FROM ' . $this->wpdb->postmeta . ' WHERE meta_key = "_fiscaat_year_id" AND meta_value = "' . $field . '" LIMIT 1' );
			}

			if ( !is_null( $row ) ) {
				$this->map_yearid[$field] = $row->value_id;
			} else {
				$this->map_yearid[$field] = 0;
			}
		}
		return $this->map_yearid[$field];
	}

	/**
	 * A mini cache system to reduce database calls to account ID's
	 *
	 * @param string $field
	 * @return string
	 */
	private function callback_accountid( $field ) {
		if ( !isset( $this->map_accountid[$field] ) ) {
			if ( ! empty( $this->sync_table ) ) {
				$row = $this->wpdb->get_row( 'SELECT value_id, meta_value FROM ' . $this->sync_table_name . ' WHERE meta_key = "_fiscaat_old_account_id" AND meta_value = "' . $field . '" LIMIT 1' );
			} else {
				$row = $this->wpdb->get_row( 'SELECT post_id AS value_id FROM ' . $this->wpdb->postmeta . ' WHERE meta_key = "_fiscaat_old_account_id" AND meta_value = "' . $field . '" LIMIT 1' );
			}

			if ( !is_null( $row ) ) {
				$this->map_accountid[$field] = $row->value_id;
			} else {
				$this->map_accountid[$field] = 0;
			}
		}
		return $this->map_accountid[$field];
	}

	/**
	 * A mini cache system to reduce database calls to user ID's
	 *
	 * @param string $field
	 * @return string
	 */
	private function callback_userid( $field ) {
		if ( !isset( $this->map_userid[$field] ) ) {
			if ( ! empty( $this->sync_table ) ) {
				$row = $this->wpdb->get_row( 'SELECT value_id, meta_value FROM ' . $this->sync_table_name . ' WHERE meta_key = "_fiscaat_user_id" AND meta_value = "' . $field . '" LIMIT 1' );
			} else {
				$row = $this->wpdb->get_row( 'SELECT user_id AS value_id FROM ' . $this->wpdb->usermeta . ' WHERE meta_key = "_fiscaat_user_id" AND meta_value = "' . $field . '" LIMIT 1' );
			}

			if ( !is_null( $row ) ) {
				$this->map_userid[$field] = $row->value_id;
			} else {
				if ( ! empty( $_POST['_fiscaat_converter_convert_users'] ) && ( $_POST['_fiscaat_converter_convert_users'] == 1 ) ) {
					$this->map_userid[$field] = 0;
				} else {
					$this->map_userid[$field] = $field;
				}
			}
		}
		return $this->map_userid[$field];
	}

	/**
	 * A mini cache system to reduce database calls map accounts ID's to year ID's
	 *
	 * @param string $field
	 * @return string
	 */
	private function callback_accountid_to_yearid( $field ) {
		$accountid = $this->callback_accountid( $field );
		if ( empty( $accountid ) ) {
			$this->map_accountid_to_yearid[$accountid] = 0;
		} elseif ( ! isset( $this->map_accountid_to_yearid[$accountid] ) ) {
			$row = $this->wpdb->get_row( 'SELECT post_parent FROM ' . $this->wpdb->posts . ' WHERE ID = "' . $accountid . '" LIMIT 1' );

			if ( !is_null( $row ) ) {
				$this->map_accountid_to_yearid[$accountid] = $row->post_parent;
			} else {
				$this->map_accountid_to_yearid[$accountid] = 0;
			}
		}

		return $this->map_accountid_to_yearid[$accountid];
	}

	protected function callback_slug( $field ) {
		retu