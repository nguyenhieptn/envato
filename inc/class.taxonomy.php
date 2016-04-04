<?php
if (!defined('ABSPATH')) {
    exit();
}


if (!class_exists('Envato_market_taxonomy')) :

    class Envato_market_taxonomy
    {
        function __construct()
        {
            // add post type.
            add_action('init', array($this, 'add_taxonomy'), 5);

            // register post settings.
            add_action('admin_init', array($this, 'register_post_settings'));

            /* actions save and edit term. */
            add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
            add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );

            /* add meta box. */
            add_action( 'add_meta_boxes', array($this,'add_meta_box' ));

            // save post.
            add_action('save_post', array($this, 'save_meta_data'));

            // add admin page.
            add_action('admin_menu', array($this, 'add_admin_page'));
        }

        function add_taxonomy(){

            global $em_post_type;

            $em_post_type = get_option('_envato-market-post-types', array());

            /* if post type null. */
            if(empty($em_post_type)) {
                $em_post_type = array();
                return;
            }

            /* attributes labels */
            $_attributes_labels = array(
                'name'              => esc_html__( 'Attributes', 'envato-market' ),
                'singular_name'     => esc_html__( 'Attribute', 'envato-market' ),
                'search_items'      => esc_html__( 'Search Attributes', 'envato-market'),
                'all_items'         => esc_html__( 'All Attributes', 'envato-market'),
                'parent_item'       => esc_html__( 'Parent Attribute', 'envato-market'),
                'parent_item_colon' => esc_html__( 'Parent Attribute:', 'envato-market'),
                'edit_item'         => esc_html__( 'Edit Attribute', 'envato-market'),
                'update_item'       => esc_html__( 'Update Attribute', 'envato-market'),
                'add_new_item'      => esc_html__( 'Add New Attribute', 'envato-market'),
                'new_item_name'     => esc_html__( 'New Attribute Name', 'envato-market'),
                'menu_name'         => esc_html__( 'Attributes', 'envato-market'),
            );

            /* attributes labels */
            $_tag_labels = array(
                'name'              => esc_html__( 'Tags (Envato)', 'envato-market' ),
                'singular_name'     => esc_html__( 'Tags', 'envato-market' ),
                'menu_name'         => esc_html__( 'Tags', 'envato-market'),
            );

            foreach($em_post_type as $_type) {

                /* register taxonomy attributes. */
                register_taxonomy("em-{$_type}-atts", $_type, array(
                    'labels' => $_attributes_labels,
                    'hierarchical' => true,
                    'query_var' => false,
                    'show_ui' => true,
                    'rewrite' => false,
                    'public' => false,
                    'meta_box_cb' => false,
                    'show_in_quick_edit' => false,
                ));

                /* register taxonomy tag. */
                register_taxonomy("em-tag", $_type, array(
                    'labels' => $_tag_labels,
                    'hierarchical' => false,
                    'show_ui' => true,
                    'rewrite' => array(
                        'slug' => 'items'
                    ),
                    'public' => true
                ));

                /* add columns to taxonomy attributes. */
                add_filter( "manage_edit-em-{$_type}-atts_columns", array( $this, 'add_attributes_columns' ) );
                add_filter( "manage_em-{$_type}-atts_custom_column", array( $this, 'add_attributes_column' ), 10, 3 );

                /* add custom views for post type */
                add_filter( "views_edit-" . $_type, array($this, 'add_post_custom_views') );

                /* add custom fields attributes. */
                add_action("em-{$_type}-atts_add_form_fields", array($this, 'add_attributes_fields'));
                add_action("em-{$_type}-atts_edit_form_fields", array( $this, 'edit_attributes_fields' ), 10 );

                /* add custom fields for posts taxonomy. */
                add_action("{$_type}-category_add_form_fields", array($this, 'add_taxonomy_fields'));
                add_action("{$_type}-category_edit_form_fields", array( $this, 'edit_taxonomy_fields' ), 10 );
            }
        }

        /**
         * custom post views.
         *
         * @param $views
         * @return mixed
         */
        function  add_post_custom_views($views){

            $screen = get_current_screen();

            $_waiting = get_option('_em_schedule_item_ids');

            if(empty($_waiting[$screen->post_type]))
                return $views;

            $total = 0;

            foreach ($_waiting[$screen->post_type] as $item){
                $total += count($item);
            }

            $views['waiting'] = '<a href="#">' . esc_html('Waiting', 'envato-market') . ' <span class="count">('.$total.')</span></a>';

            return $views;
        }

        /**
         * add new rows to attributes columns
         * @param $columns
         * @return array
         */
        function add_attributes_columns($columns){
            $new_columns = array();
            $new_columns['cb'] = $columns['cb'];
            $new_columns['name'] = $columns['name'];
            $new_columns['type'] = esc_html__( 'Type', 'envato-market' );
            $new_columns['values'] = esc_html__( 'Values', 'envato-market' );

            unset( $columns['description'] );
            unset( $columns['posts']);

            return array_merge( $new_columns, $columns );
        }

        /**
         * add values to attributes_column
         * @param $columns
         * @param $column
         * @param $id
         */
        function add_attributes_column($columns, $column, $id){
            switch ($column){
                case 'type':
                    echo esc_html(get_term_meta($id, 'attributes-type', true));
                    break;
                case 'values':
                    $_attr_values = get_term_meta($id, 'attributes-values', true);

                    if(is_array($_attr_values)){
                        foreach($_attr_values as $_value){
                            echo esc_html($_value['title']).', ';
                        }
                    }
                    break;
            }
        }

        /**
         * add taxonomy form field.
         */
        function add_taxonomy_fields(){
            require_once envato_market()->plugin_dir . 'inc/attributes/taxonomy-fields.php';
        }

        /**
         * Edit category form field.
         *
         * @param mixed $term Term (category) being edited
         */
        function edit_taxonomy_fields($term){
            require_once envato_market()->plugin_dir . 'inc/attributes/edit-taxonomy-fields.php';
        }

        /**
         * add attributes form field.
         */
        function add_attributes_fields(){
            require_once envato_market()->plugin_dir . 'inc/attributes/attributes-fields.php';
        }

        /**
         * Edit attributes form field.
         *
         * @param mixed $term Term (category) being edited
         */
        function edit_attributes_fields($term){
            require_once envato_market()->plugin_dir . 'inc/attributes/edit-attributes-fields.php';
        }

        /**
         * Add metabox for custom post type
         */
        function add_meta_box() {

            global $em_post_type;

            foreach ($em_post_type as $_type) {
                add_meta_box('envato-market-attributes', esc_html__('Attributes', 'envato-market'), array($this, 'get_meta_box_attributes_content'), $_type);
                add_meta_box('envato-market-thumb', esc_html__('Thumbnail', 'envato-market'), array($this, 'get_meta_box_thumb_content'), $_type, 'side', 'low');
            }
        }

        /**
         * meta box attributes content.
         */
        function get_meta_box_attributes_content(){
            global $post;
            require_once envato_market()->plugin_dir . 'inc/attributes/meta-box-attributes.php';
        }

        /**
         * meta box thumbnail content.
         */
        function get_meta_box_thumb_content(){
            global $post;
            require_once envato_market()->plugin_dir . 'inc/attributes/meta-box-thumb.php';
        }

        /**
         * admin panel.
         */
        function add_admin_page()
        {
            global $em_post_type;

            foreach ($em_post_type as $_type) {
                add_submenu_page("edit.php?post_type={$_type}", esc_html__('Settings', 'envato-market'), esc_html__('Settings', 'envato-market'), 'manage_options', "em-{$_type}-options", array($this, 'get_admin_page_options_content'));
            }
        }

        function get_admin_page_options_content(){
            require_once envato_market()->plugin_dir . 'inc/admin-page/admin.post.php';
        }

        /**
         * get parent taxonomy slug by id.
         *
         * @param $id
         * @param $taxonomy
         * @return string
         */
        function get_parent_taxonomy($id, $taxonomy){

            if(!$id)
                return '';

            $_term = get_term_by('id', $id, $taxonomy);

            return $_term->slug;
        }

        /**
         * get meta value.
         *
         * @param $post_id
         * @param $parent
         * @param $name
         * @return mixed
         */
        function get_meta_value($post_id ,$parent, $name){
            if($parent){
                $parent_value = get_post_meta($post_id, '_em_' . $parent, true);
                if(isset($parent_value[$name]))
                    return $parent_value[$name];
            } else {
                return get_post_meta($post_id, '_em_' . $name, true);
            }
        }


        /**
         * admin post setting.
         */
        function register_post_settings()
        {
            global $em_post_type;

            foreach ($em_post_type as $_type) {
                register_setting("em-{$_type}-settings-group", "_em-{$_type}-site-url");
                register_setting("em-{$_type}-settings-group", "_em-{$_type}-update-times");
            }
        }

        /**
         * save_category_fields function.
         *
         * @param mixed $term_id Term ID being saved
         */
        function save_category_fields($term_id, $tt_id = '', $taxonomy = ''){

            /* attributes. */
            if (isset($_POST['attributes-type'])){

                update_term_meta($term_id, 'attributes-type', $_POST['attributes-type']);

                if($_POST['attributes-type'] == 'select' || $_POST['attributes-type'] == 'multiple' && isset($_POST['attributes-values'])) {

                    $_POST['attributes-values'] = array_filter($_POST['attributes-values'], function($k){ return $k['title'] != '';});

                    update_term_meta($term_id, 'attributes-values', $_POST['attributes-values']);
                }
            }

            if(isset($_POST['parent']) && $_POST['parent'] == '-1') {
                /* taxonomy */
                if (isset($_POST['em-taxonomy-title-template']))
                    update_term_meta($term_id, 'em-taxonomy-title-template', $_POST['em-taxonomy-title-template']);
                if (isset($_POST['em-taxonomy-default-tag']))
                    update_term_meta($term_id, 'em-taxonomy-default-tag', $_POST['em-taxonomy-default-tag']);
            }

        }

        /**
         * save meta box
         *
         * meta data for post type
         *
         * @param $post_id
         * @since 1.0.0
         */
        function save_meta_data($post_id)
        {
            global $em_post_type, $post;

            // If this is an autosave, our form has not been submitted,
            // so we don't want to do anything.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return $post_id;

            // if post type null.
            if(!isset($post->post_type))
                return;

            // if post type # $em_post_type
            if (!in_array($post->post_type , $em_post_type))
                return $post_id;

            /* OK, its safe for us to save the data now. */
            foreach ($_POST as $key => $value) {

                // check key.
                if (!strstr($key, '_em_'))
                    continue;

                // Update the meta field.
                update_post_meta($post_id, $key, $value);
            }
        }
    }

endif;

new Envato_market_taxonomy();