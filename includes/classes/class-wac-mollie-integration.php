<?php
/*
 * Mollie Integration
 */
class Woo_Auction_Mollie_integration{
	
	//check if enabled
	function is_enabled(){
		$enabled = wac_setting_field('mollie_enabled');
		return ($enabled == 1)? true : false;
	}
	
	//get api key
	function apikey(){
		$apikey = wac_setting_field('mollie_apikey');
		return (!empty($apikey))? $apikey : false;
	}
	
	//get mollie payment mode (live, sandbox)
	function mode(){
		$mode = wac_setting_field('mollie_mode');
		return (!empty($mode))? $apikey : 'sandbox';
	}
	
	//get success page id
	function pageid(){
		$spage = wac_setting_field('mollie_thanks');
		return (!empty($spage))? $spage : 0;
	}
	
	//initialize mollie payment gateway object
	function init(){
		if($this->is_enabled()){
			require_once(WOO_AUCTION_PATH."/integration/Mollie/API/Autoloader.php");
			if($this->apiKey()){
				$apiKey = $this->apiKey();
				$mollie = new Mollie_API_Client;
				$mollie->setApiKey($apiKey);
				return $mollie;
			}
			else{
				return "Mollie API key is not set!";
			}
		}
		else{
			return "Mollie is not enabled!";
		}
	}
}