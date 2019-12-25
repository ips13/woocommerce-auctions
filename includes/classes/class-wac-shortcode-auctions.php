<?php
/**
 *
 *
 * @class Shotcode to Reorder auctions (Live, Future, Closed) 
 *
 */
if (!class_exists('Wac_Shortcode_Auctions')) {
    /**
     * Class YITH_Auction_Shortcodes
     *
     * @author Carlos Rodriguez <carlos.rodriguez@yourinspiration.it>
     */
    class Wac_Shortcode_Auctions
    {

        public function __construct()
        {
            $shortcodes = array(
                'wac-auction-products' => __CLASS__ . '::yith_auction_products', // print auction products
            );

            foreach ($shortcodes as $shortcode => $function) {
                add_shortcode($shortcode, $function);
            }

        }

        /**
         * Loop over found products.
         * @param  array $query_args
         * @param  array $atts
         * @param  string $loop_name
         * @return string
         */
        private static function product_loop( $query_args, $atts, $loop_name ) {
            global $woocommerce_loop;

            $products                    = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $query_args, $atts, $loop_name ) );

            //reorder auction listing
            $auction_live = array();
            $auction_future = array();
            $auction_closed = array();

            if ( $products->have_posts() ) {
                while ( $products->have_posts() ) : $products->the_post();

                    global $product;
                    $product = apply_filters('yith_wcact_get_auction_product',$product);
                    
                    if ($product->is_start() && !$product->is_closed()) {
                        $auction_live[] = $product->get_id();
                    }
                    elseif (!$product->is_closed() || !$product->is_start()) {
                        $auction_future[] = $product->get_id();
                    }
                    else{
                        $auction_closed[] = $product->get_id();
                    }
                endwhile; // end of the loop.
            }

            $allProductIDs = array_merge($auction_live, $auction_future, $auction_closed);
            $pargs = array(
                'post_type' => 'product',
                'orderby' => 'post__in', 
                'post__in' => $allProductIDs
            );
            $products     = new WP_Query($pargs);

           /* __wac_pre($auction_live);
            __wac_pre($auction_future);
            __wac_pre($auction_closed);
            __wac_pre($allProductIDs);
            die;*/

            $columns                     = absint( $atts['columns'] );
            $woocommerce_loop['columns'] = $columns;
            $woocommerce_loop['name']    = $loop_name;
            $orderby                 = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
            $catalog_orderby_options = apply_filters( 'yith_wcact_shortcode_catalog_orderby', array(
                'menu_order' => __( 'Default sorting', 'yith-auctions-for-woocommerce' ),
                /*'price'      => __( 'Sort by price: low to high', 'yith-auctions-for-woocommerce' ),
                'price-desc' => __( 'Sort by price: high to low', 'yith-auctions-for-woocommerce' ),*/
                'auction_asc' => __('Sort auctions by end date (asc)', 'yith-auctions-for-woocommerce'),
                'auction_desc' => __('Sort auctions by end date (desc)', 'yith-auctions-for-woocommerce'),
            ) );
            ob_start();
            ?>
            <form class="woocommerce-ordering" method="get">
                <select name="orderby" class="orderby">
                    <?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
                        <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php wc_query_string_form_fields( null, array( 'orderby', 'submit' ) ); ?>
            </form>

            <?php
            if ( $products->have_posts() ) {
                ?>

                <?php do_action( "woocommerce_shortcode_before_{$loop_name}_loop" ); ?>

                <?php woocommerce_product_loop_start(); ?>

                <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                    <?php wc_get_template_part( 'content', 'product' ); ?>

                <?php endwhile; // end of the loop. ?>

                <?php woocommerce_product_loop_end(); ?>

                <?php do_action( "woocommerce_shortcode_after_{$loop_name}_loop" ); ?>

                <?php
            } else {
                do_action( "woocommerce_shortcode_{$loop_name}_loop_no_results" );
            }

            woocommerce_reset_loop();
            wp_reset_postdata();
            return woocommerce_catalog_ordering().'<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
        }


        /**
         * ShortCode for auction products
         *
         * @return void
         * @since 1.0.0
         */
        public static function yith_auction_products($atts)
        {
            $atts = shortcode_atts( array(
                'columns' => '4',
                'orderby' => '',
                'order'   => 'ASC',
                'ids'     => '',
                'skus'    => ''
            ), $atts, 'products' );


            $ordering_args = self::get_catalog_ordering_args( $atts['orderby'], $atts['order'] );

            $query_args = array(
                'post_type'           => 'product',
                'post_status'         => 'publish',
                'ignore_sticky_posts' => 1,
                'orderby'             =>  $ordering_args['orderby'],
                'order'               =>  $ordering_args['order'],
                'posts_per_page'      => -1,
                'meta_query'          => WC()->query->get_meta_query()
            );

            if ( isset( $ordering_args['meta_key'] ) ) {
                $query_args['meta_key'] = $ordering_args['meta_key'];
            }

            if ( $auction_term = get_term_by( 'slug', 'auction', 'product_type' ) ) {
                $posts_in = array_unique((array)get_objects_in_term($auction_term->term_id, 'product_type'));
                if (! empty ( $posts_in)) {

                    $query_args['post__in'] = array_map('trim', $posts_in ) ;

                    // Ignore catalog visibility
                    $query_args['meta_query'] = array_merge($query_args['meta_query'], WC()->query->stock_status_meta_query());

                    wp_enqueue_style('yith-wcact-frontend-css');
                    wp_enqueue_script('yith_wcact_frontend_shop', YITH_WCACT_ASSETS_URL . '/js/frontend_shop.js', array('jquery', 'jquery-ui-sortable'), YITH_WCACT_VERSION, true);
                    wp_localize_script('yith_wcact_frontend_shop', 'object', array(
                        'ajaxurl' => admin_url('admin-ajax.php')
                    ));

                    return self::product_loop( $query_args, $atts, 'yith_auction_products' );
                }
            }
            return '';
        }

        public static function get_catalog_ordering_args($orderby = '', $order = '') {
            if ( !$orderby ) {
                $orderby_value = isset( $_GET['orderby'] ) ? wc_clean( (string) $_GET['orderby'] ) : apply_filters( 'yith_wcact_shortcode_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

                // Get order + orderby args from string
                $orderby_value = explode( '-', $orderby_value );
                $orderby       = esc_attr( $orderby_value[0] );
                $order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : $order;
            }

            $orderby = strtolower( $orderby );
            $order   = strtoupper( $order );
            $args    = array();

            // default - menu_order
            $args['orderby']  = 'menu_order title';
            $args['order']    = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
            $args['meta_key'] = '';
            $args['join'] = '';

            switch ( $orderby ) {

               /* case 'price' :
                    if ( 'DESC' === $order ) {
                        $args['orderby']  = 'meta_value';
                        $args['order'] = 'ASC';
                        $args['meta_key'] = '_price';
                        //WC()->query->order_by_price_desc_post_clauses($args);

                    } else {
                        $args['orderby']  = 'meta_value';
                        $args['order'] = 'DESC';
                        $args['meta_key'] = '_price';
                        //WC()->query->order_by_price_asc_post_clauses($args);

                    }
                    break; */
                case 'auction_asc':
                    $args['orderby'] = 'meta_value';
                    $args['order'] = 'ASC';
                    $args['meta_key'] = '_yith_auction_to';
                    break;

                case 'auction_desc':
                    $args['orderby'] = 'meta_value';
                    $args['order'] = 'DESC';
                    $args['meta_key'] = '_yith_auction_to';
                    break;
            }

            return apply_filters( 'yith_wcact_shortcode_get_catalog_ordering_args', $args );
        }

    }
}