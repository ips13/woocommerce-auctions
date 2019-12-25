<?php

class Woo_Auction_Admin{
	
	function __construct(){
		add_action( 'yith_after_auction_tab', array($this,'woo_auctions_fields'),13);
		add_action( 'woocommerce_product_write_panel_tabs', array($this,'auction_weblink_field'));
		add_action('save_post', array($this,'on_save_auction_actions'),99);

		add_action( 'show_user_profile', array($this,'vendor_info_fields') );
		add_action( 'edit_user_profile', array($this,'vendor_info_fields') );
		add_action( 'profile_update', array($this,'save_vendor_info_fields') );

		// Add column product in users dashboard
		add_action( 'init', array($this,'add_remove_actions'), 13 );
        add_filter('manage_users_columns', array(&$this, 'column_register_auction'));
        add_filter('manage_users_custom_column', array(&$this, 'column_display_auction'), 10, 3);

        //add columns in product
        add_filter( 'manage_edit-product_columns', array($this,'custom_product_billing_status'),9);
        add_filter( 'manage_edit-product_sortable_columns', array($this,'custom_product_columns_sortable') );

		add_action( 'manage_product_posts_custom_column', array($this,'custom_product_list_column_content'),99,2);
	}

	//remove actions
	function add_remove_actions(){
		remove_anonymous_object_filter( 'manage_users_columns', 'WCMp_User','column_register_product');
		remove_anonymous_object_filter( 'manage_users_custom_column', 'WCMp_User','column_display_product');
	}
	
	//display extra auction fields
	function woo_auctions_fields($post_id){
		
		$product = wc_get_product($post_id);
		$tenReq  = yit_get_prop($product, '_auction_tenant_req', true);
		$addInfo = yit_get_prop($product, '_auction_additional_info', true);
		$aucComn = yit_get_prop($product, '_auction_comment', true);
		

		echo '<div class="options_group">';
		echo '<hr>';
		woocommerce_wp_text_input(array(
            'id'   	=> '_auction_deposit',
            'name' 	=> 'wac_data[_auction_deposit]',
            'class' => 'wc_input_price short',
            'label' => __('Deposit in Euro', 'woo-auction'). ' (' . get_woocommerce_currency_symbol() . ')',
            'value' => yit_get_prop($product, '_auction_deposit', true),
            'data_type' => 'decimal',
            'description' => __('Deposit in Euro', 'woo-auction'),
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        ));

        woocommerce_wp_text_input( array(
            'id'                => 'available-from',
            'name'              => 'wac_data[_auction_available_from]',
            'class'             => 'wc_auction_datepicker',
            'label'             => __( 'Object Available From', 'woo-auction' ),
            'value'             => yit_get_prop($product, '_auction_available_from',true),
            'data_type'         => 'date'
        ));

        woocommerce_wp_select( array(
			'id' 			=> 'rental-period',
			'name'          => 'wac_data[_auction_rental_period]',
            'class'         => 'woocommerce-Select regular-select',
			'label' 		=> __('Rental Period', 'woo-auction'),
			'options' 		=> array('' => 'Select Rental Period', 'Indefinite' => 'Indefinite', '1 Year' => '1 Year', '2 Years' => '2 Years', 'Additional Information' => 'Otherwise, additional information'),
			'value'         => yit_get_prop($product, '_auction_rental_period',true)
			)
		);
		
		woocommerce_wp_textarea_input( array(
			'id'          => '_auction_tenant_req',
			'name'        => 'wac_data[_auction_tenant_req]',
			'value'       => br2nl($tenReq),
			'label'       => __( 'Tenant Requirements','woo-auction' ),
			'desc_tip'    => false,
			'description' => __( 'Tenant Requirements for auction.','woo-auction' ),
		) );
		
		woocommerce_wp_textarea_input( array(
			'id'          => '_auction_additional_info',
			'name'        => 'wac_data[_auction_additional_info]',
			'value'       => br2nl($addInfo),
			'label'       => __( 'Additional Information','woo-auction' ),
			'desc_tip'    => false,
			'description' => __( 'Additional Information for auction.','woo-auction' ),
		) );
		
		woocommerce_wp_textarea_input( array(
			'id'          => '_auction_comment',
			'name'        => 'wac_data[_auction_comment]',
			'value'       => br2nl($aucComn),
			'label'       => __( 'Auction Comment','woo-auction' ),
			'desc_tip'    => false,
			'description' => __( 'Comment for auction.','woo-auction' ),
		) );
		
		echo '</div>';
		
	}
	
