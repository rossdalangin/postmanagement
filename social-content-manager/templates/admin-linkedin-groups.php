<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once SCM_PLUGIN_DIR . 'includes/class-scm-li-group-table.php';
$table = new SCM_LI_Group_Table();
$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
if ( $action === 'edit' || $action === 'add' ) {
    $type = 'li';
    include SCM_PLUGIN_DIR . 'templates/admin-group-form.php';
} else {
    $table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">LinkedIn Groups</h1>
        <a href="?page=scm-linkedin-groups&action=add" class="page-title-action">Add New</a>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php wp_nonce_field( 'bulk-linkedin_groups' ); ?>
            <?php $table->search_box( 'Search Groups', 'search_id' ); ?>
            <?php $table->display(); ?>
        </form>
    </div>
    <?php
}
