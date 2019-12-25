<?php
global $ultimatemember;
// $ultimatemember->account->current_tab = $current_tab;
$ultimatemember->account->current_tab = 'profile';
?>
<div class="um">

	<div class="um-form wac-profile-page">
	
			
			<?php do_action('um_account_user_photo_hook__mobile', $args ); ?>
			
			<div class="um-account-side wac-direct">
			
				<?php do_action('um_account_user_photo_hook', $args ); ?>
				
				<?php do_action('um_account_display_tabs_hook', $args ); ?>

			</div>
			
			<div class="um-account-main" data-current_tab="<?php echo $current_tab; ?>">
				
				<div class="um-account-nav uimob340-show uimob500-show"><a href="#" data-tab="<?php echo $id; ?>" class="<?php if ( $id == $current_tab ) echo 'current'; ?>">
					<?php 
						/* echo $title; 
						<span class="ico"><i class="<?php echo $icon; ?>"></i></span>
						<span class="arr"><i class="um-faicon-angle-down"></i></span> */ 
					?>
					</a></div>
					
					<?php
					
					echo '<div class="um-account-tab um-account-tab-'.$id.'" data-tab="'.$id.'">';

						echo $view_content;
					
					echo '</div>';
					?>
							
			</div><div class="um-clear"></div>
		
		<?php do_action('um_after_account_page_load'); ?>
	
	</div>
	
</div>