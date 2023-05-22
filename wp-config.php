<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_plugin_1' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         '/=J!WtTJ=Q_T*cP>AU}u]-hbrQZ59S[p5m:o_xf5wrpfuNB.X*4<)o|cwkRW0W1Z' );
define( 'SECURE_AUTH_KEY',  ';UHzs6dT3LV|7lZayJcIm=8H]d/FtIM}#bm72` k82P/z=R68[/X[&<+>3q}(iGr' );
define( 'LOGGED_IN_KEY',    'w~?:8xKxkFczk1[Ue3pQ`3On+ywH/!g4[yyX|bK_r.PxFH_TK8^D*B{.d$jc&O?:' );
define( 'NONCE_KEY',        'x*(r=,1/z#?3A&QbF2P16Mj,Ny} -!r~H*WMvG<A.=M)r&t3t-p[^{k!LiCFWCi$' );
define( 'AUTH_SALT',        '3j>rx+Ye_;pE!^5mTBx:v/<w6DUl6R3uBN?]A#_$e&xpJ .>0:_hN;VR7|g6;$%k' );
define( 'SECURE_AUTH_SALT', '1e%f`82a6;Y[b!g1o1x/y|uM$8Q780w=8/9<t9d$TMf];sNO|/125]i!aJ80UF8-' );
define( 'LOGGED_IN_SALT',   'pZ_cV^4@4Ojg2hqiAHei{sjUe)`KeO<`;@v$IWHA8?lfH1Z-LI_eI>;NJ$}T:0KL' );
define( 'NONCE_SALT',       '[:1#Y55YKk^K(,t]m{gShqqzu`3T#_uQ)A*_/*ov4rC R nw3?8yi#bYsl^hje`b' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
