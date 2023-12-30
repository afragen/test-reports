<?php
/**
 * Test Reports
 *
 * @package Test_Reports
 * @author Andy Fragen, Colin Stewart.
 * @license GPL-3.0-or-later
 */

namespace Fragen_Stewart\Test_Reports;

/**
 * Settings.
 */
class Settings {
	/**
	 * Holds main plugin file.
	 *
	 * @var string
	 */
	private static $plugin_file;

	/**
	 * Holds the plugin's base URL.
	 *
	 * @var string
	 */
	private static $plugin_base_url;

	/**
	 * Holds the plugin's version.
	 *
	 * @var string
	 */
	private static $plugin_version;

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 */
	public function __construct( $file ) {
		self::$plugin_file     = $file;
		self::$plugin_version  = get_file_data( self::$plugin_file, [ 'Version' => 'Version' ] )['Version'];
		$directory             = basename( dirname( $file ) );
		self::$plugin_base_url = plugin_dir_url( $directory . '/' . basename( $file ) );
	}

	/**
	 * Loads up the settings.
	 *
	 * @return void
	 */
	public function run() {
		$this->load_hooks();
	}

	/**
	 * Loads hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', [ $this, 'add_plugin_menu' ] );

		/**
		 * Filters whether to show an item in the admin bar.
		 *
		 * @param bool Whether to show an item in the admin bar. Default true.
		 */
		if ( apply_filters( 'test_reports_show_in_admin_bar', true ) ) {
			add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 80 );
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueues scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		/** This filter is documented in src/Test_Reports/WPTR_Settings.php */
		if ( is_user_logged_in() && apply_filters( 'test_reports_show_in_admin_bar', true ) ) {
			wp_enqueue_style(
				'test-reports-admin-bar',
				self::$plugin_base_url . 'src/css/test-reports-admin-bar.css',
				[],
				self::$plugin_version
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_admin() && isset( $_GET['page'] ) && 'test-reports' === $_GET['page'] ) {
			wp_enqueue_style(
				'test-reports-template',
				self::$plugin_base_url . 'src/css/test-reports-template.css',
				[],
				self::$plugin_version
			);

			wp_enqueue_script(
				'test-reports-options',
				self::$plugin_base_url . 'src/js/test-reports-options.js',
				[ 'wp-a11y', 'wp-i18n' ],
				self::$plugin_version,
				true
			);

			wp_enqueue_script(
				'test-reports-clipboard',
				self::$plugin_base_url . 'src/js/test-reports-clipboard.js',
				[ 'jquery', 'clipboard' ],
				self::$plugin_version,
				true
			);
		}
	}

	/**
	 * Adds plugin menu to Tools or Settings.
	 *
	 * @return void
	 */
	public function add_plugin_menu() {
		$parent     = is_multisite() ? 'settings.php' : 'tools.php';
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';

		add_submenu_page(
			$parent,
			esc_html_x( 'Test Reports', 'Page title', 'test-reports' ),
			esc_html_x( 'Test Reports', 'Menu item', 'test-reports' ),
			$capability,
			'test-reports',
			[ $this, 'print_settings_page' ]
		);
	}

	/**
	 * Defines the menu for the admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar object.
	 * @return void
	 */
	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {
		// Exit if user doesn't have correct capabilities.
		if ( is_multisite() && ! is_super_admin() ) {
			return;
		}

		$wp_admin_bar->add_menu(
			[
				'id'    => 'test-reports',
				'title' => '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">' . _x( 'Test Reports', 'Menu item', 'test-reports' ) . '</span>',
				'href'  => add_query_arg(
					[ 'page' => 'test-reports' ],
					is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'tools.php' )
				),
				'meta'  => [ 'title' => __( 'Get a report template.', 'test-reports' ) ],
			]
		);
	}

	/**
	 * Prints the template for the settings page.
	 *
	 * @return void
	 */
	public function print_settings_page() {
		$introduction  = '<p>' . __( 'Get a report template for pasting into Trac, GitHub or HackerOne.', 'test-reports' ) . '</p>';
		$introduction .= '<p>' . __( 'After pasting the template, complete each section and submit your report.', 'test-reports' ) . '</p>';

		$report_template = new Report_Template();
		?>
		<div class="wrap">
			<div class="test-reports">
				<div class="test-reports-introduction">
					<h1><?php echo esc_html_x( 'Test Reports', 'Page title', 'test-reports' ); ?></h1>
					<?php echo wp_kses_post( $introduction ); ?>

					<div class="test-reports-options">
						<div class="report-type">
							<fieldset>
								<legend><?php esc_html_e( 'Report Type:', 'test-reports' ); ?></legend>
								<div class="test-reports-radio">
									<label>
										<input type="radio" name="report-type" value="bug-report" checked>
										<?php esc_html_e( 'Bug Report', 'test-reports' ); ?>
									</label>
								</div>
								<div class="test-reports-radio">
									<label>
										<input type="radio" name="report-type" value="bug-reproduction">
										<?php esc_html_e( 'Bug Reproduction', 'test-reports' ); ?>
									</label>
								</div>
								<div class="test-reports-radio">
									<label>
										<input type="radio" name="report-type" value="patch-testing">
										<?php esc_html_e( 'Patch Testing', 'test-reports' ); ?>
									</label>
								</div>
								<div class="test-reports-radio">
									<label>
										<input type="radio" name="report-type" value="security-vulnerability">
										<?php esc_html_e( 'Security Vulnerability', 'test-reports' ); ?>
									</label>
								</div>
							</fieldset>
						</div>
						<div class="report-location">
							<fieldset>
								<legend><?php esc_html_e( 'Report Location:', 'test-reports' ); ?></legend>
								<div class="test-reports-radio">
									<label>
										<input type="radio" name="report-location" value="trac" checked>
										<?php esc_html_e( 'Trac', 'test-reports' ); ?>
									</label>
								</div>
								<div class="test-reports-radio">
									<label>
										<input type="radio" name="report-location" value="github">
										<?php esc_html_e( 'GitHub', 'test-reports' ); ?>
									</label>
								</div>
							</fieldset>
						</div>
					</div>
				</div>

				<div class="test-reports-templates">
					<?php $report_template->print_report_template( 'Bug Report', 'bug-report', 'trac' ); ?>
					<?php $report_template->print_report_template( 'Bug Report', 'bug-report', 'github', true ); ?>

					<?php $report_template->print_report_template( 'Reproduction Report', 'bug-reproduction', 'trac', true ); ?>
					<?php $report_template->print_report_template( 'Reproduction Report', 'bug-reproduction', 'github', true ); ?>

					<?php $report_template->print_report_template( 'Test Report', 'patch-testing', 'trac', true ); ?>
					<?php $report_template->print_report_template( 'Test Report', 'patch-testing', 'github', true ); ?>

					<?php $report_template->print_report_template( 'Security Vulnerability', 'security-vulnerability', 'github', true ); ?>
				</div>
		</div>
		<?php
	}
}
