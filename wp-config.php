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
define('DB_NAME', 'prod_anaconda');

/** MySQL database username */
define('DB_USER', 'prod_anaconda');

/** MySQL database password */
define('DB_PASSWORD', '7RsY4hTCjt4HfT');

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
define('AUTH_KEY',         'bfrW-6O3q<.|C?9hEy7Jg#pHCoGRQ-]6g-Uy7WHWu*cal-:Wu.Es*xx03jetgh%*');
define('SECURE_AUTH_KEY',  '>Nm/S|Snv,3^,cE+{KF:UC&SYAa%Ts{z?VElr2[~eC*mC1pgXy-N+<:cDa<) bvE');
define('LOGGED_IN_KEY',    'Ip#F->5m0skFYHF![<9I=$)u[-}diwMb6}]X{K9{U0<oK=$(4fF0fy@Tru7C5I:k');
define('NONCE_KEY',        'o`<yPQ5Be_`C|p#SX%0U:=#n+^wew).[U3|?E[LR-m<_, <MiB1PE}{W[WLDE?U*');
define('AUTH_SALT',        'v31U9.E|ff}Fn+S$9zgUGu(+?<k!%]gaz1T{CUNyKCtR]$abVm+{4t~b%?/ #k$5');
define('SECURE_AUTH_SALT', 'G~BQ?2~~}co@b^BVd&9+-oroov/)Yt73-1uXFgI0G,~[-cL)/wHecO,=/#A%gWox');
define('LOGGED_IN_SALT',   '{zvKIr(xAE$e&|=7pZT, =RHbdAISwN;p=|1kbVu%.yTB3IU23EpZ-{B&*bLZFz|');
define('NONCE_SALT',       '!+i#(g0C6o$G@ZB=-~lce pA3,lNy0]b6C*e{B*Vy3e3vvaW{R.rm$7Zs}$)`R/@');

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
