<?php
/**
 * Redeem loyalty points checkout row template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/lpfw-accordion.php.
 *
 * @version 1.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
} ?>

<p class="lpfw-user-points-summary">
    <?php
    echo wp_kses_post(
        sprintf(
            $labels['balance_text'],
            '<strong>' . $user_points . '</strong>',
            '<span class="points-name">' . strtolower( $points_name ) . '</span>',
            '<strong>' . $points_worth . '</strong>'
        )
    );
    ?>
</p>
<p class="description">
    <?php
    echo wp_kses_post(
        sprintf(
            $labels['instructions'],
            '<span class="max-points">' . $max_points . '</span>',
            '<span class="points-name">' . strtolower( $points_name ) . '</span>',
        )
    );
?>
</p>

<?php
    woocommerce_form_field(
        'lpfw_redeem_loyalty_points',
        array(
            'id'          => 'lpfw_redeem_loyalty_points',
            'type'        => 'lpfw_redeem_loyalty_points',
            'value'       => '',
            'placeholder' => sprintf( $labels['placeholder'], strtolower( $points_name ) ),
            'min_points'  => $min_points,
            'max_points'  => $max_points,
        )
    );
