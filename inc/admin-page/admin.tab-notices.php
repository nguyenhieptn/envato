<?php
if (!defined('ABSPATH')) {
    exit();
}

$_update_time = get_option('_envato-market-update-time', '');

$_email = get_option('_em_settings_notices_email', '');

settings_fields('envato-market-settings-group-notices');
do_settings_sections('envato-market-settings-group-notices');
?>
<table class="form-table">
    <tr>
        <th scope="row"><?php esc_html_e('E-Mail:', 'envato-market') ?></th>
        <td>
            <input type="email" name="_em_settings_notices_email" value="<?php echo esc_html($_email); ?>" placeholder="<?php echo get_bloginfo( 'admin_email' ); ?>">
        </td>
    </tr>

    <?php if($_update_time): ?>

    <?php $_after_get_item = get_option('_em_settings_notices_after_get_item', false); ?>
    <?php $_after_check_new_item = get_option('_em_settings_notices_after_check_new_item', false); ?>
    <?php $_pending_items_day = get_option('_em_settings_notices_item_day', false); ?>

    <tr>
        <th scope="row"><?php esc_html_e('After get item:', 'envato-market') ?></th>
        <td>
            <input type="checkbox" name="_em_settings_notices_after_get_item"<?php echo $_after_get_item ? ' checked="checked"' : '' ; ?>>
            <p class="description"><?php esc_html_e('Notice after get items complete.', 'envato-market') ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('After check new items:', 'envato-market') ?></th>
        <td>
            <input type="checkbox" name="_em_settings_notices_after_check_new_item"<?php echo $_after_check_new_item ? ' checked="checked"' : '' ; ?>>
            <p class="description"><?php esc_html_e('Notice after check new items if exists new items.', 'envato-market') ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Pending items / day:', 'envato-market') ?></th>
        <td>
            <input type="checkbox" name="_em_settings_notices_item_day"<?php echo $_pending_items_day ? ' checked="checked"' : '' ; ?>>
            <p class="description"><?php esc_html_e('Notice total pending items / day.', 'envato-market') ?></p>
        </td>
    </tr>

    <?php endif; ?>

</table>
