<?php

/**
 * Shortcode to display start auction form
 */
 
 class Woo_Auction_Start_Shortcode {

	public function __construct() {
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_scripts'),99);
		add_action('wp_ajax_woo_auction_add_new', array( &$this, 'ajax_add_auction' ) );
		add_action('wp_ajax_woo_auction_weblink_title', array( &$this, 'getWeblinkData' ) );
		//add_action('wp_ajax_woo_auction_save_images', array( &$this, 'ajaxImportImages' ) );
		add_shortcode('woo-auction-form', array(&$this, 'auction_shortcode'));
		
		add_action( 'wp_print_scripts', array($this,'removeScripts'), 100 );
	}
	
	//remove scripts and enqueue new
	function removeScripts(){
		if(!is_admin() && $this->is_startauctionpage()){
			wp_dequeue_script('upload_js');
		}
	}
	
	//if start auction page
	function is_startauctionpage(){

		$aucpageid  = wac_setting_field('auction_page');
		$pageObject = get_queried_object();
		

		if(is_object($pageObject) && isset($pageObject->ID)){
			
			$parent_post_id = wac_translated_id($pageObject->ID);
			if($aucpageid == $parent_post_id){
				return true;
			}
		}
		return false;
	}
	
	//load scripts on auction form
	function frontend_scripts(){
		if($this->is_startauctionpage()){
			wp_enqueue_script('woo-media-upload', WOO_AUCTION_URL.'/assets/js/woo-media-upload.js', array('jquery'), '1.0', true);
		}
	}
	
	//get agreement link set in the settings
	function get_agreementlink(){
		$agreeLinkID = wac_setting_field('agreelink');
		$agreeLink 	 = (!empty($agreeLinkID))? get_permalink($agreeLinkID) : '';
		return $agreeLink;
	}

	//get link data on Fetch click (ajax)
	function getWeblinkData(){
		$url = $_POST['geturl'];
		$woo_auction_parsesite = new Woo_Auction_Parsesite();
		if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
			$fetched = array('success'=>false,'msg'=>['Not a valid URL, Type full URL with http:// or https://']);
		}
		else{
			$parse = parse_url($url);
			$domain = $parse['host'];
			if(strpos($domain,'funda.nl') !== false){
				$siteData = $woo_auction_parsesite->fundaSiteData($url);
				if(!empty($siteData['title'])){
					$fetched = array('success'=>true,'data'=> $siteData,'domain'=>$domain);
				}
				else{
					$fetched = array('success'=>false,'msg'=> ['Property Not Found!'],'domain'=>$domain);
				}
			}
			elseif(strpos($domain,'pararius.nl') !== false || strpos($domain,'pararius.com') !== false){
				$siteData = $woo_auction_parsesite->parariusSiteData($url);
				if(!empty($siteData['title'])){
					$fetched = array('success'=>true,'data'=> $siteData,'domain'=>$domain);
				}
				else{
					$fetched = array('success'=>false,'msg'=> ['Property Not Found!'],'domain'=>$domain);
				}
			}
			else{
				$fundalink = 'https://funda.nl';
				$parariuslink = 'https://pararius.nl';
				$msg = "You can use any weblink of the listing, but you can only import information of the listing from ";
				$msg .= "<a href='{$fundalink}' target='_blank'>Funda</a> or ";
				$msg .= "<a href='{$parariuslink}' target='_blank'>Pararius</a>";
				$msg .= ".";
				$fetched = array('false'=>true,'msg'=>[$msg]);
			}
		}		
		echo json_encode($fetched); die;
	}
	
	/**
	 * Output the Frontend Product Manager shortcode.
	 */
	public function auction_shortcode( $attr ) {
		
		if( !is_user_logged_in() ) {
			echo __('You do not have enough permission to access this page. Please logged in first.','woo-auction');
    	return;
		}
		
		$current_vendor_id = apply_filters( 'wcmp_current_loggedin_vendor_id', get_current_user_id() );
		
		// If vendor does not have product submission cap then show message
		if( is_user_logged_in() && is_user_wcmp_vendor( $current_vendor_id ) && !current_user_can('edit_products') ) {
			echo __('You do not have enough permission to submit a new product. Please contact site administrator.','woo-auction');
			return;
		}
		
		$featured_img = '';
		$product_categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0&parent=0' );
		$product_categories   = apply_filters( 'wcmp_frontend_product_cat_filter', $product_categories );
		
		//include auction form
		return wac_get_template('shortcode/auction-form.php',array('product_categories'=>$product_categories,'wac_startauc'=>$this));
	}
	
	//add auction (product) in woocommerce (ajax)
	function ajax_add_auction(){
		global $wpdb, $WCMp, $WCMp_Frontend_Product_Manager, $woo_auction;
		
		$wacStrings = $woo_auction->wacStrings;
		$product_manager_form_data = array();
		parse_str($_POST['product_manager_form'], $product_manager_form_data);
		// print_r($product_manager_form_data); die;
		$WCMp_fpm_messages = get_forntend_product_manager_messages();
		$has_error = false;
		if(isset($product_manager_form_data['title']) && !empty($product_manager_form_data['title'])) {
			$is_update = false;
			$is_publish = false;
			$is_vendor = false;
			
			$current_user_id = $vendor_id = apply_filters( 'wcmp_current_loggedin_vendor_id', get_current_user_id() );
			if( is_user_wcmp_vendor( $current_user_id ) ) $is_vendor = true;
			
			//check if saved directly from link
			$weblink = (isset($product_manager_form_data['weblink']))? $product_manager_form_data['weblink'] : false;
			$fromdirectlink = (isset($product_manager_form_data['fromsite']))? $product_manager_form_data['fromsite'] : false;
			
			if(isset($_POST['status']) && ($_POST['status'] == 'draft')) {
				$product_status = 'draft';
			} 
			else {
				if( $is_vendor ) {	
					if(!current_user_can('publish_products')){
						$product_status = 'pending';
					} else {
						$product_status = 'publish';
					}	  		
				} else {
					$product_status = 'publish';
				}
			}
			
			// Creating new product
			$description = stripslashes( html_entity_decode( $_POST['description'], ENT_QUOTES, 'UTF-8') );
			$comment = stripslashes( html_entity_decode( $_POST['comment'], ENT_QUOTES, 'UTF-8') );
			
			$tenant_req_desc = $product_manager_form_data['tenant_requirement'];
			$new_product = array(
				'post_title'   => wc_clean( $product_manager_form_data['title'] ),
				'post_status'  => $product_status,
				'post_type'    => 'product',
				'post_excerpt' => '',
				'post_content' => '',
				'post_author'  => $vendor_id
				//'post_name' => sanitize_title($product_manager_form_data['title'])
			);
			
			if(isset($product_manager_form_data['pro_id']) && $product_manager_form_data['pro_id'] == 0) {
				if ($product_status != 'draft') {
					$is_publish = true;
				}
				// print_r($new_product);
				$new_product_id = wp_insert_post( $new_product, true );
				
				if( is_wp_error( $new_product_id ) ) {
					$response = ["status"=>false, "message"=>$new_product_id->get_error_message()];
					$this->jsonResult($response);
				}
				
			} else { // For Update
				$is_update = true;
				$new_product['ID'] = $product_manager_form_data['pro_id'];
				if( !$is_vendor ) unset( $new_product['post_author'] );
				if( get_post_status( $new_product['ID'] ) != 'draft' ) {
					unset( $new_product['post_status'] );
				} else if( (get_post_status( $new_product['ID'] ) == 'draft') && ($product_status != 'draft') ) {
					$is_publish = true;
				}
				$new_product_id = wp_update_post( $new_product, true );
				if( is_wp_error( $new_product_id ) ) {
					$response = ["status"=>false, "message"=>$new_product_id->get_error_message()];
					$this->jsonResult($response);
				}
			}
			
			if(!is_wp_error($new_product_id)) {
				// For Update
				if($is_update) $new_product_id = $product_manager_form_data['pro_id'];
				  
				// Set Product Type
				$product_manager_form_data['product_type'] = 'auction';
				wp_set_object_terms( $new_product_id, $product_manager_form_data['product_type'], 'product_type' );
				
				
				// Process product type first so we have the correct class to run setters.
				$product_type = empty( $product_manager_form_data['product_type'] ) ? WC_Product_Factory::get_product_type( $new_product_id ) : sanitize_title( stripslashes( $product_manager_form_data['product_type'] ) );
				$classname    = WC_Product_Factory::get_product_classname( $new_product_id, $product_type ? $product_type : 'simple' );
				$product      = new $classname( $new_product_id );
				$errors       = $product->set_props( array(
					'sku'                => null,
					'purchase_note'      => '',
					'downloadable'       => false,
					'virtual'            => false,
					//'featured'           => isset( $product_manager_form_data['featured'] ),
					//'catalog_visibility' => wc_clean( $product_manager_form_data['visibility'] ),
					'tax_status'         => null,
					'tax_class'          => null,
					'weight'             => '',
					'length'             => '',
					'width'              => '',
					'height'             => '',
					'shipping_class_id'  => '',
					'sold_individually'  => '',
					'upsell_ids'         => array(),
					'cross_sell_ids'     => array(),
					'regular_price'      => wc_clean( $product_manager_form_data['regular_price'] ),
					'sale_price'         => wc_clean( $product_manager_form_data['sale_price'] ),
					'date_on_sale_from'  => wc_clean( $product_manager_form_data['sale_date_from'] ),
					'date_on_sale_to'    => wc_clean( $product_manager_form_data['sale_date_upto'] ),
					'manage_stock'       => ! empty( $product_manager_form_data['manage_stock'] ),
					'backorders'         => wc_clean( $product_manager_form_data['backorders'] ),
					'stock_status'       => wc_clean( $product_manager_form_data['stock_status'] ),
					'stock_quantity'     => wc_stock_amount( $product_manager_form_data['stock_qty'] ),
					'download_limit'     => '' === $product_manager_form_data['download_limit'] ? '' : absint( $product_manager_form_data['download_limit'] ),
					'download_expiry'    => '' === $product_manager_form_data['download_expiry'] ? '' : absint( $product_manager_form_data['download_expiry'] ),
					'downloads'          => $downloadables,
					'product_url'        => esc_url_raw( $product_manager_form_data['product_url'] ),
					'button_text'        => wc_clean( $product_manager_form_data['button_text'] ),
					'children'           => null,
					'reviews_allowed'    => ! empty( $product_manager_form_data['enable_reviews'] ),
					'menu_order'        => absint( $product_manager_form_data['menu_order'] ),
					'attributes'         => $pro_attributes,
					'default_attributes' => $default_attributes,
				) );
		
				if ( is_wp_error( $errors ) ) {
					$response = ['status'=>false,'message'=>$errors->get_error_message(),'id'=>$new_product_id,'redirect'=>get_permalink($new_product_id)];
					$this->jsonResult($response);
					$has_error = true;
				}
				
				
				/**
				 * @since 3.0.0 to set props before save.
				 */
				//do_action( 'woocommerce_admin_process_product_object', $product );
				$product->save();
				
				// Set Product Category
				if(isset($product_manager_form_data['product_cats']) && !empty($product_manager_form_data['product_cats'])) {
					foreach($product_manager_form_data['product_cats'] as $product_cats) {
						if(is_numeric($product_cats)){
							wp_set_object_terms( $new_product_id, (int)$product_cats, 'product_cat' );
						}
						else{
							wp_set_object_terms( $new_product_id, $product_cats, 'product_cat' );
						}
					}
				}
				
				// Set Product Custom Taxonomies
				if(isset($product_manager_form_data['product_custom_taxonomies']) && !empty($product_manager_form_data['product_custom_taxonomies'])) {
					foreach($product_manager_form_data['product_custom_taxonomies'] as $taxonomy => $taxonomy_values) {
						if( !empty( $taxonomy_values ) ) {
							$is_first = true;
							foreach( $taxonomy_values as $taxonomy_value ) {
								if($is_first) {
									$is_first = false;
									wp_set_object_terms( $new_product_id, (int)$taxonomy_value, $taxonomy );
								} else {
									wp_set_object_terms( $new_product_id, (int)$taxonomy_value, $taxonomy, true );
								}
							}
						}
					}
				}
				
				// Set Product Tags
				if(isset($product_manager_form_data['product_tags']) && !empty($product_manager_form_data['product_tags'])) {
					wp_set_post_terms( $new_product_id, $product_manager_form_data['product_tags'], 'product_tag' );
				}
				
				// Set Product Featured Image
				$wp_upload_dir = wp_upload_dir();
				if(isset($product_manager_form_data['featured_img']) && !empty($product_manager_form_data['featured_img'])) {
					if($fromdirectlink){
						$featured_img_id = $this->importImages($new_product_id, $product_manager_form_data['featured_img']);
					}
					else{
						$featured_img_id = $this->wac_get_image_id($product_manager_form_data['featured_img']);
						set_post_thumbnail( $new_product_id, $featured_img_id );
					}
				} else {
					delete_post_thumbnail( $new_product_id );
				}
				
				// Set Product Image Gallery
				if(isset($product_manager_form_data['gallery_img']) && !empty($product_manager_form_data['gallery_img'])) {
					$gallery = array();
					foreach($product_manager_form_data['gallery_img'] as $gallery_imgs) {
						if(isset($gallery_imgs['image']) && !empty($gallery_imgs['image'])) {
							$gallery_img_id = $this->wac_get_image_id($gallery_imgs['image']);
							$gallery[] = $gallery_img_id;
						}
					}
					if ( ! empty( $gallery ) ) {
						update_post_meta( $new_product_id, '_product_image_gallery', implode( ',', $gallery ) );
					}
				}
				
				//save auction meta
				update_post_meta( $new_product_id, '_auction_available_from', $product_manager_form_data['available_from'] );
				update_post_meta( $new_product_id, '_auction_deposit', $product_manager_form_data['deposit'] );
				update_post_meta( $new_product_id, '_auction_rental_period', $product_manager_form_data['rental_period'] );
				update_post_meta( $new_product_id, '_auction_tenant_req', $tenant_req_desc );
				update_post_meta( $new_product_id, '_auction_additional_info', $description );
				update_post_meta( $new_product_id, '_auction_comment', $product_manager_form_data['comment'] );
				
				//add reserve price as listing price
				update_post_meta( $new_product_id, '_yith_auction_reserve_price', $product_manager_form_data['_yith_auction_listing_price'] );

				//set if auction has minimum price (Status)
				$minprice = isset($product_manager_form_data['minprice'])? 1 : 0;
				update_post_meta( $new_product_id, '_auction_minprice_status', $minprice );
				
				//add weblink and fromlink
				if($weblink){
					update_post_meta( $new_product_id, '_auction_weblink', $weblink );
				}
				if($fromdirectlink){
					update_post_meta( $new_product_id, '_auction_fromlink', $fromdirectlink );
				}
				
				// Yoast SEO Support
				if(WCMp_Frontend_Product_Manager_Dependencies::fpm_yoast_plugin_active_check()) {
					if(isset($product_manager_form_data['yoast_wpseo_focuskw_text_input'])) {
						update_post_meta( $new_product_id, '_yoast_wpseo_focuskw_text_input', $product_manager_form_data['yoast_wpseo_focuskw_text_input'] );
						update_post_meta( $new_product_id, '_yoast_wpseo_focuskw', $product_manager_form_data['yoast_wpseo_focuskw_text_input'] );
					}
					if(isset($product_manager_form_data['yoast_wpseo_metadesc'])) {
						update_post_meta( $new_product_id, '_yoast_wpseo_metadesc', strip_tags( $product_manager_form_data['yoast_wpseo_metadesc'] ) );
					}
				}
				
				do_action('after_wcmp_fpm_meta_save', $new_product_id, $product_manager_form_data);
				
				// Set Product Vendor Data
				if( $is_vendor && !$is_update ) {
					$vendor_term = get_user_meta( $current_user_id, '_vendor_term_id', true );
					$term = get_term( $vendor_term , 'dc_vendor_shop' );
					wp_delete_object_term_relationships( $new_product_id, 'dc_vendor_shop' );
					wp_set_post_terms( $new_product_id, $term->name , 'dc_vendor_shop', true );
				}
				
				// Notify Admin on New Product Creation
				if( $is_publish ) {
					$WCMp->product->on_all_status_transitions($product_status, '', get_post($new_product_id));
				}

				//intialized email trigger event
				WC()->mailer();
				do_action('wac_auction_submitted', $new_product_id, $product_manager_form_data);				
				
				if(!$has_error) {
					if( get_post_status($new_product_id) == 'publish' ) {
						$response = ["status"=>true,"message"=>$WCMp_fpm_messages['product_published'],"redirect"=> get_permalink($new_product_id)];
						
					} else {
						$response = ["status"=>true,"message"=>$wacStrings['product_saved'],"redirect"=> add_query_arg('fpm_msg', 'product_saved', add_query_arg('pro_id', $new_product_id, get_forntend_product_manager_page()))];
					}
					$this->jsonResult($response);
				}
				die;
			}
		} else {
			$response = ['status'=>false,'message'=>$WCMp_fpm_messages['no_title']];
			$this->jsonResult($response);
		}
		
		die;
	}
	
	//return json result
	function jsonResult($arr){
		$jsonResult = json_encode($arr);
		die($jsonResult);
	}
	
	//get image id by URL
	function wac_get_image_id($attachment_url) {
		global $wpdb;
		$upload_dir_paths = wp_upload_dir();
		
		if( class_exists('WPH') ) {
			global $wph;
			$new_upload_path = $wph->functions->get_module_item_setting('new_upload_path');
			$attachment_url = str_replace( $new_upload_path, 'wp-content/uploads', $attachment_url );
		}
		
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
		
			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
			
			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
		}
		return $attachment_id; 
	}
	
	//ajax import images
	function ajaxImportImages(){
		$images = $_POST['images'];
		
		if(!empty($images)){
			$mainImage = current($images);
			if(sizeof($images) == 1){
				$returned = $this->importImages(0,$mainImage);
			}
			else{
				unset($images[0]);
				$returned = $this->importImages(0,$mainImage,$images);
			}
			$fetched = array('success'=>true,'data'=> $returned);
		}
		else{
			$fetched = array('success'=>false,'msg'=> 'No image updated!');
		}
		echo json_encode($fetched);die;
	}
	
	/*
	 * Import current product images into wordpress
	 */
	public function importImages($pid=0,$image,$gallery=array()){

		$this->pID = $pid;
		$featuredImg = $galleryImgs = false;
		
		//single file
		if(!empty($image)){
			$featureImage = explode('?',$image);
			$featureImage = $featureImage[0];
			$this->featured_image = $featureImage;
			
			if(!empty($this->featured_image)){
				$featuredImg = $this->save_featured_image();
			}
		}
		
		//product Gallery
		if(!empty($gallery)){
			$productGallery = $gallery;
			$productGalleryImgs = array();
			if(isset($productGallery) && is_array($productGallery)){
				foreach($productGallery as $productGalleryImage){
					$productGImgSrc = explode('?',$productGalleryImage);
					$productGImgSrc = $productGImgSrc[0];
					array_push($productGalleryImgs,$productGImgSrc);
				}
			}
			$this->product_gallery = $productGalleryImgs;
			
			if(sizeof($this->product_gallery) > 0){
				$galleryImgs = $this->save_product_gallery();
			}
		}
		
		return array('featured'=>$featuredImg,'galleryimgs'=>$galleryImgs);
	}
	
	
	/*
	 * Save product featured image
	 */
	public function save_featured_image(){
		
		$imageID = false;
		$imgName = pathinfo($this->featured_image);
		$imageExists = $this->checkIfImageExists($imgName['filename']);
		
		if($imageExists){
			$imageID = $imageExists;
		}
		elseif($this->is_valid_url($this->featured_image)){
			$imageID = $this->save_image_with_url($this->featured_image);
		}
		
		if ($imageID && $this->pID != 0)
			set_post_thumbnail( $this->pID, $imageID );	
		
		return $imageID;
	}

	
	/*
	 * Save product Gallery Images
	 */
	public function save_product_gallery(){	
	
		$post_id = $this->pID;
		$images = $this->product_gallery;
		$gallery = (isset($gallery))? array() : false;
		foreach ($images as $image) {
			
			$imgName = pathinfo($image);
			$imageExists = $this->checkIfImageExists($imgName['filename']);
			
			if($imageExists){
				$imageID = $imageExists;
			}
			elseif($this->is_valid_url($image)){
				$imageID = $this->save_image_with_url($image);
			}
			
			if ($imageID)
				$gallery[] = $imageID;
		}
		
		$meta_value = implode(',', $gallery);
		
		return $meta_value;
		/* if ($gallery) {
			$meta_value = implode(',', $gallery);
			update_post_meta($post_id, '_product_image_gallery', $meta_value);
		}
		else{
			update_post_meta($post_id, '_product_image_gallery', '');
			// delete_post_meta($post_id, '_product_image_gallery');
		}
		 */
	}
	
	
	/*
	 * Download image and assigned to products
	 */
	function save_image_with_url($url) {
		
		$tmp = download_url( $url , 10 );
		$post_id = $this->pID;
		$desc = "";
		$file_array = array();
		$id = false;
	
		// Set variables for storage
		// fix file filename for query strings
		@preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
		if (!$matches) {
			return $id;			
		}
		
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $tmp;
		$desc = $file_array['name'];
		
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
			return $id;
		}
	
		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		
		if(is_wp_error($id)){
			echo $id->get_error_message(); exit;
		}
	
		// If error storing permanently, unlink
		if ( is_wp_error($id) ) {
			@unlink($file_array['tmp_name']);
			return $id;
		}
		
		return $id;
	}

	
	/*
	 * Check if Image already added
	 */
	public function checkIfImageExists($image){
		
		global $wpdb;

		/* use  get_posts to retreive image instead of query direct!*/
		
		//set up the args
		$args = array(
            'numberposts'	=> 1,
            'orderby'		=> 'post_date',
			'order'			=> 'DESC',
            'post_type'		=> 'attachment',
            'post_mime_type'=> 'image',
            'post_status' 	=>'any',
		    'meta_query' => array(
		        array(
		            'key' => '_wp_attached_file',
		            'value' => sanitize_file_name($image),
		            'compare' => 'LIKE'
		        )
		    )
		);
		//get the images
        $images = get_posts($args);

        if (!empty($images)) {
        //we found a match, return it!
	        return (int)$images[0]->ID;
        } else {
        //no image found with the same name, return false
	        return false;
        }
		
	}

	/*
	 * @helper
	 * Check if given url is valid!
	 */
	public function is_valid_url($url){
		// alternative way to check for a valid url
		if  (filter_var($url, FILTER_VALIDATE_URL) === FALSE) return false; else return true;

	}
}