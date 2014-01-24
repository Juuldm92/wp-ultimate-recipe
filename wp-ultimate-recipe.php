<?php
/*
Plugin Name: WP Ultimate Recipe
Plugin URI: http://www.wpultimaterecipeplugin.com
Description: WP Ultimate Recipe is a user friendly plugin for adding recipes to any of your posts and pages.
Version: 1.0.2
Author: Bootstrapped Ventures
Author URI: http://www.bootstrappedventures.com
License: GPLv2
*/
/*
 * Credit to subtlepatterns.com for background patterns.
 */

define( 'COMPATIBLE_PREMIUM_VERSION', '1.0.1' );
define( 'WPURP_VERSION', '1.0.2' );

class WPUltimateRecipe {
    
    protected $pluginName;
    protected $pluginDir;
    protected $pluginUrl;

    protected $premiumName;
    protected $premiumDir;
    protected $premiumUrl;

    protected $installed_addons;
    protected $wpurp_core;
    
    public function __construct()
    {
        $this->pluginName = 'wp-ultimate-recipe';
        $this->pluginDir = WP_PLUGIN_DIR . '/' . $this->pluginName;
        $this->pluginUrl = plugins_url() . '/' . $this->pluginName;

        $this->premiumName = 'wp-ultimate-recipe-premium';
        $this->premiumDir = WP_PLUGIN_DIR . '/' . $this->premiumName;
        $this->premiumUrl = plugins_url() . '/' . $this->premiumName;

        // Version
        update_option( $this->pluginName . '_version', WPURP_VERSION );

        // Textdomain
        load_plugin_textdomain($this->pluginName, false, basename( dirname( __FILE__ ) ) . '/lang/'  );
        
        //Include core
        include_once( $this->pluginDir . '/core-functions.php' );
        $this->wpurp_core = new WPURP_Core( $this->pluginName, $this->pluginDir, $this->pluginUrl );

        // Hooks
        register_activation_hook( __FILE__, array( $this->wpurp_core, 'activate_taxonomies' ) );
        register_activation_hook( __FILE__, array( $this, 'wpurp_check_premium' ) );

        // Actions
        // add_action( 'init', array( $this, 'load_installed_addons' ), -10 ); // Put this in core-functions
        add_action( 'after_setup_theme', array( $this, 'wpurp_admin_menu' ) );
        add_action( 'after_setup_theme', array( $this, 'wpurp_shortcodegenerator' ) );
        add_action( 'init', array( $this, 'wpurp_check_premium' ) );
        add_action( 'admin_init', array( $this, 'wpurp_hide_notice' ) );
        add_action( 'wp_print_scripts', array( $this, 'wpurp_styles' ), 99 ); // Not wp_print_styles because we need this to be the last outputted css
        add_action( 'wp_footer', array( $this, 'wpurp_scripts' ) );
        add_action( 'admin_head', array( $this, 'wpurp_admin_styles' ) );
        add_action( 'admin_footer', array( $this, 'wpurp_admin_scripts' ) );
        add_action( 'admin_notices', array( $this, 'wpurp_admin_notices' ) );
        add_action( 'admin_footer-recipe_page_wpurp_admin', array( $this, 'support_tab' ) );

        // Other
        if ( function_exists( 'add_image_size' ) ) {
            add_image_size( 'recipe-thumbnail', 150, 9999 );
            add_image_size( 'recipe-large', 600, 9999 );
        }

    }
    
    /*
     * ================================================================================================================
     * @FRAMEWORK
     * ================================================================================================================
     */

    public function wpurp_admin_notices()
    {
        if(get_current_screen()->id == 'recipe_page_wpurp_admin' && get_user_meta( get_current_user_id(), '_wpurp_hide_notice', true ) != get_option($this->pluginName . '_version')) {
            include($this->pluginDir . '/helper/drip_form.php');
        }
    }

    function wpurp_hide_notice()
    {
        if ( ! isset( $_GET['wpurp_hide_notice'] ) ) {
            return;
        }

        check_admin_referer( 'wpurp_hide_notice', 'wpurp_hide_notice' );
        update_user_meta( get_current_user_id(), '_wpurp_hide_notice', get_option($this->pluginName . '_version') );
    }

