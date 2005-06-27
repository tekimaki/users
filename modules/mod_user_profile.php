<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_profile.php,v 1.2.2.1 2005/06/27 17:47:37 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_profile.php,v 1.2.2.1 2005/06/27 17:47:37 lsces Exp $
 * @package users
 * @subpackage modules
 */
global $gQueryUser, $gBitUser, $smarty;

if( !empty( $gQueryUser->mInfo ) ) {
	$smarty->assign_by_ref('userInfo', $gQueryUser->mInfo );
} elseif( !empty( $gBitUser->mInfo ) ) {
	$smarty->assign_by_ref('userInfo', $gBitUser->mInfo );
}
?>
