<?php
/**
 * Handles lockouts for modules and core
 *
 * @package iThemes-Security
 * @since   4.0
 */

/**
 * Class ITSEC_Lockout
 *
 * The ITSEC Lockout class is the centralized controller for detecting and blocking already locked-out users. Other
 * iThemes Security modules instruct ITSEC_Lockout to save a lock out to storage, but ITSEC Lockout will never lock
 * out a user itself.
 *
 * If a user attempts to login with valid credentials and their user ID is marked as locked out, they will be prevented
 * from logging in and the lock will remain in effect until its expiration.
 *
 * There are three types of lockouts.
 *
 *  - User ID
 *  - Username
 *  - Host
 *
 * = User ID =
 * User ID lockouts are used whenever an attacker tries to repeatedly log in with a valid username, but incorrect password.
 * By default, a host lockout will occur first ( assuming the attacker does not alter their IPs ). This is done because
 * a user ID lockout can lock out a legitimate user from signing into their account.
 *
 * = Username =
 * Username lockouts are used whenever an attacker tried to repeatedly log in with a non-existent username. Or, if
 * enabled, uses the 'admin' username. This is separate from the User ID lock out type, however the lockout message
 * is shared between the two.
 *
 * = Host =
 * Host lockouts are used whenever an IP address is flagged as an attacker. This is done via repeated 404 errors or
 * failed captcha validations. If an IP address is whitelisted, an event will be logged, but the user will not be
 * locked out. By default, host lockouts have the lowest threshold before locking out the host. The Network Brute Force
 * module does NOT create host lockouts, but utilizes ITSEC_Lockout::execute_lock() to prevent the attacker from
 * accessing the site.
 *
 * ITSEC_Lockout will store a record whenever ITSEC Lockout is instructed to perform a lockout via ::do_lockout() in the
 * itsec_temp database table. If the threshold for that lockout type has been met – the most recently added one counts –
 * an actual lockout will be saved to the itsec_lockouts table. If enabled, and enough lockouts have occurred
 * ( configurable via settings ), a host will be blacklisted instead of added to the itsec_lockouts table. Blacklisted
 * IPs are blocked at the server level. This is handled by the ban-users module.
 *
 * After the lockout has been stored, the request will be immediately exited.
 *
 * iThemes Security supports two types of whitelists. Temporary and permanent whitelists. Permanent whitelists are
 * configured in the Global Settings module and will permanently prevent a user with that IP from being locked out.
 * The temporary whitelist is a global list of admin level user's IP addresses. Whenever an admin user is logged-in and
 * using the site, their IP will be added to the whitelist for 24 hours.
 *
 * This controller also provides a number of methods to retrieve a list or clear both lockouts and temporary whitelists.
 */
final class ITSEC_Lockout {

	/** @var ITSEC_Core */
	private $core;

	private $lockout_modules;

	/**
	 * ITSEC_Lockout constructor.
	 *
	 * @param ITSEC_Core $core
	 */
	public function __construct( $core ) {

		$this->core            = $core;
		$this->lockout_modules = array(); //array to hold information on modules using this feature

		//Run database cleanup daily with cron
		if ( ! wp_next_scheduled( 'itsec_purge_lockouts' ) ) {
			wp_schedule_event( time(), 'daily', 'itsec_purge_lockouts' );
		}

		add_action( 'itsec_purge_lockouts', array( $this, 'purge_lockouts' ) );

		//Check for host lockouts
		add_action( 'init', array( $this, 'check_current_user_for_host_lockouts' ) );

		// Ensure that locked out users are prevented from checking logins.
		add_filter( 'authenticate', array( $this, 'check_authenticate_lockout' ), 30 );

		// Updated temp whitelist to ensure that admin users are automatically added.
		add_action( 'init', array( $this, 'update_temp_whitelist' ), 0 );

		//Register all plugin modules
		add_action( 'plugins_loaded', array( $this, 'register_modules' ) );

		//Set an error message on improper logout
		add_action( 'login_head', array( $this, 'set_lockout_error' ) );

		//Process clear lockout form
		add_action( 'itsec_admin_init', array( $this, 'release_lockout' ) );

		//Register Logger
		add_filter( 'itsec_logger_modules', array( $this, 'register_logger' ) );

		add_action( 'ithemes_sync_register_verbs', array( $this, 'register_sync_verbs' ) );
		add_filter( 'itsec-filter-itsec-get-everything-verbs', array( $this, 'register_sync_get_everything_verbs' ) );

		add_action( 'itsec-settings-page-init', array( $this, 'init_settings_page' ) );
		add_action( 'itsec-logs-page-init', array( $this, 'init_settings_page' ) );

		add_filter( 'itsec_notifications', array( $this, 'register_notification' ) );
		add_filter( 'itsec_lockout_notification_strings', array( $this, 'notification_strings' ) );
	}

	public function init_settings_page() {
		require_once( dirname( __FILE__ ) . '/sidebar-widget-active-lockouts.php' );
	}

