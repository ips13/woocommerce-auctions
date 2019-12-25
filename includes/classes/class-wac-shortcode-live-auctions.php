<?php

/** 
 * Display auctions
 */
class Wac_Shortcode_Live_Auctions extends WC_Shortcode_Products{
	
	public function __construct( $attributes = array(), $type = 'products' ) {
		$this->type       = $type;
		$this->attributes = $this->parse_attributes( $attributes );
		$this->query_args = $this->parse_query_args_auction();
	}
	
	function parse_query_args_auction() {
		$query_args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => $this->attributes['orderby'],
			'order'               => strtoupper( $this->attributes['order'] ),
		);

		// @codingStandardsIgnoreStart
		$query_args['posts_per_page'] = (int) $this->attributes['limit'];
		// $query_args['meta_query']     = WC()->query->get_meta_query();
		
		//display live auctions only
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
		$query_args['tax_query']      = array();
		// @codingStandardsIgnoreEnd

		// Visibility.
		// $this->set_visibility_query_args( $query_args );

		// SKUs.
		$this->set_skus_query_args( $query_args );

		// IDs.
		$this->set_ids_query_args( $query_args );

		// Set specific types query args.
		if ( method_exists( $this, "set_{$this->type}_query_args" ) ) {
			$this->{"set_{$this->type}_query_args"}( $query_args );
		}

		// Attributes.
		$this->set_attributes_query_args( $query_args );

		// Categories.
		$this->set_categories_query_args( $query_args );

		$query_args = apply_filters( 'woocommerce_shortcode_products_query', $query_args, $this->attributes, $this->type );

		// Always query only IDs.
		$query_args['fields'] = 'ids';

		return $query_args;
	}

}

//display live auctions
add_shortcode('woo-auction-live','wac_live_auctions');

function wac_live_auctions( $atts ) {
	$atts = array_merge( array(
		'limit'        => '12',
		'columns'      => '4',
		'orderby'      => 'date',
		'order'        => 'DESC',
		'category'     => '',
		'cat_operator' => 'IN'
	), (array) $atts );

	$shortcode = new Wac_Shortcode_Live_Auctions( $atts, 'live_auctions' );

	return $shortcode->get_content();
}