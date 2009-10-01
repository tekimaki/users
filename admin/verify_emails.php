<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/verify_emails.php,v 1.7 2009/10/01 14:17:06 wjames5 Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../../bit_setup_inc.php' );

	
	$gBitUser->verifyTicket();

	$selectSql = 'SELECT uu.user_id,uu.email  FROM users_users uu WHERE user_id NOT IN (SELECT user_id FROM users_groups_map WHERE group_id = ?) ORDER BY uu.user_id';
	$users     = $gBitDb->getAssoc($selectSql, array( $gBitSystem->getConfig('users_validate_email_group') ) );
	$errors;
	foreach ( $users as $id=>$email ){
		print "Verifying $email ( $id ) .... ";
		flush();
		$emailStatus = $gBitUser->verifyMx($email,$errors);
		if( $emailStatus === true){
			$gBitUser->addUserToGroup( $id , $gBitSystem->getConfig('users_validate_email_group') );
			print "valid";
		} elseif( $emailStatus === -1 )  {
			print "MX connection failed";
		} else {
			print " --INVALID-- ";
		}
		print "<br/>\n";
		flush();
	}


