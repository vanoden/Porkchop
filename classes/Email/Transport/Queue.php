<?php
	namespace Email\Transport;

	class Queue Extends Base {

		/** @method protected _deliver(email)
		 * Sends the email using the Queue transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		protected function _deliver($email) {
			$messageQueue = new \Email\Queue();
			return $messageQueue->addMessage($email);
		}
	}