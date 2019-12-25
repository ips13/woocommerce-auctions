<?php

/**
 * Overwrite default actions in Plugins
 */
class Woo_Auction_Actions_Overwrite{
	
	public $product_columns = 4;
	
	function __construct(){
		//display products per row
		$this->product_columns = 2;
		
		add_action( 'init', array($this,'add_remove_actions'), 13 );
		add_action( 'init', array($this,'add_vendor_caps'));
		
		add_action( 'wp_loaded', array($this,'runatlast'),99);
		
		add_filter('loop_shop_columns', array($this,'shop_loop_columns'));
		// add_filter('yith_wcact_shortcode_get_catalog_ordering_args',array($this,'auction_listing_ordering'), 13);

		add_action( 'woocommerce_before_shop_loop', array($this,'productloop_start'), 30 );
		add_action( 'woocommerce_after_shop_loop', array($this,'productloop_end'), 30 );
		
		add_action( 'template_redirect', array( $this, 'remove_comment_template' ),999 );
		add_action( 'template_redirect', array( $this, 'wc_redirect_to_ultimate_member'));
	}

	//add remove actions
	function add_remove_actions(){
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_anonymous_object_filter( 'woocommerce_after_shop_loop_item', 'YITH_Auction_Frontend_Premium', 'auction_end_start');
		remove_anonymous_object_filter( 'yith_wcact_after_add_to_cart_form', 'YITH_Auction_Frontend_Premium', 'add_watch_list_button');
		
		add_filter( 'yith_wcact_before_add_to_cart', array($this,'is_vendorview'),13,2);
		add_filter( 'yith_wcact_auction_price_html', array($this,'yith_change_product_price_display'),13,3);
		add_filter( 'woocommerce_product_tabs', array($this,'remove_product_tabs'),13 );
		add_filter( 'wp_nav_menu_items', array($this,'append_search_icon'), 10, 2 );
		add_filter( 'wc_get_template', array($this,'woo_overwrite_tempalte'), 10, 5 );
		add_filter( 'um_view_field_value_radio', array($this,'translate_um_field_value'), 10, 2 );
		
		add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );
		add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 10 );
		add_action( 'woocommerce_after_shop_loop_item', array($this, 'auction_end_start'), 8 );
		add_action( 'woocommerce_before_shop_loop_item_title', array($this,'woocommerce_loop_image_wrapper_start'), 1 );
		add_action( 'woocommerce_before_shop_loop_item_title', array($this,'woocommerce_loop_image_wrapper_end'), 11 );
		add_action( 'woocommerce_after_single_product_summary',array($this, 'auction_key_information'));
		
		add_action('yith_wcact_after_add_to_cart_form',array($this,'add_watch_list_button'));
		add_action('woocommerce_output_related_products_args',array($this,'related_products_args'));
		
		add_action('woocommerce_after_add_to_cart_form', array($this,'showvendor_message_onview'),13,2);
		add_action('woocommerce_shortcode_live_auctions_loop_no_results', array($this,'live_auctionloop_no_result'),13);
		
		
		remove_anonymous_object_filter( 'yith_wcact_auction_end', 'YITH_Auction_Frontend','auction_end');
		add_action('yith_wcact_auction_end', array($this,'wac_auction_end'),13);
	}

	//reorder auction list (Live, Future, Closed)
	function auction_listing_ordering($args){
		$args = array(
			'orderby'	=> 'meta_value',
			'order'		=> 'DESC',
			'meta_key'	=> '_yith_auction_to'
		);
        return $args;
	}
	
	//hide bid form for vendor viewing on auction
	function is_vendorview($status,$product){
		// print_r($product);
		if($product->is_type('auction') && wac_is_vendor()){
			return false;
		}
		return true;
	}
	
	//add new vendor capabilities
	function add_vendor_caps() {
		// gets the author role
		$role = get_role( 'dc_vendor' );
		if(is_object($role) && !isset($role->capabilities['manage_auctions'])){
			$role->add_cap('manage_auctions');
		}
	}
	
	//append search icon in menu
	function append_search_icon( $items, $args ) {
		if ($args->theme_location == 'primary') {
			$items .= '<li class="menu-item fa fa-search display-search-bar"></li>';
		}
		return $items;
	}
	
	//override woocommerce templates with auction plugin
	function woo_overwrite_tempalte($located, $template_name, $args, $template_path, $default_path){
		global $woo_auction;
		$wootemplate_path = $woo_auction->template_path();
		switch($template_name){
			case 'max-bidder.php':
				$located = $wootemplate_path.'/single-product/max-bidder.php';
			break;
			case 'list-bids.php':
				$located = $wootemplate_path.'/single-product/list-bids.php';
			break;
			case 'auction-timeleft.php':
				$located = $wootemplate_path.'/single-product/auction-timeleft.php';
			break;
			case 'single-product/add-to-cart/auction.php':
				$located = $wootemplate_path.'/single-product/add-to-cart/auction.php';
			break;
		}
		return $located;
	}

	//translate ultimate member profile value
	function translate_um_field_value($res,$data){
		//translate gender value
		if($data['metakey'] == 'gender'){
			return __( $res,'ultimate-member');
		}
		return $res;
	}
	
	//auction no product
	function live_auctionloop_no_result(){
		echo "<h4 class='live-noresult text-center'>".__('There are no live auctions at the moment','woo-auction')."</h4>";
	}
	
	//redirect woocommerce pages to ultimate member pages
	function wc_redirect_to_ultimate_member() {
		
		//if current page is account, cart or checkout
		if( is_cart() || is_checkout() ){
			
			//if vendor is logged in then redirect to Dashboard page
			if(wac_is_vendor()){
				$dashboardid = wac_setting_field('vpage_dashboard');
				$redirecturl = get_permalink($dashboardid);
			}
			else{
				$userpageid  = um_get_option('core_user');
				$redirecturl = get_permalink($userpageid);
			}
			wp_redirect($redirecturl);
			exit();
		}
	}
	
	//show products per row
	function shop_loop_columns(){
		return $this->product_columns;
	}
	
	//add column class on product loop start
	function productloop_start(){
		$columns = $this->product_columns;
		echo '<div class="woocommerce columns-' . $columns . '">';
	}
	
	//end column class wrap on loop
	function productloop_end(){
		echo '</div>';
	}
	
	//add wrapper above image on product loop
	function woocommerce_loop_image_wrapper_start(){
		echo '<div class="img-wrapper">';
	}
	
	//end wrapper above image on product loop
	function woocommerce_loop_image_wrapper_end(){
		echo '</div>';
	}
	
	//product price display only simple
	function yith_change_product_price_display($price_html, $product, $price){
		return $price;
	}
	
	//related products per page
	function related_products_args($args){
		$args['columns'] = 4;
		return $args;
	}
	
	//display auction date time on listing
	public function auction_end_start(){
		
		global $product;
		$product = apply_filters('yith_wcact_get_auction_product',$product);

		if ('auction' == $product->get_type()) {

			$auction_start = yit_get_prop($product, '_yith_auction_for', true);
			$auction_end = yit_get_prop($product, '_yith_auction_to', true);
			$date = strtotime('now');
			
			if ($date < $auction_start) {
				?>
				<div id="auction_end_start">
					<?php echo sprintf(__('Start:'),'woo-auction'); ?>
					<p class="wac_date_auction time-auction">
						<?php echo date('d M H:i', $auction_start); ?>
					</p>
				</div>
				<?php
			} else {
				if (!empty($auction_end) && !$product->is_closed() && !$product->is_closed_for_buy_now()) {
					
					$endsin = wac_datetimedifference('now',date('Y-m-d H:i:s',$auction_end));
					?>
					<div id="auction_end_start">
						<?php echo sprintf(__('Ends in:','woo-auction')); ?>
						<p class="wac_date_auction time-auction yith-date-auct-<?php echo $product->get_id(); ?>" 
							data-finish-time="<?php echo $auction_end; ?>"
							data-yith-product="<?php echo $product->get_id(); ?>"
						>
							<span class="yith-days"><?php echo $endsin->d; ?>d</span>
							<span class="yith-hours"><?php echo $endsin->h; ?>h</span>
							<span class="yith-mins"><?php echo $endsin->i; ?>m</span>
							<span class="yith-secs"><?php echo $endsin->s; ?>s</span>
							<?php //date(wc_date_format() . ' ' . wc_time_format(), $auction_end); ?>
						</p>
					</div>
					<?php
				}
				else{
					?>
					<div id="auction_end_start">
						<?php echo sprintf(__('Auction Closed','woo-auction')); ?>
					</div>
					<?php
				}
			}

		}
	}
	
	//display key information on product view page
	function auction_key_information(){
		global $product, $woo_auction;
		$prdctID = $product->get_id();
		$wacStrings = $woo_auction->wacStrings;
		?>
			<div class="keyinfo-section">
				<h2 class="keyinfo-title"><?php echo __('Key information','woo-auction'); ?></h2>
				<ul class="keyinfo-lists">
					<?php
					$vendor 	 = get_wcmp_product_vendors($prdctID);
					$vendordata  = $vendor->user_data;
					$vendorCmpny = wac_vendor_company($vendordata);
					$auctionData = array(
						'owner'		=> $vendorCmpny,
						'deposit'	=> get_post_meta($prdctID,'_auction_deposit',true),
						'rental'	=> get_post_meta($prdctID,'_auction_rental_period',true),
						'tenant'	=> get_post_meta($prdctID,'_auction_tenant_req',true),
						'availfrom'	=> get_post_meta($prdctID,'_auction_available_from',true),
						'listprice'	=> get_post_meta($prdctID,'_yith_auction_reserve_price',true),
						'minprice'	=> get_post_meta($prdctID,'_auction_minprice_status',true),
						'comment'	=> get_post_meta($prdctID,'_auction_comment',true),
						'additional_info'	=> get_post_meta($prdctID,'_auction_additional_info',true)
					);
					
					$minpriceStatus = (!empty($auctionData['minprice']))? __('Yes','woo-auction') : 'No';
					// print_r($auctionData);
					
					//all selected cities
					$product_cats = get_the_terms ($prdctID,'product_cat');
					$auctionCities = array();
					if(isset($product_cats) && is_array($product_cats)){
						foreach($product_cats as $product_cat){
							$auctionCities[] = $product_cat->name;
						}
					}
					
					$availfrom = (!empty($auctionData))? date('d-m-Y',strtotime($auctionData['availfrom'])) : '';
					
					$keyinfos = [
						'City' => ['val'=>implode(',',$auctionCities)],
						'Listing Price' => ['val'=>wc_price($auctionData['listprice'])],
						'Minimum Price'		=> ['val'=>$minpriceStatus, 'info'=>$wacStrings['pd_minpriceinfo']],
						'Deposit' 		=> ['val'=>wc_price($auctionData['deposit'])],
						'Available from' => ['val'=>$availfrom],
						'Rental Period' => ['val'=> __($auctionData['rental'],'woo-auction')],
						'Listed By' 	=> ['val'=> $auctionData['owner']],
						'ListingLink' 	=> ['val'=> wac_create_link(wac_auctionlink($prdctID))],
						'Additional information' => ['val'=> nl2br($auctionData['additional_info'])],
						'Tenant Requirements' => ['val'=> nl2br($auctionData['tenant'])],
					];

					//if min price is empty then unset and don't display
					if(empty($keyinfos['Minimum Price'])){
						unset($keyinfos['Minimum Price']);
					}

					foreach($keyinfos as $TkeyInfo => $VkeyInfo):
						?>
							<li class="keyinfo-list kil-<?php echo sanitize_title($TkeyInfo); ?>">
								
								<?php if($TkeyInfo == 'ListingLink' && !empty($VkeyInfo)): ?>
									<span class="keyinfo-list-text">
										<a href="<?php echo $VkeyInfo; ?>" class="externl-lnk" target="_blank">
											<?php echo __('Link to original listing','woo-auction'); ?>
										</a>
									</span>
								<?php else: ?>

									<?php 
									//skip if Minimum Price is empty
									if($TkeyInfo == 'Minimum Price' && empty($auctionData['minprice'])) 
										continue; 
									?>

									<span class="keyinfo-list-title"><?php echo __($TkeyInfo,'woo-auction'); ?>:</span>
									<span class="keyinfo-list-text">
										<?php echo $VkeyInfo['val']; ?>
										<?php
											if(isset($VkeyInfo['info']) && !empty($VkeyInfo['info'])){
												echo wac_ihelp($VkeyInfo['info']);
											}
										?>
									</span>
								<?php endif; ?>
							</li>
						<?php
					endforeach;
					?>
				</ul>
			</div>
		<?php
	}
	
	//remove comment template
	public function remove_comment_template() {
		
		//remove comments array
		add_filter( 'comments_array', '__return_false', 20, 2 );
		add_filter( 'comments_open', '__return_false', 20, 2 );
		add_filter( 'pings_open','__return_false', 20, 2 );
		
		// Kill the comments template.
		add_filter( 'comments_template', array( $this, 'dummy_comments_template' ), 200 );
		// Remove comment-reply script for themes that include it indiscriminately
		wp_deregister_script( 'comment-reply' );
		// feed_links_extra inserts a comments RSS link
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}
	
	//empty comment template
	public function dummy_comments_template() {
		return WOO_AUCTION_PATH.'/templates/disabled-comments.php';
	}
	
	//show different view on vendor user
	function showvendor_message_onview(){
		if(wac_is_vendor()){
			echo wac_get_template('single-product/add-to-cart/auction-vendor.php');
		}
	}
	
	//run hooks at last
	function runatlast(){
		
		//remove edit buttons
		if(wac_is_vendor()){
			remove_anonymous_object_filter( 'woocommerce_before_shop_loop_item', 'WCMp_Frontend_Product_Manager_Frontend', 'forntend_product_edit');
			remove_anonymous_object_filter( 'woocommerce_before_single_product_summary', 'WCMp_Frontend_Product_Manager_Frontend', 'forntend_product_edit');
		}
	}
	
	//remove product tabs
	function remove_product_tabs($tabs){
		unset($tabs['vendor']);
		unset($tabs['policies']);
		unset($tabs['description']);
		unset($tabs['singleproductmultivendor']);
		return $tabs;
	}
	
	//auction messages
	function wac_auction_end($product){
		
		global $woo_auction;
		$auction_id = $product->get_id();
		$wacproduct = $woo_auction->product;
		$max_bid 	= $wacproduct->get_max_bid($auction_id);

		?>
		<div id="yith_auction_end_product_page">
			<p><?php echo __('Auction closed','woo-auction') ?></p>
		</div>

		<div class="bidinfo">
			<?php
			if(is_user_logged_in()){
				$userid = get_current_user_id();
				if(!wac_is_vendor($userid)){
					echo '<p class="cruserid mb-10">'. __("You have <b>UserID {$userid}</b> for anonymous bidding.",'woo-auction'). '</p>';
				}
			}
			?>
		</div>
		
		<?php
		//if vendor is logged in then display message
		if(wac_only_current_vendor_view($auction_id)){
			
			$total_bids = $woo_auction->product->total_bids($auction_id);
			$contact_us = get_permalink(46);
			$auctionDetailLink = $woo_auction->vendor_profile->auctiondetail_link($auction_id);
			
			//if no bid
			if($total_bids <= 0){
				?>
				<div id="yith_auction_end_product_page">
					<p class="highlighted"><?php echo __('No bids received.','woo-auction'); ?></p>
					<p><?php echo __('No bids have been received for your auction. We will contact you to discuss potential causes and to find a solution to solve this for your future auctions. Can\'t wait','woo-auction'); ?> <a href="<?php echo $contact_us; ?>"><?php echo __('Contact us','woo-auction'); ?></a></p>
				</div>
				<?php
			}
			else{
				?>
				<div id="yith_auction_end_product_page">
					<p class="highlighted"><?php echo __('Closed, but not done.','woo-auction'); ?></p>
					<p><?php echo __('The auction is closed, please check your','woo-auction'); ?> <a href="<?php echo $auctionDetailLink; ?>"><?php echo __('dashboard','woo-auction'); ?></a> <?php echo __('for the next steps which could be a payment or directly contacting the bidders.','woo-auction'); ?></p>
				</div>
				<?php
			}
		}
		elseif ( $max_bid ) { 
			//it will display message to winner of auction, 2n-5highest bidders.

			//if tenant is logged in
			$current_user = wp_get_current_user();
			$cuId = $current_user->ID;
			
			//if current user is tenant
			if(wac_is_tenant($cuId)){
				
				//get last 5 bidders
				$last_bidders 	= $wacproduct->last_bidders($auction_id);
				$user_has_bids 	= $wacproduct->bids_by_user($auction_id,$cuId);
				
				if ( $cuId == $max_bid->user_id){
					?>
					<div id="Congratulations">
						<p class="highlighted"><?php echo __('Yes!','woo-auction'); ?></p>
						<p><?php echo __('You are the highest bidder and are therefore considered first for granting your dream home! The landlord will contact you as soon as possible.','woo-auction'); ?></p>
					</div>
					<?php
				}
				//2nd to 5 bidder display message
				elseif(in_array($cuId,$last_bidders)){ 
					?>
					<div id="yith_auction_end_product_page">
						<p class="highlighted"><?php echo __('Almost the highest bidder!','woo-auction'); ?></p>
						<p><?php echo __('You are one of the highest bidders. The landlord will consider you after approaching the higher bidder(s) first.','woo-auction'); ?></p>
					</div>
					<?php
				}
				//if current user bid in last bidders
				elseif(!empty($user_has_bids)){
					?>
					<div id="yith_auction_end_product_page">
						<p class="highlighted"><?php echo __('Sorry, try again.','woo-auction'); ?></p>
						<p><?php echo __('You are not one of the five highest bidders. The landlord will only consider your bid when the other bidders are not accepted. We advise you try another auction.','woo-auction'); ?></p>
					</div>
					<?php
				}
				else{
					?>
					<!--div id="yith_auction_end_product_page">
						<p class="highlighted">You haven't placed bid.</p>
					</div-->
					<?php
				}
			
			}
			else{
				//not a tenant or vendor
			}
		}
		else {
			// echo 'No max bid!';
		}
		
	}
	
	//add subscription form in auction detail page
	function add_watch_list_button($product) {
		
		//add listing price
		if($product->has_reserve_price()){
			global $woo_auction;
			$userid = get_current_user_id();
			?>
				<script type="text/javascript">
					var startprice 	 = <?php echo $product->get_start_price(); ?>;
					var listingprice = <?php echo $product->get_reserve_price(); ?>;
					<?php if($userid):
						$user_totalbids = $woo_auction->product->user_totalbids($userid,$product); ?>
						var allowedbids  = <?php echo wac_allowed_bids(); ?>;
						var bidplaced    = <?php echo $user_totalbids; ?>;
					<?php endif; ?>
					
				</script>
			<?php
		}
		
		//display subscribe form
		$this->subscribe_form($product,'end');

	}
	
	//display subscribe form for auction
	function subscribe_form($product,$type='start'){
		
		//if user is logged in not as vendor then display User id.
		?>
		<div class="bidinfo">
			<?php
			if(is_user_logged_in()){
				$userid = get_current_user_id();
				if(!wac_is_vendor($userid)){
					echo '<p class="cruserid mb-10">'. __("You have <b>UserID {$userid}</b> for anonymous bidding.",'woo-auction'). '</p>';
				}
			}
			$howtobidID = wac_setting_field('howtobid');
			?>
			<a href="<?php echo get_permalink($howtobidID); ?>" class="howtobid fancybox.iframe mb-5"><?php echo __('How to Bid?','woo-auction'); ?></a>
			<p class="mb-10"><?php echo __('We reset the auction timer every time a bid is placed in the last 10 minutes, so no stress!','woo-auction'); ?></p>
		</div>
		<?php
		if(!is_user_logged_in()){
			echo '<p class="mb-10">'.__('Register FREE to bid on this fantastic rental home.','woo-auction').'</p>';
		}
		
		if('yes' == get_option('yith_wcact_settings_tab_auction_allow_subscribe')) {
			$display_watchlist = true;
			$current_user_id = get_current_user_id();
			
			//if auction is closed then don't display subscribe form
			if($product->is_closed()){
				$display_watchlist = false;
			}
			
			if( $current_user_id ) {
				$customer = get_userdata($current_user_id);
				$cremail  = $customer->data->user_email;
				if($product->is_in_watchlist($customer->data->user_email)) {
					$display_watchlist = false;
				}
			}
			
			if ( apply_filters('yith_wcact_display_watchlist',$display_watchlist)) {
				?>
				<div class="yith-wcact-watchlist-button">
					<form class="yith-wcact-watchlist" method="post" enctype='multipart/form-data'>
						<div class="yith-wcact-watchlist-button">
							<input type="hidden" name="yith-wcact-auction-id" value="<?php echo esc_attr( $product->get_id() );?>"/>
							
							<?php if($type == 'start'): ?>
								<p class="mb-10"><?php echo __('Notify me when this auction is starts','woo-auction'); ?></p>
							<?php else: ?>
								<p class="mb-10"><?php echo __('Notify me when this auction is about to end','woo-auction'); ?></p>
							<?php endif; ?>
							
							<div class="yith-subscribe-form-wrap">
								<input type="email" name="yith-wcact-watchlist-input-email" id="yith-wcact-watchlist-email" value="<?php echo ( $current_user_id ) ? $customer->data->user_email: ''; ?>"
								   placeholder="<?php echo __('Your email','woo-auction') ?>">
								<input type="submit" class="button button-primary yith-wcact-watchlist"
								   value="<?php echo __('Subscribe','woo-auction'); ?>">
							</div>
						</div>
					</form>
				</div>
				<?php
			}
			else{
				if(isset($cremail)){
					sprintf(__('Your email "%s" is already subscribed','woo-auction'),$cremail);
				}
			}
		}
	}
	
}