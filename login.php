<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/login.php,v 1.10 2008/06/25 22:21:28 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: login.php,v 1.10 2008/06/25 22:21:28 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
include_once ("../bit_setup_inc.php");

if( !empty( $_SERVER['HTTP_REFERER'] ) && !strpos( $_SERVER['HTTP_REFERER'], 'login.php' )  && !strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) ) {
	$from = parse_url( $_SERVER['HTTP_REFERER'] );
	if( !empty( $from['path'] ) && $from['host'] == $_SERVER['SERVER_NAME'] ) {
		$_SESSION['loginfrom'] = $from['path'].'?'.( !empty( $from['query'] ) ? $from['query'] : '' );
	}
}

if( $gBitUser->isRegistered() ) {
	header( 'Location: '.USERS_PKG_URL.'my.php' );
	die;
}

if( !empty( $_REQUEST['error'] ) ) {
	$gBitSmarty->assign( 'error', $_REQUEST['error'] );
}

$gBitSystem->display( 'bitpackage:users/login.tpl', $gBitSystem->getConfig( 'site_title' ).' Login' , array( 'display_mode' => 'display' ));
?>