    public function wpurp_check_premium()
    {

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        if( is_plugin_active( 'wp-ultimate-recipe-premium/wp-ultimate-recipe-premium.php' ) ) {
            $plugin_data = get_plugin_data( $this->premiumDir . '/' . $this->premiumName . '.php' );
            $plugin_version = $plugin_data['Version'];

            if ($plugin_version < COMPATIBLE_PREMIUM_VERSION) {
                $message = __( 'Please update WP Ultimate Recipe Premium to at least version', $this->pluginName ) . ' ' . COMPATIBLE_PREMIUM_VERSION;
            }
        }

        if( !empty( $message ) ) {

            deactivate_plugins( plugin_basename( $this->premiumDir . '/' . $this->premiumName . '.php' ) );
            wp_die( $message, 'WP Ultimate Recipe Premium', array( 'back_link' => true ) );

        }

    }

    /*
     * Load all available addons - Just duplicated this because I didn't feel like thinking. Sorry. - Brecht
     */
    public function load_installed_addons()
    {

        $addons_dir = WP_PLUGIN_DIR . '/' . $this->pluginName . '-premium' . '/addons'; // Such solution. Wow.

        if( !is_dir( $addons_dir ) ) {
            return; // Should probably spam them into buying right here.
        } else {
            $dirContent = scandir($addons_dir);

            foreach ($dirContent as $folder) {

                if ($folder != '.' && $folder != '..') {
                    $this->installed_addons[$folder] = true;
                }
            }

        }
    }

    /*
     * WP Ultimate Recipe Settings page
     */
    public function wpurp_admin_menu()
    {
        require_once('helper/admin_menu_helper.php');
        require_once('template/admin.php');

        new VP_Option(array(
            'is_dev_mode'           => false,
            'option_key'            => 'wpurp_option',
            'page_slug'             => 'wpurp_admin',
            'template'              => $admin_menu,
            'menu_page'             => 'edit.php?post_type=recipe',
            'use_auto_group_naming' => true,
            'use_exim_menu'         => true,
            'minimum_role'          => 'manage_options',
            'layout'                => 'fluid',
            'page_title'            => __( 'Admin', $this->pluginName ),
            'menu_label'            => __( 'Admin', $this->pluginName ),
        ));
    }

    public function support_tab()
    {
        //var_dump(get_current_screen());
        include($this->pluginDir . '/helper/support_tab.html');
    }

    public function wpurp_shortcodegenerator()
    {
        require_once('template/shortcode_generator.php');

        new VP_ShortcodeGenerator(array(
            'name'           => 'wpurp_shortcode_generator',
            'template'       => $shortcode_generator,
            'modal_title'    => 'WP Ultimate Recipe ' . __( 'Shortcodes', $this->pluginName ),
            'button_title'   => 'WP Ultimate Recipe',
            'types'          => array( 'post', 'page' ),
            'main_image'     => $this->pluginUrl . '/img/icon_20.png',
            'sprite_image'   => $this->pluginUrl . '/img/icon_sprite.png',
        ));
    }

    public function option( $name, $default = null )
    {
        $option = vp_option( "wpurp_option." . $name );

        return is_null($option) ? $default : $option;
    }
    
    /*
     * Add inline styles and scripts from addons
     */
    
    public function wpurp_styles() { //front end CSS
        $styles  = '<style type="text/css" media="screen">';
        ob_start();
        do_action( 'wpurp_styles' );
        $styles .= ob_get_clean();
        $styles .= '</style>';        

        $output = trim(preg_replace('/\s\s+/', ' ', $styles));
        echo $output;
    }
    
    public function wpurp_scripts() { //front end JS
        $scripts  = '<script type="text/javascript">';
        ob_start();
        do_action( 'wpurp_scripts' );
        $scripts .= ob_get_clean();
        $scripts .= '</script>';
        
        $output = trim(preg_replace('/\s\s+/', ' ', $scripts));
        echo $output;
    }
    
    public function wpurp_admin_styles() { //admin CSS
        $styles  = '<style type="text/css" media="screen">';
        ob_start();
        do_action( 'wpurp_admin_styles' );
        $styles .= ob_get_clean();
        $styles .= '</style>';
        
        $output = trim(preg_replace('/\s\s+/', ' ', $styles));
        echo $output;
    }
    
    public function wpurp_admin_scripts() { //admin JS
        $scripts  = '<script type="text/javascript">';
        ob_start();
        do_action( 'wpurp_admin_scripts' );
        $scripts .= ob_get_clean();
        $scripts .= '</script>';
        
        $output = trim(preg_replace('/\s\s+/', ' ', $scripts));
        echo $output;
    }
      
