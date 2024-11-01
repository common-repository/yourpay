<?php

class WC_Yourpay_ViaBill extends WC_Yourpay_Instance {

    public function __construct() {
        global $woocommerce;

        $supports[] = "products";
        $supports[] = 'refunds';

        $this->id = 'yourpay-viabill';
        $this->title = 'ViaBill';
        $path = getFilePath('/yourpay');
        $this->icon = $path . '/assets/images/platformlogos/viabill_logo.png';
        $this->has_fields = false;
        $this->supports = $supports;

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables
        if(isset($this->settings["enabled"]))
			$this->enabled = $this->settings["enabled"];

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_yourpay_viabill', array($this, 'receipt_page'));
        if ($this->contains_plugin('dk_viabill') == true) {
            $this->init_form_fields();
            $this->init_settings();
        }
        else{
            $this->enabled = $this->settings["enabled"] = "no";
        }
    }
    public function admin_options()
    {
        if (isset($_POST['save'])&&wp_verify_nonce($_POST['save'])&&current_user_can($_POST['save'])) {
            $this->updateCheck();
        }
        echo '<h3>Viabill</h3>';
        echo '<table class="form-table">';
        if ($this->contains_plugin('dk_viabill') == false) {
            echo '<h3>Plugin not installed in Yourpay App Store</h3>';
        }
        else{
            $this->generate_settings_html();
        }
        echo '</table>';
    }
    function process_payment($order_id)
    {
        $url = $this->_GenerateToken($order_id, array("method" => "viabill"));
        return array(
            'result' => 'success',
            'redirect' => $url,
        );
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable', 'woocommerce' ),
                'type' => 'checkbox',
                'label' => __( 'Enable ViaBill payment', 'woocommerce' ),
                'default' => 'no'
            )
        );
    }
}
