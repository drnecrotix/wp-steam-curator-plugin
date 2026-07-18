<?php
/**
 * Plugin Name: BG-GAMER Steam Curator Widget
 * Description: Responsive Steam Curator follow widget for BG-GAMER with shortcode, widget, auto placement, and floating mode.
 * Version: 1.0.0
 * Author: BG-GAMER
 * Text Domain: bg-gamer-steam-curator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BGG_STEAM_CURATOR_VERSION', '1.0.0' );
define( 'BGG_STEAM_CURATOR_FILE', __FILE__ );
define( 'BGG_STEAM_CURATOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BGG_STEAM_CURATOR_URL', plugin_dir_url( __FILE__ ) );
define( 'BGG_STEAM_CURATOR_OPTION', 'bgg_steam_curator_options' );

require_once BGG_STEAM_CURATOR_DIR . 'includes/class-admin-settings.php';
require_once BGG_STEAM_CURATOR_DIR . 'includes/class-shortcode.php';
require_once BGG_STEAM_CURATOR_DIR . 'includes/class-auto-placement.php';
require_once BGG_STEAM_CURATOR_DIR . 'includes/class-widget.php';

/**
 * Main plugin service.
 */
final class BGG_Steam_Curator_Plugin {
	/**
	 * Boot plugin hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );

		BGG_Steam_Curator_Admin_Settings::init();
		BGG_Steam_Curator_Shortcode::init();
		BGG_Steam_Curator_Auto_Placement::init();
		BGG_Steam_Curator_Widget::init();
	}

	/**
	 * Default option values.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return array(
			'url'              => 'https://store.steampowered.com/curator/5043216-BG-Gamer/',
			'curator_name'     => 'BG-GAMER',
			'title'            => 'Следвай BG-GAMER в Steam',
			'description'      => 'Откривай препоръчани игри, ревюта и скрити заглавия, подбрани от екипа на BG-GAMER.',
			'logo_url'         => '',
			'steam_icon_url'   => '',
			'followers'        => '',
			'recommendations'  => '',
			'primary_button'   => 'Follow on Steam',
			'secondary_link'   => 'Виж всички препоръки',
			'layout'           => 'horizontal',
			'sticky'           => '0',
			'floating'         => '0',
			'auto_enabled'     => '0',
			'auto_post_types'  => array( 'post' ),
			'auto_categories'  => array(),
			'auto_position'    => 'after_content',
		);
	}

	/**
	 * Get normalized plugin options.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_options() {
		$options = get_option( BGG_STEAM_CURATOR_OPTION, array() );

		return wp_parse_args( is_array( $options ) ? $options : array(), self::defaults() );
	}

	/**
	 * Enqueue front-end assets only when the widget can appear.
	 */
	public static function maybe_enqueue_assets() {
		if ( self::should_enqueue_assets() ) {
			self::enqueue_assets();
		}
	}

	/**
	 * Register/enqueue assets.
	 */
	public static function enqueue_assets() {
		$css_path = BGG_STEAM_CURATOR_DIR . 'assets/css/steam-curator-widget.css';
		$js_path  = BGG_STEAM_CURATOR_DIR . 'assets/js/steam-curator-widget.js';

		wp_enqueue_style(
			'bgg-steam-curator-widget',
			BGG_STEAM_CURATOR_URL . 'assets/css/steam-curator-widget.css',
			array(),
			file_exists( $css_path ) ? (string) filemtime( $css_path ) : BGG_STEAM_CURATOR_VERSION
		);

		wp_enqueue_script(
			'bgg-steam-curator-widget',
			BGG_STEAM_CURATOR_URL . 'assets/js/steam-curator-widget.js',
			array(),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : BGG_STEAM_CURATOR_VERSION,
			true
		);
	}

