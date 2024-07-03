<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
exit;} ?>

<tr valign="top">
    <th scope="row">
        <label><?php echo esc_html( $value['title'] ); ?></label>
    </th>
    <td>
        <div class="btn-wrap" id="<?php echo esc_attr( $value['id'] ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'acfw_rebuild_auto_apply_cache' ) ); ?>">
            <button type="button" class="button-primary rebuild_auto_apply_cache" value="rebuild">
                <?php esc_html_e( 'Rebuild auto apply coupon cache', 'advanced-coupons-for-woocommerce' ); ?>
            </button>
            <button type="button" class="button clear_auto_apply_cache" value="clear">
                <?php esc_html_e( 'Clear auto apply coupon cache', 'advanced-coupons-for-woocommerce' ); ?>
            </button>
            <span class="acfw-spinner" style="display:none;">
                <img src="<?php echo esc_url( $spinner_image ); ?>">
            </span>
        </div>
        <p class="acfw-notice" style="display:none; color: #46B450;"></p>
        <p class="description"><?php echo wp_kses_post( $value['desc'] ); ?></p>
    </td>
</tr>

<script type="text/javascript">
jQuery(document).ready(function($){

    $('#<?php echo esc_html( $value['id'] ); ?> button').on( 'click', function() {

        var $button  = $(this),
            $parent  = $button.closest('.btn-wrap'),
            $spinner = $parent.find('.acfw-spinner'),
            $row     = $button.closest('tr'),
            $notice  = $row.find( '.acfw-notice' );

        $button.prop( 'disabled' , true );
        $spinner.show();

        $.post( ajaxurl , {
            action : 'acfw_rebuild_auto_apply_cache',
            nonce  : $parent.data('nonce'),
            type   : $button.val()
        }, function( response ) {

            if ( response.status == 'success' ) {

                $notice.text( response.message );
                $notice.show();

                setTimeout(function() {
                    $notice.fadeOut('fast');
                }, 5000);

            } else 
                alert( response.err_msg );

        }, 'json' ).always(function() {
            $button.prop( 'disabled' , false );
            $spinner.hide();
        });
    });
});
</script>
