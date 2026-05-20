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
 * Report Template.
 */
class Report_Template {
	/**
	 * Holds search URLs.
	 *
	 * @var array
	 */
	private static $search_urls = [
		'trac'   => 'https://core.trac.wordpress.org/search?ticket=1',
		'github' => 'https://github.com/WordPress/gutenberg/issues',
	];

	/**
	 * Holds new report URLs.
	 *
	 * @var array
	 */
	private static $new_report_urls = [
		'trac'   => 'https://core.trac.wordpress.org/newticket',
		'github' => 'https://github.com/WordPress/gutenberg/issues/new/choose',
	];

	/**
	 * Holds environment information as a string.
	 *
	 * @var string
	 */
	private static $environment_information;

	/**
	 * Prints a report template.
	 *
	 * @param string $title  The title of the report template.
	 * @param string $type   The report type. "bug-report", "bug-reproduction", "patch-testing"
	 *                       or "security-vulnerability".
	 * @param string $format The format to use. "trac" or "github".
	 * @param bool   $hidden Whether the template should have the "hidden" HTML class.
	 * @return void
	 */
	public function print_report_template( $title, $type, $format, $hidden = false ) {
		$report_template     = $this->get_report_template( $title, $type, $format );
		$template_class_list = 'template' . ( $hidden ? ' hidden' : '' );
		?>
		<div class="<?php echo esc_attr( $template_class_list ); ?>"
			data-type="<?php echo esc_attr( $type ); ?>"
			data-location="<?php echo esc_attr( $format ); ?>">

			<h2>
				<?php
				echo esc_html( $title );
				if ( 'trac' === $format ) {
					echo ' (Trac)';
				} elseif ( 'security-vulnerability' === $type ) {
					echo ' (HackerOne)';
				} elseif ( 'github' === $format ) {
					echo ' (GitHub)';
				}
				?>
			</h2>
			<div class="template-buttons">
				<?php if ( 'bug-report' === $type ) : ?>
					<?php if ( isset( self::$search_urls[ $format ] ) ) : ?>
						<a href="<?php echo esc_url( self::$search_urls[ $format ] ); ?>" target="_blank">
							<?php esc_html_e( 'Search for an existing report', 'test-reports' ); ?>
							<span aria-hidden="true" class="dashicons dashicons-external"></span>
						</a>
					<?php endif; ?>

					<?php if ( isset( self::$new_report_urls[ $format ] ) ) : ?>
						<a href="<?php echo esc_url( self::$new_report_urls[ $format ] ); ?>" target="_blank">
							<?php esc_html_e( 'Submit a new report', 'test-reports' ); ?>
							<span aria-hidden="true" class="dashicons dashicons-external"></span>
						</a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( 'security-vulnerability' === $type ) : ?>
					<a href="https://hackerone.com/wordpress?type=team" target="_blank">
						<?php esc_html_e( 'Submit a new report', 'test-reports' ); ?>
						<span aria-hidden="true" class="dashicons dashicons-external"></span>
					</a>
				<?php endif; ?>

				<div class="copy-to-clipboard">
					<button type="button" class="button" data-clipboard-text="<?php echo esc_attr( str_replace( '&nbsp;', ' ', $report_template ) ); ?>">
						<?php esc_html_e( 'Copy to clipboard', 'test-reports' ); ?>
					</button>
					<span class="success hidden" aria-hidden="true"><?php esc_html_e( 'Copied!', 'test-reports' ); ?></span>
				</div>
			</div>
			<?php echo wp_kses_post( '<div class="card">' . nl2br( $report_template ) . '</div>' ); ?>
		</div>
		<?php
	}

	/**
	 * Generates a test report template.
	 *
	 * Strings are intentionally not translated as they are
	 * intended for posting in English-language ticketing systems.
	 *
	 * @param string $title  The title of the report template.
	 * @param string $type   The report type. "bug-report", "bug-reproduction", "patch-testing"
	 *                       or "security-vulnerability".
	 * @param string $format The format to use. "trac" or "github".
	 * @return string The test report template.
	 */
	private function get_report_template( $title, $type, $format ) {
		if ( ! isset( self::$environment_information ) ) {
			$this->set_environment_information();
		}

		$is_vulnerability = 'security-vulnerability' === $type;
		$is_wiki          = 'trac' === $format;
		$heading          = $is_wiki ? '==' : '##';
		$sub_heading      = $is_wiki ? '===' : '###';
		$last_item        = $is_wiki ? 'x' : '2';

		if ( 'bug-reproduction' === $type || 'patch-testing' === $type ) {
			return $this->get_testing_report_template( $type, $heading, $sub_heading, $last_item, $title );
		}

		$report  = ! $is_vulnerability ? "$heading $title\n" : '';
		$report .= "$sub_heading Description\n" . $this->get_description( $type ) . "\n\n";
		$report .= "$sub_heading Environment\n" . self::$environment_information . "\n\n";

		$report .= "$sub_heading Steps to Reproduce\n";
		$report .= "1.&nbsp;\n";
		$report .= "$last_item. 🐞 Bug occurs.\n\n";

		$report .= "$sub_heading Expected Results\n";
		$report .= "1.&nbsp; ✅ What should happen.\n\n";

		$report .= "$sub_heading Actual Results\n";
		$report .= "1.&nbsp; ❌ What actually happened.\n\n";

		$report .= "$sub_heading Additional Notes\n";
		$report .= "- Any additional details worth mentioning.\n\n";

		$report .= "$sub_heading Supplemental Artifacts\n";

		if ( 'trac' === $format && ! $is_vulnerability ) {
			$report .= "Add Inline: [[Image(REPLACE_WITH_IMAGE_URL)]]\nor\n";
		}

		$report .= 'Add as Attachment';

		return str_replace( "\t", '', $report );
	}

