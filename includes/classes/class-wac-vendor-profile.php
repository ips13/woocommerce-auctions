<?php

/**
 * Vendor profile Dashboard and Settings.
 */
class Woo_Auction_VendorProfile{
	
	public $vendor_pageid, $aucPage;
	public $wac_query_vars = array();
	 
	public function __construct(){
		
		$this->getpageids();
		$this->wac_endpoints_query_vars();
		add_action('init', array(&$this, 'add_wac_endpoints'), 15);
		add_action('woo_auction_vendor_navigation',array($this,'add_dashboard_navs'));
		// add_action('woo_auction_vendor_header',array($this,'woo_auction_vendor_header'));
		add_action('woo_auction_vendor_content',array($this,'woo_auction_vendor_content'));
		
		add_shortcode('woo-auction-vendor-dashboard',array($this,'vendor_profile'));
		add_filter('is_wcmp_vendor_dashboard',array($this,'set_in_vendor_dashboard_page'));
		
		add_action('wp_ajax_woo_auction_save_vendor_profile', array( &$this, 'save_vendor_profile' ) );
		
		add_filter( 'pre_get_document_title',array($this,'dashboard_endpoint_titles'));
	}
	
	//get current page id
	function getpageids(){
		$setting =  get_option('woo_auction_options');
		$vpageID =  $setting['vpage_dashboard'];
		$aucPage =  $setting['auction_page'];
		$this->vendor_pageid = isset($vpageID)? $vpageID : 0;
		$this->aucPage = isset($aucPage)? $aucPage : 0;
	}
	
	//get vendor profile view
	function vendor_profile(){
		global $WCMp, $wp;
        
		/* print_r($wp->query_vars);
		echo 'asfdasf: '.$this->get_current_endpoint(); */
		
		//include auction form
		return wac_get_template('shortcode/vendor-profile/view.php');
	}
	
	//add endpoints for profile view
	function add_wac_endpoints(){
		$mask = EP_PAGES;
        foreach ($this->wac_query_vars as $key => $var) {
            if (!empty($var['endpoint'])) {
                add_rewrite_endpoint($var['endpoint'], $mask);
            }
        }
		$this->dashboardlink = get_permalink($this->vendor_pageid);
	}
	
	//add query vars to display title
	function wac_endpoints_query_vars(){
		
		$endpoints = array(
			'auction-detail' => array(
				'label' => __('Auction Detail','woo-auction'),
				'endpoint' => 'auction-detail'
			),
			'auction-reports' => array(
				'label' => __('Auction Reports','woo-auction'),
				'endpoint' => 'auction-reports'
			),
			'account-details' => array(
				'label' => __('Account Details','woo-auction'),
				'endpoint' => 'account-details'
			),
			'paycommission' => array(
				'label' => __('Pay Commission','woo-auction'),
				'endpoint' => 'paycommission'
			),
		);
		
		$this->wac_query_vars = $endpoints;
			
		return $endpoints;
	}
	
	//get current endpoint name
	function get_current_endpoint() {
        global $wp;
        foreach ($this->wac_query_vars as $key => $value) {
            if(isset($wp->query_vars[$key])){
                return $key;
            }
        }
        return '';
    }
	
	//set dashboar endpoint
	function dashboard_endpoint_titles( $title ) {
		foreach ($this->wac_query_vars as $key => $var) {
            if (!empty($var['endpoint']) && $var['endpoint'] == $this->get_current_endpoint()) {
                return $var['label'];
            }
        }
		return $title;
	}
	
	//get auction detail id on view page
	function auctiondetail_id() {
        global $wp;
		$endpoint = 'auction-detail';
		if(isset($wp->query_vars[$endpoint])){
			return $wp->query_vars[$endpoint];
		}
		
		$endpoint = 'paycommission';
		if(isset($wp->query_vars[$endpoint])){
			return $wp->query_vars[$endpoint];
		}
        return 0;
    }
	
