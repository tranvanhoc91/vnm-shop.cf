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
define('DB_NAME', 'vnm-shop.cf');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

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
define('AUTH_KEY',         'eOFae@IWT<#{o)?3J_nP-094pd4q6K2NSTp#j:f*5rqAGIcY2-hpRKH9EDzbP*1e');
define('SECURE_AUTH_KEY',  'E&bZR?k~96j_>qHld;bA!N=4JIiT(4<0% A2EhF+8JLs6okL3z60RN>TP~ RInJ ');
define('LOGGED_IN_KEY',    '7n@~F0E>`d*>aVeuWw8&-8snrj7?=*&CPC%:x^#XwaXYvqOW$o-2RhRpY)#2_lKc');
define('NONCE_KEY',        '()+#|P@&,(m:|pj(?J4m*N4F=BLu)bESeo&yZSR[cFDZ)i~j3{v&HAiJ|W02dI:e');
define('AUTH_SALT',        '<{0L?E.pWB:Ha$..c!E 1u$F1*zdIVjC67k%=rx8}p6{8ilYYss>TkDDAw7X-EC{');
define('SECURE_AUTH_SALT', '95U4n)8Rptw4zDY]c`7#Ptk0 eI8DQA[z_v+I;#x__Y[=4|[Lt Xjdy1|:uqS.UC');
define('LOGGED_IN_SALT',   'PMx|G,g_cqS]Po#e%{4oSYG{x|xi %IbkE6>M4=,$i}$3=Z.  r#%wuSY8l_lgpI');
define('NONCE_SALT',       'GJwO1 oZg[XwBWczTe+A&05VN+#.J~D,+zg^q5M4l;+1H?[=v0Y;I01aXcM<J21+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'vnm_';

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
