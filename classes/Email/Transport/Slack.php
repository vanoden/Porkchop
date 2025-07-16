<?php
	namespace Email\Transport;

	class Slack extends Base {
		/** @method protected deliver(email)
		 * Sends the email using the Slack transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 * @note This method is untested and may require adjustments based on the actual Slack API implementation.
		 */
		protected function _deliver($email) {
   		    foreach ($email->recipients() as $recipient) {
   		        $client = new \Slack\Client();
   		        $client->send($recipient, $email->body());
   		    }
		}
	}