	//get auction title
	function auction_title($auc_id){
		$product = wc_get_product($auc_id);
		$headTitle = 'Not Found!';
		if(!empty($product)){
			$headTitle = $product->get_title();
		}
		return $headTitle;
	}

	//set vendor dashboard page in backend
	function set_in_vendor_dashboard_page($is_vpage){

		$vendorPageid = $this->vendor_pageid;
		
		if(is_page($vendorPageid)){
			return true;
		}

		//if has in different language for Start Auction
        $vendorPageid_nl = wac_NL_id($vendorPageid);
        if(!empty($vendorPageid_nl)){
            return is_page($vendorPageid_nl)? true : false;
        }
        
		return $is_vpage;
	}
	
	//add dashboard navigation (Title and Side Links)
	function add_dashboard_navs($args = array()){
		global $WCMp;
		
		$startAuction = get_permalink($this->aucPage);
		$mainpagelink = get_permalink($this->vendor_pageid);
		
		$vendor_nav = [
			'start-auction' => [
                'label' => __('Start auction','woo-auction'),
				'url' => $startAuction,
				'capability' => apply_filters('wcmp_vendor_dashboard_menu_dashboard_capability', true),
				'position' => 1,
				'submenu' => array(),
				'link_target' => '_blank',
				'nav_icon' => 'wcmp-font ico-product-icon'
            ],
			'dashboard' => [
                'label' => __('Dashboard','woo-auction'),
				'url' => $mainpagelink,
				'capability' => apply_filters('wcmp_vendor_dashboard_menu_dashboard_capability', true),
				'position' => 2,
				'submenu' => array(),
				'link_target' => '_self',
				'nav_icon' => 'wcmp-font ico-dashboard-icon'
            ],
			'auction-reports' => [
				'label' => __('Auction reports','woo-auction'),
				'url' => esc_url($mainpagelink.'auction-reports'),
				'capability' => true,
				'position' => 3,
				'submenu' => array(),
				'link_target' => '_self',
				'nav_icon' => 'wcmp-font ico-reports-icon'
			],
			'account-details' => [
				'label' => __('Account details','woo-auction'),
				'url' => esc_url($mainpagelink.'account-details'),
				'capability' => true,
				'position' => 4,
				'submenu' => array(),
				'link_target' => '_self',
				'nav_icon' => 'wcmp-font ico-store-settings-icon'
			]
		];
		
		$this->vendor_nav = $vendor_nav;
		
		wac_get_template('shortcode/vendor-profile/navigation.php',array('nav_items' => $vendor_nav));
	}

	//custom header nav for dashboard Landlord
	function dashboard_header_nav() {
        $vendor = get_current_vendor();
		$startAuction = get_permalink($this->aucPage);
		$allAuctions = site_url().'/auctions';
        $header_nav = array(
            'shop-link' => array(
                'label' => __('All Auctions', 'woo-auction')
                , 'url' => apply_filters('wac_vendor_shop_permalink', esc_url($allAuctions))
                , 'class' => ''
                , 'capability' => true
                , 'position' => 10
                , 'link_target' => '_blank'
                , 'nav_icon' => 'wcmp-font ico-my-shop-icon'
            ),
            'start-auction' => array(
                'label' => __('Start Auction', 'woo-auction')
                , 'url' => apply_filters('wac_vendor_submit_product', esc_url($startAuction))
                , 'class' => ''
                , 'capability' => true
                , 'position' => 20
                , 'link_target' => '_blank'
                , 'nav_icon' => 'wcmp-font ico-product-icon'
            )
        );
        return apply_filters('wac_vendor_dashboard_header_nav', $header_nav);
    }
	
