<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h1>Import / Export</h1>

    <div class="card">
        <h2>Export Data</h2>
        <p>Export your data to CSV files.</p>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="scm_export_csv">
            <?php wp_nonce_field( 'scm_export_nonce' ); ?>
            <select name="export_type">
                <option value="facebook_groups">Facebook Groups</option>
                <option value="linkedin_groups">LinkedIn Groups</option>
                <option value="content_posts">Content Posts</option>
            </select>
            <select name="export_format">
                <option value="csv">CSV</option>
                <option value="excel">Excel (.xls)</option>
            </select>
            <?php submit_button( 'Export', 'secondary', 'submit', false ); ?>
        </form>
    </div>

    <div class="card">
        <h2>Download CSV Templates</h2>
        <p>Download sample CSV templates to ensure your import file is correctly formatted.</p>
        <div style="display: flex; gap: 10px;">
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="scm_download_template">
                <input type="hidden" name="template_type" value="facebook_groups">
                <?php wp_nonce_field( 'scm_download_template' ); ?>
                <button type="submit" class="button">Facebook Groups Template</button>
            </form>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="scm_download_template">
                <input type="hidden" name="template_type" value="linkedin_groups">
                <?php wp_nonce_field( 'scm_download_template' ); ?>
                <button type="submit" class="button">LinkedIn Groups Template</button>
            </form>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="scm_download_template">
                <input type="hidden" name="template_type" value="content_posts">
                <?php wp_nonce_field( 'scm_download_template' ); ?>
                <button type="submit" class="button">Content Posts Template</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h2>Import Data (CSV)</h2>
        <p>Upload a CSV file to import data. Note: The first row should be headers.</p>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="scm_import_csv">
            <?php wp_nonce_field( 'scm_import_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="import_type">Data Type</label></th>
                    <td>
                        <select name="import_type" id="import_type">
                            <option value="facebook_groups">Facebook Groups</option>
                            <option value="linkedin_groups">LinkedIn Groups</option>
                            <option value="content_posts">Content Posts</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="import_file">CSV File</label></th>
                    <td><input type="file" name="import_file" id="import_file" accept=".csv" required></td>
                </tr>
            </table>
            <?php submit_button( 'Import CSV' ); ?>
        </form>
    </div>

    <?php if ( isset( $_GET['imported'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>Import completed: <?php echo intval( $_GET['imported'] ); ?> rows imported, <?php echo intval( $_GET['skipped'] ); ?> skipped/errors.</p>
        </div>
    <?php endif; ?>
</div>
