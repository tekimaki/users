<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/confirm.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: confirm.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

if( $userInfo = $gBitUser->confirmRegistration( $_REQUEST["user"], $_REQUEST["pass"] ) ) {
	$smarty->assign_by_ref( 'userInfo', $userInfo );
	$gBitSystem->display( 'bitpackage:users/change_password.tpl' );
} else {
	$smarty->assign('msg', tra("Invalid username or password"));
	$gBitSystem->display( 'error.tpl' );
}
?>
