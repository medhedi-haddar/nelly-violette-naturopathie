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
define('DB_NAME', 'database_name_here');

/** MySQL database username */
define('DB_USER', 'username_here');

/** MySQL database password */
define('DB_PASSWORD', 'password_here');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'ogxdvFT=!vwvTZ*q&Y~iTFpV6(;c*IXPzyJ6j{6$ E/?V++(]r;+ynoLZq|foU2=');
define('SECURE_AUTH_KEY',  'jVX+}M!K)dRz;y7&D#|F{&.;9t +,4Ev)`Cj:H3VbiFfNen$-.1K$bm(3@EJ94d]');
define('LOGGED_IN_KEY',    'w]3r+Z?_/Bi|2F}+M;#z^guP>wM8]tC(kjAq-:Wx-).xLm8mQLXip5N9`m-P6e%Q');
define('NONCE_KEY',        'lTe{T@Q+p?WYu_]BgD%Lzf& M4GR/wb8`|OY@o}O;?yKcrYr1n@q}U9pL,eH6%f8');
define('AUTH_SALT',        '>y6gbVjO}{Enhm`#~,1;)_reNx |3I(F~x5App+7G%~3-hU^(>Aq]ZogBVNo[VMG');
define('SECURE_AUTH_SALT', 'l-w6+wB4J`8Rbm|(-<zbQ|jgx{Q%qu{?+Zz^Qji}+gZhxf-=l-&RS,k)>{7EQ|Ys');
define('LOGGED_IN_SALT',   '>%J!-!HZ<8LkRqZyDq6=HYm?_;TI!CsAXe,=I]=nJLkt,|T#`B3=qu}l%52+TV3E');
define('NONCE_SALT',       'L,Fkm)3^n8H=8SmMOL2=|iW4M0F);|FIG%$;3YvfD3Eybp(ds!NEd0=cnk)BcaNt');

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
