<?php

require_once( '../kernel/setup_inc.php' );

// this is only for registered users
if( !$gBitUser->isRegistered() ) {
    Header( 'Location: '.USERS_PKG_URL.'login.php' );
    die;
}

$gBitSystem->display('bitpackage:users/display_thankyou.tpl', 'Register' , array( 'display_mode' => 'display' ));
