<?php


class yourpay {
	private static $initiated = false;
	private static $sdk;

	public function __construct() {
		add_action('admin_menu','admin_menu');

	}

	public static function init() {
		if ( ! self::$initiated ) {
			self::$sdk = new yourpay_sdk();
			self::init_hooks();
		}
	}

	private static function init_hooks() {
		self::$initiated = true;
		add_action( 'admin_menu', array( "yourpay", "admin_menu" ) );
	}

	public static function shortcodes() {
		add_shortcode('get_email', self::current_user_email());
	}

	public static function admin_menu()
	{
		add_menu_page('Yourpay','Yourpay Home','manage_options','yourpay_admin',array("yourpay","welcome"),'dashicons-cart',4);

		add_submenu_page('yourpay_admin','Payouts','Payouts','manage_options','yourpay_payouts',array("yourpay","payouts"),2);
		add_submenu_page('yourpay_admin','Callbacks','Callbacks','manage_options','yourpay_callbacks',array("yourpay","callbacks"),3);
		add_submenu_page('yourpay_admin','Settings','Settings','manage_options','yourpay_settings',array("yourpay","settings"),4);
		/*add_submenu_page('yourpay_admin_menu','Statistics','Statistics','manage_options','yourpay_statistics','yourpay_admin_sub_statistics',2);
		*/

		add_submenu_page ("#", "Payout Data","Payout Data","manage_options","yourpay_payouts_data",array("yourpay","payouts_data"));
	}

	public static function admin_notices_not_activated() {
	    $sdk = new yourpay_sdk();
	    if($sdk->getSetting("enabled") != "yes" || ($sdk->getSetting("enabled") == "yes" && $sdk->getSetting("yp_token") == "")) {
		    $path = 'admin.php?page=yourpay_admin';
		    $url = admin_url($path);
		    $class = 'notice notice-error';
		    $message = __( 'Oh no! You have not yet activated Yourpay', 'yourpay' );
		    $link = __( 'Click here to visit the setup page for Yourpay', 'yourpay' );

		    printf( "<div class='%1\$s'><p>%2\$s</p><br /><a href='{$url}'>%3\$s</a></div>", esc_attr( $class ), esc_html( $message ), esc_html($link) );

	    }

	}



