<?php
/**
 * Plugin Name: Envato Market
 * Plugin URI: #
 * Description: Auto sync envato items, for affiliate program (themeforest.net, codecanyon.net, videohive.net, ...).
 * Version: 1.0.0
 * Author: #
 * Author URI: #
 * License: GPLv2 or later
 * Text Domain: envato-market
 */
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Envato_market')) :

    /**
     * Main Class
     *
     * @class Envato Market
     *
     * @version 1.0.0
     */
    final class Envato_market
    {

        /* single instance of the class */
        public $file = null;

        public $basename = null;

        /* base plugin_dir. */
        public $plugin_dir = null;

        public $plugin_url = null;

        /* base acess folder. */
        public $acess_dir = null;

        public $acess_url = null;

        public $template_dir = null;
        public $template_url = null;

        /**
         * Main Envato Market Instance
         *
         * Ensures only one instance of Essential Instagram is loaded or can be loaded.
         *
         * @since 1.0.0
         * @static
         *
         * @see Envato Market()
         * @return Envato Market - Main instance
         */
        public static function instance()
        {
            static $_instance = null;

            if (is_null($_instance)) {

                $_instance = new Envato_market();

                // globals.
                $_instance->setup_globals();

                // includes.
                $_instance->includes();

                // actions.
                $_instance->setup_actions();
            }

            return $_instance;
        }

        /**
         * globals value.
         *
         * @package Envato Market
         * @global path + uri.
         */
        private function setup_globals()
        {
            $this->file = __FILE__;

            /* base name. */
            $this->basename = plugin_basename($this->file);

            /* base plugin. */
            $this->plugin_dir = plugin_dir_path($this->file);
            $this->plugin_url = plugin_dir_url($this->file);

            /* base assets. */
            $this->acess_dir = trailingslashit($this->plugin_dir . 'assets');
            $this->acess_url = trailingslashit($this->plugin_url . 'assets');

            /* base template. */
            $this->template_dir = trailingslashit($this->plugin_dir . 'templates');
            $this->template_url = trailingslashit($this->plugin_url . 'templates');
        }

        /**
         * Setup all actions + filter.
         *
         * @package Envato Market
         * @version 1.0.0
         */
        private function setup_actions()
        {
            // front-end scripts.
            add_action('wp_enqueue_scripts', array($this, 'add_scrips'));

            // admin scripts.
            add_action('admin_enqueue_scripts', array($this, 'add_admin_script'));

            // menu action.
            add_action ('admin_menu', array ($this, 'add_market_menu_notice'));
        }

        /**
         * include files.
         *
         * @package Envato Market
         * @version 1.0.0
         */
        private function includes()
        {
            require_once $this->plugin_dir . 'inc/class.admin-page.php';
            require_once $this->plugin_dir . 'inc/class.taxonomy.php';
            require_once $this->plugin_dir . 'inc/class.envato.php';

            require_once $this->plugin_dir . 'inc/class.front-end.php';
        }

        /**
         * Load the translation file for current language. Checks the languages
         * folder inside the bbPress plugin first, and then the default WordPress
         * languages folder.
         */
        public function load_textdomain() {
        }

        /**
         * add front-end scripts.
         *
         * @package Envato Market
         * @version 1.0.0
         */
        function add_scrips()
        {
            global $post;

            if (!$post) return false;
        }

        /**
         * add back-end scripts.
         *
         * @package Envato Market
         * @version 1.0.0
         */
        function add_admin_script()
        {
            global $em_post_type;

            $screen = get_current_screen();

            /** post-type */
            if (isset($screen->post_type) && in_array($screen->post_type, $em_post_type)) {
                wp_enqueue_style('attributes.post-type', envato_market()->acess_url . 'css/attributes.post-type.css');
                wp_enqueue_script('em-envato', envato_market()->acess_url . 'js/envato.js', array('jquery'), '1.0.0', true);
            }

            /** tax */
            if (strstr($screen->id, 'edit-em-')) {
                wp_enqueue_media();
                wp_enqueue_style('attributes.taxonomy', envato_market()->acess_url . 'css/attributes.taxonomy.css');
                wp_enqueue_script('attributes.taxonomy', envato_market()->acess_url . 'js/attributes.taxonomy.js', array('jquery'), '1.0.0', true);
            }
        }

        /**
         * count items pending.
         */
        function add_market_menu_notice(){

            global $menu, $em_post_type;

            foreach ( $menu as $key => $item ) {

                if ($item[1] != 'edit_posts')
                    continue;

                $post_type = str_replace('edit.php?post_type=', null, $item [2]);

                if(!in_array($post_type, $em_post_type))
                    continue;

                /* count pending product. */
                $count_post = wp_count_posts ( $post_type );

                $menu [$key] [0] = $item [0] . ' <span class="awaiting-mod count-' . $count_post->pending . '"><span class="pending-count">' . $count_post->pending . '</span></span>';
            }

            return $menu;
        }

        /**
         * get list taxonomies add to post type.
         *
         * @param $post_type
         * @return array
         */
        public static function get_post_taxonomies($post_type){

            $_taxonomies = array();

            $post_taxonomies = get_object_taxonomies($post_type, 'objects');

            unset($post_taxonomies["em-{$post_type}-atts"]);

            foreach($post_taxonomies as $_slug => $_tax){
                if($_tax->hierarchical)
                    $_taxonomies[$_slug] = $_tax->labels->name;
            }

            return $_taxonomies;
        }

        /**
         * get root attributes slug from post type.
         *
         * @param $post_type
         * @return array
         */
        public static function get_root_attributes_key($post_type){

            $_attributes = get_terms("em-{$post_type}-atts", array('hide_empty'=>false, 'parent' => 0));

            $_attributes_slug = array();

            foreach ($_attributes as $_attr){
                $_attributes_slug[] = $_attr->slug;
            }

            return $_attributes_slug;
        }
    }

endif;

/**
 * Returns the main instance of envato_market() to prevent the need to use globals.
 *
 * @since 1.0
 * @return Envato_market
 */
if (!function_exists('envato_market')) {

    function envato_market()
    {
        return Envato_market::instance();
    }
}

if (defined('ENVATO_MARKET_LATE_LOAD')) {

    add_action('plugins_loaded', 'envato_market', (int)ENVATO_MARKET_LATE_LOAD);
} else {

    envato_market();
}