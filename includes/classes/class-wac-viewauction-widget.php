<?php
/**
 * Wac_Widget_Viewed_Auction
 *
 * Widget related functions and widget registration.
 *
 * @author 		WooThemes
 * @category 	Core
 * @package 	WooCommerce/Functions
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Class Wac_Widget_Viewed_Auction
 *
 * @author Carlos Rodríguez <carlos.rodriguez@yourinspiration.it>
 */
class Wac_Widget_Viewed_Auction extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'yith-wcact-auction-widget wac-auction-widget';
		$this->widget_id          = 'wac_viewed_auctions';
		$this->widget_description = __( "Display a list of a customer's recently viewed auctions.",'woo-auction');
		$this->widget_name        = __('Recent Viewed Auctions','woo-auction'); 
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title','woo-auction' )
			),
			'display' => array(
				'type'  => 'select',
				'std'   => '',
				'label' => __( 'Display','woo-auction' ),
				'options' => array(
					'viewed'    => __( 'Viewed Auctions','woo-auction' ),
					'live'  	=> __( 'Live Auctions','woo-auction' ),
				)
			),
			'show' => array(
				'type'  => 'select',
				'std'   => '',
				'label' => __( 'Show','woo-auction' ),
				'options' => array(
					'last'     => __( 'Last Auctions','woo-auction' ),
					'featured' => __( 'Featured Auctions','woo-auction' ),
				)
			),
			'non_started_auction' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show not-started auctions','woo-auction' )
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 5,
				'label' => __( 'Number of auctions to show','woo-auction' )
			),

		);

		parent::__construct();
	}

	/**
	 * Query the products and return them.
	 * @param  array $args
	 * @param  array $instance
	 * @return WP_Query
	 */
	public function get_products( $args, $instance ) {
		$number  = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];
		$show    = ! empty( $instance['show'] ) ? sanitize_title( $instance['show'] ) : $this->settings['show']['std'];
		$display = ! empty( $instance['display'] ) ? sanitize_title( $instance['display'] ) : $this->settings['display']['std'];

		$query_args = array(
			'posts_per_page' => $number,
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'no_found_rows'  => 1,
			'meta_query'     => array()
		);


		$query_args['tax_query'] = array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')); 
		$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] );

		switch ( $show ) {
			case 'featured' :
				$query_args['meta_query'][] = array(
					'key'   => '_featured',
					'value' => 'yes'
				);
				break;
			case 'last':
				$query_args['order'] = 'DESC';
				$query_args['orderby'] = 'date';
				break;
		}
		
		switch ( $display ) {
			
			//display only live auctions
			case 'live':
				$query_args['post_status'] = 'publish';
				if ( !empty( $instance['non_started_auction'] ) ) {
					$query_args['meta_query'][] = array(
						'key'     => '_yith_auction_to',
						'value'   =>  strtotime('now'),
						'compare' => '>'
					);
				}
				else{
					$query_args['meta_query'][] = array(
						array(
							'relation' => 'AND',
							array(
								'key'     => '_yith_auction_for',
								'value'   => strtotime('now'),
								'compare' => '<',
							),
							array(
								'key'     => '_yith_auction_to',
								'value'   =>  strtotime('now'),
								'compare' => '>'
							)
						)
					);
				}
				break;
				
			//display all viewed auctions
			case 'viewed':
				$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
				$viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
				$query_args['post__in'] = $viewed_products;
				break;
		}
		
		return new WP_Query( apply_filters( 'yith_wcact_products_widget_query_args', $query_args ) );
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( $this->get_cached_widget( $args ) ) {
			return;
		}
		ob_start();
		$display = ! empty( $instance['display'] ) ? sanitize_title( $instance['display'] ) : $this->settings['display']['std'];

		if ( ( $products = $this->get_products( $args, $instance ) ) && $products->have_posts() ) {
			$this->widget_start( $args, $instance );

			echo apply_filters( 'yith_wcact_before_widget_product_list', '<ul class="yith-auction-list-widget">' );

			while ( $products->have_posts() ) {
				$products->the_post();
				 wac_get_template( 'widgets/content-widget-auction.php',array('display_type'=>$display));
			}

			echo apply_filters( 'yith_wcact_after_widget_product_list', '</ul>' );

			$this->widget_end( $args );
		}

		wp_reset_postdata();

		echo $this->cache_widget( $args, ob_get_clean() );
	}
}