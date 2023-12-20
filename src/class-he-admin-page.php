<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * HE_Admin_Page
 *
 * Custom admin page base class
 *
 */
abstract class HE_Admin_Page {

    /**
     * Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

    }

    protected function get_page_title() {
        return __( 'Tapahtumien tuonti', 'hki_events' );
    }
    protected function get_menu_title() {
        return __( 'Tapahtumien tuonti', 'hki_events' );
    }
    protected function get_capability() {
        return 'manage_options';
    }

    abstract protected function get_slug();

    protected function get_icon_url() {
        return 'dashicons-admin-generic';
    }
    protected function get_position() {
        return 90;
    }

    /**
     * Add admin page as top-menu item
     */
    public function add_page() {
        add_menu_page(
            $this->get_page_title(),    // page_title
            $this->get_menu_title(),    // menu_title
            $this->get_capability(),    // capability
            $this->get_slug(),          // menu_slug
            array( $this, 'render' ),   // callback function
            $this->get_icon_url(),      // icon_url
            $this->get_position()       // position
        );
    }

    /**
     * Render this admin page.
     */
    public function render() {
        ?>

        <div class="wrap">
            <form action="options.php" method="post">
                <h1><?php echo esc_html( $this->get_page_title() ); ?></h1>
                <?php
                settings_fields( $this->get_slug() );
                do_settings_sections( $this->get_slug() );
                submit_button( __( 'Tallenna', 'hki_events' ) );
                ?>
            </form>
        </div>

        <?php
    }

    public function register_settings() {

        register_setting( $this->get_slug(), 'hki_events_api_start_date' );

        add_settings_section( 
            'hki_events_settings_api_params', 
            __( 'Linked Events API parametrit', 'hki-events-settings-item-sub' ), 
            array( $this, 'render_section' ), 
            $this->get_slug()
        );

        add_settings_field( 
            'html_element', 
            __( 'Aloitusaika:', 'hki-events-settings-item-sub' ),
            array( $this, 'render_field' ), 
            $this->get_slug(),
            'hki_events_settings_api_params'
        );

    }


    public function render_section() {
        ?>
        <h2><?php _e( '', 'hki-events-settings-item-sub' ); ?></h2>
        <?php
    }

    public function render_field() {
        $stored_option = get_option( 'hki_events_api_start_date' );
        ?>
        <input type="text" name="hki_events_api_start_date" value="<?php echo $stored_option; ?>" />
        <?php
    }
}