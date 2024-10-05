<?php
// Helper functions for the Gravity Forms WooCommerce Coupon Generator plugin

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Function to get all WooCommerce product IDs
function get_all_woocommerce_products() {
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'publish',
    ];
    $products = get_posts( $args );
    return $products;
}

// Function to get option fields from JetEngine
function get_option_fields_from_jetengine() {
    $options = [
        'discount_amount'         => 'globals::gf_nl_sub-discount_amount',
        'min_spending_amount'     => 'globals::gf_nl_sub-min_spending',
        'usage_limit'             => 'globals::gf_nl_sub-usage_limit',
        'products_include'        => 'globals::gf_nl_sub-products_include',
        'products_exclude'        => 'globals::gf_nl_sub-products_exclude',
        'discount_type'           => 'globals::gf_nl_sub-discount_type',
        'coupon_expiry_date'      => 'globals::gf_nl_sub-coupon_expiry_date',
        'categories_include'      => 'globals::gf_nl_sub-categories_include',
        'categories_exclude'      => 'globals::gf_nl_sub-categories_exclude',
        'form_id'                 => 'globals::gf_nl_sub-form_id',
        'discount_code_prefix'    => 'globals::gf_nl_sub-discount_code_prefix',
        'discount_code_suffix'    => 'globals::gf_nl_sub-discount_code_suffix',
        'individual_use'          => 'globals::gf_nl_sub-individual_use',
        'exclude_sale_items'      => 'globals::gf_nl_sub-sale_items',
        'email_subject'           => 'globals::gf_nl_sub-email_subject',
        'email_body'              => 'globals::gf_nl_sub-text',
    ];

    $fallbacks = [
        'discount_amount'         => 0,
        'min_spending_amount'     => 50,
        'usage_limit'             => 1,
        'products_include'        => get_all_woocommerce_products(),
        'products_exclude'        => [],
        'discount_type'           => 'percent',
        'coupon_expiry_date'      => '', // Empty string for no expiry
        'categories_include'      => [],
        'categories_exclude'      => [],
        'form_id'                 => 1,
        'discount_code_prefix'    => 'sb_nl-',
        'discount_code_suffix'    => '',
        'individual_use'          => 'yes',
        'exclude_sale_items'      => 'no',
        'email_subject'           => 'Codul tău de reducere',
        'email_body'              => '',
    ];

    $values = [];

    foreach ( $options as $key => $option_name ) {
        // Check if jet_engine() function exists and returns an object
        if ( function_exists( 'jet_engine' ) && jet_engine() ) {
            $value = jet_engine()->listings->data->get_option( $option_name );
        } else {
            $value = '';
        }

        if ( $key === 'individual_use' || $key === 'exclude_sale_items' ) {
            $values[ $key ] = $value === 'true' ? 'yes' : 'no';
        } else {
            $values[ $key ] = ! empty( $value ) ? $value : $fallbacks[ $key ];
        }

        if ( in_array( $key, [ 'discount_amount', 'min_spending_amount', 'usage_limit' ] ) ) {
            $values[ $key ] = is_numeric( $value ) ? intval( $value ) : $fallbacks[ $key ];
        } elseif ( $key === 'coupon_expiry_date' ) {
            $values[ $key ] = ! empty( $value ) ? strtotime( $value ) : ''; // Handle empty expiry date
        } elseif ( in_array( $key, [ 'categories_include', 'categories_exclude', 'products_include', 'products_exclude' ] ) ) {
            if ( ! empty( $value ) ) {
                if ( is_string( $value ) ) {
                    $value = explode( ',', $value );
                }
                $values[ $key ] = array_map( 'intval', (array) $value );
            } else {
                $values[ $key ] = [];
            }
        }
    }

    return $values;
}