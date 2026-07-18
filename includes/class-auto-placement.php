<?php
/**
 * Automatic placement.
 *
 * @package BGG_Steam_Curator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles automatic post and floating placements.
 */
final class BGG_Steam_Curator_Auto_Placement {
	/**
	 * Whether before-comments widget was printed.
	 *
	 * @var bool
	 */
	private static $printed_before_comments = false;

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_filter( 'the_content', array( __CLASS__, 'filter_content' ), 25 );
		add_filter( 'comments_template', array( __CLASS__, 'before_comments' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'floating_widget' ), 20 );
	}

	/**
	 * Insert widget around post content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function filter_content( $content ) {
		$options = BGG_Steam_Curator_Plugin::get_options();

		if ( ! self::can_auto_place( $options ) ) {
			return $content;
		}

		$position = sanitize_key( (string) $options['auto_position'] );

		if ( 'before_comments' === $position ) {
			return $content;
		}

		$widget = BGG_Steam_Curator_Plugin::render( array(), 'after_post' );

		if ( 'before_content' === $position ) {
			return $widget . $content;
		}

		if ( 'both' === $position ) {
			return $widget . $content . $widget;
		}

		return $content . $widget;
	}

	/**
	 * Output before comments when selected.
	 *
	 * @param string $template Comments template path.
	 * @return string
	 */
	public static function before_comments( $template ) {
		$options = BGG_Steam_Curator_Plugin::get_options();

		if ( self::$printed_before_comments || 'before_comments' !== sanitize_key( (string) $options['auto_position'] ) || ! self::can_auto_place( $options, false ) ) {
			return $template;
		}

		self::$printed_before_comments = true;
		echo BGG_Steam_Curator_Plugin::render( array(), 'before_comments' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return $template;
	}

	/**
	 * Output floating widget when enabled.
	 */
	public static function floating_widget() {
		$options = BGG_Steam_Curator_Plugin::get_options();

		if ( ! BGG_Steam_Curator_Plugin::truthy( $options['floating'] ) || is_admin() ) {
			return;
		}

		echo BGG_Steam_Curator_Plugin::render( array( 'layout' => 'floating', 'floating' => 'true' ), 'floating' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Check auto placement conditions.
	 *
	 * @param array<string,mixed> $options Options.
	 * @param bool                $require_loop Whether to require main loop context.
	 * @return bool
	 */
	private static function can_auto_place( $options, $require_loop = true ) {
		if ( ! BGG_Steam_Curator_Plugin::truthy( $options['auto_enabled'] ) || ! is_singular() ) {
			return false;
		}

		if ( $require_loop && ( ! in_the_loop() || ! is_main_query() ) ) {
			return false;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		$post_types = isset( $options['auto_post_types'] ) && is_array( $options['auto_post_types'] ) ? $options['auto_post_types'] : array();

		if ( ! empty( $post_types ) && ! in_array( $post->post_type, $post_types, true ) ) {
			return false;
		}

		$categories = isset( $options['auto_categories'] ) && is_array( $options['auto_categories'] ) ? array_map( 'absint', $options['auto_categories'] ) : array();

		if ( empty( $categories ) ) {
			return true;
		}

		if ( 'post' !== $post->post_type ) {
			return true;
		}

		return has_category( $categories, $post );
	}
}
