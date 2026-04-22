<?php
/**
 * Plugin Name: Social Content Distribution Manager
 * Description: A plugin to manage social media groups and content distribution.
 * Version: 1.0.0
 * Author: Jules
 * Text Domain: social-content-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'SCM_VERSION', '1.0.0' );
define( 'SCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class Social_Content_Manager {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once SCM_PLUGIN_DIR . 'includes/class-scm-db.php';
		require_once SCM_PLUGIN_DIR . 'includes/class-scm-admin.php';
		require_once SCM_PLUGIN_DIR . 'includes/class-scm-ajax.php';
		require_once SCM_PLUGIN_DIR . 'includes/class-scm-import-export.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'SCM_DB', 'create_tables' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Init the plugin classes.
	 */
	public function init() {
		if ( is_admin() ) {
			SCM_Admin::get_instance();
			SCM_Import_Export::get_instance();
		}
		SCM_AJAX::get_instance();
	}
}

// Start the plugin
Social_Content_Manager::get_instance();
