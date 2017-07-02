<?php

class Database{

	public static function connect(){
		$conn = new mysqli(DATABASE_ADDRESS, DATABASE_USER, DATABASE_PASSWORD, DATABASE_TABLE);
		if($conn->connect_error) exit(Shared::outputJson(DATABASE_ERROR_CODE, DATABASE_ERROR_MESSAGE));
		else{
			$conn->query("SET NAMES 'UTF8'");
			return $conn;
		}
	}

	public static function getNum($sql){
		$conn = Database::connect();
		$rs = $conn->query($sql);
		$num = $rs->num_rows;
		$conn->close();
		return $num;
	}

	public static function excute($sql){
		$conn = Database::connect();
		if($conn->query($sql) === TRUE){
			$conn->close();
			return true;
		}
		else{
			$conn->close();
			exit(Shared::outputJson(DATABASE_ERROR_CODE, DATABASE_ERROR_MESSAGE));
		}
	}

	public static function query($sql){
		$data = array();
		$conn = Database::connect();
		$rs = $conn->query($sql);
		while($row = $rs->fetch_assoc()) array_push($data, $row);
		$conn->close();
		return $data;
	}
	
}