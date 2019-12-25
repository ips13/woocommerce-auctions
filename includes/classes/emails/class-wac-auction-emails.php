<?php

class Woo_Auction_Emails{
	
	public function __construct(){
		//locate email template and display
		add_filter('woocommerce_email_classes', array($this, 'register_email_classes'),13);
		add_filter('woocommerce_locate_core_template', array($this, 'locate_core_template'), 10, 3);
		
		add_action('init',array($this,'display_email_content'));
		add_filter('site_transient_update_plugins', array($this,'disable_plugin_updates') );
		
		//add email template header & footer
		add_action('woocommerce_email_header',array($this,'add_woocommerce_email_heading'));
		add_action('woocommerce_email_footer',array($this,'add_woocommerce_email_footer'));
		
		// Save data product
		add_action('woocommerce_process_product_meta_auction', array($this, 'save_auction_data'),13);
		//set new cron for auction live.
		add_action('wac_send_emails_live_auction_hook',array($this,'cron_emails_live_auctions'));
		//trigger when auction become live
		add_action('wac_send_emails_live_auction',array($this,'trigger_auction_live_emails'));
		
		
		//trigger emails
		if (is_admin()) {
            add_action('transition_post_status', array(&$this, 'on_all_status_transitions'), 10, 3);
        }
		
		//trigger emails when auction closed.
		add_action( 'yith_wcact_send_emails_auction', array( $this, 'trigger' ), 13, 1 );
		// add_action( 'init', array( $this, 'trigger2' ));
	}
	
	//disable plugin updates
	function disable_plugin_updates( $value ) {
		if(isset($value->response['email-templates/mailtpl.php'])){
			unset( $value->response['email-templates/mailtpl.php'] );
		}
		return $value;
	}
	
	//load email classes
	function load_eclass($class){
		global $woo_auction;
		return $woo_auction->load_class($class,'emails/');
	}
	
	//display email content on front
	function display_email_content(){
		$this->load_eclass('display-email');
		new Woo_Auction_Display_Emails('auciton',1);
	}
	
	//register woocommerce emails
	function register_email_classes($email_classes){
		$email_classes['WAC_Auction_Submitted'] = $this->load_eclass('auction-submitted');
		$email_classes['WAC_Auction_Approved']  = $this->load_eclass('auction-approved');
		$email_classes['WAC_Auction_Closed']  	= $this->load_eclass('auction-closed');
		$email_classes['WAC_Auction_Closed_Tenants']  	= $this->load_eclass('auction-closed-tenants');
		$email_classes['WAC_Auction_Live_Vendor']  		= $this->load_eclass('auction-live-vendor');
		$email_classes['WAC_Auction_Live_Tenants']  	= $this->load_eclass('auction-live-tenants');
		
		//remove already registered emails
		$removeMails = ['WC_Email_Customer_Refunded_Order','WC_Email_New_Order','WC_Email_Cancelled_Order','WC_Email_Failed_Order','WC_Email_Customer_On_Hold_Order','WC_Email_Customer_Processing_Order','WC_Email_Customer_Completed_Order','WC_Email_Customer_Refunded_Order','WC_Email_Customer_Invoice','WC_Email_Customer_Note','WC_Email_Vendor_New_Order', 'WC_Email_Notify_Shipped','WC_Email_Vendor_Commission_Transactions', 'WC_Email_Vendor_Direct_Bank', 'WC_Email_Admin_Widthdrawal_Request','YITH_WCACT_Email_Better_Bid','YITH_WCACT_Email_End_Auction'];
		foreach($removeMails as $removeml){
			if(isset($email_classes[$removeml]))
				unset($email_classes[$removeml]);
		}
		
		return $email_classes;
	}
	
	//locate templates for email
	function locate_core_template($core_file, $template, $template_base){
		$custom_template = array(
			//HTML Email
			'emails/auction-submitted.php',
			'emails/auction-approved.php',
			'emails/auction-closed.php',
			'emails/auction-live-tenants.php',
			'emails/auction-live-vendor.php',
			'emails/auction-closed-tenants.php',
		);
		
		if (in_array($template, $custom_template)) {
			$core_file = WOO_AUCTION_TEMPLATEPATH . $template;
		}

		return $core_file;
	}
	
