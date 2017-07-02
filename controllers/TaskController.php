<?php

require SITE_ROOT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Task.php';

class TaskController{

	public static function init($actions){
		if(method_exists(__CLASS__, $actions[0])) TaskController::{$actions[0]}();
		else Shared::view('404');
	}

	//任何人：获取任务列表
	public static function details(){
		if(!empty($_REQUEST['tid'])){
			if(is_numeric($_REQUEST['tid']) && $_REQUEST['tid'] > 0) $tid = $_REQUEST['tid'];
			else Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		}
		else $tid = '%';
		if(!empty($_REQUEST['id'])){
			if(is_numeric($_REQUEST['id']) && $_REQUEST['id'] > 0) $id = $_REQUEST['id'];
			else Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		}
		else $id = '%';
		if(!empty($_REQUEST['aid'])){
			if(is_numeric($_REQUEST['aid']) && $_REQUEST['aid'] > 0) $aid = $_REQUEST['aid'];
			else Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		}
		else $aid = '%';
		if(!empty($_REQUEST['search'])) $search = $_REQUEST['search'];
		else $search = '%';
		$data = Task::details($tid, $id, $aid, $search);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE, $data); 
	}

	//任务发布者：新增任务，同时只能发布MAX_SEND_TASK个任务
	public static function add(){
		if(empty($_REQUEST['id']) || empty($_REQUEST['title']) || empty($_REQUEST['content']) || !isset($_REQUEST['price']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		$title = $_REQUEST['title'];
		$content = $_REQUEST['content'];
		$price = $_REQUEST['price'];
		session_start();
		if(!isset($_SESSION['id']) || $id != $_SESSION['id']) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(mb_strlen($title, 'UTF8') > MAX_TITLE_LENGTH) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		if(mb_strlen($content, 'UTF8') > MAX_CONTENT_LENGTH) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		if(!is_numeric($price) || $price > MAX_PRICE || $price < 0) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE id = '$id' AND task_status != 4") >= MAX_SEND_TASK) Shared::outputJson(TASK_OVER_LIMIT_CODE, TASK_OVER_LIMIT_MESSAGE);
		Task::add($id, $title, $content, $price);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//任务发布者：修改任务，只有在待接受状态才能修改
	public static function update(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['id']) || empty($_REQUEST['title']) || empty($_REQUEST['content']) || !isset($_REQUEST['price']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$id = $_REQUEST['id'];
		$title = $_REQUEST['title'];
		$content = $_REQUEST['content'];
		$price = $_REQUEST['price'];
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND id = '$id'") != 1) Shared::outputJson(TASK_NOT_FOUND_CODE, TASK_NOT_FOUND_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE task_status = 0 AND tid = '$tid'") != 1) Shared::outputJson(TASK_CAN_NOT_BE_MODIFIED_CODE, TASK_CAN_NOT_BE_MODIFIED_MESSAGE);
		session_start();
		if(!isset($_SESSION['id']) || $id != $_SESSION['id']) Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(mb_strlen($title, 'UTF8') > MAX_TITLE_LENGTH) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		if(mb_strlen($content, 'UTF8') > MAX_CONTENT_LENGTH) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		if(!is_numeric($price) || $price > MAX_PRICE || $price < 0) Shared::outputJson(ILLEGAL_PARAMETER_CODE, ILLEGAL_PARAMETER_MESSAGE);
		Task::modify($tid, $title, $content, $price);
		Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
	}

	//任务发布者：删除任务。只有在待接受和已结束状态才能删除
	public static function delete(){
		if(empty($_REQUEST['tid']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		session_start();
		if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){
			Task::delete($tid);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		if(empty($_REQUEST['id']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$id = $_REQUEST['id'];
		if(isset($_SESSION['id']) && $_SESSION['id'] == $id){
			if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND id = '$id' AND (task_status = 0 OR task_status = 4)") == 1){
				Task::delete($tid);
				Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
			}
			else Shared::outputJson(TASK_CAN_NOT_BE_MODIFIED_CODE, TASK_CAN_NOT_BE_MODIFIED_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}

	//任务接收者：接任务，同时只能接收MAX_RECEIVE_TASK个任务
	public static function apply(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['aid']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$aid = $_REQUEST['aid'];
		session_start();
		if(!isset($_SESSION['id']) || $_SESSION['id'] != $aid)
			Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE aid = '$aid' AND task_status != 4") >= MAX_RECEIVE_TASK) Shared::outputJson(TASK_OVER_LIMIT_CODE, TASK_OVER_LIMIT_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND task_status = 0 AND id != '$aid'") == 1){
			Task::status($tid, 1);
			Task::receiver($tid, $aid);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}

	//任务接收者：取消接任务，只有在待确认状态才能操作
	public static function cancel(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['aid']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$aid = $_REQUEST['aid'];
		session_start();
		if(!isset($_SESSION['id']) || $_SESSION['id'] != $aid)
			Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND aid = '$aid' AND task_status = 1") == 1){
			Task::receiver($tid, 'NULL');
			Task::status($tid, 0);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}

	//任务发布者：接受任务申请，只有在待确认状态才能操作
	public static function accept(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['id']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$id = $_REQUEST['id'];
		session_start();
		if(!isset($_SESSION['id']) || $_SESSION['id'] != $id)
			Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND id = '$id' AND task_status = 1") == 1){
			Task::status($tid, 2);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}

	//任务发布者：拒绝任务申请，只有在待确认状态才能操作
	public static function reject(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['id']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$id = $_REQUEST['id'];
		session_start();
		if(!isset($_SESSION['id']) || $_SESSION['id'] != $id)
			Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND id = '$id' AND task_status = 1") == 1){
			Task::receiver($tid, 'NULL');
			Task::status($tid, 0);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}

	//任务接收者：完成任务，只有在待完成状态才能操作
	public static function finish(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['aid']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$aid = $_REQUEST['aid'];
		session_start();
		if(!isset($_SESSION['id']) || $_SESSION['id'] != $aid)
			Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND aid = '$aid' AND task_status = 2") == 1){
			Task::status($tid, 3);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}

	//任务发布者：确认任务完成，只有在已完成状态才能操作
	public static function confirm(){
		if(empty($_REQUEST['tid']) || empty($_REQUEST['id']))
			Shared::outputJson(LOSE_PARAMETER_CODE, LOSE_PARAMETER_MESSAGE);
		$tid = $_REQUEST['tid'];
		$id = $_REQUEST['id'];
		session_start();
		if(!isset($_SESSION['id']) || $_SESSION['id'] != $id)
			Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
		if(Task::getNum("SELECT 1 FROM task WHERE tid = '$tid' AND id = '$id' AND task_status = 3") == 1){
			Task::status($tid, 4);
			Shared::outputJson(SUCCESS_CODE, SUCCESS_MESSAGE);
		}
		else Shared::outputJson(RANK_ERROR_CODE, RANK_ERROR_MESSAGE);
	}
}
