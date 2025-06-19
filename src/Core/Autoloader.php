<?php

namespace WooOffersPro\Core;

class Autoloader {
    private static $prefix = 'WooOffersPro\\\\';
    private static $base_dir;

    public static function register() {
        self::$base_dir = WOO_OFFERS_PRO_PLUGIN_DIR . 'src/';
        spl_autoload_register( [ __CLASS__, 'load_class' ] );
    }

    public static function load_class( $class ) {
        $len = strlen( self::$prefix );
        if ( strncmp( self::$prefix, $class, $len ) !== 0 ) {
            return;
        }
        $relative_class = substr( $class, $len );
        $file = self::$base_dir . str_replace( '\\\\', '/', $relative_class ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    }
}

Autoloader::register();
