<?php

/**
 * Autobid functionality on Auction page.
 */
class Woo_Auction_Autobid {
	
	public function __construct(){
		global $wpdb;
		$this->yithTable 	= $wpdb->prefix."yith_wcact_auction";
		
		add_action('wp_ajax_woo_auction_autobid', array( &$this, 'ajax_auction_autobid' ) );
		add_action('wp_ajax_nopriv_woo_auction_autobid', array($this, 'redirect_to_my_account'));
		
		add_action('yith_wcact_after_add_button_bid',array($this, 'add_autobid_button'));
		add_action('yith_wcact_successfully_bid', array($this,'autobid_trigger_onbid'),10,2);
		
		//on activate plugin create tables if not exists
		register_activation_hook( WOO_AUCTION_MAINFILE, array($this,'alter_yith_plugin_auction_table') );
		
		add_filter('yith_wcact_user_can_make_bid',array($this,'validate_bid'),13,2);
		
		//trigger bid by user and product id (auction id)
		// add_action('init',function() { $s=[1,61]; $p=wc_get_product($s[1]); $this->autobid_trigger_onbid($s[0],$p); });
	}
	
	//validate bid (Bid or Auto Bid)
	function validate_bid($status,$userid){
		global $woo_auction;
		$haserror = false;
		
		if ($userid && isset($_POST['bid']) && isset($_POST['product'])){
			$product_id = $_POST['product'];
			$bid 		= $_POST['bid'];
			$product 	= wc_get_product($product_id);
			$wacproduct = $woo_auction->product;
			$bidtype 	= (isset($_POST['bidtype']))? $_POST['bidtype'] : 'bid';
			$listingprice = &$product->get_reserve_price();
			
			//allowed only 3 bids per user
			$bidsplaced = $wacproduct->user_totalbids($userid,$product);
			
			//last bid price
			$yithbids = YITH_Auctions()->bids;
			$last_bid_user = $yithbids->get_last_bid_user($userid, $product_id);
			$last_autobid_user_obj = $wacproduct->max_auction_bid_byuser($userid, $product_id);
			$last_autobid_user = (isset($last_autobid_user_obj['price']))? $last_autobid_user_obj['price'] : $last_bid_user;
			
			//allowed bids per user
			$allowedBids = wac_allowed_bids();
			
			//maximum allowed bids per user
			if(!$haserror && $bidsplaced >= $allowedBids){
				$haserror = true;
				wc_add_notice(__("You can only bid on {$allowedBids} Live Auctions at the same time.",'woo-auction'), 'error');
			}
			elseif(!$haserror && $product && $product->is_type('auction') && $autobid > ($listingprice *1.5) ){
				//if product bid price more than 50% of listing price
				$haserror = true;
				wc_add_notice(__('Bid more than 50% of listing price is not allowed!','woo-auction'), 'error');
			}
			
			//if user new bid/autobid is lower than his last bid
			if(!$haserror && $last_autobid_user && $bid <= $last_autobid_user) {
				$haserror = true;
				wc_add_notice(sprintf(__('Please enter a bid that is higher than your own highest bid currently at this auction. (%s)', 'woo-auction'),
						wc_price($last_autobid_user)), 'error');
			}
			
			//exception case 
			//3 and 4th from auction rules.
			$exceptbidAllowed = false;
			
			//check if maximum autobid is unique or not
			if(!$haserror && !$wacproduct->is_unique_bid($product_id,$bid,'autobid')){
				$exceptbidAllowed = true;
				wc_add_notice(__('The amount of your (Auto) Bid was already entered through an Auto Bid of another Bidder.','woo-auction'), 'error');
			}
			
			//check if bid is not unique and removed the case for autobid
			//if placing (Bid or Auto Bid) amount is placed by (other Auto Bid) same then skip below case.
			if(!$haserror && !$wacproduct->is_unique_bid($product_id,$bid) && !$exceptbidAllowed){
				$haserror = true;
				wc_add_notice(__('Please enter a Bid that has not been placed yet.','woo-auction'), 'error');
			}
			
			//bid can't be placed below listing price if already has higher bid
			//if new bid is lower than listing price when has higher bid
			if(!$haserror && !$wacproduct->is_higherbid_thanlisting($product,$bid)){
				$haserror = true;
				wc_add_notice(__('You cannot Bid lower than the Listing Price if the Current Auction Price is already equal to or higher than the Listing Price','woo-auction'), 'error');
			}
			
			//if validation error.
			if($haserror){
				$url = array(
					'url' => get_permalink($product_id),
				);
				wp_send_json($url);
				return false;
			}
		}
		return $status;
	}
	
	//add autobid button
	function add_autobid_button($product){
		global $woo_auction;
		$wacStrings = $woo_auction->wacStrings;
		?>
		<button type="button" class="wac-autobid button alt"><?php echo __('Auto bid', 'woo-auction'); ?></button>
		<?php
		echo wac_ihelp($wacStrings['pd_autobid']);
	}
	
