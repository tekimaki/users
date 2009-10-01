<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/custom_home.php,v 1.9 2009/10/01 14:17:06 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: custom_home.php,v 1.9 2009/10/01 14:17:06 wjames5 Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
//ini_set('include_path','.;pear/');
//include('foobar.php');
/*
hfd
require_once "lib/NNTP.php";
$nntp = new Net_NNTP;
$ret = $nntp->connect("news.php.net");
$groups = $nntp->getGroups();
//print_r($groups);
$z = $nntp->selectGroup('php.announce');
print_r($z);
$h = $nntp->splitHeaders(1);
print_r($h);
$b = $nntp->getBody(1);
print_r($b);
*/
$gBitSystem->verifyFeature( 'users_custom_home' );
// Display the template
$gBitSystem->display( 'bitpackage:users/custom_home_2.tpl', NULL, array( 'display_mode' => 'display' ));
?>
