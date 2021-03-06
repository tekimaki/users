<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../../kernel/setup_inc.php' );



$gBitSystem->verifyPermission( 'p_users_admin' );

$feedback = array();

if( isset($_REQUEST["newuser"] ) ) {
	$userRecord = $_REQUEST;
	$newUser = new BitPermUser();

	if( $newUser->importUser( $userRecord ) ) {
		$gBitSmarty->assign( 'addSuccess', "User Added Successfully" );
		if( empty( $_REQUEST['admin_noemail_user'] ) ) {
			$ret = users_admin_email_user( $userRecord );
			if( is_array( $ret ) ) {
				list($key, $val) = each($ret);
				$newUser->mLogs[$key] = $val;
			}
			$logHash['action_log']['title'] = $userRecord['login'];
			$newUser->storeActionLog( $logHash );
		}
	} else {
		$gBitSmarty->assign_by_ref( 'newUser', $_REQUEST );
		$gBitSmarty->assign( 'errors', $newUser->mErrors );
	}
} elseif( isset( $_REQUEST["assume_user"]) && $gBitUser->hasPermission( 'p_users_admin' ) ) {
	$assume_user = (is_numeric( $_REQUEST["assume_user"] )) ? array( 'user_id' => $_REQUEST["assume_user"] ) : array('login' => $_REQUEST["assume_user"]) ;
	$userInfo = $gBitUser->getUserInfo( $assume_user );
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitUser->verifyTicket();
		if( $gBitUser->assumeUser( $userInfo["user_id"] ) ) {
			header( 'Location: '.$gBitSystem->getDefaultPage() );
			die;
		}elseif( !empty( $gBitUser->mErrors ) ){
			if ( !isset( $feedback['error'] ) ){
				$feedback['error'] = array();
			}
			$feedback['error'] = array_merge( $feedback['error'], $gBitUser->mErrors ); 
		}
	} else {
		$gBitSystem->setBrowserTitle( 'Assume User Identity' );
		$formHash['assume_user'] = $_REQUEST['assume_user'];
		$msgHash = array(
			'confirm_item' => tra( 'This will log you in as the user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	}
} elseif( !empty( $_REQUEST['find'] ) ) {
	$title = 'Find Users';
}
// Process actions here
// Remove user or remove user from group
if( isset( $_REQUEST["action"] ) ) {
	$formHash['action'] = $_REQUEST['action'];
	if( !empty( $_REQUEST['batch_user_ids'] ) && is_array( $_REQUEST['batch_user_ids'] ) ) {
		if( isset( $_REQUEST["confirm"] ) ) {
			$gBitUser->verifyTicket();
			$delUsers = $errDelUsers = "";
			$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
			foreach( $_REQUEST['batch_user_ids'] as $uid ) {
				$expungeUser = new $userClass( $uid );
				$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $uid ) );
				if( $expungeUser->load() && $expungeUser->expunge() ) {
					$delUsers .= "<li>{$userInfo['real_name']} ({$userInfo['login']})</li>";
				} else {
					$errDelUsers .= "<li>{$userInfo['real_name']} ({$userInfo['login']})</li>";
				}
			}

			if( !empty( $delUsers ) ) {
				$feedback['success'][] = tra( 'Users deleted' ).": <ul>$delUsers</ul>";
			} elseif( !empty( $errDelUsers ) ) {
				$feedback['error'][] = tra( 'Users not deleted' ).": <ul>$errDelUsers</ul>";
			}
		} else {
			foreach( $_REQUEST['batch_user_ids'] as $uid ) {
				$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $uid ) );
				$formHash['input'][] = '<input type="hidden" name="batch_user_ids[]" value="'.$uid.'"/>'."{$userInfo['real_name']} ({$userInfo['login']})";
			}
			$gBitSystem->setBrowserTitle( 'Delete users' );
			$msgHash = array(
				'confirm_item' => tra( 'Are you sure you want to remove these users?' ),
				'warning' => tra( 'This will permentally delete these users' ),
			);
			$gBitSystem->confirmDialog( $formHash, $msgHash );
		}
	} elseif( $_REQUEST["action"] == 'delete' ||  $_REQUEST["action"] == 'ban' ||  $_REQUEST["action"] == 'unban'  ) {
		if( !empty( $_REQUEST['user_id'] ) ){
			$formHash['user_id'] = $_REQUEST['user_id'];
			$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
			$reqUser = new $userClass( $_REQUEST["user_id"] );
			$reqUser->load();
			if( $reqUser->isValid() ){
				$reqUser->verifyAdminPermission();

				$userInfo = $reqUser->mInfo;

				if( isset( $_REQUEST["confirm"] ) ) {
					$gBitUser->verifyTicket();
					switch(  $_REQUEST["action"] ){
						case 'delete':
							$reqUser->mDb->StartTrans();
							if( $reqUser->load(TRUE) && $reqUser->expunge( !empty( $_REQUEST['delete_user_content'] ) ? $_REQUEST['delete_user_content'] : NULL ) ) {
								$feedback['success'][] = tra( 'User deleted' )." <strong>{$userInfo['real_name']} ({$userInfo['login']})</strong>";
							}
							$reqUser->mDb->CompleteTrans();
							break;
						case 'ban':
							if( $reqUser->load() && $reqUser->ban() ) {
								$feedback['success'][] = tra( 'User banned' )." <strong>{$userInfo['real_name']} ({$userInfo['login']})</strong>";
							}
							break;
						case 'unban':
							if( $reqUser->load() && $reqUser->unban() ) {
								$feedback['success'][] = tra( 'User restored' )." <strong>{$userInfo['real_name']} ({$userInfo['login']})</strong>";
							}
							break;
					}
				} else {
					switch( $_REQUEST["action"] ){
						case 'delete':
							$formHash['input'][] = "<input type='checkbox' name='delete_user_content' value='all' checked='checked'/>".tra( 'Delete all content created by this user' );
							foreach( $gLibertySystem->mContentTypes as $contentTypeGuid => $contentTypeHash ) {
	//							$formHash['input'][] = "<input type='checkbox' name='delete_user_content' checked='checked' value='$contentTypeGuid'/>Delete All User's $gLibertySystem->getContentTypeName($contentTypeHash['content_type_guid'],TRUE)";
							}

							$gBitSystem->setBrowserTitle( tra( 'Delete user' ) );
							$msgHash = array(
								'confirm_item' => tra( 'Are you sure you want to remove the user?' ),
								'warning' => tra( 'This will permentally delete the user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
							);
							break;
						case 'ban':
							$gBitSystem->setBrowserTitle( tra( 'Ban user' ) );
							$msgHash = array(
								'confirm_item' => tra( 'Are you sure you want to ban this user?' ),
								'warning' => tra( 'This will suspend the account for user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
								'title' => tra( 'Ban User Confirmation' ),
								'submit_label' => tra( 'Ban' ),
							);
							break;
						case 'unban':
							$gBitSystem->setBrowserTitle( tra( 'Unban user' ) );
							$msgHash = array(
								'confirm_item' => tra( 'Are you sure you want to unban this user?' ),
								'warning' => tra( 'This will restore the account for user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
							);
							break;
					}
					$gBitSystem->confirmDialog( $formHash,$msgHash );
				}
			} else {
				$feedback['error'][] = tra( 'User not found' );
			}
		} else {
			$feedback['error'][] = tra( 'No users were selected' );
		}
	}
	if ($_REQUEST["action"] == 'removegroup') {
		$gBitUser->removeUserFromGroup($_REQUEST["user"], $_REQUEST["group"]);
	}
}

// get default group and pass it to tpl
foreach( $gBitUser->getDefaultGroup() as $defaultGroupId => $defaultGroupName ) {
	$gBitSmarty->assign('defaultGroupId', $defaultGroupId );
	$gBitSmarty->assign('defaultGroupName', $defaultGroupName );
}

// override default max_records
$_REQUEST['max_records'] = !empty( $_REQUEST['max_records'] ) ? $_REQUEST['max_records'] : $gBitSystem->getConfig('max_records');
$gBitUser->getList( $_REQUEST );
$gBitSmarty->assign_by_ref('users', $_REQUEST["data"]);
$gBitSmarty->assign_by_ref('usercount', $_REQUEST["cant"]);
if (isset($_REQUEST["numrows"])) {
	$_REQUEST['listInfo']["numrows"] = $_REQUEST["numrows"];
} else {
	$_REQUEST['listInfo']["numrows"] = 10;
}
$_REQUEST['listInfo']["URL"] = USERS_PKG_URL."admin/index.php";
$gBitSmarty->assign_by_ref('listInfo', $_REQUEST['listInfo']);

// invoke edit service for the add user feature
$userObj = new BitPermUser();
$userObj->invokeServices( 'content_edit_function' );

// Get groups (list of groups)
$grouplist = $gBitUser->getGroups('', '', 'group_name_asc');
$gBitSmarty->assign( 'grouplist', $grouplist );
$gBitSmarty->assign( 'feedback', $feedback );

$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'userlist').'TabSelect', 'tdefault' );

// Display the template
$gBitSystem->display( 'bitpackage:users/users_admin.tpl', (!empty( $title ) ? $title : 'Edit Users' ) , array( 'display_mode' => 'admin' ));
?>
