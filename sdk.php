<?php


class yourpay_sdk {

	private static $token;
	public static $settings = [];

	function __construct() {
		$this->_GetToken();
		$this->setSettings();
	}
	private static function setSettings() {
		self::$settings = get_option( 'woocommerce_yourpay_settings' );
	}

	public static function getSetting($setting_name) {
	    return isset(self::$settings[$setting_name]) ? self::$settings[$setting_name] : "";
	}

	public static function generateFields($args) {
		$type = $args[0]; $name = $args[1];
		if(empty(self::$settings))
			self::setSettings();

		if($type == "text") {
		    ?>
            <input type='textbox' name='woocommerce_yourpay_settings[<?php echo $name; ?>]' value='<?php echo self::getSetting($name); ?>'>
            <?php
		}
		if($type == "checkbox") {
			?>
            <input type='checkbox' name='woocommerce_yourpay_settings[<?php echo $name; ?>]' value='yes' <?php
            if(isset(self::$settings[$name]) && self::$settings[$name] == "yes") {
                echo "checked=\"checked\"";
            }
            ?>>
			<?php
		}
		?>
		<?php
	}

	public static function login($username, $password) {
		$data = self::v43requestresponse("login_password", [
		        "username" => $username,
                "password" => $password
        ], "object");
		$merchant_token = isset($data->content->merchant_token) ? $data->content->merchant_token : "";
		return $merchant_token;
    }

	public static function v43requestresponse($method, $data = [], $data_structure = "object")
	{
		$all_settings = self::$settings;
		$merchant_token = $all_settings["yp_token"];
		$url = "https://webservice.yourpay.io/v4.3/" . $method . "?" . "merchant_token=" . $merchant_token;
		foreach ($data as $key => $value) {
			$url .= "&$key=" . urlencode("$value");
		}
		$output = json_decode(wp_remote_get($url)["body"]);
		if($data_structure == "array")
		    $output = (array)$output;
		return $output;
	}


    public static function v43requestnoresponse($method, $data = [])
    {
        $all_settings = self::$settings;
        $merchant_token = $all_settings["yp_token"];
        $url = "https://webservice.yourpay.io/v4.3/" . $method . "?" . "merchant_token=" . $merchant_token;
        foreach ($data as $key => $value) {
            $url .= "&$key=" . $value;
        }
        wp_remote_get($url);
        return $url;
    }
    public static function v43productnoresponse($method, $data = [])
    {
        $all_settings = self::$settings;
        $merchant_token = $all_settings["yp_token"];
        $url = "https://product.yourpay.io/v4.3/" . $method . "?" . "merchant_token=" . $merchant_token;
        foreach ($data as $key => $value) {
            $url .= "&$key=" . $value;
        }
        wp_remote_get($url);
        return $url;
    }

	private function _GetToken() {
		self::$token = get_option('yp_token', "");
	}

	public static function callback_analyze_reason($args) {
        $data = reset(self::v43requestresponse("callback_data", ["id" => $args], "object")->content->logs);
        $result = [];

        $result["status_code"] = $data->status_code;

		if($result["status_code"] == 200) {
			$result["reason_text"] = "Approved";
		}
        elseif($result["status_code"] == 0) {
	        $result["reason_text"] = "Failed. No indication of what the issue was. We did not receive any HTTP response code.";
        }
        elseif($result["status_code"] == 302) {
	        $result["reason_text"] = "Failed. Page was indicated as 'moved'. We did not follow this redirect. Please correct your integration!";
	        $result["reason_text"] .= json_encode($data);
        }
        elseif($result["status_code"] == 404) {
	        $result["reason_text"] = "Failed. Page was indicated as 'not found'. We tried to deliver our Callback, but your page did not exist.";
        }

        return $result;
}
}