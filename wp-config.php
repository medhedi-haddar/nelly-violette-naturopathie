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
define('DB_NAME', 'nv_naturopathie_db');

/** MySQL database username */
define('DB_USER', 'root');

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
define('AUTH_KEY',         ',GV}Cq0v.n^[{_RdIE6*N3SE{uCF1:uS(I-7!U{/W_D[}i:C$%wR@hguw)1~j!I}');
define('SECURE_AUTH_KEY',  '*uPlGx&sv|&>V=_F TONfdazt_ty>!t |2-$KA41SE=-7>U~qPJqP+S=Y3QS9iVl');
define('LOGGED_IN_KEY',    'L5MIK:RO--;vux`./wq%>G&v~du9ibU^yeP:,)e2zR^z)?$T.]Ne`Od)wBfDRzsu');
define('NONCE_KEY',        'Kbu?:g2 @^BB-,,St<O1$KcSzLest&3PRW5D@<LFg:g~R#@WaW#uGx%)Jzbh@~,G');
define('AUTH_SALT',        'Tos/%,^z[aYn<oH;Pw8GU,*[hy;VpD|ZeU[^c%Tp#(SF&(tLA9Hw2 .rwYKh?k%R');
define('SECURE_AUTH_SALT', 'ukJts!#F>IB<_*|Leg@dBi}4mhsj0=wFY*>!HF3>~|rvhe|zUwJK|8+gCfHlqH ,');
define('LOGGED_IN_SALT',   'c0V86b(pa4&yh>#q4|V6~f4~41jGwGBMR4N}5FD3~#4briS!;*tV0*(nF*Ns{sn9');
define('NONCE_SALT',       'B@gExo%!9+q`R:: R}ZW18z>&sU9.a+H>,0V#_Gx2Si4n/Q7wz2{2otrwEJO!JmB');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'nvn_';

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
