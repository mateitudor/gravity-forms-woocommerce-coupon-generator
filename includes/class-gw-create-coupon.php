<?php
// Class to create coupons and send emails

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class GW_Create_Coupon {

    private $args;

    public function __construct() {
        add_action( 'gform_pre_submission', [ $this, 'populate_coupon_code_field' ] );
        add_action( 'gform_after_submission', [ $this, 'create_coupon' ], 10, 2 );
    }

    private function get_coupon_args() {
        $options = GFWCG_Options::get_instance()->get_all_options();

        // Ensure options are available
        if ( empty( $options ) ) {
            // Handle the error appropriately, possibly log it
            error_log( 'GFWCG: Options are not available during form submission.' );
            return false;
        }

        // Proceed to extract options as before
        $discount_amount        = $options['discount_amount'] ?? 0;
        $min_spending_amount    = $options['min_spending_amount'] ?? 0;
        $usage_limit            = $options['usage_limit'] ?? 1;
        $products_include       = ! empty( $options['products_include'] ) ? implode( ',', $options['products_include'] ) : '';
        $products_exclude       = ! empty( $options['products_exclude'] ) ? implode( ',', $options['products_exclude'] ) : '';
        $categories_include     = ! empty( $options['categories_include'] ) ? implode( ',', $options['categories_include'] ) : '';
        $categories_exclude     = ! empty( $options['categories_exclude'] ) ? implode( ',', $options['categories_exclude'] ) : '';
        $discount_type          = $options['discount_type'] ?? 'percent';
        $coupon_expiry_date     = ! empty( $options['coupon_expiry_date'] ) ? date( 'Y-m-d', $options['coupon_expiry_date'] ) : '';
        $form_id                = $options['form_id'] ?? 1;
        $discount_code_prefix   = $options['discount_code_prefix'] ?? '';
        $discount_code_suffix   = $options['discount_code_suffix'] ?? '';
        $individual_use         = $options['individual_use'] === 'yes';
        $exclude_sale_items     = $options['exclude_sale_items'] === 'yes';
        $email_subject          = $options['email_subject'] ?? 'Your Discount Code';
        $email_body             = $options['email_body'] ?? '';

        $args = [
            'form_id'                   => $form_id,
            'source_field_id'           => 3, // Replace with your actual email field ID
            'coupon_code_field_id'      => 5, // Coupon Code field ID
            'type'                      => $discount_type,
            'amount'                    => $discount_amount,
            'min_spending_amount'       => $min_spending_amount,
            'usage_limit'               => $usage_limit,
            'expiry_date'               => $coupon_expiry_date,
            'product_ids'               => $products_include,
            'exclude_product_ids'       => $products_exclude,
            'product_categories'        => $categories_include,
            'exclude_product_categories'=> $categories_exclude,
            'meta'                      => [
                'prefix'                => $discount_code_prefix,
                'suffix'                => $discount_code_suffix,
                'discount_type'         => $discount_type,
                'coupon_amount'         => $discount_amount,
                'individual_use'        => $individual_use ? 'yes' : 'no',
                'exclude_sale_items'    => $exclude_sale_items ? 'yes' : 'no',
                'usage_limit'           => $usage_limit,
                'apply_before_tax'      => 'no',
                'free_shipping'         => 'no',
                'minimum_amount'        => $min_spending_amount,
                'customer_email'        => '',
                'email_subject'         => $email_subject,
                'email_body'            => $email_body,
            ]
        ];

        return $args;
    }

    public function populate_coupon_code_field( $form ) {
        $this->args = $this->get_coupon_args();

        // If options are not available, exit
        if ( ! $this->args ) {
            return;
        }

        if ( $form['id'] != $this->args['form_id'] ) {
            return;
        }

        $email = rgpost( "input_{$this->args['source_field_id']}" );
        if ( ! is_email( $email ) ) {
            return;
        }

        $coupon_code = $this->generate_coupon_code( $email );

        // Populate the Coupon Code field before submission
        $_POST[ "input_{$this->args['coupon_code_field_id']}" ] = $coupon_code;
    }

    public function create_coupon( $entry, $form ) {
        $this->args = $this->get_coupon_args();

        // If options are not available, exit
        if ( ! $this->args ) {
            return;
        }

        if ( $form['id'] != $this->args['form_id'] ) {
            return;
        }

        $email       = rgar( $entry, $this->args['source_field_id'] );
        $coupon_code = rgar( $entry, $this->args['coupon_code_field_id'] );

        if ( ! is_email( $email ) || empty( $coupon_code ) ) {
            return;
        }

        $this->create_wc_coupon( $coupon_code, $email );
        $this->send_coupon_email( $email, $coupon_code );
    }

    private function generate_coupon_code( $email ) {
        $sanitized_input     = strtolower( sanitize_text_field( $email ) );
        $transliterated_input= str_replace( [ 'ș', 'ț', 'î', 'ă', 'â' ], [ 's', 't', 'i', 'a', 'a' ], $sanitized_input );
        $slugified_input     = preg_replace( '/[^a-z0-9]+/', '-', $transliterated_input );
        return sprintf( '%s%s-%d%s', $this->args['meta']['prefix'], $slugified_input, $this->args['amount'], $this->args['meta']['suffix'] );
    }

    private function create_wc_coupon( $coupon_code, $email ) {
        $coupon_data = [
            'post_title'    => sanitize_text_field( $coupon_code ),
            'post_content'  => sprintf( 'Iată codul tău de cupon: %s.', $coupon_code ),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'shop_coupon',
            'post_date'     => current_datetime()->format( 'Y-m-d H:i:s' ),
        ];

        $new_coupon_id = wp_insert_post( $coupon_data );
        if ( is_wp_error( $new_coupon_id ) ) {
            error_log( 'Nu s-a putut crea cuponul WooCommerce: ' . sanitize_text_field( $new_coupon_id->get_error_message() ) );
            return;
        }

        $meta = array_merge( $this->args['meta'], [
            'discount_type'             => $this->args['type'],
            'coupon_amount'             => $this->args['amount'],
            'customer_email'            => sanitize_email( $email ),
            'expiry_date'               => $this->args['expiry_date'], // Empty if no expiry
            'minimum_amount'            => $this->args['min_spending_amount'],
            'usage_limit'               => $this->args['usage_limit'],
            'product_ids'               => $this->args['product_ids'],
            'exclude_product_ids'       => $this->args['exclude_product_ids'],
            'product_categories'        => $this->args['product_categories'],
            'exclude_product_categories'=> $this->args['exclude_product_categories'],
            'individual_use'            => $this->args['meta']['individual_use'],
            'exclude_sale_items'        => $this->args['meta']['exclude_sale_items'],
        ] );

        foreach ( $meta as $key => $value ) {
            update_post_meta( $new_coupon_id, $key, $value );
        }
    }

    private function send_coupon_email( $email, $coupon_code ) {
        $mailer         = WC()->mailer();
        $email_subject  = $this->args['meta']['email_subject'];
        $email_body     = $this->args['meta']['email_body'];

        // Build the coupon details
        $coupon_details = $this->get_coupon_details_html( $coupon_code );

        // Combine email body and coupon details
        $message = wpautop( $email_body ) . $coupon_details;

        // Use WooCommerce email template to wrap the message properly
        $wrapped_message = $this->wrap_email_with_template( $email_subject, $message );

        // Send the email
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $mailer->send( $email, $email_subject, $wrapped_message, $headers );
    }

    private function get_coupon_details_html( $coupon_code ) {
        // Get the date format from WordPress settings
        $date_format = get_option( 'date_format' );

        // Start output buffering
        ob_start();

        echo '<h3><strong>' . esc_html( $coupon_code ) . '</strong></h3>';
        echo '<p>Detalii cupon:</p>';
        echo '<ul>';

        if ( $this->args['amount'] ) {
            if ( $this->args['type'] === 'percent' ) {
                echo '<li>Reducere: ' . esc_html( $this->args['amount'] ) . '%</li>';
            } else {
                echo '<li>Reducere: ' . esc_html( $this->args['amount'] ) . ' Lei</li>';
            }
        }
        if ( $this->args['expiry_date'] ) {
            echo '<li>Data expirării: ' . esc_html( date_i18n( $date_format, strtotime( $this->args['expiry_date'] ) ) ) . '</li>';
        }
        if ( $this->args['min_spending_amount'] ) {
            echo '<li>Suma minimă pentru cumpărături: ' . esc_html( $this->args['min_spending_amount'] ) . ' Lei</li>';
        }
        if ( $this->args['usage_limit'] ) {
            echo '<li>Limită de utilizare: ' . esc_html( $this->args['usage_limit'] ) . '</li>';
        }
        if ( $this->args['product_ids'] ) {
            echo '<li>Produse incluse:<ul>';
            echo $this->get_product_items_html( $this->args['product_ids'] );
            echo '</ul></li>';
        }
        if ( $this->args['exclude_product_ids'] ) {
            echo '<li>Produse excluse:<ul>';
            echo $this->get_product_items_html( $this->args['exclude_product_ids'] );
            echo '</ul></li>';
        }
        if ( $this->args['product_categories'] ) {
            echo '<li>Categorii incluse:<ul>';
            echo $this->get_category_items_html( $this->args['product_categories'] );
            echo '</ul></li>';
        }
        if ( $this->args['exclude_product_categories'] ) {
            echo '<li>Categorii excluse:<ul>';
            echo $this->get_category_items_html( $this->args['exclude_product_categories'] );
            echo '</ul></li>';
        }
        if ( $this->args['meta']['individual_use'] === 'yes' ) {
            echo '<li>Utilizare individuală: Da</li>';
        } else {
            echo '<li>Utilizare individuală: Nu</li>';
        }
        if ( $this->args['meta']['exclude_sale_items'] === 'yes' ) {
            echo '<li>Exclude articolele aflate la reducere: Da</li>';
        } else {
            echo '<li>Exclude articolele aflate la reducere: Nu</li>';
        }

        echo '</ul>';

        // Get the content and end output buffering
        $html = ob_get_clean();

        return $html;
    }

    private function get_product_items_html( $product_ids ) {
        $html = '';
        if ( ! empty( $product_ids ) ) {
            $ids = explode( ',', $product_ids );
            foreach ( $ids as $id ) {
                $product = wc_get_product( $id );
                if ( $product ) {
                    $thumbnail = get_the_post_thumbnail( $product->get_id(), 'thumbnail' );
                    $html     .= '<li><a href="' . get_permalink( $product->get_id() ) . '">' . $thumbnail . esc_html( $product->get_name() ) . '</a></li>';
                }
            }
        }
        return $html;
    }

    private function get_category_items_html( $category_ids ) {
        $html = '';
        if ( ! empty( $category_ids ) ) {
            $ids = explode( ',', $category_ids );
            foreach ( $ids as $id ) {
                $category = get_term( $id, 'product_cat' );
                if ( $category && ! is_wp_error( $category ) ) {
                    $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
                    $image        = wp_get_attachment_image( $thumbnail_id, 'thumbnail' );
                    $html        .= '<li><a href="' . get_term_link( $category ) . '">' . $image . esc_html( $category->name ) . '</a></li>';
                }
            }
        }
        return $html;
    }

    private function wrap_email_with_template( $email_heading, $message ) {
        ob_start();

        // Include custom email template
        wc_get_template( 'emails/email-header.php', [ 'email_heading' => $email_heading ] );

        // Include custom email content with styling
        include GFWCG_PLUGIN_DIR . 'templates/email-template.php';

        wc_get_template( 'emails/email-footer.php' );

        return ob_get_clean();
    }
}