<?php
/**
 * Email for auction closed and send to Landlord/Agent
 *
 * @author  $ing#
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p>Dear {account_name},</p>
	<p>The {auction_title} auction is closed and we are excited about the result!</p>
	<p>The auction report contains the contact details of the top bidders so you can request any supporting documents and sign the contract.</p>
	<p>Complete the payment via this link now to access the auction report.</p>
	<p>{paylink}</p>
	<p>If you have any questions please take a look at the FAQ page or contact us directly.</p>
	<p>We really value your feedback, so if you have anything to share with us please do not hesitate</p>

<?php do_action( 'woocommerce_email_footer', $email );