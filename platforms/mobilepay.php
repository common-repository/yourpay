<?php

class WC_Yourpay_MobilePay extends WC_Yourpay_Instance
{

    public function __construct()
    {
        global $woocommerce;

        $supports[] = "products";
        $supports[] = 'refunds';

        $this->id = 'yourpay-mobilepay';
        $this->title = 'MobilePay';
        $path = getFilePath('/yourpay');
        $this->icon = $path . '/assets/images/platformlogos/mobilepay-small.png';
        $this->has_fields = false;
        $this->supports = $supports;

        // Load the settings.
        //$this->init_settings();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_yourpay_mobilepay', array($this, 'receipt_page'));

        if ($this->contains_plugin('dk_mobilepay') == true) {
            $this->init_form_fields();
            $this->init_settings();
        } else {
            $this->enabled = $this->settings["enabled"] = "no";
        }
    }

    public function admin_options()
    {
        if (isset($_POST['save'])&&wp_verify_nonce($_POST['save'])&&current_user_can($_POST['save'])) {
            $this->updateCheck();
        }
        echo '<h3>MobilePay</h3>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        if ($this->contains_plugin('dk_mobilepay') == false) {
            echo '<h3>Plugin not installed in Yourpay App Store</h3>';
        }
        echo '</table>';
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable MobilePay', 'woocommerce'),
                'default' => 'no'
            )
        );
    }

    function process_payment($order_id)
    {
        $url = $this->_GenerateToken($order_id, array("method" => "mobilepay"));
        return array(
            'result' => 'success',
            'redirect' => $url,
        );
    }
}