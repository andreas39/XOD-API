<?php

require SITE_ROOT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Shared.php';

class Bootstrap{

	public static function init(){

		if(DATA_AUTO_CLEAR){
			$_REQUEST = Shared::clearArray($_REQUEST);
		}

		$actions = Bootstrap::getActions();

		switch($actions[0]){
			case '':
				Shared::view('index');
				break;

			case 'user':
				if(count($actions) == 1 || $actions[1] == '') Shared::view('404');
				else{
					array_shift($actions);
					require SITE_ROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'UserController.php';
					UserController::init($actions);
				}
				break;

			case 'task':
				if(count($actions) == 1 || $actions[1] == '') Shared::view('404');
				else{
					array_shift($actions);
					require SITE_ROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'TaskController.php';
					TaskController::init($actions);
				}
				break;

			case 'sms':
				if(count($actions) == 1 || $actions[1] == '') Shared::view('404');
				else{
					array_shift($actions);
					require SITE_ROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'SmsController.php';
					SmsController::init($actions);
				}
				break;

			default:
				Shared::view('404');;
				break;
		}

	}

	public static function getActions(){
		$uri = $_SERVER['REQUEST_URI'];
		$uri = str_replace(strtolower(SITE_DIR), '', strtolower($uri));
		$uri = $uri[0] == '/' ? substr($uri, 1) : $uri;
		$uri = strpos($uri, '?') ? substr($uri, 0, strpos($uri, '?')) : $uri;
		$arr = explode('/', $uri);
		return $arr;
	}

}