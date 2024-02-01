<?php

/**
 * Plugin Name: Helsinki tapahtumat
 * Plugin URI: 
 * Description: 
 * Version: 0.1
 * Author: Tero Metsänen
 **/

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( file_exists( dirname( __FILE__ ) . "/vendor/autoload.php" ) ) {
    require_once dirname( __FILE__ ) . "/vendor/autoload.php";
}

require_once dirname( __FILE__ ) . "/inc/autoloader.php";

class HkiEvents {

    private static $instance;

    private function __construct()
    {
        // setup variables
        define( 'HE_VERSION', '1.0.0' );
        define( 'HE_DIR', dirname( __FILE__ ) );
        define( 'HE_URL', plugins_url( '', __FILE__ ) );
        define( 'HE_BASENAME', plugin_basename( __FILE__ ) );
        define( 'HE_POST_TYPE', 'hki_event' );
        define( 'HE_TAXONOMY', 'hki_event_tag' );
        define( 'HE_SHORTCODE', 'hki_events' );

        // initialize
        $init = new HE_Init();
        $init->hook_into_wp();

    }

    /**
     * Singleton
     *
     * @return HkiEvents
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }
    
}

function HkiEvents() {
    return HkiEvents::get_instance();
}

HkiEvents();