	//ajax auto bid save
	function ajax_auction_autobid(){
		// print_r($_POST);	
		global $woo_auction;
		$userid = get_current_user_id();
		$user_can_make_bid = apply_filters('yith_wcact_user_can_make_bid', true, $userid);

		if (!$user_can_make_bid) {
			die();
		}
		if ($userid && isset($_POST['bid']) && isset($_POST['product'])) {
			$bid = $_POST['bid'];
			$product_id = apply_filters( 'yith_wcact_auction_product_id',$_POST['product'] );
			$date = date("Y-m-d H:i:s");
			$product = wc_get_product($product_id);
			
			$wacproduct = $woo_auction->product;
			$end_auction = yit_get_prop($product, '_yith_auction_to', true);

			if (strtotime($date) < $end_auction) {

				$overtime = $product->get_overtime();
				if ($overtime) {

					$date_end = yit_get_prop($product, '_yith_auction_to', true);
					$date_now = time();

					$interval_seconds = $date_end - $date_now;
					$interval_minutes = ceil($interval_seconds / MINUTE_IN_SECONDS);
				}
				$set_overtime = false;

				if ($product && $product->is_type('auction')) {
					$bids 			= YITH_Auctions()->bids;
					$current_price 	= $product->get_price();
					$exist_auctions = $wacproduct->get_max_bid($product_id);
					$last_bid_user 	= $bids->get_last_bid_user($userid, $product_id);
					$lastbidprice 	= $this->user_lastautobidprice($userid, $product_id);
					
					//set autobid price.
					$autobid 	= $bid;
					
					//if no bid then use current price
					$currentBid = ($exist_auctions->bid)? $exist_auctions->bid : $current_price;
					// print_r($lastbidprice); die;
					
					if($autobid > $currentBid){
						//if auto bid amount is above current maximum bid price
						
						if(isset($lastbidprice->autobid) && $lastbidprice->autobid <= $autobid){
							//update auto bid price in table
							$this->updateautobid($autobid,$lastbidprice->id);
							
							wc_add_notice(__('You have successfully placed an auto bid.','woo-auction'), 'success');
						}
						else{
							$newbid  	= $currentBid + 10;
							$actualbid 	= $this->roundPrice($newbid);
							
							//add bid with autobid price
							$this->add_autobid($userid, $product_id, $actualbid, $autobid, $date);
							
							if (apply_filters('yith_wcact_show_message', true)) {
								wc_add_notice(__('You placed a new Auto Bid!','woo-auction'), 'success');
							}
						}
						
						//update current bid price above new price (according to autobid price)
						$this->autobid_trigger_onautobid($userid,$product,$autobid);
						
					}
					else{
						wc_add_notice(__('Please enter an auto bid that is above the current auction price.','woo-auction'), 'error');
					}
					
					$user_bid = array(
						'user_id' 	=> $userid,
						'product_id'=> $product_id,
						'bid' 		=> $autobid,
						'date' 		=> $date,
						'url' 		=> get_permalink($_POST['product']),
					);

					$actual_price = $product->get_current_bid();
					yit_save_prop($product, '_price', $actual_price);
				}

				wp_send_json($user_bid);
			} 
			else {
				$url = array(
					'url' => get_permalink($product_id),
				);
				wp_send_json($url);
			}
		}
		die();
	}
	
	//redirect to my account page
	function redirect_to_my_account(){
		if (!is_user_logged_in()) {
			$account = apply_filters('yith_wcact_redirect_url',wc_get_page_permalink( 'myaccount'));

			if (isset($_POST['bid']) && isset($_POST['product'])) {
				$url_to_redirect = add_query_arg('redirect_after_login',urlencode(get_permalink($_POST['product'])),$account);
				$array = array(
					'product_id' => $_POST['product'],
					'bid'       => $_POST['bid'],
					'url'       => $url_to_redirect,
				);

			}
			wp_send_json($array);
		}
		die();
	}
	
	// alter autobid table and add new column
	function alter_yith_plugin_auction_table(){
		global $wpdb;
		$yithTable = $this->yithTable;
		
		#Check to see if the column exists already, if not, then create it
		$found = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$yithTable}' AND column_name = 'autobid'");
		
		if(empty($found)){
			$sql = "ALTER TABLE `{$yithTable}` 
					ADD autobid INT(11) NOT NULL DEFAULT 0 AFTER bid, 
					ADD autobiddate datetime AFTER autobid";
			$wpdb->query($sql);
		}
		
