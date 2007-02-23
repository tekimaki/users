<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/preferences.php,v 1.40 2007/02/23 15:36:41 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: preferences.php,v 1.40 2007/02/23 15:36:41 squareing Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// User preferences screen
$gBitSystem->verifyFeature( 'users_preferences' );

if( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( "You are not logged in" ));
}

// set up the user we're editing
if( !empty( $_REQUEST["view_user"] ) && $_REQUEST["view_user"] <> $gBitUser->mUserId ) {
	$gBitSystem->verifyPermission( 'p_users_admin' );
	$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
	$editUser = new $userClass( $_REQUEST["view_user"] );
	$editUser->load( TRUE );
	$gBitSmarty->assign('view_user', $_REQUEST["view_user"]);
    $watches = $editUser->getWatches();
    $gBitSmarty->assign('watches', $watches );
} else {
	$gBitUser->load( TRUE );
	$editUser = &$gBitUser;
}

global $gQueryUserId;
$gQueryUserId = &$editUser->mUserId;

$parsedUrl = parse_url( $_SERVER["REQUEST_URI"] );

// settings only applicable when the wiki package is active
if( $gBitSystem->isPackageActive( 'wiki' )) {
	include_once( WIKI_PKG_PATH.'BitPage.php' );
	$parsedUrl1 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."edit", $parsedUrl["path"] );
	$parsedUrl2 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."index", $parsedUrl["path"] );
	$gBitSmarty->assign( 'url_edit', httpPrefix(). $parsedUrl1 );
	$gBitSmarty->assign( 'url_visit', httpPrefix(). $parsedUrl2 );
}

// custom user fields
if( $gBitSystem->isFeatureActive( 'custom_user_fields' )) {
	$customFields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' )  );
	$gBitSmarty->assign('customFields', $customFields );
}

// include preferences settings from other packages - these will be included as individual tabs
$packages = array();
foreach( $gBitSystem->mPackages as $package ) {
	if( $gBitSystem->isPackageActive( $package['name'] )) {
		$php_file = $package['path'].'user_preferences_inc.php';
		$tpl_file = $package['path'].'templates/user_preferences_inc.tpl';
		if( file_exists( $tpl_file )) {
			if( file_exists( $php_file ))  {
				require( $php_file );
			}
			$p=array();
			$p['template'] = $tpl_file;
			$packages[] = $p;
		}
	}
}
$gBitSmarty->assign_by_ref('packages',$packages );

// fetch available languages
$gBitLanguage->mLanguage = $editUser->getPreference( 'bitlanguage', $gBitLanguage->mLanguage );
$gBitSmarty->assign( 'gBitLanguage', $gBitLanguage );

// allow users to set their preferred site style - this option is only available when users can set the site-wide theme
if( $gBitSystem->getConfig( 'users_themes' ) == 'y' ) {
	if( !empty( $_REQUEST['prefs'] )) {
		$editUser->storePreference( 'theme', !empty( $_REQUEST["style"] ) ? $_REQUEST["style"] : NULL );
		$assignStyle = $_REQUEST["style"];
	}
	$styles = $gBitThemes->getStyles( NULL, TRUE, TRUE );
	$gBitSmarty->assign_by_ref( 'styles', $styles );

	if( !isset( $_REQUEST["style"] )) {
		$assignStyle = $editUser->getPreference( 'theme' );
	}
	$gBitSmarty->assign( 'assignStyle', $assignStyle );
}

// process the preferences form
if( isset( $_REQUEST["prefs"] )) {
	if( isset( $_REQUEST["real_name"] )) {
		$editUser->store( $_REQUEST );
	}

	// preferences
	$prefs = array(
		'users_bread_crumb'     => USERS_PKG_NAME,
		'users_homepage'        => USERS_PKG_NAME,
		'site_display_timezone' => USERS_PKG_NAME,
		'users_country'         => USERS_PKG_NAME,
		'users_information'     => USERS_PKG_NAME,
		'users_email_display'   => USERS_PKG_NAME,
	);

	if( $gBitSystem->isFeatureActive( 'users_change_language' )) {
		$prefs[] = array( 'bitlanguage' => LANGUAGES_PKG_NAME );
	}

	foreach( $prefs as $pref => $package ) {
		if( isset( $_REQUEST[$pref] )) {
			$editUser->storePreference( $pref, $_REQUEST[$pref], $package );
		} else {
			$editUser->storePreference( $pref, NULL, $package );
		}
	}

	// toggles
	$toggles = array(
		'users_double_click'  => USERS_PKG_NAME,
	);

	foreach( $toggles as $toggle => $package ) {
		if( isset( $_REQUEST[$toggle] )) {
			$editUser->storePreference( $toggle, 'y', $package );
		} else {
			$editUser->storePreference( $toggle, NULL, $package );
		}
	}

	// process custom fields
	if( isset( $customFields ) && is_array( $customFields )) {
		foreach( $customFields as $f ) {
			if( isset( $_REQUEST['CUSTOM'][$f] )) {
				$editUser->storePreference( trim( $f ), trim( $_REQUEST['CUSTOM'][$f] ), USERS_PKG_NAME );
			}
		}
	}

	// we need to reload the page for all the included user preferences
	if( isset( $_REQUEST['view_user'] )) {
		header ("location: ".USERS_PKG_URL."preferences.php?view_user=$editUser->mUserId");
	} else {
		header ("location: ".USERS_PKG_URL."preferences.php");
	}
	die;
}

