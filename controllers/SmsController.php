<?php

class SmsController{

	public static function init($actions){
		if(method_exists(__CLASS__, $actions[0])) SmsController::{$actions[0]}();
		else Shared::view('404');
	}

	//发送短信验证码并保存在session中，60秒内仅限发送一条
	public static function send(){
		if(empty($_REQUEST['phone']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$phone = $_REQUEST['phone'];
		session_start();
		$smscode = rand(100, 999) . rand(100, 999);
		if(isset($_SESSION['sms']['lasttime']) && $_SESSION['sms']['lasttime'] + SMS_MIN_APPLY_TIME > time())
			Shared::outputJson(SMS_CODE_CAN_NOT_BE_APPLIED_CODE, SMS_CODE_CAN_NOT_BE_APPLIED_MESSAGE);
		$_SESSION['sms']['lasttime'] = time();
		$_SESSION['sms']['expire'] = time() + SMS_VALID_TIME;
		$_SESSION['sms']['code'] = $smscode;
		$_SESSION['sms']['phone'] = $phone;
		$minute = SMS_VALID_TIME / 60;
		$text = "【XTU在线】您的验证码是：{$smscode}，{$minute}分钟内有效。";
		Shared::sendSms(SMS_KEY, SMS_URL, $phone, $text);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//在session中检查短信是否有效，使用过则作废
	public static function check(){
		if(empty($_REQUEST['phone']) || empty($_REQUEST['smscode']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$phone = $_REQUEST['phone'];
		$smscode = $_REQUEST['smscode'];
		session_start();
		if(isset($_SESSION['sms']['code']) && $_SESSION['sms']['code'] == $smscode && $_SESSION['sms']['phone'] == $phone && $_SESSION['sms']['expire'] > time()){
			unset($_SESSION['sms']);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else
			Shared::outputJson(SMS_CODE_INVALID_CODE, SMS_CODE_INVALID_MESSAGE);
	}

}

?>