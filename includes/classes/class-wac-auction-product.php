<?php

class Woo_Auction_Product{
	
	public $yithTable;
	
	public function __construct(){
		global $wpdb;
		$this->yithTable = $wpdb->prefix."yith_wcact_auction";
		
		add_action('wp_ajax_woo_auction_search', array( &$this, 'ajax_auction_search' ) );
		add_action('wp_ajax_nopriv_woo_auction_search', array( &$this, 'ajax_auction_search' ) );
		
		//update timeleft on auctions
		add_action('wp_ajax_woo_updatetimeleft', array( &$this, 'updatetimeleft' ) );
		add_action('wp_ajax_nopriv_woo_updatetimeleft', array( &$this, 'updatetimeleft' ) );

		//update auction status
		add_action('wp_ajax_woo_updatestatus', array( &$this, 'updatestatus' ) );
	}
	
	//get if auction is live
	function is_auction_live($product){
		$status = $product->get_status();
		return $status == 'publish'? true : false;
	}
	
	//check if bid is unique (not same price bid by anyone) on same auction
	function is_unique_bid($auction_id,$bid,$type = 'bid'){
		global $wpdb;

		$query   = $wpdb->prepare( "SELECT {$type} FROM {$this->yithTable} WHERE auction_id = %d AND `{$type}` = {$bid}", $auction_id );
		$results = $wpdb->get_var( $query );
		
		if($results){
			return false;
		}
		return true;
	}
	
	//if new bid is lower than listing price when has higher bid
	function is_higherbid_thanlisting($product,$bid){
		$auction_id = $product->get_id();
		$maxBid  	= $this->get_max_bid($auction_id);
		$listingprice = &$product->get_reserve_price();
		
		if(isset($maxBid->bid) &&  ($maxBid->bid >= $listingprice) && ($bid < $listingprice)){
			return false;
		}
		return true;
	}
	
	//get auction bids
	function get_bids_auction($auction_id) {
		global $wpdb;

		$query   = $wpdb->prepare( "SELECT * FROM {$this->yithTable} WHERE auction_id = %d ORDER by CAST( bid AS decimal(50,5)) DESC, date ASC", $auction_id );
		$results = $wpdb->get_results( $query );
		return $results;
	}
		
	//get number of bids
	function total_bids($auction_id) {
		global $wpdb;
		
		$query   = $wpdb->prepare( "SELECT count(*) FROM {$this->yithTable} WHERE auction_id = %d ORDER by CAST( bid AS decimal(50,5)) DESC, date ASC", $auction_id );
		$results = $wpdb->get_var( $query );
		return $results;
	}
	
	//get maximum auction bid User
	function max_auction_bid($auction_id){
		return $this->get_max_bid($auction_id);
	}
	
	//get maximum bid (Order by date DESC)
	function get_max_bid($auction_id){
		global $wpdb;

		$query   = $wpdb->prepare( "SELECT * FROM {$this->yithTable} WHERE auction_id = %d ORDER by CAST(bid AS decimal(50,5)) DESC, date ASC LIMIT 1", $auction_id );
		$results = $wpdb->get_row( $query );

		return $results;
	}
	
	//get maximum AUTO BID (Order by date DESC)
	function get_max_autobid($auction_id){
		global $wpdb;

		$query   = $wpdb->prepare( "SELECT * FROM {$this->yithTable} WHERE auction_id = %d ORDER by CAST(autobid AS decimal(50,5)) DESC, date ASC LIMIT 1", $auction_id );
		$results = $wpdb->get_row( $query );

		return $results;
	}
	
	//get second maximum bid
	function get_second_max_bid($product_id){
		global $wpdb;

		$bids = array();
		$first_bid = $this->get_max_bid($product_id);
		if($first_bid){
			if ( isset($first_bid->user_id) ) {
				$query = $wpdb->prepare( "SELECT * FROM {$this->yithTable} WHERE auction_id = %d AND user_id <> %d ORDER by CAST(bid AS decimal(50,5)) DESC, date ASC LIMIT 1",$product_id,$first_bid->user_id);
				$second_bid = $wpdb->get_row( $query );
				if($second_bid){
					return $second_bid;
				}
			}
		}

		return false;
	}
	
	//get second maximum Auto bid (Order by date DESC)
	function get_second_max_autobid($product_id){
		global $wpdb;

		$bids = array();
		$first_bid = $this->get_max_autobid($product_id);
		if($first_bid){
			if ( isset($first_bid->user_id) ) {
				$query = $wpdb->prepare( "SELECT * FROM {$this->yithTable} WHERE auction_id = %d AND user_id <> %d AND autobid !=0 ORDER by CAST(autobid AS decimal(50,5)) DESC, date ASC LIMIT 1",$product_id,$first_bid->user_id);
				$second_bid = $wpdb->get_row( $query );
				if($second_bid){
					return $second_bid;
				}
			}
		}

		return false;
	}
	
