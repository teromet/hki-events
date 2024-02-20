<?php

namespace HkiEvents;

use HkiEvents\LinkedEventsKeywords;
use PHPUnit\Framework\TestCase;

/**
 * @covers HkiEvents\LinkedEventsKeywords
 */
class LinkedEventsKeywordsTest extends TestCase {

    public $categories;
    public $demographic_groups;
    public $keyword_groups;

    /**
     * @before 
     */
    protected function setUp(): void {

        $this->categories = array(
            'test1' => 'Test 1',
            'test3' => 'Test 2',
            'test4' => 'Test 4'
        );

        $this->demographic_groups = array(
            'test5' => 'Test 5',
            'test6' => 'Test 6'
        );

        $this->keyword_groups = array(
            array(
                "name" => "test1",
                "ignored_by_default" => false,
                "keywords" => array(
                    array( "id" => "yso:p1808", "name" => "musiikki" ),
                    array( "id" => "yso:p11185", "name" => "konsertit" ),
                    array( "id" => "yso:p20421", "name" => "musiikkiklubit" ),
                    array( "id" => "yso:p24765", "name" => "musiikkikeikat" ),
                    array( "id" => "yso:p27962", "name" => "elävä musiikki" ),
                ),
            ),
            array(
                "name" => "test2",
                "ignored_by_default" => false,
                "keywords" => array(array( "id" => "yso:p965", "name" => "urheilu" ) ),
            ),
            array(
                "name" => "test3",
                "ignored_by_default" => false,
                "keywords" => array(
                    array( "id" => "yso:p5378", "name" => "keramiikkataide" ),
                    array( "id" => "yso:p6455", "name" => "muotoilu" ),
                    array( "id" => "yso:p2739", "name" => "kuvataide" ),
                    array( "id" => "yso:p18749", "name" => "valotaide" ),
                    array( "id" => "yso:p10649", "name" => "valokuvataide" ),
                    array( "id" => "yso:p16866", "name" => "valokuvanäyttelyt" ),
                ),
            ),
            array(
                "name" => "test4",
                "ignored_by_default" => false,
                "keywords" => array(
                    array( "id" => "yso:p1235", "name" => "elokuvat" ),
                    array( "id" => "yso:p16327", "name" => "elokuvataide" ),
                ),
            ),
            array(
                "name" => "test5",
                "ignored_by_default" => true,
                "keywords" => array(
                    array( "id" => "yso:p11617", "name" => "nuoret" ),
                    array( "id" => "yso:p16485", "name" => "koululaiset" ),
                    array( "id" => "yso:p1925", "name" => "nuorisotyö" ),
                    array( "id" => "yso:p20576", "name" => "nuorisotoimi" ),
                    ),
            ),
            array(
                "name" => "test6",
                "ignored_by_default" => true,
                "keywords" => array(
                    array( "id" => "yso:p2431", "name" => "eläkeläiset" ),
                    array( "id" => "yso:p25147", "name" => "vanhuspalvelut" ),
                    array( "id" => "yso:p2433", "name" => "ikääntyneet" ),
                ),
            ),
            array(
                "name" => "test7",
                "ignored_by_default" => true,
                "keywords" => array( array( "id" => "yso:p6165", "name" => "maahanmuuttajat" ) ),
            ),
        );

    }

    public function test_keywordsAreSetCorrectly() {

        $keywords_obj = new LinkedEventsKeywords( $this->keyword_groups );

        $keywords_obj->set_keywords( $this->categories );
        $keywords = $keywords_obj->get_keywords();
        $expected = array();

        foreach ( $keywords as $keyword ) {
            foreach ( $this->keyword_groups as $group ) {
                if( array_key_exists( $group['name'], $this->categories ) && in_array(
                    $keyword, array_map(
                        function( $v ) { return $v['id']; },
                        array_values( $group['keywords']  ) 
                    ))) {
                        $expected[] = $keyword;
                        break;
                    }
            }
        }
  
        $this->assertEquals( $keywords, $expected );

    }

    public function test_emptyCategoriesResultInEmptyArray() {

        $keywords = new LinkedEventsKeywords( $this->keyword_groups );
        $keywords->set_keywords( array() );
        $keywords = $keywords->get_keywords();

        $this->assertEmpty( $keywords );

    }

    public function test_unavailableCategoriesResultInEmptyArray() {

        $categories = array(
            'cat1' => 'Cat 1',
            'cat2' => 'Cat 2'
        );

        $keywords = new LinkedEventsKeywords( $this->keyword_groups );
        $keywords->set_keywords( $categories );
        $keywords = $keywords->get_keywords();

        $this->assertEmpty( $keywords );

    }

    public function test_ignoredKeywordsAreSetCorrectly() {

        $keywords_obj = new LinkedEventsKeywords( $this->keyword_groups );

        $keywords_obj->set_ignored_keywords( $this->demographic_groups );
        $keywords = $keywords_obj->get_ignored_keywords();
        $expected = array();

        foreach ( $keywords as $keyword ) {
            foreach ( $this->keyword_groups as $group ) {
                if( $group['ignored_by_default'] && ! array_key_exists( $group['name'], $this->demographic_groups ) && in_array(
                    $keyword, array_map(
                        function( $v ) { return $v['id']; },
                        array_values( $group['keywords']  ) 
                    ))) {
                        $expected[] = $keyword;
                        break;
                    }
            }
        }
   
        $this->assertEquals( $keywords, $expected );

    }

}

