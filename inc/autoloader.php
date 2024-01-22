<?php

spl_autoload_register( 'he_autoloader' );

/**
 * Autoloader
 * 
 * @see https://awhitepixel.com/blog/autoloader-namespaces-theme-plugin/
 */
function he_autoloader( $class ) {

    $namespace = 'HkiEvents';
  
    if ( strpos( $class, $namespace ) !== 0 ) {
        return;
    }
  
    $class = str_replace( $namespace, '', $class );
    $class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
    $class = 'class-' . sanitize_title( $class ) . '.php';
    $class = str_replace( '_', '-', $class );
  
    $path = HE_DIR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class;

    if ( file_exists( $path ) ) {
        require_once( $path );
    }

}