<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$group = null;
if ( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . ( $type === 'fb' ? 'scm_facebook_groups' : 'scm_linkedin_groups' );
    $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
}

global $wpdb;
$categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}scm_categories ORDER BY name ASC" );
?>
<div class="wrap">
    <h1><?php echo $id ? 'Edit' : 'Add New'; ?> <?php echo $type === 'fb' ? 'Facebook' : 'LinkedIn'; ?> Group</h1>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="scm_save_group">
        <input type="hidden" name="group_type" value="<?php echo esc_attr( $type ); ?>">
        <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
        <?php wp_nonce_field( 'scm_save_group_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="category_id">Category</label></th>
                <td>
                    <select name="category_id" id="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->id ); ?>" <?php if ( $group && $group->category_id == $cat->id ) echo 'selected'; ?>><?php echo esc_html( $cat->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="group_url">Group URL</label></th>
                <td><input name="group_url" type="url" id="group_url" value="<?php echo $group ? esc_url( $group->group_url ) : ''; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="post_id">Post ID</label></th>
                <td><input name="post_id" type="number" id="post_id" value="<?php echo $group ? esc_attr( $group->post_id ) : '0'; ?>" class="regular-text" required></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
