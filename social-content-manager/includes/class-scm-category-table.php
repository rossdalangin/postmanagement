<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SCM_Category_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'category',
			'plural'   => 'categories',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'   => '<input type="checkbox" />',
			'name' => __( 'Name', 'social-content-manager' ),
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

	public function column_name( $item ) {
		$delete_nonce = wp_create_nonce( 'scm_delete_category_' . $item['id'] );
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id'], $delete_nonce ),
		);

		return sprintf( '%1$s %2$s', esc_html( $item['name'] ), $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		return array(
			'bulk-delete' => 'Delete',
		);
	}

	public function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scm_categories';

		$per_page = 20;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array(
			'name' => array( 'name', true ),
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_sql_orderby( $_GET['orderby'] ) : 'name';
		$order   = ( ! empty( $_GET['order'] ) && strtoupper( $_GET['order'] ) === 'DESC' ) ? 'DESC' : 'ASC';

		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$where = '';
		if ( ! empty( $search ) ) {
			$where = $wpdb->prepare( " WHERE name LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
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
