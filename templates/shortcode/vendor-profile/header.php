<?php
/**
 * The template for displaying vendor dashboard header content
 *
 * This template can be overridden by copying it to yourtheme/dc-product-vendor/vebdor-dashboard/dashboard-header.php.
 *
 * HOWEVER, on occasion WC Marketplace will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WC Marketplace
 * @package WCMp/Templates
 * @version 3.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}
global $WCMp, $woo_auction;
$vendor = get_wcmp_vendor(get_current_vendor_id());
if(!$vendor){
    return;
}
$vendor_logo = $vendor->get_image() ? $vendor->get_image() : $WCMp->plugin_url . 'assets/images/default-vendor-dp.png';
$site_logo = get_wcmp_vendor_settings('wcmp_dashboard_site_logo', 'vendor', 'dashboard') ? get_wcmp_vendor_settings('wcmp_dashboard_site_logo', 'vendor', 'dashboard') : '';
?>

<!-- Top bar -->
<div class="top-navbar white-bkg">
    <div class="navbar navbar-default">
        <div class="topbar-left pull-left pos-rel">
            <div class="site-logo text-center pos-middle">
                <a href="<?php echo site_url(); ?>">
                    <?php if($site_logo) { ?>
                    <img src="<?php echo $site_logo; ?>" alt="<?php echo bloginfo(); ?>">
                    <?php }else{ echo bloginfo(); } ?>
                </a>
            </div>
        </div>
		<div class="navbar-header topnavbtn">
			<button data-toggle="collapse-side" data-target="#side-collapse" type="button" class="navbar-toggle pull-left larr collapsed">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
        <ul class="nav pull-right top-user-nav">
            <li class="login-user">
				<?php $accountlink = $woo_auction->vendor_profile->dashboardlink; ?>
                <a href="<?php echo $accountlink.'account-details'; ?>" class="dropdown-toggle">
                    <i class="wcmp-font ico-user-icon"></i>
                    <!--span><i class="wcmp-font ico-down-arrow-icon"></i></span-->
                </a>
                <ul class="dropdown-menu dropdown-user dropdown-menu-right">
                    <li class="sidebar-logo text-center"> 
                        <div class="vendor-profile-pic-holder">
                            <img src="<?php echo $vendor_logo; ?>" alt="hard crop 130 * 130">
                        </div>
                        <h4><?php echo $vendor->user_data->display_name; ?></h4>  
                    </li> 
                </ul>
                <!-- /.dropdown -->
            </li>
        </ul>
        
    <?php $header_nav = $woo_auction->vendor_profile->dashboard_header_nav();
    
    if($header_nav) : 
        sksort($header_nav, 'position', true); ?>
        <ul class="nav navbar-top-links navbar-right pull-right btm-nav-fixed">
            <?php 
            foreach ($header_nav as $key => $nav):
                if (current_user_can($nav['capability']) || $nav['capability'] === true): ?>
                <li class="notification-link <?php if(!empty($nav['class'])) echo $nav['class']; ?>">
                    <a href="<?php echo esc_url($nav['url']); ?>" target="<?php echo $nav['link_target']; ?>" title="<?php echo $nav['label']; ?>">
                        <i class="<?php echo $nav['nav_icon']; ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $nav['label']; ?></span>
                        <?php if($key == 'announcement') :
                            $vendor_announcements = $vendor->get_announcements(); 
                            if(isset($vendor_announcements['unread']) && count($vendor_announcements['unread']) > 0){
                                echo '<span class="notification-blink"></span>';
                            } 
                        endif; ?>
                    </a>
                </li>
            <?php 
                endif;
            endforeach;
            ?>
        </ul>     
    <?php endif; ?>
        <!-- /.navbar-top-links -->
    </div>
</div>