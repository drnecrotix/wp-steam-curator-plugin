<?php
/**
 * Admin settings page.
 *
 * @package BGG_Steam_Curator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page implementation.
 */
final class BGG_Steam_Curator_Admin_Settings {
	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register page under Settings.
	 */
	public static function add_settings_page() {
		add_options_page(
			__( 'BG-GAMER Steam Curator', 'bg-gamer-steam-curator' ),
			__( 'BG-GAMER Steam Curator', 'bg-gamer-steam-curator' ),
			'manage_options',
			'bgg-steam-curator',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register Settings API option.
	 */
	public static function register_settings() {
		register_setting(
			'bgg_steam_curator_settings',
			BGG_STEAM_CURATOR_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_options' ),
				'default'           => BGG_Steam_Curator_Plugin::defaults(),
			)
		);
	}

	/**
	 * Load media uploader on plugin settings page.
	 *
	 * @param string $hook Current admin hook.
	 */
	public static function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_bgg-steam-curator' !== $hook ) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * Sanitize all settings.
	 *
	 * @param mixed $input Raw option input.
	 * @return array<string,mixed>
	 */
	public static function sanitize_options( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = BGG_Steam_Curator_Plugin::defaults();
		$output   = array();

		$output['url']             = esc_url_raw( $input['url'] ?? $defaults['url'] );
		$output['curator_name']    = sanitize_text_field( $input['curator_name'] ?? $defaults['curator_name'] );
		$output['title']           = sanitize_text_field( $input['title'] ?? $defaults['title'] );
		$output['description']     = sanitize_textarea_field( $input['description'] ?? $defaults['description'] );
		$output['logo_url']        = esc_url_raw( $input['logo_url'] ?? '' );
		$output['steam_icon_url']  = esc_url_raw( $input['steam_icon_url'] ?? '' );
		$output['followers']       = sanitize_text_field( $input['followers'] ?? '' );
		$output['recommendations'] = sanitize_text_field( $input['recommendations'] ?? '' );
		$output['primary_button']  = sanitize_text_field( $input['primary_button'] ?? $defaults['primary_button'] );
		$output['secondary_link']  = sanitize_text_field( $input['secondary_link'] ?? $defaults['secondary_link'] );
		$output['layout']          = BGG_Steam_Curator_Plugin::sanitize_layout( $input['layout'] ?? $defaults['layout'] );
		$output['sticky']          = ! empty( $input['sticky'] ) ? '1' : '0';
		$output['floating']        = ! empty( $input['floating'] ) ? '1' : '0';
		$output['auto_enabled']    = ! empty( $input['auto_enabled'] ) ? '1' : '0';
		$output['auto_position']   = self::sanitize_auto_position( $input['auto_position'] ?? $defaults['auto_position'] );

		$post_types = isset( $input['auto_post_types'] ) && is_array( $input['auto_post_types'] ) ? $input['auto_post_types'] : array();
		$output['auto_post_types'] = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );

		$categories = isset( $input['auto_categories'] ) && is_array( $input['auto_categories'] ) ? $input['auto_categories'] : array();
		$output['auto_categories'] = array_values( array_filter( array_map( 'absint', $categories ) ) );

		if ( empty( $output['auto_post_types'] ) ) {
			$output['auto_post_types'] = $defaults['auto_post_types'];
		}

		return wp_parse_args( $output, $defaults );
	}

	/**
	 * Sanitize auto position option.
	 *
	 * @param string $position Position slug.
	 * @return string
	 */
	private static function sanitize_auto_position( $position ) {
		$allowed  = array( 'before_content', 'after_content', 'both', 'before_comments' );
		$position = sanitize_key( $position );

		return in_array( $position, $allowed, true ) ? $position : 'after_content';
	}