// change email address
if( isset( $_REQUEST['chgemail'] )) {
	// check user's password
	if( !$gBitUser->hasPermission( 'p_users_admin' ) && !$editUser->validate( $editUser->mUsername, $_REQUEST['pass'], '', '' )) {
		$gBitSystem->fatalError( tra("Invalid password.  Your current password is required to change your email address." ));
	}

	if( $editUser->change_user_email( $editUser->mUserId, $editUser->mUsername, $_REQUEST['email'], $_REQUEST['pass'] )) {
		$gBitSmarty->assign( 'successMsg', tra( 'Your email address was updated successfully' ));
		#make sure udpated value appears on screen repaint
		$editUser->mInfo['email'] = $_REQUEST['email'];
	}
}

// change user password
if( isset( $_REQUEST["chgpswd"] )) {
	if( $_REQUEST["pass1"] != $_REQUEST["pass2"] ) {
		$gBitSystem->fatalError( tra("The passwords didn't match" ));
	}
	if( !$gBitUser->hasPermission( 'p_users_admin' ) && !$editUser->validate( $editUser->mUsername, $_REQUEST["old"], '', '' )) {
		$gBitSystem->fatalError( tra( "Invalid old password" ));
	}
	//Validate password here
	$users_min_pass_length = $gBitSystem->getConfig( 'users_min_pass_length', 4 );
	if( strlen( $_REQUEST["pass1"] ) < $users_min_pass_length ) {
		$gBitSystem->fatalError( tra( "Password should be at least" ).' '.$users_min_pass_length.' '.tra( "characters long" ));
	}
	// Check this code
	if( $gBitSystem->isFeatureActive( 'users_pass_chr_num' )) {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass1"], $parsedUrl ) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass1"], $parsedUrl )) {
			$gBitSystem->fatalError( tra( "Password must contain both letters and numbers" ));
		}
	}
	if( $editUser->storePassword( $_REQUEST["pass1"] )) {
		$gBitSmarty->assign( 'successMsg', tra( 'The password was updated successfully' ));
	}
}


// this should go in tidbits
if( isset( $_REQUEST['tasksprefs'] )) {
	$editUser->storePreference( 'tasks_max_records', $_REQUEST['tasks_max_records'], 'users' );
	if( isset( $_REQUEST['tasks_use_dates'] ) && $_REQUEST['tasks_use_dates'] == 'on' ) {
		$editUser->storePreference( 'tasks_use_dates', 'y', 'users' );
	} else {
		$editUser->storePreference( 'tasks_use_dates', 'n', 'users' );
	}
}

// get available languages
$languages = array();
$languages = $gBitLanguage->listLanguages();
$gBitSmarty->assign_by_ref( 'languages', $languages );

// Get flags
$flags = array();
$h = opendir( USERS_PKG_PATH.'icons/flags/' );
while( $file = readdir( $h )) {
	if( strstr( $file, ".gif" )) {
		$flags[] = preg_replace( "/\.gif/", "", $file );
	}
}
closedir( $h );
sort( $flags );
$gBitSmarty->assign( 'flags', $flags );

$editUser->mInfo['users_bread_crumb'] = $editUser->getPreference( 'users_bread_crumb', $gBitSystem->getConfig('users_bread_crumb', 4 ));
$editUser->mInfo['users_homepage'] = $editUser->getPreference( 'users_homepage', '' );

$gBitSmarty->assign( 'editUser', $editUser );

// email scrambling methods
$scramblingMethods = array( "n", "strtr", "unicode", "x" );
$gBitSmarty->assign_by_ref( 'scramblingMethods', $scramblingMethods );
$scramblingEmails = array(
	tra("no"),
	scrambleEmail( $editUser->mInfo['email'], 'strtr' ),
	scrambleEmail( $editUser->mInfo['email'], 'unicode' )."-".tra( "unicode" ),
	scrambleEmail( $editUser->mInfo['email'], 'x' )
);
$gBitSmarty->assign_by_ref( 'scramblingEmails', $scramblingEmails );
$gBitSystem->display( 'bitpackage:users/user_preferences.tpl', 'Edit User Preferences' );
?>
