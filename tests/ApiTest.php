<?php

namespace HkiEvents;

use HkiEvents\Api\API;
use PHPUnit\Framework\TestCase;
use HkiEvents\Exceptions\HttpRequestFailedException;

/**
 * @covers HkiEvents\Api\API
 */
class ApiTest extends TestCase {

    public $api;
    public $url;
    public $params;

    /**
     * @before 
     */
    protected function setUp(): void {

        $this->api = new API();
        $this->url = 'https://api.test.fi/loremipsum/v1/test/?start=today&end2024-06-01';

    }

    public function test_get_returnsStdClassObject() {

        add_filter( 'pre_http_request', 'he_return_valid_response',  10, 3 );

        $result = $this->api->get( $this->url );
        $this->assertIsObject( $result );
        $this->assertInstanceOf( \stdClass::class, $result );

        remove_filter( 'pre_http_request', 'he_return_valid_response' );

    }

    public function test_get_throwsHttpRequestFailedExceptionOnError() {

        add_filter( 'pre_http_request', 'he_return_wp_error',  10, 3 );

        $this->expectException( HttpRequestFailedException::class );
        $result = $this->api->get( $this->url );

        remove_filter( 'pre_http_request', 'he_return_wp_error' );

    }

}


