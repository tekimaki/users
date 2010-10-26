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
 * $Readme$
 *
 * Logout and http_auth
 * If using bitweaver's built in http_auth to protect areas of the site 
 * once logged in using http_auth it is technically impossible to logout.
 * Browsers cache the http_auth user/pass values, so even if the user
 * hits this path, the cached http_auth will log them back in immediately
 * If the user clears the http_auth cache from their browser then they
 * can not see the site, which is the indended performace again.
 *
 * It is common that non-technical people will not at first understand
 * this intended effect. You can use this note to explain it to them. 
 */
$bypass_siteclose_check = 'y';

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );
global $gBitSystem;
// go offline in Live Support
if ($gBitSystem->isPackageActive( 'LIVE_SUPPORT_PKG_NAME' ) ) {
	include_once( LIVE_SUPPORT_PKG_PATH.'ls_lib.php' );
	if ($lslib->get_operator_status($user) != 'offline') {
		$lslib->set_operator_status($user, 'offline');
	}
}
$gBitUser->logout();
header ("location: ".$gBitSystem->getDefaultPage());
exit;
?>
