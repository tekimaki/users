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
include_once ("../kernel/setup_inc.php");

if( !empty( $_REQUEST['returnto'] ) ) {
	$_SESSION['returnto'] = $_REQUEST['returnto'];
} elseif( !empty( $_SERVER['HTTP_REFERER'] ) && !strpos( $_SERVER['HTTP_REFERER'], 'login.php' )  && !strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) ) {
	$from = parse_url( $_SERVER['HTTP_REFERER'] );
	if( !empty( $from['path'] ) && $from['host'] == $_SERVER['SERVER_NAME'] ) {
		$_SESSION['loginfrom'] = $from['path'].'?'.( !empty( $from['query'] ) ? $from['query'] : '' );
	}
}

if( $gBitUser->isRegistered() ) {
	// if group home is set for this user we get that
	if( method_exists( $gBitUser, 'getGroupHome' ) && 
		(( @$gBitUser->verifyId( $gBitUser->mInfo['default_group_id'] ) && ( $group_home = $gBitUser->getGroupHome( $gBitUser->mInfo['default_group_id'] ) ) ) ||
		( $gBitSystem->getConfig( 'default_home_group' ) && ( $group_home = $gBitUser->getGroupHome( $gBitSystem->getConfig( 'default_home_group' ) ) ) )) ){
		$indexType = 'group_home';

		header( 'Location: '.$gBitSystem->getIndexPage( $indexType ) );
	}
	// else global defaults
	else{
		header( 'Location: '.$gBitSystem->getConfig( 'users_login_homepage', USERS_PKG_URL.'my.php' ) );
	}
	die;
}

if( !empty( $_REQUEST['error'] ) ) {
	$gBitSmarty->assign( 'error', $_REQUEST['error'] );
}

$gBitSystem->display( 'bitpackage:users/login.tpl', tra('Login'), array( 'display_mode' => 'display' ));
?>
