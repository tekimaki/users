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
	global $gBitSystem, $gBitInstaller;
	$schema = $gBitSystem->getPackageSchema(\'users\');

	foreach( $schema[\'plugins\'] as $pluginGuid=>$pluginHash ){
		// modify the data hash with required params
		$pluginHash[\'guid\'] = $pluginGuid;
		$pluginHash[\'package_guid\'] = \'users\';
		$gBitInstaller->setPluginActive( $pluginHash );
		$gBitInstaller->installPluginAPIHandlers(  $pluginHash, NULL, NULL );
	}
' )
));

