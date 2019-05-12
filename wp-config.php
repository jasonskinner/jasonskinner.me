<?php

// ===================================================
// Load database info and local development parameters
// ===================================================
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	define( 'WP_LOCAL_DEV', true );
	include( dirname( __FILE__ ) . '/local-config.php' );
} else {
	define( 'WP_LOCAL_DEV', false );
	define( 'DB_NAME', 'jasonskinner_me' );
	define( 'DB_USER', 'jasonskinner' );
	define( 'DB_PASSWORD', 'Jss587469!' );
	define( 'DB_HOST', 'mysql.jasonskinner.me' ); // Probably 'localhost'
}

// ========================
// Custom Content Directory
// ========================
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/content' );
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content' );

// ================================================
// You almost certainly do not want to change these
// ================================================
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// ==============================================================
// Salts, for security
// Grab these from: https://api.wordpress.org/secret-key/1.1/salt
// ==============================================================
define('AUTH_KEY',         'J}^~T#%|iJ[~D*Y isYylUo]2-G2~HyA$pBwhUml!iS._D|#L>]u6e+7u6`v&_oh');
define('SECURE_AUTH_KEY',  '2Qoy<j2BQ|HR]Q1ZLueWg<5@G1:<@.o2CGV@M*{,RAk-e-<$Bu;3cl_i+mhC<3eT');
define('LOGGED_IN_KEY',    'sB/)n|:0B$Jlmc^FSU5tg,(n,JP4iJaKm}W_6-sH{N9gw%sjwnr-)AMXM|&l`|nN');
define('NONCE_KEY',        'wnX[DW}/w=+PZ2TStK>Q#O=&d#W.-WmSwH4Ot$da;%9b1UcUdh(d%gj>TeZ[wIDs');
define('AUTH_SALT',        '.9b+oPM6&S?M^C=/<p&qvk}?p.(@0mHLEcmvU(sGPTG^N^mQPrESv3-RnV] 2)wk');
define('SECURE_AUTH_SALT', 'T%i%]2&m.Dd?,2 TL/)N]1H-Qw-O48%=ye]hw9Z $_`?w:&|=r1v<}RnWy2GYRS}');
define('LOGGED_IN_SALT',   'kweS4*T}t63U7OeQ$|4-sQ@>13]P.lERZ+KrJrL:B80)a5R!_}5SXOaV~q6?H}%@');
define('NONCE_SALT',       '|41MH3aTP@iQ_zt0@fV[/f-^oV7<p:5ROl{[+4+<[,SEF7t4G]rF{dCV$M>F1,M}');

// ==============================================================
// Table prefix
// Change this if you have multiple installs in the same database
// ==============================================================
$table_prefix  = 'jss_';

// ================================
// Language
// Leave blank for American English
// ================================
define( 'WPLANG', '' );

// ===========
// Hide errors
// ===========
ini_set( 'display_errors', 0 );
define( 'WP_DEBUG_DISPLAY', false );

// =================================================================
// Debug mode
// Debugging? Enable these. Can also enable them in local-config.php
// =================================================================
// define( 'SAVEQUERIES', true );
// define( 'WP_DEBUG', true );

// ======================================
// Load a Memcached config if we have one
// ======================================
if ( file_exists( dirname( __FILE__ ) . '/memcached.php' ) )
	$memcached_servers = include( dirname( __FILE__ ) . '/memcached.php' );

// ===========================================================================================
// This can be used to programatically set the stage when deploying (e.g. production, staging)
// ===========================================================================================
define( 'WP_STAGE', '%%WP_STAGE%%' );
define( 'STAGING_DOMAIN', '%%WP_STAGING_DOMAIN%%' ); // Does magic in WP Stack to handle staging domain rewriting

// ===================
// Bootstrap WordPress
// ===================
if ( !defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/wp/' );
require_once( ABSPATH . 'wp-settings.php' );
