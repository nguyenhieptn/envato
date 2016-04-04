<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Envato_market_admin')) :

    class Envato_market_admin
    {

        function __construct()
        {
            // register plugin settings.
            add_action('admin_init', array($this, 'register_plugin_settings'));

            // add admin page.
            add_action('admin_menu', array($this, 'add_admin_page'));

            // action link.
            add_filter('plugin_action_links_' . envato_market()->basename, array($this, 'plugin_action_links'));
        }

        /**
         * admin panel.
         */
        function add_admin_page()
        {
            add_menu_page(esc_html__('Envato Market', 'envato-market'), esc_html__('Envato Market', 'envato-market'), 'manage_options', 'envato-market-options', array($this, 'get_admin_page_options_content'), 'dashicons-cart');
        }

        /**
         * admin panel content.
         */
        function get_admin_page_options_content()
        {
            require_once envato_market()->plugin_dir . 'inc/admin-page/admin.main.php';
        }

        /**
         * admin setting.
         */
        function register_plugin_settings()
        {
            register_setting('envato-market-settings-group-main', '_envato-market-access-token');
            register_setting('envato-market-settings-group-main', '_envato-market-post-types');
            register_setting('envato-market-settings-group-main', '_envato-market-template');

            register_setting('envato-market-settings-group-cronjob', '_envato-market-update-time');
            register_setting('envato-market-settings-group-cronjob', '_envato-market-sleep-time');

            register_setting('envato-market-settings-group-notices', '_em_settings_notices_after_get_item');
            register_setting('envato-market-settings-group-notices', '_em_settings_notices_email');
            register_setting('envato-market-settings-group-notices', '_em_settings_notices_after_check_new_item');
            register_setting('envato-market-settings-group-notices', '_em_settings_notices_item_day');
        }

        /**
         * Show action links on the plugin screen.
         *
         * @param    mixed $links Plugin Action links
         * @return    array
         */
        function plugin_action_links($links)
        {
            $action_links = array(
                'settings' => '<a href="' . admin_url('admin.php?page=envato-market-options') . '" title="' . esc_attr(esc_html__('View Envato Market Settings', 'user-press')) . '">' . esc_html__('Settings', 'user-press') . '</a>',
            );
            return array_merge($action_links, $links);
        }
    }
endif;

new Envato_market_admin();