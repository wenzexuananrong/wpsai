<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<style type="text/css">
#lpfw-points-review-order-metabox table {
    margin: 0 0 1em;
    width: 100%;
    border-collapse: collapse;
}

#lpfw-points-review-order-metabox table th {
    text-align: left;
}

#lpfw-points-review-order-metabox table tr > * {
    padding: 4px 5px;
    font-size: 0.96em;
    border-bottom: 1px solid #ccc;
}

#lpfw-points-review-order-metabox table tr.revoke > * {
    background: #f7e6e6;
}
#lpfw-points-review-order-metabox table .pending_earn > * {
    background: #fffbde;
}
</style>

<?php if ( is_array( $entries ) && ! empty( $entries ) ) : ?>

    <?php if ( $is_pending ) : ?>
        <p>
        <?php
            echo wp_kses_post(
                /* Translators: %1$s: <em>, %2$s: </em> */
                sprintf( __( 'The customer has the following %1$spending%2$s points for this order:', 'loyalty-program-for-woocommerce' ), '<em>', '</em>' )
            );
        ?>
        </p>

    <?php else : ?>
        <p><?php esc_html_e( 'The customer has earned the following points for this order:', 'loyalty-program-for-woocommerce' ); ?></p>
    <?php endif; ?>

    <table class="point-entries">
        <thead>
            <tr>
                <th class="activity"><?php esc_html_e( 'Activity', 'loyalty-program-for-woocommerce' ); ?></th>
                <th class="points"><?php esc_html_e( 'Points', 'loyalty-program-for-woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $entries as $entry ) : ?>
            <tr class="<?php echo esc_attr( $entry->get_prop( 'action' ) . ' ' . $entry->get_prop( 'type' ) ); ?>">
                <td class="activity"><?php echo esc_html( \LPFW()->Types->get_activity_label( $entry->get_registry() ) ); ?></td>
                <td class="points"><?php echo esc_html( $entry->get_prop( 'points' ) ); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <th><?php esc_html_e( 'Total', 'loyalty-program-for-woocommerce' ); ?></th>
            <th><?php echo esc_html( $total_points ); ?></th>
        </tfoot>
    </table>

    <p><?php echo wp_kses_post( __( 'Points can be revoked or unrevoked in the <strong>Order Actions</strong> form above.', 'loyalty-program-for-woocommerce' ) ); ?></p>

<?php else : ?>
    <p class="no-entries"><?php esc_html_e( 'There are no points earned for this order', 'loyalty-program-for-woocommerce' ); ?></p>
<?php endif; ?>
