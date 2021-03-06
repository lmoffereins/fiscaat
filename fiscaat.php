<?php

/**
 * The Fiscaat Plugin
 *
 * @package Fiscaat
 * @subpackage Main
 *
 * @todo Sync with latest bbPress
 * @todo Create Fiscaat comment system
 * @todo Fix Control
 */

/**
 * Plugin Name:       Fiscaat
 * Description:       Fiscaat is accounting software the Wordpress way
 * Plugin URI:        https://github.com/lmoffereins/fiscaat/
 * Author:            Laurens Offereins
 * Author URI:        https://github.com/lmoffereins/
 * Version:           0.0.8
 * Text Domain:       fiscaat
 * Domain Path:       /languages/
 * GitHub Plugin URI: lmoffereins/fiscaat
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'Fiscaat' ) ) :
/**
 * Main Fiscaat Class
 *
 * @since 0.0.1
 */
final class Fiscaat {

	/** Magic *****************************************************************/

	/**
	 * Fiscaat uses many variables, most of which can be filtered to customize
	 * the way that it works. To prevent unauthorized access, these variables
	 * are stored in a private array that is magically updated using PHP 5.2+
	 * methods. This is to prevent third party plugins from tampering with
	 * essential information indirectly, which would cause issues later.
	 *
	 * @see Fiscaat::setup_globals()
	 * @var array
	 */
	private $data;

	/** Not Magic *************************************************************/

	/**
	 * @var obj Add-ons append to this
	 */
	public $extend;

	/**
	 * @var array Overloads get_option()
	 */
	public $options = array();

	/**
	 * @var array Overloads get_user_meta()
	 */
	public $user_options = array();

	/** Singleton *************************************************************/

	/**
	 * Main Fiscaat Instance
	 *
	 * Insures that only one instance of Fiscaat exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @static object $instance
	 * @uses Fiscaat::setup_globals() Setup the globals needed
	 * @uses Fiscaat::includes() Include the required files
	 * @uses Fiscaat::setup_actions() Setup the hooks and actions
	 * @see fiscaat()
	 * @return The one true Fiscaat
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new Fiscaat;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		// Always return the instance
		return $instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent Fiscaat from being loaded more than once.
	 *
	 * @see Fiscaat::instance()
	 * @see fiscaat();
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent Fiscaat from being cloned
	 *
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'fiscaat' ), '0.0.1' ); }

	/**
	 * A dummy magic method to prevent Fiscaat from being unserialized
	 *
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'fiscaat' ), '0.0.1' ); }

	/**
	 * Magic method for checking the existence of a certain custom field
	 *
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting Fiscaat variables
	 *
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting Fiscaat variables
	 *
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method to prevent notices and errors from invalid method calls
	 *
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods *******************************************************/

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @access private
	 * @uses plugin_dir_path() To generate Fiscaat plugin path
	 * @uses plugin_dir_url() To generate Fiscaat plugin url
	 * @uses apply_filters() Calls various filters
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version       = '0.0.8';
		$this->db_version    = '008';

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file          = __FILE__;
		$this->basename      = apply_filters( 'fct_plugin_basename', plugin_basename( $this->file ) );
		$this->plugin_dir    = apply_filters( 'fct_plugin_dir_path', plugin_dir_path( $this->file ) );
		$this->plugin_url    = apply_filters( 'fct_plugin_dir_url',  plugin_dir_url ( $this->file ) );

		// Includes
		$this->includes_dir  = apply_filters( 'fct_includes_dir',  trailingslashit( $this->plugin_dir . 'includes'  ) );
		$this->includes_url  = apply_filters( 'fct_includes_url',  trailingslashit( $this->plugin_url . 'includes'  ) );

		// Templates
		$this->templates_dir = apply_filters( 'fct_templates_dir', trailingslashit( $this->plugin_dir . 'templates' ) );
		$this->templates_url = apply_filters( 'fct_templates_url', trailingslashit( $this->plugin_url . 'templates' ) );

		// Languages
		$this->lang_dir      = apply_filters( 'fct_lang_dir',      trailingslashit( $this->plugin_dir . 'languages' ) );

		/** Identifiers *******************************************************/

		// Post type identifiers
		$this->record_post_type    = apply_filters( 'fct_record_post_type',  'fct_record'  );
		$this->account_post_type   = apply_filters( 'fct_account_post_type', 'fct_account' );
		$this->period_post_type    = apply_filters( 'fct_period_post_type',  'fct_period'  );

		// Status identifiers
		$this->public_status_id    = apply_filters( 'fct_public_post_status',   'publish'  );
		$this->closed_status_id    = apply_filters( 'fct_closed_post_status',   'closed'   );
		$this->trash_status_id     = apply_filters( 'fct_trash_post_status',    'trash'    );

		// Account type identifiers
		$this->revenue_type_id     = apply_filters( 'fct_revenue_acccount_type', 'revenue' );
		$this->capital_type_id     = apply_filters( 'fct_capital_acccount_type', 'capital' );

