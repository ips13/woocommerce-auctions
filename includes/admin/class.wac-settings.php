<?php

class WOO_Auction_SettingsPage{
	
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $nameKeys = array();
	

    /**
     * Start up
     */
    public function __construct(){
		$this->setOptionsName();
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_footer', array( $this, 'adminfooterscripts' ),10 );
    }
	
	/*
	 * Set the options name
	 * @array $this->nameKeys
	 * Sections, groups.
	 */
	public function setOptionsName(){
		$this->nameKeys = array(
			'maintitle'		=> 'Woo Auctions',
			'menu' => array(
				'title' 		=> 'Woo Auctions',
				'slug'			=> 'woo-auction-settings',
				'pagetitle'		=> 'Woo Auction Settings',
				'permissions' 	=> 'manage_options',
				'subof' 		=> 'edit.php?post_type=product'
			),
			'option' 	=> 'woo_auction_options',
			'group'		=> 'wooauc_option_group',
			'sections'	=> array(
				array(
					'id'	=> 'wooauc_section_developers',
					'name'	=> 'wooauc-setting-admin',
					'title'	=> 'Woo Auction settings *(Required)*'
				),
				array(
					'id'	=> 'wooauc_section_mollie_gateway',
					'name'	=> 'wooauc-setting-mollie',
					'title'	=> 'Mollie Integration *(Required)*'
				)
			)
		);
	}
	
	/**
	 * All setting fields for Woocommerce Auction Sections
	 */
	public function setting_fields($key = ''){
		
		//all settings fields for Woocommerce Auction option page
		$setting_fields = array(
			'wooauc_section_developers' => array(
				array('name'=>'commission_multi', 	'type'=>'number',     	'title'=>'Commission Mulitply'),
				array('name'=>'auction_page', 		'type'=>'pagelist',     'title'=>'Start Auction Page'),
				array('name'=>'agreelink', 			'type'=>'pagelist',     'title'=>'Auction rules Link'),
				array('name'=>'howtobid', 			'type'=>'pagelist',     'title'=>'How to bid?'),
				array('name'=>'vpage_dashboard', 	'type'=>'pagelist',     'title'=>'Landlord Dashboard Page'),
				array('name'=>'vauction_perpage', 	'type'=>'number',     	'title'=>'Landlord Auctions per page'),
				array('name'=>'allowed_bids', 		'type'=>'number',     	'title'=>'Bids Per User'),
				array('name'=>'tenant_vprofile', 	'type'=>'textarea',     'title'=>'Tenant Profile View'),
				// array('name'=>'options', 			'type'=>'select',     	'title'=>'Commission Mulitply*', 'options' => [1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five'])
			),
			
			'wooauc_section_mollie_gateway' => array(
				array('name'=>'mollie_enabled', 	'type'=>'select',     	'title'=>'Mollie Enabled', 	'options'=>[1=>'Yes',0=>'No']),
				array('name'=>'mollie_mode', 		'type'=>'select',     	'title'=>'Mollie Mode', 	'options'=>['live'=>'Live','sandbox'=>'Sandbox']),
				array('name'=>'mollie_apikey', 		'type'=>'text',     	'title'=>'Mollie API key'),
				array('name'=>'mollie_thanks', 		'type'=>'pagelist',     'title'=>'Thankyou Page'),
			)
		);
			
		if($key != ''){
			//check if Section fields available
			if(array_key_exists($key,$setting_fields)){
				$setting_fields = $setting_fields[$key];
			}
			else{
				$setting_fields = array();
			}
		}
		
		return $setting_fields;
	}

    /**
     * Add options page
     */
    public function add_plugin_page(){
		
        // This page will be under "Settings"
		add_submenu_page(
			$this->nameKeys['menu']['subof'], 
			$this->nameKeys['menu']['pagetitle'], 
            $this->nameKeys['menu']['title'], 
            $this->nameKeys['menu']['permissions'], 
            $this->nameKeys['menu']['slug'], 
            array( $this, 'create_admin_page' )
		);
    }
	
	/**
	 * Print Settings on settings page dynamically
	 */
	public function setting_fields_print($which,$sectionname){
		
		//all settings fields for Bigcommerce Import option page
		$setting_fields = $this->setting_fields($which);
		
		if(sizeof($setting_fields) > 0){
			foreach($setting_fields as $fields){
				
				$tip = (isset($fields['tip']))? $fields['tip'] : '';
				add_settings_field(
					$fields['name'], 
					$fields['title'], 
					array( $this, $fields['type'].'_field_callback' ), 
					$sectionname, 
					$which,
					['name' => $fields['name'], 'id' => $fields['name'].'_field', 'class' => $fields['type'].'_field', 'title' => $fields['title'], 'tip' => $tip,'options' => @$fields['options']]
				);
			}
		}
		
	}

