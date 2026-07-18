<?php
/**
 * Shortcode integration.
 *
 * @package BGG_Steam_Curator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers [bg_gamer_steam_curator].
 */
final class BGG_Steam_Curator_Shortcode {
	/**
	 * Init hooks.
	 */
	public static function init() {
		add_shortcode( 'bg_gamer_steam_curator', array( __CLASS__, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'url'             => '',
				'title'           => '',
				'description'     => '',
				'followers'       => '',
				'recommendations' => '',
				'layout'          => 'horizontal',
				'sticky'          => 'false',
				'floating'        => 'false',
				'placement'       => 'shortcode',
			),
			(array) $atts,
			'bg_gamer_steam_curator'
		);

		$args = array_filter(
			array(
				'url'             => $atts['url'],
				'title'           => $atts['title'],
				'description'     => $atts['description'],
				'followers'       => $atts['followers'],
				'recommendations' => $atts['recommendations'],
				'layout'          => $atts['layout'],
				'sticky'          => $atts['sticky'],
				'floating'        => $atts['floating'],
			),
			static function ( $value ) {
				return '' !== $value;
			}
		);

		return BGG_Steam_Curator_Plugin::render( $args, BGG_Steam_Curator_Plugin::sanitize_placement( $atts['placement'] ) );
	}
}