	/**
	 * Render settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = BGG_Steam_Curator_Plugin::get_options();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BG-GAMER Steam Curator', 'bg-gamer-steam-curator' ); ?></h1>
			<p><?php esc_html_e( 'Configure the Steam Curator follow widget. The real follow action happens on Steam; this widget only opens the curator page.', 'bg-gamer-steam-curator' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'bgg_steam_curator_settings' ); ?>

				<h2><?php esc_html_e( 'Content', 'bg-gamer-steam-curator' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::text_field( 'url', __( 'Steam Curator URL', 'bg-gamer-steam-curator' ), $options['url'], 'url' ); ?>
					<?php self::text_field( 'curator_name', __( 'Curator name', 'bg-gamer-steam-curator' ), $options['curator_name'] ); ?>
					<?php self::text_field( 'title', __( 'Title', 'bg-gamer-steam-curator' ), $options['title'] ); ?>
					<?php self::textarea_field( 'description', __( 'Description', 'bg-gamer-steam-curator' ), $options['description'] ); ?>
					<?php self::media_field( 'logo_url', __( 'BG-GAMER logo', 'bg-gamer-steam-curator' ), $options['logo_url'] ); ?>
					<?php self::media_field( 'steam_icon_url', __( 'Steam icon', 'bg-gamer-steam-curator' ), $options['steam_icon_url'] ); ?>
					<?php self::text_field( 'followers', __( 'Followers', 'bg-gamer-steam-curator' ), $options['followers'] ); ?>
					<?php self::text_field( 'recommendations', __( 'Recommendations', 'bg-gamer-steam-curator' ), $options['recommendations'] ); ?>
					<?php self::text_field( 'primary_button', __( 'Primary button text', 'bg-gamer-steam-curator' ), $options['primary_button'] ); ?>
					<?php self::text_field( 'secondary_link', __( 'Secondary link text', 'bg-gamer-steam-curator' ), $options['secondary_link'] ); ?>
				</table>

				<h2><?php esc_html_e( 'Display', 'bg-gamer-steam-curator' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::layout_field( $options['layout'] ); ?>
					<?php self::checkbox_field( 'sticky', __( 'Enable sticky mode', 'bg-gamer-steam-curator' ), $options['sticky'] ); ?>
					<?php self::checkbox_field( 'floating', __( 'Enable floating widget', 'bg-gamer-steam-curator' ), $options['floating'] ); ?>
				</table>

				<h2><?php esc_html_e( 'Automatic placement', 'bg-gamer-steam-curator' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::checkbox_field( 'auto_enabled', __( 'Automatically show in posts', 'bg-gamer-steam-curator' ), $options['auto_enabled'] ); ?>
					<?php self::post_types_field( (array) $options['auto_post_types'] ); ?>
					<?php self::categories_field( (array) $options['auto_categories'] ); ?>
					<?php self::auto_position_field( $options['auto_position'] ); ?>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<script>
		(function () {
			function bindMediaButton(button) {
				button.addEventListener('click', function (event) {
					event.preventDefault();

					var input = document.getElementById(button.dataset.target);
					var frame = wp.media({
						title: button.dataset.title,
						button: { text: button.dataset.button },
						multiple: false
					});

					frame.on('select', function () {
						var attachment = frame.state().get('selection').first().toJSON();
						input.value = attachment.url || '';
					});

					frame.open();
				});
			}

			document.querySelectorAll('.bgg-steam-curator-media').forEach(bindMediaButton);
		}());
		</script>
		<?php
	}

	/**
	 * Render text field row.
	 *
	 * @param string $key   Option key.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 * @param string $type  Input type.
	 */
	private static function text_field( $key, $label, $value, $type = 'text' ) {
		?>
		<tr>
			<th scope="row"><label for="bgg-steam-curator-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input class="regular-text" id="bgg-steam-curator-<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>"></td>
		</tr>
		<?php
	}

	/**
	 * Render textarea row.
	 *
	 * @param string $key   Option key.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 */
	private static function textarea_field( $key, $label, $value ) {
		?>
		<tr>
			<th scope="row"><label for="bgg-steam-curator-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><textarea class="large-text" rows="4" id="bgg-steam-curator-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[' . $key . ']' ); ?>"><?php echo esc_textarea( $value ); ?></textarea></td>
		</tr>
		<?php
	}

