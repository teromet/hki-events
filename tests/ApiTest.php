<?php

namespace HkiEvents;

use HkiEvents\API;
use PHPUnit\Framework\TestCase;

/**
 * @covers HkiEvents\API
 */
class ApiTest extends TestCase {

    public $api;
    public $mock_url;
    public $params;

    /**
     * @before 
     */
    protected function setUp(): void {

        $this->api = new API();
        $this->mock_url = 'https://api.test.fi/loremipsum/v1/test/';

        $this->params = array( 
            'start'                     => '2024-01-01',
            'end'                       => '2024-07-01',
            'sort'                      => 'end_time',
            'page'                      => '1',
            'hide_recurring_children'   => 'true'
        );

    }

    public function test_get_all_returnsStdClassObject() {

        add_filter( 'pre_http_request', array( $this, 'return_valid_response' ),  10, 3 );

        $result = $this->api->get_all( $this->mock_url, $this->params );
        $this->assertIsObject( $result );
        $this->assertInstanceOf( \stdClass::class, $result );

        remove_filter( 'pre_http_request', array( $this, 'return_valid_response' ) );

    }

    public function test_get_all_returnsFalseOnError() {

        add_filter( 'pre_http_request', array( $this, 'return_wp_error' ),  10, 3 );

        $result = $this->api->get_all( $this->mock_url, $this->params );
        $this->assertFalse( $result );

        remove_filter( 'pre_http_request', array( $this, 'return_wp_error' ) );

    }

    public function test_get_all_returnsFalseOnEmptyUrl() {

        $result = $this->api->get_all( '', $this->params );
        $this->assertFalse( $result );

    }

    public function test_get_by_id_returnsStdClassObject() {

        add_filter( 'pre_http_request', array( $this, 'return_valid_response' ),  10, 3 );

        $result = $this->api->get_by_id( $this->mock_url, 5 );
        $this->assertIsObject( $result );
        $this->assertInstanceOf( \stdClass::class, $result );

        remove_filter( 'pre_http_request', array( $this, 'return_valid_response' ) );

    }

    public function test_get_by_id_returnsFalseOnError() {

        add_filter( 'pre_http_request', array( $this, 'return_wp_error' ),  10, 3 );

        $result = $this->api->get_by_id( $this->mock_url, 5 );
        $this->assertFalse( $result );

        remove_filter( 'pre_http_request', array( $this, 'return_wp_error' ) );

    }

    public function test_get_by_id_returnsFalseOnEmptyUrl() {

        $result = $this->api->get_by_id( '', 5 );
        $this->assertFalse( $result );

    }

    public function return_wp_error() {
        return new \WP_Error();
    }

    public function return_valid_response() {
        return array(
            'headers'     => array(),
            'cookies'     => array(),
            'filename'    => null,
            'response'    => 200,
            'status_code' => 200,
            'success'     => 1,
            'body'        => '{"data":[{"id":"test"}]}'
        );
    }

}


