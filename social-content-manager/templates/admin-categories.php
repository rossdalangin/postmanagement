<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once SCM_PLUGIN_DIR . 'includes/class-scm-category-table.php';
$table = new SCM_Category_Table();
$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
if ( $action === 'edit' || $action === 'add' ) {
    include SCM_PLUGIN_DIR . 'templates/admin-category-form.php';
} else {
    $table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Categories</h1>
        <a href="?page=scm-categories&action=add" class="page-title-action">Add New</a>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php wp_nonce_field( 'bulk-categories' ); ?>
            <?php $table->search_box( 'Search Categories', 'search_id' ); ?>
            <?php $table->display(); ?>
        </form>
    </div>
    <?php
}
