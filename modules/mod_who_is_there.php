<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_who_is_there.php,v 1.1.1.1.2.2 2005/07/26 15:50:31 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_who_is_there.php,v 1.1.1.1.2.2 2005/07/26 15:50:31 drewslater Exp $
 * @package users
 * @subpackage modules
 */
global $userlib;
$logged_users = $gBitUser->count_sessions();
$online_users = $gBitUser->get_online_users();
$gBitSmarty->assign('online_users', $online_users);
$gBitSmarty->assign('logged_users', $logged_users);
?>
