<?php
// Shortcode functions for the Gravity Forms WooCommerce Coupon Generator plugin

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Function to display discount information as shortcode
function gf_nl_sub_discount_info() {
    $options = GFWCG_Options::get_instance()->get_all_options();

    // Use $options instead of calling get_option_fields_from_jetengine()
    $discount_amount = $options['discount_amount'] ?? null;
    $min_spending_amount = $options['min_spending_amount'] ?? null;
    $usage_limit = $options['usage_limit'] ?? null;
    $discount_type_value = $options['discount_type'] ?? null;
    $products_include = $options['products_include'] ?? [];
    $products_exclude = $options['products_exclude'] ?? [];
    $categories_include = $options['categories_include'] ?? [];
    $categories_exclude = $options['categories_exclude'] ?? [];
    $coupon_expiry_date = ! empty( $options['coupon_expiry_date'] ) ? date_i18n( get_option( 'date_format' ), $options['coupon_expiry_date'] ) : null;
    $individual_use = $options['individual_use'] ?? 'yes';
    $exclude_sale_items = $options['exclude_sale_items'] ?? 'no';
    $discount_type_label = [
        'percent'       => 'Procentuală',
        'fixed_cart'    => 'Fixă pe coș',
        'fixed_product' => 'Fixă pe produs',
    ][ $discount_type_value ] ?? '';

    ob_start();

    if ( $discount_amount ) {
        if ( $discount_type_value === 'percent' ) {
            echo '<h3 class="discount-amount">Abonează-te acum și primești ' . esc_html( $discount_amount ) . '% reducere!</h3>';
        } elseif ( $discount_type_value === 'fixed_cart' ) {
            echo '<h3 class="discount-amount">Abonează-te acum și primești ' . esc_html( $discount_amount ) . ' Lei reducere la coșul tău!</h3>';
        } elseif ( $discount_type_value === 'fixed_product' ) {
            echo '<h3 class="discount-amount">Abonează-te acum și primești ' . esc_html( $discount_amount ) . ' Lei reducere pe produs!</h3>';
        }
    }
    if ( $min_spending_amount ) {
        echo '<p>Comandă minimă de ' . esc_html( $min_spending_amount ) . ' Lei.</p>';
    }
    if ( $usage_limit ) {
        echo '<p>Limită de utilizare: ' . esc_html( $usage_limit ) . '.</p>';
    }
    if ( $coupon_expiry_date ) {
        echo '<p>Expiră la ' . esc_html( $coupon_expiry_date ) . '.</p>';
    }
    if ( $individual_use === 'yes' ) {
        echo '<p>Acest cupon este pentru utilizare individuală.</p>';
    }
    if ( $exclude_sale_items === 'yes' ) {
        echo '<p>Produsele aflate la reducere sunt excluse.</p>';
    } else {
        echo '<p>Produsele aflate la reducere nu sunt excluse.</p>';
    }

    $sections = [
        'products_include' => [
            'title_singular' => 'Produs inclus:',
            'title_plural' => 'Produse incluse:',
            'class' => 'included-products',
            'type' => 'product'
        ],
        'products_exclude' => [
            'title_singular' => 'Produs exclus:',
            'title_plural' => 'Produse excluse:',
            'class' => 'excluded-products',
            'type' => 'product'
        ],
        'categories_include' => [
            'title_singular' => 'Categorie inclusă:',
            'title_plural' => 'Categorii incluse:',
            'class' => 'included-categories',
            'type' => 'category'
        ],
        'categories_exclude' => [
            'title_singular' => 'Categorie exclusă:',
            'title_plural' => 'Categorii excluse:',
            'class' => 'excluded-categories',
            'type' => 'category'
        ]
    ];

    $has_content = false;

    foreach ($sections as $var_name => $section) {
        if (!empty($$var_name)) {
            $has_content = true;
            break;
        }
    }

    if ($has_content) {
        echo '<div class="gfwcg-coupon-details-container">';

        foreach ($sections as $var_name => $section) {
            if (!empty($$var_name)) {
                render_section($var_name, $$var_name, $section);
            }
        }

        echo '</div>'; // Close gfwcg-coupon-details-container
    }

    return ob_get_clean();
}

function render_section($var_name, $items, $section) {
    $count = count($items);
    $title = $count > 1 ? $section['title_plural'] : $section['title_singular'];
    $list_class = $section['type'] === 'product' ? 'gfwcg-product-list' : 'gfwcg-category-list';

    echo "<div class=\"gfwcg-coupon-section gfwcg-{$section['class']}\"><h4>{$title}</h4><ul class=\"{$list_class}\">";

    foreach ($items as $item_id) {
        if ($section['type'] === 'product') {
            render_product($item_id);
        } else {
            render_category($item_id);
        }
    }

    echo '</ul></div>';
}

function render_product($product_id) {
    $product = wc_get_product($product_id);
    if ($product) {
        $thumbnail = get_the_post_thumbnail($product->get_id(), 'thumbnail');
        echo '<li><a href="' . get_permalink($product->get_id()) . '">' . $thumbnail . esc_html($product->get_name()) . '</a></li>';
    }
}

function render_category($category_id) {
    $category = get_term($category_id, 'product_cat');
    if ($category && !is_wp_error($category)) {
        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
        $image = wp_get_attachment_image($thumbnail_id, 'thumbnail');
        echo '<li><a href="' . get_term_link($category) . '">' . $image . esc_html($category->name) . '</a></li>';
    }
}