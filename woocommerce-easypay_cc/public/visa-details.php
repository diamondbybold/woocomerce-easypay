<?php

if (!isset($_GET['e']) || !isset($_GET['r']) || !isset($_GET['v']) || !isset($_GET['c']) || !isset($_GET['l']) || !isset($_GET['t_key']))
    exit();

$explodedFilePath = explode('wp-content', __FILE__);
$wpLoadFilePath   = reset($explodedFilePath) . '/wp-load.php';

if (!is_file($wpLoadFilePath)) {
    exit();
}

require_once $wpLoadFilePath;

global $wpdb;

$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?>' . PHP_EOL;
//Output XML
$xml.= '<get_detail>' . PHP_EOL;

//For scope reasons
$result;
try {
    $order = new WC_Order($_GET['t_key']);
    
    //Fetch Payment Detail
    $sql = 'SELECT * FROM ' . $wpdb->prefix . 'easypay_notifications
            WHERE   ep_entity       = ' . $_GET['e'] . ' AND 
                    ep_reference    = ' . $_GET['r'] . ' AND 
                    ep_value        = ' . $_GET['v'] . ' AND 
                    t_key           = ' . $_GET['t_key'];

    $result = $wpdb->get_row($sql);
    
    //Header
    $xml.= '<ep_status>'      . 'ok'                  .'</ep_status>' . PHP_EOL;
    $xml.= '<ep_message>'     . 'success'             .'</ep_message>' . PHP_EOL;
    $xml.= '<ep_entity>'      . $result->ep_entity    .'</ep_entity>' . PHP_EOL;
    $xml.= '<ep_reference>'   . $result->ep_reference .'</ep_reference>' . PHP_EOL;
    $xml.= '<ep_value>'       . $result->ep_value     .'</ep_value>' . PHP_EOL;
    $xml.= '<t_key>'          . $result->t_key        .'</t_key>' . PHP_EOL;
    //Order Information
    $xml.= '<order_info>' . PHP_EOL;

    $xml.= '<total_taxes>'            . ((double) $order->order_total - (double) $order->order_tax) . '</total_taxes>' . PHP_EOL;
    $xml.= '<total_including_taxes>'  . $order->order_total           . '</total_including_taxes>' . PHP_EOL;
    $xml.= '<bill_fiscal_number>'     . (isset($order->order_custom_fields['_billing_fiscal_number'][0]) ? $order->order_custom_fields['_billing_fiscal_number'][0] : '')                . '</bill_fiscal_number>' . PHP_EOL;
    $xml.= '<shipp_fiscal_number>'    . (isset($order->order_custom_fields['_shipping_fiscal_number'][0]) ? $order->order_custom_fields['_shipping_fiscal_number'][0] : '')                . '</shipp_fiscal_number>' . PHP_EOL;
    $xml.= '<bill_name>'              . $order->billing_first_name    . ' ' . $order->billing_last_name       . '</bill_name>' . PHP_EOL;
    $xml.= '<shipp_name>'             . $order->shipping_first_name   . ' ' . $order->shipping_last_name      . '</shipp_name>' . PHP_EOL;
    $xml.= '<bill_address_1>'         . $order->billing_address_1     . '</bill_address_1>' . PHP_EOL;
    $xml.= '<shipp_adress_1>'         . $order->shipping_address_1    . '</shipp_adress_1>' . PHP_EOL;
    $xml.= '<bill_address_2>'         . $order->billing_address_2     . '</bill_address_2>' . PHP_EOL;
    $xml.= '<shipp_adress_2>'         . $order->shipping_address_2    . '</shipp_adress_2>' . PHP_EOL;
    $xml.= '<bill_city>'              . $order->billing_city          . '</bill_city>' . PHP_EOL;
    $xml.= '<shipp_city>'             . $order->shipping_city         . '</shipp_city>' . PHP_EOL;
    $xml.= '<bill_zip_code>'          . $order->billing_postcode      . '</bill_zip_code>' . PHP_EOL;
    $xml.= '<shipp_zip_code>'         . $order->shipping_postcode     . '</shipp_zip_code>' . PHP_EOL;
    $xml.= '<bill_country>'           . $order->billing_country       . '</bill_country>' . PHP_EOL;
    $xml.= '<shipp_country>'          . $order->shipping_country      . '</shipp_country>' . PHP_EOL;

    $xml.= '</order_info>' . PHP_EOL;

    //Order Items
    $xml.= '<order_detail>' . PHP_EOL;
    foreach ( $order->get_items() as $item ) {
        $xml.= '<item>' . PHP_EOL;
        $xml.= '<item_description>'   . $item['name']       . '</item_description>' . PHP_EOL;
        $xml.= '<item_quantity>'      . $item['qty']        . '</item_quantity>' . PHP_EOL;
        $xml.= '<item_total>'         . $item['line_total'] . '</item_total>' . PHP_EOL;
        $xml.= '</item>' . PHP_EOL;
    }
    $xml.= '</order_detail>' . PHP_EOL;

} catch (Exception $ex) {
    $xml.= '<ep_status>'      . 'err'                   .'</ep_status>' . PHP_EOL;
    $xml.= '<ep_message>'     . $ex->getMessage()       .'</ep_message>' . PHP_EOL;
    $xml.= '<ep_entity>'      . $result->ep_entity      .'</ep_entity>' . PHP_EOL;
    $xml.= '<ep_reference>'   . $result->ep_reference   .'</ep_reference>' . PHP_EOL;
    $xml.= '<ep_value>'       . $result->ep_value       .'</ep_value>' . PHP_EOL;
}

$xml.= '</get_detail>' . PHP_EOL;
echo $xml;