<?php
	use PhpAmqpLib\Connection\AMQPConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	class Sender  
	{
		private $response;
		
		/**
		 * @var string
		 */
		private $corr_id;

		public function execute($message)
		{
			$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');

			$channel = $connection->channel();
			
			/**
			 * Cоздает анонимную эксклюзивную очередь с обратным вызовом
			 */
			list($callback_queue, ,) = $channel->queue_declare("", false, false, true, false);
			
			$channel->basic_consume($callback_queue, '', false, false, false, false, array($this, 'onResponse'));
			
			$this->response = null;
			
			$this->corr_id = uniqid();
			$jsonMessage = json_encode($message, JSON_UNESCAPED_UNICODE);
			
			$msg = new AMQPMessage($jsonMessage, array('correlation_id' => $this->corr_id, 'reply_to' => $callback_queue));
			
			$channel->basic_publish($msg, '', 'queue');
			
			while(!$this->response) {
				$channel->wait();
			}
			
			$channel->close();
			$connection->close();
			
			$result = $this->selectMessage();
			echo $result;
		}
		
		public function onResponse(AMQPMessage $rep)
		{
			if($rep->get('correlation_id') == $this->corr_id) {
				$this->response = $rep->body;
			}
		}
		
		private function selectMessage() 
		{
			/*
			 * Подключается к БД и забирает данные из таблицы
			 */
			$db = new DB();
			$link = $db->connect();
			$select = $db->select('text', 'test', "id = $this->response", $link);
			$result = $select['text'];
			mysqli_close($link);
			
			return $result;
		}
	}
?>