<?php
/**
 * This file initializes everything BUT the blog!
 *
 * It is useful when you want to do very customized templates!
 * It is also called by more complete initializers.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 * Parts of this file are copyright (c)2005-2006 by PROGIDISTRI - {@link http://progidistri.com/}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 *
 * PROGIDISTRI S.A.S. grants Francois PLANQUE the right to license
 * PROGIDISTRI S.A.S.'s contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 *
 * Matt FOLLETT grants Francois PLANQUE the right to license
 * Matt FOLLETT's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE
 * @author blueyed: Daniel HAHLER
 * @author mfollett: Matt FOLLETT.
 * @author mbruneau: Marc BRUNEAU / PROGIDISTRI
 *
 * @version $Id$
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );

if( $maintenance_mode )
{
	header('HTTP/1.0 503 Service Unavailable');
	echo '<h1>503 Service Unavailable</h1>';
	die( 'The site is temporarily down for maintenance.' );
}


/**
 * Prevent double loading since require_once won't work in all situations
 * on windows when some subfolders have caps :(
 * (Check it out on static page generation)
 */
if( defined( 'EVO_MAIN_INIT' ) )
{
	return;
}
define( 'EVO_MAIN_INIT', true );


/**
 * Security check for older PHP versions
 * Contributed by counterpoint / MAMBO team
 */
// TODO: dh> this makes sense AFAICS. Please review.
// fp> yeah I don't know about that. If you ask me, i'm not taking chances.
// if( ini_get('register_globals') )
{
	$protects = array( '_REQUEST', '_GET', '_POST', '_COOKIE', '_FILES', '_SERVER', '_ENV', 'GLOBALS', '_SESSION' );
	foreach( $protects as $protect )
	{
		if(  in_array( $protect, array_keys($_REQUEST) )
			|| in_array( $protect, array_keys($_GET) )
			|| in_array( $protect, array_keys($_POST) )
			|| in_array( $protect, array_keys($_COOKIE) )
			|| in_array( $protect, array_keys($_FILES) ) )
		{
			require_once $inc_path.'/_core/_misc.funcs.php';
			bad_request_die( 'Unacceptable params.' );
		}
	}
}

/*
 * fp> We might want to kill all auto registered globals this way:
 * TODO: testing
 *
$superglobals = array($_SERVER, $_ENV, $_FILES, $_COOKIE, $_POST, $_GET);
if (isset( $_SESSION )) array_unshift ( $superglobals , $_SESSION );
if (ini_get('register_globals') && !$this->mosConfig_register_globals)
{
	foreach ( $superglobals as $superglobal )
	{
		foreach ( $superglobal as $key => $value)
		{
			unset( $GLOBALS[$key]);
		}
	}
}
*/


/**
 * Class loader.
 */
require_once $inc_path.'_core/_class'.floor(PHP_VERSION).'.funcs.php';


/**
 * Miscellaneous functions
 */
require_once $inc_path.'/_core/_misc.funcs.php';


/**
 * Debug message log for debugging only (initialized here).
 *
 * @global Log|Log_noop $Debuglog
 */
if( $debug )
{
	load_class( '_core/model/_log.class.php', 'Log' );
	$Debuglog = & new Log( 'note' );
}
else
{
	load_class( '_core/model/_log.class.php', 'Log_noop' );
	$Debuglog = & new Log_noop( 'note' );
}


/**
 * Info & error message log for end user (initialized here)
 * @global Log $Messages
 */
$Messages = & new Log( 'error' );


/*
 * Start timer:
 */
if( $debug )
{
	load_class( '_core/model/_timer.class.php', 'Timer' );
	$Timer = & new Timer('total');

	$Timer->resume( '_main.inc' );
}
else
{
	load_class( '_core/model/_timer.class.php', 'Timer_noop' );
	$Timer = new Timer_noop();
}


/**
 * Sets various arrays and vars, also $app_name!
 *
 * Needed before the error messages.
 */
