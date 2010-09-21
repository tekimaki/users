<?php
/**
 * @version $Header$
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => USERS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Update users permissions table to allow longer character string for guid values. Add unique constraints to content_id on users_users table.",
	'post_upgrade' => NULL,
);

$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
	// insert new column
	array( 'ALTER' => array(
		'users_permissions' => array(
			'perm_name' => array( '`perm_name`', 'TYPE VARCHAR(128)' ),
		),
	)),
)),

// copy data into new column
array( 'QUERY' =>
	array(
		'SQL92' => array( 
			"ALTER TABLE `".BIT_DB_PREFIX."users_users` ADD CONSTRAINT users_users_content_id_idx UNIQUE (content_id)" 
		),
	),
),

));
