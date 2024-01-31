<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use DateTime;
use DateTimeZone;

/**
 * HE_Utils
 *
 * Helper class
 * 
 * @package HkiEvents
 */
class HE_Utils {

    /**
     * Write message to a log file
     * 
     * @param string $log log filename in /logs folder
     * @param string $text log input
     * 
     */
    public static function log( $log, $text ) {

        $log_file = fopen( HE_DIR . "/logs/".$log.".txt", "a" );

        $dt = new DateTime( "now", new DateTimeZone('Europe/Helsinki') );
        $timestamp = $dt->format( 'Y-m-d H:i' );
    
        $log_entry = $timestamp ." - ".$text."\n";
    
        fwrite( $log_file, $log_entry );
        fclose( $log_file );
    
    }

    /**
     * Uploads an image from remote url and sets as featured image of the new post
     * @see https://gist.github.com/ajskelton/23d4dda9e3b837f408e20b5dc23f6b52
     *
     * @param int    $post_id       The id of the new post
     * @param string $thumbnail_url Url of the preview image hosted by BrightTalk
     */
    public static function upload_thumbnail_from_url( $post_id, $thumbnail_url ) {

        // Early exit
        if ( $post_id <= 0 || empty( $thumbnail_url ) ) {
            return;
        }
        
        $image_url        = $thumbnail_url; // Define the image URL here
        $image_name       = basename( $thumbnail_url );
        $upload_dir       = wp_upload_dir(); // Set upload folder
        $image_data       = file_get_contents( $image_url ); // Get image data
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
        $filename         = basename( $unique_file_name ); // Create image file name

        // Early exit if no data or attachment with this name already exists
        if ( ! $image_data || attachment_url_to_postid( $upload_dir['url'].'/'.$image_name ) === $post_id ) {
            return;
        }

        // Check folder permission and define file location
        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        
        // Create the image  file on the server
        file_put_contents( $file, $image_data );
        
        // Check image file type
        $wp_filetype = wp_check_filetype( $filename, null );
        
        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name( $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        // Create the attachment
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        
        // Include image.php
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        
        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        
        // Assign metadata to attachment
        wp_update_attachment_metadata( $attach_id, $attach_data );
        
        // And finally assign featured image to post
        set_post_thumbnail( $post_id, $attach_id );

    }

}
