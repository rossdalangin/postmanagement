<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCM_AJAX {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_scm_get_content', array( $this, 'get_content' ) );
		add_action( 'wp_ajax_scm_mark_used', array( $this, 'mark_used' ) );
		add_action( 'wp_ajax_scm_get_stats', array( $this, 'get_stats' ) );
		add_action( 'wp_ajax_scm_reset_usage', array( $this, 'reset_usage' ) );
	}

	public function get_content() {
		check_ajax_referer( 'scm_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		global $wpdb;
		$platform = sanitize_text_field( $_POST['platform'] );
		$table_posts = $wpdb->prefix . 'scm_content_posts';

		$response = array();

		if ( $platform === 'fb_group' ) {
			$group_table = $wpdb->prefix . 'scm_facebook_groups';
			$group = $wpdb->get_row( "SELECT * FROM $group_table ORDER BY RAND() LIMIT 1" );

			if ( ! $group ) {
				wp_send_json_error( 'No Facebook groups found.' );
			}

			$post = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_posts WHERE good_for_fb_group = 1 AND (fb_id IS NULL OR fb_id = 0) ORDER BY RAND() LIMIT 1"
			) );

			if ( ! $post ) {
				wp_send_json_error( 'No unused content found for this group.' );
			}

			$response = array(
				'group_url'    => $group->group_url,
				'group_post_id' => $group->post_id,
				'post_id'      => $post->id,
				'content'      => $post->post_content,
			);

		} elseif ( $platform === 'li_group' ) {
			$group_table = $wpdb->prefix . 'scm_linkedin_groups';
			$group = $wpdb->get_row( "SELECT * FROM $group_table ORDER BY RAND() LIMIT 1" );

			if ( ! $group ) {
				wp_send_json_error( 'No LinkedIn groups found.' );
			}

			$post = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_posts WHERE good_for_linkedin_group = 1 AND (linkedin_id IS NULL OR linkedin_id = 0) ORDER BY RAND() LIMIT 1"
			) );

			if ( ! $post ) {
				wp_send_json_error( 'No unused content found for this group.' );
			}

			$response = array(
				'group_url'    => $group->group_url,
				'group_post_id' => $group->post_id,
				'post_id'      => $post->id,
				'content'      => $post->post_content,
			);

		} else {
			// Other platforms
			$valid_platforms = array( 'facebook', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'twitter', 'threads', 'ig' );
			if ( ! in_array( $platform, $valid_platforms ) ) {
				wp_send_json_error( 'Invalid platform.' );
			}

			$post = $wpdb->get_row( "SELECT * FROM $table_posts WHERE $platform = 0 ORDER BY RAND() LIMIT 1" );

			if ( ! $post ) {
				wp_send_json_error( 'No unused content found for this platform.' );
			}

			$response = array(
				'post_id' => $post->id,
				'content' => $post->post_content,
			);
		}

		wp_send_json_success( $response );
	}

	public function get_stats() {
		check_ajax_referer( 'scm_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		global $wpdb;
		$table_posts = $wpdb->prefix . 'scm_content_posts';

		$stats = array(
			'fb_group_unused' => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE good_for_fb_group = 1 AND (fb_id IS NULL OR fb_id = 0)" ),
			'li_group_unused' => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE good_for_linkedin_group = 1 AND (linkedin_id IS NULL OR linkedin_id = 0)" ),
			'facebook'        => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE facebook = 1" ),
			'linkedin'        => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE linkedin = 1" ),
			'youtube'         => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE youtube = 1" ),
			'tiktok'          => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE tiktok = 1" ),
			'pinterest'       => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE pinterest = 1" ),
			'twitter'         => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE twitter = 1" ),
			'threads'         => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE threads = 1" ),
			'ig'              => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts WHERE ig = 1" ),
			'total_posts'     => $wpdb->get_var( "SELECT COUNT(id) FROM $table_posts" ),
		);

		wp_send_json_success( $stats );
	}

	public function reset_usage() {
		check_ajax_referer( 'scm_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		global $wpdb;
		$table_posts = $wpdb->prefix . 'scm_content_posts';

		$wpdb->query( "UPDATE $table_posts SET fb_id = 0, linkedin_id = 0, facebook = 0, linkedin = 0, youtube = 0, tiktok = 0, pinterest = 0, twitter = 0, threads = 0, ig = 0" );

		wp_send_json_success();
	}

	public function mark_used() {
		check_ajax_referer( 'scm_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		global $wpdb;
		$platform = sanitize_text_field( $_POST['platform'] );
		$post_id  = intval( $_POST['post_id'] );
		$table_posts = $wpdb->prefix . 'scm_content_posts';

		if ( $platform === 'fb_group' ) {
			$group_post_id = intval( $_POST['group_post_id'] );
			$wpdb->update( $table_posts, array( 'fb_id' => $group_post_id ), array( 'id' => $post_id ) );
		} elseif ( $platform === 'li_group' ) {
			$group_post_id = intval( $_POST['group_post_id'] );
			$wpdb->update( $table_posts, array( 'linkedin_id' => $group_post_id ), array( 'id' => $post_id ) );
		} else {
			$valid_platforms = array( 'facebook', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'twitter', 'threads', 'ig' );
			if ( in_array( $platform, $valid_platforms ) ) {
				$wpdb->update( $table_posts, array( $platform => 1 ), array( 'id' => $post_id ) );
			}
		}

		wp_send_json_success();
	}
}