		// Record type identifiers
		$this->debit_type_id       = apply_filters( 'fct_debit_record_type',    'debit'    );
		$this->credit_type_id      = apply_filters( 'fct_credit_record_type',   'credit'   );

		// Other identifiers
		$this->rcrd_id             = apply_filters( 'fct_rcrd_id',  'fct_rcrd' );
		$this->acnt_id             = apply_filters( 'fct_acnt_id',  'fct_acnt' );
		$this->prid_id             = apply_filters( 'fct_prid_id',  'fct_prid' );
		$this->edit_id             = apply_filters( 'fct_edit_id',  'edit'     );
		$this->paged_id            = apply_filters( 'fct_paged_id', 'paged'    );

		/** Queries ***********************************************************/

		$this->current_record_id     = 0; // Current record id
		$this->current_account_id    = 0; // Current account id
		$this->current_period_id     = 0; // Current period id
		$this->the_current_period_id = 0; // The actual current period id

		$this->record_query          = new WP_Query(); // Main record query
		$this->account_query         = new WP_Query(); // Main account query
		$this->period_query          = new WP_Query(); // Main period query

		/** Misc **************************************************************/

		$this->domain                = 'fiscaat';        // Unique identifier for retrieving translated strings
		$this->currency              = '';               // Currency iso code
		$this->extend                = new stdClass();   // Plugins add data here
		$this->errors                = new WP_Error();   // Feedback

		/** Cache *************************************************************/

		// Add Fiscaat to global cache groups
		wp_cache_add_global_groups( 'fiscaat' );
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @uses is_admin() If in WordPress admin, load additional file
	 */
	private function includes() {

		/** Core **************************************************************/

		require( $this->includes_dir . 'core/sub-actions.php'      );
		require( $this->includes_dir . 'core/functions.php'        );
		require( $this->includes_dir . 'core/options.php'          );
		require( $this->includes_dir . 'core/capabilities.php'     );
		require( $this->includes_dir . 'core/update.php'           );

		/** Components ********************************************************/

		// Common
		require( $this->includes_dir . 'common/classes.php'        );
		require( $this->includes_dir . 'common/functions.php'      );
		require( $this->includes_dir . 'common/template.php'       );

		// Records
		require( $this->includes_dir . 'records/capabilities.php'  );
		require( $this->includes_dir . 'records/functions.php'     );
		require( $this->includes_dir . 'records/template.php'      );

		// Accounts
		require( $this->includes_dir . 'accounts/capabilities.php' );
		require( $this->includes_dir . 'accounts/functions.php'    );
		require( $this->includes_dir . 'accounts/template.php'     );

		// Periods
		require( $this->includes_dir . 'periods/capabilities.php'  );
		require( $this->includes_dir . 'periods/functions.php'     );
		require( $this->includes_dir . 'periods/template.php'      );

		// Users
		require( $this->includes_dir . 'users/capabilities.php'    );
		require( $this->includes_dir . 'users/functions.php'       );
		require( $this->includes_dir . 'users/template.php'        );
		require( $this->includes_dir . 'users/options.php'         );

		// Control
		require( $this->includes_dir . 'control/control.php'       );

		/** Hooks *************************************************************/

		require( $this->includes_dir . 'core/actions.php' );
		require( $this->includes_dir . 'core/filters.php' );

		/** Admin *************************************************************/

		// Quick admin check
		if ( is_admin() ){
			require( $this->includes_dir . 'admin/admin.php'   );
			require( $this->includes_dir . 'admin/actions.php' );
		}
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @access private
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'fct_activation'   );
		add_action( 'deactivate_' . $this->basename, 'fct_deactivation' );

		// If Fiscaat is being deactivated, do not add any actions
		if ( fct_is_deactivation( $this->basename ) )
			return;

		// Array of Fiscaat core actions
		$actions = array(
			'register_post_types',    // Register post types (record|account|period)
			'register_post_statuses', // Register post statuses (closed)
			'load_textdomain',        // Load textdomain (fiscaat)
			'add_rewrite_tags',       // Add rewrite tags (edit)
			'add_rewrite_rules',      // Add rewrite rules (edit)
		);

		// Add the actions
		foreach( $actions as $class_action ) {
			add_action( 'fct_' . $class_action, array( $this, $class_action ), 5 );
		}

