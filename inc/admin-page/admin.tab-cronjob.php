<?php
if (!defined('ABSPATH')) {
    exit();
}

$_update_time = get_option('_envato-market-update-time', '');
$_sleep_time = get_option('_envato-market-sleep-time', '');

/* cron job time. */
$cron_job = array(
    ''      => esc_html('Turn Off', 'envato-market'),
    '1'     => esc_html('Once Per Minute (* * * * *)', 'envato-market'),
    '5'     => esc_html('Once Per Five Minutes (*/5 * * * *)', 'envato-market'),
    '10'    => esc_html('Once Per 15 Minutes (*/10 * * * *)', 'envato-market'),
    '15'    => esc_html('Once Per Five Minutes (*/15 * * * *)', 'envato-market'),
    '30'    => esc_html('Twice Per Hour (0,30 * * * *)', 'envato-market'),
    '60'    => esc_html('Once Per Hour (0 * * * *)', 'envato-market'),
);

$sleep_time = array(
    '0'      => esc_html('Turn Off', 'envato-market'),
    '5'      => esc_html('5 minutes', 'envato-market'),
    '20'      => esc_html('20 minutes', 'envato-market'),
    '30'      => esc_html('30 minutes', 'envato-market'),
    '60'      => esc_html('1 hours', 'envato-market'),
    '120'      => esc_html('2 hours', 'envato-market'),
    '180'      => esc_html('3 hours', 'envato-market'),
    '240'      => esc_html('4 hours', 'envato-market'),
    '300'      => esc_html('5 hours', 'envato-market'),
    '360'      => esc_html('6 hours', 'envato-market'),
    '300'      => esc_html('7 hours', 'envato-market'),
    '300'      => esc_html('8 hours', 'envato-market'),
    '300'      => esc_html('9 hours', 'envato-market'),
    '600'      => esc_html('10 hours', 'envato-market')
);

settings_fields('envato-market-settings-group-cronjob');
do_settings_sections('envato-market-settings-group-cronjob');

?>
<table class="form-table">
    <tr>
        <th scope="row"><?php esc_html_e('Auto Update', 'envato-market'); ?></th>
        <td>
            <select name="_envato-market-update-time">
                <?php foreach($cron_job as $key => $_time): ?>

                    <option value="<?php echo esc_attr($key); ?>"<?php if($key == $_update_time){ echo ' selected="selected"'; }?>><?php echo esc_html($_time); ?></option>

                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php esc_html_e("1 - Edit File wp-config.php add 'define('DISABLE_WP_CRON', true);'", 'envato-market'); ?>
                </br>
                <?php esc_html_e('2 - Command:', 'envato-market'); ?> <?php echo '"wget -q -O - your-site/wp-cron.php?doing_wp_cron"'; ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Sleep Times:', 'envato-market') ?></th>
        <td>
            <select name="_envato-market-sleep-time">
                <?php foreach($sleep_time as $key => $_time): ?>

                    <option value="<?php echo esc_attr($key); ?>"<?php if($key == $_sleep_time){ echo ' selected="selected"'; }?>><?php echo esc_html($_time); ?></option>

                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e("You can set times sleep after get new items.", 'envato-market'); ?></p>
        </td>
    </tr>
</table>