	//dispay header in auction dashboard top
	function wac_create_vendor_dashboard_breadcrumbs($current_endpoint, $nav = array(), $firstLevel = true) {
        $nav = !empty($nav) ? $nav : $this->vendor_nav;
        $resultArray = array();
        $current_endpoint = $current_endpoint ? $current_endpoint : 'dashboard';
        foreach ($nav as $endpoint => $menu) {
            if ($endpoint == $current_endpoint) {
                if ($firstLevel) {
                    return '<i class="' . $menu['nav_icon'] . '"></i><span> ' . $menu['label'] . '</span>';
                } else {
                    return array('<span> ' . $menu['label'] . '</span>');
                }
            }
            if (isset($menu['submenu']) && !empty($menu['submenu'])) {
                $result = $this->wac_create_vendor_dashboard_breadcrumbs($current_endpoint, $menu['submenu'], false);
                if ($result) {
                    $resultArray = array_merge($result);
                    if (isset($menu['submenu'][$current_endpoint]['nav_icon']) && !empty($menu['submenu'][$current_endpoint]['nav_icon'])) {
                        $resultArray[] = '<i class="' . $menu['submenu'][$current_endpoint]['nav_icon'] . '"></i>';
                    } else {
                        $resultArray[] = '<i class="' . $menu['nav_icon'] . '"></i>';
                    }
                    if (!$firstLevel) {
                        return $resultArray;
                    }
                }
            }
        }
        if (count($resultArray)) {
            return implode(array_reverse($resultArray));
        }
        return false;
    }
	
	//display content according to endpoint selected
	function woo_auction_vendor_content() {
        global $wp, $WCMp, $woo_auction;
		$args = array();
		
		$woo_auction->enqueue_scripts();
		$vendor = get_wcmp_vendor(get_current_vendor_id());
		if($vendor){
			switch ($this->get_current_endpoint()) {
				case 'auction-detail':
					$templatepath = 'auction-detail.php';
					$WCMp->library->load_dataTable_lib();
					$woo_auction->dashboard_scripts();
					$args = array('auction_id' => $this->auctiondetail_id(),'wac_vpclass'=>$this);
				break;
				case 'auction-reports':
					$templatepath = 'auction-reports.php';
					$WCMp->library->load_dataTable_lib();
					$woo_auction->dashboard_scripts();
					$args = array('allAuctions'=> $this->auctionsbyUserID('reports'),'auction_id' => $this->auctiondetail_id(),'wac_vpclass'=>$this);
				break;
				case 'account-details':
					$WCMp->library->load_upload_lib();
					$woo_auction->dashboard_scripts();
					$templatepath = 'account-details.php';
					$args = array('wac_vpclass'=>$this);
				break;
				case 'paycommission':
					$templatepath = 'paycommssion.php';
					$args = array('auction_id' => $this->auctiondetail_id(),'wac_vpclass'=>$this);
				break;
				default:
					$templatepath = 'dashboard.php';
					$WCMp->library->load_dataTable_lib();
					$woo_auction->dashboard_scripts();
					$args = array('allAuctions'=> $this->auctionsbyUserID(),'wac_vpclass'=>$this);
				break;
			}
		}
		else{
			$templatepath = 'vendor-allowed.php';
		}
		
		wac_get_template('shortcode/vendor-profile/'.$templatepath,$args);
    }
	