		// All Fiscaat actions are setup (includes fiscaat-core-hooks.php)
		do_action_ref_array( 'fct_after_setup_actions', array( &$this ) );
	}

	/** Public Methods ********************************************************/

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the Fiscaat plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the Fiscaat plugin folder
	 * will be removed on Fiscaat updates. If you're creating custom
	 * translation files, please use the global language folder.
	 *
	 * @uses apply_filters() Calls 'fct_locale' with the
	 *                        {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @return bool True on success, false on failure
	 */
	public function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/fiscaat/' . $mofile;

		// Look in global /wp-content/languages/fiscaat folder
		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/fiscaat/fiscaat-languages/ folder
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( $this->domain, $mofile_local );
		}

		// Nothing found
		return false;
	}

	/**
	 * Setup the post types for records, accounts and periods
	 *
	 * @uses register_post_type() To register the post types
	 * @uses apply_filters() Calls various filters to modify the arguments
	 *                        sent to register_post_type()
	 */
	public static function register_post_types() {

		/** Records *******************************************************/

		register_post_type(
			fct_get_record_post_type(),
			apply_filters( 'fct_register_record_post_type', array(
				'labels'              => fct_get_record_post_type_labels(),
				'rewrite'             => fct_get_record_post_type_rewrite(),
				'supports'            => fct_get_record_post_type_supports(),
				'description'         => __( 'Fiscaat Records', 'fiscaat' ),
				'capabilities'        => fct_get_record_caps(),
				'capability_type'     => array( 'record', 'records' ),
				'menu_position'       => 333333,
				'has_archive'         => fct_get_root_slug(),
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'public'              => current_user_can( 'fct_spectate' ),
				'can_export'          => false,
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => ''
			) )
		);

		/** Accounts ******************************************************/

		register_post_type(
			fct_get_account_post_type(),
			apply_filters( 'fct_register_account_post_type', array(
				'labels'              => fct_get_account_post_type_labels(),
				'rewrite'             => fct_get_account_post_type_rewrite(),
				'supports'            => fct_get_account_post_type_supports(),
				'description'         => __( 'Fiscaat Accounts', 'fiscaat' ),
				'capabilities'        => fct_get_account_caps(),
				'capability_type'     => array( 'account', 'accounts' ),
				'menu_position'       => 333333,
				'has_archive'         => fct_get_root_slug(),
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'public'              => current_user_can( 'fct_spectate' ),
				'can_export'          => false,
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => ''
			) )
		);

		/** Periods *********************************************************/

		register_post_type(
			fct_get_period_post_type(),
			apply_filters( 'fct_register_period_post_type', array(
				'labels'              => fct_get_period_post_type_labels(),
				'rewrite'             => fct_get_period_post_type_rewrite(),
				'supports'            => fct_get_period_post_type_supports(),
				'description'         => __( 'Fiscaat Periods', 'fiscaat' ),
				'capabilities'        => fct_get_period_caps(),
				'capability_type'     => array( 'period', 'periods' ),
				'menu_position'       => 333333,
				'has_archive'         => fct_get_root_slug(),
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'public'              => current_user_can( 'fct_spectate' ),
				'can_export'          => false,
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => ''
			) )
		);
	}

	/**
	 * Register the post statuses used by Fiscaat
	 *
	 * @uses register_post_status() To register post statuses
	 * @uses fc_get_closed_status_id()
	 * @uses apply_filters() Calls 'fct_register_closed_post_status' with the
	 *                        post status arguments
	 */
	public static function register_post_statuses() {

		// Closed
		register_post_status(
			fct_get_closed_status_id(),
			apply_filters( 'fct_register_closed_post_status', array(
				'label'                     => _x( 'Closed', 'post', 'fiscaat' ),
				'label_count'               => _nx_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'post', 'fiscaat' ),
				'public'                    => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true
			) )
		);
	}

	/** Custom Rewrite Rules **************************************************/

	/**
	 * Add the Fiscaat-specific rewrite tags
	 *
	 * @uses add_rewrite_tag() To add the rewrite tags
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%' . fct_get_edit_rewrite_id() . '%', '([1]{1,})' ); // Edit Page tag
	}

	/**
	 * Register Fiscaat-specific rewrite rules for uri's that are not
	 * setup for us by way of custom post types. This includes:
	 * - Front-end editing
	 *
	 */
	public static function add_rewrite_rules() {

		/** Setup *************************************************************/

		// Add rules to top or bottom?
		$priority     = 'top';

		// Single Slugs
		$period_slug  = fct_get_period_slug();
		$account_slug = fct_get_account_slug();
		$record_slug  = fct_get_record_slug();

		// Secondary Slugs
		$edit_slug    = 'edit';

		// Unique rewrite ID's
		$edit_id      = fct_get_edit_rewrite_id();

		/** Add ***************************************************************/

		// Rewrite rule matches used repeatedly below
		$edit_rule    = '/([^/]+)/' . $edit_slug  . '/?$';

		// New Fiscaat specific rules to merge with existing that are not
		// handled automatically by custom post types or taxonomy types
		add_rewrite_rule( $period_slug  . $edit_rule, 'index.php?' . fct_get_period_post_type()  . '=$matches[1]&' . $edit_id . '=1', $priority );
		add_rewrite_rule( $account_slug . $edit_rule, 'index.php?' . fct_get_account_post_type() . '=$matches[1]&' . $edit_id . '=1', $priority );
		add_rewrite_rule( $record_slug  . $edit_rule, 'index.php?' . fct_get_record_post_type()  . '=$matches[1]&' . $edit_id . '=1', $priority );
	}
}

/**
 * The main function responsible for returning the one true Fiscaat Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $fiscaat = fiscaat(); ?>
 *
 * @return The one true Fiscaat Instance
 */
function fiscaat() {
	return Fiscaat::instance();
}

// Fire it up
fiscaat();

endif; // class_exists check

