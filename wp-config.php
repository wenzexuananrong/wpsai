<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
#define( 'DB_NAME', 'fredar' );

/** Database username */
#define( 'DB_USER', 'fredar' );

/** Database password */
#define( 'DB_PASSWORD', 'MPznCWBWmavifdPiX05D' );

/** Database hostname */
#define( 'DB_HOST', '127.0.0.1:3306' );

define( 'ORCA_EMAIL_DEBUG', true);

define( 'DB_NAME', 'fredar' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', "localhost" );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define( 'QM_ENABLE_API_CALL_MONITOR', true );
define( 'SAVEQUERIES', true);

define( 'EP_HOST', 'http://localhost:9200' );
define( 'EP_POST_INDEX', 'wordpscom-post-1' );
define( 'SITE_CND_DOMAIN', "https://cdn.fredar.com" );

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
define( 'AUTH_KEY',          '%Vyn3<^RJ+]|+OpO43o~B*3TRbsgGO6k,c:)oAow^5#}!~/TkEvy7^&o4X%Enq6M' );
define( 'SECURE_AUTH_KEY',   '3y9~-Nb<1t#!#Y4LlKD;!J~)s{.{8~F4>QB6?[}k5ovz}oWVr0[7FIbu>#tcIOzV' );
define( 'LOGGED_IN_KEY',     'c@!^T;;n7<5;[-:pf696=m|amUAccqH_v[dI@q#p</`s/wG4M8iccO<AE,Q5qaW#' );
define( 'NONCE_KEY',         'I4!W(aS1z^@GoszvMB 6cp?0x7|q(u={Nc6`CjoXPwG(]AxnS^E9g#f%Xj]GM2/Y' );
define( 'AUTH_SALT',         'Sl=@RU_|c(ziJ$.A9Afsa2;Lz<mdlkCx?L}PcgZ!rk~M8nTe/Km3TKsN7wF3Z{[o' );
define( 'SECURE_AUTH_SALT',  'htf}TCtGbX[u] {U*RXd GiQT@6)+HTlc0Zz3f.=8c#B* L9}b`GUeQ4D}]d@z&,' );
define( 'LOGGED_IN_SALT',    '#nuN$ol5)7WB[e)]Tmem}+P+dk/)!;Drk-[bB)t5!D@qfa|*2j2umFcjCEtD`_~/' );
define( 'NONCE_SALT',        '?;C!2a,s/8l2&d(,?gCf?`7jJ0>a2`$sZTHg}FZ@^:)6kk/}D[=sc>v@Ix6sPt=:' );
define( 'WP_CACHE_KEY_SALT', 'zl n9e7NHZGy_lRRypr,u}:[oGgH77yj1Fr=}J~m(.`C;2w?Dra(%>t&yzotDN^N' );

define( 'DISABLE_WP_CRON', true);
define( 'WP_REDIS_HOST', '127.0.0.1' );
define( 'WP_REDIS_PORT', '6379' );
define( 'WP_REDIS_DATABASE', '0' );
#define( 'WP_REDIS_PASSWORD', 'orca_bellamilton_*007' );
define( 'WP_REDIS_MAXTTL', '900');
define( 'WP_REDIS_SELECTIVE_FLUSH', true);


if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
  $_SERVER['HTTPS']='on';
}
if ( defined( 'WP_CLI' ) && WP_CLI && ! isset( $_SERVER['HTTP_HOST'] ) ) {
    $_SERVER['HTTP_HOST'] = 'fredar.com';
}

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );


/* Add any custom values between this line and the "stop editing" line. */



define( 'FS_METHOD', 'direct' );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );
define( 'CONCATENATE_SCRIPTS', false );
define( 'AUTOSAVE_INTERVAL', 600 );
define( 'WP_POST_REVISIONS', 5 );
define( 'EMPTY_TRASH_DAYS', 21 );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
