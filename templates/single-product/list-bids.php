<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


/* $instance = YITH_Auctions()->bids;
$auction_list = $instance->get_bids_auction($product->get_id()); */

global $woo_auction;
$auction_list 		= $woo_auction->product->get_bids_auction($product->get_id());
$max_auction_bid 	= $woo_auction->product->max_auction_bid($product->get_id());

?>
<div class="yith-wcact-table-bids">

      <input type="hidden" id="yith-wcact-product-id" name="yith-wcact-product" value="<?php echo esc_attr( $product->get_id() );?>">
    <?php
    if( apply_filters('yith_wcact_show_list_bids',true)) {
        ?>
        <?php
        if (count($auction_list) == 0) {
            ?>

            <p id="single-product-no-bid"><?php echo __('There is no bid for this auction','woo-auction'); ?></p>

            <?php
        } else {
            ?>
            <table id="datatable">
                <tr>
                    <td class="toptable"><?php echo __('Bid Amount','woo-auction'); ?></td>
                    <td class="toptable"><?php echo __('Username','woo-auction'); ?></td>
                    <td class="toptable"><?php echo apply_filters('yith_wcact_datetime_table', __('Datetime','woo-auction')); ?></td>
                </tr>
                <?php
                $option = get_option('yith_wcact_settings_tab_auction_show_name');
                foreach ($auction_list as $object => $id) {
                    $user = get_user_by('id', $id->user_id);
                    $username = $user->data->user_nicename;
                    if ('no' == $option) {
                        $len = strlen($username);
                        $start = 1;
                        $end = 1;
                        // $username = substr($username, 0, $start) . str_repeat('*', $len - ($start + $end)) . substr($username, $len - $end, $end);
                        $username = 'UserID '.$user->ID;
                    }
                    if ($object == 0) {
                        $bid = $product->get_price();
                        ?>
                        <tr>
                            <td><?php echo wc_price($bid) ?></td>
                            <td><?php echo $username ?></td>
                            <td class="yith_auction_datetime"><?php echo $id->date ?></td>
                        </tr>
                        <?php
                    } elseif ($max_auction_bid->bid == $id->bid) {
                        $bid = $id->bid;
                        ?>
                        <tr>
                            <td><?php echo wc_price($bid) ?></td>
                            <td><?php echo $username ?></td>
                            <td class="yith_auction_datetime"><?php echo $id->date ?></td>
                        </tr>
                        <?php
                    } elseif ($id->bid < $product->get_price()) {
                        $bid = $id->bid;
                        ?>
                        <tr>
                            <td><?php echo wc_price($bid) ?></td>
                            <td><?php echo $username ?></td>
                            <td class="yith_auction_datetime"><?php echo $id->date ?></td>
                        </tr>
                        <?php
                    }
                }
                if ($product->is_start() && $auction_list) {
                    ?>
                    <tr>
                        <td><?php echo wc_price($product->get_start_price()) ?></td>
                        <td><?php echo __('Start auction','woo-auction') ?></td>
                        <td></td>
                    </tr>
                    <?php
                }
                ?>

            </table>
            <?php
            if (count($auction_list) == 0) {
                ?>

                <p id="single-product-no-bid"><?php echo __('There is no bid for this auction','woo-auction'); ?></p>

                <?php
            }
        }
    }
    ?>
</div>