	//get maximum auction bid User
	function max_auction_bid_byuser($user_id, $auction_id){
		global $wpdb;
		$query   = $wpdb->prepare( "SELECT bid,autobid FROM {$this->yithTable} WHERE user_id = %d AND auction_id = %d ORDER by CAST(bid AS decimal(50,5)) DESC LIMIT 1", $user_id, $auction_id );
		$Rmaxbid  = $wpdb->get_row( $query );
		$maxautobid = &$Rmaxbid->autobid;
		
		// print_r($Rmaxbid);
		
		//if autobid is zero in maxbid price
		if($maxautobid == 0){
			$qautobid = $wpdb->prepare( "SELECT max(autobid) FROM {$this->yithTable} WHERE user_id = %d AND auction_id = %d", $user_id, $auction_id );
			$maxautobid = $wpdb->get_var( $qautobid );
		}
		
		//if maximum autobid is more than simple bid
		if($maxautobid > 0 && $maxautobid > $Rmaxbid->bid){
			return ['type'=>'autobid','price'=>$maxautobid];
		}
		else{
			$bid = (isset($Rmaxbid->bid))? $Rmaxbid->bid : 0;
			return ['type'=>'bid','price'=>$bid];
		}
	}
	
	//get all bids on auction
	function get_auction_bids($auction_id,$limit=5){
		global $wpdb;
		
		$query   = $wpdb->prepare( "SELECT max(bid) as maxbid, user_id FROM {$this->yithTable} WHERE auction_id = %d GROUP BY user_id ORDER by CAST( bid AS decimal(50,5)) DESC, date ASC LIMIT {$limit}", $auction_id );
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	//user in last 5 bidders
	function last_bidders($auction_id,$limit=5){
		$lastBidders = $this->get_auction_bids($auction_id,$limit);
		$allBidders = array();
		if(sizeof($lastBidders) > 0){
			foreach($lastBidders as $bidder){
				$allBidders[] = $bidder->user_id;
			}
		}
		return $allBidders;
	}
	
	//get bids by user
	function bids_by_user($auction_id,$user_id){
		global $wpdb;
		$query   = $wpdb->prepare( "SELECT bid FROM {$this->yithTable} WHERE auction_id = %d AND user_id = %d ORDER by CAST(bid AS decimal(50,5)) DESC, date ASC", $auction_id, $user_id );
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	//get user total bids on all auctions (group by auction)
	function user_totalbids($user_id,$crproduct){
		global $wpdb;
		$poststbl = $wpdb->prefix.'posts';
		//get published auctions with user id
		$query   = $wpdb->prepare( "SELECT auction_id FROM {$this->yithTable} as act LEFT JOIN {$poststbl} as pst ON act.auction_id=pst.ID WHERE user_id = %d AND pst.post_status='publish' GROUP by act.auction_id ORDER by date DESC", $user_id);
		$auctions_by_user = $wpdb->get_results( $query );
		// print_r($auctions_by_user);
		
		$total_bids = 0;
		foreach ($auctions_by_user as &$auction_obj) {
			$auction_id = $auction_obj->auction_id;
			$product = wc_get_product($auction_id);
			// echo $auction_id.'<br>';
			
			if($product && $product->is_type('auction')){
				
				//skip current product from total bids
				if($product->get_id() == $crproduct->get_id()){
					$total_bids = 0;
					break;
				}
				elseif(!$product->is_closed()){
				//if product is auction and not closed or Live auction
					// echo $auction_id.'-closed~';
					$total_bids++;
				}
			}
		}
		return $total_bids;
	}
	
	//get all auctions by user with WP_query
	function get_auctions_by_user($uid,$args=array()){
		global $WCMp;
		
		$term_id = get_user_meta($uid, '_vendor_term_id', true);
		$defaults = array(
			// 'author' => $uid,
			'post_type' => 'product',
			'tax_query' => array(
				array (
					'taxonomy' 	=> $WCMp->taxonomy->taxonomy_name,
					'field' 	=> 'term_id',
					'terms' 	=> $term_id,
				)
			)
		);
		$args = wp_parse_args( $args, $defaults );
		$allauctions = array();
		$author_posts = new WP_Query( $args );
		if( $author_posts->have_posts() ) {
			while( $author_posts->have_posts()) { 
				$author_posts->the_post();
				$allauctions[] = get_the_ID();
			}
			wp_reset_postdata();
		}
		
		return $allauctions;
	}
	
	/*
	 * get auctions query
	 * Get all Live auctions and Future auctions
	 */
	function get_auctions($args){
		
		$defaults = array(
			'post_type' 	=> 'product',
			'post_status' 	=> 'publish',
			'meta_query'	=> array(
				array(
					'key'     => '_yith_auction_to',
					'value'   =>  strtotime('now'),
					'compare' => '>'
				)
			)
		);
		$args = wp_parse_args( $args, $defaults );
		$allauctions = array();
		$auctions = new WP_Query( $args );
		wp_reset_postdata();
		
		return $auctions;
	}
	
	//get ajax content on search
	function ajax_auction_search(){
		$args = array(
			's'	=> @$_POST['search']
		);
		$auctions = $this->get_auctions($args);
		// print_r($auctions);
		
		$allauctions = array();
		if( $auctions->have_posts() ) {
			while( $auctions->have_posts()) { 
				$auctions->the_post();
				$aucId = get_the_ID();
				$allauctions[] = ['id'=>$aucId,'image'=> get_the_post_thumbnail_url($aucId,'thumbnail'),'title'=>get_the_title(),'link'=> get_permalink()];
			}
		}
		
		$return  = ['total_count' => $auctions->post_count, 'items'=> $allauctions];
		
		// print_r($allauctions); die;
		
		wp_send_json($return);
		die;
	}
	
	/*
	 * check if time difference less than 10 bid to finish auction and new bid made
	 * if max bid placed in last 10 mins
	 * anti snipping name of this rule.
	 */
	function is_antisnipping($product){
		$auction_id = $product->get_id();
		$auction_finish = ($datetime = yit_get_prop($product, '_yith_auction_to', true)) ? $datetime : NULL;
		$maxbidTime = $this->get_maxlastbid_time($auction_id);
		// $timeDiff 	= round(abs($auction_finish - $maxbidTime) / 60);
		$timeDiff 	= wac_timeleft($auction_finish,$maxbidTime);
		
		$date = strtotime('now');
		// echo $date.'-'.$auction_finish.'-'.$maxbidTime.'-'.$timeDiff.'-';
		
		return 	($timeDiff <= 10)? true : false;
	}
	
	//get maximum last bid time
	function get_maxlastbid_time($auction_id){
		$maxBid 	= $this->max_auction_bid($auction_id);
		$maxbidTime = 0;
		
		//get last bid date or Autobid date if Autobiddate is greater than bid date
		if(isset($maxBid->date)){
			if(!empty($maxBid->autobiddate) && $maxBid->autobiddate > $maxBid->date){
				$maxbidTime = strtotime($maxBid->autobiddate);
			}
			else{
				$maxbidTime = strtotime($maxBid->date);
			}
		}
		
		return $maxbidTime;
	}
	
	//get time left or start time for auction with id
	function get_timeleft($product){
		$args = array(
			'product' => $product,
		);
		ob_start();
			wac_get_template('single-product/updatetimeleft.php', $args);
		return ob_get_clean();
	}
	
	//get auction data for AJAX request
	function get_ajax_auction_data($auction_id){
		$product = wc_get_product($auction_id);
		$product = apply_filters('yith_wcact_get_auction_product',$product);
		
		return ['timeleft' => $this->get_timeleft($product), 'price' => wc_price($product->get_price())];
	}
	
	//update time of every countdown in auctions page
	function updatetimeleft(){
		$templates = array();
		$products = $_POST['products'];
		foreach($products as $auction_id){
			$templates[$auction_id] = $this->get_ajax_auction_data($auction_id);
		}
		echo json_encode($templates);
		die;
	}

	//get auction status with ajax
	function updatestatus(){
		global $woo_auction;
		$templates = array();
		$products = $_POST['products'];
		foreach($products as $auction_id){
			$product = wc_get_product($auction_id);
			$product = apply_filters('yith_wcact_get_auction_product',$product);
			$status  = $woo_auction->vendor_profile->auction_get_status($product);
			$auction = array(
				'plink'		=> get_permalink($auction_id),
				'status'	=> $status
			);
			$auctionStatus = $woo_auction->vendor_profile->status_hlink($auction);
			$templates[$auction_id] = $auctionStatus;
		}
		echo json_encode($templates);
		die;
	}

	function get_bid_comments($bid,$user_id){
		global $wpdb;
		$bidData = $wpdb->get_row("SELECT id,comment FROM wp7i_yith_wcact_auction WHERE bid='{$bid}' AND user_id='{$user_id}' " );
		$comments = (isset($bidData->comment))? unserialize($bidData->comment) : [];
		return $comments;
	}
}