	public static function register_settings() {
		register_setting(
			'settings_form', // A settings group name.
			'woocommerce_yourpay_settings', //The name of option to sanitize and save.,
            'woocommerce_yourpay_settings_validation'
		);

		add_settings_section( 'section_general',
			'',
			array("yourpay",'settings_general_output'),
			'settings_general'
		);
		add_settings_field( 'yourpay_field_enabled',
			__("Enable Yourpay", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general',
			array("checkbox","enabled")
		);
		add_settings_field( 'yourpay_field_title',
			__("Title of Credit Card", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general',
			array("text","title")
		);
		add_settings_field( 'yourpay_field_text',
			__("Yourpay Merchant Token", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general',
        array("text","yp_token")
		);

		add_settings_section( 'section_general_digital',
			'',
			array("yourpay",'settings_general_digital_output'),
			'settings_general'
		);
		add_settings_field( 'yourpay_field_autocapture',
			__("Instant Capture", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general_digital',
			array("checkbox","yp_autocapture")
		);
		add_settings_field( 'yourpay_field_testmode',
			__("Test mode", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general',
			array("checkbox","yp_force_test")
		);
		add_settings_section( 'section_general_support',
			'',
			array("yourpay",'settings_general_support_output'),
			'settings_general'
		);
		add_settings_field( 'yourpay_field_supporter',
			__("Backlink to Yourpay", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general_support',
			array("checkbox","yp_supporter")
		);
		add_settings_section( 'section_general_business',
			'',
			array("yourpay",'settings_general_business_output'),
			'settings_general'
		);
		add_settings_field( 'yourpay_field_surchage',
			__("Surchage", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'settings_general',
			'section_general_business',
			array("checkbox","yp_business_cards")
		);



		// WELCOME
		register_setting(
			'welcome_form', // A settings group name.
			'woocommerce_yourpay_settings', //The name of option to sanitize and save.,
			'welcome_settings_validation'
		);

		add_settings_section("welcome_general_settings",
            "",
            array("yourpay","welcome_general_registered_output"),
            "welcome_general_registered"
        );
		add_settings_section( 'welcome_general',
			'',
			array("yourpay",'welcome_general_output'),
			'welcome_general'
		);
		add_settings_field( 'welcome_yourpay_username',
			__("Your Yourpay username", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'welcome_general',
			'welcome_general',
			array("text","username")
		);
		add_settings_field( 'welcome_yourpay_password',
			__("Your Yourpay password", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'welcome_general',
			'welcome_general',
			array("text","password")
		);

		add_settings_section( 'welcome_general_create_account',
			'',
			array("yourpay",'welcome_general_create_account_output'),
			'panel_general_create_account'
		);
		add_settings_field( 'welcome_yourpay_email',
			__("Your Shop e-mail", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'panel_general_create_account',
			'welcome_general_create_account',
			array("text","email")
		);
		add_settings_field( 'welcome_yourpay_terms',
			__("I accept Yourpays terms and create an account", "yourpay"),
			array("yourpay_sdk",'generateFields'),
			'panel_general_create_account',
			'welcome_general_create_account',
			array("checkbox","terms")
		);





		// PAYOUTS
		add_settings_section( 'payments_general',
			'',
			array("yourpay",'payouts_general_output'),
			'payouts_general'
		);
		add_settings_section( 'payouts_general',
			'',
			array("yourpay",'payouts_list_output'),
			'payouts_list'
		);

		// PAYOUTS DATA
		add_settings_section( 'payments_data_general',
			'',
			array("yourpay",'payouts_data_general_output'),
			'payouts_data_general'
		);
		add_settings_section( 'payouts_data_general',
			'',
			array("yourpay",'payouts_data_output'),
			'payouts_data_list'
		);


		// CALLBACKS
		add_settings_section( 'callbacks_general',
			'',
			array("yourpay",'callbacks_general_output'),
			'callbacks_general'
		);
		add_settings_section( 'callbacks_general',
			'',
			array("yourpay",'callbacks_list_output'),
			'callbacks_list'
		);

	}
	function woocommerce_yourpay_settings_validation($input) {
		$newinput['checkbox'] = trim($input['checkbox']);
		return $newinput;
	}




	// PAYOUTS
	public static function payouts() {
		?>
		<div class="inner-panel">
			<h3>Payouts</h3>
			<?php
			do_settings_sections( 'payouts_general' );
			do_settings_sections( 'payouts_list' );
			?>
		</div>
		<?php
	}

	public static function payouts_general_output() {
		echo "Follow your payouts from Yourpay. What will be settled, and how will it be converted.";
	}
	public static function payouts_list_output() {
		$sdket = new yourpay_sdk();
		$template = new yourpay_template();

		$payout_list = $sdket->v43requestresponse("payout_list",[], "array")["content"];
		echo $template->build_table([
            [
                "time_created", "Payment date", "timestamp"
            ],
			[
				"time_released", "Funding release date", "timestamp"
			],
			[
				"percentage_fee_text", "Transaction fee",
			],
			/*[
				"conversionrate", "Currency Conversion",
			],*/
			[
				"amount_captured_text", "Amount captured",
			],
			[
				"amount_expected_text", "Funding after fees",
			],
			[
				"id", "Go to payout list", "button", ["yourpay_payouts_data", "id"]
			],
        ], $payout_list);

	}


	// PAYOUTS DATA
	public static function payouts_data() {
	    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
	    if($id == 0)
		    if($return = new WP_Error( 'broke', __( "Expected an Payout ID. Did not receive it.", "yourpay" ) ))
			    die($return->get_error_message());

		?>
        <div class="inner-panel">
            <h3>Payouts Data</h3>
			<?php
			do_settings_sections( 'payouts_general' );
			do_settings_sections( 'payouts_list' );
			?>
        </div>
		<?php
	}

	public static function payouts_data_general_output() {
		echo "Follow your payouts from Yourpay. What will be settled, and how will it be converted.";
	}
	public static function payouts_data_list_output() {
		$sdket = new yourpay_sdk();
		$template = new yourpay_template();

		$payout_list = $sdket->v43requestresponse("payout_list",[], "array")["content"];
		echo $template->build_table([
			[
				"time_created", "Payment date", "timestamp"
			],
			[
				"time_released", "Funding release date", "timestamp"
			],
			[
				"percentage_fee_text", "Transaction fee",
			],
			[
				"conversionrate", "Currency Conversion",
			],
			[
				"amount_captured_text", "Amount captured",
			],
			[
				"amount_expected_text", "Funding after fees",
			]
		], $payout_list);

	}




	// CALLBACKS
	public static function callbacks() {
		?>
        <div class="inner-panel">
            <h3>Callbacks</h3>
			<?php
			do_settings_sections( 'callbacks_general' );
			do_settings_sections( 'callbacks_list' );
			?>
        </div>
		<?php
	}

	public static function callbacks_general_output() {
		echo "Follow your Callbacks from Yourpay. If anything have failed, we will here tell you why.";
	}
	public static function callbacks_list_output() {
		$sdket = new yourpay_sdk();
		$template = new yourpay_template();
		$limit = 5;

		$callback_list = $sdket->v43requestresponse("callback_list",["start" => 0, "limit" => $limit],"array")["content"];

		echo $template->build_table([
            [
				"transaction_id",
				"Payment ID"
			],
			// todo: OrderID skal med på listen
			[
				"attempts",
				"Notification attempts"
			],
			[
				"status",
				"Confirmation",
                "function",
                ["yourpay", "analyze_declines", "id"]
			],
			[
				"url",
				"Notification URL"
			],
		], (array)$callback_list);
	}

	public static function analyze_declines($result_element, $args) {
	    if($result_element == 0) {
	        $callback_reason = self::$sdk->callback_analyze_reason($args);

	        $result = $callback_reason["reason_text"];
        }
	    else
		    $result = "Callback received";
	    return $result;
    }

	// SETTINGS
	public static function settings() {
		?>
		<div class="inner-panel">
            <h3>Settings</h3>
            <p>Here you find the settings for your Yourpay gateway. You can find a guide to setting up Yourpay in WooCommerce <a href="https://www.yourpay.eu/support/how-to-use-yourpay-in-woocommerce/">here</a></p>
			<form id="panel" method="post" action="options.php">
				<?php
				settings_fields( 'settings_form' );
				do_settings_sections( 'settings_general' );
				do_settings_sections( 'settings_general_digital' );
				submit_button( 'Save' );
				?>
			</form>
		</div>
		<?php
	}
	public static function settings_general_digital_output() {
		echo "<h3>Digital products</h3>";
		echo "You are only allowed to use instant capture if you sell digital products, which can be downloaded or services. It is not allowed to use instant capture when selling products, which have to be shipped. Read more about using autocapture <a href='https://www.yourpay.eu/support/when-can-you-use-autocapture/].'>here</a>";
	}
	public static function settings_general_support_output() {
	    echo "<h3>Support us for free</h3>";
		echo "You can support Yourpay by allowing backlinks from your website to Yourpay. All you have to do is to check the box.";
	}
	public static function settings_general_business_output() {
	    echo "<h3>Selling to other companies?</h3>";
		echo "Do you only sell to other businesses, then you can let them pay the transaktion fee by checking this box. It is not allowed to use this function within the EU-region, if you sell to private customers.";
	}
	public static function settings_general_output() {
		echo "Yourpay Settings";
	}
	public static function field_output() {
		?>
		Felt
		<?php
        $sdk = new yourpay_sdk();

        $sdk->generateFields("checkbox","enabled");
	}

	public static function settings_general() {
		?>
		<div class="inner-panel">
			<h3>General Settings</h3>
			<form id="panel" method="post" action="options.php">
				<?php
				settings_fields( 'settings_form' );
				do_settings_sections( 'panel_general' );
				submit_button( 'Save' );
				?>
			</form>
		</div>
		<?php
	}




	// WELCOME
	public static function welcome() {
		wp_register_script(
			'yourpay-js',
			plugin_dir_url( __FILE__ ) . 'assets/js/home.js',
			array( 'jquery' )
		);
		wp_enqueue_script( 'yourpay-js' );

	    $sdk = new yourpay_sdk();
		?>
        <div class="inner-panel">
            <h3>Welcome to Yourpay</h3>
            <form id="panel" method="post" action="options.php">
				<?php
                if($sdk->getSetting("yp_token") == "") {
                    do_settings_sections( 'welcome_general' );
                    submit_button( 'Log in on Account' );
                    /*
	                settings_fields( 'welcome_form' );
	                do_settings_sections( 'panel_general_create_account' );
	                submit_button( 'Create Account', "submit", "create_account");*/
                } else {
	                do_settings_sections( 'welcome_general_registered' );
                }
				?>
            </form>
        </div>
		<?php
	}
	public static function welcome_general_output() {
		echo "Yourpay Welcome";
	}
	public static function welcome_general_registered_output() {
		echo "Thanks for signing up at Yourpay. We have identified that you already have registered with us.";
		echo "<br />";
		echo "You can find your details under the menu Settings.";
	}
	public static function welcome_general_create_account_output() {
		echo "Yourpay Create Account";
	}

	public static function yourpay_ajax_login() {
	    $sdk = new yourpay_sdk();
		$username = isset($_POST['username']) ? (string)$_POST['username'] : "";
		$password = isset($_POST['password']) ? (string)$_POST['password'] : "";

	    $token = $sdk->login($username, $password);

		$data = array();
		$settings = get_option( 'woocommerce_yourpay_settings' );
		$data["yp_token"] = $token;
		$data["enabled"] = "yes";
		$data["yp_supporter"] = "yes";

		$settings = array_merge($settings, $data);
		if ( update_option('woocommerce_yourpay_settings', $settings ) )
		{
		    die(1);
		}
		else {
		    die(0);
		}
	}

	public static function yourpay_ajax_create_account() {
		echo " 2 !";
		die();
	}

}