	/**
	 * Builds the Reproduction or Patch Testing report template.
	 *
	 * Structure follows the proposal in WordPress/test-handbook#104:
	 * Environment, Steps taken (result as final step), Expected behavior/result,
	 * Additional Notes, Screenshots/Screencast with results, Support Content.
	 *
	 * @param string $type        "bug-reproduction" or "patch-testing".
	 * @param string $heading     Top-level heading marker for the format.
	 * @param string $sub_heading Sub-heading marker for the format.
	 * @param string $last_item   Marker used for the final list item ("x" for Trac, "2" for GitHub).
	 * @param string $title       The title of the report template.
	 * @return string The test report template.
	 */
	private function get_testing_report_template( $type, $heading, $sub_heading, $last_item, $title ) {
		$is_patch = 'patch-testing' === $type;

		$report = "$heading $title\n";

		if ( $is_patch ) {
			$report .= "Patch tested: REPLACE_WITH_PATCH_URL\n\n";
		}

		$report .= "$sub_heading Environment\n" . self::$environment_information . "\n\n";

		$report .= "$sub_heading Steps taken\n";
		$report .= "1.&nbsp;\n";
		$report .= "$last_item. ";
		$report .= $is_patch
			? "✅ Patch is solving the problem / ❌ Patch is failing\n\n"
			: "🐞 Bug occurs / ❌ Bug is not occurring\n\n";

		if ( $is_patch ) {
			$report .= "$sub_heading Expected result\n";
			$report .= "- Explain what results you were expecting from this patch.\n\n";
		} else {
			$report .= "$sub_heading Expected behavior\n";
			$report .= "- Explain what behavior you were expecting from the ticket information.\n\n";
		}

		$report .= "$sub_heading Additional Notes\n";
		$report .= "- Any additional details worth mentioning.\n\n";

		$report .= "$sub_heading Screenshots/Screencast with results\n";
		$report .= $is_patch
			? "- Screenshot/Screencast before\n- Screenshot/Screencast after\n\n"
			: "- Screenshot showcasing the problem or the bug not occurring.\n\n";

		$report .= "$sub_heading Support Content\n";
		$report .= "- Here you can add any support content useful for testing.\n";
		$report .= "For example:\n";
		$report .= "1. Blueprint JSON\n";
		$report .= "2. Website Playground URL with parameters\n";
		$report .= "3. Snippets of code\n";
		$report .= "4. Additional Screenshots\n";
		$report .= '5. etc...';

		return str_replace( "\t", '', $report );
	}

	/**
	 * Sets the environment information to a formatted string.
	 *
	 * @return void
	 */
	private function set_environment_information() {
		$environment_information            = ( new Environment_Information() )->get_environment_information();
		$environment_information['Plugins'] = implode( "\n", $environment_information['Plugins'] );
		if ( is_array( $environment_information['MU Plugins'] ) ) {
			$environment_information['MU Plugins'] = implode( "\n", $environment_information['MU Plugins'] );
		}
		$environment_information = array_map(
			static function ( $key, $value ) {
				return str_contains( $value, '*' ) ? "- $key:\n$value" : "- $key: $value";
			},
			array_keys( $environment_information ),
			$environment_information
		);

		self::$environment_information = implode( "\n", $environment_information );
	}

	/**
	 * Gets the appropriate description for the given report type.
	 *
	 * Descriptions are intentionally not translated as they are
	 * intended for posting in English-language ticketing systems.
	 *
	 * @param string $type The report type. "bug-report" or "security-vulnerability".
	 * @return string The description.
	 */
	private function get_description( $type ) {
		switch ( $type ) {
			case 'bug-report':
				$description = 'Describe the bug.';
				break;
			case 'security-vulnerability':
				$description = 'Describe the security vulnerability.';
				break;
			default:
				$description = '';
		}

		return $description;
	}
}
