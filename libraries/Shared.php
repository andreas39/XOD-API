<?php

class Shared{

	public static function outputJson($code, $msg, $data = null){
		$arr['code'] = $code;
		$arr['msg'] = urlencode($msg);
		$arr['data'] = $data;
		//if($data) $arr['data'] = Shared::urlencodeArray($data);
		$json = json_encode($arr);
		header('Content-type: application/json; charset=utf-8');
		exit(urldecode($json));
	}

	public static function clearArray($arr){
		foreach($arr as $key => $value) {
			$arr[$key] = addslashes($value);
		}
		return $arr;
	}

	public static function urlencodeArray($arr){
		foreach($arr as $key => $value) {
			$arr[$key] = urlencode($value);
		}
		return $arr;
	}

	public static function view($page, $p = null){
		require SITE_ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $page . '.html';
	}

	public static function sendSms($apikey, $url, $mobile, $text){
		$encoded_text=urlencode("$text");
		$post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
		return self::sock_post($url,$post_string);
	}

	public static function sock_post($url,$query){
		$data = "";
		$info=parse_url($url);
		$fp=fsockopen($info["host"],80,$errno,$errstr,30);
		if(!$fp){
			return $data;
		}
		$head="POST ".$info['path']." HTTP/1.0\r\n";
		$head.="Host: ".$info['host']."\r\n";
		$head.="Referer: http://".$info['host'].$info['path']."\r\n";
		$head.="Content-type: application/x-www-form-urlencoded\r\n";
		$head.="Content-Length: ".strlen(trim($query))."\r\n";
		$head.="\r\n";
		$head.=trim($query);
		$write=fputs($fp,$head);
		$header = "";
		while ($str = trim(fgets($fp,4096))) {
			$header.=$str;
		}
		while (!feof($fp)) {
			$data .= fgets($fp,4096);
		}
		return $data;
	}

}