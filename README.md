Here’s the complete and detailed README for your WordPress plugin in Markdown format:

# Gravity Forms WooCommerce Coupon Generator

This WordPress plugin automates the creation of WooCommerce discount coupons based on user submissions through Gravity Forms. It integrates directly with WooCommerce to apply specified discount rules dynamically, enhancing e-commerce functionalities.

## Features

- **Dynamic Coupon Creation**: Automatically generates coupons when a user submits a form.
- **Customizable Coupon Attributes**: Set attributes like discount amount, minimum spending, usage limit, and more.
- **Targeted Discounts**: Allows inclusion and exclusion of specific products and categories.
- **Shortcode Integration**: Easily embeddable via a shortcode with extensive attributes to customize functionality.

## Prerequisites

- **WordPress**: Version 5.0 or higher.
- **Gravity Forms**: Must be installed and activated.
- **WooCommerce**: Must be installed and activated.
- **Jet Engine (Optional)**: Provides enhanced features but is not required for basic functionality.

## Installation

1. **Download the Plugin**: Download the zip file from the GitHub repository or clone it directly.
2. **Install via WordPress Dashboard**:
   - Navigate to `Plugins > Add New > Upload Plugin`.
   - Choose the downloaded plugin zip file.
   - Click on `Install Now` and activate the plugin.
3. **Manual Installation**:
   - Extract the plugin folder from the zip file.
   - Upload the plugin folder to your `/wp-content/plugins/` directory, using your favorite FTP client.
   - Navigate to the `Plugins` dashboard page and activate the plugin.

## Usage

To use this plugin, place the `[gf_nl_sub_discount_info]` shortcode in posts, pages, or widgets where you want the associated form to appear. Configure the shortcode with necessary attributes to customize the coupon generation process.

### Shortcode

#### Basic Usage

```plaintext
[gf_nl_sub_discount_info]
```

With Attributes

```
[gf_nl_sub_discount_info discount_amount="20" min_spending_amount="100" usage_limit="2" discount_type="percent" products_include="12,34" products_exclude="56" categories_include="78" categories_exclude="90" coupon_expiry_date="2024-12-31" individual_use="yes" exclude_sale_items="no"]
```

### Attributes

Each attribute controls a specific aspect of the coupon:

	•	discount_amount (int): The amount of the discount, which could be a percentage or a fixed amount depending on discount_type.
	•	min_spending_amount (int): Minimum spending required to use the coupon.
	•	usage_limit (int): The maximum number of times the coupon can be used.
	•	discount_type (string): Can be ‘percent’, ‘fixed_cart’, or ‘fixed_product’.
	•	products_include (array): Comma-separated list of product IDs that the coupon applies to.
	•	products_exclude (array): Comma-separated list of product IDs excluded from the discount.
	•	categories_include (array): Comma-separated list of category IDs to include.
	•	categories_exclude (array): Comma-separated list of category IDs to exclude.
	•	coupon_expiry_date (string): The expiration date of the coupon (format: Y-m-d).
	•	individual_use (bool): Whether the coupon is for individual use (cannot be used with other coupons).
	•	exclude_sale_items (bool): Whether the coupon should be applied to items already on sale.

## Developer

	•	GitHub: mateitudor
	•	Name: Tudor Matei
	•	Website: https://mateitudor.com

## Support

For support, feature requests, or to report bugs, please use the GitHub issues page.

For detailed inquiries or to engage the developer for custom projects, visit https://mateitudor.com.

## License

This plugin is freely available under a custom license humorously termed “fuck licenses,” which permits both personal and commercial use without restrictions.

This README provides a comprehensive overview of your plugin, including installation instructions, usage details, shortcode explanations, and contact information for support and further engagement.