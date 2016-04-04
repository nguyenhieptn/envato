<?php
if (!defined('ABSPATH')) {
    exit();
}

/* get all post type. */
$post_types = get_post_types(array('public'=> true, 'show_ui'=> true));

unset($post_types['page']);
unset($post_types['attachment']);

/* templates */
$templates = array(
    '' => esc_html__('Default', 'envato-market'),
    'envato' => esc_html__('Envato Market', 'envato-market')
);

$_template = get_option('_envato-market-template','');

/* get options. */
$_access_token = get_option('_envato-market-access-token','');
$_post_types = get_option('_envato-market-post-types', array());

if(empty($_post_types))
    $_post_types = array();

/* ini get */
$memory_limit = ini_get("memory_limit");
$max_time = ini_get("max_execution_time");
$post_max_size = ini_get('post_max_size');
$site_url = get_site_url();

settings_fields('envato-market-settings-group-main');
do_settings_sections('envato-market-settings-group-main');

?>
<table class="form-table">
    <tr>
        <th scope="row"><?php esc_html_e('Server Info:', 'envato-market') ?></th>
        <td>
            <p class="description"><?php esc_html_e('1 - host:', 'envato-market') ?> <?php echo esc_url($site_url); ?></p>
            <p class="description"><?php esc_html_e('1 - memory_limit:', 'envato-market') ?> <?php echo esc_html($memory_limit); ?></p>
            <p class="description"><?php esc_html_e('2 - max_execution_time:', 'envato-market') ?> <?php echo esc_html($max_time); ?></p>
            <p class="description"><?php esc_html_e('3 - post_max_size:', 'envato-market') ?> <?php echo esc_html($post_max_size); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Access token:', 'envato-market') ?></th>
        <td>
            <input name="_envato-market-access-token" type="text" class="regular-text" value="<?php echo esc_attr($_access_token); ?>" placeholder="<?php esc_html_e('0LiDEmekYdJXFrXZ2jK65o3UKOu0up7h', 'envato-market'); ?>">
            <p class="description"><?php esc_html_e('You can create a personal token ', 'envato-market'); ?><a target="_blank" href="https://build.envato.com/my-apps/"><?php esc_html_e('here.', 'envato-market'); ?></a></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Post type:', 'envato-market') ?></th>
        <td>
            <select name="_envato-market-post-types[]" multiple>

                <?php foreach($post_types as $_type): ?>

                    <option value="<?php echo esc_attr($_type); ?>"<?php if(in_array($_type, $_post_types)){ echo ' selected="selected"'; }?>><?php echo esc_html($_type); ?></option>

                <?php endforeach; ?>

            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Taxonomy Slug', 'envato-market'); ?></th>
        <td><p class="description"><?php esc_html_e('You can set taxonomy slug follow (posttype_category & posttype-tag) plugin auto detect categories and tags of post type.', 'envato-market') ?></p></td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Template:', 'envato-market') ?></th>
        <td>
            <select name="_envato-market-template">

                <?php foreach($templates as $key => $template): ?>

                    <option value="<?php echo esc_attr($key); ?>"<?php if($key == $_template){ echo ' selected="selected"'; }?>><?php echo esc_html($template); ?></option>

                <?php endforeach; ?>

            </select>
        </td>
    </tr>
</table>
