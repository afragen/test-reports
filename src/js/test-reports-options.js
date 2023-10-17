/**
 * Options: Handles report options.
 *
 * @package Test_Reports
 * @license GPL-3.0-or-later
 */

const templates = document.querySelectorAll( '.template' );
const options   = document.querySelectorAll( 'input[name="report-type"], input[name="report-location"]' );

options.forEach( option => option.addEventListener( 'change', toggleReportVisibility ) );

/**
 * Toggles report visibility.
 */
function toggleReportVisibility() {
	const type     = document.querySelector( 'input[name="report-type"]:checked' ).value;
	const location = 'security-vulnerability' === type
					? 'github'
					: document.querySelector( 'input[name="report-location"]:checked' ).value;

	if ( 'security-vulnerability' === type ) {
		document.querySelector( 'input[name="report-location"][value="github"]' ).checked = true;
	}

	templates.forEach( template => template.classList.toggle( 'hidden', template.dataset.type !== type || template.dataset.location !== location ) );
	document.querySelector( '.report-location' ).classList.toggle( 'hidden', 'security-vulnerability' === type );
}
