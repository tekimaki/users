<?php
/**
 * @fork - do not commit back to bitweaver
 * forked from preferences.php
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

// User preferences screen
$gBitSystem->verifyFeature( 'users_preferences' );

$gBitUser->verifyRegistered();

// user selected 'Cancel'
if( !empty( $_REQUEST['cancel'] ) ) {
	bit_redirect( USERS_PKG_URL."my.php" );
}

// SETUP
// hash to capture errors
$feedback['errors'] = array();

// set up the user we're editing
$gBitUser->load( TRUE );
$editUser = &$gBitUser;

global $gQueryUserId;
$gQueryUserId = &$editUser->mUserId;

$parsedUrl = parse_url( $_SERVER["REQUEST_URI"] );

// custom user fields
if( $gBitSystem->isFeatureActive( 'custom_user_fields' )) {
	$customFields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' )  );
	$gBitSmarty->assign('customFields', $customFields );
}

// include preferences settings from other packages - these will be included as individual tabs
$includeFiles = $gBitSystem->getIncludeFiles( 'user_preferences_inc.php', 'user_preferences_inc.tpl' );
foreach( $includeFiles as $file ) {
	require_once( $file['php'] );
}
$gBitSmarty->assign( 'includFiles', $includeFiles );

// fetch available languages
$gBitLanguage->mLanguage = $editUser->getPreference( 'bitlanguage', $gBitLanguage->mLanguage );
$gBitSmarty->assign( 'gBitLanguage', $gBitLanguage );


// process the preferences form
if( isset( $_REQUEST["prefs"] ) || isset( $_REQUEST["save_users_information"] )) {
	$editUser->store( $_REQUEST );

	// preferences
	$prefs = array(
		'users_homepage'        => NULL,
		'site_display_utc'		=> 'Local',
		'site_display_timezone' => 'UTC',
		'users_country'         => NULL,
		'users_information'     => 'public',
		'users_email_display'   => 'n',
	);

	if( !empty( $_REQUEST['site_display_utc'] ) && $_REQUEST['site_display_utc'] != 'Fixed' ) {
		unset( $_REQUEST['site_display_timezone'] );
		$editUser->storePreference( 'site_display_timezone', NULL, USERS_PKG_NAME );
	}

	// we don't have to store http:// in the db
	if( empty( $_REQUEST['users_homepage'] ) || $_REQUEST['users_homepage'] == 'http://' ) {
		unset( $_REQUEST['users_homepage'] );
	} elseif( !preg_match( '/^http:\/\//', $_REQUEST['users_homepage'] )) {
		$_REQUEST['users_homepage'] = 'http://'.$_REQUEST['users_homepage'];
	}

	// store preferences
	if( !empty( $_REQUEST['prefs'] ) ) {
		foreach( array_keys( $_REQUEST['prefs'] ) as $key ) {
			$editUser->storePreference( $key, $_REQUEST['prefs'][$key] );
		}
	}

	// store default prefs if not set
	foreach( $prefs as $pref => $default ) {
		if( empty( $_REQUEST[$pref] ) ) {
			$editUser->storePreference( $pref, NULL, USERS_PKG_NAME );
		}
	}

	if( $gBitSystem->isFeatureActive( 'users_change_language' )) {
		if( !empty( $_REQUEST['bitlanguage'] ) ){
			$editUser->storePreference( 'bitlanguage', ( $_REQUEST['bitlanguage'] != $gBitLanguage->mLanguage ) ? $_REQUEST['bitlanguage'] : NULL, LANGUAGES_PKG_NAME );
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

	// we need to call services when prefs change as other services may depend on it
	$editUser->invokeServices( 'content_store_function', $_REQUEST );

	if( empty( $editUser->mErrors ) && empty( $feedback['errors'] ) ){
		$feedback['success'][] = tra( 'Your profile was updated successfully' );
	}
}

// change email address
if( !empty( $_REQUEST['new_email'] ) && $_REQUEST['new_email'] != $editUser->getField( 'email' ) ) {
	$org_email = $editUser->getField( 'email' );
	if( $editUser->changeUserEmail( $editUser->mUserId, $_REQUEST['new_email'] )) {
		$feedback['success'][] = tra( 'Your email address was updated successfully' );

		// we need to call services when email is changed as other services may depend on it
		$_REQUEST['org_email'] = $org_email;
		$editUser->invokeServices( 'content_store_function', $_REQUEST );

		// HACK! hard code in unsubscribe from mailchip because this is royal pain in the ass if change of email fails
		if( $gBitSystem->isPackageActive( 'mailchimp' ) ){ 
			mailchip_unsubscribe( $org_email );
		}
	} else {
		$feedback['errors'] = array_merge( $feedback['errors'], $editUser->mErrors );
	}
}

// change user password
if( isset( $_REQUEST["chgpswd"] ) && !empty( $_REQUEST['pass1'] ) ) {

	// validate new pass
	if( empty( $_REQUEST['pass2'] ) || $_REQUEST["pass1"] != $_REQUEST["pass2"] ) {
		$feedback['errors']['pass2'] = tra("The passwords didn't match" );
	}

	// validate old pass
	if( empty( $_REQUEST["old_pass"] ) || !$editUser->validate( $editUser->getField( 'email' ), $_REQUEST["old_pass"], '', '' )
	){
		$feedback['errors']['old_pass'] = tra( "Invalid old password" );
	}

	// pass length
	$users_min_pass_length = $gBitSystem->getConfig( 'users_min_pass_length', 4 );
	if( strlen( $_REQUEST["pass1"] ) < $users_min_pass_length ) {
		$feedback['errors']['pass1'] = tra( "Password should be at least" ).' '.$users_min_pass_length.' '.tra( "characters long" );
	}

	// optional number char requirement
	if( $gBitSystem->isFeatureActive( 'users_pass_chr_num' )) {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass1"], $parsedUrl ) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass1"], $parsedUrl )) {
			$feedback['errors']['pass1'] = tra( "Password must contain both letters and numbers" );
		}
	}

	if( empty( $feedback['errors']['pass1'] ) && 
		empty( $feedback['errors']['pass2'] ) && 
		empty( $feedback['errors']['old_pass'] ) && 
		$editUser->storePassword( $_REQUEST["pass1"] ) ) {
		$feedback['success'][] = tra( 'Your password was updated successfully' );
	}
}

// Check if old pass field is filled in and other fields are not...Don't display success message if that is the case
if( empty( $feedback['errors']['pass1'] ) && 
	empty( $feedback['errors']['pass2'] ) && 
	empty( $feedback['errors']['old_pass'] ) ) {
		if( !empty( $_REQUEST["old_pass"] ) && (empty( $_REQUEST['pass1'] ) || empty( $_REQUEST['pass2'] )) ) {
			$feedback['errors']['old_pass'] = tra( "Please fill out all the password fields." );
		}
	}


/**
 * Normal page loading
 */
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

