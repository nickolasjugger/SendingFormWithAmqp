<?php
	header("Content-Type: text/html; charset=utf-8");
	
	use PhpAmqpLib\Connection\AMQPConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	class Receiver 
	{
		
		public function listen()
		{
			$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();
			
			$channel->queue_declare('queue', false, false, false, false);
			
			$channel->basic_qos(null, 1, null);
			
			$channel->basic_consume('queue', '', false, false, false, false, array($this, 'callback'));
			
			while(count($channel->callbacks)) {
				$channel->wait();
			}
			
			$channel->close();
			$connection->close();
		}
		
		public function callback(AMQPMessage $req) {
			
			$message = json_decode($req->body);
			$result = $this->consumer($message);
			
			/*
			 * Создает сообщение с ответом, с таким же идентификатором
			 * корреляции, что и во входящем сообщении
			 */
			$msg = new AMQPMessage(json_encode($result, JSON_UNESCAPED_UNICODE), array('correlation_id' => $req->get('correlation_id')));
			
			/*
			 * Публикация в тот же канал, из которого пришло входящее сообщение
			 */
			$req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to'));
			
			$req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']);			
		}
		
		private function consumer($message) 
		{
			/*
			 * Переворачивает строку задом наперед 
			 */
			$strrev = "";
			for($i = mb_strlen($message, "UTF-8"); $i >= 0; $i--) {
				$strrev .= mb_substr($message, $i, 1, "UTF-8");
			}
			
			/*
			 * Подключается к БД и заносит данные в таблицу
			 */
			$db = new DB();
			$link = $db->connect();
			$id = $db->insert('text', 'test', $strrev, $link);
			mysqli_close($link);
			
			/*
			 * Возвращает в качестве ответа id записи в БД
			 */
			return $id;
		}
	}
?>