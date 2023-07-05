/**
 * Clipboard: Initializes Bug Report "Copy to clipboard" buttons.
 *
 * @package Report_A_Bug
 */

const bugReportClipboard = new ClipboardJS( "#report-a-bug-bug-reports button" );

bugReportClipboard.on(
	"success",
	function( e ) {
		const success = jQuery( e.trigger ).next( ".success" );

		success.removeClass( "hidden" );

		setTimeout(
			function() {
				success.addClass( "hidden" );
			},
			3000
		);
	}
);
