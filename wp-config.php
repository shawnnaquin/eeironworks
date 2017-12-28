<?php
/// TEST10 ///
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'eeironworks');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '**62/song/TIME/radio/76**');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
// mysql **62/song/TIME/radio/76**
/**#@+
// AKIAIRTLWUNHQGOS7R3A
// FTY064hwYrT3nMDGWdXClsxVGuXfWfknGHw08y9/

 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ':={?r_qm3o$KE(/K`2%@Jl,yJ X3a=p}S&:gZ[yLZJD9I; m^)}A0ORCz3O$tlC3');
define('SECURE_AUTH_KEY',  '`b7iwSyd(h/;XxGy3I}:ZWBT(NZ>>7nXyk4qq`=ZDCSFmp//WPVM)Ax-|n@<?8i2');
define('LOGGED_IN_KEY',    'QRY#X=owS5t<sHj()J>6%G;yXM@?yCGNoNx;&K`f^8U,hDTVTv=#.dM6R<w~4H7(');
define('NONCE_KEY',        'H7x*Q7M:zMv:%*[>.a!_Cv|km<zXI.WQAPFkW35DkKdl7,E!PFVp*t<5t.0U!YRV');
define('AUTH_SALT',        '>,W)$vo]Kng~fzvMBR/7C>$_hDXX$`Tv0&n] ImoRvi7[Gaq:hfEN^AV-uAuMj=R');
define('SECURE_AUTH_SALT', 'yz&hcn@:J*cSLr%EupJi`MK0_#.KE!a&PD2U|_0UNe:01;,hwTwoB^`zD|{4X<}I');
define('LOGGED_IN_SALT',   '! TwQ`6Ht;/UOn)SeNvE$bGzW(z~HL%y${OJDD8wPYOh,]@.tjZQT(!_MP[9~E;L');
define('NONCE_SALT',       'bsE =-k0tJd.<_O#[JFJKa=yhNRXT_]Ps5Ke%P0fG%:fRH[elmS/7qi><y$|>7?a');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