	/**
	 * Check if a user has successfully logged-in, and prevent them from accessing the site if they
	 * still have a lockout in effect.
	 *
	 * @param \WP_User|\WP_Error|null $user
	 *
	 * @return WP_User|WP_Error|null
	 */
	public function check_authenticate_lockout( $user ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return $user;
		}

		$this->check_lockout( $user->ID );

		return $user;
	}

	/**
	 * Lockout a user on every page load if there host becomes locked.
	 */
	public function check_current_user_for_host_lockouts() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		global $wpdb;

		$host       = ITSEC_Lib::get_ip();
		$host_check = $wpdb->get_var( $wpdb->prepare( "SELECT `lockout_host` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_host` = %s;", array(
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ),
			$host
		) ) );

		if ( $host_check ) {
			$this->execute_lock();
		}
	}

	/**
	 * Checks if the host or user is locked out and executes lockout
	 *
	 * @since 4.0
	 *
	 * @param mixed  $user     WordPress user object or false.
	 * @param mixed  $username The username to check.
	 * @param string $type     Lockout type asking for the check.
	 *
	 * @return void
	 */
	public function check_lockout( $user = false, $username = false, $type = '' ) {
		global $wpdb, $itsec_globals;

		$wpdb->hide_errors(); //Hide database errors in case the tables aren't there

		$host           = ITSEC_Lib::get_ip();
		$username       = sanitize_text_field( trim( $username ) );
		$username_check = false;
		$user_check     = false;
		$host_check     = false;

		if ( $user !== false && $user !== '' && $user !== null ) {

			$user    = get_userdata( intval( $user ) );
			$user_id = $user->ID;

		} else {

			$user    = wp_get_current_user();
			$user_id = $user->ID;

			if ( $username !== false && $username != '' ) {
				$username_check = $wpdb->get_results( $wpdb->prepare(
					"SELECT `lockout_username`, `lockout_type` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_username`= %s;",
					date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $username
				) );
			}

			$host_check = $wpdb->get_results( $wpdb->prepare(
				"SELECT `lockout_host`, `lockout_type` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_host`= %s;",
				date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $host
			) );

		}

		if ( $user_id !== 0 && $user_id !== null ) {

			$user_check = $wpdb->get_results( $wpdb->prepare(
				"SELECT `lockout_user`, `lockout_type` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_user`= %d;",
				date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $user_id
			) );
		}

		$error = $wpdb->last_error;

		if ( strlen( trim( $error ) ) > 0 ) {
			ITSEC_Lib::create_database_tables();
		}

		if ( $host_check ) {

			$type = $type ? $type : $host_check[0]->lockout_type;
			$this->execute_lock( array( 'type' => $type ) );

		} elseif ( $user_check || $username_check ) {

			if ( ! $type ) {
				$type = $user_check ? $user_check[0]->lockout_type : $username_check[0]->lockout_type;
			}

			$lock_context = array( 'user_lock' => true, 'type' => $type );

			if ( $user ) {
				$lock_context['user'] = $user;
			} elseif ( $username ) {
				$lock_context['username'] = $username;
			}

			$this->execute_lock( $lock_context );

		}

	}

	/**
	 * Check if a given username is locked out.
	 *
	 * @param string $username
	 *
	 * @return bool
	 */
	public function is_username_locked_out( $username ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT `lockout_username` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_username` = %s;",
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $username
		) );
	}

	/**
	 * Check if a given user is locked out.
	 *
	 * @param string $user_id
	 *
	 * @return bool
	 */
	public function is_user_locked_out( $user_id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT `lockout_user` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_user` = %d;",
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $user_id
		) );
	}

	/**
	 * This persists a lockout to storage or performs a permanent ban if appropriate.
	 *
	 * The user will be immediately locked out by this method if their IP is not whitelisted.
	 *
	 * @since 4.0
	 *
	 * @param string $module   string name of the calling module
	 * @param string $username username of user
	 *
	 * @return void
	 */
	public function do_lockout( $module, $username = null ) {

		global $wpdb, $itsec_globals;

		if ( ! isset( $this->lockout_modules[$module] ) ) {
			return;
		}

		$wpdb->hide_errors(); //Hide database errors in case the tables aren't there

		$lock_host     = null;
		$lock_user     = null;
		$lock_username = null;
		$options       = $this->lockout_modules[$module];

		$host = ITSEC_Lib::get_ip();

		if ( isset( $options['host'] ) && $options['host'] > 0 ) {

			$wpdb->insert(
				$wpdb->base_prefix . 'itsec_temp',
				array(
					'temp_type'     => $options['type'],
					'temp_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
					'temp_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
					'temp_host'     => $host,
				)
			);

			$host_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` > %s AND `temp_host`= %s;",
					date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( $options['period'] * 60 ) ),
					$host
				)
			);

			if ( $host_count >= $options['host'] ) {

				$lock_host = $host;

			}

		}

		if ( $username !== null && isset( $options['user'] ) && $options['user'] > 0 ) {

			$user_id = username_exists( sanitize_text_field( $username ) );

			if ( $user_id !== false ) {

				$wpdb->insert(
					$wpdb->base_prefix . 'itsec_temp',
					array(
						'temp_type'     => $options['type'],
						'temp_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
						'temp_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
						'temp_user'     => intval( $user_id ),
						'temp_username' => sanitize_text_field( $username ),
					)
				);

				$user_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` > '%s' AND (`temp_username`= %s OR `temp_user`= %d);",
						date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( $options['period'] * 60 ) ),
						sanitize_text_field( $username ),
						intval( $user_id )
					)
				);

				if ( $user_count >= $options['user'] ) {

					$lock_user = $user_id;

				}

			} else {

				$username = sanitize_text_field( $username );

				$wpdb->insert(
					$wpdb->base_prefix . 'itsec_temp',
					array(
						'temp_type'     => $options['type'],
						'temp_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
						'temp_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
						'temp_username' => $username,
					)
				);

				$user_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` > %s AND `temp_username`= %s;",
						date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( $options['period'] * 60 ) ),
						$username
					)
				);

				if ( $user_count >= $options['user'] ) {

					$lock_username = $username;

				}

			}

		}

		$error = $wpdb->last_error;

		if ( strlen( trim( $error ) ) > 0 ) {
			ITSEC_Lib::create_database_tables();
		}

		if ( ! ITSEC_Lib::is_ip_whitelisted( $host ) && ( $lock_host !== null || $lock_user !== null || $lock_username !== null ) ) {

			$this->lockout( $options['type'], $options['reason'], $lock_host, $lock_user, $lock_username );

		} elseif ( $lock_host !== null || $lock_user !== null ) {

			global $itsec_logger;

			$itsec_logger->log_event( 'lockout', 10, array( __( 'A whitelisted host has triggered a lockout condition but was not locked out.', 'better-wp-security' ) ), sanitize_text_field( $host ) );

		}

	}

	/**
	 * Executes lockout (locks user out)
	 *
	 * @param array $context
	 * @param bool $deprecated Deprecated argument. Previously whether this is a network lock.
	 *
	 * @return void
	 */
	public function execute_lock( $context = array(), $deprecated = false ) {

		if ( func_num_args() > 1 ) {
			_deprecated_argument( __METHOD__, '6.5.0', 'A network lockout should be specified in the $context parameter.' );
		}

		if ( is_array( $context ) ) {
			$context = wp_parse_args( $context, array( 'user_lock' => false, 'network_lock' => false, 'type' => '' ) );
			$user    = $context['user_lock'];
			$network = $context['network_lock'];
		} else {
			$user    = $context;
			$network = $deprecated;
		}

		if ( ITSEC_Lib::is_ip_whitelisted( ITSEC_Lib::get_ip() ) ) {
			return;
		}

		if ( $network === true ) { //lockout triggered by iThemes Network

			$message = ITSEC_Modules::get_setting( 'global', 'community_lockout_message' );

			if ( ! $message ) {
				$message = __( 'Your IP address has been flagged as a threat by the iThemes Security network.', 'better-wp-security' );
			}

		} elseif ( $user === true ) { //lockout the user

			$message = ITSEC_Modules::get_setting( 'global', 'user_lockout_message' );

			if ( ! $message ) {
				$message =  __( 'You have been locked out due to too many invalid login attempts.', 'better-wp-security' );
			}

		} else { //just lockout the host

			$message = ITSEC_Modules::get_setting( 'global', 'lockout_message' );

			if ( ! $message ) {
				$message = __( 'Error.', 'better-wp-security' );
			}
		}

		$formatted = false;

		if ( $context['type'] ) {
			/**
			 * Filter the lockout message displayed to the user.
			 *
			 * @param string $message
			 * @param string $type
			 * @param array  $context
			 */
			$message = apply_filters( "itsec_{$context['type']}_lockout_message", $message, $context );

			/**
			 * Filter whether to print the lockout error message with formatting or not.
			 *
			 * @param bool   $formatted
			 * @param string $type
			 * @param array  $context
			 */
			$formatted = apply_filters( "itsec_{$context['type']}_lockout_format_message", false, $context );
		}

		$current_user = wp_get_current_user();

		if ( is_object( $current_user ) && isset( $current_user->ID ) ) {
			wp_logout();
		}

		if ( $formatted ) {
			wp_die( $message, '', array( 'response' => 403 ) );
		} else {
			@header( 'HTTP/1.0 403 Forbidden' );
			@header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
			@header( 'Expires: Thu, 22 Jun 1978 00:28:00 GMT' );
			@header( 'Pragma: no-cache' );

			die( $message );
		}
	}

	/**
	 * Provides a description of lockout configuration for use in module settings.
	 *
	 * @since 4.0
	 *
	 * @return string the description of settings.
	 */
	public function get_lockout_description() {
		$global_settings_url = add_query_arg( array( 'module' => 'global' ), ITSEC_Core::get_settings_page_url() ) . '#itsec-global-blacklist';
		// If the user is currently viewing "all" then let them keep viewing all
		if ( ! empty( $_GET['module_type'] ) && 'all' === $_GET['module_type'] ) {
			$global_settings_url = add_query_arg( array( 'module_type', 'all' ), $global_settings_url );
		}

		$description  = '<h4>' . __( 'About Lockouts', 'better-wp-security' ) . '</h4>';
		$description .= '<p>';
		$description .= sprintf( __( 'Your lockout settings can be configured in <a href="%s" data-module-link="global">Global Settings</a>.', 'better-wp-security' ), esc_url( $global_settings_url ) );
		$description .= '<br />';
		$description .= __( 'Your current settings are configured as follows:', 'better-wp-security' );
		$description .= '<ul><li>';
		$description .= sprintf( __( '<strong>Permanently ban:</strong> %s', 'better-wp-security' ), ITSEC_Modules::get_setting( 'global', 'blacklist' ) === true ? __( 'yes', 'better-wp-security' ) : __( 'no', 'better-wp-security' ) );
		$description .= '</li><li>';
		$description .= sprintf( __( '<strong>Number of lockouts before permanent ban:</strong> %s', 'better-wp-security' ), ITSEC_Modules::get_setting( 'global', 'blacklist_count' ) );
		$description .= '</li><li>';
		$description .= sprintf( __( '<strong>How long lockouts will be remembered for ban:</strong> %s', 'better-wp-security' ), ITSEC_Modules::get_setting( 'global', 'blacklist_period' ) );
		$description .= '</li><li>';
		$description .= sprintf( __( '<strong>Host lockout message:</strong> %s', 'better-wp-security' ), ITSEC_Modules::get_setting( 'global', 'lockout_message' ) );
		$description .= '</li><li>';
		$description .= sprintf( __( '<strong>User lockout message:</strong> %s', 'better-wp-security' ), ITSEC_Modules::get_setting( 'global', 'user_lockout_message' ) );
		$description .= '</li><li>';
		$description .= sprintf( __( '<strong>Is this computer white-listed:</strong> %s', 'better-wp-security' ), ITSEC_Lib::is_ip_whitelisted( ITSEC_Lib::get_ip() ) === true ? __( 'yes', 'better-wp-security' ) : __( 'no', 'better-wp-security' ) );
		$description .= '</li></ul>';

		return $description;

	}

	/**
	 * Shows all lockouts currently in the database.
	 *
	 * @since 4.0
	 *
	 * @param string $type    'all', 'host', or 'user'
	 * @param array  $args    Additional arguments.
	 *
	 * @return array all lockouts in the system
	 */
	public function get_lockouts( $type = 'all', $args = array() ) {

		global $wpdb;

		if ( is_bool( $args ) ) {
			$args = array( 'current' => $args );
		}

		if ( func_num_args() === 3 ) {
			$third = func_get_arg( 2 );

			if ( $third && is_numeric( $third ) ) {
				$args['limit'] = $third;
			}
		}

		$args = wp_parse_args( $args, array(
			'current' => true,
		) );

		$where = $limit  = '';
		$wheres = array();

		switch ( $type ) {

			case 'host':
				$wheres[] = "`lockout_host` IS NOT NULL AND `lockout_host` != ''";
				break;
			case 'user':
				$wheres[] = '`lockout_user` != 0';
				break;
			case 'username':
				$wheres[] = "`lockout_username` IS NOT NULL AND `lockout_username` != ''";
				break;
		}

		if ( $args['current'] ) {
			$wheres[] = "`lockout_active` = 1 AND `lockout_expire_gmt` > '" . date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ) . "'";
		}

		if ( isset( $args['after'] ) ) {
			$after = is_int( $args['after'] ) ? $args['after'] : strtotime( $args['after'] );
			$after = date( 'Y-m-d H:i:s', $after );

			$wheres[] = "`lockout_start_gmt` > '{$after}'";
		}

		if ( $wheres ) {
			$where = ' WHERE ' . implode( ' AND ', $wheres );
		}

		if ( ! empty( $args['limit'] ) ) {
			$limit = ' LIMIT ' . absint( $args['limit'] );
		}

		if ( isset( $args['return'] ) && 'count' === $args['return'] ) {
			$select   = 'SELECT COUNT(1) as COUNT';
			$is_count = true;
		} else {
			$select   = 'SELECT *';
			$is_count = false;
		}

		$results = $wpdb->get_results( "{$select} FROM `" . $wpdb->base_prefix . "itsec_lockouts`" . $where . $limit . ';', ARRAY_A );

		if ( $is_count && $results ) {
			return $results[0]['COUNT'];
		}

		return $results;
	}

	/**
	 * Retrieve a list of the temporary whitelisted IP addresses.
	 *
	 * @return array A map of IP addresses to their expiration time.
	 */
	public function get_temp_whitelist() {
		$whitelist = get_site_option( 'itsec_temp_whitelist_ip', false );

		if ( ! is_array( $whitelist ) ) {
			$whitelist = array();
		} else if ( isset( $whitelist['ip'] ) ) {
			// Update old format
			$whitelist = array(
				$whitelist['ip'] => $whitelist['exp'] - ITSEC_Core::get_time_offset(),
			);
		} else {
			return $whitelist;
		}

		update_site_option( 'itsec_temp_whitelist_ip', $whitelist );

		return $whitelist;
	}

	/**
	 * If the current user has permission to manage ITSEC, add them to the temporary whitelist.
	 */
	public function update_temp_whitelist() {
		if ( ! ITSEC_Core::current_user_can_manage() ) {
			// Only add IP's of users that can manage Security settings.
			return;
		}

		$ip = ITSEC_Lib::get_ip();
		$this->add_to_temp_whitelist( $ip );
	}

	/**
	 * Add an IP address to the temporary whitelist for 24 hours.
	 *
	 * This method will also remove any expired IPs from storage.
	 *
	 * @param string $ip
	 */
	public function add_to_temp_whitelist( $ip ) {
		$whitelist = $this->get_temp_whitelist();
		$expiration = ITSEC_Core::get_current_time_gmt() + DAY_IN_SECONDS;
		$refresh_expiration = $expiration - HOUR_IN_SECONDS;

		if ( isset( $whitelist[$ip] ) && $whitelist[$ip] > $refresh_expiration ) {
			// An update is not needed yet.
			return;
		}

		// Remove expired entries.
		foreach ( $whitelist as $cached_ip => $cached_expiration ) {
			if ( $cached_expiration < ITSEC_Core::get_current_time_gmt() ) {
				unset( $whitelist[$cached_ip] );
			}
		}

		$whitelist[$ip] = $expiration;

		update_site_option( 'itsec_temp_whitelist_ip', $whitelist );
	}

	/**
	 * Remove a given IP address from the temporary whitelist.
	 *
	 * @param string $ip
	 */
	public function remove_from_temp_whitelist( $ip ) {
		$whitelist = $this->get_temp_whitelist();

		if ( ! isset( $whitelist[$ip] ) ) {
			return;
		}

		unset( $whitelist[$ip] );

		update_site_option( 'itsec_temp_whitelist_ip', $whitelist );
	}

	/**
	 * Completely clear the temporary whitelist of all IP addresses.
	 */
	public function clear_temp_whitelist() {
		update_site_option( 'itsec_temp_whitelist_ip', array() );
	}

	/**
	 * Check if the current user is temporarily whitelisted.
	 *
	 * @return bool
	 */
	public function is_visitor_temp_whitelisted() {
		global $itsec_globals;

		$whitelist = $this->get_temp_whitelist();
		$ip = ITSEC_Lib::get_ip();

		if ( isset( $whitelist[$ip] ) && $whitelist[$ip] > $itsec_globals['current_time'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Store a record of the locked out user/host or permanently ban the host.
	 *
	 * Permanently banned hosts will be forwarded to the ban-users module via the itsec-new-blacklisted-ip hook and
	 * not persisted to the database.
	 *
	 * If configured, notifies the configured email addresses of the lockout.
	 *
	 * @since 4.0
	 *
	 * @param  string $type     The type of lockout (for user reference)
	 * @param  string $reason   Reason for lockout, for notifications
	 * @param  string $host     Host to lock out
	 * @param  int    $user     user id to lockout
	 * @param string  $username username to lockout
	 *
	 * @return void
	 */
	private function lockout( $type, $reason, $host = null, $user = null, $username = null ) {

		global $wpdb, $itsec_logger, $itsec_globals;

		$host_expiration = null;
		$user_expiration = null;
		$username        = sanitize_text_field( trim( $username ) );
		$lock            = 'lockout_' . $host . $user . $username;

		// Acquire a lock to prevent a lockout being created more than once by a particularly fast attacker.
		if ( ITSEC_Lib::get_lock( $lock, 180 ) ) {

			//Do we have a good host to lock out or not
			if ( ! is_null( $host ) && ITSEC_Lib::is_ip_whitelisted( sanitize_text_field( $host ) ) === false && ITSEC_Lib_IP_Tools::validate( $host ) ) {
				$good_host = sanitize_text_field( $host );
			} else {
				$good_host = false;
			}

			//Do we have a valid user to lockout or not
			if ( $user !== null && ITSEC_Lib::user_id_exists( intval( $user ) ) === true ) {
				$good_user = intval( $user );
			} else {
				$good_user = false;
			}

			//Do we have a valid username to lockout or not
			if ( $username !== null && $username != '' ) {
				$good_username = $username;
			} else {
				$good_username = false;
			}

			$blacklist_host = false; //assume we're not permanently blcking the host

			//Sanitize the data for later
			$type   = sanitize_text_field( $type );
			$reason = sanitize_text_field( $reason );

			//handle a permanent host ban (if needed)
			if ( ITSEC_Modules::get_setting( 'global', 'blacklist' ) && $good_host !== false ) { //permanent blacklist

				$blacklist_period = ITSEC_Modules::get_setting( 'global', 'blacklist_period', 7 );
				$blacklist_seconds = $blacklist_period * DAY_IN_SECONDS;

				$host_count = 1 + $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_expire_gmt` > %s AND `lockout_host`= %s;",
						date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - $blacklist_seconds ),
						$host
					)
				);

				if ( $host_count >= ITSEC_Modules::get_setting( 'global', 'blacklist_count' ) && ITSEC_Files::can_write_to_files() ) {

					$host_expiration = false;

					$this->blacklist_ip( sanitize_text_field( $host ) );

					$blacklist_host = true; //flag it so we don't do a temp ban as well

				}

			}

			//We have temp bans to perform
			if ( $good_host !== false || $good_user !== false || $good_username || $good_username !== false ) {

				if ( ITSEC_Lib::is_ip_whitelisted( sanitize_text_field( $host ) ) ) {

					$whitelisted    = true;
					$expiration     = date( 'Y-m-d H:i:s', 1 );
					$expiration_gmt = date( 'Y-m-d H:i:s', 1 );

				} else {

					$whitelisted    = false;
					$exp_seconds    = ITSEC_Modules::get_setting( 'global', 'lockout_period' ) * MINUTE_IN_SECONDS;
					$expiration     = date( 'Y-m-d H:i:s', $itsec_globals['current_time'] + $exp_seconds );
					$expiration_gmt = date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] + $exp_seconds );

				}

				if ( $good_host !== false && $blacklist_host === false ) { //temp lockout host

					$host_expiration = $expiration;

					$wpdb->insert(
						$wpdb->base_prefix . 'itsec_lockouts',
						array(
							'lockout_type'       => $type,
							'lockout_start'      => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
							'lockout_start_gmt'  => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
							'lockout_expire'     => $expiration,
							'lockout_expire_gmt' => $expiration_gmt,
							'lockout_host'       => sanitize_text_field( $host ),
						)
					);

					$itsec_logger->log_event( 'lockout', 10, array(
						'expires' => $expiration, 'expires_gmt' => $expiration_gmt, 'type' => $type
					), sanitize_text_field( $host ) );

				}

				if ( $good_user !== false ) { //blacklist host and temp lockout user

					$user_expiration = $expiration;

					$wpdb->insert(
						$wpdb->base_prefix . 'itsec_lockouts',
						array(
							'lockout_type'       => $type,
							'lockout_start'      => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
							'lockout_start_gmt'  => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
							'lockout_expire'     => $expiration,
							'lockout_expire_gmt' => $expiration_gmt,
							'lockout_host'       => '',
							'lockout_user'       => intval( $user ),
						)
					);

					if ( $whitelisted === false ) {
						$itsec_logger->log_event( 'lockout', 10, array(
							'expires' => $expiration, 'expires_gmt' => $expiration_gmt, 'type' => $type
						), '', '', intval( $user ) );
					} else {
						$itsec_logger->log_event( 'lockout', 10, array(
							__( 'White Listed', 'better-wp-security' ), 'type' => $type
						), '', '', intval( $user ) );
					}

				}

				if ( $good_username !== false ) { //blacklist host and temp lockout username

					$user_expiration = $expiration;

					$wpdb->insert(
						$wpdb->base_prefix . 'itsec_lockouts',
						array(
							'lockout_type'       => $type,
							'lockout_start'      => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
							'lockout_start_gmt'  => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
							'lockout_expire'     => $expiration,
							'lockout_expire_gmt' => $expiration_gmt,
							'lockout_host'       => '',
							'lockout_username'   => $username,
						)
					);

					if ( $whitelisted === false ) {
						$itsec_logger->log_event( 'lockout', 10, array(
							'expires' => $expiration, 'expires_gmt' => $expiration_gmt, 'type' => $type
						), '', '', $username );
					} else {
						$itsec_logger->log_event( 'lockout', 10, array(
							__( 'White Listed', 'better-wp-security' ), 'type' => $type
						), '', '', $username );
					}

				}

				if ( $whitelisted === false ) {

					$this->send_lockout_email( $good_host, $good_user, $good_username, $host_expiration, $user_expiration, $reason );

					$lock_context = array(
						'type' => $type,
					);

					if ( $user ) {
						$lock_context['user'] = get_userdata( $user );
					} elseif ( $username ) {
						$lock_context['username'] = $username;
					}

					if ( $good_host !== false ) {

						ITSEC_Lib::release_lock( $lock );
						$this->execute_lock( $lock_context );

					} else {

						$lock_context['user_lock'] = true;

						ITSEC_Lib::release_lock( $lock );
						$this->execute_lock( $lock_context );

					}

				}

			}

			ITSEC_Lib::release_lock( $lock );

		}

	}

	/**
	 * Inserts an IP address into the htaccess ban list.
	 *
	 * @since 4.0
	 *
	 * @param $ip
	 *
	 * @return boolean False if the IP is whitelisted, true otherwise.
	 */
	public function blacklist_ip( $ip ) {
		$ip = sanitize_text_field( $ip );

		if ( ITSEC_Lib::is_ip_blacklisted( $ip ) ) {
			// Already blacklisted.
			return true;
		}

		if ( ITSEC_Lib::is_ip_whitelisted( $ip ) ) {
			// Cannot blacklist a whitelisted IP.
			return false;
		}

		// The following action allows modules to handle the blacklist as needed. This is primarily useful for the Ban
		// Users module.
		do_action( 'itsec-new-blacklisted-ip', $ip );

		return true;
	}

	/**
	 * Purges lockouts more than 7 days old from the database
	 *
	 * @return void
	 */
	public function purge_lockouts() {

		global $wpdb;

		$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_expire_gmt` < '" . date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( ( ITSEC_Modules::get_setting( 'global', 'blacklist_period' ) + 1 ) * DAY_IN_SECONDS ) ) . "';" );
		$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` < '" . date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - DAY_IN_SECONDS ) . "';" );

	}

	/**
	 * Register 404 and file change detection for logger
	 *
	 * @param array $logger_modules array of logger modules
	 *
	 * @return array
	 */
	public function register_logger( $logger_modules ) {

		$logger_modules['lockout'] = array(
			'type'     => 'lockout',
			'function' => __( 'Host or User Lockout', 'better-wp-security' ),
		);

		return $logger_modules;

	}

	/**
	 * Register verbs for Sync.
	 *
	 * @since 3.6.0
	 *
	 * @param Ithemes_Sync_API $api API object.
	 */
	public function register_sync_verbs( $api ) {
		$api->register( 'itsec-get-lockouts', 'Ithemes_Sync_Verb_ITSEC_Get_Lockouts', dirname( __FILE__ ) . '/sync-verbs/itsec-get-lockouts.php' );
		$api->register( 'itsec-release-lockout', 'Ithemes_Sync_Verb_ITSEC_Release_Lockout', dirname( __FILE__ ) . '/sync-verbs/itsec-release-lockout.php' );
		$api->register( 'itsec-get-temp-whitelist', 'Ithemes_Sync_Verb_ITSEC_Get_Temp_Whitelist', dirname( __FILE__ ) . '/sync-verbs/itsec-get-temp-whitelist.php' );
		$api->register( 'itsec-set-temp-whitelist', 'Ithemes_Sync_Verb_ITSEC_Set_Temp_Whitelist', dirname( __FILE__ ) . '/sync-verbs/itsec-set-temp-whitelist.php' );
	}

	/**
	 * Filter to add verbs to the response for the itsec-get-everything verb.
	 *
	 * @since 3.6.0
	 *
	 * @param  array $verbs of verbs.
	 *
	 * @return array Array of verbs.
	 */
	public function register_sync_get_everything_verbs( $verbs ) {
		$verbs['lockout'][] = 'itsec-get-lockouts';
		$verbs['lockout'][] = 'itsec-get-temp-whitelist';

		return $verbs;
	}

	/**
	 * Register modules that will use the lockout service.
	 *
	 * @return void
	 */
	public function register_modules() {

		/**
		 * Filter the available lockout modules.
		 *
		 * @param array $lockout_modules Each lockout module should be an array containing 'type', 'reason' and
		 *                               'period' options. The type is a unique string referring to the type of lockout.
		 *                               'reason' is a human readable label describing the reason for the lockout.
		 *                               'period' is the number of days to check for lockouts to decide if the host
		 *                               should be permanently banned. Additionally, the 'user' and 'host' options instruct
		 *                               security to wait for that many temporary lockout events to occur before executing
		 *                               the lockout.
		 */
		$this->lockout_modules = apply_filters( 'itsec_lockout_modules', $this->lockout_modules );
	}

	/**
	 * Process clearing lockouts on view log page
	 *
	 * @since 4.0
	 *
	 * @param int $id
	 *
	 * @return bool true on success or false
	 */
	public function release_lockout( $id = null ) {

		global $wpdb;

		if ( $id !== null && trim( $id ) !== '' ) {

			$lockout = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE lockout_id = %d;", $id ), ARRAY_A );

			if ( is_array( $lockout ) && sizeof( $lockout ) >= 1 ) {

				$success = $wpdb->update(
					$wpdb->base_prefix . 'itsec_lockouts',
					array(
						'lockout_active' => 0,
					),
					array(
						'lockout_id' => (int) $id,
					)
				);

				return $success === false ? false : true;

			} else {

				return false;

			}

		} elseif ( isset( $_POST['itsec_release_lockout'] ) && $_POST['itsec_release_lockout'] == 'true' ) {

			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'itsec_release_lockout' ) ) {
				die( __( 'Security error!', 'better-wp-security' ) );
			}

			$type    = 'updated';
			$message = __( 'The selected lockouts have been cleared.', 'better-wp-security' );

			foreach ( $_POST as $key => $value ) {

				if ( strstr( $key, "lo_" ) ) { //see if it's a lockout to avoid processing extra post fields

					$wpdb->update(
						$wpdb->base_prefix . 'itsec_lockouts',
						array(
							'lockout_active' => 0,
						),
						array(
							'lockout_id' => (int) $value,
						)
					);

				}

			}

			ITSEC_Lib::clear_caches();

			if ( is_multisite() ) {

				$error_handler = new WP_Error();

				$error_handler->add( $type, $message );

				$this->core->show_network_admin_notice( $error_handler );

			} else {

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

		}

	}

	/**
	 * Register the lockout notification.
	 *
	 * @param array $notifications
	 *
	 * @return array
	 */
	public function register_notification( $notifications ) {
		$notifications['lockout'] = array(
			'subject_editable' => true,
			'recipient'        => ITSEC_Notification_Center::R_USER_LIST_ADMIN_UPGRADE,
			'schedule'         => ITSEC_Notification_Center::S_NONE,
			'optional'         => true,
		);

		return $notifications;
	}

	/**
	 * Get the strings for the lockout notification.
	 *
	 * @return array
	 */
	public function notification_strings() {
		return array(
			'label'       => esc_html__( 'Site Lockouts', 'better-wp-security' ),
			'description' => esc_html__( 'Various modules send emails to notify you when a user or host is locked out of your website.', 'better-wp-security' ),
			'subject'     => esc_html__( 'Site Lockout Notification', 'better-wp-security' ),
		);
	}

	/**
	 * Sends an email to notify site admins of lockouts
	 *
	 * @since 4.0
	 *
	 * @param  string $host            the host to lockout
	 * @param  int    $user_id         the user id to lockout
	 * @param  string $username        the username to lockout
	 * @param  string $host_expiration when the host login expires
	 * @param  string $user_expiration when the user lockout expires
	 * @param  string $reason          the reason for the lockout to show to the user
	 *
	 * @return void
	 */
	private function send_lockout_email( $host, $user_id, $username, $host_expiration, $user_expiration, $reason ) {

		$nc = ITSEC_Core::get_notification_center();

		if ( ! $nc->is_notification_enabled( 'lockout' ) ) {
			return;
		}

		$lockouts = array();
		$show_remove_ip_ban_message = false;
		$show_remove_lockout_message = false;

		if ( false !== $user_id ) {
			$user = get_userdata( $user_id );
			$username = $user->user_login;
		}

		if ( false !== $username ) {
			$show_remove_lockout_message = true;

			$lockouts[] = array(
				'type'   => 'user',
				'id'     => $username,
				'until'  => $user_expiration,
				'reason' => $reason,
			);
		}

		if ( false !== $host ) {
			if ( false === $host_expiration ) {
				$host_expiration = __( 'Permanently', 'better-wp-security' );
				$show_remove_ip_ban_message = true;
			} else {
				$show_remove_lockout_message = true;
			}

			$lockouts[] = array(
				'type'   => 'host',
				'id'     => '<a href="' . esc_url( ITSEC_Lib::get_trace_ip_link( $host ) ) . '">' . $host . '</a>',
				'until'  => $host_expiration,
				'reason' => $reason,
			);
		}


		$mail = $nc->mail();

		$mail->add_header( esc_html__( 'Site Lockout Notification', 'better-wp-security' ), esc_html__( 'Site Lockout Notification', 'better-wp-security' ) );
		$mail->add_lockouts_table( $lockouts );

		if ( $show_remove_lockout_message ) {
			$mail->add_text( __( 'Release lockouts from the Active Lockouts section of the settings page.', 'better-wp-security' ) );
			$mail->add_button( __( 'Visit Settings Page', 'better-wp-security' ), ITSEC_Mail::filter_admin_page_url( ITSEC_Core::get_settings_page_url() ) );
		}

		if ( $show_remove_ip_ban_message ) {
			$mail->add_text( __( 'Release the permanent host ban from Ban Hosts list in the Banned Users section of the settings page.', 'better-wp-security' ) );
			$mail->add_button( __( 'Visit Banned Users Settings', 'better-wp-security' ), ITSEC_Mail::filter_admin_page_url( ITSEC_Core::get_settings_module_url( 'ban-users' ) ) );
		}

		$mail->add_footer();


		$subject = $mail->prepend_site_url_to_subject( $nc->get_subject( 'lockout' ) );
		$subject = apply_filters( 'itsec_lockout_email_subject', $subject );
		$mail->set_subject( $subject, false );

		$nc->send( 'lockout', $mail );
	}

	/**
	 * Sets an error message when a user has been forcibly logged out due to lockout
	 *
	 * @return string
	 */
	public function set_lockout_error() {

		global $itsec_globals;

		//check to see if it's the logout screen
		if ( isset( $_GET['itsec'] ) && $_GET['itsec'] == true ) {
			return '<div id="login_error">' . ITSEC_Modules::get_setting( 'global', 'user_lockout_message' ) . '</div>' . PHP_EOL;
		}

	}

}
