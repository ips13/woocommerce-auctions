<div class="col-md-12">
	<div class="panel">
		<div class="panel-body">
			<div class="row">
				<div class="wcmp_table_holder col-md-12">
					<?php
					global $WCMp, $woo_auction;
					$vendor 	= get_wcmp_vendor(get_current_vendor_id());
					$vendorArr 	= $WCMp->user->get_vendor_fields($vendor->id);
					$currEmail 	= $vendor->user_data->user_email;

					// print_r($vendorArr);
					$vendorImg = $vendorArr['vendor_image'];
					$vendor_image = (isset($vendorImg['value']) && (!empty($vendorImg['value']))) ? $vendorImg['value'] : wac_default_gravatar();
					?>
					<form method="post" name="account_detail_form" id="wac-shop-settings-form" class="form-horizontal">
						<div class="wcmp_form1">
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('First Name','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[first_name]" value="<?php echo $wac_vpclass->fieldvalue('first_name'); ?>" placeholder="<?php echo __('Enter your First Name here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Last Name','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[last_name]" value="<?php echo $wac_vpclass->fieldvalue('last_name'); ?>" placeholder="<?php echo __('Enter your Last Name here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Company Name','woo-auction'); ?> *</label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[_vendor_company]" value="<?php echo $wac_vpclass->fieldvalue('_vendor_company'); ?>" placeholder="<?php echo __('Enter your Company Name here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Email Address','woo-auction'); ?> *</label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[email]" value="<?php echo $currEmail; ?>" placeholder="<?php echo __('Enter your Email Address here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Phone','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[mobile_number]" value="<?php echo $wac_vpclass->fieldvalue('mobile_number'); ?>" placeholder="<?php echo __('Enter your Phone here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Billing Address','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[_vendor_address_1]" value="<?php echo $wac_vpclass->fieldvalue('_vendor_address_1'); ?>" placeholder="<?php echo __('Enter your Billing Address here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('City','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[_vendor_city]" value="<?php echo $wac_vpclass->fieldvalue('_vendor_city'); ?>" placeholder="<?php echo __('Enter your City here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Zip Code','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control" type="text" name="vendor[_vendor_postcode]" value="<?php echo $wac_vpclass->fieldvalue('_vendor_postcode'); ?>" placeholder="<?php echo __('Enter your Zip Code here','woo-auction'); ?>">
								</div>
							</div>

							<fieldset class="form-group">
    							<legend class="col-sm-3 col-md-3"><?php echo __('Change Password','woo-auction'); ?></legend>
							</fieldset>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Password','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control pswdfield" type="password" name="vendor[_vendor_password]" value="<?php echo $wac_vpclass->fieldvalue('_vendor_password'); ?>" placeholder="<?php echo __('Enter your current password here','woo-auction'); ?>">
								</div>
							</div>
							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('New Password','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<input class="no_input form-control pswdfield" type="password" name="vendor[_vendor_newpassword]" value="<?php echo $wac_vpclass->fieldvalue('_vendor_newpassword'); ?>" placeholder="<?php echo __('Enter your New password here','woo-auction'); ?>">
								</div>
							</div>

							<div class="wac-group form-group">
								<label class="control-label col-sm-3 col-md-3"><?php echo __('Profile Pic','woo-auction'); ?></label>
								<div class="col-md-6 col-sm-9">
									<span class="dc-wp-fields-uploader">
									<img class="one_third_part" id="vendor_image_display" width="300" src="<?php echo $vendor_image; ?>" class="placeHolder" />
									<input type="hidden" name="vendor[_vendor_image]" id="vendor_image" style="display: none;" class="user-profile-fields" readonly value="<?php echo $vendor_image; ?>"  />
									<input type="button" class="upload_button wcmp_black_btn moregap two_third_part" name="vendor_image_button" id="vendor_image_button" value="<?php echo __('Upload', 'woo-auction') ?>" style="display:none;" />
									<input type="button" class="remove_button wcmp_black_btn moregap two_third_part" name="vendor_image_remove_button" id="vendor_image_remove_button" value="<?php echo __('Replace', 'woo-auction') ?>" style="display:none;" />
								</span>
								<div class="clear"></div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<div class="wcmp-action-container">
		<button type="submit" class="btn btn-default" id="save-vendor-profile"><?php echo __('Save Profile','woo-auction'); ?></button>
		<div class="clear"></div>
	</div>
</div>