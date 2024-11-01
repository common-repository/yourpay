<?php


class yourpay_template {

	public function __construct() {

	}

	public function header() {
		echo "<div class=\"container\">
    ";
	}

	public function footer() {


	}


	public function fonts() {
		wp_enqueue_style( 'yourpay', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
		wp_enqueue_style( 'yourpay', plugin_dir_url( __FILE__ ) . 'assets/font/sui_generis_rg.ttf' );

	}

	public function build_table($columns = [], $data = []) {
		$html = "<table>";

		$html .= "<thead>";
		$html .= "<tr>";
		$dataset = [];
		foreach($columns as $value) {
			if(!is_array($value)) {
				throw new Exception( "Build table child did not support the expected array in Columns.");
			}
			$html .= "<th id='".$value[0]."'>".$value[1]."</th>";

			if(isset($value[2]) && !isset($value[3]))
				$dataset[$value[0]] = $value[2];
			elseif(isset($value[2]) && isset($value[3]))
				$dataset[$value[0]][$value[2]] = $value[3];
			else
				$dataset[$value[0]] = "";
		}
		$html .= "</tr>";
		$html .= "</thead>";

		$html .= "<tbody>";
		foreach($data as $value) {
			$html .= "<tr>";
			foreach($dataset as $kkey => $kvalue) {
				$presenting_value = $value->{$kkey};

				if($kvalue == "timestamp")
					$presenting_value = date("Y-m-d", ($value->{$kkey}/1000));
				elseif(isset($kvalue["button"]) && $kvalue["button"]) {
					$payout_id = (string)$value->{$kkey};
					$url = admin_url( 'admin.php?page=yourpay_payouts_data&id='.$payout_id, 'admin' );
					$presenting_value = "<a href='{$url}'>{$payout_id}</a>";
				}
				elseif(isset($kvalue["function"]) && $kvalue["function"]) {
					$kvarray = $kvalue["function"];
					$presenting_value = ($kvarray[0]::{$kvarray[1]}($value->{$kkey}, $value->{$kvarray[2]}));
				}

				$html .= "<td>".$presenting_value."</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</tbody>";

		return $html;
	}



}