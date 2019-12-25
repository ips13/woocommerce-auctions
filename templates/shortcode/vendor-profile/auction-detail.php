<div class="col-md-12">
	<div class="panel">
		<div class="panel-body">
			<div class="row">
				<div class="wcmp_table_holder col-md-12">
					<?php
					if($auction_id == 0){
						echo __('Not found','woo-auction');
						return;	
					}

					$product = wc_get_product($auction_id);
					if(empty($product)){
						echo __('No Auction found with id.','woo-auction');;
						return;
					}
					elseif(!wac_only_current_vendor_view($product->get_id())){
						echo __('Not Authorized to view','woo-auction');
						return;
					}	
					else{
						global $woo_auction;
						/*if($wac_vpclass->is_billing_open($product)){
							?>
							<p class='wac-msg'>
								<?php echo __('Please check and pay your auction commission on the following page:','woo-auction'); ?>
								<a href='<?php echo $wac_vpclass->paycommission_link($auction_id); ?>'><?php echo __('Pay Commission','woo-auction'); ?></a>
							</p>
							<?php
							return;
						}*/
						$auctionBids = $woo_auction->product->get_auction_bids($auction_id);
						?>
						<input type="hidden" id="yith-wcact-product-id" name="yith-wcact-product" value="<?php echo esc_attr($auction_id);?>">
						<?php
						if(count($auctionBids) == 0){
							?>
								<p class="wac-msg single-product-no-bid"><?php echo __('There is no bid for this auction','woo-auction');?></p>
							<?php
						}
						else{
								?>
								<br>
								<div class="wcmp_table_holder">
									<table id="woo-auction-vendor-detail" class="vendor-account-tbl dt-responsive nowrap" width="100%" border="0" cellspacing="0" cellpadding="0">
										<thead>
											<tr>
												<th class="toptable"><?php echo __('Name','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Highest bid','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Gender','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Nat.','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Income','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Source of Income','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('DoB','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Housemates','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('E-mail','woo-auction'); ?></th>
												<th class="toptable"><?php echo __('Phone','woo-auction'); ?></th>
											</tr>
										</thead>
										<tbody>
										<?php
											// echo '<pre>'.print_r($auctionBids,true).'</pre>';
											foreach ($auctionBids as $object => $auctionBid) {
												$user = get_user_by('id', $auctionBid->user_id);
												// print_r($user);
												$username 	= wac_vendor_name($user);
												$useremail 	= $user->data->user_email;
												$userphone 	= $wac_vpclass->umeta($auctionBid->user_id,'mobile_number');
												$gender = $wac_vpclass->umeta($auctionBid->user_id,'gender');
												$nat = $wac_vpclass->umeta($auctionBid->user_id,'Nationality');
												$userincome = $wac_vpclass->umeta($auctionBid->user_id,'monthly_income');
												$userincome = ($userincome != '-' && strpos($userincome,'€') === false)? '€'.$userincome : $userincome;
												$userdob 	= $wac_vpclass->umeta($auctionBid->user_id,'birth_date');
												$userincomesrc 	= $wac_vpclass->umeta($auctionBid->user_id,'source_of_income');
												$userhousemates = $wac_vpclass->umeta($auctionBid->user_id,'housemates');
												
												$bid = $auctionBid->maxbid;
												$bidComnts = $woo_auction->product->get_bid_comments($bid,$auctionBid->user_id);
												?>
												
												<tr class="yith-wcact-row">
													<td><?php echo $username ?></td>
													<td class="ttooltip" data-tip="cmnt-<?php echo $auctionBid->user_id ?>">
														<?php echo wc_price($bid) ?>
														<?php if(!empty($bidComnts)): ?>
															<span class="wac-um-tip">
																<i class="um-icon-information-circled"></i>
															</span>
															<div id="cmnt-<?php echo $auctionBid->user_id ?>" class="tooltiptext hidden">
															 	<?php 
																echo '<ol>';
																	foreach($bidComnts as $comnt){
																		echo "<li>".$comnt."</li>";
																	}
																echo '</ol>';	
																?> 
															</div>
														<?php endif; ?>
													</td>
													<td><?php echo is_array($gender)? current($gender) : ''; ?></td>
													<td><?php echo $nat; ?></td>
													<td><?php echo $userincome ?></td>
													<td><?php echo $userincomesrc ?></td>
													<td><?php echo $userdob ?></td>
													<td><?php echo $userhousemates ?></td>
													<td><?php echo "<a href='mailto:{$useremail}'>{$useremail}</a>"; ?></td>
													<td><?php echo $userphone ?></td>
												</tr>
												<?php
											}
										?>
										</tbody>
									</table>
									
								</div>

								<script type="text/javascript">
									(function($){
										$('.ttooltip').each(function () {
								            $(this).tooltip({
								                html: true,
								                selector: '.wac-um-tip',
								                title: $('#' + $(this).data('tip')).html(),
								            });
								        });	
						            })(jQuery); 
								</script>
								<?php
							}

						$totalBids = $woo_auction->product->total_bids($auction_id);
						$vauction_perpage = wac_setting_field('vauction_perpage');
						
						if(!empty($vauction_perpage) && $totalBids > $vauction_perpage){
							?>
							<p class='wac-msg'>
								<?php echo __("Note: There are {$vauction_perpage} more bidders. In case you do not find a good fit with the above mentioned tenants, please contact us for the account details of the other bidders",'woo-auction'); ?>
							</p>
							<?php
						}
					}
					?>

				</div>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
		if($('#woo-auction-vendor-detail').size() > 0)
			$('#woo-auction-vendor-detail').DataTable({
				responsive: true,
				language: <?php echo __datatable_lng(); ?>
			});
    });
</script>