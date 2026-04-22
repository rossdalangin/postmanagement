<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once SCM_PLUGIN_DIR . 'includes/class-scm-fb-group-table.php';
$table = new SCM_FB_Group_Table();
$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
if ( $action === 'edit' || $action === 'add' ) {
    $type = 'fb';
    include SCM_PLUGIN_DIR . 'templates/admin-group-form.php';
} else {
    $table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Facebook Groups</h1>
        <a href="?page=scm-facebook-groups&action=add" class="page-title-action">Add New</a>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php $table->search_box( 'Search Groups', 'search_id' ); ?>
            <?php $table->display(); ?>
        </form>
    </div>
    <?php
}
