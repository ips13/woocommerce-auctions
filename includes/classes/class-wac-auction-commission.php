<?php

/**
 * Auction commission
 */
class Woo_Auction_Commission {
	
	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'auction_commission_meta_box') );
		add_action( 'init', array($this,'process_auction_commission_vendor') );
		add_action( 'init', array($this,'save_auction_commission_from_mollie') );
		
		//thanks page
		add_shortcode( 'woo-mollie-thanks-page',array($this,'thankyou_page'));
		
		//display payment box on report page.
		add_shortcode( 'woo-auction-payment',array($this,'woo_auction_payment_vendor'));
	}
	
	// get commission price
	function get_commission_price($auction_id) {
		global $woo_auction;
		$listingPrice = get_post_meta($auction_id,'_yith_auction_reserve_price',true);
		$wacproduct   = $woo_auction->product;
		$maxBid 	  = $wacproduct->get_max_bid($auction_id);
		$product 	  = wc_get_product($auction_id);
		
		if($product->is_type('auction')){
			if($product->is_closed()){
				if(isset($maxBid->bid)){ 
					$highestBid = (isset($maxBid->bid))? $maxBid->bid : 0;
					if($highestBid > $listingPrice){
						$comm_multi = wac_setting_field('commission_multi');
						$commission = ($highestBid - $listingPrice) * $comm_multi;
						return wac_price($commission);
					}
				} 
			}
		}
		
		return "NA";
		
	}
	
	//add commission box in auction edit page
	function auction_commission_meta_box() {

		add_meta_box(
			'auction-commisssion',
			__( 'Auction Commission','woo-auction' ),
			array($this,'auction_commission_meta_box_callback'),
			'product',
			'side'
		);
	}
	
	//show auction payment box on report page
	function woo_auction_payment_vendor($atts){
		$atts = shortcode_atts( array(
			'pid' => 1
		), $atts);
		
		ob_start();
			if($atts['pid'] != 1){
				$args = array('auction_id' => $atts['pid'],'showform'=>true);
				wac_get_template('shortcode/paycommission-form.php',$args);
			}
			else{
				echo 'Nothing to display!';
			}
		$html = ob_get_clean();
		
		return $html;		
	}

	/**
	 * Display auction data on edit product page
	 */
	function auction_commission_meta_box_callback( $post ) {
		$auctionID = $post->ID;
		$args = array('auction_id' => $auctionID,'showform'=>false);
		return wac_get_template('shortcode/paycommission-form.php',$args);
	}
	
	/**
	 * On save check if paycmisn submitted
	 * process mollie payment and redirect user to new page.
	 */
	function process_auction_commission_vendor(){
		global $woo_auction;
		
		//pay commission script
		if(isset($_POST['paycmisn'])){
			// print_r($_POST); die;
			
			try{
				$mollie = $woo_auction->mollie->init();
				if(is_object($mollie)){
					$user_ID 	= $_POST['user_ID'];
					$auction_id = $_POST['ID'];
					$commission = $_POST['com_price'];
					$vatprice 	= $_POST['vat_price'];
					
					$totalAmount = $commission + $vatprice;
					$description = 'Auction Payment: '.$_POST['post_title'];
					$payData = array(
						"amount"       => $totalAmount,
						"description"  => $description,
						// "redirectUrl"  => $this->thankyou_link($auction_id),
						"redirectUrl"  => $woo_auction->vendor_profile->auctionreport_link(),
						"webhookUrl"   => site_url()."/?molliepay=directhit",
						"metadata"     => array(
							"auction_id" 	=> $auction_id,
							"user_id" 		=> $user_ID
						),
						'method'		=> 'ideal'
					);
					
					$payment = $mollie->payments->create($payData);
					
					// $woo_auction->pre($payment);
					header("Location: " . $payment->getPaymentUrl());
				}
				else{
					//display error message
					echo $mollie;
				}
			}
			catch (Mollie_API_Exception $e){
				echo "Failed to execute: " . htmlspecialchars($e->getMessage());
			}
			die;
		}
		
	}

	//save auction commission into options
	function save_auction_commission_from_mollie(){
		global $woo_auction;
		
		if(isset($_GET['molliepay'])){
			
			$molliepay = $_GET['molliepay'];
			
			switch($molliepay){
				
				case 'directhit':
					$mollie = $woo_auction->mollie->init();
					if(is_object($mollie)){
						try{
							$payment  = $mollie->payments->get($_POST["id"]);
							$auction_id = $payment->metadata->auction_id;
							update_post_meta($auction_id,'_molliepay_data',$payment);
							update_post_meta($auction_id,'_molliepay_txnid',$payment->id);
							update_post_meta($auction_id,'_molliepay_status',$payment->status);
							//if payment done from mollie saved yith payment status
							if($payment->status == 'paid'){
								update_post_meta($auction_id,'_yith_auction_paid_order',1);
							}
						}
						catch (Mollie_API_Exception $e){
							echo "Failed to execute: " . htmlspecialchars($e->getMessage());
						}
					}
					else{
						//display error message
						echo $mollie;
					}
					break;
					
				case 'returned':
					$auction_id = $_GET['auction_id'];
					$spage 		= $woo_auction->mollie->pageid();
					$spageUrl 	= get_permalink($spage);
					$spageUrl  .= '?aucid='.$auction_id;
					wp_redirect($spageUrl); die;
					break;
			}
			
			die;
		}
	}
	
	/*
	 * Thank you page redirection
	 */
	function thankyou_link($auction_id){
		global $woo_auction;
		$spage 		= $woo_auction->mollie->pageid();
		$spageUrl 	= get_permalink($spage);
		$spageUrl  .= '?aucid='.$auction_id;
		return $spageUrl;
	}
	
	/** 
	 * Thankyou page 
	 */
	function thankyou_page(){
		
		if(isset($_GET['aucid'])){
			$auctionID = $_GET['aucid'];
			$mollie_status = get_post_meta($auctionID,'_molliepay_status',true);
			
			if(!empty($mollie_status)){
				echo '<ul class="mollie-thanks-list">';
					echo "<li>Payment Status: <strong>{$mollie_status}</strong></li>";
				
				if($mollie_status == 'paid'){				
				
					echo "<li>Thanks your commission has been processed</li>";
					$mollie_txnid = get_post_meta($auctionID,'_molliepay_txnid',true);
					echo "<li>Your commission has been paid successfully!</li>";
					echo "<li>Transaction ID: <strong>{$mollie_txnid}</strong></li>";
					
					return;
				}
				elseif($mollie_status == 'cancelled'){
					echo "<li><strong>Please try again! Failed to Pay.</strong></li>";
				}
				else{
					echo "<li><strong>Please try again! Failed to Pay.</strong></li>";
				}
				echo '</ul>';
				
			}
			else{
				echo 'Something went wrong to complete your commission process. Please contact admin for more information.';
			}
		}
		else{
			echo 'Nothing to display!';
		}
		
	}
	
}