	/**
	 * Check whether front-end assets are needed on the current request.
	 *
	 * @return bool
	 */
	private static function should_enqueue_assets() {
		$options = self::get_options();

		if ( self::truthy( $options['floating'] ) || self::truthy( $options['auto_enabled'] ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();

			if ( $post instanceof WP_Post && has_shortcode( (string) $post->post_content, 'bg_gamer_steam_curator' ) ) {
				return true;
			}
		}

		return is_active_widget( false, false, 'bgg_steam_curator_widget', true );
	}

	/**
	 * Render a curator widget.
	 *
	 * @param array<string,mixed> $args      Overrides from shortcode/widget.
	 * @param string              $placement Tracking placement.
	 * @return string
	 */
	public static function render( $args = array(), $placement = 'shortcode' ) {
		self::enqueue_assets();

		$options = wp_parse_args( $args, self::get_options() );
		$options = self::normalize_render_options( $options );

		$url        = esc_url( $options['url'] );
		$layout     = sanitize_html_class( $options['layout'] );
		$is_sticky  = self::truthy( $options['sticky'] );
		$is_float   = self::truthy( $options['floating'] ) || 'floating' === $layout;
		$placement  = self::sanitize_placement( $placement );
		$classes    = array( 'bgg-steam-curator', 'bgg-steam-curator--' . $layout );

		if ( $is_sticky ) {
			$classes[] = 'bgg-steam-curator--sticky';
		}

		if ( $is_float ) {
			$classes[] = 'bgg-steam-curator--floating';
		}

		ob_start();
		?>
		<section class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-placement="<?php echo esc_attr( $placement ); ?>" data-curator-url="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr__( 'Steam Curator follow widget', 'bg-gamer-steam-curator' ); ?>">
			<?php if ( $is_float ) : ?>
				<button class="bgg-steam-curator__close" type="button" aria-label="<?php esc_attr_e( 'Close Steam Curator widget', 'bg-gamer-steam-curator' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			<?php endif; ?>

			<div class="bgg-steam-curator__brand">
				<?php echo self::render_logo( $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="bgg-steam-curator__steam" aria-hidden="true">
					<?php echo self::render_steam_icon( $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			</div>

			<div class="bgg-steam-curator__content">
				<p class="bgg-steam-curator__eyebrow"><?php echo esc_html( $options['curator_name'] ); ?> Steam Curator</p>
				<h2 class="bgg-steam-curator__title"><?php echo esc_html( $options['title'] ); ?></h2>
				<p class="bgg-steam-curator__description"><?php echo esc_html( $options['description'] ); ?></p>

				<?php echo self::render_stats( $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<div class="bgg-steam-curator__actions">
				<a class="bgg-steam-curator__button" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer nofollow" data-event="steam_curator_click" data-placement="<?php echo esc_attr( $placement ); ?>">
					<?php echo esc_html( $options['primary_button'] ); ?>
				</a>
				<a class="bgg-steam-curator__secondary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer nofollow" data-event="steam_curator_click" data-placement="<?php echo esc_attr( $placement ); ?>">
					<?php echo esc_html( $options['secondary_link'] ); ?>
				</a>
			</div>
		</section>
		<?php

		return trim( ob_get_clean() );
	}

	/**
	 * Normalize render options and shortcode aliases.
	 *
	 * @param array<string,mixed> $options Raw options.
	 * @return array<string,mixed>
	 */
	private static function normalize_render_options( $options ) {
		$defaults = self::defaults();

		foreach ( $defaults as $key => $default ) {
			if ( ! array_key_exists( $key, $options ) || null === $options[ $key ] ) {
				$options[ $key ] = $default;
			}
		}

		$options['url']             = esc_url_raw( (string) $options['url'] );
		$options['curator_name']    = sanitize_text_field( (string) $options['curator_name'] );
		$options['title']           = sanitize_text_field( (string) $options['title'] );
		$options['description']     = sanitize_textarea_field( (string) $options['description'] );
		$options['logo_url']        = esc_url_raw( (string) $options['logo_url'] );
		$options['steam_icon_url']  = esc_url_raw( (string) $options['steam_icon_url'] );
		$options['followers']       = sanitize_text_field( (string) $options['followers'] );
		$options['recommendations'] = sanitize_text_field( (string) $options['recommendations'] );
		$options['primary_button']  = sanitize_text_field( (string) $options['primary_button'] );
		$options['secondary_link']  = sanitize_text_field( (string) $options['secondary_link'] );
		$options['layout']          = self::sanitize_layout( (string) $options['layout'] );

		if ( empty( $options['url'] ) ) {
			$options['url'] = $defaults['url'];
		}

		return $options;
	}

	/**
	 * Render logo image/fallback.
	 *
	 * @param array<string,mixed> $options Widget options.
	 * @return string
	 */
	private static function render_logo( $options ) {
		if ( ! empty( $options['logo_url'] ) ) {
			return sprintf(
				'<img class="bgg-steam-curator__logo" src="%s" alt="%s" loading="lazy" decoding="async">',
				esc_url( $options['logo_url'] ),
				esc_attr( $options['curator_name'] )
			);
		}

		return '<span class="bgg-steam-curator__logo-fallback" aria-hidden="true">BG</span>';
	}

	/**
	 * Render Steam icon image/fallback SVG.
	 *
	 * @param array<string,mixed> $options Widget options.
	 * @return string
	 */
	private static function render_steam_icon( $options ) {
		if ( ! empty( $options['steam_icon_url'] ) ) {
			return sprintf(
				'<img src="%s" alt="" loading="lazy" decoding="async">',
				esc_url( $options['steam_icon_url'] )
			);
		}

		return '<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-9.95 9.05l5.35 2.22a2.82 2.82 0 0 1 1.58-.46l2.36-3.42V9.34a3.74 3.74 0 1 1 3.74 3.75h-.09l-3.36 2.4a2.86 2.86 0 0 1-5.63.72l-3.83-1.59A10 10 0 1 0 12 2Zm-3.1 14.96-1.22-.5a2.15 2.15 0 1 0 .98-2.87l1.27.52a1.58 1.58 0 0 1-1.03 2.85Zm6.18-5.14a2.48 2.48 0 1 0 0-4.96 2.48 2.48 0 0 0 0 4.96Zm0-.62a1.86 1.86 0 1 1 0-3.72 1.86 1.86 0 0 1 0 3.72Z"/></svg>';
	}

	/**
	 * Render optional manual stats.
	 *
	 * @param array<string,mixed> $options Widget options.
	 * @return string
	 */
	private static function render_stats( $options ) {
		$stats = array();

		if ( '' !== trim( (string) $options['followers'] ) ) {
			$stats[] = array(
				'label' => __( 'Followers', 'bg-gamer-steam-curator' ),
				'value' => $options['followers'],
			);
		}

		if ( '' !== trim( (string) $options['recommendations'] ) ) {
			$stats[] = array(
				'label' => __( 'Recommendations', 'bg-gamer-steam-curator' ),
				'value' => $options['recommendations'],
			);
		}

		if ( empty( $stats ) ) {
			return '';
		}

		$output = '<dl class="bgg-steam-curator__stats">';

		foreach ( $stats as $stat ) {
			$output .= sprintf(
				'<div class="bgg-steam-curator__stat"><dt>%s</dt><dd>%s</dd></div>',
				esc_html( $stat['label'] ),
				esc_html( $stat['value'] )
			);
		}

		$output .= '</dl>';

		return $output;
	}

	/**
	 * Sanitize layout slug.
	 *
	 * @param string $layout Layout value.
	 * @return string
	 */
	public static function sanitize_layout( $layout ) {
		$layout = sanitize_key( $layout );
		$map    = array(
			'standard'   => 'horizontal',
			'horizontal' => 'horizontal',
			'compact'    => 'compact',
			'floating'   => 'floating',
		);

		return isset( $map[ $layout ] ) ? $map[ $layout ] : 'horizontal';
	}

	/**
	 * Sanitize tracking placement.
	 *
	 * @param string $placement Placement value.
	 * @return string
	 */
	public static function sanitize_placement( $placement ) {
		$allowed = array( 'homepage', 'sidebar', 'after_post', 'before_comments', 'floating', 'shortcode' );
		$value   = sanitize_key( $placement );

		return in_array( $value, $allowed, true ) ? $value : 'shortcode';
	}

	/**
	 * Convert shortcode/admin boolean values.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function truthy( $value ) {
		return in_array( $value, array( true, 1, '1', 'true', 'yes', 'on' ), true );
	}
}

BGG_Steam_Curator_Plugin::init();
