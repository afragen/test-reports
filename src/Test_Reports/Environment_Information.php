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
 * Environment Information.
 */
class Environment_Information {
	/**
	 * Holds environment information.
	 *
	 * @var array
	 */
	private static $environment_information = [];

	/**
	 * Holds the string for unknown values.
	 *
	 * @var string
	 */
	private static $unknown;

	/**
	 * Holds the string for no activated plugins.
	 *
	 * @var string
	 */
	private static $none_activated;

	/**
	 * Constructor.
	 */
	public function __construct() {
		self::$unknown        = 'Could not determine';
		self::$none_activated = 'None activated';
	}

	/**
	 * Retrieves environment information.
	 *
	 * @return array Environment information.
	 */
	public function get_environment_information() {
		if ( empty( self::$environment_information ) ) {
			$this->set_environment_information();
		}

		return self::$environment_information;
	}

	/**
	 * Sets environment information.
	 *
	 * @return void
	 */
	private function set_environment_information() {
		global $wp_version;

		self::$environment_information['WordPress'] = $wp_version;
		self::$environment_information['PHP']       = phpversion();

		$this->set_server();
		$this->set_database();
		$this->set_browser();
		$this->set_os();
		$this->set_theme();
		$this->set_mu_plugins();
		$this->set_plugins();
	}

	/**
	 * Sets the browser's operating system's name.
	 *
	 * @return void
	 */
	private function set_os() {
		self::$environment_information['OS'] = self::$unknown;

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return;
		}

		$agent   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		$os_list = [
			// At this time, Windows 11 cannot be detected server-side by examining the User agent.
			'/windows nt 10/i'      => 'Windows 10/11',
			'/windows nt 6.3/i'     => 'Windows 8.1',
			'/windows nt 6.2/i'     => 'Windows 8',
			'/windows nt 6.1/i'     => 'Windows 7',
			'/windows nt 6.0/i'     => 'Windows Vista',
			'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     => 'Windows XP',
			'/windows xp/i'         => 'Windows XP',
			'/windows nt 5.0/i'     => 'Windows 2000',
			'/windows me/i'         => 'Windows ME',
			'/win98/i'              => 'Windows 98',
			'/win95/i'              => 'Windows 95',
			'/win16/i'              => 'Windows 3.11',
			'/macintosh|mac os x/i' => 'macOS',
			'/mac_powerpc/i'        => 'Mac OS 9',
			'/linux/i'              => 'Linux',
			'/ubuntu/i'             => 'Ubuntu',
			'/iphone/i'             => 'iPhone',
			'/ipod/i'               => 'iPod',
			'/ipad/i'               => 'iPad',
			'/android/i'            => 'Android',
			'/blackberry/i'         => 'BlackBerry',
			'/webos/i'              => 'Mobile',
		];

