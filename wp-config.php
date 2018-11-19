<?php
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
define('DB_NAME', 'actionplandb');

/** MySQL database username */
define('DB_USER', 'intelpatsar');

/** MySQL database password */
define('DB_PASSWORD', 'x3EijCXSEeAT');

/** MySQL hostname */
define('DB_HOST', 'mysql.s3platform.eu');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '@./CK!qBTN_t+ec}+t0@B;Q2Tm.nwCvZZu[o~EYDS.;x@ic`NS(4#5pXT!X&1qOh');
define('SECURE_AUTH_KEY',  '_#cG[A;-lfrTov*|35TR3.+?[G~Z-.T)V($853@!02zu;c2PVjRi~Fw9DFEo%{3)');
define('LOGGED_IN_KEY',    '!2X^GEp,j*;F?zRo}>9zuv-t%%|6>yx=v0bn(Xf0?w;|*Eb![Y/Rio: GtYPyaL;');
define('NONCE_KEY',        'n4 6p-_wNRUu =lVQ]Z$ZPS/ju`by7x!q[T])jw?#OJHfDc5l,s!f#H4s*Tc8t&.');
define('AUTH_SALT',        '-)rB~~eh+3nx(^VD7@1Cc=>DF>Fs#m*[yknAo+ygD:3+6aWn]#,gcL nI0Q^9ky{');
define('SECURE_AUTH_SALT', 'hIb~p7Ta:-A+[-0>{( EmNd2wC=rtN-!u)I=TfZIUp=4R*0DZ%~g~[ Wm@JhY%X=');
define('LOGGED_IN_SALT',   'D &$bgui]fCK@e9+qQ/iC1bs>+iX:(=)QWCM_yt-V;P2>|1S#R6w_svd6j6d^&Dy');
define('NONCE_SALT',       'Y!U!HI2DM^~b]F*z`a$~0FK2S7-|kpkbhh_PXySQTnxwYavGFF[N~jAQa.eQQrB0');

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

/** Activates Toolset pluggins. */
/** define( 'OTGS_INSTALLER_SITE_KEY_TOOLSET', 'gQIFZKRxZm' ); */
