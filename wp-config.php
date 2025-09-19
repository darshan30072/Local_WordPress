<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_site');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', '');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';(/6u|Nw9!6x>l.Gswlq*spnM5g9aei[>R!Rabx`Eub:t=R0V#M^K}YNtF([oBZ9');
define('SECURE_AUTH_KEY',  'K>sgKoczEV))PFbqTMoys]q9<~&Zh|kn<{6v)q=:ce%|%6/ID8$4.@qY[gJf2eJh');
define('LOGGED_IN_KEY',    'y=UhZzs0:|oir_Hef1eP&aiB,Y3:jj{XKR,l*n1>GeitLFCgfbR{@T)z$KR7]b)p');
define('NONCE_KEY',        'N0lL1DjBOf<IWyJwD<u3[g.IKFY.P^v/X,EovH,=:; 6Z&H,0(I|GVJ2H=/o4?S]');
define('AUTH_SALT',        '1PgW1JX-xB>G,#F@)L^uk|03pE2 &SjbNQ)b&5jh/RIyd9T#(k,Jq}s*8=`[d~1]');
define('SECURE_AUTH_SALT', 'tj=OoF#-e(cswo.Y6]T@#x :@3=,W@WE>J*^$ueuHIrQ88*Zh(*jOU Y(Q_b@9|A');
define('LOGGED_IN_SALT',   'b%X.zl$4*!_F7fL8,k,u;^MVS_(tq(@#db[i/jx/2+mg{#k;aI~:Mt5euVQ:yF~;');
define('NONCE_SALT',       'Yn 4~g#I*nW{qv:o[a`gw$v,V5W.E:g=GK^DU%1cb^utz|1TDyQMT-d%3Ysh<JR0');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'w_site_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */

/* Add any custom values between this line and the "stop editing" line. */

// Enable WP Debug mode
define('WP_DEBUG', true);

// Disable display of errors and warnings on the site
define('WP_DEBUG_DISPLAY', false);

// Enable logging of errors to /wp-content/debug.log
define('WP_DEBUG_LOG', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
