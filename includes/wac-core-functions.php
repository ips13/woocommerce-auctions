<?php
//language translation function
function __wac($txt){
	return __($txt,'woo-auction');
}

//print array
function __wac_pre($arr){
	global $woo_auction;
	return $woo_auction->pre($arr);
}

//locate template in folder
function wac_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	global $woo_auction;
	if(!$template_path){
		$template_path = $woo_auction->theme_template_path();
	}
	if(!$default_path){
		$default_path  = $woo_auction->plugin_path() . '/templates/';
	}
	
	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit($template_path) . $template_name,
			$template_name,
		)
	);
	
	// Get default template/
	if (!$template){
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters('wac_locate_template',$template,$template_name,$template_path );
}

//get file from templates
function wac_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if (!empty($args) && is_array($args)){
		extract($args);
	}

	$located = wac_locate_template($template_name,$template_path,$default_path );

	if (!file_exists($located)){
		wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.','woo-auction' ), '<code>' . $located . '</code>' ), '2.1' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters('wac_get_template',$located,$template_name,$args,$template_path,$default_path);
	
	include($located);
}

//remove hooks are loaded through classes
function remove_anonymous_object_filter( $tag, $class, $method ){
	$filters = isset($GLOBALS['wp_filter'][ $tag ])? $GLOBALS['wp_filter'][ $tag ] : false;

	if (empty($filters)){
		return;
	}

	foreach ( $filters as $priority => $filter )
	{
		foreach ( $filter as $identifier => $function )
		{
			if ( is_array( $function)
				and is_a( $function['function'][0], $class )
				and $method === $function['function'][1]
			)
			{
				remove_filter(
					$tag,
					array ( $function['function'][0], $method ),
					$priority
				);
			}
		}
	}
}

//convert break to new line
function br2nl($string){
	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}  

//date time differences
function wac_datetimedifference( $d1,$d2 ){
	$datetime1 = new DateTime($d1);
	$datetime2 = new DateTime($d2);
	return $datetime1->diff($datetime2);
}

//get time left from now to pass string
function wac_timeleft($t1,$t2='now'){
	$timenow 	= ($t2 == 'now')? strtotime('now') : $t2;
	$timeDiff 	= round(abs($t1 - $t2) / 60);
	
	return $timeDiff;
}

//date time difference from timeleft string
function wac_datetimeleft($totaltime){
	$seconds = floor($totaltime);
    $minutes = floor($seconds / 60);
    $hours 	 = floor($minutes / 60);
    $days 	 = floor($hours / 24);
	
	$hours  %= 24;
    $minutes %= 60;
    $seconds %= 60;
	  
	return ['d'=>$days,'h'=>$hours,'m'=>$minutes,'s'=>$seconds];
}

//get setting fields
function wac_setting_field($key){
	$settings =  get_option('woo_auction_options');
	return $settings[$key];
}

//display vendor full name if set firstname and lastname
function wac_vendor_name($user){
	$firstname = get_user_meta($user->ID,'first_name',true);
	$lastname  = get_user_meta($user->ID,'last_name',true);
	
	$fullName = $user->data->user_nicename;
	if(!empty($firstname)){
		$fullName = trim($firstname.' '.$lastname);
	}
	return $fullName;
}

//get company name of vendor
function wac_vendor_company($user){
	$company = get_user_meta($user->ID,'_vendor_company',true);
	if(!empty($company)){
		return $company;
	}
	
	return wac_vendor_name($user);
}

//get auction link
function wac_auctionlink($pid){
	$weblink  = get_post_meta($pid,'_auction_weblink',true);
	$fromlink = get_post_meta($pid,'_auction_fromlink',true);
	if($weblink){
		return $weblink;
	}
	return $fromlink;
}

//create link from text string
function wac_create_link($string) {
	$url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
	return preg_replace($url, 'http$2://$4', $string);
}

//default gravatar image
function wac_default_gravatar(){
	return WOO_AUCTION_URL . '/assets/img/default_avatar.jpg';
}

//check if user is vendor
function wac_is_vendor($uid=''){
	$cUID = (!empty($uid))? $uid : get_current_user_id();
	if(is_user_wcmp_vendor( $cUID )){
		return true;
	}
	return false;
}

//check if user is tenant
function wac_is_tenant($user=''){
	 if ($user && !empty($user)) {
		if (!is_object($user)) {
			$user = new WP_User(absint($user));
		}
		return (is_array($user->roles) && in_array('subscriber', $user->roles));
	} else {
		return false;
	}
}

//if only current vendor allowed to see product
function wac_only_current_vendor_view($product_id){
	if(wac_is_vendor()){
		$cUID = get_current_user_id();
		$vendor_data = get_wcmp_product_vendors($product_id);
		if($vendor_data->id == $cUID){
			return true;
		}
	}
	return false;
}

//display help icon
function wac_ihelp($txt){
	return "<span class='um-tip um-tip-w' original-title='{$txt}'><i class='um-icon-information-circled'></i></span>";
}

//allowed bids per user
function wac_allowed_bids(){
	$allowed = wac_setting_field('allowed_bids');
	return (empty($allowed) || $allowed <= 0)? 0 : $allowed;
}

//function wac price
function wac_price($prc){
	return wc_price($prc,['decimals'=>2]);
}

//get all translations
function wac_translations($post_id){
	global $sitepress;
	$trid = $sitepress->get_element_trid($post_id);
	$translations = $sitepress->get_element_translations($trid);
	if(is_array($translations) && !empty($translations)){
		return $translations;
	}
	return [];
}

//get nl site product id
function wac_NL_id($post_id,$post_type='product'){
	if(function_exists('icl_object_id')){
		return icl_object_id($post_id,$post_type,false,'nl');
	}
	return;
}

//translated id
function wac_translated_id($postId,$post_type='page'){
	if(function_exists('icl_object_id')){
		return icl_object_id( $postId, $post_type, false, ICL_LANGUAGE_CODE ); // Returns the ID of the current custom post
	}
	return;
}

//wac logs
function wac_log($log){
	$ds 	= DIRECTORY_SEPARATOR;
	$path 	= WP_CONTENT_DIR.$ds.'wac_debug.log';
	$now	= date('d-m-y H:i:s').']: ';
	$break 	= "\n";
	
	error_log( $now.$log.$break, 3, $path );
	
	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( print_r( $log, true ), 3, $path );
	} 
}

//datable strings
function __datatable_lng(){
	global $woo_auction;
	$wacStrings = $woo_auction->wacStrings;
	return json_encode($wacStrings['datatable']);
}

function wac_yesno_switch($attr){
	$attr = shortcode_atts(
		array(
			'name'  => 'input_name',
			'class'	=> 'yesno-switch'
		),
		$attr
	);
	extract($attr);
	ob_start();
		?>
		<span class="yes_no <?php echo $class; ?>">
		    <?php echo "<input type='checkbox' name='{$name}' value='1'>"; ?>
		    <label for="start">
		        <span class="yes"><?php echo __('Yes','woo-auction'); ?></span>
		        <span>|||</span>
		        <span class="no"><?php echo __('No','woo-auction'); ?></span>
		    </label>
		</span>
		<?php
	return ob_get_clean();
}