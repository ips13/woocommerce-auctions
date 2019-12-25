<?php

/**
 * Add video background shortcode and scripts
 */
class Woo_Auction_VideoBackground{
	
	public function __construct(){
		add_action( 'wp_enqueue_scripts', array($this,'vidbg_enqueue_scripts') );
		add_shortcode( 'videobg', array($this,'wac_video_background') );
	}
	
	function vidbg_enqueue_scripts() {
		wp_register_script( 'wac-vidbg', WOO_AUCTION_URL.'/assets/js/vidbg.js', array('jquery'), '1.0', true);
		wp_enqueue_style( 'wac-vidbg', WOO_AUCTION_URL.'/assets/css/vidbg.css', array(), '1.0' );
	}
	
	function wac_video_background( $atts , $content = null ) {

		// Attributes
		extract(
		  shortcode_atts(
			array(
			  'container' => 'body',
			  'mp4' => '#',
			  'webm' => '#',
			  'poster' => '#',
			  'muted' => 'true',
			  'loop' => 'true',
			  'overlay' => 'false',
			  'overlay_color' => '#000',
			  'overlay_alpha' => '0.3',
			  'source' => 'Shortcode',
			), $atts , 'vidbg'
		  )
		);

		// Enqueue the vidbg script conditionally
		wp_enqueue_script( 'wac-vidbg' );

		$output = "<script>
		  jQuery(function($){
			// Source: " . $source . "
			$( '" . $container . "' ).vidbg( {
			  mp4: '" . $mp4 . "',
			  webm: '" . $webm . "',
			  poster: '" . $poster . "',
			  mute: " . $muted . ",
			  repeat: " . $loop . ",
			  overlay: " . $overlay . ",
			  overlayColor: '" . $overlay_color . "',
			  overlayAlpha: '" . $overlay_alpha . "',
			});
		  });
		</script>";

		return $output;
	}
	
}