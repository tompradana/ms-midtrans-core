<div id="payment-<?php echo $payment_gateway->id; ?>-details" class="ms-midtrans-core-payment-details shortcode">
	<?php if ( $order_status == 'completed' ) : ?>
		<p class="ms-midtrans-payment-success"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your payment has been received.', 'ms-midtrans-core' ), null ); ?></p>
	<?php elseif ( $order_status == 'cancelled' ) : ?>
		<p class="ms-midtrans-payment-failed"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Ooops! Your payment is failed. Please contact administrator to process your order.', 'ms-midtrans-core' ), null ); ?></p>
	<?php else : ?>
		<?php if ( isset( $response['status_code'] ) && $response['status_code'] == '200' ) : ?>
			<p class="ms-midtrans-payment-processing"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Payment received, the order is awaiting fulfillment.', 'ms-midtrans-core' ), null ); ?></p>
		<?php else : ?>
			<?php if ( '' <> $payment_gateway->get_option( 'instructions' ) ) : ?>
				<div class="ms-midtrans-payment-instruction">
					<?php echo ms_midtrans_core_payment_instruction( $order_id, $payment_gateway ); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
</div>