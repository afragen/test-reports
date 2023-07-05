<?php
/**
 * Report a Bug
 *
 * @package Report_A_Bug
 * @author Andy Fragen, Colin Stewart.
 * @license MIT
 */

/**
 * Plugin Name:       Report a Bug
 * Plugin URI:        https://wordpress.org/plugins/report-a-bug/
 * Description:       Provide easily accessible data for bug reporting.
 * Author:            WordPress Upgrade/Install Team
 * Version:           0.2.0
 * Network:           true
 * Author URI:        https://make.wordpress.org/core/components/upgrade-install/
 * Text Domain:       report-a-bug
 * Domain Path:       /languages
 * License:           MIT
 * License URI:       https://www.opensource.org/licenses/MIT
 * GitHub Plugin URI: https://github.com/afragen/report-a-bug
 * Primary Branch:    main
 * Requires at least: 5.9
 * Requires PHP:      7.2
 */

// Exit if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';

add_action(
	'plugins_loaded',
	function() {
		( new RAB_Settings( __FILE__ ) )->run();
	}
);

add_filter( 'wpbt_hide_report_a_bug', '__return_true' );
