/**
 * Clipboard: Initializes "Copy to clipboard" buttons.
 *
 * @package Test_Reports
 * @license GPL-3.0-or-later
 */

const testReportClipboard = new ClipboardJS( '.copy-to-clipboard button' );

testReportClipboard.on(
	'success',
	function( e ) {
		const success = jQuery( e.trigger ).next( '.success' );

		window.wp.a11y.speak( wp.i18n.__( 'Copied to clipboard', 'test-reports' ) );

		success.removeClass( 'hidden' );

		setTimeout(
			function() {
				success.addClass( 'hidden' );
			},
			3000
		);
	}
);