require_once dirname(__FILE__).'/_vars.inc.php';


if( !$config_is_done )
{ // base config is not done!
	$error_message = 'Base configuration is not done! (see /conf/_basic_config.php)';
}
elseif( !isset( $locales[$default_locale] ) )
{
	$error_message = 'The default locale '.var_export( $default_locale, true ).' does not exist! (see /conf/_locales.php)';
}
if( isset( $error_message ) )
{ // error & exit
	require dirname(__FILE__).'/../skins_adm/conf_error.main.php';
}


/**
 * Load modules.
 *
 * This initializes table name aliases and is required before trying to connect to the DB.
 */
load_class( '_core/model/_module.class.php', 'Module' );
foreach( $modules as $module )
{
	require_once $inc_path.$module.'/_'.$module.'.init.php';
}


/**
 * Connect to DB
 */
require_once dirname(__FILE__).'/_connect_db.inc.php';


/**
 * Load settings class
 */
load_class( 'settings/model/_generalsettings.class.php', 'GeneralSettings' );
load_class( 'users/model/_usersettings.class.php', 'UserSettings' );
/**
 * Interface to general settings
 *
 * Keep this below the creation of the {@link $DB DB object}, because it checks for the
 * correct db_version and catches "table does not exist" errors, providing a link to the
 * install script.
 *
 * @global GeneralSettings $Settings
 */
$Settings = & new GeneralSettings();

/**
 * Interface to user settings
 *
 * @global UserSettings $UserSettings
 */
$UserSettings = & new UserSettings();


/**
 * Absolute Unix timestamp for server
 * @global int $servertimenow
 */
$servertimenow = time();

$time_difference = $Settings->get('time_difference');

/**
 * Corrected Unix timestamp to match server timezone
 * @global int $localtimenow
 */
$localtimenow = $servertimenow + $time_difference;


/**
 * The Hit class
 */
load_class( 'sessions/model/_hit.class.php', 'Hit' );
// fp> The following constructor requires these right now:
load_funcs('_core/_param.funcs.php');
load_funcs('_core/_url.funcs.php');


/**
 * Locale selection:
 * We need to do this as early as possible in order to set DB connection charset below
 * fp> that does not explain why it needs to be here!! Why do we need to set the Db charset HERE? BEFORE WHAT?
 *
 * sam2kb> ideally we should set the right DB charset at the time when we connect to the database. The reason is until we do it all data pulled out from DB is in wrong encoding. I put the code here because it depends on _param.funcs, so if move the _param.funcs higher we can also move this code right under _connect_db
 * See also http://forums.b2evolution.net//viewtopic.php?p=95100
 *
 */
$Debuglog->add( 'default_locale from conf: '.$default_locale, 'locale' );

locale_overwritefromDB();
$Debuglog->add( 'default_locale from DB: '.$default_locale, 'locale' );

$default_locale = locale_from_httpaccept(); // set default locale by autodetect
$Debuglog->add( 'default_locale from HTTP_ACCEPT: '.$default_locale, 'locale' );

if( ($locale_from_get = param( 'locale', 'string', NULL, true )) )
{
	if( $locale_from_get != $default_locale )
	{
		if( isset( $locales[$locale_from_get] ) )
		{
			$default_locale = $locale_from_get;
			$Debuglog->add('Overriding locale from REQUEST: '.$default_locale, 'locale');
		}
		else
		{
			$Debuglog->add('$locale_from_get ('.$locale_from_get.') is not set. Available locales: '.implode(', ', array_keys($locales)), 'locale');
			$locale_from_get = false;
		}
	}
	else
	{
		$Debuglog->add('$locale_from_get == $default_locale ('.$locale_from_get.').', 'locale');
	}

}


/**
 * Activate default locale:
 */
locale_activate( $default_locale );

// Set encoding for MySQL connection:
$DB->set_connection_charset( $current_charset );


