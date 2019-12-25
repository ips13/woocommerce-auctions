<?php 
	global $woo_auction, $WCMp, $WCMp_Frontend_Product_Manager; 
	$wacStrings = $woo_auction->wacStrings;
	$agreeLink 	= $wac_startauc->get_agreementlink();
	$current_user = wp_get_current_user();
	$listed_by 	= wac_vendor_company($current_user);
?>

<div class="woocommerce">
	<form id="wac_auction_form" class="woocommerce-form">
		<h3><?php echo __('Object details','woo-auction'); ?> <span class="required"></span></h3>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Weblink of listing','woo-auction'); ?> <?php echo wac_ihelp($wacStrings['sa_weblink']); ?></strong>
			<input type="text" class="woocommerce-Input regular-text weblink-txt" name="weblink" id="weblink" value="" placeholder="https://www.funda.nl/huur/XXXXXXXXXXXXXXXXX/">
			<button class="weblink-fetch btn btn-primary"><?php echo __('Fetch','woo-auction'); ?></button>
		</div>
		
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Title','woo-auction'); ?> <?php echo wac_ihelp($wacStrings['sa_title']); ?></strong>
			<input type="text" class="woocommerce-Input regular-text" name="title" id="auctitle" value="">
		</div>
		
		<div class="woocommerce-form-row form-row form-row-wide wac-cats-section">
			<strong for="username"><?php echo __('City','woo-auction'); ?> <?php //echo wac_ihelp($wacStrings['sa_city']); ?></strong>
			<select id="product-cats" name="product_cats[]" class="regular-select" multiple="multiple" style="width: 64%; margin-bottom: 10px;">
				<?php
					if ( $product_categories ) {
						$categories = array();
						$WCMp_Frontend_Product_Manager->generateTaxonomyHTML( 'product_cat', $product_categories, $categories );
					}
				?>
			</select>
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<?php
				$featured_img = '';
				$WCMp_Frontend_Product_Manager->wcmp_wp_fields->dc_generate_form_field( array("featured_img" => array('label' => __('Upload Auction Image','woo-auction') , 'type' => 'upload', 'class' => 'regular-text onlyimage', 'label_class' => 'pro_title', 'value' => $featured_img)));
			?>
			<!--button class="upload-direct">Direct</button-->
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Object Available From','woo-auction'); ?> <?php //echo wac_ihelp($wacStrings['sa_obavfrom']); ?></strong>
			<input type="text" class="woocommerce-Input regular-text datepicker" name="available_from" id="available-from" value="">
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Rental Period','woo-auction'); ?> <?php //echo wac_ihelp($wacStrings['sa_rental']); ?></strong>
			<select name="rental_period" id="rental-period" class="woocommerce-Select regular-select">
				<option value=""><?php echo __('Select Rental Period','woo-auction'); ?></option>
				<option value="Indefinite"><?php echo __('Indefinite','woo-auction'); ?></option>
				<option value="1 Year"><?php echo __('1 Year','woo-auction'); ?></option>
				<option value="2 Years"><?php echo __('2 Years','woo-auction'); ?></option>
				<option value="Additional Information"><?php echo __('Otherwise, additional information','woo-auction'); ?></option>
			</select>
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Deposit in Euro','woo-auction'); ?> <?php //echo wac_ihelp($wacStrings['sa_deposit']); ?></strong>
			<input type="text" class="woocommerce-Input regular-text" name="deposit" id="auction-deposit" value="">
		</div>
		
		<h3><?php echo __('Auction details'); ?> <span class="required"></span></h3>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Start Auction','woo-auction'); ?> <?php //echo wac_ihelp($wacStrings['sa_aucdatefor']); ?></strong>
			<input type="text" id="_yith_auction_for" name="_yith_auction_for" class="regular-text" value="" placeholder="<?php echo __('DD-MM-YYYY hh:mm','woo-auction'); ?>">
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Auction Close','woo-auction'); ?> <?php //echo wac_ihelp($wacStrings['sa_aucdateto']); ?></strong>
			<input type="text" id="_yith_auction_to" name="_yith_auction_to" class="regular-text" value="" placeholder="<?php echo __('DD-MM-YYYY hh:mm','woo-auction'); ?>">
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Listing price in Euro','woo-auction'); ?> <?php echo wac_ihelp($wacStrings['sa_listprice']); ?></strong>
			<input type="text" class="woocommerce-Input regular-text" name="_yith_auction_listing_price" id="_yith_auction_listing_price" value="">
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Start price in Euro','woo-auction'); ?> <?php echo wac_ihelp($wacStrings['sa_startprice']); ?></strong>
			<input type="text" class="woocommerce-Input regular-text" name="_yith_auction_start_price" id="_yith_auction_start_price" value="">
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<strong for="username"><?php echo __('Minimum Price','woo-auction'); ?> <?php echo wac_ihelp($wacStrings['sa_minprice']); ?></strong>
			<?php echo wac_yesno_switch(['class'=>'minprice-switch','name'=>'minprice']); ?>
		</div>
		
		<h3><?php echo __('Other Information','woo-auction'); ?></h3>
		<div class="woocommerce-form-row form-row form-row-wide tenant-requirement-row">
			<strong for="username"><?php echo __('Tenant Requirements','woo-auction'); ?></strong>
			<textarea type="text" class="regular-textarea" name="tenant_requirement" id="tenant-requirement" value="" placeholder="<?php echo $wacStrings['sa_tenantreq']; ?>"></textarea>
		</div>
		<div class="woocommerce-form-row form-row form-row-wide additiondesc-row">
			<strong for="username"><?php echo __('Additional Information','woo-auction'); ?></strong>
			<textarea id="additiondesc" name="description" class="regular-textarea" placeholder="<?php echo $wacStrings['sa_addinfo']; ?>" rows="2" cols="20" ></textarea>
		</div>
		<div class="woocommerce-form-row form-row form-row-wide additiondesc-row">
			<strong for="username"><?php echo __('Comment for TheNextBid (not visible for other users)','woo-auction'); ?></strong>
			<textarea id="addicomments" name="comment" class="regular-textarea" rows="2" cols="20" placeholder="<?php echo $wacStrings['sa_comment']; ?>"></textarea>
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<div class="calltxt"><?php echo __('Questions? Please call +31(0) 202 258 340','woo-auction'); ?></div>
		</div>
		<div class="woocommerce-form-row form-row form-row-wide">
			<input type="checkbox" class="agreement"> <?php echo sprintf(__('I understand and accept the %s terms and conditions %s.','woo-auction'), "<a href='{$agreeLink}' class='agreelink' target='_blank'>", "</a>"); ?>
		</div>
		<div class="action-buttons">
			<input type="submit" name="submit-data" value="<?php echo __('Preview Auction','woo-auction'); ?>" id="check-auction" class="btn btn-primary" data-toggle="modal" data-target="#preview-auction">
			<input type="hidden" id="pro_id" name="pro_id" class="hidden" value="0">
			<input type="hidden" id="listed_by" name="listed_by" class="hidden" value="<?php echo $listed_by ;?>"> 
		</div>
	</form>
	
	
	<!-- Modal -->
	<div class="modal fade" id="preview-auction" role="dialog">
		<div class="modal-dialog" style="width:900px; max-width:96%; margin:2% auto;">
		
			<!-- Modal content-->
			<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?php echo __('Preview Auction','woo-auction'); ?></h4>
			</div>
			<div class="modal-body">
				<div class="">
					<div class="row">
						<div class="col-md-7 col-sm-7 col-xs-12"><p id="prevImg"><img src="" /></p></div>
						<div class="col-md-5 col-sm-5 col-xs-12">
							<h2 id="prevTitle"></h2>
							<p id="prevListPrc">
								<?php echo __('Start Price:','woo-auction'); ?> 
								<span class="price">
									<span class="preview-Price-currencySymbol">€</span>
									<span class="preview-Price-amount"></span>
								</span>
							</p>
							<div id="auction-timeleft">
								<div class="timer" id="timer_auction">
									<p class="count-time"><?php echo __('Auction will start in','woo-auction'); ?> <span id="days" class="days_auc"></span>d <span id="hours" class="hours_auc"></span><span class="colon">h:</span>
									<span id="minutes" class="minutes_auc"></span><span class="colon">m:</span>
									<span id="seconds" class="seconds_auc"></span><span class="colon">s</span></p>
									<p class="static-time"><?php echo __('Auction already started','woo-auction'); ?></p>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-7 col-sm-7 col-xs-12">
							<div class="keyinfo-section">
								<h2 class="keyinfo-title"><?php echo __('Key information','woo-auction'); ?></h2>
								<ul class="keyinfo-lists">
								<li class="keyinfo-list kil-city">
									<span class="keyinfo-list-title"><?php echo __('City:','woo-auction'); ?></span>
									<span class="keyinfo-list-cities"></span>
								</li>
								<li class="keyinfo-list kil-listing-price">
									<span class="keyinfo-list-title"><?php echo __('Listing Price:','woo-auction'); ?></span>
									<span class="preview-Price-currencySymbol">€</span><span class="preview-Price-listing"></span>					
								</li>
								<li class="keyinfo-list kil-minprice">
									<span class="keyinfo-list-title"><?php echo __('Minimum Price:','woo-auction'); ?></span>
									<span class="keyinfo-list-minprice">
										<?php echo __('Yes','woo-auction'); ?>
										<?php echo wac_ihelp($wacStrings['pd_minpriceinfo']); ?>
									</span>
								</li>
								<li class="keyinfo-list kil-deposit">
									<span class="keyinfo-list-title"><?php echo __('Deposit:','woo-auction'); ?></span>
									<span class="preview-Price-currencySymbol">€</span><span class="preview-Price-deposit"></span>	
								</li>
								<li class="keyinfo-list kil-available-from">
									<span class="keyinfo-list-title"><?php echo __('Available from:','woo-auction'); ?></span>
									<span class="keyinfo-list-available"></span>
								</li>
								<li class="keyinfo-list kil-rental-period">
									<span class="keyinfo-list-title"><?php echo __('Rental Period:','woo-auction'); ?></span>
									<span class="keyinfo-list-rental"></span>
								</li>
								<li class="keyinfo-list kil-listed-by">
									<span class="keyinfo-list-title"><?php echo __('Listed By:','woo-auction'); ?></span>
									<span class="keyinfo-list-user"></span>
								</li>
								<li class="keyinfo-list kil-listinglink">
									<span class="keyinfo-list-text">
										<a href="#" class="externl-lnk hidden" target="_blank">
											<?php echo __('Link to original listing','woo-auction'); ?>
										</a>
									</span>
								</li>
								<li class="keyinfo-list kil-additional-financial-information">
									<span class="keyinfo-list-title"><?php echo __('Additional information:','woo-auction'); ?></span>
									<span class="keyinfo-list-addinfo"></span>
								</li>
								<li class="keyinfo-list kil-tenant-requirements">
									<span class="keyinfo-list-title"><?php echo __('Tenant Requirements:','woo-auction'); ?></span>
									<span class="keyinfo-list-tenantingo"></span>
								</li>
								</ul>
							</div>
						</div>
						<div class="col-md-5 col-sm-5 col-xs-12">
							<h2 class="keyinfo-title"><?php echo __('Bid History','woo-auction'); ?></h2>					
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Make Changes','woo-auction'); ?></button>
				<button type="button" class="btn btn-primary" id="woo-auction-submit"><?php echo __('Submit','woo-auction'); ?></button>
			</div>
		  </div>
		  
		</div>
	</div>
	  
</div>