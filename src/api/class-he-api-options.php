<?php

namespace HkiEvents\Api;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * ApiOptions class.
 * 
 * Implementation-specific query options / parameters
 *
 * @see https://dev.hel.fi/apis/linkedevents
 *
 */
class ApiOptions {

    /**
     * Start date
     * 
     * ISO 8601 (including the time of day), yyyy-mm-dd, 'today' or 'now'.
     *
     */
    private string $start_date;

    /**
     * End date
     * 
     * ISO 8601 (including the time of day), yyyy-mm-dd, 'today' or 'now'.
     *
     */
    private string $end_date;

    /**
     * Sort
     * 
     * Default ordering is descending order by -last_modified_time.
     * You may also order results by start_time, end_time, name and duration.
     * Descending order is denoted by adding - in front of the parameter, default order is ascending.
     *
     */
    private string $sort;

    /**
     * Last modified date
     *
     */
    private string $last_modified_since;

    /**
     * Bool options
     *
     */
    private array $bool_options;

    /**
     * Keywords
     * 
     */
    private array $keywords;

    /**
     * Keywords
     * 
     */
    private array $ignored_keywords;

    /**
     * ApiOptions constructor.
     *
     */
    function __construct( array $options ) {

        $this->start_date          = ! empty ( $options['start'] ) ? $options['start'] : 'today';
        $this->end_date            = $this->create_end_date( $options['time_span'] );
        $this->last_modified_since = ! empty ( $options['last_modified_since'] ) ? $options['last_modified_since'] : '';
        $this->bool_options        = $options['bool_options'];
        $this->keywords            = ! empty ( $options['keywords'] ) ? $options['keywords'] : array();
        $this->ignored_keywords    = ! empty ( $options['ignored_keywords'] ) ? $options['ignored_keywords'] : array();
        $this->sort                = 'end_time';


    }

    /**
     * Get params as array
     * 
     * @return array $params
     */
    public function get_params() {

        // Default params
        $params = array( 
            'start' => $this->start_date,
            'end'   => $this->end_date,
            'sort'  => $this->sort
        );
        // Bool options
        if ( ! empty ( $this->bool_options ) ) {

            $bool_options = array_map(
                function( $v ) { return $v ? 'true' : 'false'; },
                $this->bool_options
            );

            $params = array_merge ( $params, $bool_options );

        }
        // Keywords
        if ( ! empty ( $this->keywords ) ) {
            $params['keyword'] = implode( ",", $this->keywords );
        }
        // Ignored keywords
        if ( ! empty ( $this->ignored_keywords ) ) {
            $params['keyword!'] = implode( ",", $this->ignored_keywords );
        }
        // Last modified
        if ( ! empty ( $this->ignored_keywords ) ) {
            $params['last_modified_since'] = $this->last_modified_since;
        }
        // Paging
        $params['page'] = 1;

        return $params;

    }

    /**
     * Create the end date from number representing an upcoming month
     * 
     * @param string|int $time_span
     * 
     * @return string $end_date
     */
    private function create_end_date( string|int $time_span ) {

        $time_span  = ! empty ( $time_span ) ? $time_span : 1;
        $dt         = new \DateTime( 'now', new \DateTimeZone( 'Europe/Helsinki' ) );
        $time       = $dt->getTimestamp();

        $end_date   = date( 'Y-m-d', strtotime( '+'.intval( $time_span ).' month', $time ) );

        return $end_date;
    }

}