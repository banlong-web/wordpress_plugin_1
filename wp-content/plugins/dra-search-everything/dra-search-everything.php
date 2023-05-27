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
        private $query_instance;
        public function __construct()
        {
            add_action('admin_menu', [$this, 'setup_menu_admin']);
            add_action('admin_init', [$this, 'register_setting_admin']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'apd_settings_link']);
            add_filter('posts_join', [$this, 'dra_se_terms_join'], 10, 2);
            add_filter('posts_search', [$this, 'dra_se_search_where'], 10, 2);
            add_filter('posts_request', [$this,'dra_se_distinct']);
        }
        // creates the list of search keywords from the 's' parameters.
        function dra_se_get_search_terms() {
            global $wpdb;
            $s = isset($this->query_instance->query_vars['s']) ? $this->query_instance->query_vars['s'] : '';
            $sentence = isset( $this->query_instance->query_vars['sentence'] ) ? $this->query_instance->query_vars['sentence'] : false;
            $search_terms = [];

            if ( !empty( $s ) ) {
                // added slashes screw with quote grouping when done early, so done later
                $s = stripslashes( $s );
                if ( $sentence ) {
                    $search_terms = array( $s );
                } else {
                    preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches );
                    $search_terms = array_filter(array_map( function($a){return trim($a, "\\\\n\\r ");}, $matches[0] ));
                    
                }
            }
            return $search_terms;
        }
        function dra_se_search_where( $where, $wp_query ) { 
            if(!$wp_query->is_search()) {
                return $where;
            }
            global $wpdb;
            $this->query_instance = $wp_query;
            $options = get_option('search_everything_option');
            $searchQuery = $this->dra_se_search_default();
            
            if(isset($options)) {
                if($options['category'] || $options['product_cat']) {
                    $searchQuery .= $this->dra_se_build_search_categories();
                }
            }
            if ( $searchQuery != '' ) {
                // lets use _OUR_ query instead of WP's, as we have posts already included in our query as well(assuming it's not empty which we check for)
                $where = " AND ((" . $searchQuery . ")) ";
            }
            return $where;
        }
        function  dra_se_distinct( $query ) {
            global $wpdb;
            if ( !empty( $this->query_instance->query_vars['s'] ) ) {
                if ( strstr( $query, 'DISTINCT' ) ) {}
                else {
                    $query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
                }
            }
            return $query;
        }
        function dra_se_search_default(){
            global $wpdb;
            $not_exact = empty($this->query_instance->query_vars['exact']);
            $search_sql_query = '';
            $seperator = '';
            $terms = $this->dra_se_get_search_terms();
            // if it's not a sentance add other terms
            $search_sql_query .= '(';
    
            foreach ( $terms as $term ) {
                $search_sql_query .= $seperator;
    
                $esc_term = $wpdb->prepare("%s", $not_exact ? "%".$term."%" : $terms);
    
                $like_title = "($wpdb->posts.post_title LIKE $esc_term)";
                $like_post = "($wpdb->posts.post_content LIKE $esc_term)";
                $like_post_type = "($wpdb->posts.post_type LIKE $esc_term)";
                $search_sql_query .= "($like_title OR $like_post OR $like_post_type)";
    
                $seperator = ' AND ';
            }
    
            $search_sql_query .= ')';
            return $search_sql_query;
        }
    
        public function dra_se_build_search_categories()
        {
            global $wpdb;
            $vars = $this->query_instance->query_vars;
		    $s = $vars['s'];
            $search_terms = $this->dra_se_get_search_terms();
            $search = '';
            $exact = isset( $vars['exact'] ) ? $vars['exact'] : '';
            if ( !empty( $search_terms ) ) { 
                $searchand = '';
			    $searchSlug = '';
                foreach ( $search_terms as $term ) {
                    $term = $wpdb->prepare("%s", $exact ? $term : "%". sanitize_title_with_dashes($term) . "%");
                    $searchSlug .= "{$searchand}(tter.slug LIKE $term)";
                    $searchand = ' AND ';
                }
                $term = $wpdb->prepare("%s", $exact ? $term : "%". sanitize_title_with_dashes($s) . "%");
                if ( count( $search_terms ) > 1 && $search_terms[0] != $s ) {
                    $searchSlug = "($searchSlug) OR (tter.slug LIKE $term)";
                }
                if ( !empty( $searchSlug ) )
				    $search = "OR ({$searchSlug})";

                // Building search query for categories description.
                $searchand = '';
                $searchDesc = '';
                foreach ( $search_terms as $term ) {
                    $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                    $searchDesc .= "{$searchand}(ttax.description LIKE $term)";
                    $searchand = ' AND ';
                }
                $sentence_term = $wpdb->prepare("%s", $s);
                if ( count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
                    $searchDesc = "($searchDesc) OR (ttax.description LIKE $sentence_term)";
                }
                if ( !empty( $searchDesc ) )
                    $search = $search." OR ({$searchDesc}) ";
            }
            return $search;
        }
        function dra_se_terms_join($join)
        {
            global $wpdb, $wp_query;
            $options = get_option('search_everything_option');
            if($wp_query->is_search()) {
                if(!empty($this->query_instance->query_vars['s'])) {
                    // $enable_feature = isset($options['enable_search_feature']) ? $options['enable_search_feature'] : 0;
                    // if($enable_feature) {
                        $taxonomies = get_taxonomies();
                        if(!empty($options)) {
                            foreach($options as $key => $val) {
                                if($key !== 'enable_search_feature') {
                                    $filter_taxonomies[] = $options[$val];
                                }
                            }
                        }
                        if(isset($taxonomies)) {
                            foreach($taxonomies as $taxonomy) {
                                if(isset($filter_taxonomies)) {
                                    if(in_array($taxonomy, $filter_taxonomies)) {
                                        if($options[$taxonomy]) {
                                            $on[] = "ttax.taxonomy = '" . addslashes( $taxonomy )."'";
                                        }
                                    }
                                }
                               
                            }
                        }
                      
                        if(isset($on)) {
                            $on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
                            $join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";  
                        }
                    // }
                }
            }
            return $join;
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
        }
        public function register_setting_admin()
        {
            $taxonomies = get_taxonomies();
            register_setting('search-everything', 'search_everything_option');
            // register_setting('search-everything', 'enable_search_feature');
            // add_settings_section(
            //     'enable_id',
            //     '',
            //     [],
            //     'search-everything',
            // );
            // add_settings_field(
            //     'enable_search_feature',
            //     __('Enable tool search everything on screen', DOMAIN),
            //     [$this, 'enable_search_feature'],
            //     'search-everything',
            //     'enable_id',
            //     [
            //         'id'            => 'enable_search_feature',
            //         'option_name'   => '1'
            //     ]
            // );
            add_settings_section(
                'setting_section_id', // ID
                __('Search configurations', DOMAIN),
                [], // Callback
                'search-everything' // Page
            );
            add_settings_field(
                'select_items_taxonomies', 
                __("We've selected everything for search", DOMAIN),
                [ $this, 'taxonomies_items_select' ], 
                'search-everything',
                'setting_section_id',
                [
                    'id'            => 'select_items_taxonomies',
                    'option_name'   => $taxonomies
                ]
            );
        }
        public function taxonomies_items_select($args)
        {
            $options = get_option('search_everything_option');
            $option_name = $args['option_name'];
            foreach ($option_name as $val) {
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
                    checked(in_array($val,$default), 1, false),
                    $labelName
                );
            }
        }
        // public function enable_search_feature($args)
        // {
        //     // print_r($args);
        //     $options = get_option('search_everything_option');
        //     print_r($options);
        //     $default = isset($options['enable_search_feature']) ? $args['option_name'] : 0;
        //     // echo $default;
        //     printf(
        //         '<div class="ui toggle checkbox">
        //             <input type="checkbox" name="%1$s[enable_search_feature]" value="1" %2$s>
        //             <label></label>
        //         </div>',
        //         'search_everything_option',
        //         checked($default, 1, false)
        //     );
        // }
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
