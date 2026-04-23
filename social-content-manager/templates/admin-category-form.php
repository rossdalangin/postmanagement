<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$category = null;
if ( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'scm_categories';
    $category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
}
?>
<div class="wrap">
    <h1><?php echo $id ? 'Edit' : 'Add New'; ?> Category</h1>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="scm_save_category">
        <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
        <?php wp_nonce_field( 'scm_save_category_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="name">Category Name</label></th>
                <td><input name="name" type="text" id="name" value="<?php echo $category ? esc_attr( $category->name ) : ''; ?>" class="regular-text" required></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
