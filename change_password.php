<?php
/**
 * $Header$
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id$
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );
if( !isset( $_REQUEST['login'] )) {
	$_REQUEST['login'] = '';
}
if( !isset( $_REQUEST['user_id'] )) {
	$_REQUEST['user_id'] = '';
}
if( !isset( $_REQUEST["oldpass"] )) {
	$_REQUEST["oldpass"] = '';
}
if( !isset( $_REQUEST["provpass"] )) {
	$_REQUEST["provpass"] = '';
}

$gBitSmarty->assign( 'login', $_REQUEST['login'] );
$gBitSmarty->assign( 'oldpass', $_REQUEST["oldpass"] );
$gBitSmarty->assign( 'provpass', $_REQUEST["provpass"] );
$gBitSmarty->assign( 'provpass', $_REQUEST["v"] );

$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST['user_id'] ));
$gBitSmarty->assign_by_ref( 'userInfo', $userInfo );

if( isset( $_REQUEST["change"] )) {
	$validated = FALSE;
	$errors = array();

	if( $_REQUEST["pass"] == $_REQUEST["oldpass"] ) {
		$errors['pass'] = tra( "You can not use the same password again" );
	}

	if( $passwordError = $gBitUser->verifyPasswordFormat( $_REQUEST["pass"], $_REQUEST["pass2"] )) {
		$errors['pass'] = $passwordError;
	}

	if( empty( $errors ) ){
		if( !empty( $_REQUEST["provpass"] ) ) {
			if( $validated = $gBitUser->confirmRegistration( $userInfo['user_id'], $_REQUEST["provpass"] ) ) {
				if( $gBitSystem->isFeatureActive( 'send_welcome_email' ) ) {
					$siteName = $gBitSystem->getConfig( 'site_title', $_SERVER['HTTP_HOST'] );
					// Send the welcome mail
					$gBitSmarty->assign( 'siteName', $_SERVER["SERVER_NAME"] );
					$gBitSmarty->assign( 'mail_site', $_SERVER["SERVER_NAME"] );
					$gBitSmarty->assign( 'mail_user', $userInfo['login'] );
					$gBitSmarty->assign( 'mailPassword',$_REQUEST['pass'] );
					$gBitSmarty->assign( 'mailEmail',$validated['email'] );
					$mail_data = $gBitSmarty->fetch('bitpackage:users/welcome_mail.tpl');
					global $gSwitchboardSystem;
					$msg = array();
					$recipients = array( array( 'email' => $validated['email'] ), );
					$msg['recipients'] = $recipients;
					$msg['subject'] = tra( 'Password changed for: ' ).$siteName;
					$msg['alt_message'] = $mail_data;
					$gSwitchboardSystem->sendEmail( $msg );
				}
			} else	{
				$gBitSystem->fatalError( tra("Password reset request is invalid or has expired") );
			}
		} elseif( !( $validated = $gBitUser->validate( $userInfo['email'], $_REQUEST["oldpass"], '', '' )) ) {
			$errors['oldpass'] = tra("Invalid old password");
		}
	}

	if( $validated ) {
		$gBitUser->storePassword( $_REQUEST["pass"], (!empty( $userInfo['login'] )?$userInfo['login']:$userInfo['email']) );
		$url = $gBitUser->login( (!empty( $userInfo['login'] )?$userInfo['login']:$userInfo['email']), $_REQUEST["pass"] );
		bit_redirect( $url );
	}
	else{
		$gBitSmarty->assign( 'errors', $errors );
		if( !empty( $_REQUEST['v'] ) ){ 
			include_once( USERS_PKG_PATH.'confirm.php' );
		}
	}
}

// Display the template
$gBitSystem->display( 'bitpackage:users/change_password.tpl', 'Change Password' , array( 'display_mode' => 'display' ));

?>
