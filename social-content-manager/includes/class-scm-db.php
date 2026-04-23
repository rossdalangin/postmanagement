<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCM_DB {

	/**
	 * Create custom database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_categories = $wpdb->prefix . 'scm_categories';
		$table_fb_group   = $wpdb->prefix . 'scm_facebook_groups';
		$table_li_group   = $wpdb->prefix . 'scm_linkedin_groups';
		$table_posts      = $wpdb->prefix . 'scm_content_posts';

		$sql = array();

		$sql[] = "CREATE TABLE $table_categories (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $table_fb_group (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			group_url text NOT NULL,
			post_id bigint(20) NOT NULL,
			category_id bigint(20) DEFAULT 0 NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $table_li_group (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			group_url text NOT NULL,
			post_id bigint(20) NOT NULL,
			category_id bigint(20) DEFAULT 0 NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $table_posts (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			post_content longtext NOT NULL,
			category_id bigint(20) DEFAULT 0 NOT NULL,
			good_for_fb_group tinyint(1) DEFAULT 0 NOT NULL,
			fb_id bigint(20) DEFAULT 0,
			good_for_linkedin_group tinyint(1) DEFAULT 0 NOT NULL,
			linkedin_id bigint(20) DEFAULT 0,
			facebook tinyint(1) DEFAULT 0 NOT NULL,
			linkedin tinyint(1) DEFAULT 0 NOT NULL,
			youtube tinyint(1) DEFAULT 0 NOT NULL,
			tiktok tinyint(1) DEFAULT 0 NOT NULL,
			pinterest tinyint(1) DEFAULT 0 NOT NULL,
			twitter tinyint(1) DEFAULT 0 NOT NULL,
			threads tinyint(1) DEFAULT 0 NOT NULL,
			ig tinyint(1) DEFAULT 0 NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}
}
