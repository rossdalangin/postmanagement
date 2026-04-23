<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SCM_LI_Group_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'linkedin_group',
			'plural'   => 'linkedin_groups',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'        => '<input type="checkbox" />',
			'group_url' => __( 'Group URL', 'social-content-manager' ),
			'category'  => __( 'Category', 'social-content-manager' ),
			'post_id'   => __( 'Post ID', 'social-content-manager' ),
		);
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'group_url':
			case 'post_id':
			case 'category':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	public function column_group_url( $item ) {
		$delete_nonce = wp_create_nonce( 'scm_delete_group_' . $item['id'] );
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id'], $delete_nonce ),
		);

		return sprintf( '%1$s %2$s', esc_url( $item['group_url'] ), $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		return array(
			'bulk-delete' => 'Delete',
		);
	}

	public function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scm_linkedin_groups';

		$per_page = 20;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$where = '';
		if ( ! empty( $search ) ) {
			$where = $wpdb->prepare( " WHERE group_url LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name $where" );

		$table_cat = $wpdb->prefix . 'scm_categories';
		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.*, c.name as category
				 FROM $table_name t
				 LEFT JOIN $table_cat c ON t.category_id = c.id
				 $where LIMIT %d OFFSET %d",
				$per_page, $offset
			),
			ARRAY_A
		);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}
