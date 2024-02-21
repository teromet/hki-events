<?php

namespace HkiEvents\Api;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;

/**
 * QueryBuilder class.
 *
 *
 */
class QueryBuilder {

    /**
     * API URL
     *
     */
    private string $api_url;

    /**
     * Endpoint
     *
     */
    private string $endpoint;

    /**
     * ID
     *
     */
    private int|string $id;

    /**
     * URL params
     *
     */
    private array $params = array();

    /**
     * Page number
     *
     */
    private int $page;

    /**
     * QueryBuilder constructor.
     *
     */
    function __construct( string $api_url ) {

        $this->api_url = $api_url;
        $this->page = 1;

    }

    /**
     * Add endpoint
     * 
     * @param string $endpoint
     * 
     * @return QueryBuilder
     */
    public function endpoint( string $endpoint ) {

        $this->endpoint = $endpoint;

        return $this;

    }

    /**
     * Add id
     * 
     * @param int|string $id
     * 
     * @return QueryBuilder
     */
    public function with_id( int|string $id ) {

        $this->id = $id;

        return $this;

    }

    /**
     * Add params
     * 
     * @param array $params
     * 
     * @return QueryBuilder
     */
    public function with_params( array $params ) {

        $this->params = $params;

        return $this;
    }

    public function next_page() {
        $this->page++;
    }

    /**
     * Get the query string
     * 
     * @return string $url
     */
    public function get_query() {
        
        $params = $this->params;
        $params['page'] = $this->page;

        $url = ! preg_match ( '/.*\/$/', $this->api_url ) ? $this->api_url.'/' : $this->api_url;
        $url = sanitize_url( $this->api_url.$this->endpoint.'/' );

        if ( ! empty ( $this->id ) ) {
            $url = $url.Utils::clean_string( $this->id );
        }

        if ( ! empty ( $params ) ) {
            $url = $url.'?'.http_build_query( $params );
        }

        return $url;
        
    }

}