    /*
     * Returns array of all recipes
     */
    protected function get_recipes( $orderby = 'date', $order = 'DESC', $taxonomy = '', $term = '', $limit = -1 ) {
        $args = array(
            'post_type' => 'recipe',
            'post_status' => 'publish',
            'orderby' => $orderby,
            'order' => $order,
            'posts_per_page' => $limit,
        );

        if( is_null($limit) || $limit == -1 ) {
            $args['nopaging'] = true;
        }
        
        if( $taxonomy && !$term ) {
            $args['tax_query'] = array(
                'taxonomy' => $taxonomy,
            );
        }
        
        if( $taxonomy && $term ) {
            if( $taxonomy == 'category' ) {
                $args['category_name'] = $term;
            } else if ( $taxonomy == 'post_tag' ) {
                $args['tag'] = $term;
            } else {
                $args[$taxonomy] = $term;
            }
        }
        
        $query = new WP_Query( $args );
        
        if( $query->have_posts() ) { //recipes found
            
            $recipes = array();
            
            while( $query->have_posts() ) {
                $query->the_post();
                global $post;
                $recipes[] = $post;
            }
        }

        if( $orderby == 'post_title' || $orderby == 'title' || $orderby == 'name' ) {
            usort($recipes, array($this, "compare_post_titles"));

            if( $order == 'DESC' ) {
                $recipes = array_reverse($recipes);
            }
        }

        return $recipes;
    }

    /*
     * TODO - This is probably not that performant but does the job for now
     */
    protected function compare_post_titles($a, $b)
    {
        return strcmp($this->get_recipe_title($a), $this->get_recipe_title($b));
    }
    
    /*
     * Used in various places.
     */
    protected function recipes_fields() {
        return array(
            'recipe_title',
            'recipe_description',
            'recipe_rating',
            'recipe_servings',
            'recipe_servings_type',
            'recipe_prep_time',
            'recipe_cook_time',
            'recipe_passive_time',
            'recipe_ingredients',
            'recipe_instructions',
            'recipe_notes',
        );
    }
    
    /*
     * Check if shortcode is present in current post/page
     * Only works inside The Loop.
     */
    public function check_for_shortcode( $shortcode, $post = '' ) {
        if( $post == '' ) {
            global $post;
        }
        if( function_exists( 'has_shortcode' ) ) {
            
            if( isset($post->post_content) AND has_shortcode( $post->post_content, $shortcode )) { 
                return true;
            } 
            return false;
        }
        return true; //in older versions of WP we'll just have to enqueue everything :(
    }
    
    /* 
     * Checks whether given taxonomy is in use.
     * Returns true if more than one term used.
     */
    protected function site_is_using( $taxonomy = '' ) {
        $terms_used = get_terms( $taxonomy );
        if( count( $terms_used ) > 1 ) {
            return true;
        }
        return false;
    }
    
    /*
     * Permission checks for users.
     * Prevents future changes to permission names from breaking addons.
     * 
     * Example usage if capability type in core changed to "recipe":
     * wpurp_user_can( 'edit_posts' );
     * Will check for edit_recipes capability.
     * 
     */
    protected function wpurp_user_can( $user_id = '', $capability = '' ) {
        if( '' == $user_id || '' == $capability ) {
            return false;
        }
        
        if( $GLOBALS['wp_post_types']['recipe']['cap']->$capability ) {
            $wpurp_cap = $GLOBALS['wp_post_types']['recipe']['cap']->$capability;  
            return user_can( $user_id, $wpurp_cap );
        }  
        
        return false;
    }
    
    protected function wpurp_current_user_can( $capability = '') {
        if( '' == $capability ) {
            return false;
        }
        //echo '<pre>'.print_r($GLOBALS['wp_post_types']['recipe']->cap->$capability, true).'</pre>';
        if( isset( $GLOBALS['wp_post_types']['recipe']->cap->$capability ) ) {
            $wpurp_cap = $GLOBALS['wp_post_types']['recipe']->cap->$capability;  


            $args = array_slice( func_get_args(), 1 );
            $args = array_merge( array( $wpurp_cap ), $args );

            return current_user_can( $wpurp_cap, $args );
        }  
        
        return false;
    }

    public function get_recipe_title( $recipe )
    {
        $meta = get_post_custom($recipe->ID);

        if (!is_null($meta['recipe_title'][0]) && $meta['recipe_title'][0] != '') {
            return $meta['recipe_title'][0];
        } else {
            return $recipe->post_title;
        }
    }

}

require_once('lib/vafpress/bootstrap.php');
$wpurp = new WPUltimateRecipe();