/**
 * @global Hit The Hit object
 */
$Hit = & new Hit(); // This may INSERT a basedomain and a useragent but NOT the HIT itself!


/**
 * The Session class.
 */
load_class( 'sessions/model/_session.class.php', 'Session' );
/**
 * The Session object.
 * It has to be instantiated before the "SessionLoaded" hook.
 * @global Session
 * @todo dh> This needs the same "SET NAMES" MySQL-setup as with Session::dbsave() - see the "TODO" with unserialize() in Session::Session()
 * @todo dh> makes no sense in CLI mode (no cookie); Add isset() checks to calls on the $Session object, e.g. below?
 *       fp> We might want to use a special session for CLI. And for cron jobs through http as well.
 */
$Session = & new Session(); // IF this can't pull asesion from the DB it will always INSERT a new one!

/**
 * Handle saving the HIT and updating the SESSION at the end of the page
 */
register_shutdown_function( 'shutdown' );


/**
 * @global AbstractSettings
 */
$global_Cache = & new AbstractSettings( 'T_global__cache', array( 'cach_name' ), 'cach_cache', 0 /* load all */ );


/**
 * Plugins init.
 * This is done quite early here to give an early hook ("SessionLoaded") to plugins (though it might also be moved just after $DB init when there is reason for a hook there).
 * The {@link dnsbl_antispam_plugin} is an example that uses this to check the user's IP against a list of DNS blacklists.
 */
load_class( 'plugins/model/_plugins.class.php', 'Plugins' );
/**
 * @global Plugins The Plugin management object
 */
$Plugins = & new Plugins();


// NOTE: it might be faster (though more bandwidth intensive) to spit cached pages (CachePageContent event) than to look into blocking the request (SessionLoaded event).
$Plugins->trigger_event( 'SessionLoaded' );


// Trigger a page content caching plugin. This would either return the cached content here or start output buffering
if( empty($generating_static) )
{
	if( $Session->get( 'core.no_CachePageContent' ) )
	{ // The event is disabled for this request:
		$Session->delete('core.no_CachePageContent');
		$Debuglog->add( 'Skipping CachePageContent event, because of core.no_CachePageContent setting.', 'plugins' );
	}
	elseif( ( $get_return = $Plugins->trigger_event_first_true( 'CachePageContent' ) ) // Plugin responded to the event
			&& ( isset($get_return['data']) ) ) // cached content returned
	{
		echo $get_return['data'];
		// Note: we should not use debug_info() here, because the plugin has probably sent a Content-Length header.
		exit(0);
	}
}


// TODO: we need an event hook here for the transport_optimizer_plugin, which must get called, AFTER another plugin might have started an output buffer for caching already.
//       Plugin priority is no option, because CachePageContent is a trigger_event_first_true event, for obvious reasons.
//       Name?
//       This must not be exactly here, but before any output.


/**
 * Includes:
 */
$Timer->resume('_main.inc:requires');
// Let the modules load/register what they need:
modules_call_method( 'init' );
$Timer->pause( '_main.inc:requires' );


/*
 * Login procedure: {{{
 * TODO: dh> the meat of this login procedure should be moved to an extra file,
 *           so that if a "logged in"-session exists (in most cases) it does not
 *           trigger parsing the meat of this code.
 * fp> ming you, most hits will be on the font end and will not be loggedin sessions
 *     However, I agree that the login stuff should only be included when the user is actually attempting to log in.
 */
if( !isset($login_required) )
{
	$login_required = false;
}


$login = NULL;
$pass = NULL;
$pass_md5 = NULL;

if( isset($_POST['login'] ) && isset($_POST['pwd'] ) )
{ // Trying to log in with a POST
	$login = $_POST['login'];
	$pass = $_POST['pwd'];
	unset($_POST['pwd']); // password will be hashed below
}
elseif( isset($_GET['login'] ) )
{ // Trying to log in with a GET; we might only provide a user here.
	$login = $_GET['login'];
	$pass = isset($_GET['pwd']) ? $_GET['pwd'] : '';
	unset($_GET['pwd']); // password will be hashed below
}

