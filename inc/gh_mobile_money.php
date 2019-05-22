<?php

    class gh_mobile_money extends WC_Payment_Gateway {

        public function __construct() {
            $this->id = 'ghmobilemoney';
            $this->icon = get_bloginfo( 'url' ) . '/wp-content/plugins/ghmobilemoney/inc/files/mtn.jpg';
            $this->has_fields = true;
            $this->method_title = __('Gh Mobile Money', 'ghmobilemoney');
            $this->method_description = __('Accept Mobile Money payments on your website.', 'ghmobilemoney');

            $this->init_form_fields();
            $this->init_settings();

            $this->title        = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            add_action( 'woocommerce_api_'. strtolower(get_class($this)), [$this, 'hooker_back'] );

            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you_page' ) );
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

        }

        public function hooker_back() {
            $incoming = json_decode(file_get_contents('php://input'));
            $this->tail("Stuff coming in...");
            $this->tail($incoming);

            // if ( isset( $decoded->TransactionId ) ) {
            //     if ( $response_data->Authorized == true ) {
            //
            //         // Set order status to processing
            //         $order->update_status( 'processing', sprintf( __( 'Authorized  %s %s on credit card.', 'woocommerce-my-payment-gateway' ), get_woocommerce_currency(), $order->get_total() ) );
            //     } else {
            //         // Set order status to payment failed
            //         $order->update_status( 'failed', sprintf( __( 'Card payment failed.', 'woocommerce-my-payment-gateway' ) ) );
            //     }
            // }
        }

        public function payment_fields() {
            $fields = [
                'momo-number-field' => '<p class="form-row form-row-wide">
                    <label for="' . esc_attr( $this->id ) . '-momo-number">' . esc_html__( 'Mobile Money Number', 'ghmobilemoney' ) . '&nbsp;<span class="required">*</span></label>
                    <input id="' . esc_attr( $this->id ) . '-momo-number" name="' . esc_attr( $this->id ) . '-momo-number" class="input-text" inputmode="numeric" maxlength="10" autocomplete="tel" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="02xxxxxxxx"  />
                    <div class="alert alert-secondary" role="alert">
                      Please check your wallet approvals to complete the transaction after clicking <b>Place Order</b>.
                    </div>
                </p>',
            ];
            ?>

            <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class=''>
                <?php
                    foreach ( $fields as $field ) {
                        echo $field;
                    }
                ?>
                <div class="clear"></div>
            </fieldset>
            <?php
        }

        public function process_admin_options() {
            parent::process_admin_options();
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled'    => [
                    'title'   => __( 'Enable/Disable', 'ghmobilemoney' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable Mobile Money Payments', 'ghmobilemoney' ),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => __( 'Title', 'ghmobilemoney' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'ghmobilemoney' ),
                    'default'     => __( 'MTN Mobile Money', 'ghmobilemoney' ),
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => __( 'Description', 'ghmobilemoney' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => __( 'This controls the description which the user sees during checkout.', 'ghmobilemoney' ),
                    'default'     => __( 'Pay via MTN Mobile Money / Vodafone Cash.', 'ghmobilemoney' ),
                ],
                'merchant_options' => [
                    'title' => __( 'Merchant Options', 'ghmobilemoney' ),
                    'type' => 'title',
                    'description' => __("The following options affect where your funds will be sent when clients are billed. \r\n Use only if you have a fund collection account", 'ghmobilemoney'),
                    'id'   => 'merchant_options'
                ],
                'merchant_platform' => [
                    'title'       => __( 'Merchant Platform', 'ghmobilemoney' ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'Select a collection point. Ignore to have one created for you.', 'ghmobilemoney' ),
                    'desc_tip'    => true,
                    'options'     => [
                        'auto'    => __( 'Set Up One For Me', 'ghmobilemoney' ),
                        'anm'    => __( 'AppsNMobile', 'ghmobilemoney' ),
                        'hub' => __( 'Hubtel', 'ghmobilemoney' ),
                    ],
                ],
            ];
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            $order_data = json_decode($order);
            $data = [
                    'woo' => $this->gen_id('WOO'),
                    'callback' => get_bloginfo( 'url' ) . '/wc-api/gh_mobile_money/',
                    'amount' => $order_data->total,
                    'info' => 'Purchase on ' . get_bloginfo('name'),
                    'order_id' => $order_data->id,
                    'mobile_number' => $_POST['ghmobilemoney-momo-number'],
            ];
            $this->call($data);
            $order->update_status( 'processing', __( 'Awaiting payment confirmation', 'ghmobilemoney' ) );
            $order->reduce_order_stock();
            WC()->cart->empty_cart();
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url( $order )
            );
        }

        public function thank_you_page() {
            if ( $this->instructions ) {
                echo wpautop( wptexturize( $this->instructions ) );
            }
        }

        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( $this->instructions && ! $sent_to_admin && 'offline' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
                echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
            }
        }

        private function call($data = '', $header = null) {
            $url = "https://excelliumgh.com/auto/api/woo/_.php";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            if ($header != null) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            if ($data != '') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            $c_result = curl_exec($curl);
            if ($c_result === false) {
                $c_error = curl_error($curl);
                curl_close($curl);
                return json_encode(array("status" => $c_error));
            }
            curl_close($curl);
            return $c_result;
        }

        private function tail($str) {
            @file_put_contents(__DIR__ . '/bro.txt', print_r($str, true) . "\r\n", FILE_APPEND | LOCK_EX);
        }

        function gen_id($prefix) {
            $date  = new DateTime (); $curstamp = $date->format('Y-m-d-H-i');
            $t_id = $prefix.str_replace('-', '', $curstamp).mt_rand(10000, 50000);
            return $t_id;
        }
    }