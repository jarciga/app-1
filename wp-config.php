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
define('DB_NAME', '');

/** MySQL database username */
define('DB_USER', '');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         '3DgC1f5V3MwxTbSz0KV9R&<v_d8j~ao+bD*salby3{_9dy},o$O-/(GQ[c),=dFx');
define('SECURE_AUTH_KEY',  'v!JzXM6(hhI0f{}c7{@!zcpB/JFqt>^*mul|LsjZ-*omhS@frYz^QvS4Wp-~l,FN');
define('LOGGED_IN_KEY',    'Y`uxEV~_<2Abr_ddkW|+4KB-7rawJ2k:W|!f|A4QYbms+iQw-=K~-]*Z7b0oQ5 +');
define('NONCE_KEY',        '7T~|_s3@<_0m9ECw7?c ^+(++hZ#_#J@g3Npd6JyZ-x[muRNCRkvK2Y+=~KOb;>+');
define('AUTH_SALT',        '0{B3$|EXY5?X])k+DTyi|aC#9CbiBnpr{_U.:,K;e`.9!Z+;Y1_@|2>X|?$yE;hy');
define('SECURE_AUTH_SALT', 'd_lu.<rMYw`/!@[jpWl/u}{r)t!!%yT+EWC`T.$|% nI-@-!nSA1[}|6?+9Qc7sY');
define('LOGGED_IN_SALT',   '$$sOjr>y~V6N4Mx@.83~+x6];EmZo,QrM;QhfS( &O{!BsATPy%2NFYE IEzVevK');
define('NONCE_SALT',       ']88&Vs||PTI4-4%6&.)~Pzv0+tnS|Sh(m`Q;n,gg{|mAc_X;/G1>7&OAgqE <_4O');

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
