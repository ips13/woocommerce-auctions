<?php
/**
 * The template for displaying product widget entries
**/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
$date = strtotime('now');
$product = apply_filters('yith_wcact_get_auction_product',$product);
$auction_finish = ( $datetime = yit_get_prop( $product, '_yith_auction_to', true ) ) ?  $datetime  : NULL;
$auction_start  = ( $datetime = yit_get_prop( $product, '_yith_auction_for', true ) ) ?  $datetime  : NULL;
?>

<li class="wac-auction-item wac-<?php echo $display_type; ?>-auction">
	<a class="wac-widget-link" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
		
		<span class="product-img">
			<?php echo $product->get_image(); ?>
			<?php echo wc_price($product->get_price()); ?>
		</span>
		
		<span class="product-title"><?php echo $product->get_title(); ?></span>
		
		<?php
			
			if ($date < $auction_start && !$product->is_start()) {
				$startsin = wac_datetimedifference('now',date('Y-m-d H:i:s',$auction_start));
				?>
				<div class="auction-time">
					<label for="yith_time_left"><?php echo __('Start:','woo-auction') ?></label>
					<p class="wac_date_auction time-auction">
						<?php echo date('d M H:i', $auction_start); ?>
					</p>
				</div>
				<?php
			}
			elseif( $product->is_start() && !$product->is_closed()) {
				$endsin = wac_datetimedifference('now',date('Y-m-d H:i:s',$auction_finish));
				?>
				<div class="auction-time">
					<label for="yith_time_left"><?php echo __('Ends in:','woo-auction') ?></label>
					<p class="wac_date_auction time-auction yith-date-auct-<?php echo$product->get_id()?>" data-finish-time="<?php echo $auction_finish ?>" data-yith-product="<?php echo $product->get_id() ?>">
						<span class="yith-days"><?php echo $endsin->d; ?><?php echo __('d','woo-auction') ?></span>
						<span class="yith-hours"><?php echo $endsin->h; ?><?php echo __('h','woo-auction') ?></span>
						<span class="yith-mins"><?php echo $endsin->i; ?><?php echo __('m','woo-auction') ?></span>
						<span class="yith-secs"><?php echo $endsin->s; ?><?php echo __('s','woo-auction') ?></span>
					</p>
				</div>
				<?php
			}
			elseif($product->is_closed() || $product->is_closed_for_buy_now()){
				?>
				<div class="auction-time">
					<label for="yith_time_left"><?php echo __('Closed','woo-auction') ?></label>
				</div>
				<?php
			}
		?>
	</a>
</li>
