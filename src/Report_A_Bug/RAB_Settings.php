<?php
/**
 * Report a Bug
 *
 * @package Report_A_Bug
 * @author Andy Fragen, Colin Stewart.
 * @license MIT
 */

/**
 * Settings.
 */
class RAB_Settings {
	/**
	 * Holds main plugin file.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Load up the Settings.
	 *
	 * @return void
	 */
	public function run() {
		$this->load_hooks();
		( new RAB_Bug_Report( $this->file ) )->load_hooks();
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', [ $this, 'add_plugin_menu' ] );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 80 );
	}

	/**
	 * Add plugin menu to Tools or Settings.
	 *
	 * @return void
	 */
	public function add_plugin_menu() {
		$parent     = is_multisite() ? 'settings.php' : 'tools.php';
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';

		add_submenu_page(
			$parent,
			esc_html__( 'Report a Bug', 'report-a-bug' ),
			esc_html_x( 'Report a Bug', 'Menu item', 'report-a-bug' ),
			$capability,
			'report-a-bug',
			[ $this, 'create_settings_page' ]
		);
	}

	/**
	 * Defines the menu for the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar object.
	 * @return void
	 */
	public function admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {
		// Exit if user doesn't have correct capabilities.
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		/**
		 * Action hook to add adminbar menu.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Admin_Bar $wpadminbar The WP_Admin_Bar object.
		 */
		do_action( 'report_a_bug_add_admin_bar_menu', $wp_admin_bar );
	}

	/**
	 * Create the template for all settings pages.
	 *
	 * @return void
	 */
	public function create_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Report a Bug', 'report-a-bug' ); ?></h1>
		<?php

		/**
		 * Action hook to add admin page data to appropriate $tab.
		 *
		 * @since 1.0.0
		 *
		 * @param string $action Save action for appropriate WordPress installation.
		 *                       Single site or Multisite.
		 */
		do_action( 'report_a_bug_add_admin_page' );
		echo '</div>';
	}
}
