<?php

namespace HkiEvents\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Admin\Section;

/**
 * SettingsPage class.
 *
 * Custom settings page to Admin Menu
 * 
 */
class SettingsPage extends AdminPage {

    /**
     * @var Section[]
     * 
     * Page sections.
     */     
    private $sections = array();

    /**
     * Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'update_option', array( $this, 'after_update' ), 10, 1 );

    }

    protected function get_slug() {
        return 'hki_events_settings';
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

    protected function get_icon_url() {
        return 'dashicons-groups';
    }
    protected function get_position() {
        return 90;
    }

    public function register_settings() {

        require_once( HE_DIR . '/inc/settings-config.php' );

        // Add sections
        if ( ! empty ( $settings_sections ) ) {
            foreach ( $settings_sections as $section ) {
                $this->add_section( $section );
            }
        }

    }

    /**
     * Empty the value of last_fetched when settings are saved
     * 
     * @param string $option
     */
    public function after_update( $option ) {

        if ( $option !== 'hki_events_last_fetched' && substr( $option, 0, 11 ) === 'hki_events_' ) {
            update_option( 'hki_events_last_fetched', '' );
        }
    }

    /**
     * Add section to admin page
     * 
     * @param array $section
     */
    private function add_section( $section ) {

        if ( ! empty ( $section['id'] ) && ! empty ( $section['title'] ) ) {
            $section_obj = new Section( $section['id'], $section['title'], $section['fields'], $this->get_slug(), '' );
            $this->sections[] = $section_obj;
        }

    }

}