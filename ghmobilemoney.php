<?php
    /*
    Plugin Name: Gh Mobile Money
    Plugin URI:
    Description: Accept Mobile Money payments on your website.
    Version: 1.0
    Author: Kwame Twum
    Author URI: https://github.com/kmtwum
    Developer: Kwame Twum
    Developer URI: https://github.com/kmtwum
    Text Domain: ghmobilemoney
    WC requires at least: 2.2
    WC tested up to: 5.1
    License: GNU General Public License v3.0
    License URI: http://www.gnu.org/licenses/gpl-3.0.html

    @package   Gh Mobile Money
    @author    Kwame Twum
    @category  Admin
    @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
    */

    defined('ABSPATH') or die;

    if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

    add_action( 'plugins_loaded', 'momo_init', 11 );
    add_filter( 'woocommerce_payment_gateways', 'add_momo_to_gateways' );
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_gh_mobile_money_plugin_links' );

    function wc_gh_mobile_money_plugin_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ghmobilemoney' ) . '">' . __( 'Configure', 'ghmobilemoney' ) . '</a>'
        );
        return array_merge( $plugin_links, $links );
    }


    function add_momo_to_gateways( $gateways ) {
        $gateways[] = 'Gh_Mobile_Money';
        return $gateways;
    }


    function momo_init() {
        require_once 'inc/gh_mobile_money.php';
        return new gh_mobile_money();
    }