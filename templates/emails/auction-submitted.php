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
		<p>Great! We have successfully received your auction request.</p>
		<p>We will review the request and make it available online. In exceptional situations we will circle back to you
		within 24 hours with some comments.</p>
		<p>If you have any questions please take a look at the FAQ page or contact us directly.</p>
		<p>We really value your feedback, so if you have anything to share with us please do not hesitate.</p>

<?php do_action( 'woocommerce_email_footer', $email );