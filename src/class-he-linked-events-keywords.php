<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * LinkedEventsKeywords class.
 *
 * 
 */
class LinkedEventsKeywords {

    /**
     * Selected Linked Events keywords
     *
     * @var string[]
     */
    private array $keywords = array();

    /**
     * Ignored Linked Events keywords
     *
     * @var string[]
     */
    private array $keywords_to_ignore = array();

    /**
     * Manually defined set of keyword groups
     *
     * @var array
     */
    private array $keyword_groups;

    /**
     * LinkedEventsKeywords constructor.
     *
     */
    function __construct( $keywords_groups ) {
        $this->keyword_groups = $keywords_groups;
    }

    /**
     * Set keyword ids
     * 
     * @param array $selected
     * 
     * @return $this
     */
    public function set_keywords( array $selected ) {

        if ( ! empty ( $selected ) ) {

            $groups = $this->get_selected_keyword_groups( $selected );
            $keyword_ids = $this->keyword_groups_to_ids( $groups );
    
            $this->keywords = $keyword_ids;

        }

        return $this;

    }

    /**
     * Set ignored keyword ids
     * 
     * @param array $selected
     * 
     * @return $this
     */
    public function set_ignored_keywords( array $selected ) {

        $groups = $this->get_ignored_keyword_groups( $selected );
        $keyword_ids = $this->keyword_groups_to_ids( $groups );
        
        $this->keywords_to_ignore = $keyword_ids;

        return $this;

    }

    public function get_keywords() {
        return $this->keywords;
    }

    public function get_ignored_keywords() {
        return $this->keywords_to_ignore;
    }

    /**
     * Get keywords as Linked Events API params
     */
    public function get_params() {

        $params = array();

        if ( ! empty ( $this->keywords ) ) {
            $params['keyword'] = implode( ",", $this->keywords );
        }

        if ( ! empty ( $this->keywords_to_ignore ) ) {
            $params['keyword!'] = implode( ",", $this->keywords_to_ignore );
        }

        return $params;

    }

    /**
     * Merge keyword groups
     * 
     * @param array $groups
     * 
     * @return array $keywords
     */
    private function merge_groups( array $groups ) {

        $keywords = array();

        foreach ( $groups as $key => $value ) {
            $keywords = array_merge( $keywords, $value['keywords'] );
        }

        return $keywords;

    }

    /**
     * Get user-selected keyword groups
     * 
     * @param array $selected
     * 
     * @return array $groups
     */
    private function get_selected_keyword_groups( array $selected ) {

        $selected_keys = array_keys( $selected );

        $groups = array_filter( $this->keyword_groups, function( $v ) use ( $selected_keys ) {
            return in_array ( $v['name'], $selected_keys );
        } );

        return $groups;

    }

    /**
     * Get ignored keyword groups
     * 
     * @param array $selected
     * 
     * @return array $groups
     */
    private function get_ignored_keyword_groups( array $selected ) {

        $selected_keys = ! empty( $selected ) ? array_keys( $selected ) : array();

        $groups = array_filter( $this->keyword_groups, function( $v ) use ( $selected_keys ) {
            return $v['ignored_by_default'] == true && ! in_array ( $v['name'], $selected_keys );
        } );

        return $groups;

    }

    /**
     * Merge keyword groups and return array of ids
     * 
     * @param array $groups
     * 
     * @return string[] $keyword_ids
     */
    private function keyword_groups_to_ids( array $groups ) {

        $keywords = $this->merge_groups( $groups );
        $keyword_ids = $this->to_ids( $keywords );

        return $keyword_ids;

    }

    /**
     * Map keywords to array of keyword ids
     *
     */
    private function to_ids( array $keywords ) {

        $keywords = array_map(
            function( $v ) { return $v['id']; },
            array_values( $keywords  ) 
        );

        return $keywords;

    }

}

