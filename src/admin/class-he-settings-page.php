<?php

namespace HkiEvents\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Admin\AdminPage;

/**
 * SettingsPage class.
 *
 * Custom settings page to Admin Menu
 * 
 *
 */
class SettingsPage extends AdminPage {

    protected function get_slug() {
        return 'hki_events_settings';
    }

}