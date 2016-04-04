<?php
if (!defined('ABSPATH')) {
    exit();
}

if(!isset($_GET['post_type']))
    return;

$post_type = $_GET['post_type'];

$_site_url = get_option("_em-{$post_type}-site-url", '');

?>
<div class="wrap">
    <h2><?php esc_html_e('Setting', 'envato-market');?></h2>

    <form method="post" action="options.php">

        <?php settings_fields("em-{$post_type}-settings-group"); ?>
        <?php do_settings_sections("em-{$post_type}-settings-group"); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Site Url:', 'envato-market');?></th>
                <td>
                    <input name="_em-<?php echo esc_attr($post_type);?>-site-url" type="text" class="regular-text" value="<?php echo esc_attr($_site_url); ?>" placeholder="<?php esc_html_e('themeforest.net', 'envato-market') ?>">
                    <p class="description"><?php esc_html_e('themeforest.net, codecanyon.net, videohive.net, audiojungle.net...', 'envato-market') ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>

    </form>
</div>
