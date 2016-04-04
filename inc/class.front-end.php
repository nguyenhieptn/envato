<?php
if (!defined('ABSPATH')) {
    exit();
}


if (!class_exists('Envato_market_templates')) :

    class Envato_market_templates
    {

        function __construct()
        {
            add_filter( 'archive_template', array($this, 'get_archive_envato_template' ));
            add_filter( 'search_template', array($this, 'get_search_envato_template' ));
            add_filter( 'single_template', array($this, 'get_single_envato_template' ));
        }

        /**
         * custom archive template
         *
         * @param $archive_template
         * @return string
         */
        function get_archive_envato_template( $archive_template ) {
            global $post, $em_post_type;

            $template = get_option('_envato-market-template', '');

            if ($template && isset($post->post_type) && in_array($post->post_type, $em_post_type) )
                $archive_template = envato_market()->template_dir . '/' . $template . '/archive.php';

            return $archive_template;
        }

        /**
         * custom search template
         *
         * @param $search_template
         * @return mixed
         */
        function get_search_envato_template( $search_template ){

            $template = get_option('_envato-market-template', '');

            if($template)
                $search_template = envato_market()->template_dir . '/' . $template . '/search.php';

            return $search_template;
        }

        /**
         * custom single template
         *
         * @param $single_template
         * @return string
         */
        function get_single_envato_template( $single_template ){
            global $em_post_type;

            $template = get_option('_envato-market-template', '');

            if($template && in_array(get_post_type(), $em_post_type))
                $single_template = envato_market()->template_dir . '/' . $template . '/single.php';

            return $single_template;
        }
    }

endif;

new Envato_market_templates();