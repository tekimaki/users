<?php
/**
 * @version $Header$
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => USERS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "This upgrade registers services using the new package plugin system.",
	'post_upgrade' => NULL,
);
$gBitInstaller->registerPackageUpgrade( $infoHash, array(
array( 'PHP' => '
	global $gBitSystem;
	$schema = $gBitSystem->getPackageSchema(\'users\');
	foreach( $schema[\'plugin_apis\'] as $type => $apis ){
		foreach( $apis as $api ){
			$gBitSystem->storePluginAPI( $type, $api );
		}
	}
' )
));

