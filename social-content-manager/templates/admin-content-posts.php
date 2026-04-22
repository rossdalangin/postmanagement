<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once SCM_PLUGIN_DIR . 'includes/class-scm-post-table.php';
$table = new SCM_Post_Table();
$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
if ( $action === 'edit' || $action === 'add' ) {
    include SCM_PLUGIN_DIR . 'templates/admin-post-form.php';
} else {
    $table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Content Posts</h1>
        <a href="?page=scm-content-posts&action=add" class="page-title-action">Add New</a>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php $table->search_box( 'Search Posts', 'search_id' ); ?>
            <?php $table->display(); ?>
        </form>
    </div>
    <?php
}
