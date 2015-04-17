<?php
/* This is a sample config file.
 * Edit this file with your own settings and save it as "config.php"
 * You can leave it in the "includes" directory, or, better, move it to
 * the "user" directory. This way, when a new version of YOURLS is available,
 * simply delete everything but "/user", and upload the new version.
 */


/*
 ** MySQL settings - You can get this info from your web host
 */

/** MySQL database username */
define( 'YOURLS_DB_USER', 'yourls' );

/** MySQL database password */
define( 'YOURLS_DB_PASS', 'yourls' );

/** The name of the database for YOURLS */
define( 'YOURLS_DB_NAME', 'yourls' );

/** MySQL hostname */
define( 'YOURLS_DB_HOST', $_SERVER['SERVER_NAME'] );

/** MySQL tables prefix */
define( 'YOURLS_DB_PREFIX', 'yourls_' );

/*
 ** Site options
 */

/** YOURLS installation URL, no trailing slash */
define( 'YOURLS_SITE', 'https://'.$_SERVER['SERVER_NAME'].'/s' );

/** Timezone GMT offset */
define( 'YOURLS_HOURS_OFFSET', 0 );

/** Allow multiple short URLs for a same long URL
 ** Set to true to have only one pair of shortURL/longURL (default YOURLS behavior)
 ** Set to false to allow multiple short URLs pointing to the same long URL (bit.ly behavior) */
define( 'YOURLS_UNIQUE_URLS', true );

/** Private means protected with login/pass as defined below. Set to false for public usage. */
define( 'YOURLS_PRIVATE', true );

/** A random secret hash used to encrypt cookies. You don't have to remember it, make it long and complicated. Hint: copy from http://yourls.org/cookie **/
define( 'YOURLS_COOKIEKEY', 'I2M%$g|aA3M%PfzYYFB6RqgC&72[nZUA-@f#SJmJ' );

/**  Username(s) and password(s) allowed to access the site */
$yourls_user_passwords = array(
	'admin' => 'admin',
	'usync_username' => 'usync_password'	// You can have one or more 'login'=>'password' lines
	);

/*
 ** URL Shortening settings
 */

/** URL shortening method: 36 or 62 */
define( 'YOURLS_URL_CONVERT', 62 );
/*
 * 36: generates case insentitive lowercase keywords (ie: 13jkm)
 * 62: generate case sensitive keywords (ie: 13jKm or 13JKm)
 * Stick to one setting, don't change after you've created links as it will change all your short URLs!
 * Base 36 should be picked. Use 62 only if you understand what it implies.
 */

/**
* Reserved keywords (so that generated URLs won't match them)
* Define here negative, unwanted or potentially misleading keywords.
*/
$yourls_reserved_URL = array(
	'porn', 'faggot', 'sex', 'nigger', 'fuck', 'cunt', 'dick', 'gay',
);

/*
 ** Personal settings would go after here.
 */

