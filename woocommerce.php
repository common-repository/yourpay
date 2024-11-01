<?php
if (class_exists('WooCommerce')) {

	class WC_Yourpay2_0 extends WC_Payment_Gateway
	{
		private static $initiated = false;
		private static $sdk;
		private static $plugin_url;
	        public $merchantid;

        	public function __construct()
		{
			global $woocommerce;
			$supports[] = "products";
			$supports[] = 'refunds';
			$supports[] = 'default_credit_card_form';
			$supports[] = 'subscriptions';
			$supports[] = 'subscription_cancellation';
			$supports[] = 'subscription_reactivation';
			$supports[] = 'subscription_suspension';
			$supports[] = 'subscription_amount_changes';
			$supports[] = 'subscription_date_changes';
			$supports[] = 'subscription_payment_method_change';
			$supports[] = 'multiple_subscriptions';
			$supports[] = 'add_payment_method';
			$supports[] = 'subscription_payment_method_change_customer';
			$supports[] = 'subscription_payment_method_change_admin';
			$supports[] = 'pre-orders';

			$this->id = 'yourpay';
			$this->method_title = 'Yourpay';
			$path = getFilePath('/yourpay');
			$this->icon = $path . '/assets/images/cards/kortlogoer.png';
			$this->has_fields = false;
			$this->supports = $supports;
			$this->version = "3.2.8";
			self::$plugin_url = plugin_dir_url( __FILE__ );

			// Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Define user set variables
			$this->enabled = $this->get_option('enabled');
			$this->title = $this->settings["title"];			$this->description = $this->settings["description"];

			$this->yp_token = $this->settings["yp_token"];

			$this->yp_autocapture = isset($this->settings["yp_autocapture"]) ? $this->settings["yp_autocapture"] : "";
			$this->yp_capture_on_stage = isset($this->settings["yp_capture_on_stage"]) ? $this->settings["yp_capture_on_stage"] : "";
			$this->yp_capture_stage = isset($this->settings["yp_capture_stage"]) ? $this->settings["yp_capture_stage"] : "";

			if (isset($this->settings["yp_force_test"])) {
				$this->yp_force_test = $this->settings["yp_force_test"];
			} else {
				$this->yp_force_test = false;
			}

            if (isset($this->settings["support_woocommerce_blocks"])) {
                $this->support_woocommerce_blocks = $this->settings["support_woocommerce_blocks"];
            } else {
                $this->support_woocommerce_blocks = false;
            }

			if (isset($this->settings["yp_business_cards"])) {
				$this->yp_business_cards = $this->settings["yp_business_cards"];
			} else {
				$this->yp_business_cards = false;
			}

			if (isset($this->settings["yp_disable_save_card"])) {
				$this->yp_disable_save_card = $this->settings["yp_disable_save_card"];
			} else {
				$this->yp_disable_save_card = false;
			}
			if (isset($this->settings["yp_supporter"])) {
				$this->yp_supporter = $this->settings["yp_supporter"];
			} else {
				$this->yp_supporter = true;
			}

			// Actions
			add_action('yourpay-callback', array(&$this, 'successful_request'));

			add_action('add_meta_boxes', array(&$this, 'yourpay_meta_boxes'), 10, 0);

			add_action('woocommerce_api_' . strtolower(get_class()), array($this, 'check_callback'));
			add_action('wp_before_admin_bar_render', array($this, 'yourpay_action'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			add_action('woocommerce_receipt_yourpay', array($this, 'receipt_page'));

			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
			add_action('admin_notices', array($this, 'admin_notices'));
			add_action('plugins_loaded', array($this, 'init'), 0);
			add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'scheduled_subscription_payment'), 10, 2);
			add_action( 'admin_enqueue_scripts', array($this, 'load_admin_style' ));

		}

		public static function init() {
			if (!class_exists('WC_Payment_Gateway')) {
				return;
			}
			if ( ! self::$initiated ) {
				self::$sdk = new yourpay_sdk();
				self::init_hooks();
			}
		}

		private static function init_hooks() {
			self::$initiated = true;
		}


		public function contains_plugin($plugin_name)
		{
			$url = "https://webservice.yourpay.dk/v4.3/app_data?id=$plugin_name&merchant_token=" . get_option('yp_token', array());
			$dataOutput = wp_remote_get($url);

			if(is_array($dataOutput)) {
				$dataOutput = json_decode($dataOutput["body"]);
				if ($dataOutput->content->installed) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}


		function updateCheck()
		{
			wp_remote_get('https://webservice.yourpay.dk/checkUpdate/checkUpdate/' . $this->id . '/' . '&module=' . $this->id . '&module_version=' . $this->version . '&cms=woocommerce' . '&cms_version=' . get_bloginfo('version') . '&domain=' . $_SERVER['HTTP_HOST'] . '&phone=' . '&email=' . get_option('admin_email'));
		}

		function _GetToken() {

            $token = get_option('yp_token', array());

			if (is_admin())
    			return $token;

			$shop_id = WC()->session->get( 'shop_id', '' );
			if($shop_id != "")
				$token = get_post_meta($shop_id, 'sushi_api_yourpaytoken',true);

            return $token;
		}

		function find_merchantid()
		{
			$request['merchant_token'] = $this->_GetToken();
			$merchantdata = self::$sdk->v43requestresponse("customer_data", $request)->content;
			if ($this->yp_force_test == "yes") {
				return $merchantdata->merchant_id;
			} else {
				return $merchantdata->production_id;
			}
			return 0;
		}

		private function is_woocommerce_3()
		{
			return version_compare(WC()->version, '3.0', 'ge');
		}

		private function get_subscription($order)
		{
			if (!function_exists('wcs_get_subscriptions_for_renewal_order')) {
				return null;
			}
			$subscriptions = wcs_get_subscriptions_for_renewal_order($order);
			return end($subscriptions);
		}

		function load_admin_style() {
			wp_enqueue_style( 'admin_css', self::$plugin_url . 'assets/css/admin.css', false, '1.0.0' );
		}

		public function scheduled_subscription_payment($amount_to_charge, $renewal_order)
		{
			$subscription = $this->get_subscription($renewal_order);
			$renewal_order_id = $this->is_woocommerce_3() ? $renewal_order->get_id() : $renewal_order->id;

			$parent_order = $subscription->order;
			$parent_order_id = $this->is_woocommerce_3() ? $parent_order->get_id() : $parent_order->id;
			$subscription_id = get_post_meta($parent_order_id, 'Subscription ID', true);
			if ($subscription_id == "")
				$subscription_id = get_post_meta($parent_order_id, 'subscription_rg_code', true);
			$MerchantID = get_post_meta($parent_order_id, 'MerchantNumber', true);
			$latest_renewal = get_post_meta($parent_order_id, 'latest_renewal', true);

			$order = new WC_Order((int)$parent_order_id);
			$order->add_order_note(__('Subscription performed', 'yourpay'));

			update_post_meta((int)$parent_order_id, 'latest_renewal', time());

			if ($latest_renewal < time() - 180) {
				$order->add_order_note(__('Subscription performed', 'yourpay'));

				$method = "rebilling_customer";
				$request['MerchantID'] = $MerchantID;
				$request['subscriptioncode'] = $subscription_id;
				$request['amount'] = str_replace(array(",", "."), "", number_format($amount_to_charge, 2));
				$order->add_order_note(__('RG : ' . $subscription_id, 'yourpay'));

				$result = $this->v4requestresponse($method, $request);

				if ($result->status == "ACK") {
					update_post_meta((int)$renewal_order_id, 'Transaction ID', $result->tid);
					update_post_meta((int)$renewal_order_id, 'Card no', 'XXXXXX');
					update_post_meta((int)$renewal_order_id, 'timeid', $result->timeid);
					update_post_meta((int)$renewal_order_id, 'MerchantNumber', $MerchantID);
					$order = new WC_Order((int)$renewal_order_id);
					$order->add_order_note(__('Subscription performed', 'yourpay'));
					$order->payment_complete();
				} else {
					$order = new WC_Order((int)$renewal_order_id);
					$order->add_order_note(__('Subscription tried - funding not available', 'yourpay'));
					$order->add_order_note(__(json_encode($result), 'yourpay'));
					$order->add_order_note(__('Response : ' . json_encode($result), 'yourpay'));

				}

			}
		}

		public static function filter_load_instances($methods)
		{
			require_once 'platforms/instance.php';
			require_once 'platforms/viabill.php';
			require_once 'platforms/mobilepay.php';
			$methods[] = 'WC_Yourpay_ViaBill';
			$methods[] = 'WC_Yourpay_MobilePay';

			return $methods;
		}

		public function process_refund($order_id, $amount = null, $reason = '')
		{
			$transactionId = get_post_meta($order_id, 'Transaction ID', true);
			$data = $this->_RefundPayment($transactionId, $amount);
			return $data;
		}

		public function plugin_action_links($links)
		{
			$addons = (class_exists('WC_Subscriptions_Order') || class_exists('WC_Pre_Orders_Order')) ? '_addons' : '';
			$plugin_links = array(
				'<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_yourpay' . $addons) . '">'
				. __('Settings', 'yourpay') . '</a>',
				'<a href="http://www.yourpay.io">' . __('Support', 'yourpay') . '</a>',
				'<a href="http://www.yourpay.dk/">' . __('Docs', 'yourpay') . '</a>',
			);
			return array_merge($plugin_links, $links);
		}

		function _GetMerchantData()
		{
			$method = "customer_data";
			$result = self::$sdk->v43requestresponse($method);

			return $result;
		}

		function _GenerateToken($order_id, $array = array()) {
			$data = array();

			$order = new WC_Order($order_id);

			if (isset($this->yp_autocapture) && $this->yp_autocapture == "yes") {
				$data["autocapture"] = 1;
			}
			foreach($array as $key=>$value)
				$data[$key] = $value;

			$data["callbackurl"] = add_query_arg('wooorderid', $order_id, add_query_arg('wc-api', 'wc_yourpay2_0', $this->get_return_url($order)));
			$data["merchant_token"] = $this->_GetToken();
			$data["MerchantNumber"] = $this->find_merchantid();
			$data['amount'] = ($order->get_total() * 100);
			$data['currency'] = $this->get_iso_code($order->get_currency());
			$data['time'] = time();
			$data['cartid'] = $order->get_order_number();
			$data['customer_name'] = $order->get_billing_last_name() . "," . $order->get_billing_first_name();
			$data['customer_email'] = $order->get_billing_email();
			$data['version'] = 109;
			$data['accepturl'] = $this->get_return_url($order);
			$data['ShopPlatform'] = "woocommerce";

			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$customerdata = array("billing_email", "billing_first_name", "billing_last_name", "billing_company", "billing_address_1", "billing_address_2", "billing_city", "billing_state", "billing_postcode", "billing_country", "shipping_first_name", "shipping_last_name", "shipping_company", "shipping_address_1", "shipping_address_2", "shipping_city", "shipping_state", "shipping_postcode", "shipping_country");


			foreach ($customerdata as $value) {
				$keydataArray = $this->getPersonOrderArray($order_id, $value);
				if(isset($keydataArray['value']))
					$data[str_replace("get_","",$keydataArray['key'])] = $keydataArray['value'];
			}
			$pos = get_post_meta($order->get_id(), '_pos', true);

			if (isset($pos) && (int)$pos == 1) {
				$data['pos'] = 1;
			}

			$order_item = $order->get_items();
			$data['total_products'] .= count($order_item);
			$i = 1;
			foreach ($order_item as $product) {
				$data["product_" . $i . "_id"] = $product["product_id"];
				$data["product_" . $i . "_name"] = $product["name"];
				$data["product_" . $i . "_qty"] = $product["qty"];
				$data["product_" . $i . "_total"] = $product["line_total"];
				$data["product_" . $i . "_tax"] = $product["line_tax"];
				$i++;
			}

			if ($this->woocommerce_subscription_plugin_is_active() && wcs_order_contains_subscription($order)) {
				$data['ccrg'] = 1;
			}

			foreach ($order as $key => $value) {
				if (!is_object($value)) {
					$data[$key] = $value;
				} elseif (is_object($value)) {
					foreach ($value as $subkey => $subvalue) {
						if (!is_object($subvalue)) {
							$data[$subkey] = $subvalue;
						}

					}
				}

			}
			$token = self::$sdk->v43requestresponse("generate_token", $data);

			return $token->content->full_url;
		}


		function _RefundPayment($TransID, $amount)
		{
			$method = "payment_action";
			$data['amount'] = ($amount*100*-1);
			$data['id'] = $TransID;
			$result = self::$sdk->v43requestresponse($method, $data);
			if($result->success)
				return TRUE;
			else
				return FALSE;
		}

		function _GetPaymentData($TransID)
		{
			$method = "payment_data";
			$request['merchant_token'] = $this->_GetToken();
			$request['id'] = $TransID;

			$result = self::$sdk->v43requestresponse($method, $request);
			return $result->content;
		}

		function _CapturePayment($TransID, $Amount)
		{
			$method = "payment_action";
			$data['amount'] = $Amount;
			$data['id'] = $TransID;

			$result = self::$sdk->v43requestresponse($method, $data);

			return $result;
		}

		function _DeletePayment($TransID)
		{
			$method = "payment_release";
			$data['merchant_token'] = $this->_GetToken();
			$data['id'] = $TransID;
			$result = self::$sdk->v43requestresponse($method, $data);

			print_r($result);
			die();

			return $result;
		}

		public function v4requestresponse($method, $data)
		{
			$fields_string = array("merchant_token" => $data['token']);
			$data["merchant_token"] = $data['token'];
			$url = "https://webservice.yourpay.dk/v4/" . $method . "?" . "merchant_token=" . $fields_string['merchant_token'];
			foreach ($data as $key => $value) {
				$url .= "&$key=" . urlencode("$value");
			}
			$output = json_decode(wp_remote_get($url)["body"]);
			return $output;
		}

		public function v4pluginsresponse($data)
		{

			$url = "https://webservice.yourpay.dk/plugins/" . $data['function'];
			$fields_string = array();
			foreach ($data as $key => $value) {
				if (!is_array($value) && strlen($value) > 0) {
					$fields_string[$key] = urlencode($value);
				}

			}
			$server_output = wp_remote_post($url, array(CURLOPT_URL => $url, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($fields_string), CURLOPT_RETURNTRANSFER => true));
			return json_encode($server_output);
		}

		function woocommerce_api_callback()
		{
			echo "Yes!";
		}

		function init_form_fields()
		{

			$statuses = $this->getOrderStatus();
			foreach ($statuses as $key => $value) {
				$status_array[$key] = $value;
			}

			$arrayfields = array(
				'enabled' => array(
					'title' => __('Enable/Disable', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('Enable Yourpay', 'woocommerce'),
					'default' => 'no',
				),
				'title' => array(
					'title' => __('Title', 'Yourpay', 'yourpay'),
					'type' => 'text',
					'default' => __('Yourpay', 'yourpay'),
				),								'description' => array(                    'title' => __('Your pay', 'yourpay'),                    'type' => 'textarea',                    'description' => __( 'Your Pay.', 'yourpay' ),					'default'     => __( 'Your Pay', 'yourpay' ),					'desc_tip'    => true,                ),
				'yp_token' => array(
					'title' => __('Yourpay Token', 'yourpay'),
					'type' => 'text',
					'default' => '',
				),
				'yp_autocapture' => array(
					'title' => __('Instant Capture', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('Instant Capture', 'woocommerce'),
					'default' => 'no',
					'description' => 'Capture payment instantly',
				),
				'yp_capture_on_stage' => array(
					'title' => __('Capture at Specific Stage', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('Capture transaction automatially if it reach stage defined below', 'woocommerce'),
					'default' => 'yes',
				),
				'yp_capture_stage' => array(
					'title' => __('Capture Stage', 'woocommerce'),
					'type' => 'select',
					'label' => __('Choose stage where transaction should autocapture', 'woocommerce'),
					"options" => $status_array,
				),
				'yp_force_test' => array(
					'title' => __('Force test-mode', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('Force test-mode', 'woocommerce'),
					'default' => '',
				),
				'yp_supporter' => array(
					'title' => __('Enable backlink', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('Enable the backlink to Yourpay, and hide that you are using our services.', 'woocommerce'),
					'default' => '',
				),
				'yp_business_cards' => array(
					'title' => __('Surcharge Business', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('If customer enters a VAT-number, it will surcharge the Yourpay Payment Fee', 'woocommerce'),
					'default' => '',
				),
                'support_woocommerce_blocks' => array(
                    'title' => __( 'WooCommerce Blocks Checkout', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable WooCommerce Blocks Checkout (experimental) support.', 'woocommerce' ),
                    'description' => __( 'Shows Yourpay as a supported payment gateway on the new WooCommerce Blocks Checkout.', 'woocommerce' ),
                    'default' => 'no'
                )
			);

			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$this->form_fields = $arrayfields;
		}

		public function generateurl($key, $value)
		{

			if (!is_object($value) && !is_array($value)) {
				return $key . "=" . $value . "&";
			}

		}

		public function admin_notices()
		{
			if ($this->enabled == 'no') {
				return;
			}
		}

		public function admin_options()
		{
			if (isset($_POST['save'])&&wp_verify_nonce($_POST['save'])&&current_user_can($_POST['save'])) {
				$this->updateCheck();
			}
			$plugin_data = get_plugin_data(__FILE__, false, false);
			$version = $plugin_data["Version"];
			echo '<h3>' . 'Yourpay' . ' v' . $version . '</h3>';
			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';
			update_option("yp_token", $this->yp_token, true);
			update_option("yp_supporter", $this->yp_supporter, false);
		}

		function payment_fields()
		{
			//$this->updateCheck();
		}

		public function getPersonOrder($orderdata, $details)
		{
			return "$details=" . $orderdata->$details . "&";
		}

		public function getPersonOrderArray($id, $details)
		{
			$details = "get_" . $details;
			$order = new WC_Order($id);
			return array("key" => $details, "value" => $order->$details());
		}


		private function woocommerce_subscription_plugin_is_active()
		{
			return class_exists('WC_Subscriptions') && WC_Subscriptions::$name = 'subscription';
		}

		function process_payment($order_id)
		{
			$url = $this->_GenerateToken($order_id);
			return array(
				'result' => 'success',
				'redirect' => $url,
			);
		}

		function check_callback()
		{
			$_GET = stripslashes_deep($_GET);
			do_action("yourpay-callback", $_GET);
		}

		function successful_request($posted)
		{
			global $product;
			$order = new WC_Order((int)$posted["wooorderid"]);
			$check_payment_data=self::$sdk->v43requestresponse("payment_data",array("merchant_token"=>$this->_GetToken(),"id"=>$posted["tid"]));

			if (
				$order->get_order_number()==(int)$check_payment_data->content->order_id &&
				str_replace(array(".",","),"",wc_format_decimal( $order->get_total(), 2 ))==$check_payment_data->content->amount
			) {
				$var = "";
				$wsdl = $this->yp_encryptioncode;
				$shaprint = sha1($posted['tid'] . $wsdl);
				$order->add_order_note(__('Callback performed', 'yourpay'));
				update_post_meta((int)$posted["wooorderid"], 'Transaction ID', $posted["tid"]);
				update_post_meta((int)$posted["wooorderid"], 'Card no', $posted["tcardno"]);
				update_post_meta((string)$posted["wooorderid"], 'timeid', $posted["time"]);
				update_post_meta((string)$posted["wooorderid"], 'MerchantNumber', $posted["MerchantNumber"]);
				if ((string)$posted["ccrg"]) {
					update_post_meta((string)$posted["wooorderid"], 'subscription_rg_code', $posted["ccrg"]);
				}
				echo "OK";


				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				if ($order->status !== 'completed' && isset($posted['tid'])) {
					if ($this->woocommerce_subscription_plugin_is_active() && isset($posted['ccrg'])) {
						WC_Subscriptions_Manager::activate_subscriptions_for_order($order);
						update_post_meta((int)$posted['wooorderid'], 'Subscription ID', $posted['ccrg']);
					}

					if ($order->status == "order-proposal") {
						$order->update_status('processing');
					}

					$order->payment_complete();

					if($this->yp_autocapture == "yes") {
                        $order->update_status('completed');
                    }

					status_header(200);
				}
				die();
			} else{
				$order->update_status('Failed');
			}
			status_header(500);
			echo "NOK";
			die();
			exit;
		}

		public function payment_scripts()
		{
			if (!is_checkout()) {
				return;
			}

		}

		public function yourpay_meta_boxes()
		{
			add_meta_box(
				'yourpay-payment-info',
				__('Yourpay', 'yourpay'),
				array(&$this, 'yourpay_meta_box_payment'),
				'shop_order',
				'side',
				'high'
			);
			add_meta_box(
				'yourpay-payment-transaction',
				__('Yourpay', 'yourpay'),
				array(&$this, 'yourpay_transaction_meta_box'),
				'shop_order',
				'side',
				'high'
			);
			add_meta_box(
				'yourpay-payment-capture',
				__('Yourpay', 'yourpay'),
				array(&$this, 'yourpay_capture_meta_box'),
				'shop_order',
				'side',
				'high'
			);
		}

		public function yourpay_action()
		{
			global $woocommerce;

			if (isset($_GET["yourpay_action"])) {
				$order = new WC_Order($_GET['post']);
				$transactionId = get_post_meta($order->get_id(), 'Transaction ID', true);
				try {
					switch ($_GET["yourpay_action"]) {
						case 'capture':
							$capture = $this->_CapturePayment($transactionId, $_GET['amount'] * 100, $order);
							break;
						case 'delete':
							$delete = $this->_DeletePayment($transactionId);
							break;
					}
				} catch (Exception $e) {
					echo $this->message("error", $e->getMessage());
				}
			}
		}

		private function getOrderStatus()
		{
			if (!function_exists('is_plugin_active')) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$order_statuses = wc_get_order_statuses();
			if (!is_plugin_active("woocommerce-order-status-manager")) {
				foreach ($order_statuses as $key => $value) {
					if (substr($key, 0, 3) == "wc-") {
						$final_status[substr($key, 3)] = strtolower($value);
					}
				} // If you have installed Order Status Manager, it changes the standard Slug from wc-complete to complete
			} else {
				foreach ($order_statuses as $key => $value) {
					$final_status[$key] = strtolower($value);
				} // To ease the array_search

			}
			return $final_status;

		}

		public function yourpay_meta_box_payment()
		{
			global $post;
			$order = new WC_Order($post->ID);
			$transactionId = get_post_meta($order->get_id(), 'Transaction ID', true);
			$timeid = get_post_meta($order->get_id(), 'timeid', true);
			$MerchantNumber = get_post_meta($order->get_id(), 'MerchantNumber', true);
			if (strlen($transactionId) > 0) {
				try {
					$transaction = $this->_GetPaymentData($transactionId, $timeid, $MerchantNumber);
					if (isset($transaction->id)) {
						echo '<p>';
						echo '<img style="width:100%;" src="'.YOURPAY__PLUGIN_URL.'/assets/img/logo-blue.png">';
						echo '<p>';
						echo '<strong>' . _e('Transaction ID', 'yourpay') . ':</strong> ' . $transaction->id;
						echo '</p>';
					}
				} catch (Exception $e) {
					echo $this->message("error", $e->getMessage());
				}
			} else {
				echo "No transaction was found.";
			}
		}
		public function yourpay_transaction_meta_box()
		{
			global $post;

			$order = new WC_Order($post->ID);
			$transactionId = get_post_meta($order->get_id(), 'Transaction ID', true);
			$timeid = get_post_meta($order->get_id(), 'timeid', true);
			$MerchantNumber = get_post_meta($order->get_id(), 'MerchantNumber', true);
			if (strlen($transactionId) > 0) {
				try {
					$transaction = $this->_GetPaymentData($transactionId, $timeid, $MerchantNumber);

					$order_statuses = $this->getOrderStatus();
					$status_key = array_search($order->get_status(), $order_statuses);

					if (isset($transaction->id)) {
						$transaction->amount - $transaction->amount_captured;
						if (
							isset($yp) &&
							$yp->yp_capture_on_stage == "yes" &&
							strtolower($yp->yp_capture_stage) == strtolower($order->status) &&
							$transaction->time_captured == 0 &&
							strtolower($yp->yp_capture_stage) != "pending payment"
						) {

							$yp->_CapturePayment($transactionId, ($transaction->amount - $transaction->amount_captured), $order);

							$order->add_order_note(__('Payment captured through Bulk Capture ', 'yourpay'));

							$transaction = $yp->_GetPaymentData($transactionId, $timeid, $MerchantNumber);
						}
						echo "<table>";
						echo "<thead>";
						echo "<tr>";
						echo "<th>Type</th>";
						echo "<th>Amount</th>";
						echo "<th>Payout ID</th>";
						echo "<tr>";
						echo "</thead>";
						foreach($transaction->payout_data as $key => $value) {
							$url = admin_url( 'admin.php?page=yourpay_payouts_data&id=' . $value->id, 'admin' );
							echo "<tr>";
							echo "<td>{$value->action_type}</td>";
							echo "<td>{$value->amount}</td>";
							echo "<td><a href='{$url}'>{$value->id}</td></td>";
							echo "<tr>";
						}
						echo "</table>";
					}
				} catch (Exception $e) {
					echo $this->message("error", $e->getMessage());
				}
			} else {
				echo "No transaction was found.";
			}
		}
		public function yourpay_capture_meta_box()
		{
			global $post;

			$order = new WC_Order($post->ID);
			$transactionId = get_post_meta($order->get_id(), 'Transaction ID', true);
			$timeid = get_post_meta($order->get_id(), 'timeid', true);
			$MerchantNumber = get_post_meta($order->get_id(), 'MerchantNumber', true);
			if (strlen($transactionId) > 0) {
				try {
					$transaction = $this->_GetPaymentData($transactionId, $timeid, $MerchantNumber);

					$order_statuses = $this->getOrderStatus();
					$status_key = array_search($order->get_status(), $order_statuses);

					if (isset($transaction->id)) {
						$transaction->amount - $transaction->amount_captured;
						if (
							isset($yp) &&
							$yp->yp_capture_on_stage == "yes" &&
							strtolower($yp->yp_capture_stage) == strtolower($order->status) &&
							$transaction->time_captured == 0 &&
							strtolower($yp->yp_capture_stage) != "pending payment"
						) {

							$yp->_CapturePayment($transactionId, ($transaction->amount - $transaction->amount_captured), $order);

							$order->add_order_note(__('Payment captured through Bulk Capture ', 'yourpay'));

							$transaction = $yp->_GetPaymentData($transactionId, $timeid, $MerchantNumber);
						}
						if ($transaction->amount > $transaction->amount_captured && $transaction->time_refunded == 0) {
							echo '<p>';
							echo '<span><input type="text" value="' . number_format(($transaction->amount - $transaction->amount_captured) / 100, 2, ".", "") . '" id="yourpay_amount" name="yourpay_amount" /></span>';
							echo '</p>';
							echo '<p>';
							echo '<a class="button" onclick="javascript:location.href=\'' . admin_url('post.php?post=' . $post->ID . '&action=edit&yourpay_action=capture') . '&amount=\' + document.getElementById(\'yourpay_amount\').value">';
							echo _e('Capture Payment', 'yourpay');
							echo '</a>';
						}
					}
				} catch (Exception $e) {
					echo $this->message("error", $e->getMessage());
				}
			} else {
				echo "No transaction was found.";
			}

		}



		private function message($type, $message)
		{
			return '<div id="message" class="' . $type . '">
                                    <p>' . $message . '</p>
                            </div>';
		}

		public function get_iso_code($currency = NULL, $numeric = true)
		{
			global $woocommerce;

			if ($currency == NULL) {
				$currency = get_option('woocommerce_currency');
				if (isset($woocommerce->session->client_currency)) {
					$currency = $woocommerce->session->client_currency;
				}
			}
			if ($numeric) {
				switch (strtoupper($currency)) {
					case 'DKK':
						$return_value = '208';
						break;
					case 'EUR':
						$return_value = '978';
						break;
					case 'GBP':
						$return_value = '826';
						break;
					case 'USD':
						$return_value = '840';
						break;
					case 'NOK':
						$return_value = '578';
						break;
					case 'SEK':
						$return_value = '752';
						break;
				}
			} else {
				switch (strtoupper($currency)) {
					case '208':
						$return_value = 'DKK';
						break;
					case '978':
						$return_value = 'EUR';
						break;
					case '826':
						$return_value = 'GBP';
						break;
					case '840':
						$return_value = 'USD';
						break;
					case '578':
						$return_value = 'NOK';
						break;
					case '752':
						$return_value = 'SEK';
						break;
				}
			}
			return $return_value;
		}


		static function footer_method()
		{
			if (get_option('yp_supporter', "yes") != "no")
				wp_enqueue_script('yourpay-backlinking', 'https://webservice.yourpay.io/js/wordpress-backlink.js', array(), '1.0');
		}

	}

    function woocommerce_add_payment_gateways_woocommerce_blocks( \Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
        require_once( 'woocommerce-blocks/yourpay/yourpay.php' );
        $payment_method_instance = new \Automattic\WooCommerce\Blocks\Payments\Integrations\yourpay;
        $payment_method_registry->register( $payment_method_instance );
    }
    require_once( dirname( __FILE__ ) . '/wc-yourpay.php' );

    function WC_yourpay_info() {
        $class = new WC_YourPay_Info();
        return $class->instance();
    }

	function add_yourpay_gateway($methods)
	{
		$methods[] = 'WC_Yourpay2_0';

		return apply_filters('woocommerce_yourpay_load_instances', $methods);
	}

	function init_yourpay_gateway()
	{
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain('yourpay', false, $plugin_dir . '/languages/');
	}

	function yourpay_woocommerce_order_status_change($order_id)
	{
		$order = new WC_Order((int)$order_id);

		$transactionId = get_post_meta($order->get_id(), 'Transaction ID', true);
		$timeid = get_post_meta($order->get_id(), 'timeid', true);
		$MerchantNumber = get_post_meta($order->get_id(), 'MerchantNumber', true);

		$yp = WC_Yourpay2_0();

		$transaction = $yp->_GetPaymentData($transactionId, $timeid, $MerchantNumber);

		$amount = $transaction->amount - $transaction->amount_captured;
		if (
			$yp->yp_capture_on_stage == "yes" &&
			strtolower($yp->yp_capture_stage) == strtolower($order->status) &&
			$transaction->time_captured == 0 &&
			strtolower($yp->yp_capture_stage) != "pending payment"
		) {

			$data = $yp->_CapturePayment($transactionId, ($transaction->amount - $transaction->amount_captured), $order);

			$order->add_order_note(__('Payment captured through Bulk Capture ', 'yourpay'));

			$transaction = $yp->_GetPaymentData($transactionId, $timeid, $MerchantNumber);
		}
	}

	add_action('woocommerce_order_status_completed', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_pending', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_failed', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_on-hold', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_processing', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_refunded', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_cancelled', 'yourpay_woocommerce_order_status_change');
	add_action('woocommerce_order_status_cancelled', 'yourpay_woocommerce_order_status_change');
    add_filter( 'woocommerce_blocks_payment_method_type_registration', 'woocommerce_add_payment_gateways_woocommerce_blocks');

	add_filter('woocommerce_payment_gateways', 'add_yourpay_gateway');
	add_filter('woocommerce_yourpay_load_instances', 'WC_Yourpay2_0::filter_load_instances');
	add_action('wp_footer', 'WC_Yourpay2_0::footer_method');
	add_action('plugins_loaded', 'init_yourpay_gateway');

	function WC_Yourpay2_0()
	{
		return new WC_Yourpay2_0();
	}

	if (is_admin()) {
		add_action('load-post.php', 'WC_Yourpay2_0');
	}

	add_filter('woocommerce_get_price_html', 'custom_price_message');
	add_action('wp_enqueue_scripts', 'custom_price_message');

	function custom_price_message($price)
	{
		global $woocommerce, $merchantid;
		$available_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		if (isset($available_gateways['yourpay-viabill']) && $available_gateways['yourpay-viabill']) {
			$yp = WC_Yourpay2_0();
            if(!isset($merchantid)||$merchantid==""){
                $merchantid = $yp->find_merchantid();
            }
            wp_enqueue_script('script', 'https://webservice.yourpay.dk/external/viabill.php?merchant_id=' . $merchantid, array(), "1", true);

		}
		return $price;
	}

	/**
	 * Register a custom menu page.
	 */
	function wpdocs_register_yourpay_setup()
	{
		add_submenu_page(
			null,
			__('Yourpay Setup', 'textdomain'),
			'custom menu',
			'manage_options',
			'yourpay_setup',
			'yourpay_setup_page'
		);
	}

	function yourpay_setup_page()
	{
		$completed = 0;

	}

	add_action('woocommerce_cart_calculate_fees', 'yourpay_add_checkout_fee_for_gateway');

	function yourpay_add_checkout_fee_for_gateway()
	{
		global $woocommerce;
		$customer = WC()->customer;
		$amount = WC()->cart->get_totals()["subtotal"] + WC()->cart->get_totals()["shipping_total"] - WC()->cart->get_totals()["discount_total"];
		if ($customer->get_is_vat_exempt()) {

			$yp = WC_Yourpay2_0();

			if ($yp->yp_business_cards == "yes") {
				$data = array();
				$data["function"] = "customer_data";
				$data = $yp->v43requestresponse($data);
				$fee = $data->content->card_fee;
				WC()->cart->add_fee("Kortgebyr", ($amount / 100 * ($fee / 100)), true, 'Kortgebyr');
			}
		}
	}

	add_action('woocommerce_review_order_before_payment', 'yourpay_refresh_checkout_on_payment_methods_change');

	function yourpay_refresh_checkout_on_payment_methods_change()
	{
		?>
        <script type="text/javascript">
            (function ($) {
                $('form.checkout').on('change', 'input[name^="vat_number"]', function () {
                    $('body').trigger('update_checkout');
                });
            })(jQuery);
        </script>
		<?php
	}


	function getFilePath($filePath)
	{
		$path = plugin_dir_url( __FILE__ );
		return $path;
	}
	function getFilePathNoBase($filePath)
	{
		$path = plugin_dir_url( __FILE__ );
		return $path;
	}

}