	//get all auctions by user id
	function auctionsbyUserID($type='dashboard',$uid=false){
		
		$bids = YITH_Auctions()->bids;
		$user_id = ($uid)? $uid : get_current_user_id();
		$auctions = array();

		global $woo_auction;
		$wacproduct = $woo_auction->product;
		//echo "<pre>"; print_r($wacproduct); die;
			if($type == 'dashboard'){
				
				$auctions_by_user = $wacproduct->get_auctions_by_user($user_id,array('post_status'=>'any'));
				foreach ($auctions_by_user as $auction_id) {
					$product = wc_get_product($auction_id);
					
					if (!$product) continue;

					$product = apply_filters('yith_wcact_get_auction_product',$product);
					
					if($product->is_type('auction')){
						
						//get time remaining as per steps (Live, Approved, Closed etc..)
						$auctionStatus = $this->auction_get_status($product);
						$stepTime = $this->auction_steps_time($product,$auctionStatus);
						
						$auctions[] = array(
							'id' 		=> $product->get_id(),
							'price' 	=> wc_price($product->get_price()),
							'name' 		=> get_the_title($auction_id),
							'status' 	=> $auctionStatus,
							'listprice'	=> wc_price($product->get_reserve_price()),
							'crntprice'	=> wc_price($product->get_current_bid()),
							'time'		=> $stepTime,
							'plink'		=> get_permalink($product->get_id()),
							'billing'	=> $this->auction_billing_status($product),
							'report'	=> $this->auction_get_report($product)
						);
					}
				}
			}
			elseif($type == 'reports'){
				
				$auctions_by_user = $wacproduct->get_auctions_by_user($user_id);
				 
				foreach ($auctions_by_user as $auction_id) {
					$product = wc_get_product($auction_id);
					
					if (!$product) continue;
						
					if($product->is_type('auction')){
						//display only closed auctions
						if($product->is_closed()){
						// if($product->is_closed() || $product->is_closed_for_buy_now()){
							$exist_auctions = $wacproduct->get_max_bid($auction_id);
							$highestBid = (isset($exist_auctions->bid))? $exist_auctions->bid : 0;
							$date_end = yit_get_prop($product, '_yith_auction_to', true);
							$auction_end = date('d-m-Y', $date_end);
							
							$auctions[] = array(
								'id' 		=> $product->get_id(),
								'name' 		=> get_the_title($auction_id),
								'highbid' 	=> wc_price($highestBid),
								'listprice'	=> wc_price($product->get_reserve_price()),
								'bidders'	=> count($bids->get_users($auction_id)),
								'bids'		=> count($bids->get_bids_auction($auction_id)),
								'closing'	=> $auction_end,
								'report'	=> $this->auction_get_report($product),
								'dlink'		=> $this->auctiondetail_link($auction_id)
							);
						}
					}
				}
			}
			else{
				$auctions_by_user = $wacproduct->get_auctions_by_user($user_id);
				foreach ($auctions_by_user as $auction_id) {
					$product = wc_get_product($auction_id);
					
					if (!$product) continue;
					
					if($product->is_type('auction')){
						$auctions[] = array(
							'id' 		=> $product->get_id(),
							'name' 		=> get_the_title($auction_id)
						);
					}
				}
			}
		
		// print_r($auctions); die;
		
		return $auctions;
	}
	
	//set auction report link
	function auctionreport_link(){
		return get_permalink($this->vendor_pageid).'auction-reports';
	}
	
	//auction detail link page
	function auctiondetail_link($auction_id){
		return get_permalink($this->vendor_pageid).'auction-detail/'.$auction_id;
	}
	
	//pay commission link page
	function paycommission_link($auction_id){
		return get_permalink($this->vendor_pageid).'paycommission/'.$auction_id;
	}
	
	//list of all statuses
	function _get_status($key=''){
		$allStatuses = array(
			'closed' 	=> __('Closed','woo-auction'),
			'pfa' 		=> __('Pending for Approval','woo-auction'),
			'approved' 	=> __('Approved','woo-auction'),
			'draft' 	=> __('Draft','woo-auction'),
			'live' 		=> __('Live','woo-auction')
		);
		
		return (!empty($key))? $allStatuses[$key] : $allStatuses;
	}
	
	//get auction status
	function auction_get_status($product){
		global $woo_auction;
		$status = 'pfa';
		$wacproduct = $woo_auction->product;

		if($product->is_type('auction')){

			$date = strtotime('now');
			$auction_finish = ($datetime = yit_get_prop($product, '_yith_auction_to', true)) ? $datetime : NULL;
			$auction_start  = ($datetime = yit_get_prop($product, '_yith_auction_for', true)) ? $datetime : NULL;

			//if not approved yet by admin
			if($product->get_status() == 'draft') {
				$status = 'pfa';
			}
			//check if auction is not started (Future Auction)
			elseif ($date < $auction_start && !$product->is_start()) {
				$status = 'approved';
			}
			//if auction is Live (Live Auction)
			elseif($product->is_start() && !$product->is_closed()) {
				$status = 'live';
			}
			//if auction closed (Closed Auction)
			elseif($product->is_closed()) {
				$status = 'closed';
			}
		}
		return $status;
	}
	
