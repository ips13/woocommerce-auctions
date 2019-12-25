<div class="col-md-12">
	<div class="panel">
		<div class="panel-body">
			<div class="row">
				<div class="wcmp_table_holder col-md-12">
					<?php if(is_array($allAuctions) && sizeof($allAuctions) > 0): ?>
						<table id="woo-auction-vendor-report" class="vendor-account-tbl dt-responsive nowrap" width="100%">
							<thead>
								<tr>	
									<th><?php echo __('Auctions','woo-auction'); ?></th>
									<th><?php echo __('Highest Bid','woo-auction'); ?></th>
									<th><?php echo __('Listing Price','woo-auction'); ?></th>
									<th><?php echo __('# bidders','woo-auction'); ?></th>
									<th><?php echo __('# bids','woo-auction'); ?></th>
									<th><?php echo __('Closing Date','woo-auction'); ?></th>
									<th><?php echo __('Auction Report','woo-auction'); ?></th>
								</tr>
							</thead>
							<tbody>
								
								<?php
									// print_r($allAuctions);
									foreach($allAuctions as $auction){
										
										$auction['report'] = 'Available';

										$aucReport = ($auction['report']=='Available')? "<a href='{$wac_vpclass->auctiondetail_link($auction['id'])}'>Available</a>" : $auction['report'];
										?>
											<tr class="yith-wcact-row">
												<td><a href="<?php echo $auction['dlink']; ?>"><?php echo $auction['name']; ?></a></td>
												<td><?php echo $auction['highbid']; ?></td>
												<td><?php echo $auction['listprice']; ?></td>
												<td><?php echo $auction['bidders']; ?></td>
												<td><?php echo $auction['bids']; ?></td>
												<td><?php echo $auction['closing']; ?></td>
												<td><?php echo $aucReport; ?></td>
											</tr>
										<?php
									}
								?>
							</tbody>
						</table>
						
						<p class="wac-msg"><?php echo __('Note: Only closed auctions are visible in this overview.','woo-auction'); ?></p>
						
					<?php else: ?>
						<p class="wac-msg"><?php echo __('No Auction to display!','woo-auction'); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
		$('#woo-auction-vendor-report').DataTable({
			responsive: true,
			language: <?php echo __datatable_lng(); ?>
		});
    });
</script>