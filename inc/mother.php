<?php

    class mother  {

        function reg() {
            register_activation_hook(__FILE__, 'activate');
            register_deactivation_hook(__FILE__, 'deactivate');
            // add_action('admin_menu', [$this, 'admin_stuff']);

            add_action('plugins_loaded', [$this, 'init_me'], 11);
        }

        function init_me() {
            $this->id = 'gh_mobile_money';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'Gh Mobile Moneyz';
            $this->method_description = 'Accept Mobile Money payments on your website.';
            $this->file = 'ghmobilemoney.php';
            $this->init_form_fields();
            // $this->init_settings();
            // $this->title = $this->get_option('title');

            // add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
            // add_filter('plugin_action_links_' . plugin_basename( $this->file ), array( $this, 'manage' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled'    => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce-gateway-ghmobilemoney' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable Mobile Money Payments', 'woocommerce-gateway-ghmobilemoney' ),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'       => __( 'Title', 'woocommerce-gateway-ghmobilemoney' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-ghmobilemoney' ),
                    'default'     => __( 'GH Mobile Money', 'woocommerce-gateway-ghmobilemoney' ),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __( 'Description', 'woocommerce-gateway-ghmobilemoney' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-ghmobilemoney' ),
                    'default'     => __( 'Pay via MTN Mobile Money / Vodafone Cash.', 'woocommerce-gateway-ghmobilemoney' ),
                ),
                'instructions' => array(
                    'title'       => __( 'Instructions', 'wc-gateway-offline' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-offline' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
            );
        }

        function admin_Stuff() {
            add_menu_page('Mobile Money Plugin', 'Mobile Money', 'manage_options', 'ghmobilemoney', [$this, 'land'], 'dashicons-cart', 90);
        }

        function activate() {
            flush_rewrite_rules();
        }

        function deactivate() {
            flush_rewrite_rules();
        }

    }