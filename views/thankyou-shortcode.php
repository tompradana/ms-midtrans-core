<div id="payment-<?php echo $payment_gateway->id; ?>-details" class="ms-midtrans-core-payment-details shortcode">
	<?php if ( '' <> $payment_gateway->get_option( 'instructions' ) ) : ?>
		<div class="ms-midtrans-payment-instruction">
			<?php echo ms_midtrans_core_payment_instruction( $order_id, $payment_gateway ); ?>
		</div>
	<?php endif; ?>
</div>