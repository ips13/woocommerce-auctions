<?php

/**
 *
 * Send email to agent when auction submitted
 */

    /**
     * Class Wac_Auction_Submitted
     *
     */
class Wac_Auction_Submitted extends WC_Email {

	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'wac_auction_submitted';

		// this is the title in WooCommerce Email settings
		$this->title = __( '​Auction - Submitted','woo-auction' );

		$this->customer_email = true;

		// this is the description in WooCommerce email settings
		$this->description = __( 'On auction submitted by Landlord/Agent, automation email to Agent','woo-auction' );

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = __( 'You have submitted an auction request','woo-auction' );

		$this->subject = __( 'Your TheNextBid {auction_title} Auction Request Submitted','woo-auction' );

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html = 'emails/auction-submitted.php';

		// Trigger on new paid orders
		add_action( 'wac_auction_submitted', array( $this, 'trigger' ), 10 );

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

		// $this->trigger(794,true);
	}


	public function trigger( $product_id, $direct=false ) {
		
		//Check is email enable or not
		if ( !$this->is_enabled() ) {
			return;
		}
	
		$product 		= wc_get_product($product_id);
		$vendor_data 	= get_wcmp_product_vendors($product_id);
		$user			= $vendor_data->user_data;
		$url_product 	= get_permalink($product_id);
		
		$this->object = array(
			'user_email'    => $user->data->user_email,
			'account_name'	=> wac_vendor_name($user),
			'user_name'     => $user->user_login,
			'product_id'    => $product->get_id(),
			'product_name'  => $product->get_title(),
			'product'       => $product,
			'url_product'   => $url_product,
		);
		
		//set placeholder text
		$this->placeholders['{account_name}']    = $this->object['account_name'];
		$this->placeholders['{auction_title}']   = $this->object['product_name'];
		
		if($direct)
			return;
		
		//set recipient
		if ( !$this->recipient )
			$this->recipient = $this->object['user_email'];
		
		$this->send( $this->get_recipient(),
					 $this->get_subject(),
					 $this->get_content(),
					 $this->get_headers(),
					 $this->get_attachments() );

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
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email Heading','woo-auction' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Default: <code>%s</code>.','woo-auction' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
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
return new Wac_Auction_Submitted();