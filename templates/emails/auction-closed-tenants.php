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
	<p>Great work! You ended up with the top 5 highest bidders for the TheNextBid {auction_title} auction.</p>
	<p>The landlord will now consider contacting you after approaching the higher bidders.</p>
	<p>We cannot guarantee that you will be contacted, so we would like to advise you not to wait any
		longer and start searching for another TheNextBid auction and hopefully you will find your next
		dream home with the help of TheNextBid!</p>
	<p>If you have any questions please take a look at the FAQ page.</p>
	<p>We really value your feedback, so if you have anything to share with us please do not hesitate.</p>
	<p>Thank you for participating in the TheNextBid {auction_title} auction</p>

<?php do_action( 'woocommerce_email_footer', $email );