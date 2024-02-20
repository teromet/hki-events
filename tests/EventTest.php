<?php

namespace HkiEvents;

use HkiEvents\Event\Event;
use PHPUnit\Framework\TestCase;

/**
 * @covers HkiEvents\Event\Event
 */
class EventTest extends TestCase {

    public $id;
    public $post_id;
    public $post_ids = array();
    public $event;
    public $keywords;

    /**
     * @before 
     */
    protected function setUp(): void {

        $this->event = json_decode( json_encode( array(
            'id' => 'test:0353',
            'name' => array(
                'fi' => 'Test event'
            ),
            'start_time' => '2024-01-11T17:00:00Z',
            'end_time' => '2024-01-11T17:00:00Z',
            'description' => array(
                'fi' => 'Lorem ipsum dolor sit amet'
            ),
            'super_event_type' => 'recurring'
        ) ) );

        $this->keywords = array(
            array(
                'id' => 'yso:p10649',
                'name' => 'valokuvataide'
            ),
            array(
                'id' => 'yso:p16866',
                'name' => 'valokuvanäyttelyt'
            ),
            array(
                'id' => 'yso:p4484',
                'name' => 'jazz'
            )
        );

        $event = new Event( $this->event, $this->keywords );
        $this->post_id = $event->save();

    }

    /**
     * @after 
     */
    protected function tearDown(): void {

        wp_delete_post( $this->post_id, true );

        foreach ( $this->post_ids as $post_id ) {
            wp_delete_post( $post_id, true );
        }
    }

    public function test_save_postTypeIsCorrect() {
        $this->assertEquals( HE_POST_TYPE, get_post_type( $this->post_id ) );
    }

    public function test_save_postIdIsValid() {
        $this->assertIsNumeric( $this->post_id );
        $this->assertGreaterThan( 0, $this->post_id );
    }

    public function test_save_postTitleIsSavedCorrectly() {
        $this->assertEquals( $this->event->name->fi, get_post( $this->post_id )->post_title );
    }

    public function test_save_postContentIsSavedCorrectly() {
        $this->assertEquals( $this->event->description->fi, get_post( $this->post_id )->post_content );
    }

    public function test_add_id_idIsAddedCorrectly() {

        $id = $this->event->id;
        $id_saved = get_post_meta( $this->post_id, 'hki_event_linked_events_id', true );
        
        $this->assertEquals( $id, $id_saved );

    }

    public function test_add_start_time_startTimeIsAddedCorrectly() {

        $start_time = $this->event->start_time;

        $start_time_saved = get_post_meta( $this->post_id, 'hki_event_start_time', true );
        
        $s_date1 = new \DateTime( $start_time );
        $s_date2 = new \DateTime( $start_time_saved );
        
        $this->assertEquals( $s_date1, $s_date2 );

    }

    public function test_add_start_time_incorrectDateIsIgnored() {

        $event = $this->event;
        $event->name->fi = 'Test event 2';
        $event->start_time = '2024-fooo01-11T17:00:00Z';

        $event = new Event( $event );
        $post_id = $event->save();
        $this->post_ids[] = $post_id;

        $start_time_saved = get_post_meta( $post_id, 'hki_event_start_time', true );

        $this->assertEmpty( $start_time_saved );

    }

    public function test_add_end_time_endTimeIsAddedCorrectly() {

        $end_time = $this->event->end_time;

        $end_time_saved = get_post_meta( $this->post_id, 'hki_event_end_time', true );
        
        $e_date1 = new \DateTime( $end_time );
        $e_date2 = new \DateTime( $end_time_saved );
        
        $this->assertEquals( $e_date1, $e_date2 );

    }

    public function test_add_end_time_incorrectDateIsIgnored() {

        $event = $this->event;
        $event->name->fi = 'Test event 2';
        $event->end_time = '2024-01-11T17:00:00Z2023-01-02';

        $event = new Event( $event );
        $post_id = $event->save();
        $this->post_ids[] = $post_id;

        $end_time_saved = get_post_meta( $post_id, 'hki_event_end_time', true );

        $this->assertEmpty( $end_time_saved );
    
    }

    public function test_add_tags_postTagsAreAddedCorrectly() {

        $keywords = $this->keywords;
        $post_tags = get_the_terms( $this->post_id, HE_TAXONOMY );

        usort( $keywords, function( $a, $b ) {
        return strcmp( $a["name"], $b["name"] );
        } );

        foreach ( $post_tags as $key => $term ) {
            $this->assertEquals( $term->name, $keywords[$key]['name'] );
        }

    }

    public function test_add_tags_incorrectKeywordDataIsIgnored() {

        $event = $this->event;
        $event->name->fi = 'Test event 4';

        $keywords = array(
            array(
            'id' => 'yso:p10649',
            'name' => 'valokuvataide'
            ),
            'lorem ipsum',
            array(
                'id' => 'yso:p16866',
                'name' => 'valokuvanäyttelyt'
            ),
            105,
            array(
                'id' => 'yso:p4484',
                'name' => 'jazz'
            ),
            array(
                'id' => 'lorem'
            )
        );

        $valid_keywords = array_map(
            function( $v ) { 
                return $v['name'];
            }, array_values( $this->keywords ) );

        $event = new Event( $event, $keywords );
        $post_id = $event->save();
        $this->post_ids[] = $post_id;
        $post_tags = get_the_terms( $post_id, HE_TAXONOMY );

        $this->assertCount( count( $this->keywords ), $post_tags );

        foreach ( $post_tags as $term ) {
            $this->assertContains( $term->name, $valid_keywords );
        }

    }

}