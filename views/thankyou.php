<section id="payment-<?php echo $payment_gateway->id; ?>-details" class="ms-midtrans-core-payment-details">
	<h2><?php _e( 'Your Payment Details', 'ms-midtrans-core' ); ?></h2>
	<?php if ( '' <> $payment_gateway->get_option( 'instructions' ) ) : ?>
		<div class="ms-midtrans-payment-instruction">
			<?php echo ms_midtrans_core_payment_instruction( $order_id, $payment_gateway ); ?>
		</div>
	<?php endif; ?>
</section>