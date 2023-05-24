<?php

/**
 * @package DracoPlugin
 * 
 * Plugin Name: Search Everything for Woocommerce and WordPress
 * Description: Plugin allows you search everything that you configured.
 * Author: Draco WordPress
 * Author URI: http://localhost/wordpress_plugin_1/
 * Text-Domain: dra-search-everything
 * Domain Path: /languages/
 * License: GPLv2 or later
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SE_DF_VERSION', '1.0.0');
define('SE_DF_DIR', __FILE__);
define('DOMAIN', 'dra-search-everything');
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * Class Dra_Search_Everything
 */
if (!class_exists('Dra_Search_Everything')) {

    class Dra_Search_Everything
    {

        public function __construct()
        {
            add_action('admin_menu', [$this, 'setup_menu_admin']);
            add_action('admin_init', [$this, 'register_setting_admin']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'apd_settings_link']);
        }
        public function apd_settings_link($links)
        {
            $urlSetting  = get_admin_url() . 'options-general.php?page=search-everything';
            $settingLink = '<a href="' . $urlSetting . '">' . __('Settings', DOMAIN) . '</a>';
            array_push($links, $settingLink);
            return $links;
        }
        public function setup_menu_admin()
        {
            // add_options_page( 'Settings Admin', 
            // 'My Settings', 
            // 'manage_options', 
            // 'my-setting-admin', 
            // array( $this, 'display_admin_menu' ));
            // add_menu_page(__('Search Everything for Woocommerce and WordPress Plugin', DOMAIN), __('Search Everything', DOMAIN), 'manage_options', 'search-everything', [$this, 'display_admin_menu'], 'dashicons-search', 20);
            add_submenu_page('options-general.php',
                __('Search Everything Options', DOMAIN),
                __('Search Everything Options', DOMAIN),
                'manage_options',
                'search-everything',
                [$this, 'display_admin_menu']
            );
        }
        function display_admin_menu()
        {
            ?>
            <div class="wrap">
                <h1><?php echo esc_html__('Search everything configurations (version' . SE_DF_VERSION . ')', DOMAIN) ?></h1>
                <form method="post" action="options.php" class="ui form">
                    <?php 
                        settings_fields( 'search-everything' );
                        do_settings_sections( 'search-everything' );
                    ?>
                    <div class="submit">
                        <input type="submit" name="submit" id="submit" value="Save Changes" class="ui primary button">
                    </div>
                </form>
            </div>
            <?php
            // require_once plugin_dir_path(SE_DF_DIR) . 'templates/admin.php';
        }
        public function register_setting_admin()
        {
            $taxonomies = get_taxonomies();
            register_setting('search-everything', 'search_everything_option');
            add_settings_section(
                'setting_section_id', // ID
                __('Search configurations', DOMAIN),
                [], // Callback
                'search-everything' // Page
            );
            add_settings_field(
                'enable_search_feature',
                __('Enable tool search everything on screen', DOMAIN),
                [$this, 'enable_search_feature'],
                'search-everything',
                'setting_section_id'
            );
            add_settings_field(
                'select_items_search', 
                __("We've selected everything for search", DOMAIN),
                [ $this, 'some_items_select' ], 
                'search-everything',
                'setting_section_id',
                $taxonomies
            );
        }
        public function some_items_select($args)
        {
            $options = get_option('search_everything_option', []);
            foreach($args as $val) {
                $default = isset($options[$val]) ? (array)$options[$val] : [];
                $labelName = esc_html__(ucwords(str_replace('_', ' ', $val)), DOMAIN); 
                printf(
                    '<div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="%1$s[%2$s]" value="%2$s" %3$s>
                            <label>%4$s</label>
                        </div>
                    </div>',
                    'search_everything_option',
                    $val,
                    checked(in_array($val, $default), 1, false),
                    $labelName
                );
            }
        }
        public function enable_search_feature()
        {
            $options = get_option('search_everything_option');
            $default = isset($options['enable_search_feature']) ? $options['enable_search_feature'] : 0;
            printf(
                '<div class="ui toggle checkbox">
                    <input type="checkbox" name="%1$s[enable_search_feature]" value="1" %2$s>
                    <label></label>
                </div>',
                'search_everything_option',
                checked($default, 1, false)
            );
        }
        public function admin_enqueue_scripts()
        {
            wp_enqueue_style('semantic-ui-button', plugins_url('assets/css/button.min.css', __FILE__));
            wp_enqueue_style('semantic-ui-input', plugins_url('assets/css/input.min.css', __FILE__));
            wp_enqueue_style('semantic-ui-form', plugins_url('assets/css/form.min.css', __FILE__));
            wp_enqueue_style('semantic-ui-checkbox', plugins_url('assets/css/checkbox.min.css', __FILE__));
            wp_enqueue_style('custom-css', plugins_url('assets/css/custom.css', __FILE__));
            wp_enqueue_script('semantic-ui-checkbox', plugins_url('assets/js/checkbox.min.js', __FILE__), 'jquery', false);
            wp_enqueue_script('semantic-ui-form', plugins_url('assets/js/form.min.js', __FILE__), 'jquery', false);
        }
    }
    new Dra_Search_Everything();
}
