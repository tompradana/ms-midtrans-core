<?php
// Init
function ms_midtrans_core_va_init() {
	
	class MS_Midtrans_Core_VA_Gateway extends WC_Payment_Gateway {
		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->id 					= 'ms_midtrans_core_va';
			$this->icon 				= ''; 
			$this->has_fields 			= true; // in case you do not need a custom credit card form
			$this->method_title 		= __( 'MS Midtrans - Virtual Account', 'ms-midtrans-core' );
			$this->method_description 	= __( 'Virtual Account payment gateway using Midtrans core API', 'ms-midtrans-core' );
		 
			/**
			 * gateways can support subscriptions, refunds, saved payment methods,
			 */
			$this->supports = array(
				'products'
			);
		 
			// Method with all the options fields
			$this->init_form_fields();
		 
			// Load the settings.
			$this->init_settings();
			$this->title 			= $this->get_option( 'title' );
			$this->description 		= $this->get_option( 'description' );
			$this->instructions 	= $this->get_option( 'instructions' );
			$this->enabled 			= $this->get_option( 'enabled' );
			$this->sandbox_mode 	= 'yes' === $this->get_option( 'sandbox_mode' );
			$this->client_key 		= $this->get_option( 'client_key' );
			$this->server_key 		= $this->get_option( 'server_key' );
			$this->expiry_time 		= $this->get_option( 'expiry_time' );
			$this->expiry_unit 		= $this->get_option( 'expiry_unit' );
			$this->callback_url 	= $this->get_option( 'callback_url' );
			$this->date_format 		= $this->get_option( 'date_format' );
			$this->time_format 		= $this->get_option( 'time_format' );
			$this->notification_url = '';
			$this->api_url 			= $this->sandbox_mode ? 'https://api.sandbox.midtrans.com' : 'https://api.midtrans.com';

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// Print instructions in thank you page
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'payment_instructions' ) );
		 
			/**
			 * Do you need custom scripts?
			 * add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			 */
		 
			/**
			 * You can also register a webhook here
			 * woocommerce_api_{webhook name}
			 * url must be http://domain/wc-api/ms-midtrans-payment-status/
			 */
			add_action( 'woocommerce_api_ms-midtrans-payment-status', array( $this, 'check_payment_status' ) );
		}
 
		/**
		 * Payment settings
		 */
		public function init_form_fields(){
			$this->form_fields = array(
				'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'ms-midtrans-core' ),
					'label'       	=> __( 'Enable MS Midtrans Virtual Account', 'ms-midtrans-core' ),
					'type'        	=> 'checkbox',
					'description' 	=> '',
					'default'     	=> 'no'
				),
				'sandbox_mode' => array(
					'title'       	=> __( 'Sandbox mode', 'ms-midtrans-core' ),
					'label'       	=> __( 'Enable Sandbox Mode', 'ms-midtrans-core' ),
					'type'        	=> 'checkbox',
					'description' 	=> __( 'Place the payment gateway in sandbox mode.', 'ms-midtrans-core' ),
					'default'     	=> 'yes',
					'desc_tip'    	=> true,
				),
				'title' => array(
					'title'       	=> __( 'Title', 'ms-midtrans-core' ),
					'type'        	=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'ms-midtrans-core' ),
					'default'     	=> 'Virtual Account',
					'desc_tip'    	=> true,
				),
				'description' => array(
					'title'       	=> __( 'Description', 'ms-midtrans-core' ),
					'type'        	=> 'textarea',
					'description' 	=> __( 'This controls the description which the user sees during checkout.', 'ms-midtrans-core' ),
					'default'     	=> __( 'Pay with Virtual Account via our super-cool payment gateway.', 'ms-midtrans-core' ),
				),
				'instructions' => array(
					'title'       	=> __( 'Instructions', 'ms-midtrans-core' ),
					'type'        	=> 'textarea_html',
					'description' 	=> __( 'Available tag {{countdown}}, {{expiry_date}}, {{expiry_time}}, {{expiry_datetime}}, {{vanumber}}, {{amount}}', 'ms-midtrans-core' ),
					'default'     	=> __( 'Pay with Virtual Account via our super-cool payment gateway.', 'ms-midtrans-core' ),
					'desc_tip'    	=> false
				),
				'merchant_id'	 	=> array(
					'title'		  	=> __( 'Merchant ID', 'ms-midtrans-core' ),
					'type'		  	=> 'text'	
				),
				'client_key' => array(
					'title'       	=> __( 'Client Key', 'ms-midtrans-core' ),
					'type'        	=> 'text'
				),
				'server_key' => array(
					'title'       	=> __( 'Server Key', 'ms-midtrans-core' ),
					'type'        	=> 'text',
				),
				'expiry_time' => array(
					'title'		  	=> __( 'Payment Expiry Duration', 'ms-midtrans-core' ),
					'type'		  	=> 'number',
					'default'	  	=> 24,
					'step'		  	=> 1,
					'description' 	=> __( 'If blank default expiry time form Midtrans will be used.', 'ms-midtrans-core' )
				),
				'expiry_unit' => array(
					'title'		  	=> __( 'Payment Expiry Unit', 'ms-midtrans-core' ),
					'type'		  	=> 'select',
					'options'	  	=> array(
						'second'  	=> __( 'Second', 'ms-midtrans-core' ),
						'minute'  	=> __( 'Minute', 'ms-midtrans-core' ),
						'hour'	  	=> __( 'Hour', 'ms-midtrans-core' ),
						'day'	  	=> __( 'Day', 'ms-midtrans-core' )
					),
					'default'	  	=> 'hour'		
				),
				'date_format' => array(
					'title'       	=> __( 'Date Format', 'ms-midtrans-core' ),
					'type'        	=> 'text',
					'default'		=> get_option( 'date_format' )
				),
				'time_format' => array(
					'title'       	=> __( 'Date Format', 'ms-midtrans-core' ),
					'type'        	=> 'text',
					'default'		=> get_option( 'time_format' )
				),
				'callback_url' => array(
					'title'       	=> __( 'Callback URL', 'ms-midtrans-core' ),
					'type'        	=> 'text',
					'description' 	=> __( 'If blank default callback will be disabled.', 'ms-midtrans-core' )
				),
				'notifcation_url' => array(
					'title'		  		=> 'Notification URL',
					'type'		  		=> 'hidden',
					'custom_attributes' => array(
						'disabled' => 'true'	
					),
					'description' 		=> '<code>' . home_url( '/wc-api/ms-midtrans-payment-status/' ) . '</code><br/>Please make sure permalink already set to %postname%'
				)
			);
		}
 
		/**
		 * Payment fields: Bank selections
		 */
		public function payment_fields() {
			if ( $this->description ) {
				if ( $this->sandbox_mode ) {
					$this->description .= ' TEST MODE ENABLED. No payment placed.';
					$this->description  = trim( $this->description );
				}
				echo '<fieldset style="background:transparent">' . wpautop( wp_kses_post( $this->description ) ) . '</fieldset>';
			}
			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-form" class=wc-payment-form" style="background:transparent;">'; 
			do_action( 'woocommerce_credit_card_form_start', $this->id );
			?>

			<p>
				<label>
					<input type="radio" name="ms_midtrans_vabank" value="bca">
					<?php _e( 'BCA' ); ?>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="ms_midtrans_vabank" value="mandiri">
					<?php _e( 'Mandiri'); ?>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="ms_midtrans_vabank" value="bni">
					<?php _e( 'BNI' ); ?>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="ms_midtrans_vabank" value="permata">
					<?php _e( 'Permata' ); ?>
				</label>
			</p>

			<?php
			do_action( 'woocommerce_credit_card_form_end', $this->id );
			echo '<div class="clear"></div></fieldset>';
		}
 
		/*
		 * Insert custom scripts here
		 * public function payment_scripts() {}
		 */
 
		/*
		 * Fields validation
		 */
		public function validate_fields() {
			if( empty( $_POST[ 'ms_midtrans_vabank' ] ) ) {
				wc_add_notice(  'Please select a Bank.', 'error' );
				return false;
			}
			return true;
		}
 
		/*
		 * We're processing the payments here
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;
			// we need it to get any order detailes
			$order 	= wc_get_order( $order_id );
			$url 	= $this->api_url . "/v2/charge";
			$body 	= array();
			$bank 	= $_POST['ms_midtrans_vabank'];

			// charge details
			if ( $bank == 'bca' || $bank == 'bni' ) {
				$payment_type = 'bank_transfer';
				$body['bank_transfer'] = array(
					'bank' => $bank
				);
			} else if ( $bank == 'mandiri' ) {
				$payment_type = 'echannel';
				$body['echannel'] = array(
					'bill_info1' => __( 'Payment For:', 'ms-midtrans-core' ),
					'bill_info2' => sprintf( __( 'Order #%s', 'ms-midtrans-core' ), $order_id ),
					'bill_info3' => $order->get_formatted_billing_full_name()
				);
			} else {
				$payment_type = 'permata';
			}

			// payment type
			$body['payment_type'] =  $payment_type;

			// transactions details
			$body['transaction_details'] = array(
				'order_id' 			=> $order_id,
				'gross_amount' 		=> $order->get_total()
			);

			// customer details
			$body['customer_details'] = array(
				'first_name' 	=> $order->get_billing_first_name(),
				'last_name' 	=> $order->get_billing_last_name(),
				'email' 		=> $order->get_billing_email(),
				'phone' 		=> $order->get_billing_phone()
			);

			// items 
			$items = $order->get_items();
			$body['item_details'] = array();
			foreach( $items as $item ) {
				$body['item_details'][] = array(
					'id' 		=> $item->get_id(),
					'price' 	=> ceil($order->get_item_subtotal( $item, false )),
					'quantity' 	=> $item->get_quantity(),
					'name' 		=> $item->get_name()
				);
			}

			// shipping
			if ( $order->get_total_shipping() > 0 ) {
				$body['item_details'][] = array(
					'id' 		=> 'shipping',
					'price' 	=> ceil($order->get_total_shipping()),
					'quantity' 	=> 1,
					'name' 		=> __( 'shipping', 'ms-midtrans-core' )
				);
			}

			// tax
			if ( $order->get_total_tax() > 0 ) {
				$body['item_details'][] = array(
					'id' 		=> 'tax',
					'price' 	=> ceil( $order->get_total_tax() ),
					'quantity' 	=> 1,
					'name' 		=> __( 'tax', 'ms-midtrans-core' )
				);
			}

			// discount
			if ( $order->get_total_discount() > 0 ) {
				$body['item_details'][] = array(
					'id' 		=> 'discount',
					'price' 	=> ceil( $order->get_total_discount() ) *-1,
					'quantity' 	=> 1,
					'name' 		=> __( 'discount', 'ms-midtrans-core' )
				);
			}

			// fees
			if ( sizeof( $order->get_fees() ) > 0 ) {
				$fees = $order->get_fees();
				$i = 0;
				foreach( $fees as $item ) {
					$body['item_details'][] = array(
						'id' 		=> 'fee' . $i,
						'price' 	=>  ceil( $item['line_total'] ),
						'quantity' 	=> 1,
						'name' 		=> $item['name'],
					);
				$i++;
			  }
			}

			// recalculate gross amount
			$data_items = $body['item_details'];
			$total_amount = 0;
			foreach( $data_items as $dataitem ) {
				$total_amount+=($dataitem ['price']*$dataitem['quantity']);
			}

			// set new gross amount
			$body['transaction_details']['gross_amount'] = $total_amount;

			// expiry
			if ( '' != $this->expiry_time && $this->expiry_time != '0' ) {
				$body['custom_expiry'] = array(
					'expiry_duration' 	=> $this->expiry_time,
					'unit' 				=> $this->expiry_unit
				);
			}

			// parameter
			$args = array(
				'headers' => array(
					'Accept' 		=> 'application/json',
					'Content-Type' 	=> 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $this->server_key . ':' )
				),
				'body' => json_encode( $body )
			);

			/*
			 * Your API interaction could be built with wp_remote_post()
			 */
			$response = wp_remote_post( $url, $args );

			if ( !is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( $response_body['status_code'] == '201' ) {
					// save response
					update_post_meta( $order_id, '_ms_midtrans_payment_response', maybe_serialize( $response_body ) );

					// set expiry time
					$this->set_payment_expiry( $order_id, $response_body['transaction_time'], $this->expiry_time, $this->expiry_unit );

					// note
					$order->add_order_note( __( 'Order placed.', 'ms-midtrans-core' ) );

					// note
					$order->add_order_note( __( 'Midtrans HTTP notifications received: ', 'ms-midtrans-core' ) . $response_body['status_message'] . '. ' . $this->method_title );

					// Empty cart
					$woocommerce->cart->empty_cart();

					// note
					$order->add_order_note( __( 'Order status Pending.', 'ms-midtrans-core' ) );
		 
					// Redirect to the thank you page
					return array(
						'result' 	=> 'success',
						'redirect' 	=> $this->get_return_url( $order )
					);
				} else {
					// Note
					$order->add_order_note( __( 'Midtrans HTTP notifications received: ', 'ms-midtrans-core' ) . $response_body['status_message'] . '. ' . $this->method_title );
					wc_add_notice( $response_body['status_message'], 'error' );
					return;
				}
			} else {
				wc_add_notice(  'Connection error.', 'error' );
				return;
			}
		}

		/**
		 * Thank you
		 */
		public function payment_instructions( $order_id ) {
			$resposne 			= get_post_meta( $order_id, '_ms_midtrans_payment_response', true );
			$order 				= wc_get_order( $order_id );
			$payment_gateway 	= wc_get_payment_gateway_by_order( $order );

			// include html
			include( MS_MIDTRANS_CORE_DIR . '/views/thankyou.php' );
		}
 
		/*
		 * In case you need a webhook, like instant notification, IPN, etc
		 * Autocomplete on successful payment
		 */
		public function check_payment_status() {
			// get post data
			$body = file_get_contents('php://input');
			if ( $body ) {
				$response	= json_decode( $body ); 
				$order_id 	= $response->order_id;
				$order 		= wc_get_order( $order_id );

				// success
				if ( $response->status_code == '200' ) {
					// note
					$order->add_order_note( 
						sprintf( '%s: %s [%s]. %s', 
							__( 'Midtrans HTTP notifications received', 'ms-midtrans-core' ),
							$response->transaction_status,
							$response->status_code,
							$this->method_title
						)
					);
					
					// processing with note
					wc_reduce_stock_levels( $order_id );

					// $order->payment_complete(); with note
					$order->update_status( 'processing' );

					// completed with note
					$order->update_status( 'completed' );
				} 

				// pending payent
				else if ( $response->status_code == '201' ) {
					// note
					$order->add_order_note( 
						sprintf( '%s: %s [%s]. %s', 
							__( 'Midtrans HTTP notifications received', 'ms-midtrans-core' ),
							$response->transaction_status,
							$response->status_code,
							$this->method_title
						)
					);
				} 

				// failed or denied payment
				else {
					// note
					$order->add_order_note( 
						sprintf( '%s: %s [%s]. %s', 
							__( 'Midtrans HTTP notifications received', 'ms-midtrans-core' ),
							$response->transaction_status,
							$response->status_code,
							$this->method_title
						)
					);
					
					// cancelled
					$order->update_status( 'cancelled', __( 'The order was cancelled due to no payment from customer.', 'ms-midtrans-core') );
					
					// note
					$order->add_order_note( __( 'Order status changed from Pending payment to Cancelled.', 'ms-midtrans-core' ) );
				}

				// save payment status notifications
				update_post_meta( $order_id, '_ms_midtrans_payment_status', maybe_serialize( $response ) );

				/**
				 * $this->write_log( 'Ping from midtrans' );
				 * $this->write_log( $response );
				 */
			}
			exit;
		}

		/**
		 * Custom settings fields
		 */
		public function generate_textarea_html_html( $key, $data ) {
			$field    = $this->plugin_id . $this->id . '_' . $key;
			$defaults = array(
				'class'             => 'button-secondary',
				'css'               => '',
				'custom_attributes' => array(),
				'desc_tip'          => false,
				'description'       => '',
				'title'             => '',
			);

			$data = wp_parse_args( $data, $defaults );
			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
					<?php echo $this->get_tooltip_html( $data ); ?>
				</th>
				<td class="forminp">
					<?php 
					$content = $this->instructions;
					$editor_id = $field;
					$settings = array( 
						'textarea_name' => $field,
						'textarea_rows' => 10,
					);
					wp_editor( $content, $editor_id, $settings ); ?>
					<?php echo $this->get_description_html( $data ); ?>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		/**
		 * Set expiry time
		 */
		public function set_payment_expiry( $order_id, $transaction_time, $expiry, $unit ) {
			switch ($unit) {
				case 'minute':
					$add = $expiry * MINUTE_IN_SECONDS;
					break;
				case 'hour':
					$add = $expiry * HOUR_IN_SECONDS;
					break;
				case 'day':
					$add = $expiry * DAY_IN_SECONDS;
					break;
				default:
					$add = $expiry;
					break;
			}
			$expiry_time = strtotime( $transaction_time ) + $add;
			update_post_meta( $order_id, '_ms_midtrans_payment_expiry', $expiry_time );
		}

		/*
		 * Helper
		 * Writing log & debug
		 */
		public function write_log($log) {
			if (true === WP_DEBUG) {
				if (is_array($log) || is_object($log)) {
					error_log(print_r($log, true));
				} else {
					error_log($log);
				}
			}
		}
	}
}
add_action( 'plugins_loaded', 'ms_midtrans_core_va_init' );