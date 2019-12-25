<?php
/*
 * Plugin Name: Woocommerce Auction
 * Author: Deftsoft
 * description: Create auctions, manage commissions and frontend profiles (Vendor/Landlord, Tenant).
 * version: 1.0
 * Text Domain: woo-auction
 * Domain Path: /languages/
*/
		
Class Woo_auction{
	
	protected static $_instance = null;
	
	/*
	 * Instance for Singleton method
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/*
	 * Autoload to run plugin code
	 */
	public function __construct() {
		//check if woocommerce plugin activated
		if($this->is_woocommerce_active()){
			$this->constants();
			$this->load_plugin_textdomain();
			$this->includes();
			$this->initHooks();
			$this->load_setting();
			$this->initClasses();
		}
		else{
			add_action( 'admin_notices', array( $this, 'show_dependency_warning' ) );
		}
	}
	
	//check if woocommerce plugin active or not
	public function is_woocommerce_active(){
		if(class_exists('WooCommerce')){
			return true;
		}
		return false;
	}
	
	//show dependency warning
	public function show_dependency_warning(){
		?>
		<div class="notice notice-warning is-dismissible wac-dismiss-warning-message">
			<p>
				<strong><?php echo __('Woocommerce Auction plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>!','woo-auction'); ?></strong>
			</p>
		</div>
		<?php
	}
	
	//define constants
	private function constants(){
		$this->define('WOO_AUCTION_URL', $this->plugin_url());
		$this->define('WOO_AUCTION_PATH', $this->plugin_path());
		$this->define('WOO_AUCTION_TEMPLATEPATH', $this->plugin_path().'/templates/');
		$this->define('WOO_AUCTION_MAINFILE', __FILE__);
	}
	
	//load plugin textdomain (woo-auction)
	private function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'woocommerce' );

		unload_textdomain( 'woo-auction' );
		load_textdomain( 'woo-auction', WP_LANG_DIR . '/woocommerce-auction/woo-auction-' . $locale . '.mo' );
		load_plugin_textdomain( 'woo-auction', false, plugin_basename( dirname( WOO_AUCTION_MAINFILE ) ) . '/languages' );
	}
	
	//define constant if not exists
	private function define( $name, $value ) {
		if (!defined($name)){
			define($name,$value);
		}
	}
	
	//include all classes and load
	public function includes(){
		$this->wac_messages();
		include_once( WOO_AUCTION_PATH . '/includes/wac-core-functions.php' );
		$this->load_class('admin','','admin');
		$this->load_class('auction-emails','emails/');
		$this->load_class('auction-product');
		$this->load_class('parse-site');
		$this->load_class('start-auction');
		$this->load_class('shortcode-auctions');
		$this->load_class('shortcode-live-auctions');
		$this->load_class('actions-overwrite');
		$this->load_class('autobid');
		$this->load_class('auction-commission');
		$this->load_class('vendor-profile');
		$this->load_class('mollie-integration');
		$this->load_class('video-background');
		$this->load_class('umprofiletabs');
	}
	
	//Messages string
	function wac_messages(){
		include_once( WOO_AUCTION_PATH . '/includes/wac-messages.php' );
		$this->wacStrings = $wacStrings;
	}
	
	//load woo auction settings
	function load_setting(){
		//Include Options page in admin
		include_once(plugin_dir_path( __FILE__ ).'/includes/admin/class.wac-settings.php');
		$WOO_Auction_SettingsPage = new WOO_Auction_SettingsPage();
	}
	
	//load classes from classes folder
	public function load_class($class_name = '',$folder='',$type='classes') {
		if ('' != $class_name) {
			$class_name = esc_attr($class_name);
			return include_once( WOO_AUCTION_PATH."/includes/{$type}/{$folder}class-wac-{$class_name}.php");
		}
	}
	
	//testing function
	function testthisone(){
		if(isset($_REQUEST['d_debug'])){
			error_reporting(E_ALL);
			ini_set('display_errors',1);
			
			/* $email_vendor = WC()->mailer()->emails['WAC_Auction_Live_Vendor'];
			$email_vendor->trigger(794); die; */
			
			$this->pre(_get_cron_array()); die;
		}
	}
	
	//initialize all hooks
	public function initHooks(){
		// add_action('wp_loaded', array($this, 'settimezone') );
		add_action('widgets_init',array($this,'wac_register_widgets'),100);
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 13);
		add_action('init',array($this,'testthisone'));
	}
	
	//intialize all classes
	public function initClasses(){
		
		//mollie integration
		$this->mollie = new Woo_Auction_Mollie_integration();
		
		//admin settings and fields.
		$this->wacadmin 	  = new Woo_Auction_Admin();
		
		//auction product
		$this->product = new Woo_Auction_Product();
		
		$this->emails 		  = new Woo_Auction_Emails();
		$this->start_auction  = new Woo_Auction_Start_Shortcode();
		$this->shortcodes 	  = new Wac_Shortcode_Auctions();
		$this->liveshortcode  = new Wac_Shortcode_Live_Auctions();
		$this->overwrite	  = new Woo_Auction_Actions_Overwrite();
		$this->autobid	 	  = new Woo_Auction_Autobid();
		$this->commission 	  = new Woo_Auction_Commission();
		$this->vendor_profile = new Woo_Auction_VendorProfile();
		$this->videoBG 		  = new Woo_Auction_VideoBackground();
		$this->umprofile 	  = new Woo_Auction_UmprofileTabs();
	}
	
	//plugin url
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	//plugin path
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
	
	//filter for woo auction template path
	public function template_path() {
		return apply_filters( 'woo_auction_template_path', $this->plugin_path().'/templates' );
	}
	
	//filter for woo auction theme template path
	public function theme_template_path() {
		return apply_filters( 'woo_auction_theme_template_path', 'woo-auction/' );
	}
	
	//enqueue all scripts
	public function enqueue_scripts(){
		wp_enqueue_style('woo-auction', WOO_AUCTION_URL.'/assets/css/woo-auction.css');
		wp_enqueue_style('yith-wcact-auction-font', YITH_WCACT_ASSETS_URL . '/fonts/icons-font/style.css');
		
		//enqueque select2 style and script
		if(!wp_script_is('select2','enquequed')){
			wp_enqueue_style( 'select2' );
			wp_enqueue_script( 'select2' );
		}
		
		//load fancybox data
		wp_enqueue_style('woo-fancybox', WOO_AUCTION_URL.'/assets/fancybox/jquery.fancybox.css');
		wp_enqueue_script('woo-fancybox', WOO_AUCTION_URL.'/assets/fancybox/jquery.fancybox.js', array('jquery'), '1.0', true);
		
		//main js file
		$wac_vars = array(
			'ajaxurl' 		  => admin_url('admin-ajax.php'),
			'logged_in' 	  => is_user_logged_in(),
			'jsstring' 	 	  => $this->wacStrings['jsstring'],
			'not_login_title' => $this->wacStrings['login_reg_title'],
			'not_login_msg'   => $this->wacStrings['login_reg_msg'],
			'auctvalidate' 	  => $this->wacStrings['auctionform']['validation'],
			'default_avatar'  => wac_default_gravatar(),
			'live_auction_product_page' => get_option('yith_wcact_settings_live_auction_product_page') * 1000
		);
		wp_enqueue_script('woo-auction', WOO_AUCTION_URL.'/assets/js/woo-auction.js', array('jquery'), '1.0', true);
		wp_localize_script('woo-auction', 'wac_vars', $wac_vars );
	}

	//function dashboard scripts
	function dashboard_scripts(){
		wp_enqueue_style('wac-datatable-respsonsive', WOO_AUCTION_URL.'/assets/css/responsive.bootstrap.min.css');
        wp_enqueue_script('wac-datatable-respsonsive',  WOO_AUCTION_URL.'/assets/js/dataTables.responsive.min.js', array('jquery'));
        wp_enqueue_script('wac-datatable-respsonsive-bs',  WOO_AUCTION_URL.'/assets/js/responsive.bootstrap.min.js', array('jquery'));
	}
	
	//set europe timezone
	function settimezone(){
		date_default_timezone_set('Europe/Amsterdam');
	}
	
	//wac widgets
	function wac_register_widgets(){
		$this->load_class('viewauction-widget');
		register_widget( 'Wac_Widget_Viewed_Auction' );
	}
	
	/**
	 * Print Result
	 */
	function pre($var){
		echo '<pre>'.print_r($var,true).'</pre>';
	}
	
}

/*
 * Function to load in all files.
 */
function Woo_auction() {
	return Woo_auction::instance();
}

// Global for backwards compatibility.
$GLOBALS['woo_auction'] = Woo_auction();