$Debuglog->add( 'login: '.var_export($login, true), 'login' );
$Debuglog->add( 'pass: '.( empty($pass) ? '' : 'not' ).' empty', 'login' );

// either 'login' (normal) or 'redirect_to_backoffice' may be set here. This also helps to display the login form again, if either login or pass were empty.
$login_action = param_arrayindex( 'login_action' );

$UserCache = & get_Cache( 'UserCache' );

if( ! empty($login_action) || (! empty($login) && ! empty($pass)) )
{ // User is trying to login right now
	$Debuglog->add( 'User is trying to log in.', 'login' );

	header_nocache();

	// Note: login and password cannot include '<' !
	$login = strtolower(strip_tags(remove_magic_quotes($login)));
	$pass = strip_tags(remove_magic_quotes($pass));
	$pass_md5 = md5( $pass );


	/*
	 * Handle javascript-hashed password:
	 * If possible, the login form will hash the entered password with a salt that changes everytime.
	 */
	param('pwd_salt', 'string', ''); // just for comparison with the one from Session
	$pwd_salt_sess = $Session->get('core.pwd_salt');

	// $Debuglog->add( 'salt: '.var_export($pwd_salt, true).', session salt: '.var_export($pwd_salt_sess, true) );

	$transmit_hashed_password = (bool)$Settings->get('js_passwd_hashing') && !(bool)$Plugins->trigger_event_first_true('LoginAttemptNeedsRawPassword');
	if( $transmit_hashed_password )
	{
		param( 'pwd_hashed', 'string', '' );
	}
	else
	{ // at least one plugin requests the password un-hashed:
		$pwd_hashed = '';
	}

	// $Debuglog->add( 'pwd_hashed: '.var_export($pwd_hashed, true).', pass: '.var_export($pass, true) );

	$pass_ok = false;
	// Trigger Plugin event, which could create the user, according to another database:
	if( $Plugins->trigger_event( 'LoginAttempt', array(
			'login' => & $login,
			'pass' => & $pass,
			'pass_md5' => & $pass_md5,
			'pass_salt' => & $pwd_salt_sess,
			'pass_hashed' => & $pwd_hashed,
			'pass_ok' => & $pass_ok ) ) )
	{ // clear the UserCache, if a plugin has been called - it may have changed user(s)
		$UserCache->clear();
	}

	if( $Messages->count('login_error') )
	{ // A plugin has thrown a login error..
		// Do nothing, the error will get displayed in the login form..

		// TODO: dh> make sure that the user gets logged out?! (a Plugin might have logged him in and another one thrown an error)
	}
	else
	{ // Check login and password

		// Make sure that we can load the user:
		$User = & $UserCache->get_by_login($login);

		if( $User && ! $pass_ok )
		{ // check the password, if no plugin has said "it's ok":
			if( ! empty($pwd_hashed) )
			{ // password hashed by JavaScript:

				$Debuglog->add( 'Hashed password available.', 'login' );

				if( empty($pwd_salt_sess) )
				{ // no salt stored in session: either cookie problem or the user had already tried logging in (from another window for example)
					$Debuglog->add( 'Empty salt_sess!', 'login' );
					if( ($pos = strpos( $pass, '_hashed_' ) ) && substr($pass, $pos+8) == $Session->ID )
					{ // session ID matches, no cookie problem
						$Messages->add( T_('The login window has expired. Please try again.'), 'login_error' );
						$Debuglog->add( 'Session ID matches.', 'login' );
					}
					else
					{ // more general error:
						$Messages->add( T_('Either you have not enabled cookies or this login window has expired.'), 'login_error' );
						$Debuglog->add( 'Session ID does not match.', 'login' );
					}
				}
				elseif( $pwd_salt != $pwd_salt_sess )
				{ // submitted salt differs from the one stored in the session
					$Messages->add( T_('The login window has expired. Please try again.'), 'login_error' );
					$Debuglog->add( 'Submitted salt and salt from Session do not match.', 'login' );
				}
				else
				{ // compare the password, using the salt stored in the Session:
					#pre_dump( sha1($User->pass.$pwd_salt), $pwd_hashed );
					$pass_ok = sha1($User->pass.$pwd_salt) == $pwd_hashed;
					$Session->delete('core.pwd_salt');
					$Debuglog->add( 'Compared hashed passwords. Result: '.(int)$pass_ok, 'login' );
				}
			}
			else
			{
				$pass_ok = ( $User->pass == $pass_md5 );
				$Debuglog->add( 'Compared raw passwords. Result: '.(int)$pass_ok, 'login' );
			}
		}
	}

	if( $pass_ok )
	{ // Login succeeded, set cookies
		$Debuglog->add( 'User successfully logged in with username and password...', 'login');
		// set the user from the login that succeeded
		$current_User = & $UserCache->get_by_login($login);
		// save the user for later hits
		$Session->set_User( $current_User );
	}
	elseif( ! $Messages->count('login_error') )
	{ // if there's no login_error message yet, add the default one:
		// This will cause the login screen to "popup" (again)
		$Messages->add( T_('Wrong login/password.'), 'login_error' );
	}

}
elseif( $Session->has_User() /* logged in */
	&& /* No login param given or the same as current user: */
	( empty($login) || ( ( $tmp_User = & $UserCache->get_by_ID($Session->user_ID) ) && $login == $tmp_User->login ) ) )
{ /* if the session has a user assigned to it:
	 * User was not trying to log in, but he was already logged in:
	 */
	// get the user ID from the session and set up the user again
	$current_User = & $UserCache->get_by_ID( $Session->user_ID );

	$Debuglog->add( 'Was already logged in... ['.$current_User->get('login').']', 'login' );
}
else
{ // The Session has no user or $login is given (and differs from current user), allow alternate authentication through Plugin:
	if( ($event_return = $Plugins->trigger_event_first_true( 'AlternateAuthentication' ))
	    && $Session->has_User()  # the plugin should have attached the user to $Session
	)
	{
		$Debuglog->add( 'User has been authenticated through plugin #'.$event_return['plugin_ID'].' (AlternateAuthentication)', 'login' );
		$current_User = & $UserCache->get_by_ID( $Session->user_ID );
	}
	elseif( $login_required )
	{ /*
		 * ---------------------------------------------------------
		 * User was not logged in at all, but login is required
		 * ---------------------------------------------------------
		 */
		// echo ' NOT logged in...';
		$Debuglog->add( 'NOT logged in... (did not try)', 'login' );

		$Messages->add( T_('You must log in!'), 'login_error' );
	}
}
unset($pass);


