<?php
/*
 * Display auction emails by type
 * siteurl/wp-admin/?displaymail=auc-closed-tenants
 */
class Woo_Auction_Display_Emails extends Mailtpl_Mailer{
	
	public function __construct($plugin_name, $version){
		$this->plugin_name  = $plugin_name;
		$this->version      = $version;
		$this->opts         = Mailtpl::opts();
		add_action('admin_init',array($this,'showcontentemail'),999);
	}
	
	public function showcontentemail(){
		if(isset($_GET['displaymail'])){

			$displaymail = $_GET['displaymail'];
			$wc_emails = WC_Emails::instance();
			$emails = $wc_emails->get_emails();
			// echo '<pre>';print_r($emails);
			
			switch($displaymail){
				case 'auc-approved':
					$current_email = $emails['WAC_Auction_Approved'];
					break;
				case 'auc-closed':
					$current_email = $emails['WAC_Auction_Closed'];
					break;
				case 'auc-closed-tenants':
					$current_email = $emails['WAC_Auction_Closed_Tenants'];
					break;
				case 'auc-live-tenants':
					$current_email = $emails['WAC_Auction_Live_Tenants'];
					break;
				case 'auc-live-vendor':
					$current_email = $emails['WAC_Auction_Live_Tenants'];
					break;
				default:
					$current_email = $emails['WAC_Auction_Submitted'];
					break;
			}
			
			$maildata  = $current_email->get_content_html();
			echo $this->display_template( $maildata );
			die;
		}
	}
	
	function display_template( $email ) {
		/* if( $this->template )
			return str_replace( '%%MAILCONTENT%%', $email, $this->template ); */

		do_action( 'mailtpl/add_template', $email, $this );
		
		$template_file = apply_filters( 'mailtpl/customizer_template', MAILTPL_PLUGIN_DIR . "/admin/templates/default.php");
		ob_start();
		include_once( $template_file );
		$this->template = ob_get_contents();
		ob_end_clean();
		return str_replace( '%%MAILCONTENT%%', $email, $this->template );
	}
}