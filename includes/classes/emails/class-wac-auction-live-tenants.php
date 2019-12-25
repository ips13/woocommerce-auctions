<?php
/**
 *
 * Send Email to All Tenants when auction status changed to live.
 */

    /**
     * Class Wac_Auction_Live_Tenants
     *
     */
class Wac_Auction_Live_Tenants extends WC_Email {

	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'wac_auction_live_tenants';

		// this is the title in WooCommerce Email settings
		$this->title = __( '​Auction - Live for Tenants','woo-auction' );

		$this->customer_email = true;

		// this is the description in WooCommerce email settings
		$this->description = __( 'Email to All Tenants when auction status changed to live.','woo-auction' );

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = __( 'Auction Live for Tenants','woo-auction' );

		$this->subject = __( 'Your {sitename} subscribed {auction_title} Auction is Online','woo-auction' );

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html = 'emails/auction-live-tenants.php';
		
		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

	}

	//send mails when auction is live to all subscribed tenants
	public function trigger( $product_id, $direct=false ) {
		
		//Check is email enable or not
		if ( !$this->is_enabled() ) {
			return;
		}

		wac_log('Live for Tenants: '.$product_id);
		
		$product 		= wc_get_product($product_id);
		$url_product 	= get_permalink($product_id);
		$auction_start 	= yit_get_prop($product, '_yith_auction_for', true);
		$startsin 		= wac_datetimedifference('now',date('Y-m-d H:i:s',$auction_start));
		$x_time 		= "{$startsin->d}d {$startsin->h}h {$startsin->i}m {$startsin->s}s";

		$users = $product->get_watchlist();
		if ( $users ) {
			foreach ($users as $user_email) {
				$user = get_user_by('email',$user_email);
				$this->object = array(
					'user_email'    => $user_email,
					'account_name'	=> wac_vendor_name($user),
					'user_name'     => $user->user_login,
					'product_id'    => $product->get_id(),
					'product_name'  => $product->get_title(),
					'x_time'		=> $x_time,
					'product'       => $product,
					'url_product'   => $url_product,
				);
				
				//set placeholder text
				$this->placeholders['{sitename}']   	= get_bloginfo( 'name' );
				$this->placeholders['{account_name}']   = $this->object['account_name'];
				$this->placeholders['{auction_title}']  = $this->object['product_name'];
				$this->placeholders['{x_time}'] 		= $this->object['x_time'];
				$this->placeholders['{weblink}'] 		= $this->object['url_product'];
				
				if($direct)
					return;

				//set recipient
				$this->recipient = $this->object['user_email'];
				
				$this->send( $this->get_recipient(),
							 $this->get_subject(),
							 $this->get_content(),
							 $this->get_headers(),
							 $this->get_attachments() );

			}
		}
		
	}


	public function get_content_html() {
		$template = wc_get_template_html( 
			$this->template_html, array(
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this
			),
			'',
			WOO_AUCTION_TEMPLATEPATH );
			
		return $this->format_string($template);
	}


	public function get_content_plain() {
		$template = wc_get_template_html( 
			$this->template_plain, array(
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this
			),
			'',
			WOO_AUCTION_TEMPLATEPATH );
			
		return $this->format_string($template);
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable','woo-auction' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification','woo-auction' ),
				'default' => 'yes'
			),

			'subject'    => array(
				'title'       => __( 'Subject','woo-auction' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Default: <code>%s</code>.','woo-auction' ), $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => __( 'Email Heading','woo-auction' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Default: <code>%s</code>.','woo-auction' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => __( 'Email type','woo-auction' ),
				'type'        => 'select',
				'description' => __( 'Choose the email format to send.','woo-auction' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true
			)
		);
	}


}
return new Wac_Auction_Live_Tenants();