// Check if the user needs to be validated, but is not yet:
// TODO: dh> this block prevents registration, if you are logged in already, but not validated!
//       (e.g. when registered as "foo", you cannot register as "bar" until you logout (but there's no link in sight)
//        or validate the "foo" account)
if( ! empty($current_User)
		&& ! $current_User->validated
		&& $Settings->get('newusers_mustvalidate') // same check as in login.php
		&& param('action', 'string', '') != 'logout' ) // fp> TODO: non validated users should be automatically logged out
{
	if( $action != 'req_validatemail' && $action != 'validatemail' )
	{ // we're not in that action already:
		$action = 'req_validatemail'; // for login.php
		$Messages->add( T_('You must validate your email address before you can log in.'), 'login_error' );
	}
}
else
{ // Trigger plugin event that allows the plugins to re-act on the login event:
	if( empty($current_User) )
	{
		$Plugins->trigger_event( 'AfterLoginAnonymousUser', array() );
	}
	else
	{
		$Plugins->trigger_event( 'AfterLoginRegisteredUser', array() );

		if( ! empty($login_action) )
		{ // We're coming from the Login form and need to redirect to the requested page:
			if( $login_action == 'redirect_to_backoffice' )
			{ // user pressed the "Log into backoffice!" button
				$redirect_to = $admin_url;
			}
			else
			{
				param( 'redirect_to', 'string', $baseurl );
			}

			header_redirect( $redirect_to );
			exit(0);
		}
	}
}

