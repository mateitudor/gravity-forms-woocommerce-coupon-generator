<?php
// Email template with white background and padding

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Start email body with white background and padding
echo '<div style="background-color: #ffffff; padding: 20px;">';

// Email content
echo wpautop( wp_kses_post( $message ) );

// Close email body div
echo '</div>';