<?php

namespace HkiEvents\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AdminPage base class.
 * 
 * This section is heavily inspired by https://pressidium.com/blog/oop-wordpress-plugin-object-oriented-programming-overview-tutorial/ as of Feb 2024
 * 
 */
abstract class AdminPage {

    /**
     * Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'updated_option', array( $this, 'after_update' ) );

    }

    abstract protected function get_slug();
    abstract protected function register_settings();
    abstract protected function get_page_title();
    abstract protected function get_menu_title();
    abstract protected function get_capability();
    abstract protected function get_position();

    abstract public function after_update();

    /**
     * Return the menu icon to be used for this menu.
     *
     * @link https://developer.wordpress.org/resource/dashicons/
     *
     * @return string
     */
    protected function get_icon_url() {
        return 'dashicons-admin-generic';
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
    
}