	//add weblink field
	function auction_weblink_field(){
		global $post;
		$weblink = wac_auctionlink($post->ID);
		?>
		<script>
			jQuery(document).ready(function($) {
				var prType = $('#product-type').val();
				if(prType == 'auction'){
					var weblinkfield = '<span class="weblink"><input type="text" name="wac_data[_auction_weblink]" value="<?php echo $weblink; ?>" placeholder="Weblink"></span>';
					$(weblinkfield).insertAfter("#product-type");
					$('.weblink input').height($('#product-type').val());

					//set field in backend as well
					$('#available-from').datepicker({ minDate: 0, dateFormat: 'dd-mm-yy' }).val();
				}
			});
			
			<?php if(isset($_GET['mindays'])): ?>
				var wac_mindays = <?php echo $_GET['mindays']; ?>;
			<?php else: ?>
				var wac_mindays = 0;
			<?php endif; ?>
		</script>
		<style>
			.weblink{padding-left: 10px}
			#_auction_tenant_req, #_auction_additional_info{width: 100%}
		</style>
		<?php
	}
	
	//save fields on save post
	function on_save_auction_actions($post_id){
		$product = wc_get_product($post_id);
		if($product && $product->is_type('auction')){
			
			$wacData = $_POST['wac_data'];
			
			if(is_array($wacData) && !empty($wacData)){
				
				//Dutch language ID.
				$product_id_nl  = wac_NL_id($post_id);

				foreach($wacData as $fieldName => $fieldVal){
					
					if(isset($_POST['wac_data'][$fieldName])){
						update_post_meta($post_id,$fieldName,$fieldVal);

						//save field data for Dutch language
						if(!empty($product_id_nl) && $product_id_nl != 0){
							update_post_meta($product_id_nl,$fieldName,$fieldVal);
						}
					}
				}

			}
			
			//enable inventory management
			$product->set_stock_status('instock');
			$product->save();
		}
	}

	/*
	 * Add Vendor information fields in backend
	 */
	function vendor_info_fields($user){
	  ?>
	    <h3><?php echo __('Vendor Information','woo-auction'); ?></h3>
	    <table class="form-table">
	        <tr>
	            <th><label for="company"><?php echo __('Vendor Company','woo-auction'); ?></label></th>
	            <td>
	                <input type="text" class="regular-text" name="_vendor_company" value="<?php echo esc_attr( get_the_author_meta( '_vendor_company', $user->ID ) ); ?>" id="_vendor_company" /><br />
	                <span class="description"><?php echo __('Vendor/Landlord Company name','woo-auction'); ?></span>
	            </td>
	        </tr>
	    </table>
	  <?php
	}

	/*
	 * Save Vendor information fields in backend
	 */
	function save_vendor_info_fields($user_id){
	    # again do this only if you can
	    if(!current_user_can('manage_options'))
	        return false;

	    update_usermeta($user_id, '_vendor_company', $_POST['_vendor_company']);
	}

	/**
     * ADD auction column on user dashboard
     */
    function column_register_auction($columns) {
        $columns['auction'] = __('Auctions', 'woo-auction');
        return $columns;
    }

    /**
     * Display Auction column on user dashboard
     */
    function column_display_auction($empty, $column_name, $user_id) {
        if ('auction' != $column_name) {
            return $empty;
        }
        global $woo_auction;
		$wacproduct = $woo_auction->product;
        $vendor = get_wcmp_vendor($user_id);
        $product_count = $wacproduct->get_auctions_by_user($user_id,array('post_status'=>'any'));
        if ($vendor) {
            $product_count = count($product_count);
            return "<a href='edit.php?post_type=product&dc_vendor_shop=" . $vendor->user_data->user_login . "'><strong>{$product_count}</strong></a>";
        } else {
            return "<strong></strong>";
        }
    }

    //adding new columns in product listing
	function custom_product_billing_status($columns){
	   $columns['billing_status'] 	 = __( 'Billing Status','woo-auction'); 
	   $columns['amount_commission'] = __( 'Amount Commission','woo-auction'); 
	   return $columns;
	}

	//columns sortable
	function custom_product_columns_sortable($cols){
		$cols['billing_status'] 		= 'billing_status';
		$cols['amount_commission'] 	= 'amount_commission';
		return $cols;
	}

	function custom_product_list_column_content( $column, $product_id ){
		global $woo_auction;
		$product = wc_get_product($product_id);
		$wacvendor_profile  = $woo_auction->vendor_profile;
		$wac_commission 	= $woo_auction->commission;
		
	    switch ($column) {	
	        case 'billing_status' :
				if($product->is_type('auction')){
					echo $wacvendor_profile->auction_billing_status($product); 
				}
			break;
			case 'amount_commission' :
				echo $wac_commission->get_commission_price($product_id);
			break;
	    }
	}
}