		foreach ( $os_list as $regex => $value ) {
			if ( preg_match( $regex, $agent ) ) {
				self::$environment_information['OS'] = $value;
			}
		}
	}

	/**
	 * Sets the server's name.
	 *
	 * @return void
	 */
	private function set_server() {
		self::$environment_information['Server'] = self::$unknown;

		if ( empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return;
		}

		self::$environment_information['Server'] = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
	}

	/**
	 * Sets the database's extension, server version and client version.
	 *
	 * @return void
	 */
	private function set_database() {
		global $wpdb;

		self::$environment_information['Database'] = self::$unknown;

		if ( ! $wpdb ) {
			return;
		}

		// Populate the database debug fields.
		if ( is_resource( $wpdb->dbh ) ) {
			// Old mysql extension.
			$extension = 'mysql';
		} elseif ( is_object( $wpdb->dbh ) ) {
			// mysqli or PDO.
			$extension = get_class( $wpdb->dbh );
		} else {
			// Unknown sql extension.
			$extension = null;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$server_version = $wpdb->get_var( 'SELECT VERSION()' );

		if ( defined( 'DB_ENGINE' ) && 'sqlite' === DB_ENGINE ) {
			$client_version = class_exists( 'SQLite3' ) ? \SQLite3::version()['versionString'] : 'Unavailable';
		} elseif ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
			if ( property_exists( $wpdb->dbh, 'client_info' ) ) {
				$client_version = $wpdb->dbh->client_info;
				$client_version = explode( ' - ', $client_version )[0];
			} elseif ( isset( $GLOBALS['@pdo'] ) && $GLOBALS['@pdo'] instanceof \PDO ) {
				// phpcs:disable WordPress.DB.RestrictedClasses.mysql__PDO
				$server_version = $GLOBALS['@pdo']->getAttribute( \PDO::ATTR_SERVER_VERSION );
				$client_version = $GLOBALS['@pdo']->getAttribute( \PDO::ATTR_CLIENT_VERSION );
				// phpcs:enable WordPress.DB.RestrictedClasses.mysql__PDO
			} else {
				$client_version = 'Unavailable';
			}
		} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_client_info,PHPCompatibility.Extensions.RemovedExtensions.mysql_DeprecatedRemoved
			if ( preg_match( '|[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}|', mysqli_get_client_info(), $matches ) ) {
				$client_version = $matches[0];
			} else {
				$client_version = 'Unavailable';
			}
		}

		self::$environment_information['Database'] = $extension . ' (Server: ' . $server_version . ' / Client: ' . $client_version . ')';
	}

	/**
	 * Sets the browser's name and version based on the user agent.
	 *
	 * @return void
	 */
	private function set_browser() {
		global $is_lynx, $is_gecko, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_IE, $is_edge;

		self::$environment_information['Browser'] = self::$unknown;

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return;
		}

		$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

		if ( false !== strpos( $agent, 'OPR' ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$is_chrome = false;
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$is_opera = true;
		}

		$browsers = [
			'Lynx'              => $is_lynx,
			'Gecko'             => $is_gecko,
			'Opera'             => $is_opera,
			'Netscape 4'        => $is_NS4,
			'Safari'            => $is_safari,
			'Internet Explorer' => $is_IE,
			'Edge'              => $is_edge,
			'Chrome'            => $is_chrome,
			'Firefox'           => false !== stripos( $agent, 'Firefox' ),
		];
		$filtered = array_filter( $browsers );

		if ( empty( $filtered ) ) {
			return;
		}

		$browser                                  = array_keys( $filtered );
		self::$environment_information['Browser'] = end( $browser );

		// Try to get the browser version.
		if ( 'Safari' === self::$environment_information['Browser'] ) {
			$regex = '/Version\/([0-9\.\-]+)/';
		} elseif ( 'Edge' === self::$environment_information['Browser'] ) {
			$regex = '/Edg\/([0-9\.\-]+)/';
		} else {
			$regex = '/' . self::$environment_information['Browser'] . '\/([0-9\.\-]+)/';
		}

		preg_match( $regex, $agent, $version );

		self::$environment_information['Browser'] .= $version ? ' ' . $version[1] : '';
		self::$environment_information['Browser'] .= wp_is_mobile() ? ' (Mobile)' : '';
	}

	/**
	 * Sets the active theme's name.
	 *
	 * @return void
	 */
	private function set_theme() {
		self::$environment_information['Theme'] = self::$unknown;

		$theme = wp_get_theme();

		if ( ! $theme->exists() ) {
			return;
		}

		self::$environment_information['Theme'] = $theme->name . ' ' . $theme->version;
	}

	/**
	 * Sets the list of active plugins.
	 *
	 * @return void
	 */
	private function set_plugins() {
		self::$environment_information['Plugins'] = [ self::$none_activated ];
		$plugins                                  = get_option( 'active_plugins' );
		$network_active_plugins                   = get_site_option( 'active_sitewide_plugins' );
		if ( $network_active_plugins ) {
			$plugins = array_unique( array_merge( $plugins, array_keys( $network_active_plugins ) ) );
		}

		if ( ! $plugins ) {
			return;
		}

		foreach ( $plugins as &$file ) {
			$path    = trailingslashit( WP_PLUGIN_DIR ) . $file;
			$data    = get_plugin_data( $path );
			$name    = $data['Name'];
			$version = $data['Version'];

			$file = "&nbsp;&nbsp;* $name $version";
		}
		unset( $file );
		natcasesort( $plugins );

		self::$environment_information['Plugins'] = $plugins;
	}

	/**
	 * Sets the list of mu-plugins.
	 *
	 * @return void
	 */
	private function set_mu_plugins() {
		self::$environment_information['MU Plugins'] = self::$none_activated;
		$mu_plugins                                  = get_mu_plugins();

		if ( ! $mu_plugins ) {
			return;
		}

		foreach ( $mu_plugins as $slug => &$file ) {
			$path    = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/' . $slug;
			$data    = get_plugin_data( $path );
			$name    = ! empty( $data['Name'] ) ? $data['Name'] : $slug;
			$version = ! empty( $data['Version'] ) ? $data['Version'] : '';

			$file = "&nbsp;&nbsp;* $name $version";
		}
		unset( $file );
		natcasesort( $mu_plugins );

		self::$environment_information['MU Plugins'] = $mu_plugins;
	}
}
