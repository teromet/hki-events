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
            'start_time' => '1970-01-01T00:00:00.000Z',
            'end_time' => '1970-01-01T00:00:00.000Z',
            'image_url' => 'http://sandbox.local',
            'description' => 'Lorem ipsum dolor sit amet'
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

    public function test_save_postMetadataIsSavedCorrectly() {
        $this->assertEquals( $this->event_args['start_time'], get_post_meta( $this->post_id, 'hki_event_start_time', true ) );
        $this->assertEquals( $this->event_args['end_time'], get_post_meta( $this->post_id, 'hki_event_end_time', true ) );
        $this->assertEquals( $this->event_args['image_url'], get_post_meta( $this->post_id, 'hki_event_image_url', true ) );
    }


}