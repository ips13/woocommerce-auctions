<?php
/**
 * Auction product add to cart
 *
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;
$product = apply_filters('yith_wcact_get_auction_product',$product);

	if(true) {

		if ($product->is_start() && !$product->is_closed()) {

			$auction_finish = ($datetime = yit_get_prop($product, '_yith_auction_to', true)) ? $datetime : NULL;
			$date = strtotime('now');
			do_action('yith_wcact_before_form_auction_product',$product);
			?>
			<div class="cart">

				<div id="yith-wcact-auction-timeleft">
					
					<?php do_action('yith_wcact_auction_before_set_bid',$product) ?>
				</div>
				
				<?php do_action('woocommerce_after_add_to_cart_button'); ?>
				<?php do_action('yith_wcact_after_form_bid',$product);?>
				<?php
				$bid_increment = 1;
				$total = $auction_finish - $date;

				do_action('yith_wcact_in_to_form_add_to_cart',$product);
				
				?>

			</div>
			
			
			<?php do_action( 'yith_wcact_after_add_to_cart_form',$product); ?>

			<?php
		} elseif (!$product->is_closed() || !$product->is_start()) {

			$for_auction = ($datetime = yit_get_prop($product, '_yith_auction_for', true)) ? $datetime : NULL;
			$auction_start = $for_auction;
			$date = strtotime('now');
			$total = $auction_start - $date;
			?>
			<h3 class="hideonstart" data-hideonstart="yes"><?php echo __('Auction is not open yet', 'woo-auction') ?></h3>
				<div id="time">
					<label class="hideonstart"for="yith_time_left"><?php echo __('Time left to start auction:', 'woo-auction') ?></label>
						
					<div id="yith-wcact-auction-timeleft">
						<div class="timer" id="timer_auction" data-remaining-time="<?php echo $total ?>">
							<span id="days"
								  class="days_product_<?php echo $product->get_id() ?>"></span><?php echo __('d','woo-auction'); ?>
							<span id="hours"
								  class="hours_product_<?php echo $product->get_id() ?>"></span><span class="colon"><?php echo __('h','woo-auction'); ?></span>
							<span id="minutes"
								  class="minutes_product_<?php echo $product->get_id() ?>"></span><span class="colon"><?php echo __('m','woo-auction'); ?></span>
							<span id="seconds"
								  class="seconds_product_<?php echo $product->get_id() ?>"></span><span class="colon"><?php echo __('s','woo-auction'); ?></span>
						</div>
                    </div>
				</div>
			<?php
			//The auction end
		} else {
			
			do_action('yith_wcact_auction_end',$product);

		}
	}
?>