	/** 
	 * show step time based on status
	 * auction time remaining as per steps
	 */
	function auction_steps_time($product,$status){
		
		$finishtime = '';
		$productID 	= $product->get_id();
		
		switch($status){
			case 'pfa':
			case 'closed':
			case 'draft':
				$stepTime = '-';
			break;
			
			case 'approved':
				$finishtime = yit_get_prop($product, '_yith_auction_for', true);
				$startsin = wac_datetimedifference('now',date('Y-m-d H:i:s',$finishtime));
				$stepTime = ['d'=>$startsin->d,'h'=>$startsin->h,'i'=>$startsin->i,'s'=>$startsin->s];
			break;
			
			case 'live':
				$finishtime = yit_get_prop($product, '_yith_auction_to', true);
				$endsin = wac_datetimedifference('now',date('Y-m-d H:i:s',$finishtime));
				$stepTime = ['d'=>$endsin->d,'h'=>$endsin->h,'i'=>$endsin->i,'s'=>$endsin->s];
			break;
		}
		
		if($stepTime != '-'){
			$stepTimeHtml = "<span class='wac_date_auction time-auction yith-date-auct-{$productID}' data-finish-time='{$finishtime}' data-yith-product='{$productID}'>
					<span class='yith-days'>{$stepTime['d']}d</span>
					<span class='yith-hours'>{$stepTime['h']}h</span>
					<span class='yith-mins'>{$stepTime['i']}m</span>
					<span class='yith-secs'>{$stepTime['s']}s</span>
			</span>";
		}
		else{
			$stepTimeHtml = '-';
		}
		
		return $stepTimeHtml;
	}
	
	//get auction billing status
	function auction_billing_status($product){
		
		//if auction is closed, paid and has reserve price
		if($this->is_billing_open($product)){
			$aucID	 = $product->get_id();
			$paylink = "<a href='{$this->paycommission_link($aucID)}' class='paycom-lnk'>Open</a>";
			return $paylink;
		}
		elseif($product->is_closed() && $product->is_paid()){
			return 'Paid';
		}
		return 'NA';
	}
	
	//get auction report
	function auction_get_report($product){
		//check if highest bid price above reserved price.
		if($product->is_closed()){
			global $woo_auction;
			$bids = YITH_Auctions()->bids;
			$wacproduct 	= $woo_auction->product;
			$auction_id 	= $product->get_id();
			$exist_auctions = $wacproduct->get_max_bid($auction_id);
			$highestBid 	= (isset($exist_auctions->bid))? $exist_auctions->bid : 0;
			$listingPrice 	= $product->get_reserve_price();
			
			//if highest bid is above listing price
			if($highestBid != 0 && ($highestBid < $listingPrice) || ($highestBid > $listingPrice)){
				return 'Available';
			}
			//if auction closed and paid successfully
			elseif($product->is_paid()){
				return 'Available';
			}
		}
		return 'Not available';
	}
	
	//show billing status
	//if auction is closed, paid and has reserve price
	function is_billing_open($product){
		if($product->is_closed() && !$product->is_paid() && $product->has_reserve_price()){
			global $woo_auction;
			$bids = YITH_Auctions()->bids;
			$wacproduct 	= $woo_auction->product;
			$auction_id 	= $product->get_id();
			$exist_auctions = $wacproduct->get_max_bid($auction_id);
			$highestBid 	= (isset($exist_auctions->bid))? $exist_auctions->bid : 0;
			$listingPrice 	= $product->get_reserve_price();
			//if highest bid is above listing price
			if($highestBid != 0 && $highestBid > $listingPrice){
				return true;
			}
		}
		return false;
	}
	