		$found_comment = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$yithTable}' AND column_name = 'comment'");
		
		if(empty($found_comment)){
			$sql_comment = "ALTER TABLE `{$yithTable}` 
					ADD comment LONGTEXT AFTER date";
			$wpdb->query($sql_comment);
		}
		
	}
	
	//add autobid in auction
	function add_autobid($user_id, $auction_id, $bid, $autobid, $date ){
		global $wpdb;
		$yithTable = $this->yithTable;
		$comment = isset($_POST['comment'])? nl2br($_POST['comment']) : '';
		$comment = (!empty($comment))? serialize(array($comment)) : '';
		$insert_query = "INSERT INTO {$yithTable} (`user_id`, `auction_id`, `bid`, `autobid`, `autobiddate`, `date`, `comment`) VALUES ('" . $user_id . "', '" . $auction_id . "', '" . $bid . "' , '" . $autobid . "' , '" . $date . "' , '" . $date . "' , '" . $comment . "' )";
		$wpdb->query( $insert_query );
	}
	
	//get last bid price of user
	function user_lastautobidprice( $user_id, $auction_id ){
		global $wpdb;

		$yithTable = $this->yithTable;
		$query   = "SELECT * FROM {$yithTable} WHERE user_id = {$user_id} AND auction_id = {$auction_id} ORDER by CAST(autobid AS decimal(50,5)) DESC, date DESC LIMIT 1";
		$results = $wpdb->get_row( $query );

		return $results;
	}
	
	//trigger autobid functionality on placing simple bid
	function autobid_trigger_onbid($user_id,$product){
		$this->autobid_trigger_update_bids($user_id,$product);
	}
	
	//trigger autobid functionality on placing autobid
	function autobid_trigger_onautobid($user_id,$product,$autobid){
		$this->autobid_trigger_update_bids($user_id,$product,$autobid);
	}
	
	//trigger autobid functionality on placing simple bid and auto bid
	function autobid_trigger_update_bids($user_id,$product,$autobid=false){
		global $wpdb, $woo_auction;
		$wacproduct	= $woo_auction->product;
		$auction_id = $product->get_id();
		$yithTable 	= $this->yithTable;
		$query   = "SELECT * FROM `{$yithTable}` WHERE auction_id = '{$auction_id}' AND autobid != 0 ORDER by date DESC";
		$allAutoBids = $wpdb->get_results( $query );
		
		$currentBid = ($autobid)? $autobid : $product->get_current_bid();
		
		// print_r($allAutoBids);
		foreach($allAutoBids as $autoBid){
			
			// echo '('.$autoBid->id.'~'.$currentBid.')-';
			
			//skip already updated records (if normal bid is same as autobid)
			if($autoBid->autobid == $autoBid->bid){
				continue;
			}
			
			//get maximum autobid and second maximum normal bid
			$secMaxBid 		= $wacproduct->get_second_max_bid($auction_id);
			$maxAutobid 	= $wacproduct->get_max_autobid($auction_id);
			$secMaxAutoBid 	= $wacproduct->get_second_max_autobid($auction_id);
			
			/*__wac_pre($secMaxBid);
			__wac_pre($autoBid);
			__wac_pre($secMaxAutoBid);*/

			if($currentBid >= $autoBid->autobid || $currentBid >= $autoBid->bid){
				
				//if current maximum bid is already a maximum then check the second maximum auto bid 
				//and then update price to +10 of second maximum auto bid price
				if(($maxAutobid->autobid >= $autoBid->autobid) && ($maxAutobid->id == $autoBid->id) 
					&& $secMaxAutoBid && ($secMaxAutoBid->autobid == $autoBid->autobid)
				){
					$updatedBid  = $secMaxAutoBid->autobid + 10;
					$updatedBid  = ($updatedBid <= $autoBid->autobid)? $updatedBid : $autoBid->autobid;
					$this->updatebid($updatedBid,$autoBid);
				}
				//if maximum autobid is greater than autobid bid for different user.
				elseif(($maxAutobid->autobid >= $autoBid->autobid) && ($maxAutobid->id != $autoBid->id)){
					$updatedBid  = $autoBid->autobid;
					$this->updatebid($updatedBid,$autoBid);
				}
				else{
					//if current user is already highest bidder then skip
					if($this->is_highest_bidder($user_id,$auction_id,$autoBid)){
						//skip if user is already top bidder
						continue;
					}
					//if second normal bid is greater than second maximum autobid
					elseif(($secMaxAutoBid && $secMaxBid) && ($secMaxBid->bid > $secMaxAutoBid->autobid || !$secMaxAutoBid)){
						$latestPrice = $product->get_current_bid() + 10;
						$updatedBid  = ($autoBid->autobid > $latestPrice)? $latestPrice : $autoBid->autobid;
					}
					//if user new autobid is greater than second max bid
					elseif($secMaxAutoBid && $autoBid->autobid > $secMaxAutoBid->autobid){
						$latestPrice = $secMaxAutoBid->autobid + 10;
						$updatedBid  = ($latestPrice <= $autoBid->autobid)? $latestPrice : $autoBid->autobid;
					}
					//if user bid is same as second maximum bidder than add +10
					elseif($secMaxBid && $secMaxBid->bid == $autoBid->bid){
						$latestPrice = $product->get_current_bid() + 10;
						$updatedBid  = ($autoBid->autobid > $latestPrice)? $latestPrice : $autoBid->autobid;
					}
					else{
						$updatedBid  = $autoBid->autobid;
					}
					$this->updatebid($updatedBid,$autoBid);
				}
			}
			elseif($autoBid->autobid > $currentBid && $autoBid->bid < $currentBid){
				//get current bid is above autobid and less than simple bid
				$updatedBid = $currentBid + 10;
				// echo $updatedBid.' - '.$autoBid->user_id.' : updatebid<br>';
				$this->updatebid($updatedBid,$autoBid);
			}
			else{
				// echo $autoBid->user_id.' : notupdatebid<br>';
			}
			
			//set current bid again with updated.
			$currentBid = $product->get_current_bid();
		}
		
		//check and extend auction time. (if through antisnipping or Autobid)
		$this->extend_auction_time($product);
		
		// print_r($allAutoBids); die;
	}
	
	//check if current user is highest bidder
	function is_highest_bidder($user_id,$auction_id,$iterate_bid){
		global $woo_auction;
		$wacproduct	= $woo_auction->product;
		$maxBid 	= $wacproduct->get_max_bid($auction_id);
		
		if($user_id == $maxBid->user_id && $user_id == $iterate_bid->user_id){
			return true;
		}
		return false;
	}
	
	//function update/extend auction time
	function extend_auction_time($product){
		/** 
		 * get maximum bid timedifference
		 * if bid difference in 10min left time when auction finishing
		 * It will display countdown of 10min time.
		 *
		 * e.g) If bid is placed on 12:05 and current time is 12:08 and auction finish time 12:13 
		 * Then it will start timer from countdown 10 and end on 12:15min
		 */

		//if max bid placed in last 10 mins
		global $woo_auction;
		$wacproduct = $woo_auction->product;
		
		if($wacproduct->is_antisnipping($product)){
			
			$auction_id = $product->get_id();
			$maxbidTime = $wacproduct->get_maxlastbid_time($auction_id);
			$auction_finish = ($datetime = yit_get_prop($product, '_yith_auction_to', true)) ? $datetime : NULL;
			$timeDiff 	= wac_timeleft($auction_finish,$maxbidTime);			
			$seconds 	= 600 - ($auction_finish - $maxbidTime);
		
			// echo 'seconds'.$seconds.'-'.$auction_finish.'-'.$crBiddate;
			//if seconds more than zero then add seconds to time
			if($seconds > 0){
				$extendedTime = strtotime("+{$seconds} seconds",$auction_finish);
				yit_set_prop($product,'_yith_auction_to',$extendedTime);
				
				//Dutch language ID.
				$product_id_nl  = wac_NL_id($post_id);
				if(!empty($product_id_nl)){
					update_post_meta($product_id_nl,'_yith_auction_to',$extendedTime);
				}
			}
		}
	}
	
	//update bid in table
	function updatebid($bid,$bidObj){
		global $wpdb;
		$id   = $bidObj->id;
		$date = date("Y-m-d H:i:s");
		$yithTable = $this->yithTable;
		$update_query = "UPDATE {$yithTable} SET `bid` = '{$bid}', `autobiddate` = '{$date}' WHERE `id`={$id}";
		// $update_query = "UPDATE {$yithTable} SET `bid` = '{$bid}'  WHERE `id`={$id}";
		$wpdb->query( $update_query );
	}
	
	//update auto bid price
	function updateautobid($bid,$id){
		global $wpdb;
		$date = date("Y-m-d H:i:s");
		$yithTable = $this->yithTable;
		$comment = isset($_POST['comment'])? nl2br($_POST['comment']) : '';  
		$bid_comment = $wpdb->get_var("SELECT comment FROM {$yithTable} WHERE id = '".$id."'" );
		echo $bid_comment;
		$bid_comments =  unserialize($bid_comment);
		echo "<pre>"; print_r($bid_comments); 
		if(is_array($bid_comments)){
			array_push($bid_comments,$comment);
		}
		else{
			$bid_comments = array($comment);
		}
		
		$bid_comments = serialize($bid_comments);
		//echo $bid_comments; die;
		$update_query = "UPDATE {$yithTable} SET `autobid` = '{$bid}', `autobiddate` = '{$date}' , `comment` = '{$bid_comments}' WHERE `id`={$id}";
		$wpdb->query( $update_query );
	}
	
	//get round price by 10 (tens)
	function roundPrice($price,$n=10){
		return ceil($price / $n) * $n;	
	}
}