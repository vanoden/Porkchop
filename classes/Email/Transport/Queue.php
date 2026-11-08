<?php
	namespace Email\Transport;

	class Queue Extends Base {

		/** @method protected _deliver(email)
		 * Sends the email using the Queue transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		protected function _deliver($email) {
			$userId = 0;
			if (!empty($GLOBALS['_SESSION_']->customer->id)) {
				$userId = (int)$GLOBALS['_SESSION_']->customer->id;
			}

			$message = new \Email\MailQueue\Message();
			$queued = $message->enqueue(array(
				'user_id' => $userId,
				'from_address' => $email->from(),
				'subject' => $email->subject(),
				'mime_type' => $email->html() ? 'text/html' : 'text/plain',
				'content' => (string)$email->body(),
				'deliveries' => \Email\MailQueue\Message::deliveriesFromAddresses($email->recipients(), 'TO'),
			));
			if (!$queued) {
				$this->error($message->error() ?: "Unable to queue email");
				return false;
			}
			$this->_result = 'Queued '.$message->code;
			return true;
		}
	}