// If there are "login_error" messages, they trigger the login form at the end of this file.

/* Login procedure }}} */


/*
 * User locale selection. Only override it if not set from REQUEST.
 */
if( is_logged_in() && $current_User->get('locale') != $current_locale && ! $locale_from_get )
{ // change locale to users preference
	/*
	 * User locale selection:
	 * TODO: this should get done before instantiating $current_User, because we already use T_() there...
	 */
	locale_activate( $current_User->get('locale') );
	if( $current_locale == $current_User->get('locale') )
	{
		$default_locale = $current_locale;
		$Debuglog->add( 'default_locale from user profile: '.$default_locale, 'locale' );
	}
	else
	{
		$Debuglog->add( 'locale from user profile could not be activated: '.$current_User->get('locale'), 'locale' );
	}
}


// Init charset handling:
init_charsets( $current_charset );


// Display login errors (and form). This uses $io_charset, so it's at the end.

if( $Messages->count( 'login_error' ) )
{
	require $htsrv_path.'login.php';
	exit(0);
}

$Timer->pause( '_main.inc');


/**
 * Load hacks file if it exists
 */
if( file_exists($conf_path.'hacks.php') )
{
	$Timer->resume( 'hacks.php' );
	include_once $conf_path.'hacks.php';
	$Timer->pause( 'hacks.php' );
}


