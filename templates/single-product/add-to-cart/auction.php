<?php
/**
 * Auction product add to cart
 *
 * @author 		Carlos Rodríguez <carlos.rodriguez@yourinspiration.it>
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product, $woo_auction;

$product = apply_filters('yith_wcact_get_auction_product',$product);

// Availability
$availability      = $product->get_availability();
$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';

echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
?>

<?php do_action('yith_wcact_before_add_to_cart_form',$product) ?>

<?php if ( $product->is_in_stock() ) : ?>

    <?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
    <?php
        if(apply_filters('yith_wcact_before_add_to_cart',true,$product)) {

            if ($product->is_start() && !$product->is_closed()) {

                $auction_start = ($datetime = yit_get_prop($product, '_yith_auction_for', true)) ? $datetime : NULL;
                $auction_finish = ($datetime = yit_get_prop($product, '_yith_auction_to', true)) ? $datetime : NULL;
                $date = strtotime('now');
				$bid_increment = 1;
				$total = $auction_finish - $date;
					
				do_action('yith_wcact_before_form_auction_product',$product);
                ?>
                <form class="cart" method="post" enctype='multipart/form-data'>

                    <div id="yith-wcact-auction-timeleft">
		                <?php do_action('yith_wcact_auction_before_set_bid',$product) ?>
                    </div>

					<div id="time" class="timetito" data-finish-time="<?php echo $auction_finish ?>" data-remaining-time=" <?php echo $total ?>" data-bid-increment="<?php echo $bid_increment ?>" data-product="<?php echo $product->get_id()?>"data-current="<?php echo $product->get_price()?>"data-finish="<?php echo $auction_finish?>">

                        <div name="form_bid" id="yith-wcact-form-bid">
			                <?php
			                if ('yes' == get_option('yith_wcact_settings_tab_auction_show_button_plus_minus')) {
				                ?>
                                <input type="button" class="bid button_bid_subtr" value="-">
				                <?php
			                }
			                ?>

                            <input type="number" id="_actual_bid" name="_section" value="" size="4" min="0" class="" style="<?php echo apply_filters('yith_wcact_actual_bid_class',''); ?>">
			                <?php
			                if ('yes' == get_option('yith_wcact_settings_tab_auction_show_button_plus_minus')) {
				                ?>
                                <input type="button" class="bid button_bid_add" value="+">
				                <?php
			                }
							?>
						</div>
						<?php
						do_action('yith_wcact_before_add_button_bid',$product) ?>
						<div id="yith-wcact-aution-buttons">
							<button type="button"
								class="auction_bid button alt"><?php echo __('Bid', 'woo-auction'); ?></button>
							<?php do_action('yith_wcact_after_add_button_bid',$product); ?>
						</div>
                    </div>

                    <div class="comment-box">
                    	<span class="comment-box-label"><?php echo $woo_auction->wacStrings['pd_comment_lbl']; ?>:</span>
						<textarea id="comments" class="cmnts" name="comment" placeholder="<?php echo $woo_auction->wacStrings['pd_comment_desc']; ?>"></textarea>
					</div>
                
                	<?php do_action('woocommerce_after_add_to_cart_button'); ?>
                   	<?php do_action('yith_wcact_after_form_bid',$product); ?>
                    <?php do_action('yith_wcact_in_to_form_add_to_cart',$product); ?>

                </form>
                
                
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
				
				//display subscribe form
				$woo_auction->overwrite->subscribe_form($product);
				
                //The auction end
            } else {
                
                do_action('yith_wcact_auction_end',$product);

            }
        }
    ?>
    <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif;

