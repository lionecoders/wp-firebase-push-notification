<?php
/*
Plugin Name: Firebase Push Notification
Description: Firebase Push Notification
Version:3.2.1
Author:miraclewebssoft, deep7197
Author URI:http://www.miraclewebsoft.com
License:GPL2
License URI:https://www.gnu.org/licenses/gpl-2.0.html
*/
if (!defined('ABSPATH')) {
    exit;
}

if (!defined("FCM_VERSION_CURRENT")) define("FCM_VERSION_CURRENT", '3.2.1');
if (!defined("FCM_URL")) define("FCM_URL", plugin_dir_url( __FILE__ ) );
if (!defined("FCM_PLUGIN_DIR")) define("FCM_PLUGIN_DIR", plugin_dir_path(__FILE__));
if (!defined("FCM_PLUGIN_NM")) define("FCM_PLUGIN_NM", 'Firebase Push Notification');
if (!defined("FCM_TD")) define("FCM_TD", 'wp-firebase-push-notification');


Class Firebase_Push_Notification
{
    public $pre_name = 'fcm';

    public function __construct()
    {
        // Installation and uninstallation hooks
        register_activation_hook(__FILE__, array($this, $this->pre_name . '_activate'));
        register_deactivation_hook(__FILE__, array($this, $this->pre_name . '_deactivate'));
        add_action('admin_menu', array($this, $this->pre_name . '_setup_admin_menu'));
        add_action("admin_init", array($this, $this->pre_name . '_backend_plugin_js_scripts_filter_table'));
        add_action("admin_init", array($this, $this->pre_name . '_backend_plugin_css_scripts_filter_table'));
        add_action('admin_init', array($this, $this->pre_name . '_settings'));
        add_action('save_post', array($this, $this->pre_name . '_on_post_save'),10, 3);
        //add_action('init', array($this, $this->pre_name . '_custom_post_type'));

    }

    public function fcm_setup_admin_menu()
    {
        add_submenu_page('options-general.php', __('Firebase Push Notification', 'wp-firebase-push-notification'), 'Firebase Push Notification', 'manage_options', 'fcm_slug', array($this, 'fcm_admin_page'));

        add_submenu_page(null            // -> Set to null - will hide menu link
            , __('Test Notification', 'wp-firebase-push-notification')// -> Page Title
            , 'Test Notification'    // -> Title that would otherwise appear in the menu
            , 'administrator' // -> Capability level
            , 'test_notification'   // -> Still accessible via admin.php?page=menu_handle
            , array($this, 'fcm_test_notification') // -> To render the page
        );
    }

    public function fcm_admin_page()
    {
        include(plugin_dir_path(__FILE__) . 'views/dashboard.php');
    }

    function fcm_backend_plugin_js_scripts_filter_table()
    {
        wp_enqueue_script("jquery");
        wp_enqueue_script("fcm.js", FCM_URL . "assets/js/fcm.js", array('jquery'), FCM_VERSION_CURRENT, true);
    }

    function fcm_backend_plugin_css_scripts_filter_table()
    {
        wp_enqueue_style("fcm.css", FCM_URL . "assets/css/fcm.css", array(), FCM_VERSION_CURRENT);
    }

    public function fcm_activate()
    {

    }

    public function fcm_deactivate()
    {
    }


    function fcm_settings()
    {    //register our settings
        register_setting('fcm_group', 'stf_fcm_api', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('fcm_group', 'fcm_option', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('fcm_group', 'fcm_topic', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('fcm_group', 'fcm_disable', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('fcm_group', 'fcm_update_disable', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('fcm_group', 'fcm_page_disable', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('fcm_group', 'fcm_update_page_disable', array('sanitize_callback' => 'sanitize_text_field'));

    }

    function fcm_custom_post_type()
    {
        register_post_type('device_tokens',
            [
                'labels'      => [
                    'name'          => __('Device Tokens', 'wp-firebase-push-notification'),
                    'singular_name' => __('Device Token', 'wp-firebase-push-notification'),
                ],
                'public'      => true,
                'has_archive' => true,
            ]
        );
    }

    function fcm_on_post_save($post_id, $post, $update) {
        $from = get_bloginfo('name');
        $content = 'There are new post notification from '.$from;

        if(get_option('stf_fcm_api') && get_option('fcm_topic')) {
            //new post/page
            if (isset($post->post_status)) {

                if (!$update) {
                    if ($post->post_status == 'publish') {

                        if ($post->post_type == 'post' && get_option('fcm_disable') != 1) {
                            $this->fcm_notification($content);

                        } elseif ($post->post_type == 'page' && get_option('fcm_page_disable') != 1) {
                            $this->fcm_notification($content);
                        }


                    }

                } else {
                    //updated post/page
                    if ($post->post_status == 'publish') {
                        if ($post->post_type == 'post' && get_option('fcm_update_disable') != 1) {
                            $this->fcm_notification($content);
                        } elseif ($post->post_type == 'page' && get_option('fcm_update_page_disable') != 1) {
                            $this->fcm_notification($content);
                        }

                    }
                }
            }
        }

    }

    function fcm_test_notification(){
        $content = 'Test Notification from FCM Plugin';

        $result = $this->fcm_notification($content);

        echo '<div class="row">';
        echo '<div><h2>Debug Information</h2>';

        echo '<pre>';
        printf( '%s', esc_html( $result ) );
        echo '</pre>';

        echo '<p><a href="'. esc_url( admin_url('admin.php') ) .'?page=test_notification">Retry</a></p>';
        echo '<p><a href="'. esc_url( admin_url('admin.php') ) .'?page=fcm_slug">Home</a></p>';

        echo '</div>';
    }

    function fcm_notification($content){

        $topic =  "'".get_option('fcm_topic')."' in topics";
        $apiKey = get_option('stf_fcm_api');
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization' => 'key=' . $apiKey,
            'Content-Type'  => 'application/json'
        );
        $notification_data = array(    //// when application open then post field 'data' parameter work so 'message' and 'body' key should have same text or value
            'message'           => $content
        );

        $notification = array(       //// when application close then post field 'notification' parameter work
            'body'  => $content,
            'sound' => 'default'
        );

        $post = array(
            'condition'         => $topic,
            'notification'      => $notification,
            "content_available" => true,
            'priority'          => 'high',
            'data'              => $notification_data
        );
        //echo '<pre>';
        //var_dump($post);
        // Replace cURL with WP HTTP API
        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $headers,
            'body'        => json_encode( $post ),
            'cookies'     => array(),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        $result = wp_remote_retrieve_body( $response );

        return $result;

        //var_dump($result); die;

    }


}

$Firebase_Push_Notification_OBJ = new Firebase_Push_Notification();
