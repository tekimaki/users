<?php
class UserFilesLib extends BitBase {
	function UserFilesLib() {
		BitBase::BitBase();
	}
	function userfiles_quota($user) {
		global $bit_p_admin;
		if ($gBitUser->hasPermission( 'bit_p_admin' )) {
			return 0;
		}
		$part1 = $this->getOne("select sum(`filesize`) from `".BIT_DB_PREFIX."tiki_userfiles` where `user_id`=?",array($user));
		$part2 = $this->getOne("select sum(`size`) from `".BIT_DB_PREFIX."tiki_user_notes` where `user_id`=?",array($user));
		return $part1 + $part2;
	}
	function upload_userfile($user, $name, $filename, $filetype, $filesize, $data, $path) {
		$now = date("U");
		$query = "insert into `".BIT_DB_PREFIX."tiki_userfiles`(`user_id`,`name`,`filename`,`filetype`,`filesize`,`data`,`created`,`hits`,`path`)
    values(?,?,?,?,?,?,?,?,?)";
		$this->query($query,array($user,$name,$filename,$filetype,(int) $filesize,$this->mDb->db_byte_encode($data),(int) $now,0,$path));
	}
	function list_userfiles($user, $offset, $maxRecords, $sort_mode, $find) {
		if ($find) {
			$findesc = '%' . strtoupper( $find ). '%';
			$mid = " and (UPPER(`filename`) like ?)";
			$bindvars=array($user,$findesc);
		} else {
			$mid = " ";
			$bindvars=array($user);
		}
		$query = "select `file_id`,`user_id`,`name`,`filename`,`filetype`,`filesize`,`created`,`hits` from `".BIT_DB_PREFIX."tiki_userfiles` where `user_id`=? $mid order by ".$this->convert_sortmode($sort_mode);
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."tiki_userfiles` where `user_id`=? $mid";
		$result = $this->query($query,$bindvars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}
	function get_userfile($user, $file_id) {
		$query = "select * from `".BIT_DB_PREFIX."tiki_userfiles` where `user_id`=? and `file_id`=?";
		$result = $this->query($query,array($user,(int) $file_id));
		$res = $result->fetchRow();
		$res['data'] = $this->mDb->db_byte_decode( $res['data'] );
		return $res;
	}
	function remove_userfile($user, $file_id) {
		global $uf_use_dir;
		$path = $this->getOne("select `path` from `".BIT_DB_PREFIX."tiki_userfiles` where `user_id`=? and `file_id`=?",array($user,(int) $file_id));
		if ($path) {
			@unlink ($uf_use_dir . $path);
		}
		$query = "delete from `".BIT_DB_PREFIX."tiki_userfiles` where `user_id`=? and file_id=?";
		$this->query($query,array($user,(int) $file_id));
	}
}
$userfileslib = new UserFilesLib();
?>
