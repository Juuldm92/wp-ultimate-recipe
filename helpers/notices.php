<?php

class WPURP_Notices {

    public function __construct()
    {
        add_action( 'admin_init',       array( $this, 'wpurp_hide_notice' ) );
        add_action( 'admin_notices',    array( $this, 'wpurp_admin_notices' ) );
    }

    public function wpurp_admin_notices()
    {
        if( get_current_screen()->id == 'recipe_page_wpurp_admin' && get_user_meta( get_current_user_id(), '_wpurp_hide_notice', true ) != get_option( WPUltimateRecipe::get()->pluginName . '_version' ) ) {
            include(WPUltimateRecipe::get()->coreDir . '/static/drip_form.php');
        }

        if( $notices = get_option( 'wpurp_deferred_admin_notices' ) ) {
            foreach( $notices as $notice ) {
                echo '<div class="updated"><p>'.$notice.'</p></div>';
            }

            delete_option('wpurp_deferred_admin_notices');
        }
    }

    public function add_admin_notice( $notice )
    {
        $notices = get_option( 'wpurp_deferred_admin_notices', array() );
        $notices[] = $notice;
        update_option( 'wpurp_deferred_admin_notices', $notices );
    }

    function wpurp_hide_notice()
    {
        if ( ! isset( $_GET['wpurp_hide_notice'] ) ) {
            return;
        }

        check_admin_referer( 'wpurp_hide_notice', 'wpurp_hide_notice' );
        update_user_meta( get_current_user_id(), '_wpurp_hide_notice', get_option( WPUltimateRecipe::get()->pluginName . '_version') );
    }

    public function activation_notice() {
        $notice  = '<strong>WP Ultimate Recipe</strong><br/>';
        $notice .= '<a href="'.admin_url( 'edit.php?post_type=recipe&page=wpurp_admin#_latest_news' ).'">Check out our latest changes in your <strong>Recipes > Settings</strong> panel</a>';

        $this->add_admin_notice( $notice );
    }
}