<?php

require SITE_ROOT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Database.php';

class User extends Database{

	//获取用户信息，直接输出数组结果
	public static function profiles($id = '%'){
		$sql = "SELECT id, phone, nickname, CASE sex WHEN 0 THEN '女' WHEN 1 THEN '男' END sex, CASE status WHEN 0 THEN '禁用' WHEN 1 THEN '可用' END status FROM user WHERE id LIKE '$id'";
		return User::query($sql);
	}

	//通过电话获取用户信息，直接输出数组结果
	public static function profilesByPhone($phone){
		$sql = "SELECT id, phone, nickname, CASE sex WHEN 0 THEN '女' WHEN 1 THEN '男' END sex, CASE status WHEN 0 THEN '禁用' WHEN 1 THEN '可用' END status FROM user WHERE phone = '$phone'";
		return User::query($sql);
	}

	//新增用户
	public static function add($phone, $nickname, $sex, $originPassword, $status = 1){
		$password = md5($originPassword . MD5_SALT);
		User::excute("INSERT INTO user (phone, nickname, sex, password, status) VALUES ('$phone', '$nickname', '$sex', '$password', '$status')");
	}

	//修改用户资料
	public static function update($id, $nickname, $sex){
		User::excute("UPDATE user SET nickname = '$nickname', sex = '$sex' WHERE id = '$id'");
	}

	//修改手机号
	public static function phone($id, $phone){
		User::excute("UPDATE user SET phone = '$phone' WHERE id = '$id'");
	}

	//修改状态
	public static function status($id, $status){
		User::excute("UPDATE user SET status = '$status' WHERE id = '$id'");
	}


	//修改密码
	public static function password($id, $originPassword){
		$password = md5($originPassword . MD5_SALT);
		User::excute("UPDATE user SET password = '$password' WHERE id = '$id'");
	}

	//删除用户
	public static function delete($id){
		User::excute("DELETE FROM user WHERE id = '$id'");
	}

}