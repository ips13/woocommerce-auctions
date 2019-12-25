<?php
/**
 * Email for auction submitted and send to vendor
 *
 * @author  $ing#
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p>Dear {account_name},</p>
	<p>The auction is available on the TheNextBid website.</p>
	<p>To attract the most bids we advise you to do 3 things:</p>
	<ul>
		<li>- Paste the following message below in the description of the original listing(s).<br>
		<i>{This object will be auctioned from {startdate} to {enddate} starting at {startprice} Euro. Don’t miss this opportunity! {weblink} }</i></li>
		<li>- Print the QR and shortlink in put it in a visible location during the viewings</li>
		<li>- Tell simply anyone that is interested in this property about this auction</li>
	</ul>
	<p>In order the have an optimal result, direct all interested people to the auction.</p>
	<p>To view the auction go to: {weblink}.</p>
	<p>We are looking forward to auction more of your listings. Go to start an auction to submit a new listing and obtain the best price for your listing!</p>
	<p>{weblink}</p>
	<p>If you have any questions please take a look at the FAQ page or contact us directly.</p>
	<p>We really value your feedback, so if you have anything to share with us please do not hesitate.</p>

<?php do_action( 'woocommerce_email_footer', $email );