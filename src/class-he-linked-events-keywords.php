<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;

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
    private $keywords = array();

    /**
     * Ignored Linked Events keywords
     *
     * @var string[]
     */
    private $keywords_to_ignore = array();

    /**
     * Manually defined set of keyword groups
     *
     * @var array
     */
    private $keyword_groups;

    /**
     * LinkedEventsKeywords constructor.
     *
     */
    function __construct() {
        $this->keyword_groups = $this->load_keyword_groups();
    }

    /**
     * Set keyword ids
     */
    public function set_keywords( $selected ) {

        if ( empty ( $selected ) ) {
            return;
        }

        $groups = $this->get_selected_keyword_groups( $selected );
        $keyword_ids = $this->keyword_groups_to_ids( $groups );

        $this->keywords = array_merge( $this->keywords, $keyword_ids );

    }

    /**
     * Set ignored keyword ids
     */
    public function set_ignored_keywords( $selected ) {

        $groups = $this->get_ignored_keyword_groups( $selected );
        $keyword_ids = $this->keyword_groups_to_ids( $groups );
        
        $this->keywords_to_ignore = array_merge( $this->keywords_to_ignore, $keyword_ids );

    }


    /**
     * Get keywords as Linked Events API params
     */
    public function get_params() {

        return array(
            'keyword' => implode( ",", $this->keywords ),
            'keyword!' => implode( ",", $this->keywords_to_ignore ),
        );

    }

    /**
     * Merge keyword groups
     * 
     * @param array $groups
     * 
     * @return array $keywords
     */
    private function merge_groups( $groups ) {

        $keywords = array();

        foreach ( $groups as $key => $value ) {
            $keywords = array_merge( $keywords, $value['keywords'] );
        }

        return $keywords;

    }

    /**
     * Load keyword groups from JSON file
     *
     * @return array|false Decoded keyword data or false
     * 
     * TODO: Some error handling
     * 
     */
    private function load_keyword_groups() {

        if ( ! file_exists( HE_DIR . '/inc/keyword-groups.json' ) ) {
            return false;
        }

        $keywords_json = file_get_contents( HE_DIR . '/inc/keyword-groups.json' ); 
        $keywords_groups = json_decode( $keywords_json, true ); 

        return $keywords_groups;

    }

    /**
     * Get user-selected keyword groups
     * 
     * @param array $selected
     * 
     * @return array $groups
     */
    private function get_selected_keyword_groups( $selected ) {

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
    private function get_ignored_keyword_groups( $selected ) {

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
    private function keyword_groups_to_ids( $groups ) {

        $keywords = $this->merge_groups( $groups );
        $keyword_ids = $this->to_ids( $keywords );

        return $keyword_ids;

    }

    /**
     * Map keywords to array of keyword ids
     *
     */
    private function to_ids( $keywords ) {

        $keywords = array_map(
            function( $v ) { return $v['id']; },
            array_values( $keywords  ) 
        );

        return $keywords;

    }

}

