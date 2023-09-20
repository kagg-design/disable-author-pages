<?php
/**
 * DisableAuthorPages class file.
 *
 * @package DisableAuthorPages
 */

/**
 * Class DisableAuthorPages.
 */
class DisableAuthorPages {

	/**
	 * Redirect non-authors option name.
	 */
	private const REDIRECT_NON_AUTHORS = 'disable_author_pages_redirect_non_authors';

	/**
	 * Activate option name.
	 */
	private const ACTIVATE = 'disable_author_pages_activate';

	/**
	 * Admin-only option name.
	 */
	private const ADMIN_ONLY = 'disable_author_pages_adminonly';

	/**
	 * Destination option name.
	 */
	private const DESTINATION = 'disable_author_pages_destination';

	/**
	 * Status option name.
	 */
	private const STATUS = 'disable_author_pages_status';

	/**
	 * Author link option name.
	 */
	private const AUTHOR_LINK = 'disable_author_pages_authorlink';

	/**
	 * Menu slug.
	 */
	private const MENU_SLUG = 'disable_author_pages_settings';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'disable_author_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'options_menu' ] );
		add_action( 'plugins_loaded', [ $this, 'load_translations' ] );
		add_filter( 'author_link', [ $this, 'disable_author_link' ] );
		add_filter(
			'plugin_action_links_disable-author-pages/plugin.php',
			[ $this, 'plugin_settings_link' ]
		);
	}

	/**
	 * Redirect the user.
	 *
	 * This function is registered to the template_redirect hook and  checks
	 * to redirect the user to the selected page (or to the homepage).
	 */
	public function disable_author_page(): void {
		global $post;

		$author_request = false;

		if (
			is_404() &&
			(int) get_option( self::REDIRECT_NON_AUTHORS ) === 1 &&
			( get_query_var( 'author' ) || get_query_var( 'author_name' ) )
		) {
			$author_request = true;
		}

		if (
			is_404() &&
			! ( get_query_var( 'author' ) || get_query_var( 'author_name' ) )
		) {
			return;
		}

		if (
			( is_author() || $author_request ) &&
			(int) get_option( self::ACTIVATE ) === 1
		) {
			$admin_only = get_option( self::ADMIN_ONLY, '0' );
			$author_can = false;

			if ( $admin_only && is_object( $post ) && ! is_404() ) {
				$author_can = author_can( get_the_ID(), 'manage_options' );
			}

			if (
				( $admin_only && $author_can ) ||
				( ! $admin_only && ! is_404() ) ||
				( is_404() && ( (int) get_option( self::REDIRECT_NON_AUTHORS ) === 1 ) )
			) {
				$status = get_option( self::STATUS, '301' );
				$url    = get_option( self::DESTINATION, '' );
				$url    = $url ? get_permalink( $url ) : home_url();

				wp_safe_redirect( $url, $status );
				exit;
			}
		}
	}

	/**
	 * Register all settings
	 *
	 * Register all the settings, the plugin uses.
	 */
	public function register_settings(): void {
		register_setting( 'disable_author_pages_settings', self::ACTIVATE );
		register_setting( 'disable_author_pages_settings', self::DESTINATION );
		register_setting( 'disable_author_pages_settings', self::STATUS );
		register_setting( 'disable_author_pages_settings', self::AUTHOR_LINK );
		register_setting( 'disable_author_pages_settings', self::ADMIN_ONLY );
		register_setting( 'disable_author_pages_settings', self::REDIRECT_NON_AUTHORS );
	}

	/**
	 * Overwrite the author url with an empty string.
	 *
	 * @param string|mixed $content Url to author page.
	 *
	 * @return string
	 */
	public function disable_author_link( $content ): string {
		if ( (int) get_option( self::AUTHOR_LINK, '0' ) === 1 ) {
			return '';
		}

		return (string) $content;
	}

	/**
	 * Load plugin textdomain.
	 *
	 * Load plugin textdomain with translations for the backend settings page.
	 */
	public function load_translations(): void {
		load_plugin_textdomain(
			'disable-author-pages',
			false,
			apply_filters( 'disable_author_pages_translationpath', dirname( plugin_basename( __FILE__ ) ) . '/languages/' )
		);
	}

	/**
	 * Generate the options menu page.
	 *
	 * Generate the options page under the options menu.
	 */
	public function options_menu(): void {
		add_options_page(
			__( 'Disable Author Pages', 'disable-author-pages' ),
			__( 'Author Pages', 'disable-author-pages' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'create_options_disable_author_menu' ]
		);
	}

	/**
	 * Generate the options page for the plugin.
	 */
	public function create_options_disable_author_menu(): void {
		$selected_page = get_option( self::DESTINATION );

		?>
		<div class="wrap" id="disableauthorpages">
			<h2><?php esc_html_e( 'Disable Author settings', 'disable-author-pages' ); ?></h2>
			<p><?php esc_html_e( 'Settings to disable the author pages.', 'disable-author-pages' ); ?></p>
			<form method="POST" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
				settings_fields( 'disable_author_pages_settings' );

				$activate             = get_option( self::ACTIVATE ) ? ' checked ' : '';
				$status_301           = get_option( self::STATUS ) === '301' ? ' selected ' : '';
				$status_307           = get_option( self::STATUS ) === '307' ? ' selected ' : '';
				$author_link          = get_option( self::AUTHOR_LINK ) ? ' checked ' : '';
				$redirect_non_authors = get_option( self::REDIRECT_NON_AUTHORS ) ? ' checked ' : '';
				$admin_only           = get_option( self::ADMIN_ONLY ) ? ' checked ' : '';

				?>
				<table class="form-table">
					<tbody>
					<tr>
						<td style="width: 13px;">
							<label for="disable_author_pages_activate"></label>
							<input
									type="checkbox" id="disable_author_pages_activate"
									name="disable_author_pages_activate"
									value="1" <?php echo esc_attr( $activate ); ?> />
						</td>
						<td>
							<?php esc_html_e( 'Disable Author Pages', 'disable-author-pages' ); ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="disable_author_pages_status"></label>
							<select id="disable_author_pages_status" name="disable_author_pages_status">
								<option value="301" <?php echo esc_attr( $status_301 ); ?> >
									<?php esc_html_e( '301 (Moved Permanently)', 'disable-author-pages' ); ?>
								</option>
								<option value="307" <?php echo esc_attr( $status_307 ); ?> >
									<?php esc_html_e( '307 (Temporary Redirect)', 'disable-author-pages' ); ?>
								</option>
							</select>
							<?php esc_html_e( 'HTTP Status', 'disable-author-pages' ); ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo wp_dropdown_pages( "name=disable_author_pages_destination&selected=$selected_page&echo=0" );
							esc_html_e( 'Destination Page', 'disable-author-pages' );
							?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="disable_author_pages_authorlink"></label>
							<input
									type="checkbox" id="disable_author_pages_authorlink"
									name="disable_author_pages_authorlink"
									value="1" <?php echo esc_attr( $author_link ); ?> />
							<?php esc_html_e( 'Disable Author Link', 'disable-author-pages' ); ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="disable_author_pages_redirect_non_authors"></label>
							<input
									type="checkbox" id="disable_author_pages_redirect_non_authors"
									name="disable_author_pages_redirect_non_authors"
									value="1" <?php echo esc_attr( $redirect_non_authors ); ?> />
							<?php esc_html_e( 'Redirect non exists author pages', 'disable-author-pages' ); ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="disable_author_pages_adminonly"></label>
							<input
									type="checkbox" id="disable_author_pages_adminonly"
									name="disable_author_pages_adminonly"
									value="1" <?php echo esc_attr( $admin_only ); ?> />
							<?php esc_html_e( 'Disable for admin author pages only', 'disable-author-pages' ); ?>
						</td>
					</tr>
					</tbody>
				</table>
				<br>
				<input
						type="submit" class="button-primary"
						value="<?php esc_html_e( 'Save Changes', 'disable-author-pages' ); ?>"/>
			</form>
		</div>
		<?php
	}

	/**
	 * Add link to plugin page.
	 *
	 * @since 0.10
	 *
	 * @param array|mixed $actions  An array of plugin action links.
	 *                              By default, this can include 'activate', 'deactivate', and 'delete'.
	 *                              With Multisite active this can also include 'network_active' and 'network_only'
	 *                              items.
	 *
	 * @return array|string[] Plugin links
	 */
	public function plugin_settings_link( $actions ): array {
		$new_actions = [
			'settings' =>
				'<a href="' .
				admin_url( 'options-general.php?page=' . self::MENU_SLUG ) .
				'" aria-label="' . esc_attr__( 'Disable Author Pages Settings', 'disable-author-pages' ) . '">' .
				esc_html__( 'Settings', 'disable-author-pages' ) . '</a>',
		];

		return array_merge( $new_actions, (array) $actions );
	}
}
