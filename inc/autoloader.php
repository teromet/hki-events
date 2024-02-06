<?php

spl_autoload_register( 'he_autoloader' );

/**
 * Autoloader
 * 
 */
function he_autoloader( $class ) {

    $namespace = 'HkiEvents';
  
    if ( strpos( $class, $namespace ) !== 0 ) {
        return;
    }
    
    $class = str_replace( $namespace, '', $class );

    $class_arr = explode( '\\', $class );
    $class_end = array_pop( $class_arr );
    $class_end = ltrim( strtolower( preg_replace( '/[A-Z]([A-Z](?![a-z]))*/ ', '-$0', $class_end ) ), '-' );
  
    $class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
    $class = 'class-he-' . sanitize_title( $class_end ) . '.php';
  
    $path = HE_DIR .  DIRECTORY_SEPARATOR . 'src';

    if ( ! empty( $class_arr ) ) {
        foreach ( $class_arr as $dir ) {
            $path .= sanitize_title( $dir ) . DIRECTORY_SEPARATOR;
        }
    }

    $path .= $class;

    if ( file_exists( $path ) ) {
        require_once( $path );
    }

}