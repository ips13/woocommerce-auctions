<?php
global $woo_auction;
$wacproduct 	= $woo_auction->product;
$auction_start  = ($sdatetime = yit_get_prop($product, '_yith_auction_for', true)) ? $sdatetime : NULL;
$auction_finish = ($datetime = yit_get_prop($product, '_yith_auction_to', true)) ? $datetime : NULL;
$date 	= strtotime('now');
$total 	= $auction_start - $date;
$total 	= ($total < 0)? $auction_finish - $date : $total;
?>

<div class="timer" id="timer_auction" data-remaining-time="<?php echo $total ?>" data-finish="<?php echo $auction_finish?>">
	<span id="days"
		  class="days_product_<?php echo $product->get_id() ?>"></span><?php echo __('d','woo-auction'); ?>
	<span id="hours"
		  class="hours_product_<?php echo $product->get_id() ?>"></span><span class="colon"><?php echo __('h','woo-auction'); ?></span>
	<span id="minutes"
		  class="minutes_product_<?php echo $product->get_id() ?>"></span><span class="colon"><?php echo __('m','woo-auction'); ?></span>
	<span id="seconds"
		  class="seconds_product_<?php echo $product->get_id() ?>"></span><span class="colon"><?php echo __('s','woo-auction'); ?></span>
</div>
<div id="auction_end">
    <label
        for="_yith_auction_end"><?php echo __('Auction ends: ', 'woo-auction') ?></label>
    <?php
    $auction_end_formatted = date(wc_date_format() . ' ' . wc_time_format(), $auction_finish);
    ?>
    <p id="dateend" class="yith_auction_datetime_shop" data-finnish-shop="<?php echo $auction_finish ?>" data-yith-product="<?php echo $product->get_id()?>"><?php echo $auction_end_formatted ?></p>
</div>