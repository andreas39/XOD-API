<?php

require SITE_ROOT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Database.php';

class Task extends Database{

	//获取任务列表
	public static function details($tid = '%', $id = '%', $aid = '%', $search = '%'){;
		$sql = "SELECT tid, title, content, CASE price WHEN 0 THEN '面议' ELSE price END price, CASE task_status WHEN 0 THEN '待接受' WHEN 1 THEN '已接受' WHEN 2 THEN '已完成' END task_status, UA.id as task_sender_id, UA.phone as task_sender_phone, UA.nickname as task_sender_nickname, CASE UA.sex WHEN 0 THEN '女' WHEN 1 THEN '男' END task_sender_sex, UB.id as task_receiver_id, UB.phone as task_receiver_phone, UB.nickname as task_receiver_nickname, CASE UB.sex WHEN 0 THEN '女' WHEN 1 THEN '男' END task_receiver_sex FROM task JOIN user UA ON task.id = UA.id LEFT OUTER JOIN user UB ON task.aid = UB.id WHERE tid LIKE '$tid' AND UA.id LIKE '$id' AND (title LIKE '%$search%' OR content LIKE '%$search%')";
		if(!empty($aid) && $aid != '%') $sql .= " AND UB.id LIKE '$aid'";
		return Task::query($sql);
	}

	//新增任务
	public static function add($id, $title, $content, $price, $taskStatus = 0){
		Task::excute("INSERT INTO task (id, title, content, price, task_status) VALUES ('$id', '$title', '$content', '$price', '$taskStatus')");
	}

	//修改任务
	public static function modify($tid, $title, $content, $price){
		Task::excute("UPDATE task SET title = '$title', content = '$content', price = '$price' WHERE tid = '$tid'");
	}

	//修改任务状态
	public static function status($tid, $taskStatus){
		Task::excute("UPDATE task SET task_status = '$taskStatus' WHERE tid = '$tid'");
	}

	//修改任务接收者
	public static function receiver($tid, $aid){
		Task::excute("UPDATE task SET aid = $aid WHERE tid = '$tid'");
	}

	//删除任务
	public static function delete($tid){
		Task::excute("DELETE FROM task WHERE tid = '$tid'");
	}

}