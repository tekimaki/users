<?php
/**
 * @version $Header$
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => USERS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "This upgrade adds new permissions.",
	'post_upgrade' => NULL,
);
$gBitInstaller->registerPackageUpgrade( $infoHash, array(
array( 'PHP' => '
	global $gBitInstaller;
	$gBitInstaller->installPermission( "p_users_create","Can create a user","editors","users" );
	$gBitInstaller->installPermission( "p_users_view","Can view user","basic","users" );
	$gBitInstaller->installPermission( "p_users_update","Can update any user","editors","users" );
	$gBitInstaller->installPermission( "p_users_expunge","Can delete any user","admin","users" );
	$gBitInstaller->installPermission( "p_users_admin","Can admin any users","admin","users" );
' )
));

