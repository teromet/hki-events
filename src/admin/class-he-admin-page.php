<?php

namespace HkiEvents\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AdminPage class.
 * 
 * TODO: Create classes for Sections and Fields
 * 
 */
abstract class AdminPage {

    /**
     * Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

    }

    protected function get_page_title() {
        return __( 'Helsinki Events', 'hki_events' );
    }

    protected function get_menu_title() {
        return __( 'Helsinki Events', 'hki_events' );
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
                submit_button( __( 'Save', 'hki_events' ) );
                ?>
            </form>
        </div>

        <?php
    }

    public function register_settings() {

        register_setting( $this->get_slug(), 'hki_events_api_start_time' );
        register_setting( $this->get_slug(), 'hki_events_api_last_fetched' );

        add_settings_section( 
            'hki_events_settings_api_params', 
            __( 'Linked Events API parameters', 'hki-events-settings-item-sub' ), 
            array( $this, 'render_section' ), 
            $this->get_slug()
        );

        add_settings_field( 
            'api_start_time', 
            __( 'Start time:', 'hki-events-settings-item-sub' ),
            array( $this, 'render_field_start_time' ), 
            $this->get_slug(),
            'hki_events_settings_api_params'
        );

        add_settings_field( 
            'api_last_fetched', 
            __( 'Last fetched:', 'hki-events-settings-item-sub' ),
            array( $this, 'render_field_last_fetched' ), 
            $this->get_slug(),
            'hki_events_settings_api_params'
        );

    }

    public function render_section() {
        ?>
        <h2><?php _e( '', 'hki-events-settings-item-sub' ); ?></h2>
        <?php
    }

    public function render_field_start_time() {
        $stored_option = get_option( 'hki_events_api_start_time' );
        ?>
        <input type="text" name="hki_events_api_start_time" value="<?php echo $stored_option; ?>" />
        <?php
    }

    public function render_field_last_fetched() {
        $stored_option = get_option( 'hki_events_api_last_fetched' );
        ?>
        <input type="text" name="hki_events_api_last_fetched" value="<?php echo $stored_option; ?>" />
        <?php
    }
    
}