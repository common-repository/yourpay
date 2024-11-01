<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Our main class
 *
 */
final class WC_YourPay_Info {

    /* Version */
    public $version = '5.0.1';

    /* IDs */
    public $yourpay_id = 'yourpay';

    /* Internal variables */
    public $wc_blocks_active        = false;

    public $unpaid_statuses         = array( 'on-hold', 'pending', 'partially-paid' );

    /* Internal variables - For Yourpay */
    public $yourpay_settings            = null;
    public $yourpay_icon            = "https://cdn.yourpay.io/wp-content/uploads/2019/05/cropped-Logo-blue.png";


    /* Single instance */
    protected static $_instance = null;

    /* Constructor */
    public function __construct() {
        $this->wc_blocks_active        =
            class_exists( '\Automattic\WooCommerce\Blocks\Package' )
            &&
            //Only above 3.0
            version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), '3.0.0', '>=' )
            &&
            //And only if the featured plugin is installed
            defined( 'WC_BLOCKS_IS_FEATURE_PLUGIN' )
            &&
            WC_BLOCKS_IS_FEATURE_PLUGIN;

        $this->yourpay_settings = get_option( 'woocommerce_yourpay_settings' );
    }

    /* Ensures only one instance of our plugin is loaded or can be loaded */
    public function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /* Add to WooCommerce */
    public function woocommerce_add_payment_gateways( $methods ) {
        //Credit card
        $methods[] = 'Yourpay';
        return $methods;
    }


    /**
     * Order needs payment - valid statuses
     *
     * @since 4.4.0
     */
    public function woocommerce_valid_order_statuses_for_payment( $statuses, $order ) {
        if ( in_array( $order->get_payment_method() , array( $this->yourpay_id ) ) ) {
            $statuses = array_unique( array_merge( $statuses, $this->unpaid_statuses ) );
        }
        return $statuses;
    }
}