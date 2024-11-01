<?php
/**
 * Yourpay gateway implementation.
 *
 */

namespace Automattic\WooCommerce\Blocks\Payments\Integrations;

use Exception;
use WC_Stripe_Payment_Request; //???
use WC_Stripe_Helper; //???
use Automattic\WooCommerce\Blocks\Assets\Api;

/**
 * Yourpay woocommerce-blocks integration
 *
 * @since 2.6.0
 */

final class yourpay extends AbstractPaymentMethodType {

    /**
     * Settings from the WP options table
     *
     * @var array
     */
    protected $settings;


    /**
     * Constructor
     *
     * @param Api $asset_api An instance of Api.
     */
    public function __construct() {
        $this->initialize();
    }

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = WC_yourpay_info()->yourpay_settings;
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        wp_register_script( 'wc-payment-method-yourpay', plugins_url( 'wc-payment-method-yourpay.js', __FILE__ ), array(), WC_yourpay_info()->version, true );
        return [ 'wc-payment-method-yourpay' ];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        return [
            'title'                             => isset( $this->settings['title'] ) ? $this->settings['title'] : 'wup',
            'description'                       => isset( $this->settings['description'] ) ? $this->settings['description'] : 'uwpwpuup',
        ];
    }


}