$editUser->mInfo['users_homepage'] = $editUser->getPreference( 'users_homepage', '' );

$gBitSmarty->assign( 'editUser', $editUser );
$gBitSmarty->assign( 'gContent', $editUser ); // also assign to gContent to make services happy
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSmarty->assign( 'errors', $feedback['errors'] );

/* This should come from BitDate->get_timezone_list but that seems to rely on a global from PEAR that does not exist. */
if ( version_compare( phpversion(), "5.2.0", ">=" ) ) {
	$user_timezones = DateTimeZone::listIdentifiers();
} else {
	for($i=-12;$i<=12;$i++) {
		$user_timezones[$i * 60 * 60] = $i; // Stored offset needs to be in seconds.
	}
}
$gBitSmarty->assign( 'userTimezones', $user_timezones);

// email scrambling methods
$scramblingMethods = array( "n", "strtr", "unicode", "x" );
$gBitSmarty->assign_by_ref( 'scramblingMethods', $scramblingMethods );
$scramblingEmails = array(
	tra( "no" ),
	scramble_email( $editUser->mInfo['email'], 'strtr' ),
	scramble_email( $editUser->mInfo['email'], 'unicode' )."-".tra( "unicode" ),
	scramble_email( $editUser->mInfo['email'], 'x' )
);
$gBitSmarty->assign_by_ref( 'scramblingEmails', $scramblingEmails );

// edit services
$editUser->invokeServices( 'content_edit_function' );
$gBitUser->invokeServices( 'content_display_function' );
$pageTitle = tra('Account Settings');
$gBitSmarty->assign('pageTitle',$pageTitle );
$gBitSystem->display( 'bitpackage:users/my_bitweaver.tpl', $pageTitle, array( 'display_mode' => 'display' ));
