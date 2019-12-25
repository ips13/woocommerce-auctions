<div class="col-md-12">
	<div class="panel">
		<div class="panel-body">
			<div class="row">
				<div class="col-md-12 wcmp_table_holder">
					<?php if(is_array($allAuctions) && sizeof($allAuctions) > 0): ?>
						<table id="vendor-dashboard-auctions" class="vendor-account-tbl dt-responsive nowrap" width="100%">
							<thead>
								<tr>	
									<th><?php echo __('Auctions','woo-auction'); ?></th>
									<th><?php echo __('Status','woo-auction'); ?></th>
									<th><?php echo __('Listing price','woo-auction'); ?></th>
									<th><?php echo __('Current price','woo-auction'); ?></th>
									<th><?php echo __('Time','woo-auction'); ?></th>
									<?php /*<th><?php echo __('Billing Status','woo-auction'); ?></th> */?>
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
												<td><?php echo $auction['name']; ?></td>
												<td class="wac-status" data-auction="<?php echo $auction['id']; ?>" data-status="<?php echo $auction['status']; ?>"><?php echo $wac_vpclass->status_hlink($auction); ?></td>
												<td><?php echo $auction['listprice']; ?></td>
												<td><?php echo $auction['crntprice']; ?></td>
												<td class="wac-time"><?php echo $auction['time']; ?></td>
												<?php /*<td><?php echo $auction['billing']; ?></td> */?>
												<td><?php echo $aucReport; ?></td>
											</tr>
										<?php
									}
								?>
							</tbody>
						</table>
					<?php else: ?>
						<p class="wac-msg"><?php echo __('No Auction to display, Click on Start Auction to generate new Auction!','woo-auction'); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
		$('#vendor-dashboard-auctions').DataTable({
			responsive: true,
			language: <?php echo __datatable_lng(); ?>
		});
    });
</script>