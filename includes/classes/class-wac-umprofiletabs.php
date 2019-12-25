<?php

class Woo_Auction_UmprofileTabs{
	
	function __construct(){
		add_action('um_account_tab__profile', array($this,'um_account_tab_profile'));
		add_filter('um_account_page_default_tabs_hook', array($this,'profile_tab_in_um'), 100 );
		// add_filter('um_account_content_hook_profile', array($this,'um_account_content_hook_profile'));
		
		add_action('template_redirect',array($this,'runlasthooks'),99);
		add_shortcode('woo-tenant-profile',array($this,'tenant_profile_settings'));
		
		add_filter('the_title', array($this,'um_dynamic_user_profile_title'), 100001, 2 );
		
		add_action('wp_footer', array($this,'footer_scripts'));
	}
	
	//display page title instead of profile title
	function um_dynamic_user_profile_title($title){
		//run query for profile edit title
		global $wp_query, $WCMp;
        if (!is_null($wp_query) && !is_admin() && is_main_query() && in_the_loop() && is_page() && $this->is_umprofilepage()) {
            $endpoint = $WCMp->endpoints->get_current_endpoint();

			$acpageid = um_get_option('core_account');
			$page  = get_post($acpageid);
			$title = $page->post_title;
			
            remove_filter('the_title', array(&$this, 'um_dynamic_user_profile_title'));
        }

		return $title;
	}
	
	//run after all intialized hooks
	function runlasthooks(){
		if($this->is_umprofilepage()){
			remove_filter('um_account_page_default_tabs_hook', 'um_account_page_default_tabs_hook' );
		}
	}
	
	//if ultimate member profile page
	function is_umprofilepage(){
		$userpageid = um_get_option('core_user');
		$accpageid 	= um_get_option('core_account');
		$pageObject = get_queried_object();

		if(is_object($pageObject) && isset($pageObject->ID)){
			//get translated main product id. WPML
			$parent_product_id = apply_filters( 'yith_wcact_auction_product_id',$pageObject->ID );
			
			if(($userpageid == $parent_product_id || $accpageid == $parent_product_id)){
				return true;
			}
		}
		return false;
	}
	
	//ultimate member profile page link
	function um_pplink(){
		$accpageid 	= um_get_option('core_user');
		return get_permalink($accpageid);
	}

	//profile tab in Ultimate Member tabs
	function profile_tab_in_um( $tabs ) {
		$tabs[800]['profile']['icon'] 	= 'um-faicon-pencil';
		$tabs[800]['profile']['title'] 	= (wac_is_tenant())? 'My tenant profile' : 'Profile Information';
		$tabs[800]['profile']['custom'] = true;
		
		// print_r($tabs);
		return $tabs;
	}

	//profile tab content and output
	function um_account_tab_profile( $info ) {
		global $ultimatemember;
		extract( $info );

		$output = $ultimatemember->account->get_tab_output('profile');
		if ( $output ) { echo $output; }
	}

	//profile tab content and output on account page
	function um_account_content_hook_profile( $output ){
		ob_start();
		?>
		<div class="um-account-heading uimob340-hide uimob500-hide">
			<i class="um-icon-ios-asterisk"></i>
			How to win your prefered new home.
		</div>	
		<div class="um-field">
			<?php 
				echo $this->tenantSettingsView();
			?>
		</div>
	  
		<?php
			
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	//load tenant profile view section
	function tenantSettingsView(){
		$tenant_vprofileShortcode = wac_setting_field('tenant_vprofile');
		return apply_filters('the_content',$tenant_vprofileShortcode);
	}
	
	//tenant profile view settings Shortcode
	function tenant_profile_settings(){
		ob_start();
		
		$args = array(
			'id' 		=> 'profile',
			'title' 	=> 'Profile Information',
			'current_tab' => 'profile',
			'view_content' => $this->tenantSettingsView()
		);
		wac_get_template('shortcode/tenant-profile/view.php',$args);
			
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	//scripts in footer
	function footer_scripts(){
		if($this->is_umprofilepage()){
			?>
			<script>
			(function($){
				$('.wac-profile-page .um-account-link, .um-um_account_id .um-account-link, .wac-profile-page .um-account-nav > a, .um-um_account_id .um-account-nav > a').click(function(e){
					
					var $tab = $(this).data('tab');
					
					//skip for password and general tab.
					if($('.um-um_account_id').size() > 0 && ($tab == 'password' || $tab == 'general')){
						return true;
					}
					
					e.preventDefault();
					
					if($tab == 'profile'){
						window.location = '<?php echo $this->um_pplink(); ?>';
						return false;
					}
					else{
						window.location = $(this).attr('href');
						return false;
					}
				});
			})(jQuery);
			</script>
			<?php
		}
	}
	
}