    /**
     * Options page callback
     */
    public function create_admin_page(){
		
        // Set class property
        $this->options = get_option( $this->nameKeys['option'] );
		
		// add error/update messages		
		// echo '<pre>'.print_r($this->options,true).'</pre>'; 
        ?>
		
        <div class="wrap">
            <h1><?php echo $this->nameKeys['maintitle']; ?></h1>
            <form method="post" action="options.php">
				<?php
					// This prints out all hidden setting fields
					settings_fields( $this->nameKeys['group'] );
						//Load multiple sections
						foreach($this->nameKeys['sections'] as $section){
							do_settings_sections( $section['name'] );
						}
					submit_button();
				?>
            </form>
			
			<div class="response-products"><ul></ul></div>
			
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init(){        
	
        register_setting(
            $this->nameKeys['group'], // Option group
            $this->nameKeys['option'], // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

		//Load multiple sections
		foreach($this->nameKeys['sections'] as $section){
			add_settings_section(
				$section['id'], // ID
				$section['title'], // Title
				array( $this, 'print_section_info' ), // Callback
				$section['name'] // Page
			);  
			
			//Corcrm setting fields
			$this->setting_fields_print($section['id'],$section['name']);
		}
		
		
    }
	
	/*
	 * Print section info after the title.
	 */
	public function print_section_info(){
		
	}

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ){
        $new_input = array();
        
		$settings_fields_section = $this->setting_fields();
		
		foreach($settings_fields_section as $sfield_section){
			foreach($sfield_section as $sfield){
				
				//filter values
				if(isset( $sfield['name'] )){
					switch( $sfield['type'] ){
						case 'text':
							$new_input[$sfield['name']] = sanitize_text_field( $input[$sfield['name']] );
						break;
						case 'number':
							$new_input[$sfield['name']] = $input[$sfield['name']];
						break;
						case 'pagelist':
							$new_input[$sfield['name']] = absint( $input[$sfield['name']] );
						break;
						default:
							$new_input[$sfield['name']] = sanitize_text_field( $input[$sfield['name']] );
						break;
					}
				}
				
			}
		}
		
        return $new_input;
    }
	
	/** 
     * Get the settings option for Text fields in settings
     */
	public function text_field_callback($args){
		printf(
            '<input type="text" id="%s" name="%s['.$args['name'].']" value="%s" />',
            $args['id'],
			$this->nameKeys['option'], 
			isset( $this->options[$args['name']] ) ? esc_attr( $this->options[$args['name']]) : ''
        );
		
		$this->printlabel($args);
	}
	
	/** 
     * Get the settings option for Textarea fields in settings
     */
	public function textarea_field_callback($args){
		printf(
            '<textarea id="%s" name="%s['.$args['name'].']"/>%s</textarea>',
            $args['id'],
			$this->nameKeys['option'], 
			isset( $this->options[$args['name']] ) ? esc_attr( $this->options[$args['name']]) : ''
        );
	}
	
	/** 
     * Get the settings option for Number fields in settings
     */
	public function number_field_callback($args){
		printf(
            '<input type="number" id="%s" name="%s['.$args['name'].']" value="%s" step="0.01"/>',
            $args['id'],
			$this->nameKeys['option'],
			isset( $this->options[$args['name']] ) ? esc_attr( $this->options[$args['name']]) : ''
        );
		
		$this->printlabel($args);
	}
	
	/** 
     * Get the settings option for Number fields in settings
     */
	public function button_field_callback($args){
		
		printf(
            '<button id="%s" class="button button-primary" >%s</button>',
            $args['id'], 
			$args['title']
        );
		
		echo '<span class="spinner"></span>';
		
		$this->printlabel($args);
	}
	
	/** 
     * Select field in settings
     */
	public function select_field_callback($args){
		// [1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five']
		$options 	= $args['options'];
		$fieldname 	= "{$this->nameKeys['option']}[{$args['name']}]";
		$selected 	= @$this->options[$args['name']];
		$selectBox 	= '<select name="'.$fieldname.'">';
		foreach($options as $oKey => $oVal){
			$selectedck = selected($selected,$oKey,0);
			$selectBox .= '<option value="'.$oKey.'" '.$selectedck.'>'.$oVal.'</option>';
		}
		$selectBox .= '</select>';
		echo $selectBox;
	}

	/** 
     * Display page list
     */
	public function pagelist_field_callback($args){
		$fieldname = "{$this->nameKeys['option']}[{$args['name']}]";
		$adArgs = array(
			'id'	=> $args['id'],
			'name' 	=> $fieldname,
			'selected' => @$this->options[$args['name']]
		);
		
		return wp_dropdown_pages($adArgs);
	}
	
	/*
	 * Print Description for the setting field
	 */
	public function printlabel($args){
		if($args['tip'] != ''){
			printf(
				'<p class="description">%s</p>',$args['tip']
			);
		}
	}
	
	
	/*
	 * @ajax
	 * @admin footer
	 * Add scripts to admin footer
	 */
	public function adminfooterscripts(){
		
		//call style and scripts in bottom of the page of Woocommerce Auctions
		if(isset($_GET['page']) && $_GET['page'] == 'woo-auction-settings'){
		
			$woo_auction_options =  get_option('woo_auction_options');
			?>
				<style>
					tr.text_field input,tr.number_field input,tr.pagelist_field select,tr.textarea_field textarea,tr.select_field select {min-width: 50%;}
					tr.textarea_field textarea{min-height: 100px}
				</style>
			<?php
		}
	}
}