	/**
	 * Render media URL field row.
	 *
	 * @param string $key   Option key.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 */
	private static function media_field( $key, $label, $value ) {
		$field_id = 'bgg-steam-curator-' . $key;
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input class="regular-text" id="<?php echo esc_attr( $field_id ); ?>" type="url" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<button class="button bgg-steam-curator-media" type="button" data-target="<?php echo esc_attr( $field_id ); ?>" data-title="<?php echo esc_attr( $label ); ?>" data-button="<?php esc_attr_e( 'Use this image', 'bg-gamer-steam-curator' ); ?>"><?php esc_html_e( 'Choose from Media Library', 'bg-gamer-steam-curator' ); ?></button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render checkbox row.
	 *
	 * @param string $key   Option key.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 */
	private static function checkbox_field( $key, $label, $value ) {
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td><label><input type="checkbox" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[' . $key . ']' ); ?>" value="1" <?php checked( '1', (string) $value ); ?>> <?php esc_html_e( 'Enabled', 'bg-gamer-steam-curator' ); ?></label></td>
		</tr>
		<?php
	}

	/**
	 * Render layout select.
	 *
	 * @param string $value Current layout.
	 */
	private static function layout_field( $value ) {
		$options = array(
			'horizontal' => __( 'Standard / horizontal', 'bg-gamer-steam-curator' ),
			'compact'    => __( 'Compact', 'bg-gamer-steam-curator' ),
			'floating'   => __( 'Floating', 'bg-gamer-steam-curator' ),
		);
		?>
		<tr>
			<th scope="row"><label for="bgg-steam-curator-layout"><?php esc_html_e( 'Default layout', 'bg-gamer-steam-curator' ); ?></label></th>
			<td>
				<select id="bgg-steam-curator-layout" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[layout]' ); ?>">
					<?php foreach ( $options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render post type checkboxes.
	 *
	 * @param array<int,string> $selected Selected post types.
	 */
	private static function post_types_field( $selected ) {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Post types', 'bg-gamer-steam-curator' ); ?></th>
			<td>
				<?php foreach ( $post_types as $post_type ) : ?>
					<label style="display:block;margin:0 0 6px;">
						<input type="checkbox" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[auto_post_types][]' ); ?>" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $selected, true ) ); ?>>
						<?php echo esc_html( $post_type->labels->singular_name ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render categories multi-select.
	 *
	 * @param array<int,int> $selected Selected category IDs.
	 */
	private static function categories_field( $selected ) {
		$categories = get_categories( array( 'hide_empty' => false ) );
		?>
		<tr>
			<th scope="row"><label for="bgg-steam-curator-categories"><?php esc_html_e( 'Categories', 'bg-gamer-steam-curator' ); ?></label></th>
			<td>
				<select id="bgg-steam-curator-categories" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[auto_categories][]' ); ?>" multiple size="8" style="min-width:260px;">
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( in_array( (int) $category->term_id, $selected, true ) ); ?>><?php echo esc_html( $category->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Leave empty to allow all categories.', 'bg-gamer-steam-curator' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render auto position select.
	 *
	 * @param string $value Current value.
	 */
	private static function auto_position_field( $value ) {
		$options = array(
			'before_content'  => __( 'Before content', 'bg-gamer-steam-curator' ),
			'after_content'   => __( 'After content', 'bg-gamer-steam-curator' ),
			'both'            => __( 'Before and after content', 'bg-gamer-steam-curator' ),
			'before_comments' => __( 'Before comments', 'bg-gamer-steam-curator' ),
		);
		?>
		<tr>
			<th scope="row"><label for="bgg-steam-curator-auto-position"><?php esc_html_e( 'Auto position', 'bg-gamer-steam-curator' ); ?></label></th>
			<td>
				<select id="bgg-steam-curator-auto-position" name="<?php echo esc_attr( BGG_STEAM_CURATOR_OPTION . '[auto_position]' ); ?>">
					<?php foreach ( $options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}
}
