<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCM_Admin {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_scm_save_group', array( $this, 'handle_save_group' ) );
		add_action( 'admin_post_scm_save_post', array( $this, 'handle_save_post' ) );
		add_action( 'admin_init', array( $this, 'handle_bulk_actions' ) );
		add_action( 'admin_init', array( $this, 'handle_delete_actions' ) );
	}

	public function register_menus() {
		add_menu_page(
			__( 'Social Content Manager', 'social-content-manager' ),
			__( 'Social Content', 'social-content-manager' ),
			'manage_options',
			'scm-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-share',
			30
		);

		add_submenu_page(
			'scm-dashboard',
			__( 'Posting Dashboard', 'social-content-manager' ),
			__( 'Dashboard', 'social-content-manager' ),
			'manage_options',
			'scm-dashboard',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'scm-dashboard',
			__( 'Facebook Groups', 'social-content-manager' ),
			__( 'Facebook Groups', 'social-content-manager' ),
			'manage_options',
			'scm-facebook-groups',
			array( $this, 'render_facebook_groups' )
		);

		add_submenu_page(
			'scm-dashboard',
			__( 'LinkedIn Groups', 'social-content-manager' ),
			__( 'LinkedIn Groups', 'social-content-manager' ),
			'manage_options',
			'scm-linkedin-groups',
			array( $this, 'render_linkedin_groups' )
		);

		add_submenu_page(
			'scm-dashboard',
			__( 'Content Posts', 'social-content-manager' ),
			__( 'Content Posts', 'social-content-manager' ),
			'manage_options',
			'scm-content-posts',
			array( $this, 'render_content_posts' )
		);

		add_submenu_page(
			'scm-dashboard',
			__( 'Import / Export', 'social-content-manager' ),
			__( 'Import / Export', 'social-content-manager' ),
			'manage_options',
			'scm-import-export',
			array( $this, 'render_import_export' )
		);
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'scm-' ) === false ) {
			return;
		}

		wp_enqueue_style( 'scm-admin-style', SCM_PLUGIN_URL . 'assets/css/admin.css', array(), SCM_VERSION );
		wp_enqueue_script( 'scm-dashboard-js', SCM_PLUGIN_URL . 'assets/js/dashboard.js', array( 'jquery' ), SCM_VERSION, true );

		wp_localize_script( 'scm-dashboard-js', 'scm_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'scm_nonce' ),
		) );
	}

	public function render_dashboard() {
		include SCM_PLUGIN_DIR . 'templates/dashboard.php';
	}

	public function render_facebook_groups() {
		include SCM_PLUGIN_DIR . 'templates/admin-facebook-groups.php';
	}

	public function render_linkedin_groups() {
		include SCM_PLUGIN_DIR . 'templates/admin-linkedin-groups.php';
	}

	public function render_content_posts() {
		include SCM_PLUGIN_DIR . 'templates/admin-content-posts.php';
	}

	public function render_import_export() {
		include SCM_PLUGIN_DIR . 'templates/import-export.php';
	}

	public function handle_save_group() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		check_admin_referer( 'scm_save_group_nonce' );

		global $wpdb;
		$type = sanitize_text_field( $_POST['group_type'] );
		$table_name = $wpdb->prefix . ( $type === 'fb' ? 'scm_facebook_groups' : 'scm_linkedin_groups' );
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		$data = array(
			'group_url' => esc_url_raw( $_POST['group_url'] ),
			'post_id'   => intval( $_POST['post_id'] ),
		);

		if ( $id ) {
			$wpdb->update( $table_name, $data, array( 'id' => $id ) );
		} else {
			$wpdb->insert( $table_name, $data );
		}

		$redirect_to = admin_url( 'admin.php?page=scm-' . ( $type === 'fb' ? 'facebook' : 'linkedin' ) . '-groups' );
		wp_redirect( $redirect_to );
		exit;
	}

	public function handle_save_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		check_admin_referer( 'scm_save_post_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'scm_content_posts';
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		$data = array(
			'post_content'            => wp_kses_post( $_POST['post_content'] ),
			'good_for_fb_group'       => isset( $_POST['good_for_fb_group'] ) ? 1 : 0,
			'good_for_linkedin_group' => isset( $_POST['good_for_linkedin_group'] ) ? 1 : 0,
			'facebook'                => isset( $_POST['facebook'] ) ? 1 : 0,
			'linkedin'                => isset( $_POST['linkedin'] ) ? 1 : 0,
			'youtube'                 => isset( $_POST['youtube'] ) ? 1 : 0,
			'tiktok'                  => isset( $_POST['tiktok'] ) ? 1 : 0,
			'pinterest'               => isset( $_POST['pinterest'] ) ? 1 : 0,
			'twitter'                 => isset( $_POST['twitter'] ) ? 1 : 0,
			'threads'                 => isset( $_POST['threads'] ) ? 1 : 0,
			'ig'                      => isset( $_POST['ig'] ) ? 1 : 0,
			'created_at'              => current_time( 'mysql' ),
		);

		if ( $id ) {
			unset( $data['created_at'] ); // Don't update creation time on edit
			$wpdb->update( $table_name, $data, array( 'id' => $id ) );
		} else {
			$wpdb->insert( $table_name, $data );
		}

		wp_redirect( admin_url( 'admin.php?page=scm-content-posts' ) );
		exit;
	}

	public function handle_bulk_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = '';
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== -1 ) {
			$action = $_REQUEST['action'];
		} elseif ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] !== -1 ) {
			$action = $_REQUEST['action2'];
		}

		if ( ! in_array( $action, array( 'bulk-delete', 'bulk-mark-used' ) ) ) {
			return;
		}

		$ids = isset( $_REQUEST['bulk-delete'] ) ? array_map( 'intval', $_REQUEST['bulk-delete'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$table_name = '';
		if ( $page === 'scm-facebook-groups' ) $table_name = $wpdb->prefix . 'scm_facebook_groups';
		elseif ( $page === 'scm-linkedin-groups' ) $table_name = $wpdb->prefix . 'scm_linkedin_groups';
		elseif ( $page === 'scm-content-posts' ) $table_name = $wpdb->prefix . 'scm_content_posts';

		if ( ! $table_name ) {
			return;
		}

		if ( $action === 'bulk-delete' ) {
			$ids_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id IN ($ids_placeholders)", $ids ) );
		} elseif ( $action === 'bulk-mark-used' && $page === 'scm-content-posts' ) {
			$ids_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare(
				"UPDATE $table_name SET facebook=1, linkedin=1, youtube=1, tiktok=1, pinterest=1, twitter=1, threads=1, ig=1 WHERE id IN ($ids_placeholders)",
				$ids
			) );
		}

		wp_redirect( remove_query_arg( array( 'action', 'action2', 'bulk-delete', '_wpnonce', '_wp_http_referer' ) ) );
		exit;
	}

	public function handle_delete_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		if ( $action === 'delete' && $id ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
			$nonce_action = ( strpos( $page, 'posts' ) !== false ) ? 'scm_delete_post_' . $id : 'scm_delete_group_' . $id;

			if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
				wp_die( 'Security check failed' );
			}

			global $wpdb;
			$table_name = '';
			if ( $page === 'scm-facebook-groups' ) $table_name = $wpdb->prefix . 'scm_facebook_groups';
			elseif ( $page === 'scm-linkedin-groups' ) $table_name = $wpdb->prefix . 'scm_linkedin_groups';
			elseif ( $page === 'scm-content-posts' ) $table_name = $wpdb->prefix . 'scm_content_posts';

			if ( $table_name ) {
				$wpdb->delete( $table_name, array( 'id' => $id ) );
				wp_redirect( remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) );
				exit;
			}
		}
	}
}
