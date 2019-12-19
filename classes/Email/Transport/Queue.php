<?php
	namespace Email\Transport;

	class Queue Extends Base {

		public function deliver($email) {
			$messageQueue = new \Email\Queue();
			return $messageQueue->addMessage($email);
		}
	}