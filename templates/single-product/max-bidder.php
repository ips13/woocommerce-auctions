<?php
$bid_increment   = ( $bid = yit_get_prop( $product, '_yith_auction_bid_increment', true ) ) ?  $bid  : '1';

//code for show overtime and bidup//
$showbidup = yit_get_prop($product,'_yith_wcact_upbid_checkbox', true);
$bidup = "";
if ( 'yes' == $showbidup ) {
    $bidup = ( $bid = yit_get_prop( $product, '_yith_auction_bid_increment', true )) ? __('Bid up: ','woo-auction') . wc_price($bid) :  __('Bid up: No bid up','woo-auction') ;
}

$showoverbid = yit_get_prop($product,'_yith_wcact_overtime_checkbox', true);
$over = "";
if ( 'yes' == $showoverbid ) {
    $over = ( $overtime = $product->get_overtime()) ?  sprintf(__( 'Overtime: %s min', 'woo-auction' ), $overtime) : __('No overtime','woo-auction')  ;
}
?>
<div class="yith-wcact-max-bidder" id="yith-wcact-max-bidder">
        <div class="yith-wcact-overbidmode yith-wcact-bidupmode">
            <span id="yith-wcact-showbidup"><?php echo $bidup ?></span> <span title="<?php echo __('Total used from pool of money for automatic bid up.','woo-auction') ?>" <?php echo ('yes' == $showbidup) ? 'class="yith-auction-help-tip"': '' ?>></span> </br>
            <span id="yith-wcact-showovertime"><?php echo $over ?> </span> <span title="<?php echo __('Number of minutes added to the auction if a bid is made within the overtime range.','woo-auction')?>" <?php echo ( 'yes' == $showoverbid) ? 'class="yith-auction-help-tip"': '' ?>></span>
        </div>
    <?php
    ////////////////////////////////////
    global $woo_auction;
    $userid   	= get_current_user_id();
	$wacproduct = $woo_auction->product;
    $max_bid  	= $wacproduct->get_max_bid($product->get_id());
	$maxAutoBid = $wacproduct->max_auction_bid_byuser($userid, $product->get_id());
	$bidType 	= ($maxAutoBid['type'] == 'autobid')? 'auto bid' : 'bid';
	$maxBidprice= $maxAutoBid['price'];
    
	if ( $max_bid && $userid == $max_bid->user_id) {
        ?>
        <div id="winner_maximun_bid">
            <p id="max_winner"><?php echo __(' You are currently the highest bidder for this auction','woo-auction')?> <span title="<?php echo __('Refresh the page regularly to see if you are still the highest bidder','woo-auction') ?>" class="yith-auction-help-tip"></span></p>
            <?php
            $show_tooltip = ( $bid = yit_get_prop( $product, '_yith_auction_bid_increment', true )) ? '<span title="'. __('If your bid is higher or equivalent to the reserve price, your bid will match the reserve price with the remaining saved and used automatically to outbid a competitors bid.','woo-auction').'" class="yith-auction-help-tip"></span>': '';
            ?>
			<?php if($maxAutoBid['type'] == 'autobid'): ?>
				<p id="current_max_bid"><?php echo sprintf( apply_filters('yith_wcact_current_max_bid',__( 'Your maximum %1$s: %2$s', 'woo-auction' ),$show_tooltip), $bidType, wc_price( $maxBidprice ) ) ?> <?php echo $show_tooltip ?></p>
			<?php endif; ?>
        </div>
        <?php
    }
    ?>
</div>
    