<?php
/**
 * Test Reports
 *
 * @package Test_Reports
 * @author Andy Fragen, Colin Stewart.
 * @license GPL-3.0-or-later
 */

/**
 * Plugin Name:       Test Reports
 * Plugin URI:        https://wordpress.org/plugins/test-reports/
 * Description:       Get templates with useful information to help you submit reports to WordPress.
 * Author:            WordPress Upgrade/Install Team
 * Version:           1.1.0
 * Network:           true
 * Author URI:        https://make.wordpress.org/core/components/upgrade-install/
 * Text Domain:       test-reports
 * Domain Path:       /languages
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * GitHub Plugin URI: https://github.com/afragen/test-reports
 * Primary Branch:    main
 * Requires at least: 5.9
 * Requires PHP:      7.0
 */

namespace Fragen_Stewart\Test_Reports;

// Exit if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';

add_action(
	'plugins_loaded',
	static function () {
		( new Settings( __FILE__ ) )->run();
	}
);

// Hide Report a Bug in WordPress Beta Tester.
add_filter( 'wpbt_hide_report_a_bug', '__return_true' );
