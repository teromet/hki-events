<?php

namespace HkiEvents;

use HkiEvents\Api\ApiInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers HkiEvents\Api\ApiInterface;
 * 
 * TODO: test get_events
 */
class ApiInterfaceTest extends TestCase {

    public $api;

    /**
     * @before 
     */
    protected function setUp(): void {

        $this->api = new ApiInterface();

    }

    public function test_get_keyword_returnsFalseOnIncorrectIdFormat() {

        $result = $this->api->get_keyword( '252525');
        $this->assertFalse( $result );

        $result = $this->api->get_keyword( null );
        $this->assertFalse( $result );

    }

    public function test_get_keyword_returnsFalseOnApiError() {

        add_filter( 'pre_http_request', 'he_return_wp_error',  10, 3 );

        $result = $this->api->get_keyword( 'yso:p1244' );
        $this->assertFalse( $result );

        remove_filter( 'pre_http_request', 'he_return_wp_error' );

    }

}


