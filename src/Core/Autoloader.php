<?php

namespace WooOffers\Core;

/**
 * Enhanced Autoloader with PSR-4 compliance and includes support
 * 
 * @package WooOffers
 * @since 3.0.0
 */
class Autoloader {
    
    /**
     * Namespace prefixes and their base directories
     * 
     * @var array
     */
    private static $prefixes = [
        'WooOffers\\' => [
            WOO_OFFERS_PLUGIN_DIR . 'src/',
        ],
        // Support for legacy includes structure
        'WooOffers\\Includes\\' => [
            WOO_OFFERS_PLUGIN_DIR . 'includes/',
        ],
        'WooOffers\\Includes\\Admin\\' => [
            WOO_OFFERS_PLUGIN_DIR . 'includes/admin/',
        ],
    ];
    
    /**
     * Function files to load
     * 
     * @var array
     */
    private static $function_files = [
        'includes/template-functions.php',
    ];
    
    /**
     * Register the autoloader
     */
    public static function register() {
        spl_autoload_register( [ __CLASS__, 'load_class' ] );
        self::load_function_files();
    }
    
    /**
     * Load a class file
     * 
     * @param string $class The fully-qualified class name
     * @return bool True if file was loaded, false otherwise
     */
    public static function load_class( $class ) {
        // Work through the registered prefixes
        foreach ( self::$prefixes as $prefix => $base_dirs ) {
            $len = strlen( $prefix );
            
            // Does the class use this prefix?
            if ( strncmp( $prefix, $class, $len ) !== 0 ) {
                continue;
            }
            
            // Get the relative class name
            $relative_class = substr( $class, $len );
            
            // Try each base directory for this prefix
            foreach ( $base_dirs as $base_dir ) {
                $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
                
                if ( self::load_file( $file ) ) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Load a file if it exists
     * 
     * @param string $file The file path to load
     * @return bool True if file was loaded, false otherwise
     */
    private static function load_file( $file ) {
        if ( file_exists( $file ) && is_readable( $file ) ) {
            require_once $file;
            return true;
        }
        
        return false;
    }
    
    /**
     * Load function files
     */
    private static function load_function_files() {
        foreach ( self::$function_files as $file ) {
            $full_path = WOO_OFFERS_PLUGIN_DIR . $file;
            self::load_file( $full_path );
        }
    }
    
    /**
     * Add a new namespace prefix
     * 
     * @param string $prefix Namespace prefix
     * @param string $base_dir Base directory for the prefix
     */
    public static function add_prefix( $prefix, $base_dir ) {
        if ( ! isset( self::$prefixes[ $prefix ] ) ) {
            self::$prefixes[ $prefix ] = [];
        }
        
        array_unshift( self::$prefixes[ $prefix ], $base_dir );
    }
    
    /**
     * Add a function file to be loaded
     * 
     * @param string $file Relative path to function file
     */
    public static function add_function_file( $file ) {
        if ( ! in_array( $file, self::$function_files, true ) ) {
            self::$function_files[] = $file;
            
            // Load immediately if already registered
            $full_path = WOO_OFFERS_PLUGIN_DIR . $file;
            self::load_file( $full_path );
        }
    }
    
    /**
     * Get registered prefixes
     * 
     * @return array
     */
    public static function get_prefixes() {
        return self::$prefixes;
    }
    
    /**
     * Get registered function files
     * 
     * @return array
     */
    public static function get_function_files() {
        return self::$function_files;
    }
}

// Auto-register the autoloader when this file is included
Autoloader::register();
