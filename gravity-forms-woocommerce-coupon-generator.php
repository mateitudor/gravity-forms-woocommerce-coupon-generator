<?php
/*
Plugin Name: Gravity Forms WooCommerce Coupon Generator
Description: Generates WooCommerce coupons when a Gravity Form is submitted and sends them via email with a styled template.
Version: 1.0.3
Author: Your Name
Text Domain: gf-woocommerce-coupon-generator
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'GFWCG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GFWCG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once GFWCG_PLUGIN_DIR . 'includes/functions.php';
require_once GFWCG_PLUGIN_DIR . 'includes/class-gfwcg-options.php';
require_once GFWCG_PLUGIN_DIR . 'includes/class-gw-create-coupon.php';
require_once GFWCG_PLUGIN_DIR . 'includes/shortcodes.php';

// Initialize the plugin
function gfwcg_init() {
    // Ensure required plugins are active
    if ( ! class_exists( 'GFCommon' ) || ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'gfwcg_admin_notice_missing_plugins' );
        return;
    }

    // Initialize options
    GFWCG_Options::get_instance()->initialize_options();

    // Initialize coupon creation
    new GW_Create_Coupon();

    // Register shortcodes
    add_shortcode( 'gf_nl_sub_discount_info', 'gf_nl_sub_discount_info' );
}
// Hook to 'init' with a higher priority to ensure JetEngine is loaded
add_action( 'init', 'gfwcg_init', 20 );

// Enqueue plugin styles
function gfwcg_enqueue_styles() {
    wp_enqueue_style(
        'gfwcg-main-css',
        GFWCG_PLUGIN_URL . 'assets/css/main.min.css',
        array(),
        filemtime( GFWCG_PLUGIN_DIR . 'assets/css/main.min.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'gfwcg_enqueue_styles' );

// Admin notice if required plugins are missing
function gfwcg_admin_notice_missing_plugins() {
    echo '<div class="error"><p><strong>Gravity Forms WooCommerce Coupon Generator</strong> requires Gravity Forms and WooCommerce to be installed and active.</p></div>';
}