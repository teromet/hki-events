<?php

function he_return_wp_error() {
        return new \WP_Error();
    }

function he_return_valid_response() {
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