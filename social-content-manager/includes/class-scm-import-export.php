<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCM_Import_Export {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_post_scm_export_csv', array( $this, 'handle_export' ) );
		add_action( 'admin_post_scm_import_csv', array( $this, 'handle_import' ) );
	}

	public function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		check_admin_referer( 'scm_export_nonce' );

		$type = sanitize_text_field( $_POST['export_type'] );
		$format = isset( $_POST['export_format'] ) ? sanitize_text_field( $_POST['export_format'] ) : 'csv';

		global $wpdb;
		$table_name = $wpdb->prefix . 'scm_' . $type;

		$results = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );

		if ( empty( $results ) ) {
			wp_redirect( admin_url( 'admin.php?page=scm-import-export&error=no_data' ) );
			exit;
		}

		if ( $format === 'excel' ) {
			header( 'Content-Type: application/vnd.ms-excel' );
			header( 'Content-Disposition: attachment; filename=' . $type . '-' . date( 'Y-m-d' ) . '.xls' );

			echo '<table border="1">';
			echo '<tr>';
			foreach ( array_keys( $results[0] ) as $header ) {
				echo '<th>' . esc_html( $header ) . '</th>';
			}
			echo '</tr>';
			foreach ( $results as $row ) {
				echo '<tr>';
				foreach ( $row as $cell ) {
					echo '<td>' . esc_html( $cell ) . '</td>';
				}
				echo '</tr>';
			}
			echo '</table>';
		} else {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $type . '-' . date( 'Y-m-d' ) . '.csv' );

			$output = fopen( 'php://output', 'w' );
			fputcsv( $output, array_keys( $results[0] ) );

			foreach ( $results as $row ) {
				fputcsv( $output, $row );
			}

			fclose( $output );
		}
		exit;
	}

	public function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		check_admin_referer( 'scm_import_nonce' );

		if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_redirect( admin_url( 'admin.php?page=scm-import-export&error=upload' ) );
			exit;
		}

		$type = sanitize_text_field( $_POST['import_type'] );
		$file = $_FILES['import_file']['tmp_name'];

		global $wpdb;
		$table_name = $wpdb->prefix . 'scm_' . $type;

		$handle = fopen( $file, 'r' );
		$headers = fgetcsv( $handle );

		$imported = 0;
		$skipped = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$data = array_combine( $headers, $row );

			if ( $type === 'facebook_groups' || $type === 'linkedin_groups' ) {
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE group_url = %s", $data['group_url'] ) );
				if ( $exists ) {
					$skipped++;
					continue;
				}
				$wpdb->insert( $table_name, array(
					'group_url' => esc_url_raw( $data['group_url'] ),
					'post_id'   => intval( $data['post_id'] ),
				) );
			} elseif ( $type === 'content_posts' ) {
				$post_content = wp_kses_post( $data['post_content'] );
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE post_content = %s", $post_content ) );
				if ( $exists ) {
					$skipped++;
					continue;
				}
				$wpdb->insert( $table_name, array(
					'post_content'            => $post_content,
					'good_for_fb_group'       => intval( $data['good_for_fb_group'] ),
					'good_for_linkedin_group' => intval( $data['good_for_linkedin_group'] ),
					'facebook'                => intval( $data['facebook'] ),
					'linkedin'                => intval( $data['linkedin'] ),
					'youtube'                 => intval( $data['youtube'] ),
					'tiktok'                  => intval( $data['tiktok'] ),
					'pinterest'               => intval( $data['pinterest'] ),
					'twitter'                 => intval( $data['twitter'] ),
					'threads'                 => intval( $data['threads'] ),
					'ig'                      => intval( $data['ig'] ),
					'created_at'              => current_time( 'mysql' ),
				) );
			}
			$imported++;
		}
		fclose( $handle );

		wp_redirect( admin_url( 'admin.php?page=scm-import-export&imported=' . $imported . '&skipped=' . $skipped ) );
		exit;
	}
}
