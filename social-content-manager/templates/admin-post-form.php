<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$post_data = null;
if ( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'scm_content_posts';
    $post_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
}

$platforms = array(
    'good_for_fb_group' => 'Good for Facebook Group',
    'good_for_linkedin_group' => 'Good for LinkedIn Group',
    'facebook' => 'Facebook',
    'linkedin' => 'LinkedIn',
    'youtube' => 'YouTube',
    'tiktok' => 'TikTok',
    'pinterest' => 'Pinterest',
    'twitter' => 'Twitter',
    'threads' => 'Threads',
    'ig' => 'Instagram'
);
?>
<div class="wrap">
    <h1><?php echo $id ? 'Edit' : 'Add New'; ?> Content Post</h1>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="scm_save_post">
        <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
        <?php wp_nonce_field( 'scm_save_post_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="post_content">Post Content</label></th>
                <td><textarea name="post_content" id="post_content" rows="10" cols="50" class="large-text" required><?php echo $post_data ? esc_textarea( $post_data->post_content ) : ''; ?></textarea></td>
            </tr>
            <tr>
                <th>Platforms / Targets</th>
                <td>
                    <fieldset>
                        <?php foreach ( $platforms as $key => $label ) : ?>
                            <?php
                                $checked = false;
                                if ( $post_data ) {
                                    if ( $post_data->$key ) $checked = true;
                                } else {
                                    // Defaults for new posts
                                    if ( $key === 'good_for_fb_group' || $key === 'good_for_linkedin_group' ) $checked = true;
                                }
                            ?>
                            <label for="<?php echo esc_attr( $key ); ?>">
                                <input name="<?php echo esc_attr( $key ); ?>" type="checkbox" id="<?php echo esc_attr( $key ); ?>" value="1" <?php echo $checked ? 'checked' : ''; ?>>
                                <?php echo esc_html( $label ); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