	//get user meta
	function umeta($uid,$key,$default='-'){
		$meta = get_user_meta($uid,$key,true);
		return (!empty($meta))? $meta : $default;
	}
	
	//save vendor profile
	function save_vendor_profile(){
		global $WCMp;
		// echo '<pre>'.print_r($_POST,true).'</pre>'; die;
		
		$errors = array();
		$vendor_id = get_current_vendor_id();
		parse_str($_POST['posted'], $parsedData);
		// print_r($parsedData);
		$vendorData = $parsedData['vendor'];
		$email = $vendorData['email'];
		
		$vendor = get_wcmp_vendor($vendor_id);
		$currEmail = $vendor->user_data->user_email;
		
		if(empty($vendorData['_vendor_company'])){
			$errors[] = __('Company Name required!','woo-auction');
		}

		if(empty($vendorData['email'])){
			$errors[] = __('Email is required!','woo-auction');
		}
		if($currEmail != $email && (email_exists($email) || !is_email($email))){
			$errors[] = __('Try with another email!','woo-auction');
		}
		
		//password check
		$vnpass = trim($vendorData['_vendor_password']);
		$vnpass_new = trim($vendorData['_vendor_newpassword']);

		if (!empty($vnpass) && wp_check_password( $vnpass, $vendor->user_data->user_pass, $vendor->ID)) {
			
			if(empty($vnpass_new)){
				$errors[] = __('Your new password can\'t be empty!','woo-auction');
			}
			elseif(strlen($vnpass_new) <= 6){
				$errors[] = __('Enter password min. 6 characters!','woo-auction');
			}
		}
		elseif(!empty($vnpass)) {
			$errors[] = __('Your current password incorrect!','woo-auction');
		}

		//if vendor image empty then set placeholder image
		if(empty($vendorData['_vendor_image'])){
			$vendorData['_vendor_image'] = wac_default_gravatar();
		}
		
		$update_password = false;
		$password_fields = ['_vendor_password','_vendor_newpassword'];
		if(empty($errors)){
			$successMsgs = array();
			$vendorfields = $vendorData;
			foreach($vendorfields as $vendorfieldname => $vendorfieldvalue){
				if($vendorfieldname == 'email'){
					wp_update_user( array( 'ID' => $vendor_id, 'user_email' => $vendorfieldvalue ) );
				}
				elseif(in_array($vendorfieldname, $password_fields)){
					//update password
					if($vendorfieldname == '_vendor_newpassword'){
						$update_password = $vendorfieldvalue;
						wp_update_user( array( 'ID' => $vendor_id, 'user_pass' => $vendorfieldvalue ) );
						$successMsgs[] = __('Password updated!','woo-auction');
					}
					continue;
				}
				else{
					update_user_meta($vendor_id, $vendorfieldname, $vendorfieldvalue);
				}
			}
			
			$successMsgs[] = __('Profile Saved Successfully!','woo-auction');
			$return = ['success'=>true,'msg'=>$successMsgs];
		}
		else{
			$return = ['success'=>false,'msg'=>$errors];
		}
		
		wp_send_json($return);
		die;
	}
	
	//get fieldvalue of usermeta.
	function fieldvalue($key){
		$vid = get_current_vendor_id();
		if(isset($_POST[$key])){
			return $_POST[$key];
		}
		else{
			return $this->umeta($vid,$key,'');
		}
	}
	
	//get auction link
	function status_hlink($auction){
		switch($auction['status']){
			case 'live':
			case 'closed':
			case 'approved':
				return "<a href='{$auction['plink']}'>{$this->_get_status($auction['status'])}</a>";
			break;
			default:
				return $this->_get_status($auction['status']);
			break;
		}
	}
}