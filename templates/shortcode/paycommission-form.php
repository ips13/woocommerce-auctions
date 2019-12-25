<?php
if($auction_id == 0){
	echo 'Not found';
	return;	
}
?>
<div class="paycommision-container">
	<?php
		global $woo_auction;
		$listingPrice = get_post_meta($auction_id,'_yith_auction_reserve_price',true);
		$wacproduct = $woo_auction->product;
		$maxBid 	= $wacproduct->get_max_bid($auction_id);
		
		//get current auction object (product)
		$product = wc_get_product($auction_id);
		
		if($product && $product->is_type('auction')){
			
			$curr_user_id = get_current_user_id();
			$auction_name = $product->get_title();
			
			if($showform)
				echo "<h4 class='product_title'>{$auction_name}</h4>";
			
			//if payment is already paid then display status
			$mollie_status = get_post_meta($auction_id,'_molliepay_status',true);
			if(!empty($mollie_status)){
				echo '<ul class="list-group">';
					echo "<li class='list-group-item'>". __('Payment Status:','woo-auction'). "<strong>{$mollie_status}</strong></li>";
				
				if($mollie_status == 'paid'){
					$mollie_txnid = get_post_meta($auction_id,'_molliepay_txnid',true);
					echo "<li class='list-group-item'>". __('Commission has been already paid','woo-auction') ."</li>";
					echo "<li class='list-group-item'>". __('Transaction ID: <strong>{$mollie_txnid}</strong>','woo-auction') ."</li>";
					
					return;
				}
				elseif($mollie_status == 'cancelled'){
					echo "<li class='list-group-item'><strong>". __('Please try again!','woo-auction') ."</strong></li>";
				}
				else{
					echo "<li class='list-group-item'><strong>". __('Please try again!','woo-auction') ."</strong></li>";
				}
				echo '</ul>';
				
			}
			
			if($product->is_closed()){
				if(isset($maxBid->bid)){
					
					$highestBid = $maxBid->bid;
					
					if(empty($listingPrice) || $listingPrice <= 0 ){
						echo 'Listing Price is very low. '.$listingPrice;
						return;
					}
					if($highestBid > $listingPrice){
						$comm_multi = wac_setting_field('commission_multi');
						$commission = ($highestBid - $listingPrice) * $comm_multi;
						
						//add 21% VAT on commission
						$VATprice = ($commission>0)? $commission*0.21 : 0;
					
						update_post_meta($auction_id,'_auction_commission_price',$commission);
						
						//total price
						$totalPrice = $commission + $VATprice;
						?>
							<ul class="list-group">
								<li class="list-group-item"><label><?php echo __('Highest Bid','woo-auction'); ?>:</label> <?php echo wac_price($maxBid->bid); ?></li>
								<li class="list-group-item"><label><?php echo __('Listing Price','woo-auction'); ?>:</label> <?php echo wac_price($listingPrice); ?></li>
								<li class="list-group-item"><label><?php echo __('Commission (highest bid - listing price)','woo-auction'); ?>:</label> <?php echo wac_price($commission); ?></li>
								<li class="list-group-item"><label><?php echo __('VAT (21%)','woo-auction'); ?>:</label> <?php echo wac_price($VATprice); ?></li>
								<li class="list-group-item comm-withvat"><label><?php echo __('Commission incl. VAT','woo-auction'); ?>:</label> <?php echo wac_price($totalPrice); ?></li>
							</ul>
						<?php
						
						if($showform && $commission > 0){
							?>
							<form id="auction-paycommission" method="post">
								<input type="hidden" name="ID" value="<?php echo $auction_id; ?>">
								<input type="hidden" name="user_ID" value="<?php echo $curr_user_id; ?>">
								<input type="hidden" name="com_price" value="<?php echo $commission; ?>">
								<input type="hidden" name="vat_price" value="<?php echo $VATprice; ?>">
								<input type="hidden" name="post_title" value="<?php echo $auction_name; ?>">
								<input type="hidden" name="paycmisn" value="1">
								<button type="submit" class="btn btn-default paycmisn"><?php echo __('Pay Commission','woo-auction'); ?></button>
							</form>
							<?php
						}
						else{
							echo 'Ask Landlord for complete payment!';
						}
					}
					else{
						echo 'Highest Bid lower than listing price';
					}
				}
				else{
					echo 'Maximum bid not found!';
				}
			}
			else{
				echo "It's not ended yet!";
			}
		}
		else{
			echo 'Not valid auction!';
		}
	?>
</div>
