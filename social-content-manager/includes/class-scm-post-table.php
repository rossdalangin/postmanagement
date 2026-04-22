<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SCM_Post_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'content_post',
			'plural'   => 'content_posts',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'post_content' => __( 'Content', 'social-content-manager' ),
			'platforms'    => __( 'Platforms/Targets', 'social-content-manager' ),
			'created_at'   => __( 'Date', 'social-content-manager' ),
		);
	}

	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	public function column_post_content( $item ) {
		$delete_nonce = wp_create_nonce( 'scm_delete_post_' . $item['id'] );
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id'], $delete_nonce ),
		);

		$content = wp_trim_words( $item['post_content'], 10 );
		return sprintf( '%1$s %2$s', esc_html( $content ), $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		return array(
			'bulk-delete'    => 'Delete',
			'bulk-mark-used' => 'Mark as Used (All platforms)',
		);
	}

	public function column_platforms( $item ) {
		$platforms = array();
		if ( $item['good_for_fb_group'] ) $platforms[] = 'FB Group';
		if ( $item['good_for_linkedin_group'] ) $platforms[] = 'LI Group';
		if ( $item['facebook'] ) $platforms[] = 'Facebook';
		if ( $item['linkedin'] ) $platforms[] = 'LinkedIn';
		if ( $item['youtube'] ) $platforms[] = 'YouTube';
		if ( $item['tiktok'] ) $platforms[] = 'TikTok';
		if ( $item['pinterest'] ) $platforms[] = 'Pinterest';
		if ( $item['twitter'] ) $platforms[] = 'Twitter';
		if ( $item['threads'] ) $platforms[] = 'Threads';
		if ( $item['ig'] ) $platforms[] = 'IG';

		return implode( ', ', $platforms );
	}

	public function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scm_content_posts';

		$per_page = 20;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array(
			'created_at' => array( 'created_at', true ),
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'created_at';
		$order   = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'DESC';

		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$where = '';
		if ( ! empty( $search ) ) {
			$where = $wpdb->prepare( " WHERE post_content LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name $where" );

		$this->items = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_name $where ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset ),
			ARRAY_A
		);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}
