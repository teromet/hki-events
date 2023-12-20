<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\HE_Admin_Page as Admin_Page;

/**
 * HE_Admin_Page
 *
 * Custom settings page to Admin Menu
 *
 */
class HE_Settings_Page extends Admin_Page {

    protected function get_slug() {
        return 'hki_events_settings';
    }

}