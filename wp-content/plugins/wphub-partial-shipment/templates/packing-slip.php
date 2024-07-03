<?php
global $wpdb;
$shipment_id = isset($args['shipment-id']) ? absint($args['shipment-id']) : 0;
$order = is_a($args['order'],'WC_Order') ? $args['order'] : new stdClass();
$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."partial_shipment_items WHERE shipment_id=".$shipment_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo __('Packing Slip','wphub-wc-invoice'); ?> #<?php echo $shipment_id; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<style>
    @page {
        margin:10px;
    }
    body {
        margin-top:15px;
        background:#ffffff;
        font-size:16px;
        font-weight:400;
        line-height:22px;
        color: #212529;
        -webkit-text-size-adjust: 100%;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        font-family: Helvetica, sans-serif;
    }
    .invoice{
        background: #fff;
        padding: 20px;
    }
    table.table-main,
    table.tab-foot{
        width:100%;
    }
    table.table-main > tr > td{
        width:50%;
    }
    table.table-main tr td span{
        display:block;
    }
    .txt-r{
        text-align:right;
    }
    .ft22{
        font-size:22px;
    }
    .ft20{
        font-size:20px;
    }
    .ft18{
        font-size:18px;
    }
    .ft16{
        font-size:16px;
    }
    table.table-main,
    table.items{
        width:100%;
        border-spacing:0;
        border-collapse:collapse;
        page-break-inside: avoid;
    }
    table.tab-foot{
        width:100%;
        border-spacing:0;
        page-break-inside: avoid;
    }
    table.items{
        margin-top:15px;
        border-top: 1px solid #687173;
        border-bottom: 1px solid #687173;
    }
    table.items thead tr th{
        background:#EAECED;
        padding:6px 0;
    }
    table.items thead tr th,
    table.items tbody tr td{
        border-collapse: collapse;
        margin:2px 0;
    }
    table.items thead tr th.no,
    table.items tbody tr td.no{
        width:5%;
    }
    table.items thead tr th.title,
    table.items tbody tr td.title{
        width:50%;
        text-align:left;
    }
    table.items thead tr th.qty,
    table.items tbody tr td.qty,
    table.items thead tr th.unit,
    table.items tbody tr td.unit,
    table.items thead tr th.total,
    table.items tbody tr td.total{
        width:15%;
    }
    table.items tbody tr td.no,
    table.items tbody tr td.qty,
    table.items tbody tr td.unit,
    table.items tbody tr td.total{
        text-align:center;
    }
    table.items tbody tr td.title span.desc{
        color:#687173;
        font-size: 12px;
    }
    table.items tbody tr:nth-child(even){
        background-color:#EEEEEE;
    }
    table.tab-foot tbody tr td.foot-sp{
        width:56%;
    }
    table.tab-foot tbody tr td.foot-st{
        width:22%;
        border-bottom: 1px solid #687173;
        font-size:18px;
        padding:6px 0 6px 12px;
    }
    .invoice-footer {
        border-top: 1px solid #ddd;
        padding-top: 10px;
        font-size: 10px;
    }
    .invoice-footer p{
        padding:0;
        margin:0;
        line-height:16px;
    }
    .text-center{
        text-align:center;
    }
    .in-btm{
        padding-right:8px;
    }
    .inb{
        font-weight:600;
        text-transform:capitalize;
    }
    .in-label{
        display: inline-block;
        padding: 6px 24px;
        margin: 6px 0;
        border-radius: 6px;
    }
    .in-paid{
        background:#28a745;
        color:#ffffff;
    }
    .in-due{
        background:#ffc107;
        color:#343a40;
    }
    strong{
        padding-left:4px;
    }
    table.table-main tr td span.woocommerce-Price-amount,
    table.table-main tr td span.amount,
    table.table-main tr td span.woocommerce-Price-currencySymbol{
        display:inline;
    }
    table.table-main tr td span.woocommerce-Price-currencySymbol{
        font-family:DejaVu Sans,Helvetica,sans-serif;
    }
    .ft-spacer{
        display:block;
        margin:15px;
    }
    ul.wc-item-meta,
    ul.wc-item-meta li{
        list-style: none;
        padding:0;
        margin:0;
        font-size:12px;
        line-height:14px;
        vertical-align:text-bottom;
    }
    ul.wc-item-meta li p{
        display:inline-block;
        padding:0;
        margin:0;
        font-size: 12px;
        vertical-align:text-top;
    }
    ul.wc-item-meta{
        margin: 3px 0;
    }
    ul.wc-item-meta li strong{
        vertical-align:text-top;
    }
