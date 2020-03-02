<?php
// common
function ms_midtrans_core_payment_gateways( $methods ) {
	$methods[] = 'MS_Midtrans_Core_VA_Gateway'; 
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'ms_midtrans_core_payment_gateways' );

// print out instruction
function ms_midtrans_core_payment_instruction( $order_id, $payment ) {
	$instruction 		= str_replace('ms_midtrans_core_', '', $payment->id );
	$payment_details 	= maybe_unserialize( get_post_meta( $order_id, '_ms_midtrans_payment_response', true ) );
	$payment_status 	= maybe_unserialize( get_post_meta( $order_id, '_ms_midtrans_payment_status', true ) );
	$payment_expiry 	= get_post_meta( $order_id, '_ms_midtrans_payment_expiry', true );
	$transaction_time 	= $payment_details['transaction_time'];

	// data
	$amount 			= wc_price( $payment_details['gross_amount'] );
	$vanumber 			= ms_midtrans_core_vanumber( $payment_details );
	$expiry_date 		= date_i18n( 'Y-m-d', $payment_expiry, true );
	$expiry_time 		= date_i18n( 'H:i:s', $payment_expiry, true );
	$expiry_datetime 	= date_i18n( 'Y-m-d H:i:s', $payment_expiry, true );

	// build html
	$html = $payment->get_option('instructions');
	$html = str_replace('{{amount}}', $amount, $html );
	$html = str_replace('{{vanumber}}', $vanumber, $html );
	$html = str_replace('{{expiry_date}}', $expiry_date, $html );
	$html = str_replace('{{expiry_time}}', $expiry_time, $html );
	$html = str_replace('{{expiry_datetime}}', $expiry_datetime, $html );

	// load html
	include( MS_MIDTRANS_CORE_DIR . '/views/instructions/'.$instruction.'.php' );
}

function ms_midtrans_core_vanumber( $payment_details ) {
	if ( isset( $payment_details['va_numbers'] ) ) {
		$number = sprintf( '%s %s', strtoupper( $payment_details['va_numbers'][0]['bank'] ), $payment_details['va_numbers'][0]['va_number'] );
	} else if ( isset( $payment_details['permata_va_number'] ) ) {
		$number = sprintf( __( 'PERMATA %s', 'ms-midtrans-core' ), $payment_details['permata_va_number'] );
	} else {
		$number = sprintf( __( 'Bill Key: %s', 'ms-midtrans-core' ), $payment_details['bill_key'] );
		$number .= "\r\n";
		$number .= sprintf( __( 'Biller Code: %s', 'ms-midtrans-core' ), $payment_details['biller_kode'] );
	}
	return $number;
}

// helper
if ( !function_exists( 'wp_is_mobile' ) ) {
	function wp_is_mobile() {
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}
}