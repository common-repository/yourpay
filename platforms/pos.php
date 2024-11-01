<?php

class WC_Yourpay_POS extends WC_Yourpay_Instance {
    
    public function __construct() {
        global $woocommerce;

        $supports[] = "products";
			
	$this->id = 'yourpay-pos';
	$this->title = 'Point of Sales';
        $path = getFilePath('/yourpay');
	$this->icon = $path . '/kortlogoer.png';
	$this->has_fields = false;
        $this->supports = $supports;
         $this->description = "Payment Integration of Point of Sales.";
        $this->has_fields = false;

	// Load the form fields.
	$this->init_form_fields();
			

	// Load the settings.
	$this->init_settings();


        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_yourpay_mobilepay', array($this, 'receipt_page'));
			
    }
		public function admin_options()
		{
            if (isset($_POST['save'])&&wp_verify_nonce($_POST['save'])&&current_user_can($_POST['save'])) {
                $this->updateCheck();
            }
			echo '<h3>Point of Sales</h3>';
			echo '<table class="form-table">';
				$this->generate_settings_html();
            echo '</table>';
		}
    
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable', 'woocommerce' ),
                'type' => 'checkbox', 
                'label' => __( 'Enable Point Of Sales', 'woocommerce' ), 
                'default' => 'no'
            )
        );
    }
    function process_payment($order)
    {
        $orderdata = new WC_Order((int)$order);
        $url = "https://payments.yourpay.se/betalingsvindue.php?method=pos";
        $merchantdata = $this->_GetMerchantData();
        $callbackurl = urlencode(add_query_arg('wooorderid', $order, add_query_arg ('wc-api', 'wc_yourpay2_0', $this->get_return_url( $orderdata ))));
        $orderTotal = ($orderdata->order_total * 100);
        $currency = $this->get_iso_code(get_option('woocommerce_currency'));
        $cartid = $orderdata->this->id;
        $customerName = $orderdata->billing_last_name . "," . $orderdata->billing_first_name;
        $version = 207;
        $merchantid = $merchantdata->merchantid;
        $time = time();
        
        $checksum = sha1($cartid.$version.$merchantid."0".$orderTotal.$currency.$time);
        
        $urlparts["accepturl"]   = $this->get_return_url($orderdata);
        $urlparts["callbackurl"] = $callbackurl;
        $urlparts["version"]     = $version;
        $urlparts["ShopPlatform"] = "woocommerce";
        $urlparts["split"] = 0;
        $urlparts["amount"] = $orderTotal;
        $urlparts["CurrencyCode"] = $currency;
        $urlparts["time"] = $time;
        $urlparts["cartid"] = $cartid;
        $urlparts["customername"] = $customerName;
        $urlparts["designurl"] = time();
        $urlparts["checksum"] = $checksum;
        $urlparts["merchantid"] = $merchantid;
        $urlparts["customer_email"] = $orderdata->billing_email;
        $urlparts["customer_phone"] = $orderdata->billing_phone;
        $url .= "&MerchantNumber=".$merchantdata->prod_merchantid."&";
        foreach($urlparts as $key=>$value)
            $url .= "$key=$value&";
        
        $customerdata = array("billing_first_name", "billing_last_name", "billing_company", "billing_address_1", "billing_address_2", "billing_city", 
            "billing_state", "billing_postcode", "billing_country","shipping_first_name", "shipping_last_name", "shipping_company", "shipping_address_1", 
            "shipping_address_2", "shipping_city", "shipping_state", "shipping_postcode", "shipping_country");
        foreach($customerdata as $value) {$url .= $this->getPersonOrder($orderdata,$value);}
        $order_item = $orderdata->get_items(); $url .= "total_products=".count($order_item)."&"; $i = 1;
        foreach($order_item as $product ) {
            $product_data = wc_get_product($product["product_id"]);
            $url .= "product_".$i."_id=" . $product["product_id"]."&";
            $url .= "product_".$i."_name=" . $product["name"]."&";
            $url .= "product_".$i."_qty=" . $product["qty"]."&";
            $url .= "product_".$i."_total=" . $product_data->get_price_excluding_tax()."&";
            $url .= "product_".$i."_tax=" . ($product_data->get_price()-$product_data->get_price_excluding_tax())."&"; $i++;
        }
                    
        $orderdata = new WC_Order((int)$order);
        foreach($orderdata as $key=>$value)
            $url .= $this->generateurl($key,$value);
        foreach($orderdata->post as $key=>$value)
            $url .= $this->generateurl($key,$value);
        
	return array(
            'result' 	=> 'success',
            'redirect'	=> $url
        );
    }
    function receipt_page( $order )
    {
        echo "Yes!";
    }
}