<?php
	class DB 
	{
		/*
		 * Данные для подключения к БД
		 */
		protected $db_host = 'localhost';
		protected $db_user = 'root';
		protected $db_pass = '';
		protected $db_name = 'testamqp';
		
		/*
		 * Открывает соединение к БД
		 */ 
		public function connect() 
		{
			$link = mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
			mysqli_set_charset($link, "utf8");
			return $link;
		}
		
		/* 
		 * Берет ряд mysql и возвращает ассоциативный массив, в котором
		 * названия колонок являются ключами массива
		 */
		public function processRowSet($rowSet, $singleRow=false)
		{
			$resultArray = array();
				while($row = mysqli_fetch_assoc($rowSet))
			{
				array_push($resultArray, $row);
			}
			if($singleRow === true) {
				return $resultArray[0];
			}
			return $resultArray;
		}

		/*
		 * Выбирает ряды из БД
		 */
		public function select($column, $table, $where, $link) 
		{
			$sql = "SELECT $column FROM $table WHERE $where";
			$result = mysqli_query($link, $sql);
			if(mysqli_num_rows($result) == 1) {
				return $this->processRowSet($result, true);
			}
			return $this->processRowSet($result);
			mysqli_free_result($result);
		}
		
		/*
		 * Вставляет новый ряд в таблицу
		 */
		public function insert($column, $table, $value, $link) 
		{
			$sql = "insert into $table ($column) values ('$value')";
			mysqli_query($link, $sql) or die(mysqli_error($link));
			return mysqli_insert_id($link);
		}
	}
?>