<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency           = get_woocommerce_currency_symbol( get_woocommerce_currency() );
$currency_label_min = esc_html__( 'Min', 'viwec-email-template-customizer' ) . " ({$currency})";
$currency_label_max = esc_html__( 'Max', 'viwec-email-template-customizer' ) . " ({$currency})";

?>
<div>
    <div class="viwec-setting-row" data-attr="priority">
        <div class="viwec-option-label"><?php esc_html_e( 'Enter the priority for the template rules. The template whose this value is higher will be given priority', 'viwec-email-template-customizer' ) ?></div>
        <div class="viwec-flex viwec-group-input">
            <span class="viwec-subtotal-symbol"><?php echo esc_html( 'Priority' ); ?></span>
            <input type="number" name="menu_order" value="<?php echo esc_attr( $priority ); ?>">
        </div>
    </div>
    <div class="viwec-setting-row" data-attr="country">
		<?php

		if ( function_exists( 'icl_get_languages' ) || class_exists( 'TRP_Translate_Press' ) ) {
			if ( function_exists( 'icl_get_languages' ) ) {
				$languages = icl_get_languages();

			} else {
				$languagesTRP = trp_get_languages();
				$languages    = [];
				foreach ( $languagesTRP as $key => $value ) {
					$languages[] = [
						'language_code' => $key,
						'native_name'   => $value
					];
				}
			}
			?>
            <div class="viwec-option-label"><?php esc_html_e( 'Apply to languages', 'viwec-email-template-customizer' ) ?></div>
            <select name="viwec_setting_rules[languages][]" class="viwec-select2 viwec-input" multiple data-placeholder="All languages">
				<?php
				foreach ( $languages as $data ) {
					$selected = in_array( $data['language_code'], $languages_selected ) ? 'selected' : '';
					echo "<option value='{$data['language_code']}' {$selected}>{$data['native_name']}</option>";
				}
				?>
            </select>
			<?php
		}
		?>
        <div class="viwec-option-label"><?php esc_html_e( 'Apply to billing countries', 'viwec-email-template-customizer' ) ?></div>
        <select name="viwec_setting_rules[countries][]" class="viwec-select2 viwec-input" multiple data-placeholder="All countries">
			<?php
			$wc_countries       = WC()->countries->get_countries();
			$countries_selected = is_array( $countries_selected ) ? $countries_selected : [];
			foreach ( $wc_countries as $code => $country ) {
				$selected = in_array( $code, $countries_selected ) ? 'selected' : '';
				echo "<option value='{$code}' {$selected}>{$country}</option>";
			}
			?>
        </select>
    </div>

    <div class="viwec-setting-row" data-attr="category">
        <div class="viwec-option-label"><?php esc_html_e( 'Apply to categories', 'viwec-email-template-customizer' ) ?></div>
        <select name="viwec_setting_rules[categories][]" class="viwec-select2 viwec-input" multiple data-placeholder="All categories">
			<?php
			$categories_selected = is_array( $categories_selected ) ? $categories_selected : [];
			$categories          = \VIWEC\INCLUDES\Utils::get_all_categories();
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $cat ) {
					$selected = in_array( $cat['id'], $categories_selected ) ? 'selected' : '';
					echo "<option value='{$cat['id']}' {$selected}>{$cat['name']}</option>";
				}
			}
			?>
        </select>
    </div>

    <div class="viwec-setting-row" data-attr="products">
        <div class="viwec-option-label"><?php esc_html_e( 'Apply to products', 'viwec-email-template-customizer' ) ?></div>
        <select name="viwec_setting_rules[products][]" class="wc-product-search viwec-input" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
                data-action="woocommerce_json_search_products_and_variations" multiple>
			<?php
			$products_selected = is_array( $products_selected ) ? $products_selected : [];
			if ( ! empty( $products_selected ) ) {
				foreach ( $products_selected as $p ) {
					$selected = 'selected';
					echo "<option value='{$p}' {$selected}>" . get_the_title( $p ) . "</option>";
				}
			}
			?>
        </select>
    </div>

    <div class="viwec-setting-row" data-attr="payment_methods">
        <div class="viwec-option-label"><?php esc_html_e( 'Apply to Payments methods', 'viwec-email-template-customizer' ) ?></div>
		<?php
		$woo_payments = WC()->payment_gateways->payment_gateways();
		?>
        <select name="viwec_setting_rules[payment][]" class="wc-payment-method viwec-select2 viwec-input" data-placeholder="<?php esc_attr_e( 'Search for a payment method', 'woocommerce' ); ?>"
                data-action="woocommerce_json_search_payment_method" multiple>
            <option value=""><?php esc_html_e( 'All payment methods', 'b2bc-wholesale-solution-for-woocommerce' ); ?></option>

			<?php
			$payments_selected = is_array( $payments_selected ) ? $payments_selected : [];

			if ( ! empty( $woo_payments ) ) {
				foreach ( $woo_payments as $k => $v ) {
					printf( '<option value="%1s" %2s >%3s</option>',
						esc_attr( $k ), selected( in_array( $k, $payments_selected ), true ), wp_kses_post( $v->method_title ?? $v->title ?? $k ) );
				}
			}
			?>
        </select>
    </div>

    <div class="viwec-setting-row" data-attr="price_type_order">
        <div class="viwec-option-label"><?php esc_html_e( 'Choose price type apply to min/max price order( Default: Total)', 'viwec-email-template-customizer' ) ?></div>
        <div class="viwec-flex viwec-group-input">
            <select name="viwec_setting_rules[price_type]" class="viwec-input" data-placeholder="<?php esc_attr_e( 'Choose rule price type', 'woocommerce' ); ?>">
                <option value=""><?php echo esc_html_e( "Choose rule price type", "viwec-email-template-customizer" ); ?></option>
                <option value="total" <?php echo esc_attr( selected( 'total', $price_type ) ); ?>><?php echo esc_html_e( "Order Total", "viwec-email-template-customizer" ); ?></option>
                <option value="subtotal" <?php echo esc_attr( selected( 'subtotal', $price_type ) ); ?>><?php echo esc_html_e( "Order Subtotal", "viwec-email-template-customizer" ); ?></option>
            </select>
        </div>
    </div>
    <div class="viwec-setting-row" data-attr="min_order">
        <div class="viwec-option-label"><?php esc_html_e( 'Min price order', 'viwec-email-template-customizer' ) ?></div>
        <div class="viwec-flex viwec-group-input">
            <span class="viwec-subtotal-symbol"><?php echo esc_html( $currency_label_min ); ?></span>
            <input type="text" name="viwec_setting_rules[min_price]" value="<?php echo esc_attr( $min_price ) ?>">
        </div>
    </div>
    <div class="viwec-setting-row" data-attr="max_order">
        <div class="viwec-option-label"><?php esc_html_e( 'Max price order', 'viwec-email-template-customizer' ) ?></div>
        <div class="viwec-flex viwec-group-input">
            <span class="viwec-subtotal-symbol"><?php echo esc_html( $currency_label_max ); ?></span>
            <input type="text" name="viwec_setting_rules[max_price]" value="<?php echo esc_attr( $max_price ) ?>">
        </div>
    </div>
	<?php
    do_action( 'viwec_after_section_email_rule',$template_ID ); ?>

</div>
