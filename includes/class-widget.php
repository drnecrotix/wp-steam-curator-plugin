<?php
/**
 * WordPress widget integration.
 *
 * @package BGG_Steam_Curator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sidebar widget.
 */
final class BGG_Steam_Curator_Widget extends WP_Widget {
	/**
	 * Register widget.
	 */
	public static function init() {
		add_action(
			'widgets_init',
			static function () {
				register_widget( __CLASS__ );
			}
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'bgg_steam_curator_widget',
			__( 'BG-GAMER Steam Curator', 'bg-gamer-steam-curator' ),
			array(
				'description' => __( 'Displays the BG-GAMER Steam Curator follow widget.', 'bg-gamer-steam-curator' ),
			)
		);
	}

	/**
	 * Front-end output.
	 *
	 * @param array<string,mixed> $args     Widget args.
	 * @param array<string,mixed> $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo BGG_Steam_Curator_Plugin::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'title'    => $instance['title'] ?? '',
				'layout'   => $instance['layout'] ?? 'compact',
				'sticky'   => ! empty( $instance['sticky'] ) ? 'true' : 'false',
				'floating' => 'false',
			),
			'sidebar'
		);

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array<string,mixed> $instance Widget instance.
	 */
	public function form( $instance ) {
		$title  = $instance['title'] ?? '';
		$layout = $instance['layout'] ?? 'compact';
		$sticky = ! empty( $instance['sticky'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title override', 'bg-gamer-steam-curator' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php esc_html_e( 'Layout', 'bg-gamer-steam-curator' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>">
				<option value="compact" <?php selected( $layout, 'compact' ); ?>><?php esc_html_e( 'Compact', 'bg-gamer-steam-curator' ); ?></option>
				<option value="horizontal" <?php selected( $layout, 'horizontal' ); ?>><?php esc_html_e( 'Horizontal', 'bg-gamer-steam-curator' ); ?></option>
			</select>
		</p>
		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'sticky' ) ); ?>" value="1" <?php checked( $sticky ); ?>>
				<?php esc_html_e( 'Sticky in sidebar', 'bg-gamer-steam-curator' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Sanitize widget instance.
	 *
	 * @param array<string,mixed> $new_instance New values.
	 * @param array<string,mixed> $old_instance Old values.
	 * @return array<string,mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'  => sanitize_text_field( $new_instance['title'] ?? '' ),
			'layout' => BGG_Steam_Curator_Plugin::sanitize_layout( $new_instance['layout'] ?? 'compact' ),
			'sticky' => ! empty( $new_instance['sticky'] ) ? '1' : '0',
		);
	}
}