	//set email title for woocommerce emails
	function add_woocommerce_email_heading($email_heading){
		$settings = Mailtpl::opts();
		$header_content_h1 = "
			color: ".$settings['header_text_color'].";
			margin: -20px -20px 0px;
			padding: 15px 10px;
			display:block;
			font-family:Arial;
			font-size: ".$settings['header_text_size']."px;
			font-weight:bold;
			text-align:".$settings['header_aligment'].";
			line-height: 150%;
			background-color: ".$settings['header_bg'].";
		";
		$header_content_h1_a = "
			color: ".$settings['header_text_color'].";
			text-decoration: none;
		";
		$defaultTitle = (!empty($settings['header_logo_text']))? $settings['header_logo_text'] : 'Title';
		?>
		<h1 style="<?php echo $header_content_h1; ?>" id="logo">
			<a style="<?php echo $header_content_h1_a;?>" title="">
			<?php
				echo (!empty($email_heading))? $email_heading : $defaultTitle;
			?>
			</a>
		</h1>
		<?php
	}
	
	//add content in footer email template woocommerce
	function add_woocommerce_email_footer(){
		?>
			<br>
			<p style="margin-top:0px;">Happy auctioning,</p>
			<p>Michiel & Marnix Founders</p>
			<p>TheNextBid</p>
		<?php
	}
	
	//triggerr approved auction email
	function on_all_status_transitions($new_status, $old_status, $post) {
		if (current_user_can('administrator') && $new_status != $old_status && $post->post_status == 'publish') {
            if (isset($_POST['choose_vendor']) && !empty($_POST['choose_vendor'])) {
                $term = get_term($_POST['choose_vendor'], 'dc_vendor_shop');
                if ($term) {
					//trigger approved auction
                    $email_vendor = WC()->mailer()->emails['WAC_Auction_Approved'];
                    $email_vendor->trigger($post->ID);
                }
            }
        }
	}
	
	/**
     * Save the data input into the auction product box
     *
	 */
	function save_auction_data($post_id){
		$product = wc_get_product($post_id);
		$product_type = empty($_POST['product-type']) ? 'simple' : sanitize_title(stripslashes($_POST['product-type']));

		if ('auction' == $product_type) {
			
			if (isset($_POST['_yith_auction_for'])) {
				//Clear all Product CronJob
				if (wp_next_scheduled('wac_send_emails_live_auction', array($post_id))) {
					wp_clear_scheduled_hook('wac_send_emails_live_auction', array($post_id));
				}
				//Create the CronJob //when the auction is about to end
				do_action('wac_send_emails_live_auction_hook', $post_id);
			}
				
		}
	}
	
	//cron live auctions
	function cron_emails_live_auctions($product_id){
		$product = wc_get_product($product_id);
		$time 	 = yit_get_prop($product, '_yith_auction_for',true);
		wp_schedule_single_event( $time, 'wac_send_emails_live_auction', array( $product_id ) );
	}
	
	//trigger emails when auction become live.
	function trigger_auction_live_emails($product_id){
		//trigger auction is live to Vendor of this auction
		$email_vendor = WC()->mailer()->emails['WAC_Auction_Live_Vendor'];
		$email_vendor->trigger($product_id);
		
		//trigger auction is live to all Subscribed Tenants
		$email_tenants = WC()->mailer()->emails['WAC_Auction_Live_Tenants'];
		$email_tenants->trigger($product_id);
	}
	
	//trigger emails when auction closed
	function trigger($product_id){
		
		//send email to auction owner
		$email_vendor = WC()->mailer()->emails['WAC_Auction_Closed'];
		$email_vendor->trigger($product_id);
		
		//send email to Top 5 bidders except winner.
		$email_tenants = WC()->mailer()->emails['WAC_Auction_Closed_Tenants'];
		$email_tenants->trigger($product_id);
	}
	
	//trigger emails when auction closed
	function trigger2(){
		/* global $woo_auction;
		$product 		= wc_get_product(794);
		var_dump($woo_auction->vendor_profile->is_billing_open($product));
		die; */
		/* $email_vendor = WC()->mailer()->emails['WAC_Auction_Closed_Tenants'];
		$email_vendor->trigger(794); */
	}
}