<?php
if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}
global $WCMp,$woo_auction;

do_action('before_wcmp_vendor_dashboard');

//wc_print_notices();
wac_get_template('/shortcode/vendor-profile/header.php');

do_action('woo_auction_vendor_navigation', array());
$is_single = !is_null($WCMp->endpoints->get_current_endpoint_var()) ? '-single' : '';
?>
<div id="page-wrapper" class="wac-vendorprofile side-collapse-container">
    <div id="current-endpoint-title-wrapper" class="current-endpoint-title-wrapper">
        <div class="current-endpoint">
            <?php echo $woo_auction->vendor_profile->wac_create_vendor_dashboard_breadcrumbs($woo_auction->vendor_profile->get_current_endpoint()); ?>
        </div>
    </div>
    <!-- /.row -->
    <div class="content-padding gray-bkg <?php echo $WCMp->endpoints->get_current_endpoint() ? $woo_auction->vendor_profile->get_current_endpoint().$is_single : 'dashboard'; ?>">
        <div class="notice-wrapper">
            <?php if(function_exists('wc_print_notices')){ wc_print_notices(); } ?>
        </div>
        <div class="row">
            <?php do_action('woo_auction_vendor_content'); ?>
        </div>
    </div>
</div>

<?php
$WCMp->template->get_template('vendor-dashboard/dashboard-footer.php');

do_action('after_wcmp_vendor_dashboard');

