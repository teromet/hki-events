<?php

namespace HkiEvents;

use HkiEvents\HE_Event as Event;
use PHPUnit\Framework\TestCase;

/**
 * HE_Event
 *
 */
class EventTest extends TestCase {

    public $post_id;
    public $event_args;

    protected function setUp(): void {

        $this->event_args = array(
            'title' => 'Test event',
            'start_time' => '2024-01-11T17:00:00Z',
            'end_time' => '2024-01-11T17:00:00Z',
            'image_url' => 'http://sandbox.local',
            'description' => 'Lorem ipsum dolor sit amet',
            'recurring' => true,
            'dates' => array(
                0 => '2024-01-11T17:00:00Z',
                1 => '2024-01-21T17:00:00Z',
                2 => '2024-01-25T17:00:00Z'
            )
        );

        $event = new Event( $this->event_args );
        $this->post_id = $event->save();

    }
    protected function tearDown(): void {
        wp_delete_post( $this->post_id, true );
    }

    public function test_save_postTypeIsCorrect() {
        $this->assertEquals( HE_POST_TYPE, get_post_type( $this->post_id ) );
    }

    public function test_save_postIdIsValid() {
        $this->assertIsNumeric( $this->post_id );
        $this->assertGreaterThan( 0, $this->post_id );
    }

    public function test_save_postTitleIsSavedCorrectly() {
        $this->assertEquals( $this->event_args['title'], get_post( $this->post_id )->post_title );
    }

    public function test_save_postContentIsSavedCorrectly() {
        $this->assertEquals( $this->event_args['description'], get_post( $this->post_id )->post_content );
    }

    public function test_save_imageUrlIsSavedCorrectly() {
        $this->assertEquals( $this->event_args['image_url'], get_post_meta( $this->post_id, 'hki_event_image_url', true ) );
    }

    public function test_add_event_dates_eventDatesAreAddedCorrectly() {

        $start_time = $this->event_args['start_time'];  
        $end_time = $this->event_args['end_time'];

        $start_time_saved = get_post_meta( $this->post_id, 'hki_event_start_time', true );
        $end_time_saved = get_post_meta( $this->post_id, 'hki_event_end_time', true );
        
        $s_date1 = new \DateTime( $start_time );
        $e_date1 = new \DateTime( $end_time );
        $s_date2 = new \DateTime( $start_time_saved );
        $e_date2 = new \DateTime( $end_time_saved );
        
        $this->assertEquals( $s_date1, $s_date2 );
        $this->assertEquals( $e_date1, $e_date2 );

    }

    public function test_add_event_dates_recurringEventDatesAreAddedCorrectly() {

        $dates = $this->event_args['dates'];  
        $dates_saved = explode(', ', get_post_meta( $this->post_id, 'hki_event_dates', true ));

        foreach($dates as $key => $value) {
            $date1 = new \DateTime( $value );
            $date2 = new \DateTime( $dates_saved[$key] );
            $this->assertEquals( $date1->format('j.n.Y'), $date2->format('j.n.Y') );
        }

    }



}