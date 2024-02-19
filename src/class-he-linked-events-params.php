<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;

/**
 * LinkedEventsParams class.
 *
 * Wrapping the Linked Event API params.
 * 
 */
class LinkedEventsParams {

    /**
     * Start date
     * 
     * @var string
     */
    private $start;

    /**
     * End date
     * 
     * @var string
     */
    private $end;

    /**
     * Page number
     *
     * @var integer
     */
    private $page;

    /**
     * Query only free events
     *
     * @var bool
     */
    private $is_free;

    /**
     * Hide child events of recurring super event
     *
     * @var bool
     */
    private $hide_recurring_children;

    /**
     * Last modified
     * 
     * @var string
     */
    private $last_modified_since;

    /**
     * Keyword params
     * 
     * @var array
     */
    private $keywords;


    /**
     * LinkedEventsParams constructor.
     *
     */
    function __construct( $keywords, $options = array() ) {

        $dt = new \DateTime( 'now', new \DateTimeZone( 'Europe/Helsinki' ) );
        $time = $dt->getTimestamp();

        // Keywords
        $this->keywords = $keywords;

        // Other options
        $this->start                    = 'today';
        $this->end                      = date( 'Y-m-d', strtotime( '+'.intval( $options['time_span'] ).' month', $time ) );
        $this->is_free                  = $options['is_free'];
        $this->hide_recurring_children  = 'true';
        $this->last_modified_since      = $options['last_fetched'];

        // Page
        $this->page = 1;

    }

    public function next_page() {
        $this->page++;
    }

    /**
     * Make an array of the parameters
     * 
     * @return array api params
     * 
     */
    public function get_params() {

        // API Params
        $params = array( 
            'start'                     => $this->start,
            'end'                       => $this->end,
            'sort'                      => 'end_time',
            'page'                      => $this->page,
            'hide_recurring_children'   => $this->hide_recurring_children
        );

        if ( boolval( $this->is_free ) ) {
            $params['is_free'] = 'true';
        }
        if ( ! empty ( $this->last_modified_since ) ) {
            $params['last_modified_since'] = $this->last_modified_since;
        }
        if ( ! empty ( $this->keywords ) ) {
            $params = array_merge( $params, $this->keywords );
        }

        return $params;

    }

}