</style>
<body>
<div class="invoice">
    <table class="table-main">
        <tr>
            <td>{invoice_logo}</td>
            <td class="txt-r ft22"><?php echo __('Packing Slip','wphub-wc-invoice'); ?></td>
        </tr>
        <tr>
            <td></td>
            <td class="txt-r">
                <span class="ft18 inb">{your_company_name}</span>
                <span class="ft16">{your_company_address}</span>
                <span class="ft16">{your_company_phone}</span>
                <span class="ft16">{your_company_email}</span>
            </td>
        </tr>
        <tr>
            <td><div class="ft-spacer"></div></td>
            <td><div class="ft-spacer"></div></td>
        </tr>
        <tr>
            <td>
                <span class="ft22"><?php echo __('BILL TO','wphub-wc-invoice'); ?></span>
                <span class="ft18 inb">{billing_first_name} {billing_last_name}</span>
                <span class="ft18">{billing_company_name}</span>
                <span class="ft16">{billing_address}</span>
                <span class="ft16">{billing_phone}</span>
                <span class="ft16">{billing_email}</span>
                <span class="ft16 inb">{billing_vat_number}</span>
            </td>
            <td class="txt-r">
                <span class="ft22"><?php echo __('SHIP TO','wphub-wc-invoice'); ?></span>
                <span class="ft18 inb">{shipping_first_name} {shipping_last_name}</span>
                <span class="ft18">{shipping_company_name}</span>
                <span class="ft16">{shipping_address}</span>
                <span class="ft16 inb">{invoice_order_number}</span>
                <span class="ft16 inb">{order_date}</span>
            </td>

        </tr>
        <tr>
            <td colspan="2">
                <table class="items">
                    <thead>
                    <tr>
                        <th class="no">#</th>
                        <th class="title"><?php echo __('Item','wphub-wc-invoice'); ?></th>
                        <th class="qty"><?php echo __('Qty','wphub-wc-invoice'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					$n=1;
					if(is_array($items) && !empty($items)){
						foreach($items as $shipment_item){
							$item = $order->get_item($shipment_item->item_id);
							if(!is_a($item,'WC_Order_Item_Product')){
								continue;
							}

							$product = $item->get_product();
							?>
                            <tr>
                                <td class="no"><?php echo esc_attr($n); ?></td>
                                <td class="title">
                                    <span><?php echo esc_attr($item->get_name()); ?></span>
									<?php
									$sku = $product->get_sku();
									if($sku!=''){
										echo '<span class="desc">'.__('SKU:','wphub-wc-invoice').' '.esc_attr($sku).'</span>';
									}
									wc_display_item_meta($item);
									?>
                                </td>
                                <td class="qty"><?php echo esc_attr($shipment_item->item_qty); ?></td>
                            </tr>
							<?php
							$n++;
						}
					}
					?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-r">
                <table class="tab-foot">
                    <tbody>
                    <tr>
                        <td class="foot-sp"></td>
                        <td class="foot-st"><?php echo __('Shipping Method :','wphub-wc-invoice'); ?></td>
                        <td class="foot-st"><?php echo wp_kses_post($order->get_shipping_method()); ?></td></tr>
                    <tr>
                    <tr>
                        <td class="foot-sp"></td>
                        <td class="foot-st"><?php echo __('Payment Method :','wphub-wc-invoice'); ?></td>
                        <td class="foot-st"><?php echo wp_kses_post($order->get_payment_method_title()); ?></td></tr>
                    <tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <div class="invoice-footer">
        <p class="text-center">{invoice_copyright_note}</p>
    </div>
</div>
<script type='text/php'>
  if(isset($pdf)){
    $x = 530;
    $y = 820;
    $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
    $font = $fontMetrics->get_font("helvetica","bold");
    $size = 8;
    $color = array(0,0,0);
    $word_space = 0.0;
    $char_space = 0.0;
    $angle = 0.0;
    $pdf->page_text($x,$y,$text,$font,$size,$color,$word_space,$char_space,$angle);
  }
</script>
</body>
</html>