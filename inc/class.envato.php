<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Envato_market_envato')) :

    class Envato_market_envato
    {

        function __construct()
        {
            /* add ajax. */
            add_action('wp_ajax_em_get_new_items', array($this, 'ajax_get_new_callback'));
            add_action('wp_ajax_em_get_items', array($this, 'ajax_get_items_callback'));
            add_action('wp_ajax_em_get_item', array($this, 'ajax_get_item_callback'));

            /* add actions button to posts. */
            add_action('manage_posts_extra_tablenav', array($this,'add_actions_button'));

            /* cron job */
            add_action( 'schedule_event_hook', array($this , 'schedule_event'));
            add_filter( 'cron_schedules', array($this, 'custom_recurrence'));
            add_action( 'wp', array($this, 'set_schedule_event'));

            /* message box */
            add_action('admin_footer', array($this, 'add_console_log'));

            /* delete post */
            add_action('before_delete_post', array($this, 'delete_post_remove_attach_image'));
        }

        function set_schedule_event(){

            $update_time = get_option('_envato-market-update-time', false);

            if($update_time && !wp_next_scheduled( 'schedule_event_hook' ) ) {
                wp_schedule_event( time(), 'every_three_minutes', 'schedule_event_hook' );
            }
        }

        // Scheduled Action Hook
        function schedule_event() {

            /* check sleep time. */
            if(get_transient('_em_schedule_sleep_times'))
                return;

            /* get list pending ids. */
            $_schedule_ids = get_option('_em_schedule_item_ids', array());

            /* list not null. */
            if(!empty($_schedule_ids)) {
                $this->get_item_from_pending_list($_schedule_ids);
            } else {
                $this->get_new_items_to_pending_list();
            }
        }

        /**
         * auto check new items.
         */
        function get_new_items_to_pending_list(){

            $post_type = get_option('_envato-market-post-types');

            if(empty($post_type))
                return;

            $_schedule_pending_check = get_option('_em_schedule_pending_check', array());

            /* create pending check list. */
            if(empty($_schedule_pending_check)){
                foreach($post_type as $type){
                    /* get list taxonomy. */
                    $_taxonomy = envato_market()->get_post_taxonomies($type);

                    foreach($_taxonomy as $slug => $tax){
                        $_schedule_pending_check[] = $type . '/' . str_replace($type . '_', null,$slug);
                    }
                }

                /* new list. */
                update_option('_em_schedule_pending_check', $_schedule_pending_check);

                /* check back. */
                $this->get_new_items_to_pending_list();

            } else {

                $_post_type_and_cat = explode('/', $_schedule_pending_check[0]);

                $local_ids = $this->get_lastest_envato_ids_from_post($_post_type_and_cat[0], $_post_type_and_cat[1]);

                if(!empty($local_ids)) {
                    /* get ids. */
                    $_new_ids = $this->check_envato_new_items($_post_type_and_cat[0], $_post_type_and_cat[1], $local_ids);

                    if(!isset($_new_ids->error)) {
                        /* update list. */
                        $this->update_pending_ids($_post_type_and_cat[0], $_post_type_and_cat[1], $_new_ids);
                        /* send email. */
                        $this->send_email_after_check_new($_new_ids, $_post_type_and_cat[0]);
                    }
                }

                /* remove item checked. */
                unset($_schedule_pending_check[0]);

                /* update list. */
                update_option('_em_schedule_pending_check', array_values($_schedule_pending_check));
            }
        }

        /**
         * auto get item data from pending list.
         *
         * cron job.
         */
        function get_item_from_pending_list($_schedule_ids){

            $item_data = $_post_type = $_category = '';

            foreach ($_schedule_ids as $_post_type => $_categories){

                /* if post type null. */
                if(!empty($_categories)){

                    foreach ($_categories as $_category => $ids) {

                        /* if category null. */
                        if (!empty($ids)) {

                            /* get item by id. */
                            $item_data = $this->get_envato_item($ids[0], $_post_type);

                            /* remove id precessed. */
                            unset($ids[0]);

                            /* update to list.*/
                            $_schedule_ids[$_post_type][$_category] = array_values($ids);
                            break;
                        } else {
                            /* remove category null */
                            unset($_categories[$_category]);
                            $_schedule_ids[$_post_type] = $_categories;
                        }
                    }
                    break;
                } else {
                    /* remove post type null */
                    unset($_schedule_ids[$_post_type]);
                }
            }

            update_option('_em_schedule_item_ids', $_schedule_ids);

            /* if check no new items set sleep times. */
            if (empty($_schedule_ids)){
                $sleep_time = get_option('_envato-market-sleep-time', 30);
                set_transient('_em_schedule_sleep_times', $sleep_time, 60 * (int)$sleep_time);
            }

            if(!empty($item_data)){
                /** send email */
                $this->send_email_after_get_item($item_data);
            }
        }

        // Custom Cron Recurrences
        function custom_recurrence( $schedules ) {

            $update_time = get_option('_envato-market-update-time', 1);

            if(!$update_time)
                return $schedules;

            $schedules['every_three_minutes'] = array(
                'display' => __( 'Envato Update Time', 'envato-market' ),
                'interval' => 10,
            );

            return $schedules;
        }

        /**
         * add actions button to posts.
         */
        function add_actions_button(){

            global $em_post_type;

            $screen = get_current_screen();

            if(!isset($screen->post_type))
                return;

            if(!in_array($screen->post_type , $em_post_type))
                return;

            $em_post_terms = envato_market()->get_post_taxonomies($screen->post_type);

            echo "<div class='alignleft actions em-actions'>";
            echo      "<select class='em-category'>";
            foreach($em_post_terms as $_key => $_term){
                echo "<option value='".str_replace($screen->post_type . '_', null, $_key)."'>{$_term}</option>";
            }
            echo      "</select>";
            echo      "<input type='button' class='button em-get-new-items' value='Check New'>";
            echo      "<input type='number' class='em-page' title='Page' max='60' min='1' value='1'>";
            echo      "<input type='number' class='em-items' title='Items/Page' max='30' min='1' value='30'>";
            echo      "<input type='button' class='button em-get-items' value='Get Items'>";
            echo      "<input type='text' class='em-item-id' title='Item ID' placeholder='Add Item ID'>";
            echo      "<input type='button' class='button em-get-item' value='Get Item'>";
            echo      "<span class='spinner is-active' style='display: none'></span>";
            echo "</div>";
        }

        /**
         * console log.
         */
        function add_console_log(){
            global $em_post_type;

            $screen = get_current_screen();

            if($screen->base != 'edit')
                return;

            if(!in_array($screen->post_type , $em_post_type))
                return;

            echo '<div id="em-console-log">';
            echo '<div class="em-console-header"><span class="dashicons dashicons-editor-code"></span><span id="em-console-title">'.esc_html__('standby...', 'envato-market').'</span><span class="dashicons dashicons-arrow-up-alt2 right"></span></div>';
            echo '<div class="em-console-content"><div></div></div>';
            echo '</div>';
        }

        /**
         * get new items.
         */
        function ajax_get_new_callback(){

            header('Content-Type: application/json');

            $_type = $_POST['type'];
            $_category = $_POST['category'];

            $_latest_ids = $this->get_lastest_envato_ids_from_post($_type, $_category);

            $_new_ids = $this->check_envato_new_items($_type, $_category, $_latest_ids);

            if(isset($_new_ids->error))
                exit(json_encode($_new_ids));

            $this->update_pending_ids($_type, $_category, $_new_ids);

            exit(json_encode($_new_ids));
        }

        /**
         * ajax get items
         * @param $_POST['page']
         * @param $_POST['number-items']
         */
        function ajax_get_items_callback(){

            header('Content-Type: application/json');

            $_type = $_POST['type'];

            $_page = (int)(isset($_POST['page']) ? $_POST['page'] : 1);
            $_items = (int)(isset($_POST['items']) ? $_POST['items'] : 30);
            $_category = $_POST['category'];

            $items_data = $this->remote_get_enveto_items(array('site'=> $_type . '.net', 'category'=> $_category, 'page'=> $_page, 'page_size' => $_items, 'sort_by'=>'date', 'sort_direction'=>'desc'));

            /* is error. */
            if(isset($items_data->error))
                exit(json_encode($items_data));

            /* insert posts. */
            $log = $this->insert_posts($items_data, $_type);

            exit(json_encode($log));
        }

        /**
         * ajax get item
         * @param $_POST['item_id']
         */
        function ajax_get_item_callback(){

            header('Content-Type: application/json');

            /* if type null/ */
            if(empty($_POST['type']) || empty($_POST['item_id']))
                exit();

            /* get item by id. */
            $log = $this->get_envato_item($_POST['item_id'], $_POST['type']);

            exit(json_encode($log));
        }

        /**
         * delete post remove attach image
         */
        public function delete_post_remove_attach_image($post_id){
            global $em_post_type;

            $post_type = get_post_type($post_id);

            if(!in_array($post_type, $em_post_type)) return;

            $media = get_children(array(
                'post_parent' => $post_id,
                'post_type' => 'attachment'
            ));

            if (empty($media)) return;

            foreach ($media as $file) {
                // pick what you want to do
                wp_delete_attachment($file->ID);
            }
        }

        /**
         * insert posts
         *
         * @param $items_data
         * @param $post_type
         * @return bool
         */
        private function insert_posts($items_data, $post_type){

            /* if data null. */
            if(empty($items_data->matches))
                return (object)array('error' => esc_html__('search', 'envato-market'), 'error_description' => esc_html__('search not found.', 'envato-market'));

            $attributes_key = envato_market()->get_root_attributes_key($post_type);

            $log = array();

            foreach ($items_data->matches as $item_data){
                $log[$item_data->id] = $this->insert_post($item_data, $post_type, $attributes_key);
            }

            return $log;
        }

        /**
         * @param $item_data
         * @param $post_type
         * @return bool
         */
        private function insert_post($item_data, $post_type, $attributes_key = array()){

            /* check post exist. */
            $_post_exist = $this->get_post_by_item_id($item_data->id, $post_type);

            if(!empty($_post_exist))
                return (object)array('error' => esc_html__('exist', 'envato-market'), 'error_description' => sprintf('item " %s " exist', $item_data->name));

            /* query product data. */
            $post = array(
                'post_title' => $item_data->name,
                'post_status' => 'pending',
                'post_content' => isset($item_data->description_html) ? $item_data->description_html : $item_data->description,
                'post_date' => $item_data->published_at,
                'post_date_gmt' => $item_data->published_at,
                'post_type' => $post_type,
            );

            /* insert product. */
            $post_id = wp_insert_post($post, true);

            if(!$post_id)
                return (object)array('error' => esc_html__('wp_insert_post', 'envato-market'), 'error_description' => esc_html__('does not insert post', 'envato-market'));;

            /* update category. */
            $_categories = explode('/',$item_data->classification);
            if(isset($_categories[0])) {
                /* get root tax. */
                $taxonomy = $post_type . '_' . $_categories[0];

                /* get tax ids. */
                $_terms_id = $this->insert_taxonomies($_categories, $taxonomy);

                /* update post category. */
                wp_set_post_terms($post_id, $_terms_id , $taxonomy);

                /* set post meta taxonomy type. */
                update_post_meta($post_id, '_em_taxonomy', $_categories[0]);
            }

            /* insert tags. */
            wp_set_post_terms($post_id, $item_data->tags , 'em-tag');

            /* add icon preview image.*/
            $_attachment_icon_id = $this->insert_attachment_from_url($item_data->previews->icon_with_landscape_preview->icon_url, $post_id, $item_data->name);

            /* add landscape preview image.*/
            $_attachment_landscape_id = $this->insert_attachment_from_url($item_data->previews->icon_with_landscape_preview->landscape_url, $post_id, $item_data->name);

            /* set post thumbnail & icon. */
            set_post_thumbnail($post_id, $_attachment_landscape_id);
            update_post_meta($post_id, '_em_thumbnail', $_attachment_icon_id);

            /* update post meta. */
            $this->insert_post_meta($item_data, $post_id, $attributes_key);

            return (object)array('ID' => $post_id,'item_id'=> $item_data->id, 'title' => $item_data->name, 'url' => get_the_permalink($post_id));
        }

        /**
         * update taxonomies
         *
         * @param array $terms
         * @param $taxonomy
         * @return array|bool
         */
        private function insert_taxonomies($terms = array(), $taxonomy){

            /* if taxonomies not exists. */
            if (!taxonomy_exists($taxonomy))
                return false;

            /* remove root tax. */
            unset($terms[0]);

            /* all tax ids. */
            $_terms_id = array();

            /* current parent id. */
            $_parent_id = 0;

            /* add or get tax ids. */
            foreach($terms as $_cat){

                /* get term from slug. */
                $term_exists = get_term_by('slug', $_cat , $taxonomy, ARRAY_A);

                /* if term exists.*/
                if($term_exists) {

                    /* get id parent. */
                    $_terms_id[] = $_parent_id = $term_exists['term_id'];

                } else {

                    /* insert new term. */
                    $new_term = wp_insert_term(ucwords($_cat), $taxonomy, array(
                        'slug' => $_cat,
                        'parent' => $_parent_id
                    ));

                    /* get new id parent. */
                    if(!is_wp_error($new_term))
                        $_terms_id[] = $_parent_id = $new_term['term_id'];
                }
            }

            return $_terms_id;
        }

        /**
         * update post meta.
         *
         * @param $post_meta
         * @param $post_id
         * @param array $meta_keys
         */
        private function insert_post_meta($post_meta, $post_id, $meta_keys = array()){

            /* item id not exists. */
            if(!in_array('id', $meta_keys))
                update_post_meta($post_id, '_em_id', $post_meta->id);

            foreach ($post_meta as $key => $meta){
                if(!in_array($key, $meta_keys))
                    continue;

                /* value is object or array. */
                if($key == 'attributes')
                    $meta = $this->convert_envato_attributes($meta);

                /* meta is object. */
                if(is_object($meta))
                    $meta = $this->convert_envato_object($meta);

                update_post_meta($post_id, '_em_' . $key, $meta);
            }
        }

        /**
         * Insert an attachment from an URL address.
         *
         * @param String $url
         * @param Int $post_id
         * @return Int Attachment ID
         */
        private function insert_attachment_from_url($url, $post_id, $title = '')
        {
            if (! class_exists('WP_Http'))
                include_once (ABSPATH . WPINC . '/class-http.php');

            $http = new WP_Http();

            $response = $http->request($url);
            if (is_wp_error($response)) {
                return false;
            }

            $upload = wp_upload_bits(basename($url), null, $response['body']);

            if (! empty($upload['error'])) {
                return false;
            }

            $file_path = $upload['file'];
            $file_name = basename($file_path);
            $file_type = wp_check_filetype($file_name, null);
            $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
            $wp_upload_dir = wp_upload_dir();

            $post_info = array(
                'guid' => $wp_upload_dir['url'] . '/' . $file_name,
                'post_mime_type' => $file_type['type'],
                'post_title' => $title,
                'post_status' => 'inherit'
            );

            // Create the attachment
            $attach_id = wp_insert_attachment($post_info, $file_path, $post_id);

            // Include image.php
            require_once (ABSPATH . 'wp-admin/includes/image.php');

            // Define attachment metadata
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);

            // Assign metadata to attachment
            wp_update_attachment_metadata($attach_id, $attach_data);

            return $attach_id;
        }

        /**
         * get envato item by id.
         *
         * @param $id
         * @param $post_type
         */
        private function get_envato_item($id, $post_type){

            /* get item data by ID */
            $item_data = $this->remote_get_envato_item($id);

            /* is error. */
            if(isset($item_data->error))
                return $item_data;

            /* get attributes key */
            $attributes_key = envato_market()->get_root_attributes_key($post_type);

            /* insert item. */
            return $this->insert_post($item_data, $post_type, $attributes_key);
        }
        /**
         * get 5 post envato ID latest.
         *
         * @param $post_type
         * @param $taxonomy
         * @return array|null
         */
        private function get_lastest_envato_ids_from_post($post_type, $taxonomy){

            $args = array(
                'posts_per_page'    => 5,
                'post_type'         => $post_type,
                'meta_key'          => '_em_taxonomy',
                'meta_value'        => $taxonomy,
                'post_status'       => 'any',
                'orderby'           => 'date',
                'order'             => 'DESC'
            );

            $the_query = new WP_Query( $args );

            if(empty($the_query->posts))
                return null;

            $_latest_ids = array();

            foreach($the_query->posts as $_post){
                $_latest_ids[] = get_post_meta($_post->ID, '_em_id', true);
            }

            if(empty($_latest_ids))
                return null;

            return $_latest_ids;
        }

        /**
         * get post by item id
         * @param $item_id
         * @param $post_type
         * @return array
         */
        private function get_post_by_item_id($item_id, $post_type){

            /* get a post by meta. */
            $_post = new WP_Query(array(
                'post_type' => $post_type,
                'posts_per_page' => 1,
                'post_status' => 'any',
                'meta_query' => array(
                    array(
                        'key' => '_em_id',
                        'value'    => $item_id,
                    )
                )
            ));

            return $_post->posts;
        }

        /**
         * check new item
         *
         * @param $site
         * @param $category
         * @param $local_ids
         * @param int $page
         * @return array|null
         */
        private function check_envato_new_items($site, $category, $local_ids, $page = 1){

            if(empty($local_ids))
                return (object)array('error' => esc_html__('local_ids', 'envato-market'), 'error_description' => esc_html__('data null do not check', 'envato-market'));

            $items_data = $this->remote_get_enveto_items(array('site'=> $site . '.net', 'category'=> $category, 'page'=> $page, 'page_size' => 30, 'sort_by'=>'date', 'sort_direction'=>'desc'));

            /* is error. */
            if(isset($items_data->error))
                return $items_data;

            $new_ids = array();

            foreach($items_data->matches as $item){
                if(in_array($item->id, $local_ids))
                    break;
                $new_ids[] = $item->id;
            }

            /* next page. */
            if(count($new_ids) == 30 && $page < 3) {
                $page++;
                $new_ids = array_merge($new_ids, $this->check_envato_new_items($site, $category, $local_ids, $page));
            }

            return array_values($new_ids);
        }

        /**
         * update pending ids.
         *
         * @param $post_type
         * @param $taxonomy
         * @param $new_ids
         */
        private function update_pending_ids($post_type, $category, $new_ids){

            /* list item pending update. */
            $_schedule_list = get_option('_em_schedule_item_ids', array());

            if(isset($_schedule_list[$post_type][$category])){
                $_schedule_list[$post_type][$category] = array_unique(array_merge($_schedule_list[$post_type][$category], $new_ids));
            } else {
                $_schedule_list[$post_type][$category] = $new_ids;
            }

            update_option('_em_schedule_item_ids', $_schedule_list);
        }

        /**
         * convert envato attributes to array.
         *
         * @param $attributes
         * @return array
         */
        private function convert_envato_attributes($attributes){

            $_new_attr = array();

            foreach($attributes as $attr){
                $_new_attr[$attr->name] = $attr->value;
            }

            return $_new_attr;
        }

        /**
         * @param $_object
         * @return array
         */
        private function convert_envato_object($_object){
            $_new_attr = array();

            foreach($_object as $key => $val){
                $_new_attr[$key] = $val;
            }

            return $_new_attr;
        }

        /**
         * send email after get item.
         *
         * @param string $body
         */
        private function send_email_after_get_item($item_data){

            $_body = $_title = '';

            $_send_email = get_option('_em_settings_notices_after_get_item', false);

            /* if setting off. */
            if(!$_send_email)
                return;

            $_email = get_option('_em_settings_notices_email');

            /* if email null get blog email. */
            if(!$_email)
                $_email = get_bloginfo( 'admin_email' );

            /* mail content. */
            if(!isset($item_data->error)){
                $_title = $item_data->title;
                $_body .= "<table><tbody>";
                $_body .= "<th>TIME</th>";
                $_body .= "<th>STATUS</th>";
                $_body .= "<th>ITEM ID</th>";
                $_body .= "<th>NAME</th>";
                $_body .= "<th>LINK</th>";
                $_body .= "<tr>";
                $_body .= "<td>" . current_time( 'mysql' ) . "</td>";
                $_body .= "<td>Complete</td>";
                $_body .= "<td>{$item_data->item_id}</td>";
                $_body .= "<td>{$item_data->title}</td>";
                $_body .= "<td><a href='{$item_data->url}' target='_blank'>{$item_data->url}</a></td>";
                $_body .= "</tr>";
                $_body .= "</table></tbody>";
            } else {
                $_title = esc_html__('Error Code %s: ', 'envato-market') . $item_data->error;
                $_body = isset($item_data->description) ? $item_data->description : $item_data->error_description;
            }

            /* mail type. */
            add_filter( 'wp_mail_content_type', function( $content_type ) {
                return 'text/html';
            });

            /* send email. */
            wp_mail($_email, $_title, $_body);
        }

        /**
         * send email after get item.
         *
         * @param string $body
         */
        private function send_email_after_check_new($items_id, $site){
            $_send_email = get_option('_em_settings_notices_after_check_new_item', false);

            if(!$_send_email)
                return;

            $_email = get_option('_em_settings_notices_email');

            if(!$_email)
                $_email = get_bloginfo( 'admin_email' );

            wp_mail($_email, sprintf(esc_html__('%s - %u items add to pending list', 'envato-market'), ucfirst($site), count($items_id)), implode(',', $items_id));
        }

        private function remote_get_enveto_items($options = array()){

            /* get access-token. */
            $api_key = get_option('_envato-market-access-token');

            /* if api null. */
            if(!$api_key)
                return (object)array('error' => esc_html__('API', 'envato-market'), 'error_description' => esc_html__('you can update your access token', 'envato-market'));

            // get metadata from evanto
            $response = wp_remote_get("https://api.envato.com/v1/discovery/search/search/item?" . http_build_query($options) . "&access_token={$api_key}", array(
                'timeout' => 30,
                'httpversion' => '1.1'
            ));

            /* if remote error. */
            if (is_wp_error($response))
                return (object)array('error' => esc_html__('wp_remote_get()', 'envato-market'), 'error_description' => esc_html__('network', 'envato-market'));

            /* get data. */
            $data = json_decode($response['body']);

            /* json data null. */
            if(empty($data))
                return (object)array('error' => esc_html__('data null', 'envato-market'), 'error_description' => esc_html__('server or api down', 'envato-market'));

            return $data;
        }

        private function remote_get_envato_item($item_id){

            /* get access-token. */
            $api_key = get_option('_envato-market-access-token');

            /* if api null. */
            if(!$api_key)
                return (object)array('error' => esc_html__('API', 'envato-market'), 'error_description' => esc_html__('you can update your access token', 'envato-market'));

            /* get metadata from envato. */
            $response = wp_remote_get("https://api.envato.com/v3/market/catalog/item?id={$item_id}&access_token={$api_key}", array(
                'timeout' => 30,
                'httpversion' => '1.1'
            ));

            /* if remote error. */
            if (is_wp_error($response))
                return (object)array('error' => esc_html__('wp_remote_get()', 'envato-market'), 'error_description' => esc_html__('network', 'envato-market'));

            /* get data. */
            $data = json_decode($response['body']);

            /* json data null. */
            if(empty($data))
                return (object)array('error' => esc_html__('data null', 'envato-market'), 'error_description' => esc_html__('server or api down', 'envato-market'));

            return $data;
        }
    }
endif;

new Envato_market_envato();