/*
 * $Log$
 * Revision 1.123  2009/09/20 16:55:14  blueyed
 * Performance boost: add Timer_noop class and use it when not in debug mode.
 *
 * Revision 1.122  2009/09/20 16:21:17  blueyed
 * If locale gets set from REQUEST (locale_from_get), do not override it from user settings.
 *
 * Revision 1.121  2009/09/16 00:48:50  fplanque
 * getting a bit more serious with modules
 *
 * Revision 1.120  2009/09/16 00:25:41  fplanque
 * rollback of stuff that doesn't make any sense at all!!!
 *
 * Revision 1.118  2009/09/15 19:31:54  fplanque
 * Attempt to load classes & functions as late as possible, only when needed. Also not loading module specific stuff if a module is disabled (module granularity still needs to be improved)
 * PHP 4 compatible. Even better on PHP 5.
 * I may have broken a few things. Sorry. This is pretty hard to do in one swoop without any glitch.
 * Thanks for fixing or reporting if you spot issues.
 *
 * Revision 1.117  2009/09/14 12:26:53  efy-arrin
 * Included the ClassName in load_class() call with proper UpperCase
 *
 * Revision 1.116  2009/09/08 19:17:59  fplanque
 * reverted change that broke user registration
 *
 * Revision 1.115  2009/08/30 18:52:11  tblue246
 * Removed checking of unused variable
 *
 * Revision 1.114  2009/08/23 00:25:27  sam2kb
 * Never use locale from HTTP_ACCEPT nor locale from REQUEST when we set DB connection charset
 *
 * Revision 1.113  2009/08/12 12:01:49  sam2kb
 * doc
 *
 * Revision 1.112  2009/08/06 15:11:15  fplanque
 * doc
 *
 * Revision 1.111  2009/07/28 23:51:08  sam2kb
 * Do locale selection and set DB connection charset as early as possible
 * in order to get results in the right encoding
 *
 * Revision 1.110  2009/05/28 23:01:03  blueyed
 * Add Debuglog when charsets are not setup yet when translating: encoding issues in e.g. login form.. :/
 *
 * Revision 1.109  2009/03/31 21:57:00  blueyed
 * bad_request_die for protecting globals requires _misc.funcs.php
 *
 * Revision 1.108  2009/03/08 23:57:38  fplanque
 * 2009
 *
 * Revision 1.107  2009/03/05 23:38:53  blueyed
 * Merge autoload branch (lp:~blueyed/b2evolution/autoload) into CVS HEAD.
 *
 * Revision 1.106  2009/03/04 02:04:40  fplanque
 * better safe than sorry until someone is positive about this
 *
 * Revision 1.105  2009/03/03 20:14:11  blueyed
 * doc
 *
 * Revision 1.104  2009/03/03 20:13:25  blueyed
 * Instantiate "Debuglog" according to "debug" right away, which is known here already.
 *
 * Revision 1.103  2009/03/03 20:04:29  blueyed
 * Only "protect" superglobals if register_globals is on. Please review.
 *
 * Revision 1.102  2009/02/27 22:57:26  blueyed
 * Use load_funcs for swfcharts, and especially only include it when needed (in the stats controllers only, not main.inc)
 *
 * Revision 1.101  2009/02/27 22:25:16  blueyed
 * Fix inclusion of misc.funcs. Includes load_funcs now after all.
 *
 * Revision 1.100  2009/02/27 21:33:32  blueyed
 * Move load_funcs from class4.funcs to misc.funcs
 *
 * Revision 1.99  2009/02/26 23:52:30  blueyed
 * Fix inline CVS log
 *
 * Revision 1.98  2009/02/26 22:33:21  blueyed
 * Fix messup in last commit.
 *
 * Revision 1.97  2009/02/26 22:16:53  blueyed
 * Use load_class for classes (.class.php), and load_funcs for funcs (.funcs.php)
 *
 * Revision 1.96  2009/02/19 03:54:44  blueyed
 * Optimize: move instantiation of $IconLegend (and $UserSettings query) out of main.inc.php, into get_IconLegend. TODO: test if it works with PHP4, or if it needs assignment by reference. Will do so on the test server.
 *
 * Revision 1.95  2009/02/11 20:50:36  blueyed
 * Add more Debuglog to locale_from_get handling
 *
 * Revision 1.94  2008/12/28 19:02:19  fplanque
 * minor
 *
 * Revision 1.93  2008/12/23 17:17:25  blueyed
 * global_Cache: load all entries.. a typical installation (now) has 3 entries in this table and all get queried. So this saves 2 queries.
 *
 * Revision 1.92  2008/05/11 22:20:46  fplanque
 * minor
 *
 * Revision 1.91  2008/05/10 22:59:09  fplanque
 * keyphrase logging
 *
 * Revision 1.90  2008/02/19 11:11:16  fplanque
 * no message
 *
 * Revision 1.89  2008/01/22 15:34:46  fplanque
 * minor
 *
 * Revision 1.88  2008/01/21 09:35:23  fplanque
 * (c) 2008
 *
 * Revision 1.87  2008/01/14 23:41:47  fplanque
 * cleanup load_funcs( urls ) in main because it is ubiquitously used
 *
 * Revision 1.86  2007/12/10 01:06:33  blueyed
 * Apply same check as in login.php: if a user is not validated, but validation is turned off then do not require him to validate
 *
 * Revision 1.85  2007/12/10 00:45:33  blueyed
 * todo
 *
 * Revision 1.84  2007/11/28 17:29:45  fplanque
 * Support for getting updates from b2evolution.net
 *
 * Revision 1.83  2007/11/24 21:25:40  fplanque
 * make password encryption look like encryption
 *
 * Revision 1.82  2007/07/01 18:47:11  fplanque
 * fixes
 *
 * Revision 1.81  2007/06/26 02:40:53  fplanque
 * security checks
 *
 * Revision 1.80  2007/06/25 10:58:51  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.79  2007/06/24 01:05:31  fplanque
 * skin_include() now does all the template magic for skins 2.0.
 * .disp.php templates still need to be cleaned up.
 *
 * Revision 1.78  2007/06/20 23:12:51  fplanque
 * "Who's online" moved to a plugin
 *
 * Revision 1.77  2007/04/26 00:11:05  fplanque
 * (c) 2007
 *
 * Revision 1.76  2007/03/18 01:39:54  fplanque
 * renamed _main.php to main.page.php to comply with 2.0 naming scheme.
 * (more to come)
 *
 * Revision 1.75  2007/02/03 18:46:30  fplanque
 * doc
 *
 * Revision 1.74  2007/01/26 21:52:42  blueyed
 * Improved LoginAttempt hook: all params get passed by reference and "pass_ok" has been added
 *
 * Revision 1.73  2007/01/26 04:49:17  fplanque
 * cleanup
 *
 * Revision 1.72  2007/01/19 03:06:57  fplanque
 * Changed many little thinsg in the login procedure.
 * There may be new bugs, sorry. I tested this for several hours though.
 * More refactoring to be done.
 *
 * Revision 1.71  2006/12/28 15:44:31  fplanque
 * login refactoring / simplified
 *
 * Revision 1.70  2006/12/15 22:54:14  fplanque
 * allow disabling of password hashing
 *
 * Revision 1.69  2006/12/09 01:55:35  fplanque
 * feel free to fill in some missing notes
 * hint: "login" does not need a note! :P
 *
 * Revision 1.68  2006/12/08 12:33:22  blueyed
 * "login" debuglog category for "pwd_hashed" entry
 *
 * Revision 1.67  2006/12/06 23:32:35  fplanque
 * Rollback to Daniel's most reliable password hashing design. (which is not the last one)
 * This not only strengthens the login by providing less failure points, it also:
 * - Fixes the login in IE7
 * - Removes the double "do you want to memorize this password' in FF.
 *
 * Revision 1.66  2006/12/06 22:30:07  fplanque
 * Fixed this use case:
 * Users cannot register themselves.
 * Admin creates users that are validated by default. (they don't have to validate)
 * Admin can invalidate a user. (his email, address actually)
 *
 * Revision 1.65  2006/12/04 21:45:39  fplanque
 * cleanup
 *
 * Revision 1.63  2006/12/04 00:18:52  fplanque
 * keeping the login hashing
 *
 * Revision 1.61  2006/12/03 22:38:34  fplanque
 * doc
 *
 * Revision 1.60  2006/12/03 18:26:27  fplanque
 * doc
 *
 * Revision 1.59  2006/11/29 20:04:35  blueyed
 * More cleanup for login-password hashing
 *
 * Revision 1.58  2006/11/29 03:25:53  blueyed
 * Enhanced password hashing during login: get the password salt through async request + cleanup
 *
 * Revision 1.57  2006/11/24 18:27:22  blueyed
 * Fixed link to b2evo CVS browsing interface in file docblocks
 *
 * Revision 1.56  2006/11/22 00:04:19  blueyed
 * todo: $Session should not get instantiated if $is_cli
 *
 * Revision 1.55  2006/11/19 23:43:04  blueyed
 * Optimized icon and $IconLegend handling
 *
 * Revision 1.54  2006/11/14 21:13:58  blueyed
 * I've spent > 2 hours debugging this charset nightmare and all I've got are those lousy TODOs..
 *
 * Revision 1.53  2006/10/23 22:19:02  blueyed
 * Fixed/unified encoding of redirect_to param. Use just rawurlencode() and no funky &amp; replacements
 *
 * Revision 1.52  2006/10/15 21:30:45  blueyed
 * Use url_rel_to_same_host() for redirect_to params.
 *
 * Revision 1.51  2006/10/14 16:27:05  blueyed
 * Client-side password hashing in the login form.
 */
?>
