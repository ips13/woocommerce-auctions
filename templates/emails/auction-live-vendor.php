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

	<p>Hi {account_name},</p>
	<p>Your {auction_title} is live for bidding. We are looking forward to the auction.</p>
	<p>Follow your favorite listing live at: {weblink}</p>
	<p>If you have any questions please take a look at the FAQ page.</p>
	<p>We really value your feedback, so if you have anything to share with us please do not hesitate.</p>

<?php do_action( 'woocommerce_email_footer', $email );