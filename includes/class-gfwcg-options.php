<?php
// Class to store and provide access to plugin options

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class GFWCG_Options {

    private static $instance = null;
    private $options = [];

    private function __construct() {
        // Constructor left empty intentionally
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initialize_options() {
        $this->options = get_option_fields_from_jetengine();
        // Cache the options for later use
        update_option( 'gfwcg_cached_options', $this->options );
    }

    public function get_option( $key, $default = null ) {
        $options = $this->get_all_options();
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    public function get_all_options() {
        // If options are empty, retrieve from cache
        if ( empty( $this->options ) ) {
            $this->options = get_option( 'gfwcg_cached_options', [] );
        }
        return $this->options;
    }
}